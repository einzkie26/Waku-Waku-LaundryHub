<div class="modal fade" id="bookingsModal" tabindex="-1" aria-labelledby="bookingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bookingsModalLabel">My Orders</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if(count($order) == 0)
                    <div class="alert alert-info">You don't have any orders yet.</div>
                @else
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Weight</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Ordered At</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order as $orders)
                                <tr>
                                    <td>{{ $orders->service }}</td>
                                    <td>{{ $orders->weight }} kg</td>
                                    <td>{{ \Carbon\Carbon::parse($orders->date)->format('M d, Y') }}</td>
                                    <td>{{ $orders->time }}</td>
                                    <td>{{ $orders->created_at->format('M d, Y h:i A') }}</td>
                                    <td>
                                        @php
                                            $status = strtolower($orders->status);
                                            $badgeClass = match($status) {
                                                'pending' => 'bg-warning',
                                                'picked up' => 'bg-info',
                                                'washing' => 'bg-primary',
                                                'drying' => 'bg-secondary',
                                                'ready' => 'bg-success',
                                                'completed' => 'bg-dark',
                                                default => 'bg-light text-dark',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ ucfirst($orders->status) }}</span>
                                    </td>
                                    <td>
                                        @if($orders->status !== 'Completed')
                                            <a href="{{ route('order.tracking', ['reference' => $orders->reference_no]) }}" class="btn btn-sm btn-outline-primary">
                                                Track Order
                                            </a>
                                        @else
                                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#orderDetailsModal-{{ $orders->id }}">
                                                View Details
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Move all order details modals outside the main modal --}}
@foreach($order as $orders)
    @if($orders->status === 'Completed')
    <div class="modal fade" id="orderDetailsModal-{{ $orders->id }}" tabindex="-1" aria-labelledby="orderDetailsModalLabel-{{ $orders->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <div class="w-100 text-center">
                        <div class="mb-2">
                            <span class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 2rem;">
                                <i class="fas fa-check"></i>
                            </span>
                        </div>
                        <h5 class="modal-title fw-bold">Order Completed</h5>
                        <span class="badge bg-dark">Completed</span>
                    </div>
                    <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-0">
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <ul class="list-group list-group-flush mb-3">
                                <li class="list-group-item"><strong>Reference #:</strong> {{ $orders->reference_no }}</li>
                                <li class="list-group-item"><strong>Service:</strong> {{ $orders->service }}</li>
                                <li class="list-group-item"><strong>Weight:</strong> {{ $orders->weight }} kg</li>
                                <li class="list-group-item"><strong>Date:</strong> {{ \Carbon\Carbon::parse($orders->date)->format('M d, Y') }}</li>
                                <li class="list-group-item"><strong>Time:</strong> {{ $orders->time }}</li>
                                <li class="list-group-item"><strong>Status:</strong> {{ $orders->status }}</li>
                                <li class="list-group-item"><strong>Ordered At:</strong> {{ $orders->created_at->format('M d, Y h:i A') }}</li>
                                <li class="list-group-item"><strong>Total Price:</strong> ₱{{ number_format($orders->total_price, 2) }}</li>
                            </ul>
                            <div class="text-center">
                                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach
