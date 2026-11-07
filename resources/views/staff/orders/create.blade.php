@extends('layouts.staff-app')
@section('title', 'New order')
@section('content')
<div class="page-heading"><div><div class="eyebrow">Start a fresh load</div><h1>Create an order</h1><p>Choose the customer, set the pickup, and review the price before saving.</p></div><a href="{{ route('staff.orders.index') }}">Back to order queue</a></div>
@include('components.laundry-booking-form', ['operator' => true, 'action' => route('staff.orders.store')])
@endsection
