<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

class StaffAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $guard = Auth::guard('staff');
        $staffId = $guard->id() ?? Session::get('staff_id');
        $employee = !Auth::guard('web')->check() && $staffId ? Employee::find($staffId) : null;
        if (!$employee || !$employee->is_active || strtolower($employee->status) !== 'active') {
            $guard->logout();
            Session::forget(['staff_id', 'remember_staff']);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Please sign in with an active staff account.'], 401);
            }

            return redirect()->route('login.form')->with('error', 'Please sign in with an active staff account.');
        }
        // Keep existing employee sessions and staff workflows compatible with the shared login.
        if (!$guard->check()) $guard->login($employee);
        Session::put('staff_id', $employee->id);
        $request->attributes->add(['staff' => $employee]);
        
        return $next($request);
    }
}
