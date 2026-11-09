@extends('layouts.app')
@section('title', 'Sign in')
@section('content')
<div class="auth-shell">
    <div class="auth-copy"><h1>Welcome to Waku-Waku.</h1><p>One sign-in for customers, employees, and administrators.</p></div>
    <section class="surface auth-card"><h2>Sign in</h2><p class="text-muted small mb-4" id="login-identity-help">Customers and administrators use a username. Employees use their staff email.</p>
        <form method="POST" action="{{ route('login') }}" data-single-submit>@csrf
            <div class="mb-3"><label class="form-label" for="username">Username or staff email</label><input class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username') }}" maxlength="255" autocomplete="username" aria-describedby="login-identity-help" required autofocus></div>
            <div class="mb-3"><label class="form-label" for="password">Password</label><div class="input-group"><input class="form-control" type="password" id="password" name="password" autocomplete="current-password" required><button class="btn btn-light w-auto" type="button" data-toggle-password="password" aria-pressed="false">Show</button></div></div>
            <label class="small d-flex gap-2 align-items-center mb-4"><input type="checkbox" class="form-check-input mt-0" name="remember" value="1" @checked(old('remember'))>Keep me signed in</label>
            <button class="btn btn-primary" type="submit">Sign in</button>
        </form>
        <p class="auth-footnote">New customer? <a href="{{ route('register.form') }}">Create an account</a></p>
    </section>
</div>
@endsection
