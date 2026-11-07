@extends('layouts.app')
@section('title', 'Staff sign in')
@section('content')
<div class="auth-shell">
    <div class="auth-copy"><div class="eyebrow">Waku-Waku staff workspace</div><h1>A smoother day starts with a clear queue.</h1><p>Manage pickups, update cleaning progress, and keep customer orders moving.</p><div class="notice mt-4">Use your staff email and password. Customers sign in with their username.</div></div>
    <section class="surface auth-card"><h2>Staff sign in</h2><p class="text-muted small mb-4">Welcome back to the shop.</p>
        <form method="POST" action="{{ route('staff.login') }}" data-single-submit>@csrf
            <div class="mb-3"><label class="form-label" for="email">Staff email</label><input class="form-control @error('email') is-invalid @enderror" type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus></div>
            <div class="mb-4"><label class="form-label" for="password">Password</label><div class="input-group"><input class="form-control" type="password" id="password" name="password" autocomplete="current-password" required><button class="btn btn-light" type="button" data-toggle-password="password" aria-pressed="false">Show</button></div></div>
            <button class="btn btn-primary" type="submit">Open staff workspace</button>
        </form>
        <p class="auth-footnote">Here for your laundry? <a href="{{ route('login.form') }}">Customer sign in</a></p>
    </section>
</div>
@endsection
