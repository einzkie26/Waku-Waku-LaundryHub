<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Attendance;
use App\Http\Middleware\StaffAuth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Events\OrderStatusUpdated;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\OrderStatusHistory;
use App\Models\UserLog;
use App\Notifications\OrderCompleted;

class StaffLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('staff.login');
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            Log::info('Staff login attempt', ['email' => $request->email]);

            $employee = Employee::where('email', $request->email)->first();

            if (!$employee || !Hash::check($request->password, $employee->password)) {
                Log::warning('Failed staff login attempt', [
                    'email' => $request->email,
                    'reason' => 'Invalid credentials'
                ]);
                return back()
                    ->withInput()
                    ->withErrors([
                        'email' => 'Invalid email or password.',
                    ]);
            }

            if ($employee->status !== 'Active') {
                Log::warning('Inactive staff login attempt', [
                    'employee_id' => $employee->id,
                    'name' => $employee->name,
                    'status' => $employee->status
                ]);
                return back()
                    ->withInput()
                    ->with('error', 'Your account is inactive. Please contact the administrator.');
            }

            // Successful login
            Session::forget('staff_id');
            Session::put('staff_id', $employee->id);
            
            if ($request->filled('remember')) {
                Session::put('remember_staff', true);
            }

            Log::info('Staff login successful', [
                'employee_id' => $employee->id,
                'name' => $employee->name
            ]);
            return redirect()->route('staff.dashboard')
                           ->with('success', 'Welcome back, ' . $employee->name);
        } catch (\Exception $e) {
            Log::error('Staff login error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'email' => 'An error occurred during login. Please try again.',
                ]);
        }
    }

    public function dashboard()
    {
        $staffId = Session::get('staff_id');
        if (!$staffId) {
            return redirect()->route('staff.login.form')
                           ->with('error', 'Please login to access the dashboard.');
        }

        $employee = Employee::findOrFail($staffId);
        
        $todayTasks = Order::whereDate('created_at', Carbon::today())
                          ->where('status', '!=', 'Completed')
                          ->count();

        $completedTasks = Order::whereHas('statusHistories', function($query) use ($staffId) {
            $query->where('changed_by_id', $staffId)
                  ->where('changed_by_type', 'staff')
                  ->where('status', 'Completed')
                  ->whereDate('created_at', Carbon::today());
        })->count();

        $todayAttendance = Attendance::where('employee_id', $staffId)
                                   ->whereDate('date', Carbon::today())
                                   ->first();
                                   
        $attendanceStatus = [
            'status' => $todayAttendance ? 'checked_in' : 'not_checked_in',
            'time' => $todayAttendance ? Carbon::parse($todayAttendance->check_in)->format('h:i A') : null
        ];

        $workHours = Attendance::where('employee_id', $staffId)
                             ->whereDate('date', Carbon::today())
                             ->whereNotNull('check_out')
                             ->get()
                             ->sum(function($record) {
                                 return Carbon::parse($record->check_out)
                                             ->diffInHours(Carbon::parse($record->check_in));
                             });

        $recentOrders = Order::whereHas('statusHistories', function($query) use ($staffId) {
            $query->where('changed_by_id', $staffId)
                  ->where('changed_by_type', 'staff');
        })
        ->with('user')
        ->latest()
        ->take(5)
        ->get()
        ->map(function($order) {
            return (object)[
                'reference' => $order->reference_no,
                'customer_name' => $order->user ? $order->user->name : 'N/A',
                'status' => $order->status,
                'status_color' => $this->getStatusColor($order->status),
                'due_date' => Carbon::parse($order->date),
                'created_at' => $order->created_at,
                'items_count' => 1
            ];
        });

        $attendanceHistory = Attendance::where('employee_id', $staffId)
                                     ->latest()
                                     ->take(5)
                                     ->get()
                                     ->map(function($record) {
                                         return (object)[
                                             'date' => Carbon::parse($record->date),
                                             'check_in' => Carbon::parse($record->check_in)->format('h:i A'),
                                             'check_out' => $record->check_out ? Carbon::parse($record->check_out)->format('h:i A') : null,
                                             'status' => $record->status,
                                             'status_color' => $this->getAttendanceStatusColor($record->status)
                                         ];
                                     });

        return view('staff.dashboard', compact(
            'employee',
            'todayTasks',
            'completedTasks',
            'attendanceStatus',
            'workHours',
            'recentOrders',
            'attendanceHistory'
        ));
    }

    public function tasks()
    {
        $staffId = Session::get('staff_id');
        if (!$staffId) {
            return redirect()->route('staff.login.form')
                           ->with('error', 'Please login to access tasks.');
        }

        $employee = Employee::findOrFail($staffId);
        $orders = Order::with('user')
                     ->where('status', 'Processing')
                     ->latest()
                     ->get();
        $users = \App\Models\User::where('role', 'customer')->get();
        return view('staff.task', compact('employee', 'orders', 'users'));
    }

    public function storeTask(Request $request)
    {
        \Log::info('Staff ID in session:', ['staff_id' => Session::get('staff_id')]);
        $staffId = Session::get('staff_id');
        if (!$staffId) {
            \Log::warning('No staff_id in session!');
            return redirect()->route('staff.login.form')->with('error', 'Unauthorized');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'service' => 'required|string',
            'laundry_style' => 'required|string',
            'fabric_type' => 'required|string',
            'weight' => 'required|numeric|min:0.1',
            'date' => 'required|date',
            'time' => 'required|string',
            'special_instructions' => 'nullable|string',
            'additional_services' => 'nullable|array',
            'payment_status' => 'nullable|in:pending,paid',
            'payment_method' => 'nullable|string',
            'payment_reference' => 'nullable|string',
        ]);
        \Log::info('Validation passed', $validated);

        $priceList = [
            'Wash & Fold' => 50,
            'Dry Cleaning' => 70,
            'Express Service' => 100,
            'Ironing' => 40,
        ];

        $pricePerKg = $priceList[$request->service] ?? 0;
        $basePrice = $pricePerKg * $request->weight;

        // Calculate additional charges
        $additionalCharges = 0;
        $additionalServices = [];
        if ($request->has('additional_services')) {
            $servicePrices = [
                'Starch' => 20,
                'Fabric Softener' => 15,
                'Express Processing' => 50
            ];
            
            foreach ($request->additional_services as $service) {
                if (isset($servicePrices[$service])) {
                    $additionalCharges += $servicePrices[$service];
                    $additionalServices[] = $service;
                }
            }
        }

        // Generate a unique reference number
        do {
            $referenceNo = 'REF-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 5));
        } while (Order::where('reference_no', $referenceNo)->exists());

        $order = Order::create([
            'user_id' => $request->user_id,
            'service' => $request->service,
            'laundry_style' => $request->laundry_style,
            'fabric_type' => $request->fabric_type,
            'special_instructions' => $request->special_instructions,
            'additional_services' => $additionalServices,
            'additional_charges' => $additionalCharges,
            'weight' => $request->weight,
            'date' => $request->date,
            'time' => $request->time,
            'total_price' => $basePrice,
            'total_amount' => $basePrice + $additionalCharges,
            'status' => 'Pending',
            'reference_no' => $referenceNo,
            'payment_status' => $request->payment_status ?? 'pending',
            'payment_method' => $request->payment_method,
            'payment_reference' => $request->payment_reference,
            'paid_at' => ($request->payment_status === 'paid') ? now() : null,
            'operator_type' => 'staff',
            'operator_id' => $staffId,
        ]);
        \Log::info('Order created', ['order_id' => $order->id]);

        // Log order creation in order_status_histories
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => 'Created',
            'changed_by_type' => 'staff',
            'changed_by_id' => $staffId,
        ]);

        // Create notification for the customer
        Notification::create([
            'title' => 'New Order Created',
            'message' => "Your order #{$referenceNo} has been created successfully.",
            'type' => 'order_created',
            'action_url' => route('order.tracking', $referenceNo),
            'notifiable_id' => $request->user_id,
            'notifiable_type' => User::class,
        ]);

        return redirect()->route('staff.store', ['order' => $order->id])->with('success', 'Order created successfully!');
    }

    private function getStatusColor($status)
    {
        return match($status) {
            'Pending' => 'warning',
            'Processing' => 'info',
            'Completed' => 'success',
            'Cancelled' => 'danger',
            default => 'secondary'
        };
    }

    private function getAttendanceStatusColor($status)
    {
        return match($status) {
            'Present' => 'success',
            'Late' => 'warning',
            'Absent' => 'danger',
            default => 'secondary'
        };
    }

    public function profile()
    {
        $staffId = Session::get('staff_id');
        if (!$staffId) {
            return redirect()->route('staff.login.form')
                           ->with('error', 'Please login to view profile.');
        }

        $employee = Employee::findOrFail($staffId);
        return view('staff.profile', compact('employee'));
    }

    public function attendance()
    {
        $staffId = Session::get('staff_id');
        if (!$staffId) {
            return redirect()->route('staff.login.form')
                           ->with('error', 'Please login to access attendance.');
        }

        $employee = Employee::findOrFail($staffId);
        $todayAttendance = Attendance::where('employee_id', $staffId)
                                   ->whereDate('date', Carbon::today())
                                   ->first();

        $attendanceHistory = Attendance::where('employee_id', $staffId)
                                     ->latest()
                                     ->take(10)
                                     ->get();

        return view('staff.attendance', compact('employee', 'todayAttendance', 'attendanceHistory'));
    }

    public function markAttendance(Request $request)
    {
        $staffId = Session::get('staff_id');
        if (!$staffId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $employee = Employee::findOrFail($staffId);
        $now = Carbon::now();
        $type = $request->input('type');

        try {
            if ($type === 'check-in') {
                $existingAttendance = Attendance::where('employee_id', $employee->id)
                    ->whereDate('date', $now->toDateString())
                    ->first();

                if ($existingAttendance) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Already checked in for today'
                    ], 400);
                }

                $shift = $request->input('shift') ?? $employee->shift ?? 'Morning';
                $shiftKey = strtolower($shift);
                $shiftStartTimes = [
                    'morning' => ['hour' => 6,  'minute' => 0],   // 6:00 AM
                    'afternoon' => ['hour' => 14, 'minute' => 0], // 2:00 PM
                    'night' => ['hour' => 22, 'minute' => 0],     // 10:00 PM
                ];
                $graceMinutes = 15;
                $start = $shiftStartTimes[$shiftKey] ?? ['hour' => 6, 'minute' => 0];
                $shiftStart = Carbon::today()->setHour($start['hour'])->setMinute($start['minute']);
                if ($shiftKey === 'night' && $now->hour < 12) {
                    $shiftStart->subDay();
                }
                $lateTime = $shiftStart->copy()->addMinutes($graceMinutes);
                $isLate = $now->greaterThan($lateTime);

                $attendance = Attendance::create([
                    'employee_id' => $employee->id,
                    'shift' => $shift,
                    'date' => $now->toDateString(),
                    'check_in' => $now,
                    'status' => $isLate ? 'Late' : 'Present'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Check-in recorded successfully',
                    'attendance' => $attendance
                ]);
            } else if ($type === 'check-out') {
                $attendance = Attendance::where('employee_id', $employee->id)
                    ->whereDate('date', $now->toDateString())
                    ->first();

                if (!$attendance) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No check-in record found for today'
                    ], 400);
                }

                if ($attendance->check_out) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Already checked out for today'
                    ], 400);
                }

                $attendance->update([
                    'check_out' => $now
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Check-out recorded successfully',
                    'attendance' => $attendance
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid attendance type'
            ], 400);

        } catch (\Exception $e) {
            \Log::error('Attendance Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error recording attendance: ' . $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        Session::forget('staff_id');
        
        $request->session()->invalidate();
        
        $request->session()->regenerateToken();
        
        return redirect()->route('staff.login.form')
                        ->with('success', 'You have been successfully logged out.');
    }

    public function history()
    {
        $staffId = Session::get('staff_id');
        if (!$staffId) {
            return redirect()->route('staff.login.form')
                           ->with('error', 'Please login to view history.');
        }

        $employee = Employee::findOrFail($staffId);
        
        $attendanceHistory = Attendance::where('employee_id', $staffId)
                                    ->latest()
                                    ->take(10)
                                    ->get();

        // Get orders that the staff member has worked on
        $taskHistory = Order::whereHas('statusHistories', function($query) use ($staffId) {
            $query->where('changed_by_id', $staffId)
                  ->where('changed_by_type', 'staff');
        })
        ->with(['user', 'statusHistories' => function($query) use ($staffId) {
            $query->where('changed_by_id', $staffId)
                  ->where('changed_by_type', 'staff')
                  ->latest();
        }])
        ->latest()
        ->take(10)
        ->get();

        return view('staff.history', compact('employee', 'attendanceHistory', 'taskHistory'));
    }

    public function getDashboardData()
    {
        $staffId = Session::get('staff_id');
        if (!$staffId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $employee = Employee::findOrFail($staffId);
        
        $todayTasks = Order::whereDate('created_at', Carbon::today())
                          ->where('status', '!=', 'Completed')
                          ->count();

        $completedTasks = Order::where('status', 'Completed')
                             ->whereDate('updated_at', Carbon::today())
                             ->count();

        $todayAttendance = Attendance::where('employee_id', $staffId)
                                   ->whereDate('date', Carbon::today())
                                   ->first();
        $attendanceStatus = $todayAttendance ? $todayAttendance->status : 'Not Marked';

        $workHours = Attendance::where('employee_id', $staffId)
                             ->whereDate('date', Carbon::today())
                             ->whereNotNull('check_out')
                             ->get()
                             ->sum(function($record) {
                                 return Carbon::parse($record->check_out)
                                             ->diffInHours(Carbon::parse($record->check_in));
                             });

        $recentOrders = Order::with('user')
                           ->latest()
                           ->take(5)
                           ->get()
                           ->map(function($order) {
                               return (object)[
                                   'reference' => $order->reference_no,
                                   'customer_name' => $order->user ? $order->user->name : 'N/A',
                                   'status' => $order->status,
                                   'status_color' => $this->getStatusColor($order->status),
                                   'due_date' => Carbon::parse($order->date)->format('M d, Y')
                               ];
                           });

        $attendanceHistory = Attendance::where('employee_id', $staffId)
                                     ->latest()
                                     ->take(5)
                                     ->get()
                                     ->map(function($record) {
                                         return (object)[
                                             'date' => Carbon::parse($record->date)->format('M d, Y'),
                                             'check_in' => Carbon::parse($record->check_in)->format('h:i A'),
                                             'status' => $record->status,
                                             'status_color' => $this->getAttendanceStatusColor($record->status)
                                         ];
                                     });

        return response()->json([
            'todayTasks' => $todayTasks,
            'completedTasks' => $completedTasks,
            'attendanceStatus' => $attendanceStatus,
            'workHours' => $workHours,
            'recentOrders' => $recentOrders,
            'attendanceHistory' => $attendanceHistory
        ]);
    }

    public function createTask()
    {
        $users = \App\Models\User::where('role', 'customer')->get();
        $bookedDates = \App\Models\Order::pluck('date')->toArray();
        return view('staff.task-create', compact('users', 'bookedDates'));
    }

    public function showStore($orderId)
    {
        $staffId = Session::get('staff_id');
        if (!$staffId) {
            return redirect()->route('staff.login.form')
                           ->with('error', 'Please login to view order details.');
        }

        $order = Order::with('user')->findOrFail($orderId);
        return view('staff.store', compact('order'));
    }

    public function orders()
    {
        $staffId = Session::get('staff_id');
        if (!$staffId) {
            return redirect()->route('staff.login.form')
                           ->with('error', 'Please login to view orders.');
        }

        $employee = Employee::findOrFail($staffId);
        
        $orders = Order::with('user')
            ->where('operator_id', $employee->id)
            ->where('operator_type', 'staff')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                return (object)[
                    'id' => $order->id,
                    'reference_no' => $order->reference_no,
                    'customer_name' => $order->user ? $order->user->name : 'N/A',
                    'service' => $order->service,
                    'weight' => $order->weight,
                    'total_price' => $order->total_price,
                    'status' => $order->status,
                    'pickup_date' => $order->date
                ];
            });

        return view('staff.orders.index', compact('employee', 'orders'));
    }

    public function showOrder($id)
    {
        $order = Order::with('user')->findOrFail($id);
        
        return view('staff.store', [
            'order' => (object)[
                'id' => $order->id,
                'reference_no' => $order->reference_no,
                'customer_name' => $order->user ? $order->user->name : 'N/A',
                'customer_phone' => $order->user ? $order->user->phone : 'N/A',
                'customer_address' => $order->user ? $order->user->address : 'N/A',
                'service' => $order->service,
                'weight' => $order->weight,
                'total_price' => $order->total_price,
                'status' => $order->status,
                'pickup_date' => $order->date,
                'pickup_time' => $order->time
            ]
        ]);
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $staffId = Session::get('staff_id');
        if (!$staffId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ]);
        }

        $order = Order::findOrFail($id);
        
        // Prevent status updates for refunded orders
        if ($order->status === 'Refunded') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update status of refunded orders'
            ]);
        }

        // Define status flows for different services
        $statusFlows = [
            'Wash & Fold' => ['Pending', 'Picked Up', 'Washing', 'Drying', 'Ready', 'Completed'],
            'Dry Cleaning' => ['Pending', 'Picked Up', 'Processing', 'Ready', 'Completed'],
            'Express Service' => ['Pending', 'Picked Up', 'Processing', 'Ready', 'Completed'],
            'Ironing' => ['Pending', 'Picked Up', 'Processing', 'Ready', 'Completed']
        ];

        $statusSteps = $statusFlows[$order->service] ?? $statusFlows['Wash & Fold'];
        $currentIndex = array_search($order->status, $statusSteps);
        
        // If current status is not found or is the last status, return error
        if ($currentIndex === false || $currentIndex === count($statusSteps) - 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot advance status further'
            ]);
        }

        // Get the next status
        $newStatus = $statusSteps[$currentIndex + 1];

        // Update the order status
        $order->status = $newStatus;
        $order->save();

        // Create status history entry
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => $newStatus,
            'changed_by_type' => 'staff',
            'changed_by_id' => $staffId
        ]);

        // If order is marked as completed, notify the user
        if ($newStatus === 'Completed') {
            $order->user->notify(new OrderCompleted($order));
        }

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully'
        ]);
    }

    public function reports()
    {
        $staffId = Session::get('staff_id');
        if (!$staffId) {
            return redirect()->route('staff.login.form')
                           ->with('error', 'Please login to view reports.');
        }

        $employee = Employee::findOrFail($staffId);
        
        $todayOrders = Order::where('operator_id', $staffId)
                          ->where('operator_type', 'staff')
                          ->whereDate('created_at', Carbon::today())
                          ->count();
                          
        $todayCompleted = Order::where('operator_id', $staffId)
                             ->where('operator_type', 'staff')
                             ->whereDate('updated_at', Carbon::today())
                             ->where('status', 'Completed')
                             ->count();
                             
        $todayRevenue = Order::where('operator_id', $staffId)
                           ->where('operator_type', 'staff')
                           ->whereDate('created_at', Carbon::today())
                           ->sum('total_price');

        return view('staff.reports.index', compact('employee', 'todayOrders', 'todayCompleted', 'todayRevenue'));
    }

    public function dailyReport()
    {
        $staffId = Session::get('staff_id');
        if (!$staffId) {
            return redirect()->route('staff.login.form')
                           ->with('error', 'Please login to view daily report.');
        }

        $employee = Employee::findOrFail($staffId);
        
        $last7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $last7Days[$date] = [
                'total_orders' => 0,
                'completed_orders' => 0,
                'revenue' => 0
            ];
        }

        $orders = Order::where('operator_id', $staffId)
                     ->where('operator_type', 'staff')
                     ->whereDate('created_at', '>=', Carbon::now()->subDays(7))
                     ->get();

        foreach ($orders as $order) {
            $date = $order->created_at->format('Y-m-d');
            if (isset($last7Days[$date])) {
                $last7Days[$date]['total_orders']++;
                if ($order->status === 'Completed') {
                    $last7Days[$date]['completed_orders']++;
                }
                $last7Days[$date]['revenue'] += $order->total_price;
            }
        }

        return view('staff.reports.daily', compact('employee', 'last7Days'));
    }

    public function monthlyReport()
    {
        $staffId = Session::get('staff_id');
        if (!$staffId) {
            return redirect()->route('staff.login.form')
                           ->with('error', 'Please login to view monthly report.');
        }

        $employee = Employee::findOrFail($staffId);
        
        $monthlyStats = Order::where('operator_id', $staffId)
                          ->where('operator_type', 'staff')
                          ->whereDate('created_at', '>=', Carbon::now()->subMonths(6))
                          ->get()
                          ->groupBy(function($order) {
                              return $order->created_at->format('Y-m');
                          })
                          ->map(function($orders) {
                              return [
                                  'total_orders' => $orders->count(),
                                  'completed_orders' => $orders->where('status', 'Completed')->count(),
                                  'revenue' => $orders->sum('total_price')
                              ];
                          });

        return view('staff.reports.monthly', compact('employee', 'monthlyStats'));
    }

    public function performanceReport()
    {
        $staffId = Session::get('staff_id');
        if (!$staffId) {
            return redirect()->route('staff.login.form')
                           ->with('error', 'Please login to view performance report.');
        }

        $employee = Employee::findOrFail($staffId);
        
        $totalOrders = Order::where('operator_id', $staffId)
                          ->where('operator_type', 'staff')
                          ->count();
        $completedOrders = Order::where('operator_id', $staffId)
                              ->where('operator_type', 'staff')
                              ->where('status', 'Completed')
                              ->count();
        $totalRevenue = Order::where('operator_id', $staffId)
                           ->where('operator_type', 'staff')
                           ->sum('total_price');
                           
        $avgCompletionTime = Order::where('operator_id', $staffId)
                                ->where('operator_type', 'staff')
                                ->where('status', 'Completed')
                                ->get()
                                ->avg(function($order) {
                                    return Carbon::parse($order->created_at)
                                               ->diffInHours(Carbon::parse($order->updated_at));
                                });

        $serviceDistribution = Order::where('operator_id', $staffId)
                                  ->where('operator_type', 'staff')
                                  ->get()
                                  ->groupBy('service')
                                  ->map(function($orders) {
                                      return $orders->count();
                                  });

        return view('staff.reports.performance', compact(
            'employee',
            'totalOrders',
            'completedOrders',
            'totalRevenue',
            'avgCompletionTime',
            'serviceDistribution'
        ));
    }

    public function notifications()
    {
        $staffId = Session::get('staff_id');
        if (!$staffId) {
            return redirect()->route('staff.login.form')
                           ->with('error', 'Please login to view notifications.');
        }

        $employee = Employee::findOrFail($staffId);
        
        $notifications = Notification::where('notifiable_id', $staffId)
                                  ->where('notifiable_type', 'App\Models\Employee')
                                  ->whereNull('read_at')
                                  ->latest()
                                  ->get()
                                  ->map(function($notification) {
                                      return (object)[
                                          'id' => $notification->id,
                                          'type' => $notification->type,
                                          'data' => $notification->data,
                                          'created_at' => Carbon::parse($notification->created_at)
                                      ];
                                  });

        $readNotifications = Notification::where('notifiable_id', $staffId)
                                      ->where('notifiable_type', 'App\Models\Employee')
                                      ->whereNotNull('read_at')
                                      ->where('created_at', '>=', Carbon::now()->subDays(30))
                                      ->latest()
                                      ->get()
                                      ->map(function($notification) {
                                          return (object)[
                                              'id' => $notification->id,
                                              'type' => $notification->type,
                                              'data' => $notification->data,
                                              'created_at' => Carbon::parse($notification->created_at),
                                              'read_at' => Carbon::parse($notification->read_at)
                                          ];
                                      });

        return view('staff.notifications', compact('employee', 'notifications', 'readNotifications'));
    }

    public function markNotificationAsRead($notificationId)
    {
        $staffId = Session::get('staff_id');
        if (!$staffId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $notification = Notification::where('notifiable_id', $staffId)
                                 ->where('notifiable_type', 'App\Models\Employee')
                                 ->where('id', $notificationId)
                                 ->first();

        if ($notification) {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
    }

    public function markAllNotificationsAsRead(Request $request)
    {
        $staffId = Session::get('staff_id');
        if (!$staffId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $notificationIds = $request->input('notification_ids', []);
        
        if (!empty($notificationIds)) {
            Notification::where('notifiable_id', $staffId)
                       ->where('notifiable_type', 'App\Models\Employee')
                       ->whereIn('id', $notificationIds)
                       ->update(['read_at' => now()]);
        } else {
            Notification::where('notifiable_id', $staffId)
                       ->where('notifiable_type', 'App\Models\Employee')
                       ->whereNull('read_at')
                       ->update(['read_at' => now()]);
        }

        return response()->json(['success' => true]);
    }

    public function createOrder()
    {
        if (!Session::has('staff_id')) {
            return redirect()->route('staff.login.form');
        }

        $employee = Employee::find(Session::get('staff_id'));
        if (!$employee) {
            return redirect()->route('staff.login.form');
        }

        $customers = User::where('role', 'customer')->get();

        return view('staff.orders.create', compact('employee', 'customers'));
    }

    public function storeOrder(Request $request)
    {
        if (!Session::has('staff_id')) {
            return redirect()->route('staff.login.form');
        }

        $employee = Employee::find(Session::get('staff_id'));
        if (!$employee) {
            return redirect()->route('staff.login.form');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'service' => 'required|string',
            'laundry_style' => 'required|string',
            'fabric_type' => 'required|string',
            'weight' => 'required|numeric|min:0.1',
            'date' => 'required|date',
            'time' => 'required|string',
            'special_instructions' => 'nullable|string',
            'additional_services' => 'nullable|array',
            'payment_status' => 'nullable|in:pending,paid',
            'payment_method' => 'nullable|string',
            'payment_reference' => 'nullable|string',
        ]);

        $priceList = [
            'Wash & Fold' => 50,
            'Dry Cleaning' => 70,
            'Express Service' => 100,
            'Ironing' => 40,
        ];

        $pricePerKg = $priceList[$request->service] ?? 0;
        $basePrice = $pricePerKg * $request->weight;

        // Calculate additional charges
        $additionalCharges = 0;
        $additionalServices = [];
        if ($request->has('additional_services')) {
            $servicePrices = [
                'Starch' => 20,
                'Fabric Softener' => 15,
                'Express Processing' => 50
            ];
            
            foreach ($request->additional_services as $service) {
                if (isset($servicePrices[$service])) {
                    $additionalCharges += $servicePrices[$service];
                    $additionalServices[] = $service;
                }
            }
        }

        // Generate a unique reference number
        do {
            $referenceNo = 'REF-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 5));
        } while (Order::where('reference_no', $referenceNo)->exists());

        $order = Order::create([
            'user_id' => $request->user_id,
            'service' => $request->service,
            'laundry_style' => $request->laundry_style,
            'fabric_type' => $request->fabric_type,
            'special_instructions' => $request->special_instructions,
            'additional_services' => $additionalServices,
            'additional_charges' => $additionalCharges,
            'weight' => $request->weight,
            'date' => $request->date,
            'time' => $request->time,
            'total_price' => $basePrice,
            'total_amount' => $basePrice + $additionalCharges,
            'status' => 'Pending',
            'reference_no' => $referenceNo,
            'payment_status' => $request->payment_status ?? 'pending',
            'payment_method' => $request->payment_method,
            'payment_reference' => $request->payment_reference,
            'paid_at' => ($request->payment_status === 'paid') ? now() : null,
            'operator_type' => 'staff',
            'operator_id' => $employee->id,
        ]);

        // Log order creation in order_status_histories
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => 'Created',
            'changed_by_type' => 'staff',
            'changed_by_id' => $employee->id,
        ]);

        // Create notification for the customer
        Notification::create([
            'title' => 'New Order Created',
            'message' => "Your order #{$referenceNo} has been created successfully.",
            'type' => 'order_created',
            'action_url' => route('order.tracking', $referenceNo),
            'notifiable_id' => $request->user_id,
            'notifiable_type' => User::class,
        ]);

        return redirect()->route('staff.store', $order->id)
            ->with('success', 'Order created successfully!');
    }

    public function markAsPaid(Request $request, $orderId)
    {
        $order = \App\Models\Order::findOrFail($orderId);
        $order->update([
            'payment_status' => 'paid',
            'payment_method' => $request->payment_method,
            'payment_reference' => $request->payment_reference,
            'paid_at' => now(),
        ]);
        return back()->with('success', 'Order marked as paid.');
    }
}

