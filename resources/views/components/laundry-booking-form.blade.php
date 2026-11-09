@php
    use App\Support\LaundryWorkflow;
    use App\Support\LaundryProducts;
    $modal = $modal ?? false;
    $bookingErrorBag = $bookingErrorBag ?? 'default';
    $bookingErrors = $errors->getBag($bookingErrorBag);
    $bookingOld = fn ($key, $default = null) => $bookingErrors->any() ? old($key, $default) : $default;
    $selectedService = $bookingOld('service', $modal ? 'Wash & Fold' : request('service', 'Wash & Fold'));
    if (!is_string($selectedService) || !isset(LaundryWorkflow::SERVICES[$selectedService])) $selectedService = 'Wash & Fold';
    $selectedExtras = array_values(array_intersect(array_filter((array) $bookingOld('additional_services', []), 'is_string'), array_keys(LaundryWorkflow::EXTRAS)));
    $initialWeight = is_numeric($bookingOld('weight')) ? max(0, (float) $bookingOld('weight')) : 0;
    $initialBase = LaundryWorkflow::SERVICES[$selectedService]['price'] * $initialWeight;
    $initialExtras = array_sum(array_intersect_key(LaundryWorkflow::EXTRAS, array_flip($selectedExtras)));
    try {
        $initialProducts = LaundryProducts::quote(['service' => $selectedService, 'detergent_brand' => $bookingOld('detergent_brand'), 'detergent_sachets' => $bookingOld('detergent_sachets', 0), 'softener_brand' => $bookingOld('softener_brand'), 'softener_cups' => $bookingOld('softener_cups', 0)]);
    } catch (\Illuminate\Validation\ValidationException $exception) {
        $initialProducts = ['items' => [], 'amount_cents' => 0];
    }
    $initialDetergent = collect($initialProducts['items'])->firstWhere('category', 'detergent');
    $initialSoftener = collect($initialProducts['items'])->firstWhere('category', 'softener');
@endphp
@once
    @push('styles')<link href="{{ asset('css/booking.css') }}?v={{ filemtime(public_path('css/booking.css')) }}" rel="stylesheet">@endpush
    @push('scripts')<script src="{{ asset('js/booking.js') }}?v={{ filemtime(public_path('js/booking.js')) }}" defer></script>@endpush
