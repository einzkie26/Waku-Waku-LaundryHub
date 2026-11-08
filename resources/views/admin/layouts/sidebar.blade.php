@php
    $navigationLinks = [
        ['admin.dashboard', 'Overview', 'grid-1x2', 'admin.dashboard'],
        ['orders.index', 'Orders', 'basket', 'orders.*'],
        ['admin.customers.index', 'Customers', 'people', 'admin.customers.*'],
        ['employees.list', 'Employees', 'person-badge', 'employees.*'],
        ['reports.attendance', 'Attendance', 'calendar-check', 'reports.attendance*'],
        ['reports.revenue-report', 'Revenue', 'bar-chart-line', 'reports.revenue-report'],
        ['reports.service-stats', 'Services', 'pie-chart', 'reports.service-stats'],
        ['settings.profile', 'Settings', 'gear', 'settings.profile*'],
        ['landing', 'Customer site', 'box-arrow-up-right', 'landing'],
    ];
@endphp
<div class="admin-radial-nav" id="admin-navigation" data-dock="bottom-left">
    <button class="admin-radial-toggle" id="admin-menu-toggle" type="button" aria-controls="admin-radial-selections" aria-expanded="false" aria-label="Open admin navigation" aria-describedby="admin-navigation-help" title="Click to open navigation. Drag to an edge or corner.">
        <img src="{{ asset('img/logo.png') }}" alt="" width="46" height="46" draggable="false">
    </button>
    <span class="admin-radial-caption" aria-hidden="true">Menu <i class="bi bi-plus" aria-hidden="true"></i></span>
    <nav class="admin-radial-selections" id="admin-radial-selections" aria-label="Admin navigation" hidden inert>
        @foreach($navigationLinks as [$route, $label, $icon, $pattern])
            <a class="admin-radial-choice {{ request()->routeIs($pattern) ? 'active' : '' }}" href="{{ route($route) }}" data-nav-choice tabindex="-1" @if(request()->routeIs($pattern)) aria-current="page" @endif>
                <i class="bi bi-{{ $icon }}" aria-hidden="true"></i><span>{{ $label }}</span>
            </a>
        @endforeach
    </nav>
    <span class="admin-radial-tip" aria-hidden="true">Click logo to close<small>Drag logo to dock</small></span>
    <span class="visually-hidden" id="admin-navigation-help">Click or press Enter to expand navigation. Drag the logo to snap to one of eight edges or corners. When the logo is focused, use the arrow keys to change its dock. Press Escape to collapse.</span>
    <span class="visually-hidden" role="status" aria-live="polite" aria-atomic="true" data-dock-status></span>
</div>
<noscript>
    <nav class="admin-nav-fallback" aria-label="Admin navigation">
        @foreach($navigationLinks as [$route, $label])<a href="{{ route($route) }}">{{ $label }}</a>@endforeach
    </nav>
</noscript>
