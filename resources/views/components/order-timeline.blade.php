@php
    $steps = \App\Support\LaundryWorkflow::steps($order->service);
    $current = array_search($order->status, $steps, true);
@endphp
@if($current === false)
    <div class="notice mt-3">{{ \App\Support\LaundryWorkflow::description($order->status) }}</div>
@else
    <ol class="tracking-list" aria-label="Laundry progress">
        @foreach($steps as $index => $step)
        <li class="tracking-item {{ $index < $current ? 'is-done' : ($index === $current ? 'is-current' : 'is-future') }}" @if($index === $current) aria-current="step" @endif>
            <span class="tracking-marker" aria-hidden="true">{{ $index < $current ? '✓' : $index + 1 }}</span>
            <div><h3>{{ \App\Support\LaundryWorkflow::label($step) }}@if($index === $current)<span class="small fw-normal text-muted ms-2">Current step</span>@endif</h3><p>{{ \App\Support\LaundryWorkflow::description($step) }}</p></div>
        </li>
        @endforeach
    </ol>
@endif
