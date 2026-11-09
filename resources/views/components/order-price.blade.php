<div class="summary-line"><span>{{ $order->service }} · {{ $order->weight }} kg</span><strong>₱{{ number_format($order->total_price, 2) }}</strong></div>
@foreach($order->laundry_products ?? [] as $product)
    <div class="summary-line"><span>{{ $product['name'] }} {{ $product['category'] === 'detergent' ? 'detergent' : 'fabric softener' }}<small class="d-block text-muted">{{ $product['quantity'] }} {{ \Illuminate\Support\Str::plural($product['unit'], $product['quantity']) }}@if($product['category'] === 'softener') · {{ $product['volume_ml'] }} mL @endif</small></span><strong>₱{{ number_format($product['amount_cents'] / 100, 2) }}</strong></div>
@endforeach
<div class="summary-line"><span>Optional extras</span><strong>₱{{ number_format($order->additional_charges, 2) }}</strong></div>
@if($order->additional_services)<ul class="summary-extras">@foreach($order->additional_services as $extra)<li>{{ $extra }}</li>@endforeach</ul>@endif
<div class="summary-line summary-total"><span>Total</span><strong>₱{{ number_format($order->total_amount, 2) }}</strong></div>
<div class="mt-3"><span class="payment-pill payment-{{ $order->payment_status }}">{{ $order->payment_status === 'paid' ? 'Paid' : 'Payment due' }}</span>@if($order->payment_status === 'paid')<span class="small text-muted ms-2">{{ $order->payment_method }}@if($order->paid_at) · {{ $order->paid_at->format('M j, g:i A') }}@endif</span>@endif</div>
