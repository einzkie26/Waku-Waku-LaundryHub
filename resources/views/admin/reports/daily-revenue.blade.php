@extends('admin.layouts.app')
@section('content')
<div class="container-fluid py-4">
    @php
    // Get dates from variables passed by controller
    $startDate = $startDate ?? now()->subDays(7);
    $endDate = $endDate ?? now();
    
    // Calculate metrics from variables passed by controller
    $totalOrders = $totalOrders ?? 0;
    $totalRevenue = $totalRevenue ?? 0;
    $avgOrderValue = $avgOrderValue ?? 0;
    $avgDailyRevenue = $avgDailyRevenue ?? 0;
    
    // Get customer metrics from controller
    $customerMetrics = $customerMetrics ?? [
        'total_customers' => 0,
        'new_customers' => 0,
        'returning_customers' => 0
    ];
    $customerRetentionRate = $customerRetentionRate ?? 0;
    
    // Get service revenue analysis from controller
    $serviceRevenueAnalysis = $serviceRevenueAnalysis ?? [];
    
    // Get transactions for the period from the report data
    $transactions = \App\Models\Order::whereBetween('created_at', [$startDate, $endDate])
        ->orderBy('created_at', 'desc')
        ->get();
    @endphp

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Revenue Report</h2>
            <p class="text-muted mb-0">Period: {{ $startDate->format('M d, Y') }} to {{ $endDate->format('M d, Y') }}</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <select class="form-select" onchange="location = '?period=' + this.value;">
                <option value="week" {{ $period == 'week' ? 'selected' : '' }}>Last 7 Days</option>
                <option value="month" {{ $period == 'month' ? 'selected' : '' }}>Last 30 Days</option>
            </select>
            <a href="{{ route('reports.download', ['period' => $period]) }}" class="btn btn-primary">
                <i class="fas fa-download me-2"></i>Download Report
            </a>
        </div>
    </div>

    <!-- Key Performance Indicators -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Total Revenue</h6>
                    <h3 class="card-title mb-0">₱{{ number_format($totalRevenue, 2) }}</h3>
                    <p class="card-text mt-2 mb-0">
                        <small>Total revenue for period</small>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-gradient-success text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Total Orders</h6>
                    <h3 class="card-title mb-0">{{ number_format($totalOrders) }}</h3>
                    <p class="card-text mt-2 mb-0">
                        <small>Completed orders</small>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-gradient-info text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Average Order Value</h6>
                    <h3 class="card-title mb-0">₱{{ number_format($avgOrderValue, 2) }}</h3>
                    <p class="card-text mt-2 mb-0">
                        <small>Per order average</small>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-gradient-warning text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">Customer Retention</h6>
                    <h3 class="card-title mb-0">{{ number_format($customerRetentionRate, 1) }}%</h3>
                    <p class="card-text mt-2 mb-0">
                        <small>Returning customers</small>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Daily Revenue</h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Revenue Analysis -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Service Revenue Analysis</h5>
                </div>
                <div class="card-body">
                    <canvas id="serviceChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Service Details</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th class="text-end">Orders</th>
                                    <th class="text-end">Revenue</th>
                                    <th class="text-end">% of Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($serviceRevenue as $service)
                                <tr>
                                    <td>{{ $service->name }}</td>
                                    <td class="text-end">{{ number_format($service->orders) }}</td>
                                    <td class="text-end">₱{{ number_format($service->revenue, 2) }}</td>
                                    <td class="text-end">{{ $totalRevenue > 0 ? number_format(($service->revenue / $totalRevenue) * 100, 1) : 0 }}%</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Methods -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Payment Methods</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Payment Method</th>
                                    <th class="text-end">Orders</th>
                                    <th class="text-end">Revenue</th>
                                    <th class="text-end">% of Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($paymentMethodData as $method)
                                <tr>
                                    <td>{{ ucfirst($method->payment_method) }}</td>
                                    <td class="text-end">{{ number_format($method->orders) }}</td>
                                    <td class="text-end">₱{{ number_format($method->revenue, 2) }}</td>
                                    <td class="text-end">{{ $totalRevenue > 0 ? number_format(($method->revenue / $totalRevenue) * 100, 1) : 0 }}%</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Customers -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Top Customers</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th class="text-end">Orders</th>
                                    <th class="text-end">Total Spent</th>
                                    <th class="text-end">% of Total Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topCustomers as $customer)
                                <tr>
                                    <td>{{ $customer->name }}</td>
                                    <td class="text-end">{{ number_format($customer->orders) }}</td>
                                    <td class="text-end">₱{{ number_format($customer->total_spent, 2) }}</td>
                                    <td class="text-end">{{ $totalRevenue > 0 ? number_format(($customer->total_spent / $totalRevenue) * 100, 1) : 0 }}%</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: @json($lineChartLabels),
            datasets: [{
                label: 'Daily Revenue',
                data: @json($lineChartData),
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1,
                fill: false
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Daily Revenue Trend'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Service Revenue Chart
    const serviceCtx = document.getElementById('serviceChart').getContext('2d');
    new Chart(serviceCtx, {
        type: 'pie',
        data: {
            labels: @json($pieChartLabels),
            datasets: [{
                data: @json($pieChartData),
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)',
                    'rgb(75, 192, 192)',
                    'rgb(153, 102, 255)',
                    'rgb(255, 159, 64)'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right',
                },
                title: {
                    display: true,
                    text: 'Revenue by Service'
                }
            }
        }
    });
});
</script>
@endpush
@endsection 