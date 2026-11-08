<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Services\AdminReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminCustomerController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $query = User::where('role', 'customer')->withCount('orders')->withMax('orders', 'created_at');
        if ($request->filled('q')) {
            $term = trim($request->query('q'));
            $query->where(fn ($query) => $query->where('name', 'like', '%'.$term.'%')->orWhere('username', 'like', '%'.$term.'%')->orWhere('phone', 'like', '%'.$term.'%'));
        }
        $customers = $query->orderBy('name')->paginate(12)->withQueryString();
        $metrics = [
            'total' => User::where('role', 'customer')->count(),
            'withOrders' => User::where('role', 'customer')->has('orders')->count(),
            'newThisMonth' => User::where('role', 'customer')->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
        ];
        return view('admin.customers.index', compact('customers', 'metrics'));
    }

    public function show($id)
    {
        $customer = User::where('role', 'customer')->findOrFail($id);
        $orders = $customer->orders()->with('user')->latest()->paginate(10);
        $metrics = [
            'orders' => $customer->orders()->count(),
            'active' => $customer->orders()->whereNotIn('status', ['Completed', 'Refunded'])->count(),
            'paid' => (float) $customer->orders()->where('payment_status', 'paid')->where('status', '!=', 'Refunded')->sum(DB::raw(AdminReportService::AMOUNT)),
        ];
        return view('admin.customers.show', compact('customer', 'orders', 'metrics'));
    }

    public function create()
    {
        return view('admin.customers.create');
    }

    public function edit($id)
    {
        $customer = User::where('role', 'customer')->findOrFail($id);
        return view('admin.customers.edit', compact('customer'));
    }

    private function details(Request $request, ?User $customer = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($customer?->id)],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:255'],
        ];
        if (!$customer) $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        return $request->validate($rules);
    }

    public function store(Request $request)
    {
        $customer = User::create($this->details($request) + ['role' => 'customer']);
        return redirect()->route('admin.customers.show', $customer->id)->with('success', 'Customer added. You can now create their first order.');
    }

    public function update(Request $request, $id)
    {
        $customer = User::where('role', 'customer')->findOrFail($id);
        $customer->update($this->details($request, $customer));
        return redirect()->route('admin.customers.show', $customer->id)->with('success', 'Customer details updated.');
    }
}
