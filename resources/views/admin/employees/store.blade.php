@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="mb-3">Staff Member Added Successfully!</h4>
                    <p class="text-muted mb-4">The new staff member has been added to the system.</p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ route('employees.list') }}" class="btn btn-primary">
                            <i class="bi bi-people me-1"></i> View All Staff
                        </a>
                        <a href="{{ route('employees.add') }}" class="btn btn-light">
                            <i class="bi bi-person-plus me-1"></i> Add Another Staff
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Redirect to list page after 3 seconds
    setTimeout(function() {
        window.location.href = "{{ route('employees.list') }}";
    }, 3000);
</script>
@endpush
@endsection
