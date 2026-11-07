<div class="surface table-surface">
    <div class="table-responsive">
        <table class="table queue-table order-queue">
            <caption class="visually-hidden">Laundry order queue with pickup schedules, progress, payment status and next actions</caption>
            <thead><tr><th scope="col">Order / customer</th><th scope="col">Laundry</th><th scope="col">Pickup</th><th scope="col">Progress</th><th scope="col">Total / payment</th><th scope="col" class="text-end">Next action</th></tr></thead>
            <tbody>
                @forelse($orders as $order)
                @php($next = \App\Support\LaundryWorkflow::nextStatus($order->service, $order->status))
                <tr>
                    <td><a class="reference text-decoration-none" href="{{ route('staff.orders.show', $order->id) }}">{{ $order->reference_no }}</a><p class="customer-name">{{ $order->user?->name ?? 'Customer unavailable' }}</p></td>
                    <td data-label="Laundry">{{ $order->service }}<p>{{ $order->weight }} kg</p></td>
                    <td data-label="Pickup">{{ \Carbon\Carbon::parse($order->date)->format('M j, Y') }}<p>{{ \Carbon\Carbon::parse($order->time)->format('g:i A') }}</p></td>
                    <td data-label="Progress">@include('components.order-status', ['order' => $order])</td>
                    <td data-label="Total / payment"><strong>₱{{ number_format($order->total_amount, 2) }}</strong><p><span class="payment-pill payment-{{ $order->payment_status }}">{{ $order->payment_status === 'paid' ? 'Paid' : 'Payment due' }}</span></p></td>
                    <td><div class="queue-actions"><a class="btn btn-light btn-sm" href="{{ route('staff.orders.show', $order->id) }}">Details</a>
                        @if($next)
                        <form method="POST" action="{{ route('staff.orders.status', $order->id) }}" data-single-submit>@csrf<input type="hidden" name="current_status" value="{{ $order->status }}"><button class="btn btn-primary btn-sm" type="submit">{{ \App\Support\LaundryWorkflow::action($next) }}</button></form>
                        @endif
                    </div></td>
                </tr>
                @empty<tr><td colspan="6"><div class="empty-state"><div class="empty-icon"><i class="bi bi-basket" aria-hidden="true"></i></div><h2>No orders in this view.</h2><p>Try another status or search, or create a new order.</p><a class="btn btn-light" href="{{ route('staff.orders.index', ['status' => 'all']) }}">View all orders</a></div></td></tr>@endforelse
            </tbody>
        </table>
    </div>
</div>
