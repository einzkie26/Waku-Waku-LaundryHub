@extends('layouts.staff-app')

@section('title', 'Create New Order')

@section('content')
<div class="container-fluid p-0 m-0 pt-4">
    <div class="mb-4 px-3 wrapper">
        <h2 class="text-dark">Create New Order</h2>
    </div>

    <div class="px-3 wrapper">
        <div class="d-flex gap-4">
            <div class="form-container">
                <h5 class="mb-3">Order Details</h5>
                <div class="card shadow p-4 mb-4">
                    <form method="POST" action="{{ route('staff.tasks.store') }}" id="orderForm">
                        @csrf
                        {{-- Select Customer --}}
                        <div class="mb-3">
                            <label for="userSelect" class="form-label">Select Customer</label>
                            <select name="user_id" id="userSelect" class="form-select" required>
                                <option value="" selected disabled>Select a customer</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}"
                                        data-name="{{ $user->name }}"
                                        data-phone="{{ $user->phone }}"
                                        data-address="{{ $user->address }}"
                                        data-photo="{{ $user->photo }}">
                                        {{ $user->name }} ({{ $user->phone }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Auto-filled Info & Photo --}}
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" id="name" class="form-control" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone Number</label>
                                        <input type="text" id="phone" class="form-control" readonly>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Address</label>
                                        <textarea id="address" class="form-control" rows="2" readonly></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <label class="form-label d-block">Photo</label>
                                <div class="mx-auto" style="width: 200px; height: 200px; overflow: hidden; border:1px solid #dee2e6; border-radius: .25rem;">
                                    <img id="userPhoto"
                                         src="{{ asset('img/no-img.png') }}"
                                         alt="User Photo"
                                         class="img-fluid"
                                         style="width: 200px; height: 200px; object-fit: cover;">
                                </div>
                            </div>
                        </div>
                        {{-- Laundry Details --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Service</label>
                                <select name="service" id="service" class="form-select" required>
                                    <option value="" disabled selected>Select a service</option>
                                    <option value="Wash & Fold" data-price="50">Wash & Fold (₱50/kg)</option>
                                    <option value="Dry Cleaning" data-price="70">Dry Cleaning (₱70/kg)</option>
                                    <option value="Express Service" data-price="100">Express Service (₱100/kg)</option>
                                    <option value="Ironing" data-price="40">Ironing (₱40/kg)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Weight (kg)</label>
                                <input type="number" name="weight" id="weight" class="form-control" min="0.1" step="0.1" required>
                            </div>
                        </div>
                        {{-- Laundry Style & Fabric Type --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Laundry Style</label>
                                <select name="laundry_style" id="laundry_style" class="form-select" required>
                                    <option value="" disabled selected>Select style</option>
                                    <option value="Regular">Regular</option>
                                    <option value="Delicate">Delicate</option>
                                    <option value="Hand Wash">Hand Wash</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fabric Type</label>
                                <select name="fabric_type" id="fabric_type" class="form-select" required>
                                    <option value="" disabled selected>Select fabric type</option>
                                    <option value="Cotton">Cotton</option>
                                    <option value="Wool">Wool</option>
                                    <option value="Silk">Silk</option>
                                    <option value="Synthetic">Synthetic</option>
                                    <option value="Mixed">Mixed</option>
                                </select>
                            </div>
                        </div>
                        {{-- Additional Services --}}
                        <div class="mb-3">
                            <label class="form-label">Additional Services</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input additional-service" type="checkbox" 
                                               name="additional_services[]" value="Starch" 
                                               data-price="20" id="service_starch">
                                        <label class="form-check-label" for="service_starch">
                                            Starch (₱20)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input additional-service" type="checkbox" 
                                               name="additional_services[]" value="Fabric Softener" 
                                               data-price="15" id="service_softener">
                                        <label class="form-check-label" for="service_softener">
                                            Fabric Softener (₱15)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input additional-service" type="checkbox" 
                                               name="additional_services[]" value="Express Processing" 
                                               data-price="50" id="service_express">
                                        <label class="form-check-label" for="service_express">
                                            Express Processing (₱50)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- Special Instructions --}}
                        <div class="mb-3">
                            <label for="special_instructions" class="form-label">Special Instructions</label>
                            <textarea class="form-control" id="special_instructions" name="special_instructions" rows="3"></textarea>
                        </div>
                        {{-- Date & Time --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Pickup Date</label>
                                <input type="date" name="date" id="date" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pickup Time</label>
                                <select name="time" id="time" class="form-select" required>
                                    <option value="09:00">09:00 AM</option>
                                    <option value="10:00">10:00 AM</option>
                                    <option value="11:00">11:00 AM</option>
                                    <option value="14:00">02:00 PM</option>
                                    <option value="16:00">04:00 PM</option>
                                </select>
                            </div>
                        </div>
                        {{-- Payment Information --}}
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Payment Method</label>
                                <select name="payment_method" id="payment_method" class="form-select">
                                    <option value="">Select Payment Method</option>
                                    <option value="Cash">Cash</option>
                                    <option value="GCash">GCash</option>
                                    <option value="Bank">Bank Transfer</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Payment Reference</label>
                                <input type="text" name="payment_reference" id="payment_reference" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Payment Status</label>
                                <select name="payment_status" id="payment_status" class="form-select">
                                    <option value="pending">Pending</option>
                                    <option value="paid">Paid</option>
                                </select>
                            </div>
                        </div>
                        {{-- Summary --}}
                        <div class="border rounded p-3 bg-light mb-3">
                            <h5 class="mb-2">Order Summary</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Base Price:</strong> ₱<span id="basePrice">0.00</span></p>
                                    <p><strong>Additional Services:</strong> ₱<span id="additionalCharges">0.00</span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Total Amount:</strong> ₱<span id="totalAmount">0.00</span></p>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Create Order</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    html, body {
        width: 100%;
        margin: 0;
        padding: 0;
        overflow-x: hidden;
    }
    .container-fluid {
        width: 100%;
        box-sizing: border-box;
    }
    .navbar {
        width: 100%;
        padding: 0.5rem 1rem !important; /* Standard Bootstrap navbar padding */
    }
    .wrapper {
        width: 1050px;
        margin: 0 auto;
        box-sizing: border-box;
    }
    .d-flex {
        width: 100%;
    }
    .form-container {
        background-color: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border-radius: 0.5rem;
        padding: 1.5rem;
        box-sizing: border-box;
        width: 100%; /* Changed from 50% to 100% */
    }
    .card {
        border: none;
        border-radius: 0.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .form-label {
        font-weight: 500;
        color: #333;
        margin-bottom: 0.25rem;
    }
    .form-control, .form-select {
        border-radius: 0.25rem;
        border: 1px solid #e0e0e0;
        padding: 0.5rem;
        font-size: 0.875rem;
        width: 100%;
        box-sizing: border-box;
    }
    .form-control::placeholder {
        color: #aaa;
    }
    .form-control:focus, .form-select:focus {
        border-color: #003087;
        box-shadow: 0 0 0 0.2rem rgba(0, 48, 135, 0.25);
    }
    .btn-primary {
        background-color: #003087;
        border-color: #003087;
        padding: 0.5rem 1rem;
        border-radius: 0.25rem;
        font-weight: 500;
    }
    .btn-primary:hover {
        background-color: #002a6e;
        border-color: #002a6e;
    }
    .mb-3 {
        margin-bottom: 1rem !important;
    }
    .summary-item {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px solid #f0f0f0;
        font-size: 0.875rem;
    }
    .summary-item span:first-child {
        color: #666;
    }
    .summary-item span:last-child {
        color: #333;
    }
    .total-amount {
        font-weight: 600;
        font-size: 1rem;
        color: #333;
    }
    .total-amount span:last-child {
        color: #003087;
    }
    h5 {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
    }
    h6 {
        font-size: 0.9rem;
        font-weight: 600;
        color: #333;
    }
    /* Table Styles */
    .table-container {
        background-color: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border-radius: 0.5rem;
        padding: 1.5rem;
        width: 100%;
        box-sizing: border-box;
        margin-top: 2rem;
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
        width: 14.28%; /* Equal width for 7 columns */
        text-align: left;
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
        width: 14.28%;
    }
    .badge {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
        border-radius: 1rem;
        text-transform: lowercase;
        background-color: #e7f3ff;
        color: #003087;
    }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const serviceSelect = document.getElementById('service');
    const weightInput = document.getElementById('weight');
    const additionalServices = document.querySelectorAll('.additional-service');
    const basePriceSpan = document.getElementById('basePrice');
    const additionalChargesSpan = document.getElementById('additionalCharges');
    const totalAmountSpan = document.getElementById('totalAmount');

    const priceList = {
        'Wash & Fold': 50,
        'Dry Cleaning': 70,
        'Express Service': 100,
        'Ironing': 40
    };

    function calculateTotal() {
        const service = serviceSelect.value;
        const weight = parseFloat(weightInput.value) || 0;
        const basePrice = priceList[service] * weight;
        
        let additionalCharges = 0;
        additionalServices.forEach(service => {
            if (service.checked) {
                additionalCharges += parseFloat(service.dataset.price);
            }
        });

        const totalAmount = basePrice + additionalCharges;

        basePriceSpan.textContent = basePrice.toFixed(2);
        additionalChargesSpan.textContent = additionalCharges.toFixed(2);
        totalAmountSpan.textContent = totalAmount.toFixed(2);
    }

    serviceSelect.addEventListener('change', calculateTotal);
    weightInput.addEventListener('input', calculateTotal);
    additionalServices.forEach(service => {
        service.addEventListener('change', calculateTotal);
    });

    // Set minimum date to today
    const dateInput = document.getElementById('date');
    const today = new Date().toISOString().split('T')[0];
    dateInput.min = today;

    function updateSummary() {
        const name = document.getElementById('name').value;
        const phone = document.getElementById('phone').value;
        const serviceEl = document.getElementById('service');
        const selectedService = serviceEl.options[serviceEl.selectedIndex];
        const weight = parseFloat(document.getElementById('weight').value) || 0;
        const date = document.querySelector('[name="date"]').value;
        const time = document.querySelector('[name="time"]').value;
        const pricePerKg = parseFloat(selectedService?.dataset.price) || 0;
        const total = pricePerKg * weight;

        document.getElementById('estimatedPrice').value = `₱${total.toFixed(2)}`;
        document.getElementById('summaryName').textContent = name || '-';
        document.getElementById('summaryPhone').textContent = phone || '-';
        document.getElementById('summaryService').textContent = selectedService?.value || '-';
        document.getElementById('summaryWeight').textContent = weight || '-';
        document.getElementById('summaryDate').textContent = date || '-';
        document.getElementById('summaryTime').textContent = time || '-';
        document.getElementById('summaryPrice').textContent = total.toFixed(2);
    }

    document.getElementById('userSelect').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        document.getElementById('name').value = selected.dataset.name;
        document.getElementById('phone').value = selected.dataset.phone;
        document.getElementById('address').value = selected.dataset.address;
        const photo = selected.dataset.photo;
        const photoPath = photo ? `/storage/profile_photos/${photo}` : `/img/default-user.gif`;
        document.getElementById('userPhoto').src = photoPath;
        updateSummary();
    });

    document.querySelectorAll('input, select, textarea').forEach(el => {
        el.addEventListener('input', updateSummary);
        el.addEventListener('change', updateSummary);
    });

    updateSummary();
});
</script>
@endpush
@endsection