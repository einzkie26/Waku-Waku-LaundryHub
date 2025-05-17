<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use PDF; // Import the PDF facade

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Get the period from the request (week or month)
        $period = $request->input('period', 'week');  // Default to 'week' if not provided

        // Fetch data from the database based on the period
        if ($period == 'week') {
            $salesData = DB::table('sales')
                ->where('date', '>=', now()->subDays(7))
                ->orderBy('date')
                ->get(['date', 'revenue']);
        } else {
            $salesData = DB::table('sales')
                ->where('date', '>=', now()->subDays(30))
                ->orderBy('date')
                ->get(['date', 'revenue']);
        }

        $lineChartLabels = $salesData->pluck('date')->toArray();
        $lineChartData = $salesData->pluck('revenue')->toArray();

        // Prepare Pie Chart Data (assuming you have this logic as well)
        $pieChartData = DB::table('sales')
            ->select('service_type', DB::raw('SUM(revenue) as total_revenue'))
            ->groupBy('service_type')
            ->get();

        $pieChartLabels = $pieChartData->pluck('service_type')->toArray();
        $pieChartData = $pieChartData->pluck('total_revenue')->toArray();

        // Pass data to the view
        return view('admin.reports.daily-revenue', [
            'lineChartLabels' => $lineChartLabels,
            'lineChartData' => $lineChartData,
            'pieChartLabels' => $pieChartLabels,
            'pieChartData' => $pieChartData,
        ]);
    }

    // Download the report as a PDF
    public function download(Request $request)
    {
        // Fetch the same data as in the index method
        $period = $request->input('period', 'week');  // Default to 'week' if not provided

        if ($period == 'week') {
            $salesData = DB::table('sales')
                ->where('date', '>=', now()->subDays(7))
                ->orderBy('date')
                ->get(['date', 'revenue']);
        } else {
            $salesData = DB::table('sales')
                ->where('date', '>=', now()->subDays(30))
                ->orderBy('date')
                ->get(['date', 'revenue']);
        }

        $lineChartLabels = $salesData->pluck('date')->toArray();
        $lineChartData = $salesData->pluck('revenue')->toArray();

        // Prepare the PDF data
        $data = [
            'lineChartLabels' => $lineChartLabels,
            'lineChartData' => $lineChartData,
            'totalRevenue' => array_sum($lineChartData),
        ];

        // Load the view into the PDF
        $pdf = PDF::loadView('admin.reports.pdf', $data);

        // Download the PDF with a specific filename
        return $pdf->download('daily_revenue_report.pdf');
    }
}


