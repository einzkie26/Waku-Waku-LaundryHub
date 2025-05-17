@extends('layouts.staff-app')

@section('title', 'Profile')

@push('styles')
<style>
    .profile-header {
        background: #fff;
        padding: 1.5rem;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        margin-bottom: 1.5rem;
    }
    
    .profile-card {
        border: none;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        border-radius: 10px;
    }
    
    .profile-avatar {
        width: 120px;
        height: 120px;
        background: #f8f9fa;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
    }
    
    .profile-avatar i {
        font-size: 4rem;
        color: #6c757d;
    }
    
    .form-control:read-only {
        background-color: #f8f9fa;
    }
    
    .password-section {
        border-top: 1px solid #eee;
        margin-top: 2rem;
        padding-top: 2rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="profile-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="text-dark mb-1">Profile</h2>
                <p class="text-muted mb-0">Manage your account settings and preferences</p>
            </div>
            <span class="text-muted">{{ now()->format('M d, Y') }}</span>
        </div>
    </div>

    <!-- Profile Card -->
    <div class="card profile-card">
        <div class="card-body">
            <form id="profileForm">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div class="profile-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <h4>{{ $employee->name }}</h4>
                        <p class="text-muted">{{ $employee->role }}</p>
                    </div>
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ $employee->name }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ $employee->email }}" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="{{ $employee->phone }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">Role</label>
                                <input type="text" class="form-control" id="role" value="{{ $employee->role }}" readonly>
                            </div>
                        </div>

                        <!-- Password Change Section -->
                        <div class="password-section">
                            <h5 class="mb-3">Change Password</h5>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="current_password" name="current_password">
                                        <button class="btn btn-outline-secondary" type="button" id="toggleCurrentPassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="new_password" name="new_password">
                                        <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation">
                                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirmNewPassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary" id="resetForm">Reset</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle current password
    const toggleCurrentPassword = document.querySelector('#toggleCurrentPassword');
    const currentPassword = document.querySelector('#current_password');

    toggleCurrentPassword.addEventListener('click', function() {
        const type = currentPassword.getAttribute('type') === 'password' ? 'text' : 'password';
        currentPassword.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });

    // Toggle new password
    const toggleNewPassword = document.querySelector('#toggleNewPassword');
    const newPassword = document.querySelector('#new_password');

    toggleNewPassword.addEventListener('click', function() {
        const type = newPassword.getAttribute('type') === 'password' ? 'text' : 'password';
        newPassword.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });

    // Toggle confirm new password
    const toggleConfirmNewPassword = document.querySelector('#toggleConfirmNewPassword');
    const confirmNewPassword = document.querySelector('#new_password_confirmation');

    toggleConfirmNewPassword.addEventListener('click', function() {
        const type = confirmNewPassword.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmNewPassword.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });

    const form = document.getElementById('profileForm');
    const resetBtn = document.getElementById('resetForm');
    const originalData = {
        name: form.querySelector('#name').value,
        email: form.querySelector('#email').value,
        phone: form.querySelector('#phone').value
    };

    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
        // Only include password fields if they are filled
        if (!data.current_password) {
            delete data.current_password;
            delete data.new_password;
            delete data.new_password_confirmation;
        }

        fetch('{{ route("staff.profile.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    // Update original data after successful save
                    originalData.name = form.querySelector('#name').value;
                    originalData.email = form.querySelector('#email').value;
                    originalData.phone = form.querySelector('#phone').value;
                    
                    // Clear password fields
                    form.querySelector('#current_password').value = '';
                    form.querySelector('#new_password').value = '';
                    form.querySelector('#new_password_confirmation').value = '';
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while updating your profile.'
            });
        });
    });

    // Handle reset button
    resetBtn.addEventListener('click', function() {
        Swal.fire({
            title: 'Reset Changes?',
            text: 'Are you sure you want to reset all changes?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, reset it!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.querySelector('#name').value = originalData.name;
                form.querySelector('#email').value = originalData.email;
                form.querySelector('#phone').value = originalData.phone;
                form.querySelector('#current_password').value = '';
                form.querySelector('#new_password').value = '';
                form.querySelector('#new_password_confirmation').value = '';
                
                Swal.fire({
                    icon: 'success',
                    title: 'Reset!',
                    text: 'Your changes have been reset.',
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        });
    });
});
</script>
@endpush