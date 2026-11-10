<?php

namespace App\Providers;

use App\Http\Middleware\StaffAuth;
use App\Models\Employee;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \App\Models\Order::observe(\App\Observers\LaundryOrderObserver::class);
        RateLimiter::for('laundry-assistant', fn ($request) => [
            Limit::perMinute(12)->by('assistant:'.$request->ip()),
            Limit::perDay(150)->by('assistant-day:'.$request->ip()),
        ]);
        RateLimiter::for('laundry-bixby', fn ($request) => [
            Limit::perMinute(60)->by('bixby:'.$request->ip()),
            Limit::perDay(2000)->by('bixby-day:'.$request->ip()),
        ]);
        \Illuminate\Support\Facades\Blade::anonymousComponentPath(resource_path('views/admin/components'));
        //
        Route::middlewareGroup('staff.auth', [StaffAuth::class]);
        View::composer('staff.*', function ($view) {
            $employee = null;
            if (Session::has('staff_id')) {
                $employee = Employee::find(Session::get('staff_id'));
            }
            $view->with('employee', $employee);
        });
    }
}
