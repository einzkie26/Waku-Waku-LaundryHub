@extends('layouts.staff-app')
@section('title', 'Customer details')
@section('content')
<div class="page-heading"><div><a href="{{ route('staff.customers') }}" class="small d-inline-block mb-3">← Customers</a><h1>{{ $customer->name }}</h1><p>{{ $customer->username }} · {{ $customer->phone }}</p></div><div class="d-flex gap-2"><a class="btn btn-light" href="{{ route('staff.customers.edit', $customer->id) }}">Edit details</a><a class="btn btn-primary" href="{{ route('staff.orders.create', ['customer' => $customer->id]) }}">New order</a></div></div>
<div class="surface mb-4"><h2>Pickup address</h2><p class="mb-0">{{ $customer->address }}</p></div>
<div class="section-heading"><h2>Order history</h2></div>
@include('staff.orders.table')
<div class="mt-4">{{ $orders->links('pagination::bootstrap-5') }}</div>
@endsection
