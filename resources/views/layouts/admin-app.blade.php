<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Laundry System</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.ico') }}">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    
    <!-- AdminLTE CSS -->
    <link href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css" rel="stylesheet">
    
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Custom Animation CSS -->
    <link rel="stylesheet" href="{{ asset('css/admin-animations.css') }}">
    
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(90deg, rgba(0, 162, 255, 1) 5%, rgba(153, 255, 221, 1) 37%, rgba(0, 136, 247, 1) 77%);
            overflow-x: hidden;
        }
        .navbar {
            background: #fff !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border-bottom: 1px solid rgba(0,0,0,0.07);
            color: #222;
            font-size: 1.1rem;
            font-weight: 500;
            z-index: 1001;
        }
        .navbar .nav-link {
            color: #222 !important;
            font-weight: 500;
            font-size: 1rem;
            margin-right: 1.5rem;
            transition: color 0.2s;
        }
        .navbar .nav-link:hover, .navbar .nav-link.active {
            color: #1E6AA8 !important;
        }
        .content-wrapper {
            background: transparent;
            min-height: 100vh;
            transition: all 0.3s ease;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.10);
            margin-bottom: 1.5rem;
            background: #FCFCFC;
            padding: 1.5rem;
        }
        .card-header, .card-title {
            border-radius: 10px 10px 0 0;
            background: #4e73df;
            color: #fff;
            font-weight: 600;
        }
        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
            border-radius: 10px;
            font-weight: 500;
            transition: background 0.3s, box-shadow 0.3s;
        }
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2e59d9;
            box-shadow: 0 4px 8px rgba(0,0,0,0.12);
        }
        .sidebar {
            min-height: 100vh;
            background: #1E6AA8;
            box-shadow: 2px 0 10px rgba(0,0,0,0.08);
            border-radius: 0;
        }
        .sidebar .nav-link {
            color: #fff;
            border-radius: 0;
            margin: 4px 0;
            padding: 12px 18px;
            font-size: 1rem;
            font-weight: 500;
            transition: background 0.2s, color 0.2s;
        }
        .sidebar .nav-link.active, .sidebar .nav-link:hover {
            background: #003087;
            color: #fff;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        @if (!Route::is('reports.attendance'))
            <!-- Navbar -->
            @include('admin.layouts.navbar')
        @endif
        
        <!-- Main Sidebar Container -->
        @include('admin.layouts.sidebar')
        
        <!-- Content Wrapper -->
        <div class="content-wrapper">
            @if (!Route::is('reports.attendance'))
            <!-- Content Header -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">@yield('title', 'Dashboard')</h1>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Main content -->
            <div class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE JS -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    
    <!-- AOS Animation JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <!-- Custom Animation JS -->
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            mirror: false
        });
        
        // Page load animation
        document.addEventListener('DOMContentLoaded', function() {
            // Add fade-in effect to page content
            document.body.classList.add('page-loaded');
            
            // Add animation to elements with data-aos attribute
            const animatedElements = document.querySelectorAll('[data-aos]');
            animatedElements.forEach(el => {
                el.style.opacity = '0';
                el.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
            });
            
            // Trigger animations
            setTimeout(() => {
                animatedElements.forEach(el => {
                    el.style.opacity = '1';
                });
            }, 100);
        });
        
        // Smooth scroll for anchor links
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
    </script>
    
    @stack('scripts')
</body>
</html>