@extends('layouts.admin-app')

@section('content')
<div class="attendance-report-wrapper">
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-calendar-check text-primary me-2"></i>
                                Attendance Report
                            </h3>
                            <div class="d-flex gap-2">
                                <select id="month" class="form-select">
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>
                                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                        </option>
                                    @endforeach
                                </select>
                                <select id="year" class="form-select">
                                    @foreach(range(now()->year - 1, now()->year) as $y)
                                        <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endforeach
                                </select>
                                <button class="btn btn-primary" onclick="filterAttendance()" id="filterBtn">
                                    <i class="fas fa-filter me-1"></i>Filter
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Statistics Cards -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <div class="stat-card bg-primary bg-opacity-10 rounded-3 p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-primary text-white rounded-3 p-3 me-3">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div>
                                            <h6 class="stat-label text-muted mb-1">Total Staff</h6>
                                            <h3 class="stat-value mb-0">{{ $totalStaff }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card bg-success bg-opacity-10 rounded-3 p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-success text-white rounded-3 p-3 me-3">
                                            <i class="fas fa-user-check"></i>
                                        </div>
                                        <div>
                                            <h6 class="stat-label text-muted mb-1">On Duty Today</h6>
                                            <h3 class="stat-value mb-0">{{ $onDutyToday }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card bg-warning bg-opacity-10 rounded-3 p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-warning text-white rounded-3 p-3 me-3">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <div>
                                            <h6 class="stat-label text-muted mb-1">Late Arrivals</h6>
                                            <h3 class="stat-value mb-0">{{ $lateArrivals }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card bg-danger bg-opacity-10 rounded-3 p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-danger text-white rounded-3 p-3 me-3">
                                            <i class="fas fa-user-times"></i>
                                        </div>
                                        <div>
                                            <h6 class="stat-label text-muted mb-1">On Leave</h6>
                                            <h3 class="stat-value mb-0">{{ $onLeave }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Attendance Table -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0">Staff Member</th>
                                        <th class="border-0">Position</th>
                                        <th class="border-0 text-center">Days Present</th>
                                        <th class="border-0 text-center">Absences</th>
                                        <th class="border-0 text-center">Late Arrivals</th>
                                        <th class="border-0 text-center">On Time</th>
                                        <th class="border-0">Attendance Rate</th>
                                    </tr>
                                </thead>
                                <tbody id="attendanceTableBody">
                                    @forelse($attendanceRecords as $record)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle bg-primary bg-opacity-10 text-primary me-2">
                                                        {{ substr($record->employee->name, 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $record->employee->name }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $record->employee->position }}</td>
                                            <td class="text-center">{{ $record->days_present }}</td>
                                            <td class="text-center">{{ $record->absences }}</td>
                                            <td class="text-center">{{ $record->late_arrivals }}</td>
                                            <td class="text-center">{{ $record->on_time }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                        <div class="progress-bar {{ $record->attendance_rate >= 90 ? 'bg-success' : ($record->attendance_rate >= 75 ? 'bg-warning' : 'bg-danger') }}"
                                                             role="progressbar"
                                                             style="width: {{ $record->attendance_rate }}%"
                                                             aria-valuenow="{{ $record->attendance_rate }}"
                                                             aria-valuemin="0"
                                                             aria-valuemax="100">
                                                        </div>
                                                    </div>
                                                    <span class="attendance-rate {{ $record->attendance_rate >= 90 ? 'text-success' : ($record->attendance_rate >= 75 ? 'text-warning' : 'text-danger') }}">
                                                        {{ $record->attendance_rate }}%
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <div class="empty-state">
                                                    <i class="fas fa-calendar-times text-muted mb-3" style="font-size: 3rem;"></i>
                                                    <h5 class="text-muted">No attendance records found</h5>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
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
.attendance-report-wrapper {
    width: 100%;
    max-width: 1000px;
    margin: 0 auto;
    padding: 0.5rem 1rem 1rem 1rem;
    margin-left: 260px; /* Offset for sidebar */
}
@media (max-width: 991.98px) {
    .attendance-report-wrapper {
        margin-left: 0;
        padding: 1rem 0.5rem 2rem 0.5rem;
    }
}
.stat-card {
    transition: transform 0.2s;
}
.stat-card:hover {
    transform: translateY(-2px);
}
.stat-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}
.stat-label {
    font-size: 0.875rem;
    font-weight: 500;
}
.stat-value {
    font-size: 1.5rem;
    font-weight: 600;
}
.avatar-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
}
.progress {
    background-color: #f8f9fa;
    border-radius: 1rem;
}
.attendance-rate {
    font-weight: 600;
    font-size: 0.875rem;
    min-width: 45px;
}
.empty-state {
    padding: 2rem;
    text-align: center;
}
.table th {
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.table td {
    font-size: 0.875rem;
}
.form-select {
    border-radius: 0.5rem;
    border-color: #e9ecef;
    font-size: 0.875rem;
}
.btn {
    border-radius: 0.5rem;
    font-weight: 500;
}
@media (max-width: 768px) {
    .attendance-report-wrapper {
        padding: 0 0.5rem;
    }
    .card-header .d-flex {
        flex-direction: column;
        gap: 1rem;
    }
    .card-header .d-flex > div {
        width: 100%;
    }
    .form-select, .btn {
        width: 100%;
    }
}
</style>

@push('scripts')
<script>
function filterAttendance() {
    const month = document.getElementById('month').value;
    const year = document.getElementById('year').value;
    const filterBtn = document.getElementById('filterBtn');
    const tbody = document.getElementById('attendanceTableBody');

    // Show loading state
    filterBtn.disabled = true;
    filterBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Loading...';
    tbody.innerHTML = `
        <tr>
            <td colspan="7" class="text-center py-4">
                <div class="empty-state">
                    <i class="fas fa-spinner fa-spin text-primary mb-3" style="font-size: 3rem;"></i>
                    <h5 class="text-muted">Loading attendance data...</h5>
                </div>
            </td>
        </tr>
    `;

    fetch(`/admin/reports/attendance/filter?month=${month}&year=${year}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        tbody.innerHTML = '';

        if (data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <div class="empty-state">
                            <i class="fas fa-calendar-times text-muted mb-3" style="font-size: 3rem;"></i>
                            <h5 class="text-muted">No attendance records found</h5>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        data.forEach(record => {
            const progressBarClass = record.attendance_rate >= 90 ? 'bg-success' : 
                                   (record.attendance_rate >= 75 ? 'bg-warning' : 'bg-danger');
            const textClass = record.attendance_rate >= 90 ? 'text-success' : 
                            (record.attendance_rate >= 75 ? 'text-warning' : 'text-danger');

            tbody.innerHTML += `
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle bg-primary bg-opacity-10 text-primary me-2">
                                ${record.employee.name.charAt(0)}
                            </div>
                            <div>
                                <h6 class="mb-0">${record.employee.name}</h6>
                            </div>
                        </div>
                    </td>
                    <td>${record.employee.position}</td>
                    <td class="text-center">${record.days_present}</td>
                    <td class="text-center">${record.absences}</td>
                    <td class="text-center">${record.late_arrivals}</td>
                    <td class="text-center">${record.on_time}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                <div class="progress-bar ${progressBarClass}"
                                     role="progressbar"
                                     style="width: ${record.attendance_rate}%"
                                     aria-valuenow="${record.attendance_rate}"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                </div>
                            </div>
                            <span class="attendance-rate ${textClass}">
                                ${record.attendance_rate}%
                            </span>
                        </div>
                    </td>
                </tr>
            `;
        });
    })
    .catch(error => {
        console.error('Error:', error);
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4">
                    <div class="empty-state">
                        <i class="fas fa-exclamation-circle text-danger mb-3" style="font-size: 3rem;"></i>
                        <h5 class="text-danger">Error loading attendance data</h5>
                    </div>
                </td>
            </tr>
        `;
    })
    .finally(() => {
        // Reset button state
        filterBtn.disabled = false;
        filterBtn.innerHTML = '<i class="fas fa-filter me-1"></i>Filter';
    });
}
</script>
@endpush
