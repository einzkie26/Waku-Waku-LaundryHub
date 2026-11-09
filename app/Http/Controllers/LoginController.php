<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Employee;
use App\Models\User;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('credentials.login');
    }

    public function login(Request $request)
    {
        if (!$request->filled('username') && $request->filled('email')) {
            $request->merge(['username' => $request->input('email')]);
        }
        $request->validate([
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
        ]);
        $identity = trim($request->input('username'));
        $remember = $request->boolean('remember');
        $employee = Employee::where('email', $identity)->first();
        if ($employee && Hash::check($request->password, $employee->password)) {
            $user = User::where('username', $identity)->first();
            if ($user && Hash::check($request->password, $user->password)) {
                return redirect()->route('login.form')->withErrors(['username' => 'These details match more than one account. Ask the administrator to update your username.'])
                    ->withInput($request->only('username', 'remember'));
            }
            if (!$employee->is_active || strtolower($employee->status) !== 'active') {
                return redirect()->route('login.form')->withErrors(['username' => 'Your staff account is inactive. Please contact the administrator.'])
                    ->withInput($request->only('username', 'remember'));
            }
            Auth::guard('web')->logout();
            Auth::guard('staff')->login($employee, $remember);
            $request->session()->put('staff_id', $employee->id);
            $request->session()->forget(['remember_staff', 'url.intended']);
            $request->session()->regenerate();

            return redirect()->route('staff.dashboard')->with('success', 'Welcome back, '.$employee->name.'.');
        }
        if (Auth::guard('web')->attempt(['username' => $identity, 'password' => $request->password], $remember)) {
            Auth::guard('staff')->logout();
            $request->session()->forget(['staff_id', 'remember_staff']);
            $request->session()->regenerate();
            $user = Auth::guard('web')->user();

            if ($user->role === 'admin') {
                session()->flash('success', 'Welcome back, '.$user->name.'.');
                return redirect()->route('admin.dashboard');
            }

            session()->flash('success', 'Welcome back, ' . $user->name);
            return redirect()->intended(route('landing'));
        }

        return redirect()->route('login.form')->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('username', 'remember'));
    }

    public function logout(Request $request)
    {
        return app(LogoutController::class)->logout($request);
    }
}
