@extends('layouts.app')
@section('title', 'Profile settings')
@section('content')
<div class="container">
    <div class="page-heading"><div><div class="eyebrow">Make yourself at home</div><h1>Profile settings</h1><p>Keep your contact details current so pickup goes smoothly.</p></div><a href="{{ route('booking.create') }}">Back to booking</a></div>
    <div class="detail-grid">
        <section class="surface"><h2 class="mb-4">Your details</h2>
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" data-single-submit>@csrf @method('PUT')
                <div class="row g-3">
                    @foreach(['name' => ['Full name', 'text', 'name'], 'username' => ['Username', 'text', 'username'], 'phone' => ['Phone number', 'tel', 'tel']] as $field => [$label, $type, $autocomplete])
                    <div class="{{ $field === 'phone' ? 'col-12' : 'col-sm-6' }}"><label class="form-label" for="{{ $field }}">{{ $label }}</label><input class="form-control" id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" autocomplete="{{ $autocomplete }}" value="{{ old($field, auth()->user()->$field) }}" maxlength="{{ $field === 'phone' ? 20 : 255 }}" required></div>
                    @endforeach
                    <div class="col-12"><label class="form-label" for="address">Pickup address</label><textarea class="form-control" id="address" name="address" autocomplete="street-address" maxlength="255" required>{{ old('address', auth()->user()->address) }}</textarea></div>
                    <div class="col-12"><label class="form-label" for="photo">Profile photo <span class="text-muted fw-normal">(optional)</span></label><input class="form-control" type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/gif"><p class="form-text">JPG, PNG, or GIF. Maximum 2 MB.</p></div>
                    <div class="col-12"><hr><h3 class="h6">Change password</h3><p class="form-text">Leave both fields blank to keep your current password.</p></div>
                    <div class="col-sm-6"><label class="form-label" for="password">New password</label><input class="form-control" id="password" name="password" type="password" minlength="8" autocomplete="new-password"></div>
                    <div class="col-sm-6"><label class="form-label" for="password_confirmation">Confirm new password</label><input class="form-control" id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password"></div>
                </div>
                <button class="btn btn-primary mt-4" type="submit">Save changes</button>
            </form>
        </section>
        <aside class="notice"><div class="notice-title">Ready for pickup</div><p>Include your house or unit number, street, barangay, and city. Use a phone number our team can reach on pickup day.</p></aside>
    </div>
</div>
@endsection
