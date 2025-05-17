@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h4 class="mb-0">Track Your Order</h4>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5>Order Details</h5>
                    <dl class="row">
                        <dt class="col-sm-4">Reference No.</dt>
                        <dd class="col-sm-8">{{ $order->reference_no }}</dd>

                        <dt class="col-sm-4">Service</dt>
                        <dd class="col-sm-8">{{ $order->service }}</dd>

                        <dt class="col-sm-4">Weight</dt>
                        <dd class="col-sm-8">{{ $order->weight }} kg</dd>

                        <dt class="col-sm-4">Total Amount</dt>
                        <dd class="col-sm-8">₱{{ number_format($order->total_price, 2) }}</dd>
                    </dl>
                </div>
            </div>

            <h5 class="mb-3">Order Status</h5>
            <div class="tracking-progress">
                @foreach($statusSteps as $index => $status)
                    <div class="tracking-step {{ $index <= $currentStepIndex ? 'active' : '' }}">
                        <div class="step-icon">
                            @if($index < $currentStepIndex)
                                <i class="fas fa-check-circle"></i>
                            @elseif($index == $currentStepIndex)
                                <i class="fas fa-circle"></i>
                            @else
                                <i class="far fa-circle"></i>
                            @endif
                        </div>
                        <div class="step-content">
                            <h6>{{ $status }}</h6>
                            <p class="text-muted mb-0">{{ $statusDescriptions[$status] }}</p>
                        </div>
                        @if($index < count($statusSteps) - 1)
                            <div class="step-line {{ $index < $currentStepIndex ? 'active' : '' }}"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Order Status History Section --}}
<div class="container py-2">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Order Status History</h5>
        </div>
        <div class="card-body">
            <ul class="list-group mb-3">
                @foreach($order->statusHistories()->orderBy('created_at')->get() as $history)
                    <li class="list-group-item">
                        <strong>{{ $history->status }}</strong>
                        by
                        @if($history->changed_by_type === 'admin')
                            {{ \App\Models\User::find($history->changed_by_id)?->name ?? 'Admin' }} (Admin)
                        @elseif($history->changed_by_type === 'staff')
                            {{ \App\Models\Employee::find($history->changed_by_id)?->name ?? 'Staff' }} (Staff)
                        @else
                            Unknown
                        @endif
                        at {{ $history->created_at->format('Y-m-d H:i') }}
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

<style>
.tracking-progress {
    position: relative;
    padding: 20px 0;
}

.tracking-step {
    position: relative;
    padding-left: 45px;
    margin-bottom: 30px;
}

.step-icon {
    position: absolute;
    left: 0;
    top: 0;
    width: 30px;
    height: 30px;
    text-align: center;
    line-height: 30px;
    color: #ccc;
}

.tracking-step.active .step-icon {
    color: #28a745;
}

.step-content {
    padding: 0 15px;
}

.step-line {
    position: absolute;
    left: 15px;
    top: 30px;
    bottom: -30px;
    width: 2px;
    background: #ccc;
}

.step-line.active {
    background: #28a745;
}

.tracking-step:last-child .step-line {
    display: none;
}
</style>
@endsection
