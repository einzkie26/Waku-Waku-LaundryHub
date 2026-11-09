@extends('layouts.staff-app')
@section('title', 'New order')
@section('content')
<div class="page-heading"><div><h1>New order</h1></div><a href="{{ route('staff.orders.index') }}">Back to order queue</a></div>
@include('components.laundry-booking-form', ['operator' => true, 'action' => route('staff.orders.store')])
@endsection
