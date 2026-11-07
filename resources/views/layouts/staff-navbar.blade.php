<header class="staff-topbar">
    <div class="d-flex align-items-center gap-3"><button class="btn btn-light d-lg-none" id="sidebarToggle" type="button" aria-label="Open navigation" aria-controls="staff-sidebar" aria-expanded="false"><i class="bi bi-list" aria-hidden="true"></i></button><span class="topbar-title">Laundry operations <span class="d-none d-sm-inline">/ @yield('title', 'Workspace')</span></span></div>
    <div class="dropdown"><button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="user-avatar">{{ mb_strtoupper(mb_substr($employee?->name ?? 'S', 0, 1)) }}</span><span class="d-none d-sm-inline">{{ $employee?->name ?? 'Staff' }}</span></button>
        <ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="{{ route('staff.profile') }}">My profile</a></li><li><form method="POST" action="{{ route('staff.logout') }}">@csrf<button class="dropdown-item" type="submit">Sign out</button></form></li></ul>
    </div>
</header>