@endonce
<form method="POST" action="{{ $action }}" class="booking-grid laundry-booking" id="laundry-booking" data-single-submit>
    @csrf
    @if($modal)<input type="hidden" name="_admin_modal" value="order-create"><input type="hidden" name="_return_to" value="{{ \App\Support\AdminModal::pageUrl(request()) }}">@endif
    <div>
        <section class="surface booking-section" aria-labelledby="choose-laundry">
            <div class="booking-section-title"><span class="step-number">01</span><h2 id="choose-laundry">{{ $operator ? 'Customer & service' : 'Service & weight' }}</h2></div>
            @if($operator)
                <div class="mb-4"><label class="form-label" for="user_id">Customer</label><select class="form-select @error('user_id', $bookingErrorBag) is-invalid @enderror" name="user_id" id="user_id" data-modal-initial-focus required><option value="">Choose a customer</option>@foreach($customers as $customer)<option value="{{ $customer->id }}" data-name="{{ $customer->name }}" data-phone="{{ $customer->phone }}" data-address="{{ $customer->address }}" @selected($bookingOld('user_id', request('customer')) == $customer->id)>{{ $customer->name }} · {{ $customer->phone }}</option>@endforeach</select>@error('user_id', $bookingErrorBag)<div class="invalid-feedback">{{ $message }}</div>@enderror<p class="form-text">New customer? <a href="{{ $customerCreateUrl ?? route('staff.customers.create') }}">Add customer</a></p></div>
            @endif
            <fieldset><legend class="form-label">Laundry service</legend><div class="service-choices">
                @foreach(LaundryWorkflow::SERVICES as $name => $service)<label class="service-choice"><input type="radio" name="service" value="{{ $name }}" @checked($selectedService === $name) required><div class="service-choice-body"><strong>{{ $name }}</strong><span>₱{{ number_format($service['price'], 2) }} / kg</span></div></label>@endforeach
            </div></fieldset>
            <div class="weight-field"><label class="form-label" for="weight">Laundry weight</label><div class="input-group"><input class="form-control @error('weight', $bookingErrorBag) is-invalid @enderror" type="number" min="0.1" max="1000" step="0.1" name="weight" id="weight" value="{{ $bookingOld('weight') }}" placeholder="e.g. 2.5" required><span class="input-group-text bg-white">kg</span></div>@error('weight', $bookingErrorBag)<p class="text-danger small mt-2 mb-0">{{ $message }}</p>@enderror</div>
            <details class="care-details" @if($bookingErrors->has('laundry_style') || $bookingErrors->has('fabric_type')) open @endif><summary>Wash preferences & instructions</summary><div class="row g-3"><div class="col-sm-6"><label class="form-label" for="laundry_style">Wash preference</label><select class="form-select" id="laundry_style" name="laundry_style">@foreach(LaundryWorkflow::STYLES as $style)<option @selected($bookingOld('laundry_style', 'Regular Wash') === $style)>{{ $style }}</option>@endforeach</select></div><div class="col-sm-6"><label class="form-label" for="fabric_type">Fabric type</label><select class="form-select" id="fabric_type" name="fabric_type">@foreach(LaundryWorkflow::FABRICS as $fabric)<option @selected($bookingOld('fabric_type', 'Mixed') === $fabric)>{{ $fabric }}</option>@endforeach</select></div><div class="col-12"><label class="form-label" for="special_instructions">Special instructions <span class="text-muted fw-normal">(optional)</span></label><textarea class="form-control" name="special_instructions" id="special_instructions" maxlength="1000" rows="2" placeholder="Care labels, stains, or pickup directions">{{ $bookingOld('special_instructions') }}</textarea></div></div></details>
        </section>
        <section class="surface booking-section" aria-labelledby="products-heading">
            <div class="booking-section-title"><span class="step-number">02</span><h2 id="products-heading" data-products-title>Products & extras</h2></div>
            @include('components.laundry-products')
            @error('products', $bookingErrorBag)<p class="text-danger small">{{ $message }}</p>@enderror
            <fieldset class="booking-extras"><legend class="form-label">Optional extras</legend><div class="extras-grid">
                @foreach(LaundryWorkflow::EXTRAS as $name => $price)<label class="extra-choice" data-extra-option="{{ $name }}"><input class="form-check-input mt-0" type="checkbox" name="additional_services[]" value="{{ $name }}" @checked(in_array($name, $selectedExtras))><i class="bi bi-{{ ['Scent Boost' => 'stars', 'Hang Dry' => 'wind', 'With Packaging' => 'box-seam'][$name] }}" aria-hidden="true"></i><strong>{{ $name }}</strong><span>+₱{{ number_format($price, 0) }}</span></label>@endforeach
            </div></fieldset>
        </section>
        <section class="surface booking-section" aria-labelledby="schedule-pickup">
            <div class="booking-section-title"><span class="step-number">03</span><h2 id="schedule-pickup">Pickup</h2></div>
            @if($operator)<div class="pickup-contact"><strong data-customer-name>Choose a customer</strong><p data-customer-phone></p><p data-customer-address></p></div>
            @else<div class="d-flex justify-content-between gap-3"><span class="form-label">Pickup contact</span><a class="small" href="{{ route('profile.settings') }}">Edit</a></div><div class="pickup-contact"><strong>{{ auth()->user()->name }}</strong><p>{{ auth()->user()->phone ?: 'Add your phone number in profile settings.' }}</p><p>{{ auth()->user()->address ?: 'Add your pickup address in profile settings.' }}</p></div>@endif
            <div class="row g-3"><div class="col-sm-6"><label class="form-label" for="date">Pickup date</label><input class="form-control @error('date', $bookingErrorBag) is-invalid @enderror" type="date" name="date" id="date" min="{{ today()->toDateString() }}" value="{{ $bookingOld('date') }}" required>@error('date', $bookingErrorBag)<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-sm-6"><label class="form-label" for="time">Pickup time</label><select class="form-select @error('time', $bookingErrorBag) is-invalid @enderror" id="time" name="time" data-today="{{ today()->toDateString() }}" data-current-time="{{ now()->format('H:i') }}" required><option value="">Choose a time</option>@foreach(LaundryWorkflow::TIMES as $value => $label)<option value="{{ $value }}" @selected($bookingOld('time') === $value)>{{ $label }}</option>@endforeach</select>@error('time', $bookingErrorBag)<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
        </section>
    </div>
    <aside class="surface summary-card booking-review" aria-labelledby="review-heading">
        <div class="booking-section-title"><span class="step-number">04</span><h2 id="review-heading">Review {{ $operator ? 'order' : 'booking' }}</h2></div>
        <div class="summary-heading"><strong data-summary-service>{{ $selectedService }}</strong><p data-summary-weight>{{ $initialWeight ? $initialWeight.' kg' : 'Enter your laundry weight' }}</p></div>
        <div class="summary-line"><span>Service fee</span><strong data-summary-base>₱{{ number_format($initialBase, 2) }}</strong></div>
        <div class="summary-line" data-product-summary-row><span>Detergent<small data-summary-detergent-detail>{{ $initialDetergent ? $initialDetergent['name'].' · '.$initialDetergent['quantity'].' sachets' : 'Choose a detergent' }}</small></span><strong data-summary-detergent>₱{{ number_format(($initialDetergent['amount_cents'] ?? 0) / 100, 2) }}</strong></div>
        <div class="summary-line" data-product-summary-row><span>Fabric softener<small data-summary-softener-detail>{{ $initialSoftener ? $initialSoftener['name'].' · '.$initialSoftener['volume_ml'].' mL' : 'No softener' }}</small></span><strong data-summary-softener>₱{{ number_format(($initialSoftener['amount_cents'] ?? 0) / 100, 2) }}</strong></div>
        <div class="summary-line"><span>Optional extras</span><strong data-summary-extras-total>₱{{ number_format($initialExtras, 2) }}</strong></div>
        <ul class="booking-summary-extras" data-summary-extras>@foreach($selectedExtras as $extra)<li><span>{{ $extra }}</span><span>₱{{ number_format(LaundryWorkflow::EXTRAS[$extra], 2) }}</span></li>@endforeach</ul>
        <div class="summary-line summary-total"><span>Total</span><strong data-summary-total aria-live="polite" aria-atomic="true">₱{{ number_format($initialBase + $initialExtras + $initialProducts['amount_cents'] / 100, 2) }}</strong></div>
        <div class="summary-line mt-4"><span>Pickup</span><strong class="text-end" data-summary-pickup>Choose date & time</strong></div>
        @if($operator)
            <div class="payment-fields"><hr><label class="form-label" for="payment_status">Payment</label><select class="form-select" name="payment_status" id="payment_status"><option value="pending" @selected($bookingOld('payment_status', 'pending') === 'pending')>Payment still due</option><option value="paid" @selected($bookingOld('payment_status') === 'paid')>Payment received</option></select><div data-payment-fields class="mt-3"><label class="form-label" for="payment_method">Payment method</label><select class="form-select mb-3" name="payment_method" id="payment_method"><option value="">Choose method</option>@foreach(['Cash' => 'Cash', 'GCash' => 'GCash', 'Bank' => 'Bank transfer'] as $value => $label)<option value="{{ $value }}" @selected($bookingOld('payment_method') === $value)>{{ $label }}</option>@endforeach</select><label class="form-label" for="payment_reference">Payment reference</label><input class="form-control" id="payment_reference" name="payment_reference" maxlength="100" value="{{ $bookingOld('payment_reference') }}"></div></div>
        @else<p class="booking-payment-note">No online charge. Payment is recorded when received.</p>@endif
        @unless($modal)<button class="btn btn-primary w-100 mt-4" type="submit">{{ $operator ? 'Create order' : 'Confirm pickup' }} <i class="bi bi-arrow-right ms-2" aria-hidden="true"></i></button><a class="btn btn-light w-100 mt-2" href="{{ $cancelUrl ?? ($operator ? route('staff.orders.index') : route('customer.orders.history')) }}">Cancel</a>@endunless
        <noscript><p class="form-text mt-3">Enable JavaScript to adjust the measuring cup and preview your full price before confirming.</p></noscript>
    </aside>
</form>
<script type="application/json" id="laundry-catalog">@json(['services' => LaundryWorkflow::SERVICES, 'extras' => LaundryWorkflow::EXTRAS, 'products' => LaundryProducts::catalog()])</script>
