@extends('admin.layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h4 class="mb-0">Employee Details</h4>
        </div>
        <div class="card-body bg-white">
            <dl class="row">
                <dt class="col-sm-3 text-muted">Name</dt>
                <dd class="col-sm-9">{{ $employee->name }}</dd>

                <dt class="col-sm-3 text-muted">Email</dt>
                <dd class="col-sm-9">{{ $employee->email }}</dd>

                <dt class="col-sm-3 text-muted">Position</dt>
                <dd class="col-sm-9">{{ $employee->position }}</dd>

                <dt class="col-sm-3 text-muted">Contact</dt>
                <dd class="col-sm-9">{{ $employee->contact }}</dd>

                <dt class="col-sm-3 text-muted">Hire Date</dt>
                <dd class="col-sm-9">{{ $employee->hire_date }}</dd>

                <dt class="col-sm-3 text-muted">Shift</dt>
                <dd class="col-sm-9">{{ $employee->shift }}</dd>

                <dt class="col-sm-3 text-muted">Status</dt>
                <dd class="col-sm-9">{{ $employee->status }}</dd>
            </dl>
            <a href="{{ route('employees.list') }}" class="btn btn-secondary mt-3"><i class="fas fa-arrow-left me-1"></i> Back to Staff List</a>
        </div>
    </div>
</div>

@push('styles')
<style>
    body {
        background-color: #f8f9fa;
    }
    .card {
        border: none;
        box-shadow: 0 0 15px rgba(0,0,0,0.05);
    }
    .card-header {
        border-bottom: 1px solid #e9ecef;
    }
    dt {
        font-weight: 500;
    }
    dd {
        margin-bottom: 0.5rem;
    }
</style>
@endpush
@endsection 