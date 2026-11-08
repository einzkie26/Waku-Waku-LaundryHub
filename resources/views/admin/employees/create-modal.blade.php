<div class="modal fade employee-create-modal" id="employee-create-modal" tabindex="-1" aria-labelledby="employee-create-title" aria-hidden="true" data-open-on-load="{{ request()->boolean('add') || $errors->getBag('employeeForm')->any() ? 'true' : 'false' }}">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content" id="employee-create-form" method="POST" action="{{ route('employees.store') }}" data-single-submit>
            @csrf
            <div class="modal-header align-items-start">
                <h2 class="modal-title" id="employee-create-title">Add employee</h2>
                <button class="btn-close mt-1" type="button" data-bs-dismiss="modal" aria-label="Close add employee"></button>
            </div>
            <div class="modal-body">
                @if($errors->getBag('employeeForm')->any())
                    <div class="alert alert-danger" role="alert" id="employee-form-errors" tabindex="-1">
                        <strong>Please check the employee details.</strong>
                        <ul class="mb-0 mt-2">@foreach($errors->getBag('employeeForm')->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
                    </div>
                @endif
                @include('admin.employees.form-fields')
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" type="submit"><i class="bi bi-person-plus me-2" aria-hidden="true"></i>Add employee</button>
            </div>
        </form>
    </div>
</div>
