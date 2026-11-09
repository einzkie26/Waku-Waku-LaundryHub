@extends('layouts.staff-app')
@section('title', 'Order details')
@section('content')
@php($next = \App\Support\LaundryWorkflow::nextStatus($order->service, $order->status))
<div class="page-heading"><div><a href="{{ route('staff.orders.index') }}" class="small d-inline-block mb-3">← Order queue</a><h1>Order <span class="reference fs-4">{{ $order->reference_no }}</span></h1></div>@include('components.order-status', ['order' => $order])</div>
<div class="detail-grid">
    <div>
        <section class="surface mb-4">
            <div class="eyebrow">Keep this load moving</div><h2>Next action</h2>
            @if($next)
                <p class="text-muted small">Current progress: <strong>{{ \App\Support\LaundryWorkflow::label($order->status) }}</strong>. Update only after the next step has happened.</p>
                <form method="POST" action="{{ route('staff.orders.status', $order->id) }}" data-single-submit>@csrf<input type="hidden" name="current_status" value="{{ $order->status }}"><button class="btn btn-primary" type="submit">{{ \App\Support\LaundryWorkflow::action($next) }} <i class="bi bi-arrow-right ms-2" aria-hidden="true"></i></button></form>
            @else
                <div class="notice mt-3">{{ \App\Support\LaundryWorkflow::description($order->status) }}</div>
            @endif
            @include('components.order-timeline', ['order' => $order])
        </section>
        <section class="surface mb-4"><h2 class="mb-4">Customer & pickup</h2><dl class="detail-list"><div><dt>Customer</dt><dd>{{ $order->user?->name ?? 'Customer unavailable' }}</dd></div><div><dt>Phone</dt><dd>{{ $order->user?->phone ?? 'Not provided' }}</dd></div><div><dt>Pickup date</dt><dd>{{ \Carbon\Carbon::parse($order->date)->format('M j, Y') }}</dd></div><div><dt>Pickup time</dt><dd>{{ \Carbon\Carbon::parse($order->time)->format('g:i A') }}</dd></div></dl><div class="pickup-contact">{{ $order->user?->address ?? 'Address unavailable' }}</div>
            <hr><dl class="detail-list mb-0"><div><dt>Wash preference</dt><dd>{{ $order->laundry_style ?: 'Regular Wash' }}</dd></div><div><dt>Fabric</dt><dd>{{ $order->fabric_type ?: 'Mixed' }}</dd></div></dl>
            @if($order->special_instructions)<div class="notice mt-4"><div class="notice-title">Customer instructions</div><p>{{ $order->special_instructions }}</p></div>@endif
        </section>
        <section class="surface"><h2>Order activity</h2><ul class="activity-list">
            @forelse($order->statusHistories->sortByDesc('created_at') as $history)<li><span>{{ $history->status === 'Created' ? 'Order created' : \App\Support\LaundryWorkflow::label($history->status) }}<small class="text-muted d-block">Updated by {{ $history->changed_by_type }}</small></span><time datetime="{{ $history->created_at->toIso8601String() }}">{{ $history->created_at->format('M j, Y · g:i A') }}</time></li>@empty<li>No activity recorded yet.</li>@endforelse
        </ul></section>
    </div>
    <div>
        <section class="surface mb-4"><h2 class="mb-4">Price & payment</h2>@include('components.order-price', ['order' => $order])
            @if($order->payment_status !== 'paid' && $order->status !== 'Refunded')
            <form method="POST" action="{{ route('staff.orders.pay', $order->id) }}" class="mt-4" data-payment-record data-single-submit>
                @csrf
                <p class="form-text">Record payment only after receiving it. This does not change laundry progress.</p>
                <label class="form-label" for="payment_method">Payment method</label><select class="form-select mb-3" id="payment_method" name="payment_method" required><option value="">Choose method</option>@foreach(['Cash' => 'Cash', 'GCash' => 'GCash', 'Bank' => 'Bank transfer'] as $value => $label)<option value="{{ $value }}" @selected(old('payment_method') === $value)>{{ $label }}</option>@endforeach</select>
                <label class="form-label" for="payment_reference">Reference <span class="text-muted fw-normal">(required for GCash / bank)</span></label><input class="form-control mb-3" id="payment_reference" name="payment_reference" maxlength="100" value="{{ old('payment_reference') }}">
                <button class="btn btn-primary w-100" type="submit">Record payment received</button>
            </form>
            @endif
            @if($order->payment_reference)<p class="form-text mt-3">Payment reference: {{ $order->payment_reference }}</p>@endif
            <a class="btn btn-light w-100 mt-3" href="{{ route('receipt.show', $order->id) }}" target="_blank" rel="noopener"><i class="bi bi-receipt me-2" aria-hidden="true"></i>View / print receipt</a>
        </section>
        <div class="notice"><div class="notice-title">Progress and payment are separate.</div><p>“Completed” means the laundry was delivered. “Paid” means the money was received. Always check both.</p></div>
    </div>
</div>
@endsection
