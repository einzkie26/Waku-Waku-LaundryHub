<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\StaffAuth;
use Illuminate\Support\Facades\View;
use App\Models\Employee;
use Illuminate\Support\Facades\Session;

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
