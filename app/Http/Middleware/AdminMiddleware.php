<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle($request, Closure $next)
    {
        \Log::info('AdminMiddleware: Checking authentication', [
            'is_authenticated' => Auth::check(),
            'user' => Auth::check() ? Auth::user()->toArray() : null,
            'path' => $request->path()
        ]);

        if (!Auth::check()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Unauthorized.'], 401);
            }
            return redirect()->route('login.form')
                ->with('error', 'Please log in to access the admin portal.');
        }

        if (Auth::user()->role !== 'admin') {
            \Log::warning('AdminMiddleware: User is not an admin', [
                'user_id' => Auth::id(),
                'user_role' => Auth::user()->role
            ]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }
            return redirect()->route('landing')
                ->with('error', 'You do not have permission to access the admin portal.');
        }

        return $next($request);
    }
}

