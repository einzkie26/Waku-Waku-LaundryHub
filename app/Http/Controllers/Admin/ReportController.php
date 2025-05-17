<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Service;
use App\Models\Customer;
use Barryvdh\DomPDF\Facade\PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function dailyRevenue(Request $request)
    {
        try {
            \Log::info('ReportController: Accessing dailyRevenue', [
                'user' => Auth::user()->toArray(),
                'request_path' => $request->path(),
                'request_method' => $request->method(),
                'session_id' => session()->getId(),
                'session_data' => session()->all(),
                'auth_check' => Auth::check(),
                'auth_user' => Auth::user() ? [
                    'id' => Auth::id(),
                    'role' => Auth::user()->role,
                    'name' => Auth::user()->name
                ] : null
            ]);

            $period = $request->get('period', 'week');
            $days = $period === 'week' ? 7 : 30;
            $startDate = Carbon::now()->subDays($days - 1)->startOfDay();
            $endDate = Carbon::now()->endOfDay();

            // Get daily revenue data
            $dailyRevenue = Order::where('status', 'completed')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(total_amount) as revenue'),
                    DB::raw('COUNT(*) as orders')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Get service revenue data
            $serviceRevenue = Order::where('status', 'completed')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->join('services', 'order_items.service_id', '=', 'services.id')
                ->select(
                    'services.name',
                    DB::raw('SUM(order_items.total_price) as revenue'),
                    DB::raw('COUNT(DISTINCT orders.id) as orders')
                )
                ->groupBy('services.name')
                ->orderByDesc('revenue')
                ->get();

            // Get customer metrics
            $customerMetrics = Order::whereBetween('orders.created_at', [$startDate, $endDate])
                ->where('orders.status', 'completed')
                ->join('customers', 'orders.customer_id', '=', 'customers.id')
                ->selectRaw('
                    COUNT(DISTINCT customers.id) as total_customers,
                    COUNT(DISTINCT CASE WHEN customers.created_at >= DATE_SUB(orders.created_at, INTERVAL 30 DAY) THEN customers.id END) as new_customers,
                    COUNT(DISTINCT CASE WHEN customers.created_at < DATE_SUB(orders.created_at, INTERVAL 30 DAY) THEN customers.id END) as returning_customers,
                    AVG(orders.total_amount) as avg_customer_spend
                ')
                ->first();

            // Get payment method data
            $paymentMethodData = Order::where('status', 'completed')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    'payment_method',
                    DB::raw('SUM(total_amount) as revenue'),
                    DB::raw('COUNT(*) as orders')
                )
                ->groupBy('payment_method')
                ->orderByDesc('revenue')
                ->get();

            // Get top customers
            $topCustomers = Order::where('status', 'completed')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->join('customers', 'orders.customer_id', '=', 'customers.id')
                ->select(
                    'customers.name',
                    DB::raw('SUM(orders.total_amount) as total_spent'),
                    DB::raw('COUNT(DISTINCT orders.id) as orders')
                )
                ->groupBy('customers.id', 'customers.name')
                ->orderByDesc('total_spent')
                ->limit(5)
                ->get();

            // Calculate totals and averages
            $totalRevenue = $dailyRevenue->sum('revenue');
            $totalOrders = $dailyRevenue->sum('orders');
            $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
            $avgDailyRevenue = $dailyRevenue->count() > 0 ? $totalRevenue / $dailyRevenue->count() : 0;
            $customerRetentionRate = $customerMetrics->total_customers > 0
                ? ($customerMetrics->returning_customers / $customerMetrics->total_customers) * 100
                : 0;

            // Prepare data for charts
            $lineChartLabels = $dailyRevenue->pluck('date')->map(fn($date) => Carbon::parse($date)->format('M d'))->toArray();
            $lineChartData = $dailyRevenue->pluck('revenue')->toArray();
            $pieChartLabels = $serviceRevenue->pluck('name')->toArray();
            $pieChartData = $serviceRevenue->pluck('revenue')->toArray();

            // Prepare orders by date and service
            $ordersByDate = $dailyRevenue->pluck('orders', 'date')->toArray();
            $ordersByService = $serviceRevenue->pluck('orders', 'name')->toArray();

            return view('admin.reports.daily-revenue', compact(
                'lineChartLabels',
                'lineChartData',
                'pieChartLabels',
                'pieChartData',
                'totalRevenue',
                'totalOrders',
                'avgOrderValue',
                'avgDailyRevenue',
                'customerMetrics',
                'customerRetentionRate',
                'serviceRevenue',
                'paymentMethodData',
                'topCustomers',
                'ordersByDate',
                'ordersByService',
                'period',
                'startDate',
                'endDate'
            ));
        } catch (\Exception $e) {
            Log::error('Revenue Report Error: ' . $e->getMessage());
            return back()->with('error', 'Error loading revenue data: ' . $e->getMessage());
        }
    }

    public function downloadReport(Request $request)
    {
        try {
            $period = $request->get('period', 'week');
            $days = $period === 'week' ? 7 : 30;
            $startDate = Carbon::now()->subDays($days - 1)->startOfDay();
            $endDate = Carbon::now()->endOfDay();

            // Get daily revenue data
            $dailyRevenue = Order::where('status', 'completed')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(total_amount) as revenue'),
                    DB::raw('COUNT(*) as orders')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Get service revenue data
            $serviceRevenue = Order::where('status', 'completed')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->join('services', 'order_items.service_id', '=', 'services.id')
                ->select(
                    'services.name',
                    DB::raw('SUM(order_items.total_price) as revenue'),
                    DB::raw('COUNT(DISTINCT orders.id) as orders')
                )
                ->groupBy('services.name')
                ->orderByDesc('revenue')
                ->get();

            // Get customer metrics
            $customerMetrics = Order::whereBetween('orders.created_at', [$startDate, $endDate])
                ->where('orders.status', 'completed')
                ->join('customers', 'orders.customer_id', '=', 'customers.id')
                ->selectRaw('
                    COUNT(DISTINCT customers.id) as total_customers,
                    COUNT(DISTINCT CASE WHEN customers.created_at >= DATE_SUB(orders.created_at, INTERVAL 30 DAY) THEN customers.id END) as new_customers,
                    COUNT(DISTINCT CASE WHEN customers.created_at < DATE_SUB(orders.created_at, INTERVAL 30 DAY) THEN customers.id END) as returning_customers,
                    AVG(orders.total_amount) as avg_customer_spend
                ')
                ->first();

            // Get payment method data
            $paymentMethodData = Order::where('status', 'completed')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    'payment_method',
                    DB::raw('SUM(total_amount) as revenue'),
                    DB::raw('COUNT(*) as orders')
                )
                ->groupBy('payment_method')
                ->orderByDesc('revenue')
                ->get();

            // Get top customers
            $topCustomers = Order::where('status', 'completed')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->join('customers', 'orders.customer_id', '=', 'customers.id')
                ->select(
                    'customers.name',
                    DB::raw('SUM(orders.total_amount) as total_spent'),
                    DB::raw('COUNT(DISTINCT orders.id) as orders')
                )
                ->groupBy('customers.id', 'customers.name')
                ->orderByDesc('total_spent')
                ->limit(5)
                ->get();

            // Calculate totals and averages
            $totalRevenue = $dailyRevenue->sum('revenue');
            $totalOrders = $dailyRevenue->sum('orders');
            $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
            $avgDailyRevenue = $dailyRevenue->count() > 0 ? $totalRevenue / $dailyRevenue->count() : 0;
            $customerRetentionRate = $customerMetrics->total_customers > 0
                ? ($customerMetrics->returning_customers / $customerMetrics->total_customers) * 100
                : 0;

            // Prepare data for PDF
            $report = [
                'type' => $period,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_revenue' => $totalRevenue,
                'total_orders' => $totalOrders,
                'average_order_value' => $avgOrderValue,
                'average_daily_revenue' => $avgDailyRevenue,
                'total_customers' => $customerMetrics->total_customers,
                'new_customers' => $customerMetrics->new_customers,
                'returning_customers' => $customerMetrics->returning_customers,
                'customer_retention_rate' => $customerRetentionRate,
                'service_revenue' => $serviceRevenue,
                'payment_methods' => $paymentMethodData,
                'top_customers' => $topCustomers
            ];

            $viewData = [
                'report' => $report,
                'lineChartLabels' => $dailyRevenue->pluck('date')->map(fn($date) => Carbon::parse($date)->format('M d'))->toArray(),
                'lineChartData' => $dailyRevenue->pluck('revenue')->toArray(),
                'ordersByDate' => $dailyRevenue->pluck('orders', 'date')->toArray(),
                'serviceRevenue' => $serviceRevenue,
                'paymentMethods' => $paymentMethodData,
                'topCustomers' => $topCustomers,
                'period' => $period === 'week' ? 'Last 7 Days' : 'Last 30 Days'
            ];

            // Generate PDF
            $pdf = PDF::loadView('admin.reports.pdf.daily-revenue', $viewData);
            $pdf->setPaper('a4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
                'dpi' => 150
            ]);

            return $pdf->download('revenue_report_' . $period . '_' . now()->format('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            Log::error('PDF Generation Error: ' . $e->getMessage());
            return back()->with('error', 'Error generating PDF: ' . $e->getMessage());
        }
    }

    private function getReportData($startDate, $endDate)
    {
        // Get daily revenue data
        $dailyRevenue = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get service revenue data
        $serviceRevenue = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('services', 'order_items.service_id', '=', 'services.id')
            ->select(
                'services.name',
                DB::raw('SUM(order_items.total_price) as revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as orders')
            )
            ->groupBy('services.name')
            ->orderByDesc('revenue')
            ->get();

        // Get customer metrics
        $customerMetrics = Order::whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(DISTINCT CASE WHEN customers.created_at >= DATE_SUB(orders.created_at, INTERVAL 30 DAY) THEN customers.id END) as new_customers'),
                DB::raw('COUNT(DISTINCT CASE WHEN customers.created_at < DATE_SUB(orders.created_at, INTERVAL 30 DAY) THEN customers.id END) as returning_customers')
            )
            ->join('customers', 'orders.customer_id', '=', 'customers.id')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get peak hour data
        $peakHourData = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('hour')
            ->orderByDesc('revenue')
            ->first();

        // Get day of week data
        $dayOfWeekData = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DAYNAME(created_at) as day'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('day')
            ->orderBy('revenue', 'desc')
            ->get();

        // Get payment method data
        $paymentMethodData = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                'payment_method',
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('payment_method')
            ->orderByDesc('revenue')
            ->get();

        // Get top customers
        $topCustomers = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->join('customers', 'orders.customer_id', '=', 'customers.id')
            ->select(
                'customers.name',
                DB::raw('SUM(orders.total_amount) as total_spent'),
                DB::raw('COUNT(DISTINCT orders.id) as order_count')
            )
            ->groupBy('customers.id', 'customers.name')
            ->orderByDesc('total_spent')
            ->limit(5)
            ->get();

        // Get top services
        $topServices = Order::where('status', 'completed')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('services', 'order_items.service_id', '=', 'services.id')
            ->select(
                'services.name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.total_price) as total_revenue')
            )
            ->groupBy('services.id', 'services.name')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        // Get transactions for the period from the report data
        $transactions = \App\Models\Order::with('customer')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'dailyRevenue' => $dailyRevenue,
            'serviceRevenue' => $serviceRevenue,
            'customerMetrics' => $customerMetrics,
            'peakHourData' => $peakHourData,
            'dayOfWeekData' => $dayOfWeekData,
            'paymentMethodData' => $paymentMethodData,
            'topCustomers' => $topCustomers,
            'topServices' => $topServices,
            'transactions' => $transactions
        ];
    }
}
