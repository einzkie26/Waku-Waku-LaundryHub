@extends('layouts.admin-app')
@section('title', 'New order')
@section('content')
<div class="admin-heading"><div><a class="admin-back-link" href="{{ route('orders.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i>Orders</a><h1>A fresh load starts here.</h1></div></div>
@include('components.laundry-booking-form', ['operator' => true, 'action' => route('orders.store'), 'cancelUrl' => route('orders.index'), 'customerCreateUrl' => route('admin.customers.create')])
@endsection
