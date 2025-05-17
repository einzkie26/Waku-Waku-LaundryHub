@unless (Route::is('booking.step2'))
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm" data-aos="fade-down">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            <img src="{{ asset('img/logo.png') }}" alt="Waku Logo" style="height: 80px; width: auto;">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="#hero">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                <li class="nav-item"><a class="nav-link" href="#how-it-works">How It Works</a></li>
                <li class="nav-item"><a class="nav-link" href="#pricing">Pricing</a></li>
            </ul>
            <ul class="navbar-nav ms-auto">
                @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login.form') }}">Login</a>
                    </li>
                    <li class="nav-item my-2 mx-2">|</li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register.form') }}">Register</a>
                    </li>
                @endguest
                @auth
                @unless (Request::is('settings') || Request::is('booking*'))
                <li class="nav-item dropdown" id="hoverDropdown">
                    <a class="nav-link" href="#" id="megaMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-grid-3x3-gap-fill fs-5"></i>
                    </a>

                    <div class="dropdown-menu compact-menu dropdown-menu-end p-3 shadow" aria-labelledby="megaMenu">
                        <h6 class="text-uppercase fw-bold mb-3 text-muted">Menu</h6>

                        <div class="d-flex align-items-start mb-3">
                            <i class="bi bi-calendar-check-fill fs-4 text-primary me-3"></i>
                            <div>
                                <div class="fw-semibold small">My Orders</div>
                                <a href="#" class="badge bg-primary text-white mt-1" data-bs-toggle="modal" data-bs-target="#bookingsModal">Open</a>
                            </div>
                        </div>

                        <div class="d-flex align-items-start mb-3">
                            <i class="bi bi-tags-fill fs-4 text-success me-3"></i>
                            <div>
                                <div class="fw-semibold small">Order History</div>
                                <a href="{{ route('customer.orders.history') }}" class="badge bg-success text-white mt-1">View</a>
                            </div>
                        </div>

                        <div class="d-flex align-items-start">
                            <i class="bi bi-bell-fill fs-4 text-danger me-3 position-relative">
                                @php
                                    $unreadCount = auth()->user()->logs()
                                        ->where('created_at', '>=', now()->subDay())
                                        ->count();
                                @endphp
                                @if($unreadCount > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.5rem;">
                                        {{ $unreadCount }}
                                    </span>
                                @endif
                            </i>
                            <div>
                                <div class="fw-semibold small">Notifications</div>
                                <a href="#" class="badge bg-danger text-white mt-1" data-bs-toggle="modal" data-bs-target="#notificationModal">Check</a>
                            </div>
                        </div>
                    </div>
                </li>
                @endunless
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{{ Auth::user()->photo ? asset('storage/' . Auth::user()->photo) : asset('img/no-img.png') }}" alt="Profile" class="rounded-circle" style="width: 35px; height: 35px; object-fit: cover;">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li>
                            <h6 class="dropdown-header">
                                {{ Auth::user()->name }}
                            </h6>
                        </li>
                        <li><a class="dropdown-item text-primary" href="{{ route('profile.settings') }}">Profile Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">Logout</button>
                            </form>
                        </li>
                        @endauth
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
@endunless

