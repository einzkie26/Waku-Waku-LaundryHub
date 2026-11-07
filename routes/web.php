<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Controllers
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserLogController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CustomerBookingController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\StaffLoginController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\RevenueReportController;
use App\Http\Controllers\AdminAttendanceReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Staff\StaffProfileController;
use App\Http\Controllers\AdminDailyRevenueController;
use App\Http\Controllers\Admin\ReportController;

// Middleware
use App\Http\Middleware\StaffAuth;
use App\Http\Middleware\AdminMiddleware;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Temporary route to check employees - REMOVE AFTER USE
Route::get('/check-employees', function() {
    $employees = \App\Models\Employee::all();
    return $employees;
});

// Landing Page
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Authentication Routes
Route::middleware('guest')->group(function () {
    // Customer Authentication
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login.form');
    Route::post('/login', [LoginController::class, 'login'])->name('login');
    
    Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register.form');
    Route::post('/register', [RegisterController::class, 'register'])->name('register');

    // Staff Authentication
    Route::get('/staff/login', [StaffLoginController::class, 'showLoginForm'])->name('staff.login.form');
    Route::post('/staff/login', [StaffLoginController::class, 'login'])->name('staff.login');
});

// Order Tracking (Public)
Route::get('/order-tracking/{reference}', [OrderController::class, 'orderTracking'])
    ->name('order.tracking')
    ->middleware('auth');

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/booking', [CustomerBookingController::class, 'create'])->name('booking.create');
    Route::post('/booking', [CustomerBookingController::class, 'store'])->name('booking.store');
    // Logout
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

    // User Profile
    Route::get('/settings', function () {
        return view('settings');
    })->name('profile.settings');
    
    Route::put('/settings', [ProfileController::class, 'update'])->name('profile.update');

    // Notifications
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');
    Route::get('/notifications/count', [NotificationController::class, 'getCount'])->name('notifications.count');

    // Customer Routes
    Route::get('/customer/orders/history', [CustomerController::class, 'orderHistory'])->name('customer.orders.history');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth', AdminMiddleware::class])
    ->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

        // Orders
        Route::prefix('orders')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('orders.index');
            Route::get('/create', [OrderController::class, 'create'])->name('orders.create');
            Route::post('/', [OrderController::class, 'store'])->name('orders.store');
            Route::patch('/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
            Route::post('/{order}/pay', [OrderController::class, 'markAsPaid'])->name('orders.pay');
            Route::get('/{order}', [OrderController::class, 'show'])->name('orders.show');
            Route::post('/{order}/reschedule', [OrderController::class, 'reschedule'])->name('orders.reschedule');
            Route::post('/{order}/refund', [OrderController::class, 'refund'])->name('orders.refund');
        });

        // Customers
        Route::prefix('customers')->group(function () {
            Route::get('/', [CustomerController::class, 'index'])->name('admin.customers.index');
            Route::get('/list', [CustomerController::class, 'getCustomers'])->name('admin.customers.list');
            Route::get('/{id}/orders', [CustomerController::class, 'getCustomerOrders'])->name('admin.customers.orders');
            Route::get('/{id}/recent-orders', [CustomerController::class, 'getRecentOrders'])->name('admin.customers.recent-orders');
        });

        // Employees
        Route::prefix('employees')->group(function () {
            Route::get('/', [EmployeeController::class, 'index'])->name('employees.list');
            Route::get('/add', [EmployeeController::class, 'add'])->name('employees.add');
            Route::post('/', [EmployeeController::class, 'store'])->name('employees.store');
            Route::patch('/{employee}/activate', [EmployeeController::class, 'activate'])->name('employees.activate');
            Route::patch('/{employee}/deactivate', [EmployeeController::class, 'deactivate'])->name('employees.deactivate');
            Route::get('/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
        });

        // Reports
        Route::prefix('reports')->group(function () {
            Route::get('/revenue-report', [ReportController::class, 'dailyRevenue'])->name('reports.revenue-report');
            Route::get('/revenue-report/download', [ReportController::class, 'downloadReport'])->name('reports.download');
            Route::get('/service-stats', [OrderController::class, 'serviceStats'])->name('reports.service-stats');
            Route::get('/attendance', [AdminAttendanceReportController::class, 'index'])->name('reports.attendance');
            Route::get('/attendance/filter', [AdminAttendanceReportController::class, 'filterByMonth'])->name('reports.attendance.filter');
        });

        // Settings
        Route::get('/settings/profile', function () {
            return view('admin.settings.profile');
        })->name('settings.profile');
    });

