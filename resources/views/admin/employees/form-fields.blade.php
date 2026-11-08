<div class="row g-3">
    <div class="col-sm-6">
        <label class="form-label" for="employee-name">Full name</label>
        <input class="form-control @error('name', 'employeeForm') is-invalid @enderror" id="employee-name" name="name" value="{{ old('name') }}" maxlength="255" autocomplete="name" required>
        @error('name', 'employeeForm')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-sm-6">
        <label class="form-label" for="employee-contact">Phone number</label>
        <input class="form-control @error('contact', 'employeeForm') is-invalid @enderror" type="tel" id="employee-contact" name="contact" value="{{ old('contact') }}" maxlength="20" autocomplete="tel" required>
        @error('contact', 'employeeForm')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-sm-6">
        <label class="form-label" for="employee-position">Position</label>
        <input class="form-control @error('position', 'employeeForm') is-invalid @enderror" list="staff-positions" id="employee-position" name="position" value="{{ old('position') }}" maxlength="255" placeholder="e.g. Laundry attendant" required>
        <datalist id="staff-positions"><option value="Staff"><option value="Manager"><option value="Cashier"><option value="Delivery rider"><option value="Laundry attendant"></datalist>
        @error('position', 'employeeForm')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-sm-6">
        <label class="form-label" for="employee-shift">Work shift</label>
        <select class="form-select @error('shift', 'employeeForm') is-invalid @enderror" id="employee-shift" name="shift" required>
            <option value="">Choose a shift</option>
            @foreach(['Morning', 'Afternoon', 'Night'] as $shift)<option @selected(old('shift') === $shift)>{{ $shift }}</option>@endforeach
        </select>
        @error('shift', 'employeeForm')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label" for="employee-hire-date">Hire date</label>
        <input class="form-control @error('hire_date', 'employeeForm') is-invalid @enderror" type="date" id="employee-hire-date" name="hire_date" value="{{ old('hire_date', today()->toDateString()) }}" required>
        @error('hire_date', 'employeeForm')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12"><hr><div class="admin-section-kicker mb-0">STAFF SIGN-IN</div></div>
    <div class="col-12">
        <label class="form-label" for="employee-email">Staff email</label>
        <input class="form-control @error('email', 'employeeForm') is-invalid @enderror" type="email" id="employee-email" name="email" value="{{ old('email') }}" maxlength="255" autocomplete="off" required>
        @error('email', 'employeeForm')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-sm-6">
        <label class="form-label" for="employee-password">Password</label>
        <input class="form-control @error('password', 'employeeForm') is-invalid @enderror" type="password" id="employee-password" name="password" minlength="8" autocomplete="new-password" required>
        @error('password', 'employeeForm')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-sm-6">
        <label class="form-label" for="employee-password-confirmation">Confirm password</label>
        <input class="form-control" type="password" id="employee-password-confirmation" name="password_confirmation" minlength="8" autocomplete="new-password" required>
    </div>
    <div class="col-12"><p class="form-text mb-0">Use at least 8 characters. The employee can sign in with this email and password once added.</p></div>
</div>
