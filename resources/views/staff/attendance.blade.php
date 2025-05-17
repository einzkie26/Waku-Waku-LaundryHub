@extends('layouts.staff-app')

@section('title', 'Mark Attendance')

@section('content')
<div class="container-fluid p-0 m-0 pt-4">
    <!-- Header -->
    <div class="mb-4 px-3 wrapper">
        <h2 class="text-dark">Mark Attendance</h2>
    </div>

    <!-- Main Content -->
    <div class="px-3 wrapper">
        <div class="row">
            <!-- Mark Attendance Card -->
            <div class="col-md-6 mb-4">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h5 class="mb-4">Today's Attendance</h5>
                        
                        @if($todayAttendance)
                            <div class="alert alert-info">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <div>
                                        <strong>Status:</strong> {{ $todayAttendance->status }}
                                        <br>
                                        <strong>Check-in:</strong> {{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('h:i A') }}
                                        @if($todayAttendance->check_out)
                                            <br>
                                            <strong>Check-out:</strong> {{ \Carbon\Carbon::parse($todayAttendance->check_out)->format('h:i A') }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            @if(!$todayAttendance->check_out)
                                <button type="button" class="btn btn-primary w-100" id="checkOutBtn">
                                    <i class="fas fa-sign-out-alt me-2"></i>Check Out
                                </button>
                            @endif
                        @else
                            <button type="button" class="btn btn-success w-100" id="checkInBtn">
                                <i class="fas fa-sign-in-alt me-2"></i>Check In
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Attendance History -->
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h5 class="mb-4">Recent Attendance</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Check In</th>
                                        <th>Check Out</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($attendanceHistory as $record)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($record->date)->format('M d, Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($record->check_in)->format('h:i A') }}</td>
                                        <td>{{ $record->check_out ? \Carbon\Carbon::parse($record->check_out)->format('h:i A') : '-' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $record->status === 'Present' ? 'success' : ($record->status === 'Late' ? 'warning' : 'danger') }}">
                                                {{ $record->status }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .wrapper {
        width: 1050px;
        margin: 0 auto;
        box-sizing: border-box;
    }
    .card {
        border: none;
        border-radius: 0.5rem;
    }
    .table {
        margin-bottom: 0;
    }
    .table th {
        font-weight: 600;
        color: #666;
        border-bottom: 1px solid #f0f0f0;
    }
    .table td {
        vertical-align: middle;
    }
    .badge {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
        border-radius: 1rem;
    }
    .btn {
        padding: 0.75rem 1rem;
        font-weight: 500;
    }
    .alert {
        border: none;
        border-radius: 0.5rem;
    }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing attendance buttons...');
    
    const checkInBtn = document.getElementById('checkInBtn');
    const checkOutBtn = document.getElementById('checkOutBtn');

    if (checkInBtn) {
        console.log('Check-in button found, adding click listener...');
        checkInBtn.addEventListener('click', function() {
            console.log('Check-in button clicked');
            markAttendance('check-in');
        });
    }

    if (checkOutBtn) {
        console.log('Check-out button found, adding click listener...');
        checkOutBtn.addEventListener('click', function() {
            console.log('Check-out button clicked');
            markAttendance('check-out');
        });
    }

    function markAttendance(type) {
        console.log('Marking attendance:', type);
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        console.log('CSRF Token:', token);
        
        const body = { type: type };
        fetch('/staff/attendance/mark', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify(body)
        })
        .then(response => {
            console.log('Response received:', response);
            return response.json();
        })
        .then(data => {
            console.log('Data:', data);
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Error marking attendance');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error marking attendance');
        });
    }
});
</script>
@endpush
@endsection