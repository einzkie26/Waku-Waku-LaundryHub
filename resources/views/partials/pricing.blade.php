<section id="pricing" class="py-3">
    <div class="container text-center">
        <h2 class="mb-2" data-aos="fade-up">Our Pricing</h2>
        <p class="mb-3" data-aos="fade-up" data-aos-delay="100">Affordable laundry services for every need!</p>
        <div class="row justify-content-center">
            @php
                $services = [
                    ['icon' => 'bi bi-droplet-half', 'title' => 'Wash & Fold', 'desc' => 'Complete laundry service charged per kg', 'price' => '50.00'],
                    ['icon' => 'bi bi-moisture', 'title' => 'Dry Cleaning', 'desc' => 'Professional care for delicate garments', 'price' => '70.00'],
                    ['icon' => 'bi-clock', 'title' => 'Express Service', 'desc' => 'Quick turnaround for urgent needs', 'price' => '100.00'],
                    ['icon' => 'bi bi-symmetry-horizontal', 'title' => 'Ironing Service', 'desc' => 'Professional pressing and ironing', 'price' => '40.00'],
                ];
            @endphp

            @foreach ($services as $index => $service)
                <div class="col-md-3 col-sm-6 mb-3" data-aos="fade-up" data-aos-delay="{{ ($index + 1) * 100 }}">
                    <div class="price-card p-3 h-100 shadow-sm border rounded">
                        <div class="text-center mb-2">
                            <i class="service-icon bi {{ $service['icon'] }} fs-1 text-primary"></i>
                        </div>
                        <h5 class="fw-bold">{{ $service['title'] }}</h5>
                        <p class="text-muted small">{{ $service['desc'] }}</p>
                        <p class="price fs-5 fw-semibold">₱{{ $service['price'] }}<small>/kg</small></p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="suggestions py-5 bg-dark text-white">
    <div class="container">
        <h2 class="text-center mb-3" data-aos="fade-up">Help Us Improve</h2>
        <p class="text-center mb-4" data-aos="fade-up" data-aos-delay="100">Your feedback helps us provide better service</p>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <form class="suggestion-form" data-aos="fade-up" data-aos-delay="200">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-light">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input type="text" class="form-control bg-dark text-white border-light" id="name" placeholder="Your name">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-light">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" class="form-control bg-dark text-white border-light" id="email" placeholder="Your email">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-light">
                                <i class="bi bi-tag"></i>
                            </span>
                            <select class="form-select bg-dark text-white border-light" id="category">
                                <option selected disabled>Select a category</option>
                                <option value="service">Service Quality</option>
                                <option value="pricing">Pricing</option>
                                <option value="delivery">Delivery</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="suggestion" class="form-label">Your Suggestion or Recommendation</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-light align-items-start pt-3">
                                <i class="bi bi-chat-left-text"></i>
                            </span>
                            <textarea class="form-control bg-dark text-white border-light" id="suggestion" rows="4" placeholder="Share your thoughts with us..."></textarea>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary px-5 py-2">
                            <i class="bi bi-send me-2"></i>Submit Feedback
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<style>
.price-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    background: white;
}
.price-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important;
}
.service-icon {
    transition: transform 0.3s ease;
}
.price-card:hover .service-icon {
    transform: scale(1.1);
}

.suggestions .form-control,
.suggestions .form-select,
.suggestions .input-group-text {
    transition: all 0.3s ease;
}

.suggestions .form-control:focus,
.suggestions .form-select:focus {
    background-color: #2c3034 !important;
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.suggestions .form-control::placeholder {
    color: rgba(255, 255, 255, 0.5);
}

.suggestions .btn-primary {
    background-color: #0d6efd;
    border: none;
    padding: 12px 35px;
    font-weight: 500;
    transition: all 0.3s ease;
    border-radius: 25px;
}

.suggestions .btn-primary:hover {
    background-color: #0b5ed7;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.suggestions .form-label {
    color: rgba(255, 255, 255, 0.9);
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.suggestions .input-group-text {
    color: rgba(255, 255, 255, 0.7);
}

.suggestions .form-select {
    cursor: pointer;
}

.suggestions .form-select option {
    background-color: #343a40;
    color: white;
}

@media (max-width: 768px) {
    .suggestions .btn-primary {
        width: 100%;
    }
}
</style>
