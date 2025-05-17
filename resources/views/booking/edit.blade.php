@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3 class="mb-4">Edit Booking</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('bookings.update', $booking->id) }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="service" class="form-label">Service</label>
            <select name="service" id="service" class="form-select" required>
                <option value="Wash & Fold" data-price="50.00" {{ $booking->service == 'Wash & Fold' ? 'selected' : '' }}>Wash & Fold</option>
                <option value="Dry Cleaning" data-price="70.99" {{ $booking->service == 'Dry Cleaning' ? 'selected' : '' }}>Dry Cleaning</option>
                <option value="Express Service" data-price="100.99" {{ $booking->service == 'Express Service' ? 'selected' : '' }}>Express Service</option>
                <option value="Ironing" data-price="40.99" {{ $booking->service == 'Ironing' ? 'selected' : '' }}>Ironing</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="weight" class="form-label">Weight (kg)</label>
            <input type="number" class="form-control" name="weight" id="weight" value="{{ $booking->weight }}" required min="1">
        </div>

        <div class="mb-3">
            <label for="date" class="form-label">Date</label>
            <input type="date" class="form-control" name="date" id="date" value="{{ $booking->date }}" required>
        </div>

        <div class="mb-3">
            <label for="time" class="form-label">Time</label>
            <input type="time" class="form-control" name="time" id="time" value="{{ $booking->time }}" required>
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" class="form-control" name="phone" id="phone" value="{{ $booking->phone }}" required pattern="\d{11}" maxlength="11">
        </div>

        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <textarea class="form-control" name="address" id="address" required>{{ $booking->address }}</textarea>
        </div>

        <div class="mb-3">
            <label for="total_price" class="form-label">Total Price</label>
            <input type="text" class="form-control" id="total_price" disabled value="₱{{ $booking->total_price }}">
        </div>

        <button type="submit" class="btn btn-success">Update Booking</button>
        <a href="{{ route('landing') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script>
    function updateTotalPrice() {
        const weight = parseFloat(document.getElementById('weight').value) || 0;
        const service = document.getElementById('service');
        const pricePerKg = parseFloat(service.options[service.selectedIndex].dataset.price) || 0;
        const total = weight * pricePerKg;
        document.getElementById('total_price').value = '₱' + total.toFixed(2);
    }

    document.getElementById('weight').addEventListener('input', updateTotalPrice);
    document.getElementById('service').addEventListener('change', updateTotalPrice);

    document.addEventListener('DOMContentLoaded', updateTotalPrice);
</script>
@endsection
