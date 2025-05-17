@extends('admin.layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endsection

@section('content')
<div class="admin-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold">Order Management</h5>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('orders.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Create Order
            </a>
            <div class="position-relative">
                <input type="text" 
                       class="form-control" 
                       id="orderSearch" 
                       placeholder="Search orders by customer, reference, status..."
                       style="min-width: 300px;">
                <div class="position-absolute end-0 top-50 translate-middle-y me-2">
                    <i class="fas fa-search text-muted"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
        <div id="statusToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <span id="toastMessage">Order status updated successfully!</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="admin-table">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Reference No.</th>
                <th>Status</th>
                <th>Delivery Date</th>
                <th>Amount</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
                <tr data-order-id="{{ $order->id }}">
                    <td>{{ $order->user->name ?? 'N/A' }}</td>
                    <td>{{ $order->reference_no }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="status-badge status-{{ strtolower($order->status) }}">
                                {{ ucfirst($order->status) }}
                            </span>
                            @if($order->status !== 'Completed' && $order->status !== 'Refunded')
                            <form action="{{ route('orders.updateStatus', $order->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-arrow-right"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($order->date)->format('M d, Y') }}</td>
                    <td>₱{{ number_format($order->total_price, 2) }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('orders.show', $order->id) }}" class="action-btn text-primary" title="View Order">
                                <i class="bi bi-eye"></i>
                            </a>

                            <button type="button" class="action-btn text-warning border-0 bg-transparent"
                                    data-bs-toggle="modal" data-bs-target="#rescheduleModal"
                                    data-order="{{ $order }}" title="Reschedule Delivery">
                                <i class="bi bi-calendar-event"></i>
                            </button>

                            <button type="button" class="action-btn text-danger border-0 bg-transparent"
                                    data-bs-toggle="modal" data-bs-target="#refundModal"
                                    data-order="{{ $order }}" title="Process Refund">
                                <i class="bi bi-cash-stack"></i>
                            </button>

                            @if($order->status === 'Completed')
                                <button class="action-btn text-info border-0 bg-transparent"
                                        data-bs-toggle="modal"
                                        data-bs-target="#receiptModal"
                                        title="Print Receipt"
                                        data-reference="{{ $order->reference_no }}"
                                        data-customer="{{ $order->user->name ?? 'N/A' }}"
                                        data-date="{{ $order->created_at->format('M d, Y h:i A') }}"
                                        data-status="{{ ucfirst($order->status) }}"
                                        data-service="{{ $order->service }}"
                                        data-weight="{{ $order->weight }} kg"
                                        data-total="₱{{ number_format($order->total_price, 2) }}">
                                    <i class="bi bi-printer"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center">No orders found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div id="noResults" class="text-center p-3 text-muted d-none">
        <i class="fas fa-search me-2"></i>No orders found matching your search.
    </div>
</div>

<!-- Single Dynamic Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="receiptModalLabel">Receipt</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0" style="background: #f8f8f8;">
        <div id="printable-receipt" class="receipt-container">
          <div class="receipt-header">
            <img src="{{ asset('img/logo.png') }}" alt="Logo" class="receipt-logo">
            <h1>LAUNDRY RECEIPT</h1>
            <div class="receipt-meta">
              <span id="modalReference"></span>
              <span id="modalDate"></span>
            </div>
          </div>
          <div class="receipt-content">
            <div class="receipt-row"><span>Customer:</span> <span id="modalCustomer"></span></div>
            <div class="receipt-row"><span>Status:</span> <span id="modalStatus"></span></div>
            <div class="receipt-row"><span>Service:</span> <span id="modalService"></span></div>
            <div class="receipt-row"><span>Weight:</span> <span id="modalWeight"></span></div>
            <div class="receipt-row total"><span>Total:</span> <span id="modalTotal"></span></div>
          </div>
          <div class="receipt-footer">
            <div class="qr-code">
              <img id="modalQR" src="" alt="QR Code">
            </div>
            <p class="thank-you">Thank you for choosing us!</p>
            <p class="keep-receipt">Please keep this receipt for your records.</p>
          </div>
        </div>
        <div id="pdf-clone-container" style="position: fixed; left: -9999px; top: -9999px; visibility: hidden;"></div>
      </div>
      <div class="modal-footer border-0">
        <button onclick="downloadReceiptAsPDF()" class="btn btn-primary" id="generateBtn">
          <i class="bi bi-download"></i> Generate Receipt
        </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Reschedule Modal -->
