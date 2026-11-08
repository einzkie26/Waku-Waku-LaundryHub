<div class="table-responsive">
    <table class="table admin-table" data-responsive>
        <caption class="visually-hidden">Orders with customer, laundry, pickup schedule, progress, total and next actions</caption>
        <thead><tr><th scope="col">Order / customer</th><th scope="col">Laundry</th><th scope="col">Pickup</th><th scope="col">Progress</th><th scope="col">Total / payment</th><th scope="col" class="text-end">Actions</th></tr></thead>
        <tbody>
        @forelse($orders as $order)
            @php($next = \App\Support\LaundryWorkflow::nextStatus($order->service, $order->status))
            <tr>
                <td><a class="reference" href="{{ route('orders.show', $order->id) }}">{{ $order->reference_no }}</a><small>{{ $order->user?->name ?? 'Customer unavailable' }}</small></td>
                <td data-label="Laundry">{{ $order->service }}<small>{{ number_format($order->weight, 1) }} kg</small></td>
                <td data-label="Pickup">{{ \Carbon\Carbon::parse($order->date)->format('M j, Y') }}<small>{{ \Carbon\Carbon::parse($order->time)->format('g:i A') }}</small></td>
                <td data-label="Progress">@include('components.order-status', ['order' => $order])</td>
                <td data-label="Total / payment"><strong>₱{{ number_format($order->total_amount, 2) }}</strong><small><span class="payment-pill payment-{{ $order->payment_status }}">{{ $order->payment_status === 'paid' ? 'Paid' : 'Payment due' }}</span></small></td>
                <td><div class="admin-row-actions"><a class="btn btn-light btn-sm" href="{{ route('orders.show', $order->id) }}">Details</a>
                    @if($next && !($compact ?? false))<form method="POST" action="{{ route('orders.updateStatus', $order->id) }}" data-single-submit>@csrf @method('PATCH')<input type="hidden" name="current_status" value="{{ $order->status }}"><button class="btn btn-primary btn-sm" type="submit">{{ \App\Support\LaundryWorkflow::action($next) }}</button></form>@endif
                </div></td>
            </tr>
        @empty
            <tr><td colspan="6"><div class="admin-empty"><i class="bi bi-basket" aria-hidden="true"></i><h2>No orders here yet.</h2><p>Try a different filter or create a new laundry order.</p><a class="btn btn-primary" href="{{ route('orders.create') }}">Create an order</a></div></td></tr>
        @endforelse
        </tbody>
    </table>
</div>
