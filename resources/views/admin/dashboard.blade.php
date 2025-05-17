@extends('layouts.admin-app')

@section('title', 'Dashboard')

@push('styles')
<style>
    .stat-card {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        border-radius: 12px;
        overflow: hidden;
        position: relative;
        z-index: 1;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(45deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
        z-index: -1;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .stat-card:hover::before {
        opacity: 1;
    }
    
    .stat-icon {
        font-size: 2.5rem;
        opacity: 0.8;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover .stat-icon {
        transform: scale(1.1);
        opacity: 1;
    }
    
    .stat-number {
        font-size: 1.8rem;
        font-weight: 600;
        margin: 5px 0;
        display: block;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover .stat-number {
        transform: translateY(-3px);
    }
    
    .stat-text {
        font-size: 0.9rem;
        opacity: 0.8;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover .stat-text {
        opacity: 1;
    }
    
    .recent-orders {
        border-radius: 12px;
        overflow: hidden;
    }
    
    .table th {
        border-top: none;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: capitalize;
    }
</style>
@endpush

@section('content')
<div class="row">
    <!-- Total Orders -->
    <div class="col-lg-3 col-6 mb-4" data-aos="fade-up" data-aos-delay="100">
        <div class="stat-card bg-white p-4 h-100 shadow-sm">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-soft-info d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="fas fa-shopping-cart text-info stat-icon"></i>
                </div>
                <div class="ms-3">
                    <span class="text-muted stat-text">Total Orders</span>
                    <h3 class="mb-0 text-dark stat-number">{{ $totalOrders ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Orders -->
    <div class="col-lg-3 col-6 mb-4" data-aos="fade-up" data-aos-delay="200">
        <div class="stat-card bg-white p-4 h-100 shadow-sm">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-soft-warning d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="fas fa-clock text-warning stat-icon"></i>
                </div>
                <div class="ms-3">
                    <span class="text-muted stat-text">Pending Orders</span>
                    <h3 class="mb-0 text-dark stat-number">{{ $pendingOrders ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Customers -->
    <div class="col-lg-3 col-6 mb-4" data-aos="fade-up" data-aos-delay="300">
        <div class="stat-card bg-white p-4 h-100 shadow-sm">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-soft-success d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="fas fa-users text-success stat-icon"></i>
                </div>
                <div class="ms-3">
                    <span class="text-muted stat-text">Active Customers</span>
                    <h3 class="mb-0 text-dark stat-number">{{ $activeCustomers ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Revenue -->
    <div class="col-lg-3 col-6 mb-4" data-aos="fade-up" data-aos-delay="400">
        <div class="stat-card bg-white p-4 h-100 shadow-sm">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-soft-danger d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="fas fa-dollar-sign text-danger stat-icon"></i>
                </div>
                <div class="ms-3">
                    <span class="text-muted stat-text">Today's Revenue</span>
                    <h3 class="mb-0 text-dark stat-number">₱{{ number_format($dailyRevenue ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Orders -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Orders</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Service</th>
                                <th>Status</th>
                                <th>Amount</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders ?? [] as $order)
                                <tr>
                                    <td>{{ $order->reference_no }}</td>
                                    <td>{{ $order->service }}</td>
                                    <td>
                                        <span class="badge badge-{{ $order->status === 'Completed' ? 'success' : ($order->status === 'Pending' ? 'warning' : 'info') }}">
                                            {{ $order->status }}
                                        </span>
                                    </td>
                                    <td>₱{{ number_format($order->total_price, 2) }}</td>
                                    <td>{{ $order->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No recent orders</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Services -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Top Services</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Orders</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topServices ?? [] as $service)
                                <tr>
                                    <td>{{ $service->service }}</td>
                                    <td>{{ $service->orders }}</td>
                                    <td>₱{{ number_format($service->revenue, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No service data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
