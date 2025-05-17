@extends('admin.layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow p-4">
        <h2 class="text-center text-success mb-4">Order Created Successfully</h2>

        <div class="mb-3">
            <h5>Customer Info:</h5>
            <p><strong>Name:</strong> {{ $order->user->name }}</p>
            <p><strong>Phone:</strong> {{ $order->user->phone }}</p>
            <p><strong>Address:</strong> {{ $order->user->address }}</p>
        </div>

        <div class="mb-3">
            <h5>Order Details:</h5>
            <p><strong>Service:</strong> {{ $order->service }}</p>
            <p><strong>Weight:</strong> {{ $order->weight }} kg</p>
            <p><strong>Pickup Date:</strong> {{ $order->date }}</p>
            <p><strong>Pickup Time:</strong> {{ \Carbon\Carbon::createFromFormat('H:i', $order->time)->format('h:i A') }}</p>
            <p><strong>Total Price:</strong> ₱{{ number_format($order->price, 2) }}</p>
        </div>

        <a href="{{ route('orders.index') }}" class="btn btn-primary w-100">Back to Orders</a>
    </div>
</div>
@endsection
