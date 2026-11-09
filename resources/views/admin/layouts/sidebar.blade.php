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
    <button class="admin-radial-quick-switch" type="button" data-navigation-mode="sidebar" aria-label="Switch to sidebar navigation" aria-pressed="false" title="Switch to sidebar"><i class="bi bi-layout-sidebar-inset" aria-hidden="true"></i></button>
    <span class="admin-radial-caption" aria-hidden="true">Menu <i class="bi bi-plus" aria-hidden="true"></i></span>
    <nav class="admin-radial-selections" id="admin-radial-selections" aria-label="Admin navigation" hidden inert>
        @foreach($navigationLinks as [$route, $label, $icon, $pattern])
            <a class="admin-radial-choice {{ request()->routeIs($pattern) ? 'active' : '' }}" href="{{ route($route) }}" data-nav-choice tabindex="-1" @if($route === 'settings.profile') data-bs-toggle="modal" data-bs-target="#admin-settings-modal" @endif @if(request()->routeIs($pattern)) aria-current="page" @endif>
                <i class="bi bi-{{ $icon }}" aria-hidden="true"></i><span>{{ $label }}</span>
            </a>
        @endforeach
    </nav>
    <div class="admin-navigation-switch admin-radial-mode-switch" role="group" aria-label="Navigation style" data-radial-mode-controls hidden inert>
        <button type="button" data-navigation-mode="radial" aria-pressed="true">Menu+</button>
        <button type="button" data-navigation-mode="sidebar" aria-pressed="false">Sidebar</button>
    </div>
    <span class="admin-radial-tip" aria-hidden="true">Drag logo to dock</span>
    <span class="visually-hidden" id="admin-navigation-help">Click or press Enter to expand navigation. Drag the logo to snap to one of eight edges or corners. When the logo is focused, use the arrow keys to change its dock. Press Escape to collapse. Use the navigation style switch to open a sidebar.</span>
    <span class="visually-hidden" role="status" aria-live="polite" aria-atomic="true" data-dock-status></span>
</div>
<aside class="admin-sidebar admin-mode-sidebar" id="admin-sidebar" aria-label="Admin sidebar" aria-hidden="true" hidden inert>
    <div class="admin-sidebar-heading">
        <a class="admin-brand" href="{{ route('admin.dashboard') }}"><span class="admin-brand-logo"><img src="{{ asset('img/logo.png') }}" alt="" width="42" height="42"></span><span>Waku-Waku<small>Laundryhub</small></span></a>
        <button class="admin-sidebar-close" type="button" data-close-admin-sidebar aria-label="Close sidebar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </div>
    <div class="admin-sidebar-workspace"><span class="admin-sidebar-status-dot"></span>Admin workspace<span class="admin-sidebar-workspace-mark" aria-hidden="true"><i class="bi bi-stars"></i></span></div>
    <nav class="admin-sidebar-links" aria-label="Admin navigation">
        @foreach($navigationLinks as [$route, $label, $icon, $pattern])
            @if($loop->index === 0)<div class="admin-sidebar-section">Workspace</div>@endif
            @if($loop->index === 4)<div class="admin-sidebar-section">Reports</div>@endif
            @if($loop->index === 7)<div class="admin-sidebar-section">Account</div>@endif
            <a class="admin-sidebar-link {{ request()->routeIs($pattern) ? 'active' : '' }}" href="{{ route($route) }}" data-sidebar-link @if($route === 'settings.profile') data-bs-toggle="modal" data-bs-target="#admin-settings-modal" @endif @if(request()->routeIs($pattern)) aria-current="page" @endif><span class="admin-sidebar-link-icon"><i class="bi bi-{{ $icon }}" aria-hidden="true"></i></span><span>{{ $label }}</span>@if(request()->routeIs($pattern))<span class="admin-sidebar-current-dot" aria-hidden="true"></span>@endif</a>
        @endforeach
    </nav>
    <div class="admin-sidebar-mode-footer">
        <div class="admin-sidebar-mode-label"><span>Navigation style</span><i class="bi bi-sliders2" aria-hidden="true"></i></div>
        <div class="admin-navigation-switch admin-sidebar-mode-switch" role="group" aria-label="Navigation style">
            <button type="button" data-navigation-mode="radial" aria-pressed="false"><i class="bi bi-circle" aria-hidden="true"></i>Menu+</button>
            <button type="button" data-navigation-mode="sidebar" aria-pressed="true"><i class="bi bi-layout-sidebar-inset" aria-hidden="true"></i>Sidebar</button>
        </div>
    </div>
</aside>
<button class="admin-sidebar-backdrop" type="button" data-close-admin-sidebar aria-label="Close sidebar" tabindex="-1" hidden></button>
<div class="admin-navigation-fx" id="admin-navigation-fx" aria-hidden="true" hidden inert>
    <svg class="admin-navigation-fx-canvas" xmlns="http://www.w3.org/2000/svg" focusable="false">
        <defs>
            <linearGradient id="admin-nav-fluid-fill" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#2d6c54"/><stop offset=".42" stop-color="#16483c"/><stop offset="1" stop-color="#0c2928"/></linearGradient>
            <linearGradient id="admin-nav-swirl-fill" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#9fcda6"/><stop offset=".45" stop-color="#ece7bb"/><stop offset="1" stop-color="#397f67"/></linearGradient>
        </defs>
        <path data-nav-fluid fill="url(#admin-nav-fluid-fill)"/>
        <path data-nav-fluid-shine fill="none" stroke="#cce6b6" stroke-width="1.5" stroke-linecap="round"/>
        <circle data-nav-pop-ring fill="none" stroke="#9cc49d" stroke-width="1.5"/>
        <path data-nav-swirl fill="none" stroke="url(#admin-nav-swirl-fill)" stroke-width="3" stroke-linecap="round"/>
        <path data-nav-swirl-echo fill="none" stroke="#f0edcf" stroke-width="1.2" stroke-linecap="round"/>
        @for($particle = 0; $particle < 9; $particle++)<circle data-nav-droplet fill="{{ $particle % 3 === 0 ? '#d9dfae' : '#a6ceb6' }}" stroke="#f6fff0" stroke-width=".6"/>@endfor
    </svg>
    <div class="admin-navigation-bubble" data-nav-bubble><span class="admin-navigation-bubble-inner"><img src="{{ asset('img/logo.png') }}" alt="" width="42" height="42"></span><span class="admin-navigation-bubble-glint"></span></div>
</div>
<span class="visually-hidden" role="status" aria-live="polite" id="admin-navigation-status"></span>
<noscript>
    <nav class="admin-nav-fallback" aria-label="Admin navigation">
        @foreach($navigationLinks as [$route, $label])<a href="{{ route($route) }}">{{ $label }}</a>@endforeach
    </nav>
</noscript>
