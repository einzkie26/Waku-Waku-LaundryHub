<div class="card p-4 shadow-sm">
    <h5>Booking Summary</h5>
    <ul class="list-group mb-4">
        <li class="list-group-item"><strong>Service:</strong> {{ $service }}</li>
        <li class="list-group-item"><strong>Weight:</strong> {{ $weight }} kg</li>
        <li class="list-group-item"><strong>Date:</strong> {{ $date }}</li>
        <li class="list-group-item"><strong>Time:</strong> {{ $time }}</li>
    </ul>

    <a href="{{ route('booking.step1') }}" class="btn btn-secondary">Start Over</a>
</div>
