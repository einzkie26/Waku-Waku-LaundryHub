<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Services\AdminReportService;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request, AdminReportService $reports)
    {
        $request->validate(['period' => ['nullable', 'in:week,month']]);
        $period = $request->query('period', 'week');
        $report = $reports->revenue($period);
        $metrics = [
            'collectedToday' => (float) $reports->paidOrders(today(), now()->endOfDay())->sum(\Illuminate\Support\Facades\DB::raw(AdminReportService::AMOUNT)),
            'activeOrders' => Order::whereNotIn('status', ['Completed', 'Refunded'])->count(),
            'readyOrders' => Order::where('status', 'Ready')->count(),
            'customers' => User::where('role', 'customer')->count(),
        ];
        $recentOrders = Order::with('user')->latest()->take(6)->get();
        $serviceVolumes = Order::where('status', '!=', 'Refunded')
            ->whereBetween('created_at', [$report['startDate'], $report['endDate']])
            ->selectRaw('service, COUNT(*) as orders')->groupBy('service')->orderByDesc('orders')->get();

        return view('admin.dashboard', compact('metrics', 'recentOrders', 'serviceVolumes', 'report', 'period'));
    }
}
