@php
    $staffLinks = [
        ['staff.dashboard', 'Overview', 'grid', request()->routeIs('staff.dashboard')],
        ['staff.orders.index', 'Order queue', 'basket', request()->routeIs('staff.orders.*', 'staff.store')],
        ['staff.customers', 'Customers', 'people', request()->routeIs('staff.customers*')],
        ['staff.attendance', 'Attendance', 'clock', request()->routeIs('staff.attendance*')],
        ['staff.history', 'Work history', 'clock-history', request()->routeIs('staff.history')],
        ['staff.profile', 'My profile', 'person-circle', request()->routeIs('staff.profile*')],
    ];
@endphp
<aside class="staff-sidebar" id="staff-sidebar" aria-label="Staff navigation">
    <div class="staff-sidebar-heading">
        <a class="brand-link" href="{{ route('staff.dashboard') }}"><img src="{{ asset('img/logo.png') }}" alt="" width="48" height="48"><span class="brand-name">waku-waku<small>Staff workspace</small></span></a>
        <button class="staff-icon-button staff-sidebar-close" type="button" data-close-staff-sidebar aria-label="Close sidebar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </div>
    <nav class="nav flex-column staff-sidebar-links">
        @foreach($staffLinks as [$route, $label, $icon, $active])
            <a class="nav-link {{ $active ? 'active' : '' }}" href="{{ route($route) }}" @if($active) aria-current="page" @endif><i class="bi bi-{{ $icon }}" aria-hidden="true"></i><span>{{ $label }}</span>@if($active)<span class="staff-current-dot" aria-hidden="true"></span>@endif</a>
        @endforeach
        <button class="nav-link staff-notification-trigger" type="button" data-open-staff-notifications aria-haspopup="dialog" aria-controls="staff-notifications"><i class="bi bi-bell" aria-hidden="true"></i><span>Notifications</span><span class="staff-unread-badge" data-staff-unread hidden>0</span></button>
    </nav>
    <div class="staff-sidebar-footer">
        <span class="staff-nav-style-label">Navigation style</span>
        <div class="staff-mode-switch" role="group" aria-label="Navigation style">
            <button type="button" data-staff-mode="sidebar" aria-pressed="true"><i class="bi bi-layout-sidebar-inset" aria-hidden="true"></i>Sidebar</button>
            <button type="button" data-staff-mode="radial" aria-pressed="false"><i class="bi bi-circle" aria-hidden="true"></i>Menu+</button>
        </div>
    </div>
</aside>
<div class="staff-menu" id="staff-menu" hidden inert>
    <button class="staff-menu-toggle" id="staff-menu-toggle" type="button" aria-controls="staff-menu-choices" aria-expanded="false" aria-label="Open staff navigation" aria-describedby="staff-menu-help" title="Open navigation. Drag to an edge or corner."><img src="{{ asset('img/logo.png') }}" alt="" width="44" height="44" draggable="false"></button>
    <span class="staff-menu-caption" aria-hidden="true">Menu <i class="bi bi-plus" aria-hidden="true"></i></span>
    <button class="staff-menu-quick-switch" type="button" data-staff-mode="sidebar" aria-pressed="false" aria-label="Switch to sidebar" title="Switch to sidebar"><i class="bi bi-layout-sidebar-inset" aria-hidden="true"></i></button>
    <nav id="staff-menu-choices" class="staff-menu-choices" aria-label="Staff navigation" hidden inert>
        @foreach($staffLinks as [$route, $label, $icon, $active])
            <a class="staff-menu-choice {{ $active ? 'active' : '' }}" data-staff-choice href="{{ route($route) }}" tabindex="-1" @if($active) aria-current="page" @endif><i class="bi bi-{{ $icon }}" aria-hidden="true"></i><span>{{ $label }}</span></a>
        @endforeach
        <button class="staff-menu-choice" data-staff-choice data-open-staff-notifications type="button" tabindex="-1" aria-haspopup="dialog" aria-controls="staff-notifications"><i class="bi bi-bell" aria-hidden="true"></i><span>Notifications</span><b class="staff-unread-badge" data-staff-unread hidden>0</b></button>
    </nav>
    <div class="staff-mode-switch staff-menu-mode-switch" role="group" aria-label="Navigation style" hidden inert>
        <button type="button" data-staff-mode="sidebar" aria-pressed="false">Sidebar</button>
        <button type="button" data-staff-mode="radial" aria-pressed="true">Menu+</button>
    </div>
    <span class="visually-hidden" id="staff-menu-help">Press Enter to open. Drag the logo to an edge or corner, or use the arrow keys to change its dock. Press Escape to close. Use the style switch for a sidebar.</span>
</div>
<div class="staff-nav-fold" id="staff-nav-fold" aria-hidden="true" hidden inert><span></span><span></span><span></span><span></span><span></span><span></span><img src="{{ asset('img/logo.png') }}" alt="" width="44" height="44"></div>
<span class="visually-hidden" role="status" aria-live="polite" id="staff-navigation-status"></span>
