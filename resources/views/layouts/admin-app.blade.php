<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Overview') · Waku-Waku Admin</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/laundryhub.css') }}" rel="stylesheet">
    <link href="{{ asset('css/admin-workspace.css') }}" rel="stylesheet">
    <link href="{{ asset('css/admin-navigation.css') }}?v={{ filemtime(public_path('css/admin-navigation.css')) }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="admin-shell admin-radial-shell">
    <a class="skip-link" href="#admin-main">Skip to content</a>
    @include('admin.layouts.sidebar')
    @include('admin.layouts.navbar')
    <main class="admin-content" id="admin-main" tabindex="-1">
        <div class="admin-container">
            @include('partials.feedback')
            @yield('content')
            <footer class="admin-footer"><span>Waku-Waku Laundryhub</span><span>A little more clarity. Every day.</span></footer>
        </div>
    </main>
    @include('admin.settings.modal')
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/laundryhub.js') }}" defer></script>
    <script src="{{ asset('js/admin-workspace.js') }}?v={{ filemtime(public_path('js/admin-workspace.js')) }}" defer></script>
    @stack('scripts')
    @yield('scripts')
</body>
</html>