<div class="modal fade" id="rescheduleModal" tabindex="-1" aria-labelledby="rescheduleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="rescheduleForm" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="rescheduleModalLabel">Reschedule Delivery</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="newDeliveryDate" class="form-label">New Delivery Date</label>
            <input type="date" class="form-control" id="newDeliveryDate" name="new_delivery_date" required>
          </div>
          <div class="mb-3">
            <label for="delayReason" class="form-label">Reason (optional)</label>
            <input type="text" class="form-control" id="delayReason" name="reason">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Reschedule</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Refund Modal -->
<div class="modal fade" id="refundModal" tabindex="-1" aria-labelledby="refundModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="refundForm" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="refundModalLabel">Process Refund</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="refundAmount" class="form-label">Refund Amount</label>
            <input type="number" class="form-control" id="refundAmount" name="refund_amount" min="0" step="0.01" required>
          </div>
          <div class="mb-3">
            <label for="refundReason" class="form-label">Reason (optional)</label>
            <input type="text" class="form-control" id="refundReason" name="refund_reason">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Process Refund</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('scripts')
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

    // Set form action dynamically for reschedule
    document.querySelectorAll('button[data-bs-target="#rescheduleModal"]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var order = btn.getAttribute('data-order');
            var orderId = JSON.parse(order).id;
            document.getElementById('rescheduleForm').action = `/admin/orders/${orderId}/reschedule`;
        });
    });

    // Set form action dynamically for refund
    document.querySelectorAll('button[data-bs-target="#refundModal"]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var order = btn.getAttribute('data-order');
            var orderId = JSON.parse(order).id;
            document.getElementById('refundForm').action = `/admin/orders/${orderId}/refund`;
        });
    });
});
</script>
@endsection

