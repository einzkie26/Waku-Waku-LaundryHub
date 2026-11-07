<header class="site-header">
    <nav class="navbar navbar-expand-lg" aria-label="Main navigation">
        <div class="container">
            <a class="brand-link" href="{{ route('landing') }}">
                <img src="{{ asset('img/logo.png') }}" alt="" width="48" height="48">
                <span class="brand-name">waku-waku<small>Laundryhub</small></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#customer-navigation" aria-controls="customer-navigation" aria-expanded="false" aria-label="Open navigation"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="customer-navigation">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('landing') ? 'active' : '' }}" href="{{ route('landing') }}" @if(request()->routeIs('landing')) aria-current="page" @endif>Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('landing') }}#services">Services & prices</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('landing') }}#how-it-works">How it works</a></li>
                    @auth<li class="nav-item"><a class="nav-link {{ request()->routeIs('customer.orders.history', 'order.tracking') ? 'active' : '' }}" href="{{ route('customer.orders.history') }}">My orders</a></li>@endauth
                </ul>
                <div class="d-flex gap-2 align-items-center">
                    @auth
                        <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Account options">{{ explode(' ', auth()->user()->name)[0] }}</button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('profile.settings') }}">Profile settings</a></li>
                                @if(auth()->user()->role === 'admin')<li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">Admin dashboard</a></li>@endif
                                <li><form method="POST" action="{{ route('logout') }}">@csrf<button class="dropdown-item" type="submit">Sign out</button></form></li>
                            </ul>
                        </div>
                    @else
                        <a class="btn btn-light" href="{{ route('login.form') }}">Sign in</a>
                    @endauth
                    <a class="btn btn-primary" href="{{ route('booking.create') }}">Book a pickup <i class="bi bi-arrow-up-right ms-1" aria-hidden="true"></i></a>
                </div>
            </div>
        </div>
    </nav>
</header>
