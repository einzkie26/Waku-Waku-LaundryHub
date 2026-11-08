@extends('layouts.admin-app')
@section('title', 'Order details')
@section('content')
@php($next = \App\Support\LaundryWorkflow::nextStatus($order->service, $order->status))
<div class="admin-heading"><div><a class="admin-back-link" href="{{ route('orders.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i>Orders</a><h1>{{ $order->reference_no }}</h1></div><div class="admin-heading-actions">@include('components.order-status', ['order' => $order])<a class="btn btn-light" href="{{ route('receipt.show', $order->id) }}" target="_blank" rel="noopener"><i class="bi bi-printer me-2" aria-hidden="true"></i>Receipt</a></div></div>
<div class="admin-columns">
    <div>
        <section class="admin-panel"><div class="admin-panel-header"><h2>Laundry progress</h2></div><div class="admin-panel-body">
            @if($next)<div class="admin-notice mb-4"><strong>Next: {{ \App\Support\LaundryWorkflow::action($next) }}</strong>Update the progress when this step happens. Payment is recorded separately.</div><form method="POST" action="{{ route('orders.updateStatus', $order->id) }}" data-single-submit>@csrf @method('PATCH')<input type="hidden" name="current_status" value="{{ $order->status }}"><button class="btn btn-primary" type="submit">{{ \App\Support\LaundryWorkflow::action($next) }} <i class="bi bi-arrow-right ms-2" aria-hidden="true"></i></button></form>@endif
            @include('components.order-timeline')
        </div></section>
        <section class="admin-panel"><div class="admin-panel-header"><h2>Customer & pickup</h2>@if($order->user)<a href="{{ route('admin.customers.show', $order->user_id) }}">Customer profile <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>@endif</div><div class="admin-panel-body"><dl class="admin-detail-list">
            <div><dt>Customer</dt><dd>{{ $order->user?->name ?? 'Customer unavailable' }}</dd></div><div><dt>Phone</dt><dd>{{ $order->user?->phone ?: 'Not provided' }}</dd></div>
            <div><dt>Pickup date</dt><dd>{{ \Carbon\Carbon::parse($order->date)->format('M j, Y') }}</dd></div><div><dt>Pickup time</dt><dd>{{ \Carbon\Carbon::parse($order->time)->format('g:i A') }}</dd></div>
            <div class="full"><dt>Pickup address</dt><dd>{{ $order->user?->address ?: 'Not provided' }}</dd></div>
        </dl>@if($order->status === 'Pending')<button class="btn btn-light mt-4" type="button" data-bs-toggle="modal" data-bs-target="#reschedule-modal"><i class="bi bi-calendar3 me-2" aria-hidden="true"></i>Reschedule pickup</button>@endif</div></section>
        <section class="admin-panel"><div class="admin-panel-header"><h2>Care details</h2></div><div class="admin-panel-body"><dl class="admin-detail-list"><div><dt>Service</dt><dd>{{ $order->service }}</dd></div><div><dt>Weight</dt><dd>{{ number_format($order->weight, 1) }} kg</dd></div><div><dt>Wash preference</dt><dd>{{ $order->laundry_style ?: 'Regular Wash' }}</dd></div><div><dt>Fabric</dt><dd>{{ $order->fabric_type ?: 'Mixed' }}</dd></div></dl>@if($order->special_instructions)<div class="admin-notice mt-4"><strong>Customer instructions</strong>{{ $order->special_instructions }}</div>@endif</div></section>
    </div>
    <div>
        <section class="admin-panel"><div class="admin-panel-header"><h2>Price & payment</h2></div><div class="admin-panel-body">@include('components.order-price')
            @if($order->payment_status !== 'paid' && $order->status !== 'Refunded')
            <form class="mt-4" method="POST" action="{{ route('orders.pay', $order->id) }}" data-payment-record data-single-submit>@csrf
                <div class="admin-section-kicker">RECORD A PAYMENT</div><p class="form-text">Use this after the money has been received.</p>
                <label class="form-label" for="payment_method">Payment method</label><select class="form-select mb-3" id="payment_method" name="payment_method" required><option value="">Choose method</option>@foreach(['Cash' => 'Cash', 'GCash' => 'GCash', 'Bank' => 'Bank transfer'] as $value => $label)<option value="{{ $value }}" @selected(old('payment_method') === $value)>{{ $label }}</option>@endforeach</select>
                <label class="form-label" for="payment_reference">Reference <span class="text-muted fw-normal">(GCash / bank)</span></label><input class="form-control mb-3" id="payment_reference" name="payment_reference" maxlength="100" value="{{ old('payment_reference') }}">
                <button class="btn btn-primary w-100" type="submit">Record payment received</button>
            </form>@endif
            @if($order->payment_reference)<p class="form-text mt-3 mb-0">Payment reference: {{ $order->payment_reference }}</p>@endif
        </div></section>
        <section class="admin-panel"><div class="admin-panel-header"><h2>Activity</h2></div><div class="admin-panel-body pt-2"><ul class="activity-list">@forelse($order->statusHistories->sortByDesc('created_at') as $history)<li><span>{{ $history->status === 'Created' ? 'Order created' : \App\Support\LaundryWorkflow::label($history->status) }}<small class="d-block text-muted">{{ ucfirst($history->changed_by_type) }}</small></span><time datetime="{{ $history->created_at->toIso8601String() }}">{{ $history->created_at->format('M j, g:i A') }}</time></li>@empty<li>No updates recorded.</li>@endforelse</ul></div></section>
        @if($order->payment_status === 'paid' && $order->status !== 'Refunded')<section class="admin-panel"><div class="admin-panel-body"><div class="admin-section-kicker">ORDER ADJUSTMENTS</div><p class="form-text">Already issued a full refund outside Laundryhub? Record it here to close the order.</p><button class="btn btn-outline-danger w-100" type="button" data-bs-toggle="modal" data-bs-target="#refund-modal">Record a refund</button></div></section>@endif
    </div>
