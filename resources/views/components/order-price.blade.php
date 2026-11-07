<div class="summary-line"><span>{{ $order->service }} · {{ $order->weight }} kg</span><strong>₱{{ number_format($order->total_price, 2) }}</strong></div>
<div class="summary-line"><span>Optional extras</span><strong>₱{{ number_format($order->additional_charges, 2) }}</strong></div>
@if($order->additional_services)<ul class="summary-extras">@foreach($order->additional_services as $extra)<li>{{ $extra }}</li>@endforeach</ul>@endif
<div class="summary-line summary-total"><span>Total</span><strong>₱{{ number_format($order->total_amount, 2) }}</strong></div>
<div class="mt-3"><span class="payment-pill payment-{{ $order->payment_status }}">{{ $order->payment_status === 'paid' ? 'Paid' : 'Payment due' }}</span>@if($order->payment_status === 'paid')<span class="small text-muted ms-2">{{ $order->payment_method }}@if($order->paid_at) · {{ $order->paid_at->format('M j, g:i A') }}@endif</span>@endif</div>
