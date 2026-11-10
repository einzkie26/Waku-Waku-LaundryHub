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
    @auth('web')<link href="{{ asset('css/laundry-notifications.css') }}?v={{ filemtime(public_path('css/laundry-notifications.css')) }}" rel="stylesheet">@endauth
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
    @auth('web')@include('partials.notifications')@endauth
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/laundryhub.js') }}?v={{ filemtime(public_path('js/laundryhub.js')) }}" defer></script>
    @auth('web')<script src="{{ asset('js/laundry-notifications.js') }}?v={{ filemtime(public_path('js/laundry-notifications.js')) }}" defer></script>@endauth
    @stack('scripts')
</body>
</html>
