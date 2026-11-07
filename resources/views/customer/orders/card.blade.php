<article class="surface order-card">
    <div>
        <div class="d-flex flex-wrap gap-2 align-items-center"><span class="reference">{{ $order->reference_no }}</span>@include('components.order-status', ['order' => $order])</div>
        <h3>{{ $order->service }} <span class="text-muted fw-normal">· {{ $order->weight }} kg</span></h3>
        <p>Pickup {{ \Carbon\Carbon::parse($order->date)->format('M j, Y') }} · {{ \Carbon\Carbon::parse($order->time)->format('g:i A') }}</p>
        <p class="mt-2">{{ \App\Support\LaundryWorkflow::description($order->status) }}</p>
    </div>
    <div class="order-card-side">
        <div class="text-end"><div class="order-card-price">₱{{ number_format($order->total_amount, 2) }}</div><span class="payment-pill payment-{{ $order->payment_status }}">{{ $order->payment_status === 'paid' ? 'Paid' : 'Payment due' }}</span></div>
        <a href="{{ route('order.tracking', $order->reference_no) }}" class="btn btn-light">{{ in_array($order->status, ['Completed', 'Refunded']) ? 'View details' : 'Track order' }} <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i></a>
    </div>
</article>
