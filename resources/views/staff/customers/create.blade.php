@extends('layouts.staff-app')
@section('title', 'Add customer')
@section('content')
<div class="page-heading"><div><a href="{{ route('staff.customers') }}" class="small d-inline-block mb-3">← Customers</a><h1>Add a customer</h1><p>Save their details, then continue straight to their order.</p></div></div>
@include('staff.customers.form', ['customer' => null])
@endsection
