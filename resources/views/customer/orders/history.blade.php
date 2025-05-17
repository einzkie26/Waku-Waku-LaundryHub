@extends('layouts.app')

@section('title', 'Order History')

@section('content')
<div class="container py-5">
    <h2 class="mb-4">Order History</h2>
    @if($orders->isEmpty())
        <div class="alert alert-info">You have no order history yet.</div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Reference #</th>
                        <th>Service</th>
                        <th>Weight</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Total Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td>{{ $order->reference_no }}</td>
                            <td>{{ $order->service }}</td>
                            <td>{{ $order->weight }} kg</td>
                            <td>{{ \Carbon\Carbon::parse($order->date)->format('M d, Y') }}</td>
                            <td>{{ $order->time }}</td>
                            <td>
                                @php
                                    $status = strtolower($order->status);
                                    $badgeClass = match($status) {
                                        'pending' => 'bg-warning',
                                        'picked up' => 'bg-info',
                                        'washing' => 'bg-primary',
                                        'drying' => 'bg-secondary',
                                        'ready' => 'bg-success',
                                        'completed' => 'bg-dark',
                                        default => 'bg-light text-dark',
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ ucfirst($order->status) }}</span>
                            </td>
                            <td>₱{{ number_format($order->total_price, 2) }}</td>
                            <td>
                                @if($order->status === 'Completed')
                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#orderDetailsModal-{{ $order->id }}">
                                        View Details
                                    </button>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- Modals for completed orders --}}
@foreach($orders as $order)
    @if($order->status === 'Completed')
    <div class="modal fade" id="orderDetailsModal-{{ $order->id }}" tabindex="-1" aria-labelledby="orderDetailsModalLabel-{{ $order->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <div class="w-100 text-center">
                        <div class="mb-2">
                            <span class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 2rem;">
                                <i class="fas fa-check"></i>
                            </span>
                        </div>
                        <h5 class="modal-title fw-bold">Order Completed</h5>
                        <span class="badge bg-dark">Completed</span>
                    </div>
                    <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-0">
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <ul class="list-group list-group-flush mb-3">
                                <li class="list-group-item"><strong>Reference #:</strong> {{ $order->reference_no }}</li>
                                <li class="list-group-item"><strong>Service:</strong> {{ $order->service }}</li>
                                <li class="list-group-item"><strong>Weight:</strong> {{ $order->weight }} kg</li>
                                <li class="list-group-item"><strong>Date:</strong> {{ \Carbon\Carbon::parse($order->date)->format('M d, Y') }}</li>
                                <li class="list-group-item"><strong>Time:</strong> {{ $order->time }}</li>
                                <li class="list-group-item"><strong>Status:</strong> {{ $order->status }}</li>
                                <li class="list-group-item"><strong>Ordered At:</strong> {{ $order->created_at->format('M d, Y h:i A') }}</li>
                                <li class="list-group-item"><strong>Total Price:</strong> ₱{{ number_format($order->total_price, 2) }}</li>
                            </ul>
                            <div class="text-center">
                                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach
@endsection 