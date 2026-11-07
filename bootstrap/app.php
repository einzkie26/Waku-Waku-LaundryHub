<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias(['staff.auth' => \App\Http\Middleware\StaffAuth::class]);
        $middleware->redirectGuestsTo(fn () => route('login.form'));
        $middleware->redirectUsersTo(fn () => auth()->user()?->role === 'admin' ? route('admin.dashboard') : route('landing'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
