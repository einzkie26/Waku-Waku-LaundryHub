<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm fixed-top" style="z-index: 1030; margin-left: 250px; right: 0; transition: all 0.3s ease;">
    <div class="container-fluid px-4">
        <button class="btn btn-link text-dark d-lg-none" id="sidebarToggle" type="button">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="d-flex align-items-center ms-auto">
            <div class="dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="avatar avatar-sm me-2">
                        <span class="avatar-initial rounded-circle bg-primary text-white">
                            {{ $employee ? strtoupper(substr($employee->name, 0, 1)) : '?' }}
                        </span>
                    </div>
                    <span class="d-none d-md-inline">{{ $employee ? $employee->name : 'Staff' }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="{{ route('staff.profile') }}"><i class="fas fa-user me-2"></i> Profile</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('staff.logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<style>
    .navbar {
        padding: 0.75rem 0;
    }
    
    .navbar .dropdown-menu {
        border: none;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        border-radius: 0.5rem;
        margin-top: 0.5rem;
    }
    
    .navbar .dropdown-header {
        border-radius: 0.5rem 0.5rem 0 0;
    }
    
    .navbar .dropdown-item {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
        border-radius: 0.25rem;
        margin: 0.1rem 0.5rem;
        width: auto;
    }
    
    .navbar .dropdown-item:hover {
        background-color: #f8f9fa;
    }
    
    .navbar .avatar {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .navbar .avatar-initial {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }
    
    @media (max-width: 767.98px) {
        .navbar {
            margin-left: 0 !important;
        }
        
        .content {
            margin-left: 0 !important;
            padding-top: 70px;
        }
    }
</style>

<script>
    //Toggle sidebar on mobile
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.querySelector('.staff-sidebar').classList.toggle('show');
        document.body.classList.toggle('sidebar-toggled');
    });
    
    //Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const sidebar = document.querySelector('.staff-sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');
        
        if (window.innerWidth <= 991.98 && !sidebar.contains(event.target) && event.target !== toggleBtn && !toggleBtn.contains(event.target)) {
            sidebar.classList.remove('show');
            document.body.classList.remove('sidebar-toggled');
        }
    });
    
    //Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 991.98) {
            document.querySelector('.staff-sidebar').classList.remove('show');
            document.body.classList.remove('sidebar-toggled');
        }
    });
</script>
