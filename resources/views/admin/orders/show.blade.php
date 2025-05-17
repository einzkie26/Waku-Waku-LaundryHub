@extends('admin.layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h4 class="mb-0">Order Details</h4>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Reference No.</dt>
                <dd class="col-sm-9">{{ $order->reference_no }}</dd>

                <dt class="col-sm-3">Customer</dt>
                <dd class="col-sm-9">{{ $order->user->name ?? 'N/A' }}</dd>

                <dt class="col-sm-3">Service</dt>
                <dd class="col-sm-9">{{ $order->service }}</dd>

                <dt class="col-sm-3">Weight</dt>
                <dd class="col-sm-9">{{ $order->weight }} kg</dd>

                <dt class="col-sm-3">Delivery Date</dt>
                <dd class="col-sm-9">{{ \Carbon\Carbon::parse($order->date)->format('M d, Y') }}</dd>

                <dt class="col-sm-3">Time</dt>
                <dd class="col-sm-9">{{ $order->time }}</dd>

                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">{{ ucfirst($order->status) }}</dd>

                <dt class="col-sm-3">Total Price</dt>
                <dd class="col-sm-9">₱{{ number_format($order->total_price, 2) }}</dd>

                <dt class="col-sm-3">Payment Status</dt>
                <dd class="col-sm-9">{{ ucfirst($order->payment_status) }}</dd>
            </dl>
            <a href="{{ route('orders.index') }}" class="btn btn-secondary mt-3"><i class="fas fa-arrow-left me-1"></i> Back to Orders</a>
        </div>
    </div>
</div>
@endsection 