<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\OrderStatusHistory;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Notifications\OrderCompleted;

class OrderController extends Controller
{
    /**
     * Display a paginated list of orders.
     */
    public function index()
    {
        $orders = Order::latest()->paginate(10);
        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Display a paginated list of orders for staff members.
     */
    public function staffIndex()
    {
        $orders = Order::with(['user', 'statusHistories'])
            ->latest()
            ->paginate(10);

        return view('staff.orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new order.
     */
    public function create()
    {
        $users = User::where('role', 'customer')->get();
        $bookedDates = Order::pluck('date')->toArray();
        
        // Get lists for dropdowns
        $order = new Order();
        $laundryStyles = $order->laundry_styles_list;
        $fabricTypes = $order->fabric_types_list;
        $additionalServices = $order->additional_services_list;
        
        return view('admin.orders.create', compact('users', 'bookedDates', 'laundryStyles', 'fabricTypes', 'additionalServices'));
    }

    /**
     * Store a newly created order in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'service' => 'required|string',
                'laundry_style' => 'required|string',
                'fabric_type' => 'required|string',
                'weight' => 'required|numeric|min:0.1',
                'date' => 'required|date',
                'time' => 'required|string',
                'special_instructions' => 'nullable|string',
                'additional_services' => 'nullable|array',
                'payment_status' => 'nullable|in:pending,paid',
                'payment_method' => 'nullable|string',
                'payment_reference' => 'nullable|string',
            ]);

            $priceList = [
                'Wash & Fold' => 50,
                'Dry Cleaning' => 70,
                'Express Service' => 100,
                'Ironing' => 40,
            ];

            $pricePerKg = $priceList[$request->service] ?? 0;
            $basePrice = $pricePerKg * $request->weight;

            // Calculate additional charges
            $additionalCharges = 0;
            $additionalServices = [];
            if ($request->has('additional_services')) {
                $order = new Order();
                $servicePrices = $order->additional_services_list;
                
                foreach ($request->additional_services as $service) {
                    if (isset($servicePrices[$service])) {
                        $additionalCharges += $servicePrices[$service];
                        $additionalServices[] = $service;
                    }
                }
            }

            // Generate a unique reference number
            do {
                $referenceNo = 'REF-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 5));
            } while (Order::where('reference_no', $referenceNo)->exists());

            $order = Order::create([
                'user_id' => $request->user_id,
                'service' => $request->service,
                'laundry_style' => $request->laundry_style,
                'fabric_type' => $request->fabric_type,
                'special_instructions' => $request->special_instructions,
                'additional_services' => $additionalServices,
                'additional_charges' => $additionalCharges,
                'weight' => $request->weight,
                'date' => $request->date,
                'time' => $request->time,
                'total_price' => $basePrice,
                'total_amount' => $basePrice + $additionalCharges,
                'status' => 'Pending',
                'reference_no' => $referenceNo,
                'payment_status' => $request->payment_status ?? 'pending',
                'payment_method' => $request->payment_method,
                'payment_reference' => $request->payment_reference,
                'paid_at' => ($request->payment_status === 'paid') ? now() : null,
                'operator_type' => auth('web')->check() ? 'admin' : (auth('staff')->check() ? 'staff' : 'unknown'),
                'operator_id' => auth('web')->check() ? auth('web')->id() : (auth('staff')->check() ? auth('staff')->id() : null),
            ]);

            // Log order creation in order_status_histories
            $operatorType = auth('web')->check() ? 'admin' : (auth('staff')->check() ? 'staff' : 'unknown');
            $operatorId = auth('web')->check() ? auth('web')->id() : (auth('staff')->check() ? auth('staff')->id() : null);
            \App\Models\OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'Created',
                'changed_by_type' => $operatorType,
                'changed_by_id' => $operatorId,
            ]);

            Log::info('Order created successfully', [
                'order_id' => $order->id,
                'reference_no' => $order->reference_no,
                'laundry_style' => $order->laundry_style,
                'fabric_type' => $order->fabric_type
            ]);

            return redirect()->route('orders.show', $order->id)
                ->with('success', 'Order created successfully!');
        } catch (\Exception $e) {
            Log::error('Error creating order: ' . $e->getMessage());
            return back()->with('error', 'Error creating order: ' . $e->getMessage());
        }
    }

    public function orderTracking($reference)
    {
        $query = Order::with(['user', 'statusHistories'])->where('reference_no', $reference);
        if (auth()->user()->role !== 'admin') {
            $query->where('user_id', auth()->id());
        }
        $order = $query->firstOrFail();
        return view('order-tracking', compact('order'));
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        // Prevent status update if order is refunded
        if ($order->status === 'Refunded') {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update status of a refunded order'
                ]);
            } else {
                return redirect()->route('orders.index')->with('error', 'Cannot update status of a refunded order');
            }
        }

        // Define status flows for different services
        $statusFlows = [
            'Wash & Fold' => ['Pending', 'Picked Up', 'Washing', 'Drying', 'Ready', 'Completed'],
            'Dry Cleaning' => ['Pending', 'Picked Up', 'Processing', 'Ready', 'Completed'],
            'Express Service' => ['Pending', 'Picked Up', 'Processing', 'Ready', 'Completed'],
            'Ironing' => ['Pending', 'Picked Up', 'Processing', 'Ready', 'Completed']
        ];

        $statusSteps = $statusFlows[$order->service] ?? $statusFlows['Wash & Fold'];
        $currentIndex = array_search($order->status, $statusSteps);

        // If current status is not found or is the last status, return error
        if ($currentIndex === false || $currentIndex === count($statusSteps) - 1) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot advance status further'
                ]);
            } else {
                return redirect()->route('orders.index')->with('error', 'Cannot advance status further');
            }
        }

        // Get the next status
        $newStatus = $statusSteps[$currentIndex + 1];

        // Update the order status
        $order->status = $newStatus;
        $order->save();

        // Create status history entry
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => $newStatus,
            'changed_by_type' => 'admin',
            'changed_by_id' => auth()->id()
        ]);

        // Create user log entry
        UserLog::create([
            'user_id' => auth()->id(),
            'action' => 'Order Status Updated',
            'description' => "Order #{$order->id} status updated to {$newStatus}",
            'ip_address' => request()->ip()
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully'
            ]);
        } else {
            return redirect()->route('orders.index')->with('success', 'Order status updated successfully!');
        }
    }

    public function serviceStats(Request $request)
    {
        $period = $request->get('period', 'all');
        $query = Order::query();

        if ($period === 'week') {
            $query->where('created_at', '>=', now()->subDays(7));
        } elseif ($period === 'month') {
            $query->where('created_at', '>=', now()->subDays(30));
        }

        $serviceStats = $query->selectRaw('
                service,
                COUNT(*) as total_orders,
                SUM(total_price) as revenue
            ')
            ->groupBy('service')
            ->get();

        if ($serviceStats->isEmpty()) {
            $defaultServices = [
                'Wash & Fold' => ['orders' => 0, 'revenue' => 0],
                'Dry Cleaning' => ['orders' => 0, 'revenue' => 0],
                'Express Service' => ['orders' => 0, 'revenue' => 0],
                'Ironing' => ['orders' => 0, 'revenue' => 0]
            ];

            $serviceStats = collect($defaultServices)->map(function ($data, $service) {
                return (object)[
                    'service' => $service,
                    'total_orders' => $data['orders'],
                    'revenue' => $data['revenue'],
                    'rank' => 0
                ];
            });
        }

        $serviceStats = $serviceStats->sortByDesc('total_orders')->values();
        foreach ($serviceStats as $i => $stat) {
            $stat->rank = $i + 1;
        }

        $labels = $serviceStats->pluck('service')->toArray();
        $revenues = $serviceStats->pluck('revenue')->toArray();
        $orders = $serviceStats->pluck('total_orders')->toArray();

        return view('admin.reports.service-stats', compact('serviceStats', 'labels', 'revenues', 'orders', 'period'));
    }

    public function markAsPaid(Request $request, Order $order)
    {
        $order->update([
            'payment_status' => 'paid',
            'payment_method' => $request->payment_method,
            'payment_reference' => $request->payment_reference,
            'paid_at' => now(),
        ]);
        return back()->with('success', 'Order marked as paid.');
    }

    public function showReceipt($orderId)
    {
        $staff = session('staff_id') ? \App\Models\Employee::whereKey(session('staff_id'))->whereRaw('LOWER(status) = ?', ['active'])->exists() : false;
        abort_unless(auth()->check() || $staff, 403);
        $query = Order::with(['user', 'statusHistories'])->whereKey($orderId);
        if (!$staff && auth()->user()->role !== 'admin') {
            $query->where('user_id', auth()->id());
        }
        $order = $query->firstOrFail();
        return view('receipt', compact('order'));
    }

    public function show($id)
    {
        $order = Order::with('user')->findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Reschedule the delivery date of an order.
     */
    public function reschedule(Request $request, $id)
    {
        $request->validate([
            'new_delivery_date' => 'required|date|after:today',
            'reason' => 'nullable|string|max:255',
        ]);
        $order = Order::findOrFail($id);
        $order->date = $request->new_delivery_date;
        $order->save();
        return redirect()->route('orders.index')->with('success', 'Order delivery rescheduled successfully.');
    }

    /**
     * Process a refund for an order.
     */
    public function refund(Request $request, $id)
    {
        $request->validate([
            'refund_amount' => 'required|numeric|min:0',
            'refund_reason' => 'nullable|string|max:255',
        ]);
        $order = Order::findOrFail($id);
        $order->status = 'Refunded';
        $order->save();
        return redirect()->route('orders.index')->with('success', 'Refund processed successfully.');
    }
}
