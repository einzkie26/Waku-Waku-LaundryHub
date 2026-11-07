<aside class="staff-sidebar" id="staff-sidebar" aria-label="Staff navigation">
    <a class="brand-link" href="{{ route('staff.dashboard') }}"><img src="{{ asset('img/logo.png') }}" alt="" width="48" height="48"><span class="brand-name">waku-waku<small>Staff workspace</small></span></a>
    <button class="btn btn-light d-lg-none mb-3" type="button" data-close-sidebar>Close menu <i class="bi bi-x-lg ms-2" aria-hidden="true"></i></button>
    <p class="sidebar-label">THE DAILY LOAD</p>
    <nav class="nav flex-column">
        @foreach([
            ['staff.dashboard', 'Overview', 'grid', request()->routeIs('staff.dashboard')],
            ['staff.orders.index', 'Order queue', 'basket', request()->routeIs('staff.orders.*', 'staff.store')],
            ['staff.customers', 'Customers', 'people', request()->routeIs('staff.customers*')],
            ['staff.attendance', 'Attendance', 'clock', request()->routeIs('staff.attendance*')],
            ['staff.history', 'Work history', 'clock-history', request()->routeIs('staff.history')]
        ] as [$route, $label, $icon, $active])
        <a class="nav-link {{ $active ? 'active' : '' }}" href="{{ route($route) }}" @if($active) aria-current="page" @endif><i class="bi bi-{{ $icon }}" aria-hidden="true"></i>{{ $label }}</a>
        @endforeach
    </nav>
    <div class="sidebar-guide"><strong>One queue. Every load.</strong>Open an order, follow the next action, and record payment when it is received.</div>
</aside>
