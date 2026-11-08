<?php

namespace App\Services;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class AdminReportService
{
    public const AMOUNT = '(COALESCE(orders.total_price, 0) + COALESCE(orders.additional_charges, 0))';
    public const PAYMENT_DATE = 'COALESCE(orders.paid_at, orders.created_at)';

    public function dates(string $period): array
    {
        $days = $period === 'month' ? 30 : 7;
        return [now()->subDays($days - 1)->startOfDay(), now()->endOfDay(), $days];
    }

    public function paidOrders(Carbon $start, Carbon $end): Builder
    {
        return Order::where('payment_status', 'paid')
            ->where('status', '!=', 'Refunded')
            ->whereRaw(self::PAYMENT_DATE.' BETWEEN ? AND ?', [$start->toDateTimeString(), $end->toDateTimeString()]);
    }

    public function revenue(string $period): array
    {
        [$startDate, $endDate, $days] = $this->dates($period);
        $query = $this->paidOrders($startDate, $endDate);
        $grouped = (clone $query)
            ->selectRaw('DATE('.self::PAYMENT_DATE.') as day, SUM('.self::AMOUNT.') as revenue, COUNT(*) as orders')
            ->groupBy('day')->orderBy('day')->toBase()->get()->keyBy('day');

        $series = collect();
        for ($index = 0; $index < $days; $index++) {
            $date = $startDate->copy()->addDays($index);
            $row = $grouped->get($date->toDateString());
            $series->push([
                'date' => $date->toDateString(), 'label' => $date->format('M j'),
                'revenue' => (float) ($row?->revenue ?? 0), 'orders' => (int) ($row?->orders ?? 0),
            ]);
        }

        $totalRevenue = $series->sum('revenue');
        $totalPaidOrders = $series->sum('orders');
        $serviceRevenue = (clone $query)->select('service as name')
            ->selectRaw('COUNT(*) as orders, SUM('.self::AMOUNT.') as revenue')
            ->groupBy('service')->orderByDesc('revenue')->toBase()->get();
        $paymentMethods = (clone $query)->select('payment_method')
            ->selectRaw('COUNT(*) as orders, SUM('.self::AMOUNT.') as revenue')
            ->groupBy('payment_method')->orderByDesc('revenue')->toBase()->get();
        $topCustomers = (clone $query)->with('user')->select('user_id')
            ->selectRaw('COUNT(*) as orders, SUM('.self::AMOUNT.') as total_spent')
            ->groupBy('user_id')->orderByDesc('total_spent')->limit(5)->get();

        return compact('period', 'startDate', 'endDate', 'days', 'series', 'totalRevenue', 'totalPaidOrders', 'serviceRevenue', 'paymentMethods', 'topCustomers') + [
            'averagePayment' => $totalPaidOrders ? $totalRevenue / $totalPaidOrders : 0,
            'averageDaily' => $totalRevenue / $days,
        ];
    }
}
