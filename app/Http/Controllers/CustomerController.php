<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = User::where('role', 'customer')
            ->withCount('orders')
            ->with(['orders' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->get()
            ->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'username' => $customer->username,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'address' => $customer->address,
                    'photo' => $customer->photo,
                    'created_at' => $customer->created_at,
                    'orders_count' => $customer->orders_count,
                    'last_order_date' => optional($customer->orders->first())->created_at,
                ];
            });

        return view('admin.customers.index', ['customers' => $customers]);
    }

    public function staffIndex()
    {
        $customers = User::where('role', 'customer')
            ->withCount('orders')
            ->with(['orders' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->get();

        return view('staff.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('staff.customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('customer-photos', 'public');
            $validated['photo'] = $photoPath;
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 'customer';

        $customer = User::create($validated);

        return redirect()->route('staff.customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function show($id)
    {
        $customer = User::with(['orders' => function ($query) {
            $query->latest();
        }])->findOrFail($id);

        return view('staff.customers.show', compact('customer'));
    }

    public function edit($id)
    {
        $customer = User::findOrFail($id);
        return view('staff.customers.edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        $customer = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $id,
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($customer->photo) {
                Storage::disk('public')->delete($customer->photo);
            }
            $photoPath = $request->file('photo')->store('customer-photos', 'public');
            $validated['photo'] = $photoPath;
        }

        $customer->update($validated);

        return redirect()->route('staff.customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function getCustomers()
    {
        $customers = User::where('role', 'customer')
            ->withCount('orders')
            ->with(['orders' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->get()
            ->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'address' => $customer->address,
                    'created_at' => $customer->created_at,
                    'orders_count' => $customer->orders_count,
                    'last_order_date' => optional($customer->orders->first())->created_at,
                ];
            });

        return response()->json($customers);
    }

    public function getRecentOrders($id)
    {
        $orders = Order::where('user_id', $id)
            ->latest()
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'reference_no' => $order->reference_no,
                    'date' => $order->created_at->format('Y-m-d'),
                    'service' => $order->service_name ?? $order->service ?? '—',
                    'weight' => $order->weight ? $order->weight . ' kg' : '—',
                    'status' => ucfirst($order->status),
                    'amount' => '₱' . number_format($order->amount ?? $order->total_price, 2),
                ];
            });

        return response()->json($orders);
    }

    public function getCustomerOrders($id)
    {
        $orders = Order::where('user_id', $id)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'date' => $order->created_at,
                    'service' => $order->service,
                    'weight' => $order->weight,
                    'status' => $order->status,
                    'reference_no' => $order->reference_no,
                    'amount' => $order->total_price,
                ];
            });

        return response()->json($orders);
    }

    public function orderHistory(Request $request)
    {
        $view = $request->query('view', 'active');
        abort_unless(in_array($view, ['active', 'closed', 'all'], true), 404);
        $query = Order::where('user_id', auth()->id());
        $counts = [
            'active' => (clone $query)->whereNotIn('status', ['Completed', 'Refunded'])->count(),
            'closed' => (clone $query)->whereIn('status', ['Completed', 'Refunded'])->count(),
            'all' => (clone $query)->count(),
        ];
        if ($view === 'active') {
            $query->whereNotIn('status', ['Completed', 'Refunded']);
        } elseif ($view === 'closed') {
            $query->whereIn('status', ['Completed', 'Refunded']);
        }
        $orders = $query->latest()->paginate(10)->withQueryString();

        return view('customer.orders.history', compact('orders', 'counts', 'view'));
    }

    public function adminShow($id)
    {
        $customer = User::select('id', 'name', 'username', 'phone', 'address', 'created_at', 'role')
            ->where('role', 'customer')
            ->where('id', $id)
            ->with(['orders' => function ($query) {
                $query->latest();
            }])
            ->firstOrFail();

        $customer->total_amount = $customer->orders->sum('total_price');

        return view('admin.customers.show', compact('customer'));
    }
}
