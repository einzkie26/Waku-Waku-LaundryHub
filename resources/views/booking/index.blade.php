@extends('layouts.app')
@section('title', 'Book a pickup')
@section('content')
<div class="container">
    <div class="page-heading"><div><h1>Book a pickup</h1></div><a href="{{ route('customer.orders.history') }}">Back to my orders</a></div>
    @if(!auth()->user()->phone || !auth()->user()->address)
        <div class="alert alert-warning mb-4">Before you start, <a href="{{ route('profile.settings') }}">add your phone number and pickup address</a> so our team can collect your laundry.</div>
    @endif
    @include('components.laundry-booking-form', ['operator' => false, 'action' => route('booking.store')])
</div>
@endsection
