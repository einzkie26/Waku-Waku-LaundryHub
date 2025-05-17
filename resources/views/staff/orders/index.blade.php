@extends('layouts.staff-app')

@section('title', 'Orders')

@section('content')
<div class="container-fluid p-0 m-0 pt-4">
    <!-- Header -->
    <div class="mb-4 px-3 wrapper">
        <h2 class="text-dark">Orders</h2>
    </div>

    <!-- Orders Table -->
    <div class="px-3 wrapper">
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">All Orders</h5>
            </div>
            <table class="table table-borderless align-middle" id="ordersTable">
                <thead>
                    <tr>
                        <th scope="col">Reference #</th>
                        <th scope="col">Customer</th>
                        <th scope="col">Service</th>
                        <th scope="col">Weight</th>
                        <th scope="col">Total Amount</th>
                        <th scope="col">Status</th>
                        <th scope="col">Pickup Date</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr data-order-id="{{ $order->id }}">
                        <td>{{ $order->reference_no }}</td>
                        <td>{{ $order->customer_name }}</td>
                        <td>{{ $order->service }}</td>
                        <td>{{ $order->weight }} kg</td>
                        <td>₱{{ number_format($order->total_price, 2) }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="status-{{ strtolower($order->status) }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                                @if($order->status !== 'Completed' && $order->status !== 'Refunded')
                                <form action="{{ route('staff.orders.status', $order->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-arrow-right"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($order->pickup_date)->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('staff.store', $order->id) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">No orders found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('orderSearch');
    const noResults = document.getElementById('noResults');
    let searchTimeout;

    // Enhanced search functionality
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = this.value.toLowerCase().trim();
        
        // Debounce the search
        searchTimeout = setTimeout(() => {
            const rows = document.querySelectorAll('table tbody tr');
            let hasResults = false;
            
            rows.forEach(row => {
                if (!row.hasAttribute('data-order-info')) return;
                
                const orderInfo = JSON.parse(row.getAttribute('data-order-info'));
                const searchableText = [
                    orderInfo.customer,
                    orderInfo.reference,
                    orderInfo.status,
                    orderInfo.delivery_date,
                    orderInfo.amount
                ].map(text => text.toLowerCase()).join(' ');
                
                const shouldShow = searchTerm === '' || searchableText.includes(searchTerm);
                row.style.display = shouldShow ? '' : 'none';
                if (shouldShow) hasResults = true;
            });
            
            // Show/hide no results message
            noResults.classList.toggle('d-none', hasResults || searchTerm === '');
        }, 150); // Reduced debounce time to 150ms
    });

    // Clear search when clicking the search icon
    searchInput.parentElement.querySelector('.fa-search').addEventListener('click', function() {
        searchInput.value = '';
        searchInput.dispatchEvent(new Event('input'));
    });

    // AJAX for Advance Status
    document.querySelectorAll('form[action*="staff/orders/status"]').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const url = form.action;
            const token = document.querySelector('meta[name="csrf-token"]').content;
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('toastMessage').textContent = data.message;
                    const toast = new bootstrap.Toast(document.getElementById('statusToast'));
                    toast.show();
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    alert(data.message || 'Failed to update status');
                }
            })
            .catch(error => {
                alert('Failed to update status');
            });
        });
    });
});
</script>
@endpush

<style>
    .wrapper {
        width: 1050px;
        margin: 0 auto;
        box-sizing: border-box;
    }
    .table-container {
        background-color: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border-radius: 0.5rem;
        padding: 1.5rem;
        width: 100%;
        box-sizing: border-box;
    }
    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .table thead th {
        padding: 0.75rem;
        font-weight: 600;
        color: #666;
        border-bottom: 1px solid #f0f0f0;
        font-size: 0.875rem;
    }
    .table tbody tr {
        transition: background-color 0.2s ease-in-out;
    }
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }
    .table tbody td {
        padding: 0.75rem;
        vertical-align: middle;
        font-size: 0.875rem;
        color: #333;
    }
    .badge {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
        border-radius: 1rem;
        text-transform: capitalize;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    .status-select {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        height: auto;
        min-width: 120px;
    }
</style>
@endsection 