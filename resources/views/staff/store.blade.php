@extends('layouts.staff-app')

@section('title', 'Order Created')

@section('content')
<div class="container-fluid p-0 m-0 pt-4">
    <!-- Header -->
    <div class="mb-4 px-3 wrapper">
        <h2 class="text-dark">Order Created Successfully</h2>
    </div>

    <!-- Order Details -->
    <div class="px-3 wrapper">
        <div class="card shadow p-4">
            <!-- Customer Information -->
            <div class="mb-4">
                <h5 class="mb-3">Customer Information</h5>
                <div class="row">
                    <div class="col-md-8">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> {{ $order->user->name }}</p>
                                <p><strong>Phone:</strong> {{ $order->user->phone }}</p>
                                <p><strong>Address:</strong> {{ $order->user->address }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Reference Number:</strong> {{ $order->reference_no }}</p>
                                <p><strong>Status:</strong> <span class="badge bg-{{ $order->status === 'Pending' ? 'warning' : 'success' }}">{{ $order->status }}</span></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="mx-auto" style="width: 150px; height: 150px; overflow: hidden; border:1px solid #dee2e6; border-radius: .25rem;">
                            <img src="{{ $order->user->photo ? asset('storage/' . $order->user->photo) : asset('img/no-img.png') }}"
                                 alt="Customer Photo"
                                 class="img-fluid"
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Details -->
            <div class="mb-4">
                <h5 class="mb-3">Order Details</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Service:</strong> {{ $order->service }}</p>
                        <p><strong>Laundry Style:</strong> {{ $order->laundry_style }}</p>
                        <p><strong>Fabric Type:</strong> {{ $order->fabric_type }}</p>
                        <p><strong>Weight:</strong> {{ $order->weight }} kg</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Pickup Date:</strong> {{ \Carbon\Carbon::parse($order->date)->format('M d, Y') }}</p>
                        <p><strong>Pickup Time:</strong> {{ \Carbon\Carbon::createFromFormat('H:i', $order->time)->format('h:i A') }}</p>
                        <p><strong>Base Price:</strong> ₱{{ number_format($order->total_price, 2) }}</p>
                        @if($order->additional_charges > 0)
                            <p><strong>Additional Charges:</strong> ₱{{ number_format($order->additional_charges, 2) }}</p>
                        @endif
                        <p><strong>Total Amount:</strong> ₱{{ number_format($order->total_amount, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Additional Services -->
            @if(!empty($order->additional_services))
            <div class="mb-4">
                <h5 class="mb-3">Additional Services</h5>
                <ul class="list-unstyled">
                    @foreach($order->additional_services as $service)
                        <li>{{ $service }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Special Instructions -->
            @if($order->special_instructions)
            <div class="mb-4">
                <h5 class="mb-3">Special Instructions</h5>
                <p>{{ $order->special_instructions }}</p>
            </div>
            @endif

            <!-- Payment Information -->
            <div class="mb-4">
                <h5 class="mb-3">Payment Information</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Payment Status:</strong> 
                            <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </p>
                        @if($order->payment_method)
                            <p><strong>Payment Method:</strong> {{ $order->payment_method }}</p>
                        @endif
                        @if($order->payment_reference)
                            <p><strong>Payment Reference:</strong> {{ $order->payment_reference }}</p>
                        @endif
                        @if($order->paid_at)
                            <p><strong>Paid At:</strong> {{ \Carbon\Carbon::parse($order->paid_at)->format('M d, Y h:i A') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="text-end mt-4">
                <a href="{{ route('staff.orders.index') }}" class="btn btn-secondary me-2">Back to Orders</a>
                <a href="{{ route('receipt.show', $order->id) }}" class="btn btn-primary" target="_blank">
                    <i class="fas fa-print me-1"></i> Print Receipt
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .wrapper {
        width: 1050px;
        margin: 0 auto;
        box-sizing: border-box;
    }
    .card {
        border: none;
        border-radius: 0.5rem;
    }
    .badge {
        font-size: 0.875rem;
        padding: 0.5em 1em;
        border-radius: 1rem;
        text-transform: capitalize;
    }
    .btn {
        padding: 0.5rem 1rem;
        font-weight: 500;
    }
    .btn i {
        margin-right: 0.5rem;
    }
</style>
@endsection 