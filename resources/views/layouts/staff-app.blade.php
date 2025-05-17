<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laundry Shop - @yield('title')</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.ico') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/staff-animations.css') }}">
    <link rel="stylesheet" href="{{ asset('css/staff-dashboard.css') }}">
    <style>
        /* Notification Animation */
        @keyframes bellRing {
            0% { transform: rotate(0); }
            25% { transform: rotate(10deg); }
            50% { transform: rotate(-10deg); }
            75% { transform: rotate(5deg); }
            100% { transform: rotate(0); }
        }
        .bell-ring {
            animation: bellRing 0.5s ease-in-out;
        }
        .notification-item {
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        .notification-item.unread {
            background-color: rgba(78, 115, 223, 0.05);
            border-left-color: #4e73df;
        }
        .notification-item:hover {
            background-color: rgba(0, 0, 0, 0.03);
        }
        .notification-time {
            font-size: 0.75rem;
            color: #6c757d;
        }
    </style>
    
    @stack('styles')
    
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --light-color: #f8f9fc;
            --dark-color: #5a5c69;
            --transition-speed: 0.3s;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(90deg, rgba(0, 162, 255, 1) 5%, rgba(153, 255, 221, 1) 37%, rgba(0, 136, 247, 1) 77%);
            overflow-x: hidden;
        }
        
        .content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
            background: linear-gradient(90deg, rgba(0, 162, 255, 1) 5%, rgba(153, 255, 221, 1) 37%, rgba(0, 136, 247, 1) 77%);
            transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 1;
        }
        
        .content::before {
            content: '';
            position: fixed;
            top: 0;
            left: 250px;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, rgba(0, 162, 255, 0.1) 5%, rgba(153, 255, 221, 0.1) 37%, rgba(0, 136, 247, 0.1) 77%);
            z-index: -1;
            pointer-events: none;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            transition: all var(--transition-speed) ease;
            overflow: hidden;
            position: relative;
            z-index: 1;
            margin-bottom: 1rem;
            max-width: 100%;
            background-color: #FCFCFC;
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
            z-index: -1;
            opacity: 0;
            transition: opacity var(--transition-speed) ease;
        }
        
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1.5rem 0.5rem rgba(58, 59, 69, 0.15);
        }
        
        .card:hover::before {
            opacity: 1;
        }
        
        .container-fluid {
            padding: 1rem;
        }

        .row {
            margin: -0.5rem;
        }

        .col, .col-md-6, .col-lg-4, .col-xl-3 {
            padding: 0.5rem;
        }

        .card-body {
            padding: 1rem;
            background-color: #FCFCFC;
        }

        .card-title {
            margin-bottom: 0.75rem;
            font-size: 1.1rem;
        }

        .card-text {
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .btn {
            border-radius: 8px;
            padding: 0.5rem 1.25rem;
            font-weight: 500;
            transition: all var(--transition-speed) ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .table {
            transition: all var(--transition-speed) ease;
            background-color: #FCFCFC;
        }
        
        .table thead th {
            border-top: none;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            color: var(--secondary-color);
            background-color: #FCFCFC;
            border-bottom: 2px solid rgba(0, 0, 0, 0.1);
        }
        
        .table tbody tr {
            transition: all var(--transition-speed) ease;
            background-color: #FCFCFC;
        }
        
        .table tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.05);
            transform: translateX(5px);
        }
        
        .status-badge {
            transition: all 0.3s ease;
            padding: 5px 10px;
            border-radius: 12px;
            display: inline-block;
        }
        
        .status-badge:hover {
            transform: scale(1.05);
        }
        
        @media (max-width: 991.98px) {
            .staff-sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                z-index: 1040;
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
                border-radius: 0;
            }
            
            .staff-sidebar.show {
                transform: translateX(0);
            }
            
            .content {
                margin-left: 0 !important;
                padding-top: 1rem;
            }
            
            body.sidebar-toggled {
                overflow: hidden;
            }
            
            body.sidebar-toggled::after {
                content: '';
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1039;
                transition: opacity 0.3s ease-in-out;
            }
        }

        .navbar {
            background-color: #FCFCFC !important;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .staff-sidebar {
            background-color: #FCFCFC;
            border-right: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 0;
        }

        .staff-sidebar .nav-link {
            color: #333;
            transition: all 0.3s ease;
            border-radius: 0;
            margin: 2px 0;
        }

        .staff-sidebar .nav-link:hover {
            background-color: rgba(0, 0, 0, 0.05);
            border-radius: 0;
        }

        .staff-sidebar .nav-link.active {
            background-color: rgba(0, 0, 0, 0.1);
            color: #000;
            border-radius: 0;
        }

        .staff-sidebar .nav-item {
            border-radius: 0;
        }

        .staff-sidebar .nav-item .nav-link {
            border-radius: 0;
        }

        .staff-sidebar .nav-item .nav-link i {
            border-radius: 0;
        }
    </style>
</head>
<body class="d-flex flex-column">
    @php
        $staffId = Session::get('staff_id');
        $employee = $staffId ? \App\Models\Employee::find($staffId) : null;
    @endphp
    @include('layouts.staff-navbar')
    <div class="d-flex flex-grow-1" style="margin-top: 70px;">
        @include('layouts.staff-sidebar')
        <main class="content flex-grow-1" data-aos="fade-in">
            <div class="container-fluid py-4">
                @yield('content')
            </div>
        </main>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            mirror: false,
            offset: 100
        });
        document.addEventListener('DOMContentLoaded', function() {
            document.body.classList.add('page-loaded');
            const animatedElements = document.querySelectorAll('[data-aos]');
            animatedElements.forEach(el => {
                el.style.opacity = '0';
                el.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
            });
            setTimeout(() => {
                animatedElements.forEach(el => {
                    el.style.opacity = '1';
                });
            }, 100);
        });
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                setTimeout(() => {
                    animatedElements.forEach(el => {
                        el.style.opacity = '1';
                    });
                }, 100);
            });
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    
                    const targetId = this.getAttribute('href');
                    if (targetId === '#') return;
                    
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        window.scrollTo({
                            top: targetElement.offsetTop - 80,
                            behavior: 'smooth'
                        });
                    }
                });
            });
        });
    </script>
    
    @stack('scripts')
    
    <!-- Notification System -->
    <script src="{{ asset('js/staff-notifications.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the notification system
            if (typeof StaffNotifications !== 'undefined') {
                window.staffNotifications = new StaffNotifications();
            } else {
                console.error('StaffNotifications class is not defined. Make sure staff-notifications.js is loaded.');
            }
            
            // Toggle sidebar on mobile
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.staff-sidebar');
            
            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.body.classList.toggle('sidebar-toggled');
                    sidebar.classList.toggle('show');
                });
                
                // Close sidebar when clicking outside
                document.addEventListener('click', function(e) {
                    if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                        document.body.classList.remove('sidebar-toggled');
                        sidebar.classList.remove('show');
                    }
                });
            }
        });
    </script>
</body>
</html>