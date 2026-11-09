@extends('layouts.staff-app')

@section('title', 'Daily Report')

@section('content')
<div class="container-fluid p-0 m-0 pt-4">
    <!-- Header -->
    <div class="mb-4 px-3 wrapper">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-dark">Daily Report</h2>
            <a href="{{ route('staff.reports') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Reports
            </a>
        </div>
    </div>

    <div class="px-3 wrapper">
        <!-- Daily Statistics -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <h5 class="card-title mb-4">Last 7 Days Statistics</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Total Orders</th>
                                <th>Completed Orders</th>
                                <th>Booked value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($last7Days as $date => $stats)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</td>
                                <td>{{ $stats['total_orders'] }}</td>
                                <td>{{ $stats['completed_orders'] }}</td>
                                <td>₱{{ number_format($stats['revenue'], 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-body">
                        <h5 class="card-title">Orders Trend</h5>
                        <canvas id="ordersChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-body">
                        <h5 class="card-title">Booked value trend</h5>
                        <canvas id="revenueChart"></canvas>
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
    // Orders Chart
    const ordersCtx = document.getElementById('ordersChart').getContext('2d');
    new Chart(ordersCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($last7Days->keys()->map(function($date) { return \Carbon\Carbon::parse($date)->format('M d'); })) !!},
            datasets: [{
                label: 'Total Orders',
                data: {!! json_encode($last7Days->pluck('total_orders')) !!},
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }, {
                label: 'Completed Orders',
                data: {!! json_encode($last7Days->pluck('completed_orders')) !!},
                borderColor: 'rgb(54, 162, 235)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($last7Days->keys()->map(function($date) { return \Carbon\Carbon::parse($date)->format('M d'); })) !!},
            datasets: [{
                label: 'Revenue',
                data: {!! json_encode($last7Days->pluck('revenue')) !!},
                borderColor: 'rgb(153, 102, 255)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endpush
@endsection 