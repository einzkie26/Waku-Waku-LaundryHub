<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;

use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    //
    public function index()
{
    $dailyRevenue = Order::whereDate('created_at', Carbon::today())->sum('total_price');
    $monthlyRevenue = Order::whereMonth('created_at', Carbon::now()->month)->sum('total_price');
    $totalOrders = Order::count();
    $pendingOrders = Order::where('status', 'Pending')->count();
    $activeCustomers = User::has('orders')->count();

    $recentOrders = Order::latest()->take(6)->get();
    $topServices = Order::select('service', \DB::raw('count(*) as orders'), \DB::raw('sum(total_price) as revenue'))
                        ->groupBy('service')
                        ->orderByDesc('orders')
                        ->take(6)
                        ->get();

    return view('admin.dashboard', compact(
        'dailyRevenue',
        'monthlyRevenue',
        'totalOrders',
        'pendingOrders',
        'activeCustomers',
        'recentOrders',
        'topServices'
    ));
}
}
