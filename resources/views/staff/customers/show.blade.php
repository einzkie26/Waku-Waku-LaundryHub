@extends('layouts.staff-app')

@section('title', 'Customer Details')

@section('content')
<div class="container-fluid p-0 m-0 pt-4">
    <!-- Header -->
    <div class="mb-4 px-3 wrapper">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-dark">Customer Details</h2>
            <div>
                <a href="{{ route('staff.customers.edit', $customer->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit Customer
                </a>
                <a href="{{ route('staff.customers.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Customers
                </a>
            </div>
        </div>
    </div>

    <div class="px-3 wrapper">
        <div class="row">
            <!-- Customer Information -->
            <div class="col-md-4">
                <div class="card shadow mb-4">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <img src="{{ $customer->photo ? asset('storage/' . $customer->photo) : asset('img/no-img.png') }}"
                                 alt="{{ $customer->name }}"
                                 class="rounded-circle"
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        </div>
                        <h4 class="mb-1">{{ $customer->name }}</h4>
                        <p class="text-muted mb-3">Customer since {{ $customer->created_at->format('M d, Y') }}</p>
                        
                        <div class="text-start">
                            <p><strong>Email:</strong> {{ $customer->email }}</p>
                            <p><strong>Phone:</strong> {{ $customer->phone }}</p>
                            <p><strong>Address:</strong> {{ $customer->address }}</p>
                        </div>
                    </div>
                </div>

                <!-- Order Statistics -->
                <div class="card shadow">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Order Statistics</h5>
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <h3 class="mb-1">{{ $customer->orders_count }}</h3>
                                <small class="text-muted">Total Orders</small>
                            </div>
                            <div class="col-6 mb-3">
                                <h3 class="mb-1">{{ $customer->orders->where('status', 'completed')->count() }}</h3>
                                <small class="text-muted">Completed</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order History -->
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Order History</h5>
                        
                        @if($customer->orders->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Total</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($customer->orders as $order)
                                        <tr>
                                            <td>{{ $order->reference_number }}</td>
                                            <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                                            <td>
                                                <span class="badge bg-{{ $order->status_color }}">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td>
                                            <td>₱{{ number_format($order->total_amount, 2) }}</td>
                                            <td>
                                                <a href="{{ route('staff.orders.show', $order->id) }}" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-shopping-bag fa-2x mb-3"></i>
                                    <p>No orders found</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 