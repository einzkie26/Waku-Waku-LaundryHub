<aside class="staff-sidebar shadow-sm d-flex flex-column align-items-center" style="min-height: 100vh; width: 250px; position: fixed; top: 0; left: 0; z-index: 1040; border-radius: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.08); font-family: 'Inter', sans-serif; background: #1E6AA8;">
    <!-- Logo and Brand -->
    <div class="w-100 text-center py-4 border-bottom" style="background: #1E6AA8; border-radius: 0;" data-aos="fade-down" data-aos-duration="300">
        <img src="{{ asset('img/logo.png') }}" alt="Logo" style="max-width: 60px; max-height: 60px; margin-bottom: 0.5rem;">
        <div class="fw-bold text-white" style="font-size: 1.2rem; letter-spacing: 1px;">Waku Laundry System</div>
        <div class="text-white" style="font-size: 1rem; font-weight: 500;">Staff Portal</div>
    </div>
    <!-- Navigation -->
    <nav class="w-100 px-3 py-4">
        <ul class="nav flex-column">
            <li class="nav-item" data-aos="fade-right" data-aos-duration="300" data-aos-delay="50">
                <a href="{{ route('staff.dashboard') }}" class="nav-link px-4 py-3 rounded-pill mb-2 {{ Route::is('staff.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item" data-aos="fade-right" data-aos-duration="300" data-aos-delay="100">
                <a href="{{ route('staff.orders.index') }}" class="nav-link px-4 py-3 rounded-pill mb-2 {{ Route::is('staff.orders*') ? 'active' : '' }}">
                    <i class="fas fa-shopping-cart me-2"></i> Orders
                </a>
            </li>
            <li class="nav-item" data-aos="fade-right" data-aos-duration="300" data-aos-delay="150">
                <a href="{{ route('staff.tasks') }}" class="nav-link px-4 py-3 rounded-pill mb-2 {{ Route::is('staff.tasks*') ? 'active' : '' }}">
                    <i class="fas fa-tasks me-2"></i> Tasks
                </a>
            </li>
            <li class="nav-item" data-aos="fade-right" data-aos-duration="300" data-aos-delay="200">
                <a href="{{ route('staff.attendance') }}" class="nav-link px-4 py-3 rounded-pill mb-2 {{ Route::is('staff.attendance*') ? 'active' : '' }}">
                    <i class="fas fa-clock me-2"></i> Attendance
                </a>
            </li>
            <li class="nav-item" data-aos="fade-right" data-aos-duration="300" data-aos-delay="225">
                <a href="{{ route('staff.history') }}" class="nav-link px-4 py-3 rounded-pill mb-2 {{ Route::is('staff.history') ? 'active' : '' }}">
                    <i class="fas fa-clock-rotate-left me-2"></i> Work History
                </a>
            </li>
        </ul>
    </nav>
</aside>
<style>
.staff-sidebar {
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border-right: 1px solid #e0e0e0;
    background: #167891;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.staff-sidebar .nav-link {
    color: #fff;
    font-weight: 500;
    border-radius: 0;
    margin-bottom: 0.25rem;
    transition: all 0.3s ease;
    font-size: 1rem;
    display: flex;
    align-items: center;
    position: relative;
    overflow: hidden;
    opacity: 0.7;
}
.staff-sidebar .nav-link::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 0;
    background-color: rgba(255, 255, 255, 0.1);
    transition: width 0.3s ease;
}
.staff-sidebar .nav-link:hover::before {
    width: 100%;
}
.staff-sidebar .nav-link.active, .staff-sidebar .nav-link:hover {
    background: rgba(255, 255, 255, 0.15);
    color: #fff;
    transform: translateX(5px);
    opacity: 1;
}
.staff-sidebar .nav-link i {
    width: 22px;
    text-align: center;
    transition: transform 0.3s ease;
}
.staff-sidebar .nav-link:hover i {
    transform: scale(1.1);
}
@media (max-width: 991px) {
    .staff-sidebar { 
        width: 100%; 
        min-height: auto; 
        position: relative; 
        border-radius: 0;
        transform: translateX(-100%);
    }
    .staff-sidebar.visible {
        transform: translateX(0);
    }
}
</style>  