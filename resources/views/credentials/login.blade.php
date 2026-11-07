@extends('layouts.app')
@section('title', 'Sign in')
@section('content')
<div class="auth-shell">
    <div class="auth-copy"><div class="eyebrow">Welcome back</div><h1>Your next fresh start is right here.</h1><p>Sign in to book a pickup and follow your laundry from collection to delivery.</p><div class="notice mt-4">One account for your bookings, order updates, and previous loads.</div></div>
    <section class="surface auth-card"><h2>Good to see you.</h2><p class="text-muted small mb-4">Sign in to your customer account.</p>
        <form method="POST" action="{{ route('login') }}" data-single-submit>@csrf
            <div class="mb-3"><label class="form-label" for="username">Username</label><input class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username') }}" autocomplete="username" required autofocus></div>
            <div class="mb-3"><label class="form-label" for="password">Password</label><div class="input-group"><input class="form-control" type="password" id="password" name="password" autocomplete="current-password" required><button class="btn btn-light" type="button" data-toggle-password="password" aria-pressed="false">Show</button></div></div>
            <label class="small d-flex gap-2 align-items-center mb-4"><input type="checkbox" class="form-check-input mt-0" name="remember" value="1" @checked(old('remember'))>Keep me signed in</label>
            <button class="btn btn-primary" type="submit">Sign in</button>
        </form>
        <p class="auth-footnote">New to Waku-Waku? <a href="{{ route('register.form') }}">Create an account</a></p><p class="auth-footnote">Working at the shop? <a href="{{ route('staff.login.form') }}">Staff sign in</a></p>
    </section>
</div>
@endsection
