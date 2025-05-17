@extends('layouts.staff-app')

@section('title', 'Dashboard')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
<style>
    .welcome-text {
        position: relative;
        overflow: hidden;
    }
    
    .welcome-text::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 3px;
        background: linear-gradient(90deg, var(--primary-color), transparent);
        animation: underline 1.5s ease-in-out infinite alternate;
    }
    
    @keyframes underline {
        0% { width: 50px; opacity: 0.7; }
        100% { width: 100%; opacity: 1; }
    }
    
    .stat-card {
        border-left: 4px solid;
        transition: all 0.3s ease;
        overflow: hidden;
        position: relative;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(45deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
        z-index: 0;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .stat-card:hover::before {
        opacity: 1;
    }
    
    .stat-icon {
        font-size: 1.8rem;
        opacity: 0.8;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover .stat-icon {
        transform: scale(1.1);
        opacity: 1;
    }
    
    .stat-number {
        font-size: 1.75rem;
        font-weight: 600;
        margin: 5px 0;
        display: block;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover .stat-number {
        transform: translateY(-3px);
    }
    
    .stat-text {
        font-size: 0.9rem;
        opacity: 0.8;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover .stat-text {
        opacity: 1;
    }
    
    .recent-orders {
        border-radius: 12px;
        overflow: hidden;
    }
    
    .table th {
        border-top: none;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: capitalize;
    }

    .wrapper {
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .card {
        border: none;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        border-radius: 10px;
    }
    
    .card-header {
        border-bottom: 1px solid #eee;
        padding: 1rem;
    }
    
    .table th {
        font-weight: 600;
        color: #666;
    }
    
    .badge {
        padding: 0.5em 0.75em;
        font-weight: 500;
    }

    .dashboard-header {
        background: #fff;
        padding: 1.5rem;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        margin-bottom: 1.5rem;
    }

    .dashboard-stats {
        margin-bottom: 1.5rem;
    }

    .dashboard-content {
        margin-bottom: 1.5rem;
    }

    .quick-actions {
        margin-bottom: 1.5rem;
    }

    .quick-action-btn {
        padding: 1rem;
        border-radius: 10px;
        transition: all 0.3s ease;
    }

    .quick-action-btn:hover {
        transform: translateY(-3px);
    }
</style>
@endpush

@section('content')
<div class="container-fluid p-0 m-0 pt-4">
    <!-- Header -->
    <div class="dashboard-header" data-aos="fade-up">
        <div class="d-flex justify-content-between align-items-center">
            <div>
        <h2 class="text-dark mb-1">Dashboard</h2>
        <p class="text-muted welcome-text" style="width: fit-content;">Welcome back, {{ $employee->name }}! 👋</p>
            </div>
            <div class="quick-actions">
                <a href="{{ route('staff.attendance') }}" class="btn btn-outline-primary">
                    <i class="fas fa-user-clock me-2"></i>Mark Attendance
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="dashboard-stats">
        <div class="row g-4">
            <!-- Today's Tasks -->
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-card card h-100 border-left-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted stat-text">Today's Tasks</span>
                                <h3 class="mb-0 stat-number" id="todayTasks">{{ $todayTasks }}</h3>
                            </div>
                            <div class="bg-soft-primary p-3 rounded-circle">
                                <i class="fas fa-tasks text-primary stat-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Completed Tasks -->
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-card card h-100 border-left-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted stat-text">Completed Tasks</span>
                                <h3 class="mb-0 stat-number" id="completedTasks">{{ $completedTasks }}</h3>
                            </div>
                            <div class="bg-soft-success p-3 rounded-circle">
                                <i class="fas fa-check-circle text-success stat-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Status -->
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-card card h-100 border-left-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted stat-text">Attendance</span>
                                @if($attendanceStatus['status'] === 'checked_in')
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-success status-badge me-2">Checked In</span>
                                        <div class="spinner-grow spinner-grow-sm text-success" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-1">Since: {{ $attendanceStatus['time'] }}</small>
                                @else
                                    <span class="badge bg-warning status-badge">Not Checked In</span>
                                @endif
                            </div>
                            <div class="bg-soft-warning p-3 rounded-circle">
                                <i class="fas fa-user-clock text-warning stat-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Work Hours -->
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="400">
                <div class="stat-card card h-100 border-left-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted stat-text">Work Hours</span>
                                <h3 class="mb-0 stat-number" id="workHours">{{ $workHours }} hrs</h3>
                            </div>
                            <div class="bg-soft-info p-3 rounded-circle">
                                <i class="fas fa-clock text-info stat-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders and Attendance -->
    <div class="dashboard-content">
        <div class="row g-4">
            <!-- Recent Orders -->
            <div class="col-lg-8" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-semibold">Recent Orders</h5>
                        <a href="{{ route('staff.orders.index') }}" class="btn btn-sm btn-outline-primary">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="card-body p-0">
                        @if($recentOrders->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4">Reference</th>
                                            <th>Customer</th>
                                            <th>Status</th>
                                            <th class="text-end pe-4">Due Date</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recentOrdersTable">
                                        @foreach($recentOrders as $index => $order)
                                        <tr class="order-row" data-aos="fade-up" data-aos-delay="{{ 100 + ($index * 50) }}" data-bs-toggle="tooltip" data-bs-placement="top" title="View Order Details">
                                            <td class="ps-4 fw-medium">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-2">
                                                        <i class="fas fa-receipt text-primary"></i>
                                                    </div>
                                                    <div>
                                                        {{ $order->reference }}
                                                        <div class="text-muted small">{{ $order->created_at->diffForHumans() }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm me-2">
                                                        <div class="avatar-title bg-soft-primary text-primary rounded-circle">
                                                            {{ substr($order->customer_name, 0, 1) }}
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="fw-medium">{{ $order->customer_name }}</div>
                                                        <div class="text-muted small">{{ $order->items_count }} items</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-soft-{{ $order->status_color }} text-{{ $order->status_color }} d-inline-flex align-items-center">
                                                    <span class="bullet bullet-dot bg-{{ $order->status_color }} me-1"></span>
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="fw-medium">{{ $order->due_date->format('M d, Y') }}</div>
                                                <div class="text-muted small">{{ $order->due_date->diffForHumans() }}</div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center p-5">
                                <div class="mb-3">
                                    <i class="fas fa-inbox fa-3x text-muted"></i>
                                </div>
                                <h5 class="text-muted mb-3">No Recent Orders</h5>
                                <p class="text-muted mb-0">When you have new orders, they'll appear here.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Attendance History -->
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="150">
                <div class="card h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-semibold">Attendance History</h5>
                        <a href="{{ route('staff.attendance') }}" class="btn btn-sm btn-outline-secondary">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="card-body p-0">
                        @if($attendanceHistory->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4">Date</th>
                                            <th>Status</th>
                                            <th class="text-end pe-4">Time</th>
                                        </tr>
                                    </thead>
                                    <tbody id="attendanceHistoryTable">
                                        @foreach($attendanceHistory as $index => $record)
                                        <tr data-aos="fade-up" data-aos-delay="{{ 150 + ($index * 50) }}">
                                            <td class="ps-4">
                                                <div class="fw-medium">{{ $record->date->format('M d, Y') }}</div>
                                                <div class="text-muted small">{{ $record->date->format('l') }}</div>
                                            </td>
                                            <td>
                                                @php
                                                    $statusClass = [
                                                        'checked_in' => 'success',
                                                        'checked_out' => 'primary',
                                                        'on_break' => 'warning',
                                                        'late' => 'danger',
                                                        'absent' => 'secondary'
                                                    ][$record->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-soft-{{ $statusClass }} text-{{ $statusClass }} d-inline-flex align-items-center">
                                                    @if($record->status === 'checked_in')
                                                        <i class="fas fa-sign-in-alt me-1"></i>
                                                    @elseif($record->status === 'checked_out')
                                                        <i class="fas fa-sign-out-alt me-1"></i>
                                                    @elseif($record->status === 'on_break')
                                                        <i class="fas fa-coffee me-1"></i>
                                                    @elseif($record->status === 'late')
                                                        <i class="fas fa-clock me-1"></i>
                                                    @else
                                                        <i class="fas fa-user-clock me-1"></i>
                                                    @endif
                                                    {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                                                </span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="fw-medium">{{ $record->check_in }}</div>
                                                @if($record->check_out)
                                                    <div class="text-muted small">Out: {{ $record->check_out }}</div>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center p-5">
                                <div class="mb-3">
                                    <i class="fas fa-user-clock fa-3x text-muted"></i>
                                </div>
                                <h5 class="text-muted mb-3">No Attendance Records</h5>
                                <p class="text-muted mb-0">Your attendance records will appear here.</p>
                            </div>
                        @endif
                    </div>
                    @if($attendanceHistory->count() > 0)
                    <div class="card-footer bg-transparent border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Last updated: {{ now()->format('M d, Y h:i A') }}</small>
                            <button class="btn btn-sm btn-link p-0" id="refreshAttendance">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Initialize AOS animation
        AOS.init({
            duration: 600,
            easing: 'ease-out-cubic',
            once: true,
            offset: 50
        });

        // Animate counter
        function animateCounter(element, target, duration = 1000) {
            target = parseInt(target);
            if (isNaN(target) || target < 0) target = 0; // Prevent negative or NaN

            const start = 0;
            const range = target - start;
            let current = start;
            if (range === 0) {
                element.textContent = target;
                return;
            }
            const increment = target > start ? 1 : -1;
            const stepTime = Math.abs(Math.floor(duration / Math.abs(range)));

            const timer = setInterval(() => {
                current += increment;
                element.textContent = current;

                if (current === target) {
                    clearInterval(timer);
                }
            }, stepTime);
        }

        // Update dashboard stats with animation
        function updateDashboard(data) {
            console.log('Dashboard data:', data); // Debugging output
            if (data.todayTasks !== undefined) {
                const element = document.getElementById('todayTasks');
                animateCounter(element, parseInt(data.todayTasks));
            }
            if (data.completedTasks !== undefined) {
                const element = document.getElementById('completedTasks');
                animateCounter(element, parseInt(data.completedTasks));
            }
            if (data.workHours !== undefined) {
                const element = document.getElementById('workHours');
                const currentValue = parseFloat(element.textContent);
                const targetValue = parseFloat(data.workHours);
                const duration = 1000;
                const startTime = performance.now();
                
                function updateValue(timestamp) {
                    const elapsed = timestamp - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    const currentValue = currentValue + (targetValue - currentValue) * progress;
                    
                    element.textContent = currentValue.toFixed(1) + ' hrs';
                    
                    if (progress < 1) {
                        requestAnimationFrame(updateValue);
                    }
                }
                
                requestAnimationFrame(updateValue);
            }
            if (data.recentOrders) {
                updateOrdersTable(data.recentOrders);
            }
        }

        // Update attendance data with animation
        function updateAttendance(data) {
            if (data.attendanceStatus !== undefined) {
                const statusElement = document.querySelector('#attendanceStatus .badge');
                if (statusElement) {
                    // Animate status change
                    statusElement.style.transform = 'scale(1.1)';
                    setTimeout(() => {
                        statusElement.style.transform = 'scale(1)';
                        statusElement.textContent = data.attendanceStatus;
                    }, 200);
                }
            }
            if (data.attendanceHistory) {
                updateAttendanceTable(data.attendanceHistory);
            }
        }

        // Update orders table with animation
        function updateOrdersTable(orders) {
            const tbody = document.getElementById('recentOrdersTable');
            if (!tbody) return;
            
            // Clear all existing rows before adding new ones
            tbody.innerHTML = '';
            
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const newRows = [];
            
            // Create new rows with animation
            orders.forEach((order, index) => {
                const row = document.createElement('tr');
                row.className = 'order-row';
                row.style.opacity = '0';
                row.style.transform = 'translateY(20px)';
                row.style.transition = 'all 0.3s ease-out';
                
                row.innerHTML = `
                    <td class="ps-4 fw-medium">
                        <div class="d-flex align-items-center">
                            <div class="me-2">
                                <i class="fas fa-receipt text-primary"></i>
                            </div>
                            <div>
                                ${order.reference}
                                <div class="text-muted small">${new Date(order.created_at).toLocaleString()}</div>
                            </div>
                        </div>
                    </td>
                    <td>${order.customer_name}</td>
                    <td>
                        <span class="badge bg-${order.status_color} d-inline-flex align-items-center">
                            <span class="bullet bullet-dot bg-${order.status_color} me-1"></span>
                            ${order.status}
                        </span>
                    </td>
                    <td>${new Date(order.due_date).toLocaleDateString()}</td>
                `;
                
                newRows.push(row);
                
                // Animate in new row
                setTimeout(() => {
                    row.style.opacity = '1';
                    row.style.transform = 'translateY(0)';
                }, 50 * index);
            });
            
            // Add new rows to the top
            newRows.reverse().forEach(row => {
                tbody.insertBefore(row, tbody.firstChild);
            });
            
            // Remove old rows if needed
            if (newRows.length > 0 && tbody.children.length > 5) {
                while (tbody.children.length > 5) {
                    tbody.removeChild(tbody.lastChild);
                }
            }
        }

        // Update attendance table with animation
        function updateAttendanceTable(records) {
            const tbody = document.getElementById('attendanceHistoryTable');
            if (!tbody) return;
            
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const newRows = [];
            
            records.forEach((record, index) => {
                const existingRow = rows.find(row => 
                    row.querySelector('td:first-child')?.textContent?.includes(record.date)
                );
                
                if (!existingRow) {
                    const row = document.createElement('tr');
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(20px)';
                    row.style.transition = 'all 0.3s ease-out';
                    
                    const statusClass = {
                        'checked_in': 'success',
                        'checked_out': 'primary',
                        'on_break': 'warning',
                        'late': 'danger',
                        'absent': 'secondary'
                    }[record.status] || 'secondary';
                    
                    const statusIcon = {
                        'checked_in': 'sign-in-alt',
                        'checked_out': 'sign-out-alt',
                        'on_break': 'coffee',
                        'late': 'clock',
                        'absent': 'user-clock'
                    }[record.status] || 'user-clock';
                    
                    row.innerHTML = `
                        <td>${record.date}</td>
                        <td>
                            <span class="badge bg-${statusClass} d-inline-flex align-items-center">
                                <i class="fas fa-${statusIcon} me-1"></i>
                                ${record.status.replace('_', ' ')}
                            </span>
                        </td>
                        <td>${record.check_in}${record.check_out ? '<br><small class="text-muted">Out: ' + record.check_out + '</small>' : ''}</td>
                    `;
                    
                    newRows.push(row);
                    
                    // Animate in new row
                    setTimeout(() => {
                        row.style.opacity = '1';
                        row.style.transform = 'translateX(0)';
                    }, 50 * index);
                }
            });
            
            // Add new rows to the top
            newRows.reverse().forEach(row => {
                tbody.insertBefore(row, tbody.firstChild);
            });
            
            // Remove old rows if needed
            if (newRows.length > 0 && rows.length + newRows.length > 5) {
                for (let i = 0; i < newRows.length; i++) {
                    if (tbody.children.length > 5) {
                        tbody.removeChild(tbody.lastChild);
                    }
                }
            }
            
            // Update last updated time
            const lastUpdated = document.querySelector('.last-updated');
            if (lastUpdated) {
                lastUpdated.textContent = `Last updated: ${new Date().toLocaleString()}`;
            }
        }

        // Handle refresh button click
        const refreshBtn = document.getElementById('refreshAttendance');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', function() {
                // Add rotation animation
                this.classList.add('fa-spin');
                
                // Fetch updated data
                fetch('/staff/dashboard/data')
                    .then(response => response.json())
                    .then(data => {
                        updateDashboard(data);
                        updateAttendance(data);
                    })
                    .catch(error => {
                        console.error('Error fetching dashboard data:', error);
                    })
                    .finally(() => {
                        // Remove rotation animation
                        setTimeout(() => {
                            this.classList.remove('fa-spin');
                        }, 500);
                    });
            });
        }

        // Refresh dashboard data every 30 seconds
        let refreshInterval = setInterval(fetchDashboardData, 30000);
        
        // Function to fetch dashboard data
        function fetchDashboardData() {
            fetch('/staff/dashboard/data')
                .then(response => response.json())
                .then(data => {
                    updateDashboard(data);
                    updateAttendance(data);
                })
                .catch(error => console.error('Error fetching dashboard data:', error));
        }
        
        // Initial data fetch
        fetchDashboardData();
        
        // Handle page visibility changes
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                clearInterval(refreshInterval);
            } else {
                // Refresh immediately when tab becomes visible
                fetchDashboardData();
                // Then continue with regular refresh
                refreshInterval = setInterval(fetchDashboardData, 30000);
            }
        });
    });
</script>
@endpush

<!-- Quick Task Modal -->
<div class="modal fade" id="quickTaskModal" tabindex="-1" aria-labelledby="quickTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold" id="quickTaskModalLabel">Create New Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="quickTaskForm">
                    <div class="mb-3">
                        <label for="taskTitle" class="form-label">Task Title</label>
                        <input type="text" class="form-control" id="taskTitle" placeholder="Enter task title" required>
                    </div>
                    <div class="mb-3">
                        <label for="taskDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="taskDescription" rows="3" placeholder="Enter task description"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="taskDueDate" class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="taskDueDate" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="taskPriority" class="form-label">Priority</label>
                            <select class="form-select" id="taskPriority">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection