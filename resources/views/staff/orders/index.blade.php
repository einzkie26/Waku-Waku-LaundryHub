@extends('layouts.staff-app')
@section('title', 'Order queue')
@section('content')
<div class="page-heading"><div><h1>Order queue</h1></div><a class="btn btn-primary" href="{{ route('staff.orders.create') }}"><i class="bi bi-plus-lg me-2" aria-hidden="true"></i>New order</a></div>
@include('staff.orders.stats')
<div class="workflow-hint"><i class="bi bi-signpost-split" aria-hidden="true"></i><div><strong>Pickup → Cleaning → Ready → Delivered</strong><br>Use the action beside each order only when that step happens. Open Details to review instructions or record payment.</div></div>
<form method="GET" action="{{ route('staff.orders.index') }}" class="filter-bar">
    <div class="search-field"><label class="form-label" for="queue-search">Find an order</label><input class="form-control" id="queue-search" name="q" value="{{ request('q') }}" maxlength="100" placeholder="Reference, customer name, or phone"></div>
    <div class="status-field"><label class="form-label" for="queue-status">Progress</label><select class="form-select" id="queue-status" name="status">
        @foreach(['active' => 'In progress', 'all' => 'All orders', 'unpaid' => 'Payment still due', 'Pending' => 'Awaiting pickup', 'Picked Up' => 'Picked up', 'Washing' => 'Washing', 'Drying' => 'Drying', 'Processing' => 'Processing', 'Ready' => 'Ready for delivery', 'Completed' => 'Completed', 'Refunded' => 'Refunded'] as $value => $label)<option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>@endforeach
    </select></div>
    <button class="btn btn-primary" type="submit">Apply filters</button>
    @if(request()->filled('q') || $status !== 'active')<a class="btn btn-light" href="{{ route('staff.orders.index') }}">Reset</a>@endif
</form>
<div class="d-flex justify-content-between mb-3 small text-muted"><span>{{ $orders->total() }} {{ \Illuminate\Support\Str::plural('order', $orders->total()) }} in this view</span><span class="d-none d-sm-inline">Earliest pickup first</span></div>
@include('staff.orders.table')
<div class="mt-4">{{ $orders->links('pagination::bootstrap-5') }}</div>
@endsection