@push('styles')
<style>
.receipt-container {
  max-width: 340px;
  margin: 0 auto;
  font-family: 'Courier New', Courier, monospace;
  border: 2px dashed #222;
  background: #fff;
  padding: 24px 18px;
  box-shadow: 0 2px 16px rgba(0,0,0,0.08);
  color: #222;
  position: relative;
}
.receipt-header {
  text-align: center;
  margin-bottom: 18px;
}
.receipt-logo {
  width: 60px;
  height: auto;
  margin-bottom: 6px;
  display: inline-block;
}
.receipt-header h1 {
  font-size: 1.1rem;
  font-weight: bold;
  letter-spacing: 2px;
  margin: 0 0 4px 0;
}
.receipt-meta {
  font-size: 0.85rem;
  color: #666;
  margin-bottom: 4px;
  display: flex;
  justify-content: space-between;
  padding: 0 2px;
}
.receipt-content {
  font-size: 0.97rem;
  margin-bottom: 12px;
}
.receipt-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 7px;
  line-height: 1.4;
}
.receipt-row.total {
  font-weight: bold;
  font-size: 1.08rem;
  margin-top: 10px;
  border-top: 1px dashed #222;
  padding-top: 7px;
}
.receipt-footer {
  text-align: center;
  margin-top: 16px;
}
.qr-code {
  margin-bottom: 10px;
  display: inline-block;
}
.qr-code img {
  width: 80px;
  height: 80px;
  display: block;
  margin: 0 auto;
}
.thank-you {
  margin: 0;
  color: #444;
  font-size: 0.98rem;
}
.keep-receipt {
  margin: 3px 0 0;
  font-size: 0.82rem;
  color: #888;
}
.modal-body {
  padding: 0 !important;
  background: #f8f8f8;
}
.modal-content {
  background: #f8f8f8;
}
.status-badge {
    padding: 0.35em 0.65em;
    font-size: 0.75em;
    font-weight: 700;
    border-radius: 0.25rem;
    text-transform: capitalize;
}
.status-completed { background-color: #198754; color: white; }
.status-pending { background-color: #ffc107; color: black; }
.status-cancelled { background-color: #dc3545; color: white; }
.status-in-progress { background-color: #0dcaf0; color: black; }
.status-processing { background-color: #ffc107; color: black; }
.status-ready { background-color: #0dcaf0; color: black; }
.status-delivered { background-color: #20c997; color: white; }
.status-select {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    height: auto;
    min-width: 120px;
    border-radius: 0.25rem;
    border: 1px solid #ced4da;
    background-color: #fff;
    cursor: pointer;
}

.status-select:focus {
    border-color: #86b7fe;
    outline: 0;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.admin-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-bottom: 1rem;
    background-color: #fff;
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.admin-table th {
    background-color: #f8f9fa;
    color: #495057;
    font-weight: 600;
    padding: 1rem;
    text-align: left;
    border-bottom: 2px solid #dee2e6;
    white-space: nowrap;
}

.admin-table td {
    padding: 1rem;
    vertical-align: middle;
    border-bottom: 1px solid #dee2e6;
    color: #212529;
}

.admin-table tbody tr:last-child td {
    border-bottom: none;
}

.admin-table tbody tr:hover {
    background-color: #f8f9fa;
}

.admin-table .action-btn {
    padding: 0.5rem;
    border-radius: 0.25rem;
    transition: all 0.2s ease;
}

.admin-table .action-btn:hover {
    background-color: #e9ecef;
}
</style>
@endpush
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var receiptModal = document.getElementById('receiptModal');
    receiptModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        document.getElementById('modalReference').textContent = button.getAttribute('data-reference');
        document.getElementById('modalCustomer').textContent = button.getAttribute('data-customer');
        document.getElementById('modalDate').textContent = button.getAttribute('data-date');
        document.getElementById('modalStatus').textContent = button.getAttribute('data-status');
        document.getElementById('modalService').textContent = button.getAttribute('data-service');
        document.getElementById('modalWeight').textContent = button.getAttribute('data-weight');
        document.getElementById('modalTotal').textContent = button.getAttribute('data-total');
        document.getElementById('modalQR').src = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" + encodeURIComponent(button.getAttribute('data-reference'));
    });
});

function downloadReceiptAsPDF() {
    const button = document.getElementById('generateBtn');
    const originalContent = button.innerHTML;
    const receipt = document.getElementById('printable-receipt');
    button.classList.add('btn-loading');
    button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
    button.disabled = true;
    const cloneContainer = document.getElementById('pdf-clone-container');
    cloneContainer.innerHTML = '';
    const clone = receipt.cloneNode(true);
    cloneContainer.appendChild(clone);

    // Get the reference number for the filename
    const reference = document.getElementById('modalReference').textContent.trim();

    const opt = {
        margin: 0.3,
        filename: `receipt-${reference}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { 
            scale: 2,
            useCORS: true,
            logging: false,
            letterRendering: true,
            allowTaint: true,
            scrollY: 0,
            scrollX: 0
        },
        jsPDF: { 
            unit: 'in', 
            format: 'letter', 
            orientation: 'portrait'
        }
    };
    const images = clone.getElementsByTagName('img');
    let loaded = 0;
    if (images.length === 0) {
        generatePDF();
    } else {
        for (let img of images) {
            img.onload = img.onerror = () => {
                loaded++;
                if (loaded === images.length) generatePDF();
            };
            if (img.complete) {
                loaded++;
                if (loaded === images.length) generatePDF();
            }
        }
    }
    function generatePDF() {
        html2pdf().set(opt).from(clone).save()
        .then(() => {
            button.classList.remove('btn-loading');
            button.innerHTML = originalContent;
            button.disabled = false;
            cloneContainer.innerHTML = '';
        })
        .catch(error => {
            console.error('PDF generation failed:', error);
            button.classList.remove('btn-loading');
            button.innerHTML = originalContent;
            button.disabled = false;
            cloneContainer.innerHTML = '';
            alert('Failed to generate PDF. Please try again.');
        });
    }
}
</script>
@endpush
