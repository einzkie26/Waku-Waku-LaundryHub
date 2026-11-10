@extends('layouts.staff-app')
@section('title', 'My profile')
@section('content')
<div class="staff-profile-page">
    <div class="page-heading"><div><div class="eyebrow">Your account</div><h1>My profile</h1></div><span class="staff-profile-status">{{ $employee->status }}</span></div>
    <form action="{{ route('staff.profile.update') }}" method="POST" enctype="multipart/form-data" data-staff-profile data-single-submit>
        @csrf
        <div class="staff-profile-grid">
            <aside class="staff-identity-card" aria-label="Profile photo and work details">
                <div class="staff-identity-cover" aria-hidden="true"><svg viewBox="0 0 58 70" fill="none"><rect x="3" y="3" width="52" height="64" rx="9" stroke="currentColor" stroke-width="2"/><path d="M4 18h50" stroke="currentColor"/><circle cx="29" cy="41" r="17" stroke="currentColor" stroke-width="2"/><path d="M16 42c7-8 15 8 26 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="13" cy="11" r="2" fill="currentColor"/><path d="M36 11h9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></div>
                <div class="staff-identity-content">
                    <div class="staff-photo-preview" data-photo-preview data-saved-photo="{{ $employee->profile_photo_url }}">
                        <span data-photo-initial @if($employee->profile_photo_url) hidden @endif>{{ mb_strtoupper(mb_substr($employee->name, 0, 1)) }}</span>
                        <img data-photo-image src="{{ $employee->profile_photo_url ?: asset('img/logo.png') }}" alt="Your profile photo" @if(!$employee->profile_photo_url) hidden @endif>
                        <span class="staff-photo-camera" aria-hidden="true"><i class="bi bi-camera"></i></span>
                    </div>
                    <h2>{{ $employee->name }}</h2><p class="staff-identity-position">{{ $employee->position }}</p>
                    <input class="staff-photo-input" type="file" name="photo" id="staff-photo" accept="image/jpeg,image/png,image/webp" aria-describedby="staff-photo-help staff-photo-feedback">
                    <label class="btn btn-light btn-sm staff-photo-upload" for="staff-photo"><i class="bi bi-upload me-2" aria-hidden="true"></i>{{ $employee->profile_photo_url ? 'Change photo' : 'Upload photo' }}</label>
                    <p class="staff-photo-help" id="staff-photo-help">JPG, PNG or WebP. Up to 3 MB, 64–4,096 pixels per side. Your photo updates when you save.</p>
                    @if($employee->profile_photo_path)<label class="staff-photo-remove"><input class="form-check-input mt-0" type="checkbox" name="remove_photo" value="1" data-remove-photo>Remove current photo</label>@endif
                    <p class="staff-photo-feedback" id="staff-photo-feedback" role="status" data-photo-feedback></p>
                    <dl class="staff-identity-meta"><div><dt>Position</dt><dd>{{ $employee->position }}</dd></div><div><dt>Shift</dt><dd>{{ $employee->shift }}</dd></div><div><dt>Joined</dt><dd>{{ $employee->hire_date?->format('M j, Y') }}</dd></div></dl>
                </div>
            </aside>
            <div class="staff-profile-fields">
                <section class="staff-profile-section" aria-labelledby="staff-contact-title">
                    <div class="staff-profile-section-heading"><i class="bi bi-person-vcard" aria-hidden="true"></i><h2 id="staff-contact-title">Personal details</h2></div>
                    <div class="staff-profile-form-grid">
                        <div class="staff-field-wide"><label for="staff-name" class="form-label">Full name</label><input class="form-control @error('name') is-invalid @enderror" id="staff-name" name="name" autocomplete="name" value="{{ old('name', $employee->name) }}" required maxlength="255">@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="staff-field-wide"><label for="staff-email" class="form-label">Work email</label><input class="form-control @error('email') is-invalid @enderror" type="email" id="staff-email" name="email" autocomplete="email" value="{{ old('email', $employee->email) }}" required maxlength="255"><div class="form-text">Use this email to sign in.</div>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="staff-field-wide"><label for="staff-contact" class="form-label">Phone number</label><input class="form-control @error('contact') is-invalid @enderror" type="tel" id="staff-contact" name="contact" autocomplete="tel" value="{{ old('contact', $employee->contact) }}" required maxlength="20">@error('contact')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    </div>
                </section>
                <details class="staff-profile-section staff-password-details" @if($errors->hasAny(['current_password', 'new_password'])) open @endif>
                    <summary><i class="bi bi-shield-lock" aria-hidden="true"></i><span><strong>Change password</strong><small>Only fill this in when you want a new password.</small></span><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                    <div class="staff-profile-form-grid">
                        <div class="staff-field-wide"><label class="form-label" for="staff-current-password">Current password</label><div class="staff-password-input"><input class="form-control" type="password" name="current_password" id="staff-current-password" autocomplete="current-password"><button type="button" data-toggle-password="staff-current-password" aria-pressed="false" aria-label="Show current password">Show</button></div></div>
                        <div><label class="form-label" for="staff-new-password">New password</label><div class="staff-password-input"><input class="form-control" type="password" name="new_password" id="staff-new-password" autocomplete="new-password" minlength="8"><button type="button" data-toggle-password="staff-new-password" aria-pressed="false" aria-label="Show new password">Show</button></div><div class="form-text">At least 8 characters.</div></div>
                        <div><label class="form-label" for="staff-confirm-password">Confirm password</label><div class="staff-password-input"><input class="form-control" type="password" name="new_password_confirmation" id="staff-confirm-password" autocomplete="new-password" minlength="8"><button type="button" data-toggle-password="staff-confirm-password" aria-pressed="false" aria-label="Show password confirmation">Show</button></div></div>
                    </div>
                </details>
                <div class="staff-profile-actions"><button class="btn btn-light" type="reset">Reset changes</button><button class="btn btn-primary" type="submit"><i class="bi bi-check2 me-2" aria-hidden="true"></i>Save changes</button></div>
            </div>
        </div>
    </form>
</div>
@endsection
@push('scripts')
<script src="{{ asset('js/staff-profile.js') }}?v={{ filemtime(public_path('js/staff-profile.js')) }}" defer></script>
@endpush
