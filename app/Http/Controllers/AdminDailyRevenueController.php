<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PDF;
use Exception;

class AdminDailyRevenueController extends Controller
{
    public function dailyRevenue(Request $request)
    {
        try {
            $period = $request->query('period', 'week');
            $startDate = $period === 'month' ? Carbon::now()->subDays(30) : Carbon::now()->subDays(7);

            $orders = Order::selectRaw('DATE(created_at) as date, COUNT(*) as total_orders, SUM(total_price) as revenue')
                ->where('created_at', '>=', $startDate)
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date')
                ->get();

            $services = Order::select('service', DB::raw('SUM(total_price) as total'))
                ->where('created_at', '>=', $startDate)
                ->groupBy('service')
                ->orderByDesc('total')
                ->get();

            $lineChartLabels = $orders->pluck('date')->map(fn($d) => Carbon::parse($d)->format('M d'))->toArray();
            $lineChartData = $orders->pluck('revenue')->toArray();

            $pieChartLabels = $services->pluck('service')->toArray();
            $pieChartData = $services->pluck('total')->toArray();

            return view('admin.reports.daily-revenue', [
                'orders' => $orders,
                'lineChartLabels' => $lineChartLabels,
                'lineChartData' => $lineChartData,
                'pieChartLabels' => $pieChartLabels,
                'pieChartData' => $pieChartData,
                'period' => $period
            ]);
        } catch (Exception $e) {
            return back()->with('error', 'Error loading revenue data: ' . $e->getMessage());
        }
    }

    public function download(Request $request)
    {
        try {
            $period = $request->query('period', 'week');
            $startDate = $period === 'month' ? Carbon::now()->subDays(30) : Carbon::now()->subDays(7);

            $orders = Order::selectRaw('DATE(created_at) as date, COUNT(*) as total_orders, SUM(total_price) as revenue')
                ->where('created_at', '>=', $startDate)
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date')
                ->get();

            $services = Order::select('service', DB::raw('SUM(total_price) as total'))
                ->where('created_at', '>=', $startDate)
                ->groupBy('service')
                ->orderByDesc('total')
                ->get();

            $lineChartLabels = $orders->pluck('date')->map(fn($d) => Carbon::parse($d)->format('M d'))->toArray();
            $lineChartData = $orders->pluck('revenue')->toArray();

            $pieChartLabels = $services->pluck('service')->toArray();
            $pieChartData = $services->pluck('total')->toArray();

            $pdf = PDF::loadView('admin.reports.pdf.daily-revenue', [
                'lineChartLabels' => $lineChartLabels,
                'lineChartData' => $lineChartData,
                'pieChartLabels' => $pieChartLabels,
                'pieChartData' => $pieChartData,
                'period' => $period
            ]);

            $pdf->setPaper('a4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
                'dpi' => 150,
                'debugCss' => false
            ]);

            $periodText = $period === 'month' ? '30days' : '7days';
            $filename = 'daily_revenue_report_' . $periodText . '_' . now()->format('Y-m-d') . '.pdf';
            return $pdf->download($filename);
        } catch (Exception $e) {
            return back()->with('error', 'Error generating PDF: ' . $e->getMessage());
        }
    }
} 