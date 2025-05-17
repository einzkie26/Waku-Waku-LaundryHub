<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Employee;
use Illuminate\Support\Facades\Log;

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
        Log::info('StaffAuth middleware check', [
            'has_staff_id' => Session::has('staff_id'),
            'staff_id' => Session::get('staff_id'),
            'url' => $request->url()
        ]);

        if (!Session::has('staff_id')) {
            Log::warning('No staff session found', [
                'url' => $request->url(),
                'ip' => $request->ip()
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Unauthorized.'], 401);
            }
            
            return redirect()->route('staff.login.form')
                ->with('error', 'Please log in to access the staff portal.');
        }
        
        // Verify the employee still exists and is active
        $employee = Employee::where('id', Session::get('staff_id'))
                          ->where('status', 'active')
                          ->first();
                          
        if (!$employee) {
            Log::warning('Invalid staff session', [
                'staff_id' => Session::get('staff_id'),
                'reason' => 'Employee not found or inactive'
            ]);

            Session::forget('staff_id');
            return redirect()->route('staff.login.form')
                ->with('error', 'Your session has expired. Please log in again.');
        }
        
        // Add staff user to the request for easy access in controllers
        $request->attributes->add(['staff' => $employee]);
        
        return $next($request);
    }
}
