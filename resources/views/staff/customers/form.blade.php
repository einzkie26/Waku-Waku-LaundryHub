<div class="detail-grid">
    <section class="surface"><h2 class="mb-4">{{ isset($customer) ? 'Customer details' : 'Contact & sign-in details' }}</h2>
        <form method="POST" action="{{ isset($customer) ? route('staff.customers.update', $customer->id) : route('staff.customers.store') }}" data-single-submit>@csrf @if(isset($customer)) @method('PUT') @endif
            <div class="row g-3">
                @foreach(['name' => ['Full name', 'text'], 'username' => ['Username', 'text'], 'phone' => ['Phone number', 'tel']] as $field => [$label, $type])
                <div class="{{ $field === 'phone' ? 'col-12' : 'col-sm-6' }}"><label class="form-label" for="{{ $field }}">{{ $label }}</label><input class="form-control" id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" value="{{ old($field, $customer?->$field ?? '') }}" maxlength="{{ $field === 'phone' ? 20 : 255 }}" required></div>
                @endforeach
                <div class="col-12"><label class="form-label" for="address">Pickup address</label><textarea class="form-control" id="address" name="address" maxlength="255" rows="3" required>{{ old('address', $customer->address ?? '') }}</textarea></div>
                @unless(isset($customer))
                <div class="col-sm-6"><label class="form-label" for="password">Sign-in password</label><input class="form-control" type="password" id="password" name="password" minlength="8" autocomplete="new-password" required></div>
                <div class="col-sm-6"><label class="form-label" for="password_confirmation">Confirm password</label><input class="form-control" type="password" id="password_confirmation" name="password_confirmation" minlength="8" autocomplete="new-password" required></div>
                <div class="col-12"><p class="form-text mb-0">Set sign-in details with the customer. Use at least 8 characters for the password.</p></div>
                @endunless
            </div>
            <div class="d-flex gap-2 mt-4"><button class="btn btn-primary" type="submit">{{ isset($customer) ? 'Save changes' : 'Add & continue to order' }}</button><a class="btn btn-light" href="{{ route('staff.customers') }}">Cancel</a></div>
        </form>
    </section>
    <aside class="notice"><div class="notice-title">A smoother pickup starts here.</div><p>Check the phone number and full address with the customer. Their account lets them track orders using their username.</p></aside>
</div>
