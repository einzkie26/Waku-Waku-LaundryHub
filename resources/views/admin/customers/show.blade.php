@extends('layouts.admin-app')
@section('title', 'Customer profile')
@section('content')
<div class="admin-heading"><div><a class="admin-back-link" href="{{ route('admin.customers.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i>Customers</a><h1>{{ $customer->name }}</h1></div><div class="admin-heading-actions"><a class="btn btn-light" href="{{ route('admin.customers.edit', $customer->id) }}"><i class="bi bi-pencil me-2" aria-hidden="true"></i>Edit details</a><a class="btn btn-primary" href="{{ route('orders.create', ['customer' => $customer->id]) }}"><i class="bi bi-plus-lg me-2" aria-hidden="true"></i>New order</a></div></div>
@include('admin.components.metrics', ['items' => [
    ['label' => 'Total orders', 'value' => $metrics['orders'], 'note' => 'All previous and active loads', 'icon' => 'basket'],
    ['label' => 'In progress', 'value' => $metrics['active'], 'note' => 'Currently in our care', 'icon' => 'arrow-repeat'],
    ['label' => 'Payments received', 'value' => '₱'.number_format($metrics['paid'], 2), 'note' => 'Excludes refunded orders', 'icon' => 'wallet2', 'featured' => true],
]])
<section class="admin-panel mb-4"><div class="admin-panel-header"><h2>Contact & pickup details</h2></div><div class="admin-panel-body"><dl class="admin-detail-list"><div><dt>Phone number</dt><dd>{{ $customer->phone ?: 'Not provided' }}</dd></div><div><dt>Username</dt><dd>{{ $customer->username }}</dd></div><div class="full"><dt>Pickup address</dt><dd>{{ $customer->address ?: 'Not provided' }}</dd></div></dl></div></section>
<section class="admin-panel"><div class="admin-panel-header mb-4"><div><h2>Order history</h2></div></div>@include('admin.components.order-table', ['compact' => true])@if($orders->hasPages())<div class="admin-pagination">{{ $orders->links('pagination::bootstrap-5') }}</div>@endif</section>
@endsection
