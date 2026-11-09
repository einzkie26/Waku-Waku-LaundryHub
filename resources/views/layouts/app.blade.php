<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Book a laundry pickup, see your total, and follow every step with Waku-Waku Laundryhub.">
    <title>@yield('title', 'A little less laundry. A little more life.') · Waku-Waku Laundryhub</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/laundryhub.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <a class="skip-link" href="#main-content">Skip to content</a>
    @include('partials.navbar')
    <main id="main-content" class="site-main" tabindex="-1">
        <div class="container">@include('partials.feedback')</div>
        @yield('content')
    </main>
    @include('partials.footer')
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/laundryhub.js') }}?v={{ filemtime(public_path('js/laundryhub.js')) }}" defer></script>
    @stack('scripts')
</body>
</html>
