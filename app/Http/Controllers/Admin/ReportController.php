<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private function data(Request $request, AdminReportService $reports): array
    {
        $request->validate(['period' => ['nullable', 'in:week,month']]);
        return $reports->revenue($request->query('period', 'week'));
    }

    public function dailyRevenue(Request $request, AdminReportService $reports)
    {
        $report = $this->data($request, $reports);
        $transactions = $reports->paidOrders($report['startDate'], $report['endDate'])
            ->with('user')->orderByRaw(AdminReportService::PAYMENT_DATE.' DESC')->orderByDesc('id')
            ->paginate(15)->withQueryString();

        return view('admin.reports.daily-revenue', compact('report', 'transactions'));
    }

    public function downloadReport(Request $request, AdminReportService $reports)
    {
        $report = $this->data($request, $reports);
        return Pdf::loadView('admin.reports.pdf.daily-revenue', compact('report'))
            ->setPaper('a4')->setOptions(['isRemoteEnabled' => false, 'defaultFont' => 'DejaVu Sans'])
            ->download('waku-waku-revenue-'.now()->format('Y-m-d').'.pdf');
    }
}
