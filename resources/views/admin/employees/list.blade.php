@extends('admin.layouts.app')

@section('content')
<div class="card shadow-sm">
    {{-- Header Section --}}
    <div class="card-header bg-white py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Staff Management</h5>
            <a href="{{ route('employees.add') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add Staff
            </a>
        </div>
    </div>

    {{-- Search Section --}}
    <div class="card-body border-bottom">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="position-relative">
                    <input type="text" 
                           class="form-control" 
                           id="employeeSearch" 
                           placeholder="Search staff by name, email, or position..."
                           style="min-width: 300px;">
                    <div class="position-absolute end-0 top-50 translate-middle-y me-2">
                        <i class="fas fa-search text-muted"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="btn-group">
                    <a href="{{ route('employees.list') }}" class="btn btn-outline-secondary{{ request('status') ? '' : ' active' }}">All</a>
                    <a href="{{ route('employees.list', ['status' => 'active']) }}" class="btn btn-outline-secondary{{ request('status') === 'active' ? ' active' : '' }}">Active</a>
                    <a href="{{ route('employees.list', ['status' => 'inactive']) }}" class="btn btn-outline-secondary{{ request('status') === 'inactive' ? ' active' : '' }}">Inactive</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Table Section --}}
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="py-3 ps-3">Name</th>
                        <th class="py-3">Email</th>
                        <th class="py-3">Position</th>
                        <th class="py-3">Status</th>
                        <th class="py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="employeeTableBody">
                    @forelse ($employees as $employee)
                        <tr>
                            <td class="py-3 ps-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-2">
                                        <img src="{{ $employee->photo ?? asset('images/default-avatar.png') }}" 
                                             alt="{{ $employee->name }}" 
                                             class="rounded-circle"
                                             width="32" height="32">
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $employee->name }}</h6>
                                        <small class="text-muted">ID: {{ $employee->id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3">{{ $employee->email }}</td>
                            <td class="py-3">
                                <span class="badge bg-info">{{ $employee->position }}</span>
                            </td>
                            <td class="py-3">
                                <span class="badge {{ $employee->status === 'Active' ? 'bg-success' : 'bg-secondary' }} rounded-pill">
                                    {{ $employee->status }}
                                </span>
                            </td>
                            <td class="py-3 text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-sm btn-light" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($employee->status === 'Active')
                                        <form action="{{ route('employees.deactivate', $employee->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-light text-danger" title="Set Inactive">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('employees.activate', $employee->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-light text-success" title="Set Active">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-people me-2"></i>No staff members found
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Loading Spinner --}}
        <div id="loadingSpinner" class="d-none text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        {{-- Pagination --}}
        @if($employees->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $employees->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

@include('admin.partials.employees-inactivemodal')
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('employeeSearch');
    const tableBody = document.getElementById('employeeTableBody');
    const rows = tableBody.getElementsByTagName('tr');
    let searchTimeout;

    // Search functionality
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = this.value.toLowerCase().trim();
        
        searchTimeout = setTimeout(() => {
            Array.from(rows).forEach(row => {
                const name = row.querySelector('h6')?.textContent.toLowerCase() || '';
                const email = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
                const position = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
                
                const shouldShow = searchTerm === '' || 
                                 name.includes(searchTerm) || 
                                 email.includes(searchTerm) || 
                                 position.includes(searchTerm);
                
                row.style.display = shouldShow ? '' : 'none';
            });
        }, 150);
    });

    // Clear search when clicking the search icon
    searchInput.parentElement.querySelector('.fa-search').addEventListener('click', function() {
        searchInput.value = '';
        searchInput.dispatchEvent(new Event('input'));
    });
});

function showSpinner() {
    document.getElementById('employeeTableBody').classList.add('d-none');
    document.getElementById('loadingSpinner').classList.remove('d-none');
}

function hideSpinner() {
    document.getElementById('employeeTableBody').classList.remove('d-none');
    document.getElementById('loadingSpinner').classList.add('d-none');
}
</script>
@endsection
