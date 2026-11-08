@php
    $customerRecord = $customerRecord ?? null;
    $customerFieldPrefix = $customerFieldPrefix ?? 'customer';
    $customerErrorBag = $customerErrorBag ?? 'default';
    $customerOld = fn ($key, $default = '') => $errors->getBag($customerErrorBag)->any() ? old($key, $default) : $default;
@endphp
<div class="row g-3">
    <div class="col-sm-6">
        <label class="form-label" for="{{ $customerFieldPrefix }}-name">Full name</label>
        <input class="form-control @error('name', $customerErrorBag) is-invalid @enderror" name="name" id="{{ $customerFieldPrefix }}-name" value="{{ $customerOld('name', $customerRecord?->name ?? '') }}" maxlength="255" autocomplete="name" data-modal-initial-focus required>
        @error('name', $customerErrorBag)<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-sm-6">
        <label class="form-label" for="{{ $customerFieldPrefix }}-username">Username</label>
        <input class="form-control @error('username', $customerErrorBag) is-invalid @enderror" name="username" id="{{ $customerFieldPrefix }}-username" value="{{ $customerOld('username', $customerRecord?->username ?? '') }}" maxlength="255" autocomplete="off" required>
        @error('username', $customerErrorBag)<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label" for="{{ $customerFieldPrefix }}-phone">Phone number</label>
        <input class="form-control @error('phone', $customerErrorBag) is-invalid @enderror" type="tel" name="phone" id="{{ $customerFieldPrefix }}-phone" value="{{ $customerOld('phone', $customerRecord?->phone ?? '') }}" maxlength="20" autocomplete="tel" required>
        @error('phone', $customerErrorBag)<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label" for="{{ $customerFieldPrefix }}-address">Pickup address</label>
        <textarea class="form-control @error('address', $customerErrorBag) is-invalid @enderror" name="address" id="{{ $customerFieldPrefix }}-address" maxlength="255" rows="3" autocomplete="street-address" required>{{ $customerOld('address', $customerRecord?->address ?? '') }}</textarea>
        @error('address', $customerErrorBag)<div class="invalid-feedback">{{ $message }}</div>@enderror
        <p class="form-text">Include the house or unit number, street, barangay, and city.</p>
    </div>
    @unless($customerRecord)
        <div class="col-12"><hr><h3 class="h6">Customer sign-in</h3></div>
        <div class="col-sm-6">
            <label class="form-label" for="{{ $customerFieldPrefix }}-password">Password</label>
            <input class="form-control @error('password', $customerErrorBag) is-invalid @enderror" type="password" id="{{ $customerFieldPrefix }}-password" name="password" minlength="8" autocomplete="new-password" required>
            @error('password', $customerErrorBag)<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-sm-6">
            <label class="form-label" for="{{ $customerFieldPrefix }}-password-confirmation">Confirm password</label>
            <input class="form-control" type="password" id="{{ $customerFieldPrefix }}-password-confirmation" name="password_confirmation" minlength="8" autocomplete="new-password" required>
        </div>
        <div class="col-12"><p class="form-text mb-0">Use at least 8 characters.</p></div>
    @endunless
</div>
