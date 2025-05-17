<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Revenue Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #2c3e50;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #7f8c8d;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0 10px;
            color: #2c3e50;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .kpi-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #eee;
        }
        .kpi-card h4 {
            margin: 0 0 10px;
            font-size: 14px;
            color: #7f8c8d;
        }
        .kpi-card .value {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
        }
        .kpi-card .subtext {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 12px;
            color: #7f8c8d;
        }
    </style>
</head>
@php
    $period = $report['type'];
    $startDate = $report['start_date'];
    $endDate = $report['end_date'];
    $totalRevenue = $report['total_revenue'];
    $totalOrders = $report['total_orders'];
    $avgOrderValue = $report['average_order_value'];
    $avgDailyRevenue = $report['average_daily_revenue'];
    
    // Customer metrics from report
    $customerMetrics = [
        'total_customers' => $report['total_customers'],
        'new_customers' => $report['new_customers'],
        'returning_customers' => $report['returning_customers']
    ];
    $customerRetentionRate = $report['customer_retention_rate'];

    // Peak hour data from report
    $peakHourData = $report['peak_hours'];
@endphp

<body>
    <div class="header">
        <h1>Revenue Report</h1>
        <p>{{ $period === 'week' ? 'Last 7 Days' : 'Last 30 Days' }}</p>
        <p>Period: {{ $startDate->format('M d, Y') }} to {{ $endDate->format('M d, Y') }}</p>
    </div>

    <!-- Key Performance Indicators -->
    <div class="section-title">Key Performance Indicators</div>
    <div class="kpi-grid">
        <div class="kpi-card">
            <h4>Total Revenue</h4>
            <div class="value">₱{{ number_format($totalRevenue, 2) }}</div>
            <div class="subtext">Total revenue for period</div>
        </div>
        <div class="kpi-card">
            <h4>Total Orders</h4>
            <div class="value">{{ number_format($totalOrders) }}</div>
            <div class="subtext">Completed orders</div>
        </div>
        <div class="kpi-card">
            <h4>Average Order Value</h4>
            <div class="value">₱{{ number_format($avgOrderValue, 2) }}</div>
            <div class="subtext">Per order average</div>
        </div>
        <div class="kpi-card">
            <h4>Customer Retention</h4>
            <div class="value">{{ number_format($customerRetentionRate, 1) }}%</div>
            <div class="subtext">Returning customers</div>
        </div>
    </div>

    <!-- Daily Revenue -->
    <div class="section-title">Daily Revenue</div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th class="text-right">Orders</th>
                <th class="text-right">Revenue</th>
                <th class="text-right">Avg. Order Value</th>
                <th class="text-right">% of Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach(array_combine($lineChartLabels, $lineChartData) as $date => $revenue)
            <tr>
                <td>{{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</td>
                <td class="text-right">{{ $ordersByDate[$date] ?? 0 }}</td>
                <td class="text-right">₱{{ number_format($revenue, 2) }}</td>
                <td class="text-right">₱{{ number_format(($ordersByDate[$date] ?? 0) > 0 ? $revenue / ($ordersByDate[$date] ?? 0) : 0, 2) }}</td>
                <td class="text-right">{{ $totalRevenue > 0 ? number_format(($revenue / $totalRevenue) * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td><strong>Total</strong></td>
                <td class="text-right"><strong>{{ number_format($totalOrders) }}</strong></td>
                <td class="text-right"><strong>₱{{ number_format($totalRevenue, 2) }}</strong></td>
                <td class="text-right"><strong>₱{{ number_format($avgOrderValue, 2) }}</strong></td>
                <td class="text-right"><strong>100%</strong></td>
            </tr>
        </tbody>
    </table>

    <!-- Service Revenue Analysis -->
    <div class="section-title">Service Revenue Analysis</div>
    <table>
        <thead>
            <tr>
                <th>Service Type</th>
                <th class="text-right">Orders</th>
                <th class="text-right">Revenue</th>
                <th class="text-right">% of Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($serviceRevenue as $service)
            <tr>
                <td>{{ $service->name }}</td>
                <td class="text-right">{{ number_format($service->orders) }}</td>
                <td class="text-right">₱{{ number_format($service->revenue, 2) }}</td>
                <td class="text-right">{{ $totalRevenue > 0 ? number_format(($service->revenue / $totalRevenue) * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td><strong>Total</strong></td>
                <td class="text-right"><strong>{{ number_format($serviceRevenue->sum('orders')) }}</strong></td>
                <td class="text-right"><strong>₱{{ number_format($totalRevenue, 2) }}</strong></td>
                <td class="text-right"><strong>100%</strong></td>
            </tr>
        </tbody>
    </table>

    <!-- Payment Method Analysis -->
    <div class="section-title">Payment Method Analysis</div>
    <table>
        <thead>
            <tr>
                <th>Payment Method</th>
                <th class="text-right">Orders</th>
                <th class="text-right">Revenue</th>
                <th class="text-right">% of Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($paymentMethods as $method)
            <tr>
                <td>{{ ucfirst($method->payment_method) }}</td>
                <td class="text-right">{{ number_format($method->orders) }}</td>
                <td class="text-right">₱{{ number_format($method->revenue, 2) }}</td>
                <td class="text-right">{{ $totalRevenue > 0 ? number_format(($method->revenue / $totalRevenue) * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td><strong>Total</strong></td>
                <td class="text-right"><strong>{{ number_format($paymentMethods->sum('orders')) }}</strong></td>
                <td class="text-right"><strong>₱{{ number_format($totalRevenue, 2) }}</strong></td>
                <td class="text-right"><strong>100%</strong></td>
            </tr>
        </tbody>
    </table>

    <!-- Top Customers -->
    <div class="section-title">Top Customers</div>
    <table>
        <thead>
            <tr>
                <th>Customer Name</th>
                <th class="text-right">Orders</th>
                <th class="text-right">Total Spent</th>
                <th class="text-right">% of Total Revenue</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topCustomers as $customer)
            <tr>
                <td>{{ $customer->name }}</td>
                <td class="text-right">{{ number_format($customer->orders) }}</td>
                <td class="text-right">₱{{ number_format($customer->total_spent, 2) }}</td>
                <td class="text-right">{{ $totalRevenue > 0 ? number_format(($customer->total_spent / $totalRevenue) * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on {{ now()->format('M d, Y H:i:s') }}</p>
        <p>This is a computer-generated report. No signature is required.</p>
    </div>
</body>
</html> 