<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Receipt {{ $order->reference_no }} · Waku-Waku Laundryhub</title><link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet"><link href="{{ asset('css/laundryhub.css') }}" rel="stylesheet"><style>.receipt-wrap{max-width:600px;margin:40px auto;padding:0 20px}.receipt-document{padding:36px}.receipt-logo{width:64px;height:64px;object-fit:contain}.receipt-document h1{font-size:1.6rem}.receipt-meta{font-size:.83rem;color:var(--muted)}@media print{body{background:#fff}.receipt-wrap{max-width:100%;margin:0}.receipt-document{border:0;box-shadow:none}.receipt-controls{display:none!important}a{color:inherit;text-decoration:none}}</style></head>
<body>
<div class="receipt-wrap">
    <div class="receipt-controls d-flex justify-content-between gap-3 mb-4"><a class="btn btn-light" href="{{ session('staff_id') ? route('staff.orders.show', $order->id) : route('customer.orders.history') }}">Back to orders</a><button class="btn btn-primary" type="button" data-print-receipt>Print / save PDF</button></div>
    <main class="surface receipt-document">
        <div class="text-center mb-4"><img class="receipt-logo" src="{{ asset('img/logo.png') }}" alt="Waku-Waku Laundryhub"><div class="eyebrow mt-3">Waku-Waku Laundryhub</div><h1>Order receipt</h1><p class="reference">{{ $order->reference_no }}</p></div>
        <dl class="detail-list"><div><dt>Customer</dt><dd>{{ $order->user?->name ?? 'Customer unavailable' }}</dd></div><div><dt>Booked on</dt><dd>{{ $order->created_at->format('M j, Y') }}</dd></div><div><dt>Pickup</dt><dd>{{ \Carbon\Carbon::parse($order->date)->format('M j, Y') }}<br>{{ \Carbon\Carbon::parse($order->time)->format('g:i A') }}</dd></div><div><dt>Laundry progress</dt><dd>{{ \App\Support\LaundryWorkflow::label($order->status) }}</dd></div></dl>
        <hr class="my-4">@include('components.order-price', ['order' => $order])
        @if($order->payment_reference)<p class="receipt-meta mt-3">Payment reference: {{ $order->payment_reference }}</p>@endif
        <p class="receipt-meta text-center mt-5 mb-0">Thank you for choosing Waku-Waku.<br>{{ $order->payment_status === 'paid' ? 'Payment has been recorded for this order.' : 'This order still has a payment due.' }}</p>
    </main>
</div>
<script src="{{ asset('js/laundryhub.js') }}" defer></script>
</body></html>
