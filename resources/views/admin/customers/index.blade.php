@extends('admin.layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Customers Profiling</h1>
        <input type="text" class="form-control w-25" placeholder="Search customers..." id="customerSearch">
    </div>

    <div class="card p-3">
        <table class="table table-hover" id="customersTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Total Orders</th>
                    <th>Last Order Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($customers as $customer)
                <tr
                    data-customer-id="{{ $customer['id'] }}"
                    data-customer-info="{{ htmlspecialchars(json_encode([
                        'name' => $customer['name'],
                        'username' => $customer['username'] ?? 'N/A',
                        'email' => $customer['email'] ?? 'N/A',
                        'contact' => $customer['phone'] ?? 'N/A',
                        'address' => $customer['address'] ?? 'N/A',
                        'photo' => $customer['photo'] ?? null,
                        'totalOrders' => $customer['orders_count'],
                        'memberSince' => \Carbon\Carbon::parse($customer['created_at'])->format('Y-m-d'),
                        'lastOrderDate' => $customer['last_order_date'] ? \Carbon\Carbon::parse($customer['last_order_date'])->format('Y-m-d') : 'N/A'
                    ]), ENT_QUOTES, 'UTF-8') }}"
                >
                    <td>{{ $customer['name'] }}</td>
                    <td>{{ $customer['phone'] ?? 'N/A' }}</td>
                    <td>{{ $customer['orders_count'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($customer['last_order_date'])->format('d M Y') ?? '—' }}</td>
                    <td>
                        <a href="{{ route('admin.customers.show', $customer['id']) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye me-1"></i>View Details
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if(count($customers) === 0)
            <div class="text-center p-3 text-muted">No customers found.</div>
        @endif
    </div>
</div>

@include('admin.partials.customer-modal')
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // View Details Button Click Handler
    document.querySelectorAll('.view-details').forEach(button => {
        button.addEventListener('click', function () {
            const row = this.closest('tr');
            const customerId = row.getAttribute('data-customer-id');
            const info = JSON.parse(row.getAttribute('data-customer-info'));

            // Update customer information
            document.getElementById('customerName').textContent = info.name;
            document.getElementById('customerUsername').textContent = info.username || 'N/A';
            document.getElementById('customerEmail').textContent = info.email || 'N/A';
            document.getElementById('customerContact').textContent = info.contact || 'N/A';
            document.getElementById('customerAddress').textContent = info.address || 'N/A';
            document.getElementById('customerMemberSince').textContent = formatDate(info.memberSince);
            document.getElementById('customerLastOrder').textContent = info.lastOrderDate === 'N/A' ? 'N/A' : formatDate(info.lastOrderDate);
            document.getElementById('customerTotalOrders').textContent = info.totalOrders;

            // Update customer photo if available
            const customerPhoto = document.getElementById('customerPhoto');
            if (info.photo) {
                customerPhoto.src = `{{ asset('storage/') }}/${info.photo}`;
                customerPhoto.style.display = 'block';
            } else {
                customerPhoto.src = '{{ asset("img/no-img.png") }}';
                customerPhoto.style.display = 'block';
            }

            // Update "View All Orders" button link
            document.getElementById('viewAllOrdersBtn').href = `/admin/customers/${customerId}/orders`;

            // Show loading spinner
            const loadingSpinner = document.getElementById('orderHistoryLoading');
            loadingSpinner.classList.remove('d-none');

            // Fetch and display order history
            fetch(`/admin/customers/${customerId}/orders`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(orders => {
                    const tableBody = document.getElementById('orderHistoryTableBody');
                    tableBody.innerHTML = '';

                    if (!orders || orders.length === 0) {
                        tableBody.innerHTML = `
                            <tr>
                                <td colspan="6" class="text-center py-3">
                                    <div class="text-muted">
                                        <i class="fas fa-info-circle me-2"></i>
                                        No order history available
                                    </div>
                                </td>
                            </tr>`;
                    } else {
                        orders.forEach(order => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>#${order.reference_no || order.id}</td>
                                <td>${formatDate(order.date)}</td>
                                <td>${order.service || 'N/A'}</td>
                                <td>${order.weight ? order.weight + ' kg' : 'N/A'}</td>
                                <td><span class="status-badge status-${order.status.toLowerCase()}">${order.status}</span></td>
                                <td>₱${formatAmount(order.amount)}</td>
                            `;
                            tableBody.appendChild(row);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching order history:', error);
                    const tableBody = document.getElementById('orderHistoryTableBody');
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center py-3 text-danger">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                Error loading order history
                            </td>
                        </tr>`;
                })
                .finally(() => {
                    // Hide loading spinner
                    loadingSpinner.classList.add('d-none');
                });
        });
    });

    // Search functionality
    const searchInput = document.getElementById('customerSearch');
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#customersTable tbody tr');
        
        rows.forEach(row => {
            const name = row.querySelector('td:first-child').textContent.toLowerCase();
            const contact = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const shouldShow = name.includes(searchTerm) || contact.includes(searchTerm);
            row.style.display = shouldShow ? '' : 'none';
        });
    });
});

// Helper function to format dates
function formatDate(dateString) {
    if (!dateString || dateString === 'N/A') return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Helper function to format amounts
function formatAmount(amount) {
    if (!amount) return '0.00';
    return parseFloat(amount).toFixed(2);
}
</script>
@endsection
