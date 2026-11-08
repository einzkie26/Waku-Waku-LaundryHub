@extends('layouts.admin-app')
@section('title', 'Orders')
@section('content')
<div class="admin-heading"><div><h1>Orders</h1></div><button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#order-create-modal"><i class="bi bi-plus-lg me-2" aria-hidden="true"></i>New order</button></div>
@if(session('new_order_id'))<p class="mb-4"><a class="btn btn-light btn-sm" href="{{ route('orders.show', session('new_order_id')) }}">View the new order <i class="bi bi-arrow-up-right ms-1" aria-hidden="true"></i></a></p>@endif
@include('admin.components.metrics', ['items' => [
    ['label' => 'All orders', 'value' => $metrics['all'], 'note' => 'Across every service', 'icon' => 'collection', 'href' => route('orders.index')],
    ['label' => 'In progress', 'value' => $metrics['active'], 'note' => 'Still in our care', 'icon' => 'basket', 'href' => route('orders.index', ['status' => 'active'])],
    ['label' => 'Ready for delivery', 'value' => $metrics['ready'], 'note' => 'Ready to hand back', 'icon' => 'bag-check', 'href' => route('orders.index', ['status' => 'Ready'])],
    ['label' => 'Payment still due', 'value' => '₱'.number_format($metrics['unpaid'], 2), 'note' => 'Excludes refunded orders', 'icon' => 'wallet2', 'featured' => true, 'href' => route('orders.index', ['payment' => 'pending'])],
]])
<form class="admin-filter" action="{{ route('orders.index') }}" method="GET">
    <div class="search-field"><label class="form-label" for="order-search">Find an order</label><input class="form-control" id="order-search" name="q" value="{{ request('q') }}" maxlength="100" placeholder="Reference, customer, or phone"></div>
    <div class="filter-field"><label class="form-label" for="order-status">Progress</label><select class="form-select" id="order-status" name="status">@foreach(['all' => 'All progress', 'active' => 'In progress', 'Pending' => 'Awaiting pickup', 'Picked Up' => 'Picked up', 'Washing' => 'Washing', 'Drying' => 'Drying', 'Processing' => 'Processing', 'Ready' => 'Ready for delivery', 'Completed' => 'Completed', 'Refunded' => 'Refunded'] as $value => $label)<option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>@endforeach</select></div>
    <div class="filter-field"><label class="form-label" for="order-payment">Payment</label><select class="form-select" id="order-payment" name="payment"><option value="all">Any payment</option><option value="pending" @selected(request('payment') === 'pending')>Payment due</option><option value="paid" @selected(request('payment') === 'paid')>Paid</option></select></div>
    <div class="filter-field"><label class="form-label" for="order-service">Service</label><select class="form-select" id="order-service" name="service"><option value="">All services</option>@foreach(\App\Support\LaundryWorkflow::SERVICES as $name => $service)<option value="{{ $name }}" @selected(request('service') === $name)>{{ $name }}</option>@endforeach</select></div>
    <button class="btn btn-primary" type="submit">Apply</button>@if(request()->hasAny(['q', 'status', 'payment', 'service']))<a class="btn btn-light" href="{{ route('orders.index') }}">Reset</a>@endif
</form>
<div class="admin-table-summary"><span>{{ $orders->total() }} {{ \Illuminate\Support\Str::plural('order', $orders->total()) }} in this view</span><span>Most recent first</span></div>
<section class="admin-panel">
    @include('admin.components.order-table')
    @if($orders->hasPages())<div class="admin-pagination">{{ $orders->links('pagination::bootstrap-5') }}</div>@endif
</section>
@include('admin.orders.create-modal')
@endsection
