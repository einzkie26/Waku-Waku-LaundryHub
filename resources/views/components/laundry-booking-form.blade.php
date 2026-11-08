@php
    use App\Support\LaundryWorkflow;
    $modal = $modal ?? false;
    $bookingErrorBag = $bookingErrorBag ?? 'default';
    $bookingErrors = $errors->getBag($bookingErrorBag);
    $bookingOld = fn ($key, $default = null) => $bookingErrors->any() ? old($key, $default) : $default;
    $selectedService = $bookingOld('service', $modal ? 'Wash & Fold' : request('service', 'Wash & Fold'));
    if (!is_string($selectedService) || !array_key_exists($selectedService, LaundryWorkflow::SERVICES)) $selectedService = 'Wash & Fold';
    $selectedExtras = array_values(array_filter((array) $bookingOld('additional_services', []), 'is_string'));
    $initialWeight = is_numeric($bookingOld('weight')) ? (float) $bookingOld('weight') : 0;
    $initialBase = LaundryWorkflow::SERVICES[$selectedService]['price'] * $initialWeight;
    $initialExtras = array_sum(array_intersect_key(LaundryWorkflow::EXTRAS, array_flip($selectedExtras)));
@endphp
<form method="POST" action="{{ $action }}" class="booking-grid" id="laundry-booking" data-single-submit>
    @csrf
    @if($modal)
        <input type="hidden" name="_admin_modal" value="order-create">
        <input type="hidden" name="_return_to" value="{{ \App\Support\AdminModal::pageUrl(request()) }}">
    @endif
    <div>
        <section class="surface booking-section" aria-labelledby="choose-laundry">
            <div class="step-heading"><span class="step-number">01</span><div><h2 id="choose-laundry">{{ $operator ? 'Customer & laundry' : 'Choose your laundry' }}</h2>@unless($modal)<p>All service prices are per kilogram.</p>@endunless</div></div>
            @if($operator)
                <div class="mb-4">
                    <label class="form-label" for="user_id">Customer</label>
                    <select class="form-select @error('user_id', $bookingErrorBag) is-invalid @enderror" name="user_id" id="user_id" data-modal-initial-focus required>
                        <option value="">Choose a customer</option>
                        @foreach($customers as $customer)<option value="{{ $customer->id }}" data-name="{{ $customer->name }}" data-phone="{{ $customer->phone }}" data-address="{{ $customer->address }}" @selected($bookingOld('user_id', request('customer')) == $customer->id)>{{ $customer->name }} · {{ $customer->phone }}</option>@endforeach
                    </select>
                    <p class="form-text">New customer? <a href="{{ $customerCreateUrl ?? route('staff.customers.create') }}">Add their details first</a>, then return to this form.</p>
                </div>
            @endif
            <fieldset>
                <legend class="form-label">Laundry service</legend>
                <div class="service-choices">
                    @foreach(LaundryWorkflow::SERVICES as $name => $service)
                        <label class="service-choice">
                            <input type="radio" name="service" value="{{ $name }}" @checked($selectedService === $name) required>
                            <div class="service-choice-body"><strong>{{ $name }}</strong><span>₱{{ number_format($service['price'], 2) }} / kg</span></div>
                        </label>
                    @endforeach
                </div>
            </fieldset>
            <div class="mt-4">
                <label for="weight" class="form-label">Laundry weight</label>
                <div class="input-group"><input class="form-control @error('weight', $bookingErrorBag) is-invalid @enderror" type="number" min="0.1" max="1000" step="0.1" name="weight" id="weight" value="{{ $bookingOld('weight') }}" placeholder="e.g. 5" aria-describedby="weight-help" required><span class="input-group-text bg-white">kg</span></div>
                <p id="weight-help" class="form-text">Enter the weight to calculate your total. Decimal weights such as 2.5 kg are accepted.</p>
            </div>
            <details class="care-details" @if($bookingErrors->has('laundry_style') || $bookingErrors->has('fabric_type') || count($selectedExtras)) open @endif>
                <summary>Garment care & optional extras</summary>
                <div class="row g-3">
                    <div class="col-sm-6"><label class="form-label" for="laundry_style">Wash preference</label><select class="form-select" id="laundry_style" name="laundry_style">
                        @foreach(LaundryWorkflow::STYLES as $style)<option @selected($bookingOld('laundry_style', 'Regular Wash') === $style)>{{ $style }}</option>@endforeach
                    </select></div>
                    <div class="col-sm-6"><label class="form-label" for="fabric_type">Fabric type</label><select class="form-select" id="fabric_type" name="fabric_type">
                        @foreach(LaundryWorkflow::FABRICS as $fabric)<option @selected($bookingOld('fabric_type', 'Mixed') === $fabric)>{{ $fabric }}</option>@endforeach
                    </select></div>
                </div>
                <p class="form-text">Not sure about the fabric? Choose Mixed and add any care-label instructions below.</p>
                <fieldset class="mt-3"><legend class="form-label">Optional extras <span class="text-muted fw-normal">· charged once per order</span></legend>
                    <div class="extras-grid">
                        @foreach(LaundryWorkflow::EXTRAS as $name => $price)
                        <label class="extra-choice" data-extra-option="{{ $name }}"><input type="checkbox" class="form-check-input mt-0" name="additional_services[]" value="{{ $name }}" @checked(in_array($name, $selectedExtras))>{{ $name }}<span>+₱{{ number_format($price, 0) }}</span></label>
                        @endforeach
                    </div>
                </fieldset>
            </details>
            <div class="mt-4"><label class="form-label" for="special_instructions">Anything we should know? <span class="text-muted fw-normal">(optional)</span></label><textarea class="form-control" id="special_instructions" name="special_instructions" maxlength="1000" rows="2" placeholder="Care instructions, stains, or pickup directions">{{ $bookingOld('special_instructions') }}</textarea></div>
        </section>
        <section class="surface booking-section" aria-labelledby="schedule-pickup">
            <div class="step-heading"><span class="step-number">02</span><div><h2 id="schedule-pickup">Schedule the pickup</h2>@unless($modal)<p>This is when we collect the laundry, before cleaning.</p>@endunless</div></div>
            @if($operator)
                <div class="pickup-contact"><strong data-customer-name>Choose a customer above</strong><p data-customer-phone></p><p data-customer-address></p></div>
            @else
                <div class="d-flex justify-content-between gap-3"><span class="form-label">Pickup contact & address</span><a class="small" href="{{ route('profile.settings') }}">Edit profile</a></div>
                <div class="pickup-contact"><strong>{{ auth()->user()->name }}</strong><p>{{ auth()->user()->phone ?: 'Phone number missing — add it in Profile settings.' }}</p><p>{{ auth()->user()->address ?: 'Pickup address missing — add it in Profile settings.' }}</p></div>
            @endif
            <div class="row g-3">
                <div class="col-sm-6"><label class="form-label" for="date">Pickup date</label><input class="form-control @error('date', $bookingErrorBag) is-invalid @enderror" type="date" id="date" name="date" min="{{ today()->toDateString() }}" value="{{ $bookingOld('date') }}" required></div>
                <div class="col-sm-6"><label class="form-label" for="time">Pickup time</label><select class="form-select @error('time', $bookingErrorBag) is-invalid @enderror" id="time" name="time" data-today="{{ today()->toDateString() }}" data-current-time="{{ now()->format('H:i') }}" required><option value="">Choose a time</option>@foreach(LaundryWorkflow::TIMES as $value => $label)<option value="{{ $value }}" @selected($bookingOld('time') === $value)>{{ $label }}</option>@endforeach</select></div>
            </div>
            <p class="form-text mt-3 mb-0">Keep your laundry ready at this address. Delivery is arranged after cleaning; this time is for pickup only.</p>
        </section>
    </div>
    <aside class="surface summary-card" aria-labelledby="review-heading">
        <div class="step-heading"><span class="step-number">03</span><div><h2 id="review-heading">Review & {{ $operator ? 'create' : 'confirm' }}</h2>@unless($modal)<p>Your full price, in one place.</p>@endunless</div></div>
        <div class="summary-heading"><strong data-summary-service>{{ $selectedService }}</strong><p data-summary-weight>{{ $initialWeight ? $initialWeight.' kg of laundry' : 'Add a weight to see your total' }}</p></div>
        <div class="summary-line"><span>Service subtotal</span><strong data-summary-base>₱{{ number_format($initialBase, 2) }}</strong></div>
        <div class="summary-line"><span>Optional extras</span><strong data-summary-extras-total>₱{{ number_format($initialExtras, 2) }}</strong></div>
        <ul class="summary-extras" data-summary-extras>@foreach($selectedExtras as $extra)<li>{{ $extra }}</li>@endforeach</ul>
        <div class="summary-line summary-total"><span>Total</span><strong data-summary-total aria-live="polite" aria-atomic="true">₱{{ number_format($initialBase + $initialExtras, 2) }}</strong></div>
        <div class="summary-line mt-4"><span>Pickup</span><strong class="text-end" data-summary-pickup>{{ $bookingOld('date') && $bookingOld('time') ? $bookingOld('date').' · '.$bookingOld('time') : 'Choose a date & time' }}</strong></div>
        @if($operator)
            <hr>
            <label class="form-label" for="payment_status">Has payment been received?</label>
            <select name="payment_status" id="payment_status" class="form-select"><option value="pending" @selected($bookingOld('payment_status', 'pending') === 'pending')>No — payment still due</option><option value="paid" @selected($bookingOld('payment_status') === 'paid')>Yes — record payment received</option></select>
            <div data-payment-fields class="mt-3">
                <label class="form-label" for="payment_method">Payment method</label>
                <select class="form-select mb-3" id="payment_method" name="payment_method"><option value="">Choose method</option>@foreach(['Cash' => 'Cash', 'GCash' => 'GCash', 'Bank' => 'Bank transfer'] as $value => $label)<option value="{{ $value }}" @selected($bookingOld('payment_method') === $value)>{{ $label }}</option>@endforeach</select>
                <label class="form-label" for="payment_reference">Payment reference <span class="text-muted fw-normal">(for GCash / bank)</span></label><input class="form-control" id="payment_reference" name="payment_reference" maxlength="100" value="{{ $bookingOld('payment_reference') }}">
            </div>
            <p class="form-text mt-3">Record money already received. Creating an order does not collect an online payment.</p>
        @else
            <div class="notice mt-4"><div class="notice-title">No online payment needed</div><p>Payment starts as unpaid. Our team records it when received, and you can check it in My orders.</p></div>
        @endif
        @unless($modal)
        <button class="btn btn-primary w-100 mt-4" type="submit">{{ $operator ? 'Create order' : 'Confirm booking' }} <i class="bi bi-arrow-right ms-2" aria-hidden="true"></i></button>
        <a href="{{ $cancelUrl ?? ($operator ? route('staff.orders.index') : route('customer.orders.history')) }}" class="btn btn-light w-100 mt-2">Cancel</a>
        @endunless
        <noscript><p class="form-text mt-3">Your final total is calculated securely when you confirm. Enable JavaScript for a live price preview.</p></noscript>
    </aside>
</form>
<script type="application/json" id="laundry-catalog">@json(['services' => LaundryWorkflow::SERVICES, 'extras' => LaundryWorkflow::EXTRAS])</script>
