<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Order;
use App\Models\User;
use App\Models\UserLog;
use App\Services\AdminReportService;
use App\Services\LaundryOrderService;
use App\Support\LaundryWorkflow;
use App\Support\AdminModal;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['all', 'active', 'Pending', 'Picked Up', 'Washing', 'Drying', 'Processing', 'Ready', 'Completed', 'Refunded'])],
            'payment' => ['nullable', 'in:all,pending,paid'],
            'service' => ['nullable', Rule::in(array_keys(LaundryWorkflow::SERVICES))],
        ]);
        $status = $request->query('status', 'all');
        $query = Order::with('user');
        if ($status === 'active') $query->whereNotIn('status', ['Completed', 'Refunded']);
        elseif ($status !== 'all') $query->where('status', $status);
        if ($request->filled('payment') && $request->query('payment') !== 'all') $query->where('payment_status', $request->query('payment'));
        if ($request->filled('service')) $query->where('service', $request->query('service'));
        if ($request->filled('q')) {
            $term = trim($request->query('q'));
            $query->where(fn ($query) => $query->where('reference_no', 'like', '%'.$term.'%')
                ->orWhereHas('user', fn ($user) => $user->where('name', 'like', '%'.$term.'%')->orWhere('phone', 'like', '%'.$term.'%')));
        }
        $orders = $query->latest()->paginate(12)->withQueryString();
        $metrics = [
            'all' => Order::count(),
            'active' => Order::whereNotIn('status', ['Completed', 'Refunded'])->count(),
            'ready' => Order::where('status', 'Ready')->count(),
            'unpaid' => (float) Order::where('payment_status', 'pending')->where('status', '!=', 'Refunded')->sum(DB::raw(AdminReportService::AMOUNT)),
        ];
        $bookingCustomers = User::where('role', 'customer')->orderBy('name')->get();
        return view('admin.orders.index', compact('orders', 'metrics', 'status', 'bookingCustomers'));
    }

    public function staffIndex()
    {
        return redirect()->route('staff.orders.index');
    }

    public function create()
    {
        $customers = User::where('role', 'customer')->orderBy('name')->get();
        return view('admin.orders.create', compact('customers'));
    }

    public function store(Request $request, LaundryOrderService $orders)
    {
        $modal = $request->input('_admin_modal') === 'order-create';
        $errorBag = $modal ? 'orderForm' : 'default';
        $data = $request->validateWithBag($errorBag, LaundryWorkflow::rules(true));
        try {
            $order = $orders->create($data, (int) $data['user_id'], 'admin', $request->user()->id);
        } catch (ValidationException $exception) {
            throw $exception->errorBag($errorBag);
        }
        if ($modal) {
            return redirect(AdminModal::returnUrl($request, 'orders.index'))
                ->with('success', $order->reference_no.' was created.')
                ->with('new_order_id', $order->id);
        }
        return redirect()->route('orders.show', $order->id)->with('success', 'Order created. Confirm pickup when the laundry has been collected.');
    }

    public function orderTracking($reference)
    {
        $query = Order::with(['user', 'statusHistories'])->where('reference_no', $reference);
        if (auth()->user()->role !== 'admin') $query->where('user_id', auth()->id());
        $order = $query->firstOrFail();
        return view('order-tracking', compact('order'));
    }

    public function show($id)
    {
        $order = Order::with(['user', 'statusHistories'])->findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, $id, LaundryOrderService $orders)
    {
        $data = $request->validate(['current_status' => ['required', 'string', 'max:40']]);
        $order = $orders->advance(Order::findOrFail($id), 'admin', $request->user()->id, $data['current_status']);
        $message = $order->reference_no.': '.LaundryWorkflow::label($order->status).'.';
        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => $message])
            : back()->with('success', $message);
    }

    public function markAsPaid(Request $request, Order $order, LaundryOrderService $orders)
    {
        $data = $request->validate([
            'payment_method' => ['required', Rule::in(['Cash', 'GCash', 'Bank'])],
            'payment_reference' => ['nullable', 'string', 'max:100', 'required_if:payment_method,GCash,Bank'],
        ]);
        $orders->recordPayment($order, $data);
        return back()->with('success', 'Payment recorded. Laundry progress stays unchanged.');
    }

    public function showReceipt($orderId)
    {
        $staff = session('staff_id') && Employee::whereKey(session('staff_id'))->whereRaw('LOWER(status) = ?', ['active'])->exists();
        abort_unless(auth()->check() || $staff, 403);
        $query = Order::with(['user', 'statusHistories'])->whereKey($orderId);
        if (!$staff && auth()->user()->role !== 'admin') $query->where('user_id', auth()->id());
        $order = $query->firstOrFail();
        return view('receipt', compact('order'));
    }

    public function reschedule(Request $request, $id)
    {
        $data = $request->validate([
            'new_delivery_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'new_pickup_time' => ['required', Rule::in(array_keys(LaundryWorkflow::TIMES))],
        ]);
        if (Carbon::createFromFormat('!Y-m-d H:i', $data['new_delivery_date'].' '.$data['new_pickup_time'])->isPast()) {
            throw ValidationException::withMessages(['new_pickup_time' => 'Choose a pickup time in the future.']);
        }
        DB::transaction(function () use ($id, $data) {
            $order = Order::lockForUpdate()->findOrFail($id);
            if ($order->status !== 'Pending') throw ValidationException::withMessages(['date' => 'Pickup can only be rescheduled before the laundry is collected.']);
            $order->update(['date' => $data['new_delivery_date'], 'time' => $data['new_pickup_time']]);
            UserLog::create(['user_id' => auth()->id(), 'action' => 'Rescheduled '.$order->reference_no.' pickup to '.$data['new_delivery_date'].' '.$data['new_pickup_time']]);
        });
        return back()->with('success', 'Pickup rescheduled.');
    }

    public function refund(Request $request, $id)
    {
        $data = $request->validate([
            'refund_amount' => ['required', 'numeric', 'min:0.01'],
            'refund_reason' => ['required', 'string', 'max:255'],
        ]);
        DB::transaction(function () use ($id, $data) {
            $order = Order::lockForUpdate()->findOrFail($id);
            if ($order->payment_status !== 'paid' || $order->status === 'Refunded') {
                throw ValidationException::withMessages(['refund' => 'Only a paid order that has not been refunded can be closed as refunded.']);
            }
            if (abs((float) $data['refund_amount'] - (float) $order->total_amount) > 0.009) {
                throw ValidationException::withMessages(['refund_amount' => 'Enter the full order total. Partial refunds are not supported.']);
            }
            $order->update(['status' => 'Refunded']);
            $order->statusHistories()->create(['status' => 'Refunded', 'changed_by_type' => 'admin', 'changed_by_id' => auth()->id()]);
            UserLog::create(['user_id' => auth()->id(), 'action' => Str::limit('Refund recorded for '.$order->reference_no.': '.$data['refund_amount'].'. '.$data['refund_reason'], 250)]);
        });
        return back()->with('success', 'Refund recorded and order closed.');
    }

    public function serviceStats(Request $request)
    {
        $request->validate(['period' => ['nullable', 'in:all,week,month']]);
        $period = $request->query('period', 'month');
        $query = Order::where('status', '!=', 'Refunded');
        if ($period !== 'all') $query->where('created_at', '>=', now()->subDays(($period === 'week' ? 7 : 30) - 1)->startOfDay());
        $rows = $query->select('service')->selectRaw('COUNT(*) as total_orders, SUM(weight) as weight, SUM('.AdminReportService::AMOUNT.') as revenue')->groupBy('service')->toBase()->get()->keyBy('service');
        $names = collect(array_keys(LaundryWorkflow::SERVICES))->merge($rows->keys())->unique();
        $serviceStats = $names->map(fn ($name) => (object) [
            'service' => $name, 'total_orders' => (int) ($rows->get($name)?->total_orders ?? 0),
            'weight' => (float) ($rows->get($name)?->weight ?? 0), 'revenue' => (float) ($rows->get($name)?->revenue ?? 0),
        ])->sortByDesc('total_orders')->values();
        $totalOrders = $serviceStats->sum('total_orders');
        $totalWeight = $serviceStats->sum('weight');
        $bookedValue = $serviceStats->sum('revenue');
        return view('admin.reports.service-stats', compact('serviceStats', 'period', 'totalOrders', 'totalWeight', 'bookedValue'));
    }
}
