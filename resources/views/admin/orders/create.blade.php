@extends('admin.layouts.app')

@section('content')
<div class="container mb-1">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow p-4">
                <h2 class="text-center mb-4">Create Laundry Order</h2>

                {{-- Success & Error Alerts --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('orders.store') }}" id="orderForm">
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

                    {{-- Service & Laundry Style --}}
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
                            <label class="form-label">Laundry Style</label>
                            <select name="laundry_style" id="laundry_style" class="form-select" required>
                                <option value="" disabled selected>Select laundry style</option>
                                @foreach($laundryStyles as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Fabric Type & Weight --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Fabric Type</label>
                            <select name="fabric_type" id="fabric_type" class="form-select" required>
                                <option value="" disabled selected>Select fabric type</option>
                                @foreach($fabricTypes as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Weight (kg)</label>
                            <input type="number" name="weight" id="weight" class="form-control" min="1" step="0.1" required>
                        </div>
                    </div>

                    {{-- Additional Services --}}
                    <div class="mb-3">
                        <label class="form-label">Additional Services</label>
                        <div class="row" id="additionalServices">
                            @foreach($additionalServices as $service => $price)
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input additional-service" type="checkbox" 
                                               name="additional_services[]" value="{{ $service }}" 
                                               data-price="{{ $price }}" id="service_{{ Str::slug($service) }}">
                                        <label class="form-check-label" for="service_{{ Str::slug($service) }}">
                                            {{ $service }} (₱{{ $price }})
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Special Instructions --}}
                    <div class="mb-3">
                        <label class="form-label">Special Instructions</label>
                        <textarea name="special_instructions" id="special_instructions" 
                                  class="form-control" rows="3" 
                                  placeholder="Enter any special instructions or notes..."></textarea>
                    </div>

                    {{-- Date & Time --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Pickup Date</label>
                            <input type="date" name="date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pickup Time</label>
                            <select name="time" class="form-select" required>
                                <option value="09:00">09:00 AM</option>
                                <option value="10:00">10:00 AM</option>
                                <option value="11:00">11:00 AM</option>
                                <option value="14:00">2:00 PM</option>
                                <option value="16:00">4:00 PM</option>
                            </select>
                        </div>
                    </div>

                    {{-- Estimated Price --}}
                    <div class="mb-3">
                        <label class="form-label">Estimated Price</label>
                        <input type="text" id="estimatedPrice" class="form-control bg-light" readonly>
                    </div>

                    {{-- Summary --}}
                    <div class="border rounded p-3 bg-light mb-3">
                        <h5 class="mb-2">Order Summary</h5>
                        <ul class="list-unstyled" id="summaryPreview">
                            <li><strong>Name:</strong> <span id="summaryName">-</span></li>
                            <li><strong>Phone:</strong> <span id="summaryPhone">-</span></li>
                            <li><strong>Service:</strong> <span id="summaryService">-</span></li>
                            <li><strong>Laundry Style:</strong> <span id="summaryLaundryStyle">-</span></li>
                            <li><strong>Fabric Type:</strong> <span id="summaryFabricType">-</span></li>
                            <li><strong>Weight:</strong> <span id="summaryWeight">-</span> kg</li>
                            <li><strong>Additional Services:</strong> <span id="summaryAdditionalServices">-</span></li>
                            <li><strong>Pickup:</strong> <span id="summaryDate">-</span> at <span id="summaryTime">-</span></li>
                            <li><strong>Base Price:</strong> ₱<span id="summaryBasePrice">0</span></li>
                            <li><strong>Additional Charges:</strong> ₱<span id="summaryAdditionalCharges">0</span></li>
                            <li><strong>Total Price:</strong> ₱<span id="summaryTotalPrice">0</span></li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method</label>
                        <select name="payment_method" id="payment_method" class="form-select">
                            <option value="">-- Select --</option>
                            <option value="Cash">Cash</option>
                            <option value="GCash">GCash</option>
                            <option value="Bank">Bank</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="payment_reference" class="form-label">Payment Reference</label>
                        <input type="text" name="payment_reference" id="payment_reference" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="payment_status" class="form-label">Payment Status</label>
                        <select name="payment_status" id="payment_status" class="form-select">
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Create Order</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript --}}
<script>
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

    document.addEventListener('DOMContentLoaded', () => {
        updateSummary();

        const bookedDates = @json($bookedDates ?? []);
        const dateInput = document.querySelector('input[name="date"]');

        dateInput.addEventListener('input', function () {
            if (bookedDates.includes(this.value)) {
                alert('Sorry, this date is fully booked. Please choose another.');
                this.value = '';
            }
        });

        dateInput.setAttribute('min', new Date().toISOString().split('T')[0]);
    });

    document.getElementById('userSelect').addEventListener('change', function () {
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
</script>
@endsection
