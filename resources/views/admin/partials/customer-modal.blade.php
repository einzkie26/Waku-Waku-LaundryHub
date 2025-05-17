<div class="modal fade" id="customerDetailsModal" tabindex="-1" aria-labelledby="customerDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Customer Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <!-- Customer Info -->
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Customer Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Customer Photo -->
                                <div class="col-md-3 text-center mb-3 mb-md-0">
                                    <img id="customerPhoto" 
                                         src="{{ asset('img/no-img.png') }}" 
                                         alt="Customer Photo"
                                         class="img-fluid rounded-circle"
                                         style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #e9ecef;">
                                </div>
                                <!-- Customer Details -->
                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Name:</strong> <span id="customerName" class="text-primary fw-bold"></span></p>
                                            <p><strong>Username:</strong> <span id="customerUsername"></span></p>
                                            <p><strong>Email:</strong> <span id="customerEmail"></span></p>
                                            <p><strong>Contact:</strong> <span id="customerContact"></span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Address:</strong> <span id="customerAddress"></span></p>
                                            <p><strong>Member Since:</strong> <span id="customerMemberSince"></span></p>
                                            <p><strong>Last Order:</strong> <span id="customerLastOrder"></span></p>
                                            <p><strong>Total Orders:</strong> <span id="customerTotalOrders" class="badge bg-primary"></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order History -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Order History</h5>
                            <div id="orderHistoryLoading" class="spinner-border spinner-border-sm text-primary d-none" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Date</th>
                                            <th>Service</th>
                                            <th>Weight</th>
                                            <th>Status</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody id="orderHistoryTableBody">
                                        <tr>
                                            <td colspan="6" class="text-center py-3">
                                                <div class="text-muted">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    Select a customer to view order history
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <a href="#" id="viewAllOrdersBtn" class="btn btn-primary">
                    <i class="fas fa-list me-2"></i>View All Orders
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<style>
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
.status-processing { background-color: #6f42c1; color: white; }
.status-ready { background-color: #fd7e14; color: white; }
.status-delivered { background-color: #20c997; color: white; }
</style>
