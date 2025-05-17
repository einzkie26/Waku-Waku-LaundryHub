<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/authenticated.css') }}" rel="stylesheet">
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>
    <style>
        body {
            background: linear-gradient(90deg, rgba(0, 162, 255, 1) 5%, rgba(153, 255, 221, 1) 37%, rgba(0, 136, 247, 1) 77%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .card {
            border-radius: 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
            border: none;
            border-radius: 10px;
        }
        .form-control {
            border-radius: 0;
        }
        .btn {
            border-radius: 0;
        }
        .input-group-text {
            border-radius: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div id="login-response" class="mt-2"></div>
                        <img src="{{ asset('img/logo.png') }}" alt="Waku Logo" style="height: 100px; width: auto; display: block; margin: 0 auto;">
                        <h3 class="card-title text-center">Sign In</h3>
                        <hr>
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="username" class="form-label">Username:</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username') }}" required>
                                </div>
                                @error('username')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password:</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Sign In</button>
                        </form>
                        <div class="mt-2 text-center">
                            <p>Don't have an account? <a href="{{ route('register.form') }}" class="text-decoration-none">Sign Up</a></p>
                        </div>
                        <a href="{{ route('staff.login.form') }}" class="btn btn-outline-secondary mt-2 w-100">
                            <i class="bi bi-person-badge"></i> Log in as Staff
                        </a>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.body.addEventListener('htmx:afterRequest', function(evt) {
            const redirectUrl = evt.detail.xhr.getResponseHeader('HX-Redirect');
            if (redirectUrl) {
                window.location.href = redirectUrl;
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.querySelector('#togglePassword');
            const password = document.querySelector('#password');

            togglePassword.addEventListener('click', function() {
                // Toggle the password visibility
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                
                // Toggle the eye icon
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        });
    </script>
</body>
</html>
