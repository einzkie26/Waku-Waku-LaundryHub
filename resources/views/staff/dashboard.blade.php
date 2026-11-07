@extends('layouts.staff-app')
@section('title', 'Overview')
@section('content')
<div class="page-heading"><div><div class="eyebrow">A fresh start to the day</div><h1>Hello, {{ explode(' ', $employee->name)[0] }}.</h1><p>Here is the shop's laundry queue. Start with the earliest pickups and keep each order moving.</p></div><a class="btn btn-primary" href="{{ route('staff.orders.create') }}"><i class="bi bi-plus-lg me-2" aria-hidden="true"></i>New order</a></div>
@include('staff.orders.stats', ['todayOnly' => true])
<div class="workflow-hint"><i class="bi bi-check2-circle" aria-hidden="true"></i><div><strong>A clear next step for every order.</strong><br>Confirm pickup when collected, update cleaning as it happens, then confirm delivery when the customer receives their laundry.</div></div>
<div class="section-heading"><div><h2>Up next</h2><p>Active orders, starting with the earliest pickup.</p></div><a href="{{ route('staff.orders.index') }}">Open full queue <i class="bi bi-arrow-right" aria-hidden="true"></i></a></div>
@include('staff.orders.table')
@endsection
