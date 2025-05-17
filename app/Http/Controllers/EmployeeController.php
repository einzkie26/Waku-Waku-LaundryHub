<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function add()
    {
        return view('admin.employees.add');
    }

    public function store(Request $request)
    {
        Log::info('Employee creation request data:', $request->all());

        $request->validate([
            'name'      => 'required|string|max:255',
            'position'  => 'required|string|max:255',
            'contact'   => 'required|string|max:20',
            'email'     => 'required|email|unique:employees,email',
            'hire_date' => 'required|date',
            'shift'     => 'required|string|max:20',
            'password'  => 'required|string|min:8',
        ]);

        try {
            $employee = Employee::create([
                'name'      => $request->name,
                'position'  => $request->position,
                'contact'   => $request->contact,
                'email'     => $request->email,
                'hire_date' => $request->hire_date,
                'shift'     => $request->shift,
                'status'    => 'Active',
                'password'  => Hash::make($request->password),
            ]);

            Log::info('Employee created successfully:', ['employee_id' => $employee->id]);

            return redirect()->route('employees.index')->with('success', 'Employee added successfully!');
        } catch (\Exception $e) {
            Log::error('Error creating employee:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create employee. Please try again.']);
        }
    }

    private function generateUniquePasskey()
    {
        do {
            $passkey = strtoupper(substr(uniqid('EMP'), -8));
        } while (Employee::where('passkey', $passkey)->exists());

        return $passkey;
    }

    public function index(Request $request)
    {
        $status = $request->query('status');
        $query = Employee::query();
        if ($status === 'active') {
            $query->where('status', 'Active');
        } elseif ($status === 'inactive') {
            $query->where('status', 'Inactive');
        }
        $employees = $query->paginate(5);

        if ($request->ajax()) {
            return view('admin.employees.partials.employee-table', compact('employees'))->render();
        }
        return view('admin.employees.list', compact('employees'));
    }

    public function activate($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->status = 'Active';
        $employee->save();
        return redirect()->route('employees.list')->with('success', 'Employee activated successfully.');
    }

    public function deactivate($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->status = 'Inactive';
        $employee->save();
        return redirect()->route('employees.list')->with('success', 'Employee deactivated successfully.');
    }

    public function show($id)
    {
        $employee = Employee::findOrFail($id);
        return view('admin.employees.show', compact('employee'));
    }
}
