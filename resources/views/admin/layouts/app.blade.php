<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laundry Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    @stack('styles')
    <style>
        .admin-sidebar {
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-right: 1px solid #e0e0e0;
            background: #1E6AA8;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .admin-sidebar .nav-link {
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
        .admin-sidebar .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 0;
            background-color: rgba(255, 255, 255, 0.1);
            transition: width 0.3s ease;
        }
        .admin-sidebar .nav-link:hover::before {
            width: 100%;
        }
        .admin-sidebar .nav-link.active, .admin-sidebar .nav-link:hover {
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            transform: translateX(5px);
            opacity: 1;
        }
        .admin-sidebar .nav-link i {
            width: 22px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        .admin-sidebar .nav-link:hover i {
            transform: scale(1.1);
        }
        @media (max-width: 991px) {
            .admin-sidebar { 
                width: 100%; 
                min-height: auto; 
                position: relative; 
                border-radius: 0;
                transform: translateX(-100%);
            }
            .admin-sidebar.visible {
                transform: translateX(0);
            }
        }
        .admin-sidebar-hidden {
            transform: translateX(-250px);
        }
        .main-content {
            margin-left: 250px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .main-content-expanded {
            margin-left: 0;
        }
        .menu-button {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            display: none;
        }
        @media (max-width: 991px) {
            .menu-button {
                display: block;
            }
            .admin-sidebar {
                transform: translateX(-250px);
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Menu Button -->
    <button class="btn btn-primary menu-button" onclick="toggleSidebar()" data-aos="fade-right">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <div id="sidebar" class="admin-sidebar">
        @include('admin.layouts.sidebar')
    </div>

    <!-- Main Content -->
    <div class="main-content" id="main-content">
        @if (!Route::is('reports.attendance'))
        <!-- Navbar content area -->
        <div class="content-navbar" data-aos="fade-down">
            <h1 class="h3 fw-bold mb-0">
                @if (Route::is('reports.daily-revenue'))
                    Daily Revenue
                @elseif (Route::is('reports.service-stats'))
                    Service Stats
                @else
                    Dashboard Overview
                @endif
            </h1>
        </div>
        @endif

        <!-- Content Inner -->
        <div class="content-inner">
            @yield('content')
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init();

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('admin-sidebar-hidden');
            document.getElementById('main-content').classList.toggle('main-content-expanded');
        }
    </script>
    @stack('scripts')
    @yield('scripts')
</body>
</html>
