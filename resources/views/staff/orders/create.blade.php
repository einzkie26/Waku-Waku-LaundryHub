@extends('layouts.staff-app')

@section('title', 'Create Order')

@section('content')
<div class="container-fluid p-0 m-0 pt-4">
    <!-- Header -->
    <div class="mb-4 px-3 wrapper">
        <h2 class="text-dark">Create New Order</h2>
    </div>

    <!-- Order Form -->
    <div class="px-3 wrapper">
        <div class="card shadow p-4">
            <form action="{{ route('staff.orders.store') }}" method="POST" id="orderForm">
                @csrf
                <div class="row">
                    <!-- Customer Selection -->
                    <div class="col-md-6 mb-3">
                        <label for="user_id" class="form-label">Customer</label>
                        <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" 
                                    data-name="{{ $customer->name }}"
                                    data-phone="{{ $customer->phone }}"
                                    data-address="{{ $customer->address }}"
                                    {{ old('user_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }} ({{ $customer->phone }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Service Selection -->
                    <div class="col-md-6 mb-3">
                        <label for="service" class="form-label">Service</label>
                        <select class="form-select @error('service') is-invalid @enderror" id="service" name="service" required>
                            <option value="">Select Service</option>
                            <option value="Wash & Fold">Wash & Fold</option>
                            <option value="Dry Cleaning">Dry Cleaning</option>
                            <option value="Express Service">Express Service</option>
                            <option value="Ironing">Ironing</option>
                        </select>
                        @error('service')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Laundry Style -->
                    <div class="col-md-6 mb-3">
                        <label for="laundry_style" class="form-label">Laundry Style</label>
                        <select class="form-select @error('laundry_style') is-invalid @enderror" id="laundry_style" name="laundry_style" required>
                            <option value="">Select Style</option>
                            <option value="Regular">Regular</option>
                            <option value="Delicate">Delicate</option>
                            <option value="Hand Wash">Hand Wash</option>
                        </select>
                        @error('laundry_style')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Fabric Type -->
                    <div class="col-md-6 mb-3">
                        <label for="fabric_type" class="form-label">Fabric Type</label>
                        <select class="form-select @error('fabric_type') is-invalid @enderror" id="fabric_type" name="fabric_type" required>
                            <option value="">Select Fabric Type</option>
                            <option value="Cotton">Cotton</option>
                            <option value="Wool">Wool</option>
                            <option value="Silk">Silk</option>
                            <option value="Synthetic">Synthetic</option>
                            <option value="Mixed">Mixed</option>
                        </select>
                        @error('fabric_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Weight -->
                    <div class="col-md-6 mb-3">
                        <label for="weight" class="form-label">Weight (kg)</label>
                        <input type="number" class="form-control @error('weight') is-invalid @enderror" 
                               id="weight" name="weight" min="0.1" step="0.1" required>
                        @error('weight')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Additional Services -->
                    <div class="col-12 mb-3">
                        <label class="form-label">Additional Services</label>
                        <div class="row" id="additionalServices">
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input additional-service" type="checkbox" 
                                           name="additional_services[]" value="Starch" 
                                           data-price="20" id="service_starch">
                                    <label class="form-check-label" for="service_starch">
                                        Starch (₱20)
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input additional-service" type="checkbox" 
                                           name="additional_services[]" value="Fabric Softener" 
                                           data-price="15" id="service_softener">
                                    <label class="form-check-label" for="service_softener">
                                        Fabric Softener (₱15)
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
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

                    <!-- Special Instructions -->
                    <div class="col-12 mb-3">
                        <label for="special_instructions" class="form-label">Special Instructions</label>
                        <textarea class="form-control @error('special_instructions') is-invalid @enderror" 
                                  id="special_instructions" name="special_instructions" rows="3"></textarea>
                        @error('special_instructions')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Pickup Date & Time -->
                    <div class="col-md-6 mb-3">
                        <label for="date" class="form-label">Pickup Date</label>
                        <input type="date" class="form-control @error('date') is-invalid @enderror" 
                               id="date" name="date" required>
                        @error('date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="time" class="form-label">Pickup Time</label>
                        <select class="form-select @error('time') is-invalid @enderror" id="time" name="time" required>
                            <option value="09:00">09:00 AM</option>
                            <option value="10:00">10:00 AM</option>
                            <option value="11:00">11:00 AM</option>
                            <option value="14:00">02:00 PM</option>
                            <option value="16:00">04:00 PM</option>
                        </select>
                        @error('time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Payment Information -->
                    <div class="col-md-6 mb-3">
                        <label for="payment_method" class="form-label">Payment Method</label>
                        <select class="form-select @error('payment_method') is-invalid @enderror" 
                                id="payment_method" name="payment_method">
                            <option value="">Select Payment Method</option>
                            <option value="Cash">Cash</option>
                            <option value="GCash">GCash</option>
                            <option value="Bank">Bank Transfer</option>
                        </select>
                        @error('payment_method')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="payment_reference" class="form-label">Payment Reference</label>
                        <input type="text" class="form-control @error('payment_reference') is-invalid @enderror" 
                               id="payment_reference" name="payment_reference">
                        @error('payment_reference')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="payment_status" class="form-label">Payment Status</label>
                        <select class="form-select @error('payment_status') is-invalid @enderror" 
                                id="payment_status" name="payment_status">
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                        </select>
                        @error('payment_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Price Summary -->
                    <div class="col-12 mb-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5 class="card-title">Price Summary</h5>
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
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <a href="{{ route('staff.orders.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .wrapper {
        width: 1050px;
        margin: 0 auto;
        box-sizing: border-box;
    }
    .card {
        border: none;
        border-radius: 0.5rem;
    }
    .form-label {
        font-weight: 500;
        color: #333;
    }
    .form-control, .form-select {
        border-radius: 0.375rem;
        border: 1px solid #dee2e6;
        padding: 0.5rem 0.75rem;
    }
    .form-control:focus, .form-select:focus {
        border-color: #003087;
        box-shadow: 0 0 0 0.2rem rgba(0, 48, 135, 0.25);
    }
    .btn {
        padding: 0.5rem 1rem;
        font-weight: 500;
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
});
</script>
@endpush
@endsection 