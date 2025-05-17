<aside class="admin-sidebar shadow-sm d-flex flex-column align-items-center" style="min-height: 100vh; width: 250px; position: fixed; top: 0; left: 0; z-index: 1040; border-radius: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.08); font-family: 'Inter', sans-serif; background: #1E6AA8;">
    <!-- Logo and Brand -->
    <div class="w-100 text-center py-4 border-bottom" style="background: #1E6AA8; border-radius: 0;" data-aos="fade-down" data-aos-duration="300">
        <img src="{{ asset('img/logo.png') }}" alt="Logo" style="max-width: 60px; max-height: 60px; margin-bottom: 0.5rem;">
        <div class="fw-bold text-white" style="font-size: 1.2rem; letter-spacing: 1px;">Waku Laundry System</div>
        <div class="text-white" style="font-size: 1rem; font-weight: 500;">Admin Portal</div>
    </div>
    <!-- Navigation -->
    <nav class="w-100 px-3 py-4">
        <ul class="nav flex-column">
            <li class="nav-item" data-aos="fade-right" data-aos-duration="300" data-aos-delay="50">
                <a href="{{ route('admin.dashboard') }}" class="nav-link px-4 py-3 rounded-pill mb-2 {{ Route::is('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item" data-aos="fade-right" data-aos-duration="300" data-aos-delay="100">
                <a href="{{ route('orders.index') }}" class="nav-link px-4 py-3 rounded-pill mb-2 {{ Route::is('orders.*') ? 'active' : '' }}">
                    <i class="fas fa-shopping-cart me-2"></i> Orders
                </a>
            </li>
            <li class="nav-item" data-aos="fade-right" data-aos-duration="300" data-aos-delay="150">
                <a href="{{ route('admin.customers.index') }}" class="nav-link px-4 py-3 rounded-pill mb-2 {{ Route::is('admin.customers.*') ? 'active' : '' }}">
                    <i class="fas fa-users me-2"></i> Customers
                </a>
            </li>
            <li class="nav-item" data-aos="fade-right" data-aos-duration="300" data-aos-delay="200">
                <a href="{{ route('reports.attendance') }}" class="nav-link px-4 py-3 rounded-pill mb-2 {{ Route::is('reports.attendance') ? 'active' : '' }}">
                    <i class="fas fa-user-clock me-2"></i> Attendance Report
                </a>
            </li>
            <li class="nav-item" data-aos="fade-right" data-aos-duration="300" data-aos-delay="250">
                <a href="{{ route('reports.revenue-report') }}" class="nav-link px-4 py-3 rounded-pill mb-2 {{ Route::is('reports.revenue-report') ? 'active' : '' }}">
                    <i class="fas fa-chart-line me-2"></i> Revenue Report
                </a>
            </li>
            <li class="nav-item" data-aos="fade-right" data-aos-duration="300" data-aos-delay="300">
                <a href="{{ route('reports.service-stats') }}" class="nav-link px-4 py-3 rounded-pill mb-2 {{ Route::is('reports.service-stats') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie me-2"></i> Service Stats
                </a>
            </li>
            <li class="nav-item" data-aos="fade-right" data-aos-duration="300" data-aos-delay="350">
                <a href="{{ route('employees.list') }}" class="nav-link px-4 py-3 rounded-pill mb-2 {{ Route::is('employees.*') ? 'active' : '' }}">
                    <i class="fas fa-user-tie me-2"></i> Staff Management
                </a>
            </li>
        </ul>
    </nav>
</aside>
<style>
.admin-sidebar {
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border-right: 1px solid #e0e0e0;
    background: #fff;
    width: 250px !important;
    min-width: 250px !important;
    max-width: 250px !important;
}
.admin-sidebar .nav-link {
    color: #333;
    font-weight: 500;
    border-radius: 2rem;
    margin-bottom: 0.25rem;
    transition: background 0.2s, color 0.2s;
    font-size: 1rem;
    display: flex;
    align-items: center;
}
.admin-sidebar .nav-link.active, .admin-sidebar .nav-link:hover {
    background: #003087;
    color: #fff;
}
.admin-sidebar .nav-link i {
    width: 22px;
    text-align: center;
}
@media (max-width: 991px) {
    .admin-sidebar { width: 100% !important; min-width: auto !important; max-width: 100% !important; min-height: auto; position: relative; border-radius: 0; }
}
</style>
