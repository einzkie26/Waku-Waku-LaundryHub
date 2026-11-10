<header class="staff-topbar">
    <div class="d-flex align-items-center gap-3"><button class="staff-icon-button staff-sidebar-open" id="staff-sidebar-toggle" type="button" aria-label="Open navigation" aria-controls="staff-sidebar" aria-expanded="false"><i class="bi bi-list" aria-hidden="true"></i></button><span class="topbar-title"><span class="staff-topbar-prefix">Laundry operations / </span>@yield('title', 'Workspace')</span></div>
    <div class="staff-topbar-actions">
        <button class="staff-icon-button staff-topbar-bell" type="button" data-open-staff-notifications aria-haspopup="dialog" aria-controls="staff-notifications" aria-label="Notifications"><i class="bi bi-bell" aria-hidden="true"></i><span class="staff-unread-badge" data-staff-unread hidden>0</span></button>
    <div class="dropdown"><button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Staff account"><span class="user-avatar">@if($employee?->profile_photo_url)<img src="{{ $employee->profile_photo_url }}" alt="">@else{{ mb_strtoupper(mb_substr($employee?->name ?? 'S', 0, 1)) }}@endif</span><span class="staff-account-name d-none d-sm-inline">{{ $employee?->name ?? 'Staff' }}</span></button>
        <ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="{{ route('staff.profile') }}">My profile</a></li><li><form method="POST" action="{{ route('staff.logout') }}">@csrf<button class="dropdown-item" type="submit">Sign out</button></form></li></ul>
    </div>
    </div>
</header>
