<header class="admin-topbar">
    <div class="d-flex align-items-center gap-3">
        <button class="admin-sidebar-open-button" id="admin-sidebar-toggle" type="button" aria-controls="admin-sidebar" aria-expanded="false" aria-label="Open sidebar"><i class="bi bi-list" aria-hidden="true"></i></button>
        <nav aria-label="Breadcrumb" class="admin-breadcrumb"><a href="{{ route('admin.dashboard') }}">Workspace</a><i class="bi bi-chevron-right" aria-hidden="true"></i><span aria-current="page">@yield('title', 'Overview')</span></nav>
    </div>
    <div class="admin-topbar-actions">
        <form class="admin-quick-search" action="{{ route('orders.index') }}" method="GET" role="search"><i class="bi bi-search" aria-hidden="true"></i><label class="visually-hidden" for="admin-quick-search">Search orders</label><input id="admin-quick-search" name="q" maxlength="100" placeholder="Find an order…" value="{{ request()->routeIs('orders.index') ? request('q') : '' }}"><input type="hidden" name="status" value="all"></form>
        <a class="admin-help-link" href="{{ route('orders.create') }}" aria-label="Create a new order" @if(request()->routeIs('orders.index')) data-bs-toggle="modal" data-bs-target="#order-create-modal" @endif><i class="bi bi-plus-lg" aria-hidden="true"></i></a>
        @include('partials.notification-bell', ['notificationRole' => 'admin'])
        <span class="admin-topbar-divider"></span>
        <div class="dropdown">
            <button class="admin-account" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="admin-avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span><span class="admin-account-name">{{ explode(' ', auth()->user()->name)[0] }}<small>Administrator</small></span><i class="bi bi-chevron-down" aria-hidden="true"></i></button>
            <ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="{{ route('settings.profile') }}" data-bs-toggle="modal" data-bs-target="#admin-settings-modal">Account settings</a></li><li><a class="dropdown-item" href="{{ route('landing') }}">Customer site</a></li><li><hr class="dropdown-divider"></li><li><form method="POST" action="{{ route('logout') }}">@csrf<button class="dropdown-item" type="submit">Sign out</button></form></li></ul>
        </div>
    </div>
</header>
