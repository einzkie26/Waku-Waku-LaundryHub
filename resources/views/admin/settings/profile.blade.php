@extends('layouts.admin-app')
@section('title', 'Account settings')
@section('content')
<div class="admin-heading"><div><h1>Account settings</h1></div></div>
<div class="admin-split-form">
    <section class="admin-panel"><div class="admin-panel-body"><div class="admin-profile-intro"><span class="admin-person-avatar admin-detail-avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span><div><h2>{{ auth()->user()->name }}</h2><p>Administrator · {{ auth()->user()->username }}</p></div></div>
        <form method="POST" action="{{ route('settings.profile.update') }}" data-single-submit>@csrf @method('PUT')
            <div class="row g-3">
                <div class="col-sm-6"><label class="form-label" for="name">Full name</label><input class="form-control" id="name" name="name" value="{{ old('name', auth()->user()->name) }}" maxlength="255" autocomplete="name" required></div>
                <div class="col-sm-6"><label class="form-label" for="username">Username</label><input class="form-control" id="username" name="username" value="{{ old('username', auth()->user()->username) }}" maxlength="255" autocomplete="username" required></div>
                <div class="col-12"><label class="form-label" for="phone">Phone number <span class="text-muted fw-normal">(optional)</span></label><input class="form-control" type="tel" id="phone" name="phone" value="{{ old('phone', auth()->user()->phone) }}" maxlength="20" autocomplete="tel"></div>
                <div class="col-12"><label class="form-label" for="address">Address <span class="text-muted fw-normal">(optional)</span></label><textarea class="form-control" id="address" name="address" maxlength="255" rows="2">{{ old('address', auth()->user()->address) }}</textarea></div>
                <div class="col-12"><hr><div class="admin-section-kicker">CHANGE PASSWORD</div><p class="form-text mb-0">Leave these fields blank to keep your current password.</p></div>
                <div class="col-12"><label class="form-label" for="current_password">Current password</label><input class="form-control" type="password" name="current_password" id="current_password" autocomplete="current-password"></div>
                <div class="col-sm-6"><label class="form-label" for="password">New password</label><input class="form-control" type="password" id="password" name="password" minlength="8" autocomplete="new-password"></div>
                <div class="col-sm-6"><label class="form-label" for="password_confirmation">Confirm new password</label><input class="form-control" type="password" id="password_confirmation" name="password_confirmation" minlength="8" autocomplete="new-password"></div>
                <div class="col-12"><p class="form-text mb-0">Use at least 8 characters for a new password.</p></div>
            </div>
            <button class="btn btn-primary mt-4" type="submit">Save changes</button>
        </form>
    </div></section>
    <aside class="admin-panel admin-actions-panel"><div class="admin-panel-body"><div class="admin-section-kicker">ADMINISTRATOR</div><h2 class="h5 mb-3">A clear view of the shop.</h2><p>Your account gives you access to orders, customer records, employee management, and business reports.</p><p class="mb-0">Use your username to sign in. After changing your password, use the new password for future sign-ins.</p></div></aside>
</div>
@endsection
