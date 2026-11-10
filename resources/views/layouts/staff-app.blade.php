<!DOCTYPE html>
<html lang="en" data-staff-nav-mode="sidebar">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Staff workspace') · Waku-Waku Laundryhub</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/laundryhub.css') }}" rel="stylesheet">
    <script>try { document.documentElement.dataset.staffNavMode = localStorage.getItem('waku-staff-navigation-mode') === 'radial' ? 'radial' : 'sidebar'; } catch {}</script>
    <link href="{{ asset('css/staff-workspace.css') }}?v={{ filemtime(public_path('css/staff-workspace.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/laundry-notifications.css') }}?v={{ filemtime(public_path('css/laundry-notifications.css')) }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="staff-body">
    @php($employee = $employee ?? request()->attributes->get('staff'))
    <a class="skip-link" href="#main-content">Skip to content</a>
    @include('layouts.staff-sidebar')
    @include('layouts.staff-navbar')
    <button class="staff-nav-backdrop" type="button" data-close-staff-sidebar aria-label="Close navigation" tabindex="-1" hidden></button>
    <main id="main-content" class="staff-content" tabindex="-1"><div class="workspace">
        @include('partials.feedback')
        @yield('content')
    </div></main>
    @include('layouts.staff-notifications')
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/laundryhub.js') }}?v={{ filemtime(public_path('js/laundryhub.js')) }}" defer></script>
    <script src="{{ asset('js/staff-navigation.js') }}?v={{ filemtime(public_path('js/staff-navigation.js')) }}" defer></script>
    <script src="{{ asset('js/laundry-notifications.js') }}?v={{ filemtime(public_path('js/laundry-notifications.js')) }}" defer></script>
    @stack('scripts')
</body>
</html>
