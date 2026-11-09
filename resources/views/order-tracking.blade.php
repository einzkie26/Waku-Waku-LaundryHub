@extends('layouts.app')
@section('title', 'Track '.$order->reference_no)
@section('content')
<div class="container">
    <div class="page-heading"><div><a href="{{ route('customer.orders.history') }}" class="small d-inline-block mb-3">← My orders</a><h1>{{ $order->reference_no }}</h1></div>@include('components.order-status', ['order' => $order])</div>
    <div class="detail-grid">
        <div>
            <section class="surface mb-4"><div class="eyebrow">What happens next</div><h2>{{ \App\Support\LaundryWorkflow::label($order->status) }}</h2>@include('components.order-timeline', ['order' => $order])</section>
            <section class="surface"><h2>Order updates</h2><ul class="activity-list">
                @forelse($order->statusHistories->sortByDesc('created_at') as $history)
                    <li><span>{{ $history->status === 'Created' ? 'Booking received' : \App\Support\LaundryWorkflow::label($history->status) }}</span><time datetime="{{ $history->created_at->toIso8601String() }}">{{ $history->created_at->format('M j, Y · g:i A') }}</time></li>
                @empty<li><span>Your order has been received. New updates appear here.</span></li>@endforelse
            </ul></section>
        </div>
        <div>
            <section class="surface mb-4"><h2 class="mb-4">Order total</h2>@include('components.order-price', ['order' => $order])<a href="{{ route('receipt.show', $order->id) }}" class="btn btn-light w-100 mt-4" target="_blank" rel="noopener"><i class="bi bi-receipt me-2" aria-hidden="true"></i>View receipt</a></section>
            <section class="surface"><h2 class="mb-4">Pickup details</h2><dl class="detail-list"><div><dt>Date</dt><dd>{{ \Carbon\Carbon::parse($order->date)->format('M j, Y') }}</dd></div><div><dt>Time</dt><dd>{{ \Carbon\Carbon::parse($order->time)->format('g:i A') }}</dd></div></dl><div class="pickup-contact"><strong>{{ $order->user?->name }}</strong><p>{{ $order->user?->phone }}</p><p>{{ $order->user?->address }}</p></div><p class="form-text mb-0">This appointment is for collection. Delivery is arranged when your laundry is ready.</p>@if($order->special_instructions)<hr><h3 class="h6">Your instructions</h3><p class="small mb-0">{{ $order->special_instructions }}</p>@endif</section>
        </div>
    </div>
</div>
@endsection
