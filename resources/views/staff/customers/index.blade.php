@extends('layouts.staff-app')
@section('title', 'Customers')
@section('content')
<div class="page-heading"><div><div class="eyebrow">The people behind each load</div><h1>Customers</h1><p>Find contact details or start an order for a customer.</p></div><a class="btn btn-primary" href="{{ route('staff.customers.create') }}">Add customer</a></div>
<form class="filter-bar" method="GET" action="{{ route('staff.customers') }}"><div class="search-field"><label class="form-label" for="customer-search">Find a customer</label><input class="form-control" id="customer-search" name="q" value="{{ request('q') }}" placeholder="Name or phone number" maxlength="100"></div><button class="btn btn-primary" type="submit">Search</button>@if(request('q'))<a class="btn btn-light" href="{{ route('staff.customers') }}">Reset</a>@endif</form>
<div class="surface table-surface table-responsive"><table class="table queue-table"><caption class="visually-hidden">Customer contact details and order actions</caption><thead><tr><th scope="col">Customer</th><th scope="col">Phone</th><th scope="col">Pickup address</th><th scope="col">Orders</th><th scope="col" class="text-end">Actions</th></tr></thead><tbody>
    @forelse($customers as $customer)<tr><td><strong>{{ $customer->name }}</strong><p>{{ $customer->username }}</p></td><td>{{ $customer->phone }}</td><td>{{ $customer->address }}</td><td>{{ $customer->orders_count }}</td><td><div class="queue-actions"><a class="btn btn-light btn-sm" href="{{ route('staff.customers.show', $customer->id) }}">Details</a><a class="btn btn-primary btn-sm" href="{{ route('staff.orders.create', ['customer' => $customer->id]) }}">New order</a></div></td></tr>@empty<tr><td colspan="5"><div class="empty-state"><h2>No customers found.</h2><p>Try a different name or add a new customer.</p></div></td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $customers->links('pagination::bootstrap-5') }}</div>
@endsection
