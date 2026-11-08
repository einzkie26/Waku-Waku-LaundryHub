<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['q' => ['nullable', 'string', 'max:100'], 'status' => ['nullable', 'in:all,active,inactive']]);
        $status = $request->query('status', 'all');
        $query = Employee::query();
        if ($status !== 'all') $query->whereRaw('LOWER(status) = ?', [$status]);
        if ($request->filled('q')) {
            $term = trim($request->query('q'));
            $query->where(fn ($query) => $query->where('name', 'like', '%'.$term.'%')->orWhere('email', 'like', '%'.$term.'%')->orWhere('contact', 'like', '%'.$term.'%'));
        }
        $employees = $query->orderBy('name')->paginate(12)->withQueryString();
        $metrics = [
            'all' => Employee::count(),
            'active' => Employee::whereRaw('LOWER(status) = ?', ['active'])->count(),
            'inactive' => Employee::whereRaw('LOWER(status) = ?', ['inactive'])->count(),
        ];
        return view('admin.employees.list', compact('employees', 'metrics', 'status'));
    }

    public function add()
    {
        return redirect()->route('employees.list', ['add' => 1]);
    }

    public function store(Request $request)
    {
        $data = $request->validateWithBag('employeeForm', [
            'name' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'contact' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', 'unique:employees,email'],
            'hire_date' => ['required', 'date_format:Y-m-d'],
            'shift' => ['required', 'in:Morning,Afternoon,Night'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $data['password'] = Hash::make($data['password']);
        $employee = Employee::create($data + ['status' => 'Active', 'is_active' => true]);

        return redirect()->route('employees.list')
            ->with('success', $employee->name.' was added. Their staff account is active.')
            ->with('new_employee_id', $employee->id);
    }

    public function activate($id)
    {
        Employee::findOrFail($id)->update(['status' => 'Active', 'is_active' => true]);
        return back()->with('success', 'Employee activated. Staff sign-in is available.');
    }

    public function deactivate($id)
    {
        Employee::findOrFail($id)->update(['status' => 'Inactive', 'is_active' => false]);
        return back()->with('success', 'Employee deactivated. Staff sign-in is disabled.');
    }

    public function show($id)
    {
        $employee = Employee::findOrFail($id);
        $attendance = $employee->attendances()->latest('date')->take(8)->get();
        $ordersHandled = OrderStatusHistory::where('changed_by_type', 'staff')->where('changed_by_id', $employee->id)->distinct()->count('order_id');

        return view('admin.employees.show', compact('employee', 'attendance', 'ordersHandled'));
    }
}
