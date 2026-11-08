@extends('layouts.admin-app')
@section('title', 'Edit customer')
@section('content')
<div class="admin-heading"><div><a class="admin-back-link" href="{{ route('admin.customers.show', $customer->id) }}"><i class="bi bi-arrow-left" aria-hidden="true"></i>{{ $customer->name }}</a><h1>Edit customer details</h1></div></div>
@include('admin.customers.form')
@endsection