/*
|--------------------------------------------------------------------------
| Staff Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['staff.auth'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [StaffLoginController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [StaffProfileController::class, 'show'])->name('profile');
    Route::post('/profile/update', [StaffProfileController::class, 'update'])->name('profile.update');
    Route::get('/attendance', [StaffLoginController::class, 'attendance'])->name('attendance');
    Route::post('/attendance/mark', [StaffLoginController::class, 'markAttendance'])->name('attendance.mark');
    Route::get('/history', [StaffLoginController::class, 'history'])->name('history');
    Route::get('/tasks', fn () => redirect()->route('staff.orders.index'))->name('tasks');
    Route::post('/tasks', [StaffLoginController::class, 'storeOrder'])->name('tasks.store');
    Route::get('/tasks/create', fn () => redirect()->route('staff.orders.create'))->name('tasks.create');
    Route::get('/store/{order}', [StaffLoginController::class, 'showStore'])->name('store');
    Route::get('/reports', [StaffLoginController::class, 'reports'])->name('reports');
    Route::get('/reports/daily', [StaffLoginController::class, 'dailyReport'])->name('reports.daily');
    Route::get('/reports/monthly', [StaffLoginController::class, 'monthlyReport'])->name('reports.monthly');
    Route::get('/reports/performance', [StaffLoginController::class, 'performanceReport'])->name('reports.performance');
    Route::get('/dashboard/data', [StaffLoginController::class, 'getDashboardData'])->name('dashboard.data');
    Route::post('/logout', [StaffLoginController::class, 'logout'])->name('logout');
    
    // Customer routes
    Route::get('/customers', [StaffLoginController::class, 'customers'])->name('customers');
    Route::get('/customers/create', [StaffLoginController::class, 'createCustomer'])->name('customers.create');
    Route::post('/customers', [StaffLoginController::class, 'storeCustomer'])->name('customers.store');
    Route::get('/customers/{customer}', [StaffLoginController::class, 'showCustomer'])->name('customers.show');
    Route::get('/customers/{customer}/edit', [StaffLoginController::class, 'editCustomer'])->name('customers.edit');
    Route::put('/customers/{customer}', [StaffLoginController::class, 'updateCustomer'])->name('customers.update');
    
    // Order routes
    Route::get('/orders', [StaffLoginController::class, 'orders'])->name('orders.index');
    Route::get('/orders/create', [StaffLoginController::class, 'createOrder'])->name('orders.create');
    Route::post('/orders', [StaffLoginController::class, 'storeOrder'])->name('orders.store');
    Route::get('/orders/{order}', [StaffLoginController::class, 'showOrder'])->name('orders.show');
    Route::get('/orders/{order}/edit', [StaffLoginController::class, 'editOrder'])->name('orders.edit');
    Route::put('/orders/{order}', [StaffLoginController::class, 'updateOrder'])->name('orders.update');
    Route::delete('/orders/{order}', [StaffLoginController::class, 'deleteOrder'])->name('orders.delete');
    Route::post('/orders/{order}/status', [StaffLoginController::class, 'updateOrderStatus'])->name('orders.status');
    Route::post('/orders/{order}/pay', [StaffLoginController::class, 'markAsPaid'])->name('orders.pay');
});

Route::get('/receipt/{order}', [App\Http\Controllers\OrderController::class, 'showReceipt'])->name('receipt.show');

// Admin Customer Routes
Route::middleware(['auth', AdminMiddleware::class])->group(function () {
    Route::get('/admin/customers', [CustomerController::class, 'index'])->name('admin.customers.index');
    Route::get('/admin/customers/{id}', [CustomerController::class, 'adminShow'])->name('admin.customers.show');
});
