@extends('layouts.app')
@section('title', 'Create an account')
@section('content')
<div class="auth-shell">
    <div class="auth-copy"><div class="eyebrow">Make room for a lighter day</div><h1>A fresh start, one load at a time.</h1><p>Create your account so we know where to collect your laundry and how to reach you.</p><div class="notice mt-4">Your phone and address are used for pickup. You can update them in Profile settings.</div></div>
    <section class="surface auth-card"><h2>Join the fresh side.</h2><p class="text-muted small mb-4">A few details, then you are ready to book.</p>
        <form method="POST" action="{{ route('register') }}" data-single-submit>@csrf
            @foreach(['name' => ['Full name', 'name', 'text'], 'username' => ['Username', 'username', 'text'], 'phone' => ['Phone number', 'tel', 'tel']] as $field => [$label, $autocomplete, $type])
            <div class="mb-3"><label class="form-label" for="{{ $field }}">{{ $label }}</label><input class="form-control @error($field) is-invalid @enderror" id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" autocomplete="{{ $autocomplete }}" value="{{ old($field) }}" maxlength="{{ $field === 'phone' ? 20 : 255 }}" required></div>
            @endforeach
            <div class="mb-3"><label class="form-label" for="address">Pickup address</label><textarea class="form-control" id="address" name="address" autocomplete="street-address" maxlength="255" rows="2" placeholder="House / unit, street, barangay, city" required>{{ old('address') }}</textarea></div>
            <div class="mb-3"><label class="form-label" for="password">Password</label><div class="input-group"><input class="form-control" type="password" id="password" name="password" autocomplete="new-password" minlength="6" required><button class="btn btn-light" type="button" data-toggle-password="password" aria-pressed="false">Show</button></div><p class="form-text">Use at least 6 characters.</p></div>
            <div class="mb-4"><label class="form-label" for="password_confirmation">Confirm password</label><input class="form-control" type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" minlength="6" required></div>
            <button class="btn btn-primary" type="submit">Create account</button>
        </form>
        <p class="auth-footnote">Already have an account? <a href="{{ route('login.form') }}">Sign in</a></p>
    </section>
</div>
@endsection
