@extends('layouts.app')
@section('title', 'My orders')
@section('content')
<div class="container">
    <div class="page-heading"><div><h1>My orders</h1></div><a href="{{ route('booking.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Book a pickup</a></div>
    <nav class="tab-list mb-4" aria-label="Filter orders">
        @foreach(['active' => 'In progress', 'closed' => 'Past orders', 'all' => 'All orders'] as $key => $label)<a href="{{ route('customer.orders.history', ['view' => $key]) }}" class="{{ $view === $key ? 'active' : '' }}" @if($view === $key) aria-current="page" @endif>{{ $label }} <small>{{ $counts[$key] }}</small></a>@endforeach
    </nav>
    @forelse($orders as $order)
        @include('customer.orders.card', ['order' => $order])
    @empty
        <div class="surface empty-state"><div class="empty-icon"><i class="bi bi-basket" aria-hidden="true"></i></div><h2>{{ $view === 'active' ? 'Your basket is clear.' : 'No orders here yet.' }}</h2><p>{{ $view === 'active' ? 'No laundry is in progress. Book a pickup whenever your next load is ready.' : 'Your previous orders will stay here so you can find them anytime.' }}</p><a href="{{ route('booking.create') }}" class="btn btn-primary">Book a pickup</a></div>
    @endforelse
    <div class="mt-4">{{ $orders->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
