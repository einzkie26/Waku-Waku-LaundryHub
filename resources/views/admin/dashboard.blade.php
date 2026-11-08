@extends('layouts.admin-app')
@section('title', 'Overview')
@section('content')
<div class="admin-heading">
    <div><h1>Your shop, at a glance.</h1></div>
    <div class="admin-heading-actions"><span class="admin-date"><i class="bi bi-calendar3" aria-hidden="true"></i>{{ now()->format('D, M j, Y') }}</span><a class="btn btn-primary" href="{{ route('orders.create') }}"><i class="bi bi-plus-lg me-2" aria-hidden="true"></i>New order</a></div>
</div>
@include('admin.components.metrics', ['items' => [
    ['label' => 'Collected today', 'value' => '₱'.number_format($metrics['collectedToday'], 2), 'note' => 'Payments received today', 'icon' => 'wallet2', 'featured' => true, 'href' => route('reports.revenue-report')],
    ['label' => 'Orders in progress', 'value' => $metrics['activeOrders'], 'note' => 'From pickup to delivery', 'icon' => 'basket', 'href' => route('orders.index', ['status' => 'active'])],
    ['label' => 'Ready for delivery', 'value' => $metrics['readyOrders'], 'note' => 'Clean, packed, and ready', 'icon' => 'bag-check', 'href' => route('orders.index', ['status' => 'Ready'])],
    ['label' => 'Customers', 'value' => $metrics['customers'], 'note' => 'People in your care', 'icon' => 'people', 'href' => route('admin.customers.index')],
]])
<div class="admin-columns">
    <section class="admin-panel">
        <div class="admin-panel-header"><div><h2>Payments overview</h2></div><nav class="admin-period" aria-label="Payment chart period"><a href="{{ route('admin.dashboard', ['period' => 'week']) }}" class="{{ $period === 'week' ? 'active' : '' }}" @if($period === 'week') aria-current="page" @endif>7 days</a><a href="{{ route('admin.dashboard', ['period' => 'month']) }}" class="{{ $period === 'month' ? 'active' : '' }}" @if($period === 'month') aria-current="page" @endif>30 days</a></nav></div>
        @include('admin.components.revenue-chart')
        <div class="admin-panel-footer">Includes service and extras. Refunded orders are excluded.</div>
    </section>
    <section class="admin-panel">
        <div class="admin-panel-header"><div><h2>What is in the wash?</h2></div><a href="{{ route('reports.service-stats', ['period' => $period]) }}" aria-label="View service insights"><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></div>
        <div class="admin-panel-body">
            @php($mostOrders = max(1, $serviceVolumes->max('orders')))
            @forelse($serviceVolumes as $service)
            <div class="admin-service-row"><div class="admin-service-label"><strong>{{ $service->service }}</strong><span>{{ $service->orders }} {{ \Illuminate\Support\Str::plural('order', $service->orders) }}</span></div><div class="admin-service-track" role="img" aria-label="{{ $service->service }}: {{ $service->orders }} orders"><span style="width:{{ $service->orders / $mostOrders * 100 }}%"></span></div></div>
            @empty<div class="admin-empty py-4"><i class="bi bi-stars" aria-hidden="true"></i><h3>A fresh slate.</h3><p class="mb-0">Your service mix will appear as orders come in.</p></div>@endforelse
        </div>
        <div class="admin-panel-footer"><a href="{{ route('reports.service-stats') }}" class="text-decoration-none">Explore service insights <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i></a></div>
    </section>
</div>
<section class="admin-panel">
    <div class="admin-panel-header mb-4"><div><h2>Recent orders</h2></div><a href="{{ route('orders.index') }}">View all orders <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i></a></div>
    @include('admin.components.order-table', ['orders' => $recentOrders, 'compact' => true])
</section>
@endsection