</div>
@if($order->status === 'Pending')
<div class="modal fade" id="reschedule-modal" tabindex="-1" aria-labelledby="reschedule-title" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form method="POST" action="{{ route('orders.reschedule', $order->id) }}" data-single-submit>@csrf
    <div class="modal-header"><h2 class="modal-title" id="reschedule-title">Reschedule pickup</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div>
    <div class="modal-body"><p class="form-text mb-4">Update the collection appointment for {{ $order->reference_no }}.</p><label class="form-label" for="new_delivery_date">New pickup date</label><input class="form-control mb-3" type="date" id="new_delivery_date" name="new_delivery_date" min="{{ today()->toDateString() }}" value="{{ old('new_delivery_date', $order->date) }}" required><label class="form-label" for="new_pickup_time">New pickup time</label><select class="form-select" id="new_pickup_time" name="new_pickup_time" required>@foreach(\App\Support\LaundryWorkflow::TIMES as $value => $label)<option value="{{ $value }}" @selected(old('new_pickup_time', substr($order->time, 0, 5)) === $value)>{{ $label }}</option>@endforeach</select></div>
    <div class="modal-footer"><button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Save pickup time</button></div>
</form></div></div></div>@endif
@if($order->payment_status === 'paid' && $order->status !== 'Refunded')
<div class="modal fade" id="refund-modal" tabindex="-1" aria-labelledby="refund-title" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form method="POST" action="{{ route('orders.refund', $order->id) }}" data-single-submit>@csrf
    <div class="modal-header"><h2 class="modal-title" id="refund-title">Record a full refund</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div>
    <div class="modal-body"><div class="admin-notice mb-4"><strong>This records a refund already issued.</strong>No money is transferred here. The order will be closed as refunded and excluded from collected-payment totals.</div><label class="form-label" for="refund_amount">Full refund amount</label><input class="form-control mb-3" id="refund_amount" type="number" name="refund_amount" value="{{ number_format($order->total_amount, 2, '.', '') }}" readonly required><label class="form-label" for="refund_reason">Reason</label><textarea class="form-control" id="refund_reason" name="refund_reason" maxlength="255" rows="3" required>{{ old('refund_reason') }}</textarea></div>
    <div class="modal-footer"><button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-danger" type="submit">Record refund & close</button></div>
</form></div></div></div>@endif
@endsection
