@extends('staff.layouts.app')

@section('content')
<div class="container mb-1">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow p-4">
                <h2 class="text-center mb-4">Create Laundry Order</h2>

                {{-- Success & Error Alerts --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @include('components.order-form', [
                    'formAction' => route('staff.tasks.store'),
                    'users' => $users,
                    'bookedDates' => $bookedDates
                ])
            </div>
        </div>
    </div>
</div>
@endsection 