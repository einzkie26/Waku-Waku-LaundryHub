@extends('layouts.admin-app')
@section('title', 'Add customer')
@section('content')
<div class="admin-heading"><div><a class="admin-back-link" href="{{ route('admin.customers.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i>Customers</a><h1>Welcome a new customer.</h1></div></div>
@include('admin.customers.form')
@endsection
