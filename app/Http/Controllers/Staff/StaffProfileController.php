<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffProfileController extends Controller
{
    public function show()
    {
        $employee = Employee::find(Session::get('staff_id'));
        if (!$employee) {
            return redirect()->route('staff.login.form')
                           ->with('error', 'Please login to access your profile.');
        }

        return view('staff.profile', compact('employee'));
    }

    public function update(Request $request)
    {
        $employee = Employee::find(Session::get('staff_id'));
        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('employees')->ignore($employee->id)],
            'phone' => 'required|string|max:20',
            'current_password' => 'required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
        ]);

        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $employee->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 422);
            }
        }

        try {
            $employee->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
            ]);

            if ($request->filled('new_password')) {
                $employee->update([
                    'password' => Hash::make($request->new_password)
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'employee' => $employee
            ]);
        } catch (\Exception $e) {
            \Log::error('Profile Update Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating profile: ' . $e->getMessage()
            ], 500);
        }
    }
} 