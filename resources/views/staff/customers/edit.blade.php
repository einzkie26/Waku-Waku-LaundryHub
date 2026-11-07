@extends('layouts.staff-app')
@section('title', 'Edit customer')
@section('content')
<div class="page-heading"><div><a href="{{ route('staff.customers.show', $customer->id) }}" class="small d-inline-block mb-3">← Customer details</a><h1>Edit customer</h1><p>Update {{ $customer->name }}'s contact and pickup details.</p></div></div>
@include('staff.customers.form')
@endsection
