@extends('layouts.staff-app')

@section('title', 'Reports')

@section('content')
<div class="container-fluid p-0 m-0 pt-4">
    <!-- Header -->
    <div class="mb-4 px-3 wrapper">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-dark">Reports</h2>
        </div>
    </div>

    <div class="px-3 wrapper">
        <!-- Today's Summary -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-body">
                        <h5 class="card-title text-muted mb-3">Today's Orders</h5>
                        <h2 class="mb-0">{{ $todayOrders }}</h2>
                        <small class="text-muted">Total orders today</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-body">
                        <h5 class="card-title text-muted mb-3">Completed Orders</h5>
                        <h2 class="mb-0">{{ $todayCompleted }}</h2>
                        <small class="text-muted">Orders completed today</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-body">
                        <h5 class="card-title text-muted mb-3">Booked today</h5>
                        <h2 class="mb-0">₱{{ number_format($todayRevenue, 2) }}</h2>
                        <small class="text-muted">Total revenue today</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Types -->
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card shadow h-100">
                    <div class="card-body">
                        <h5 class="card-title">Daily Report</h5>
                        <p class="text-muted">View your daily order statistics and performance metrics.</p>
                        <a href="{{ route('staff.reports.daily') }}" class="btn btn-primary">
                            <i class="fas fa-chart-line"></i> View Daily Report
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card shadow h-100">
                    <div class="card-body">
                        <h5 class="card-title">Monthly Report</h5>
                        <p class="text-muted">Analyze your monthly performance and revenue trends.</p>
                        <a href="{{ route('staff.reports.monthly') }}" class="btn btn-primary">
                            <i class="fas fa-chart-bar"></i> View Monthly Report
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card shadow h-100">
                    <div class="card-body">
                        <h5 class="card-title">Performance Report</h5>
                        <p class="text-muted">Track your overall performance and service distribution.</p>
                        <a href="{{ route('staff.reports.performance') }}" class="btn btn-primary">
                            <i class="fas fa-chart-pie"></i> View Performance Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 