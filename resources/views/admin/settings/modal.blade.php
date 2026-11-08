@php
    $settingsOld = fn ($key, $default = '') => $errors->getBag('settingsForm')->any() ? old($key, $default) : $default;
@endphp
<div class="modal fade admin-form-modal" id="admin-settings-modal" tabindex="-1" aria-labelledby="admin-settings-title" aria-hidden="true" data-admin-modal data-open-on-load="{{ request()->routeIs('settings.profile') || $errors->getBag('settingsForm')->any() ? 'true' : 'false' }}">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content" id="admin-settings-form" method="POST" action="{{ route('settings.profile.update') }}" data-single-submit>
            @csrf
            @method('PUT')
            <input type="hidden" name="_return_to" value="{{ request()->routeIs('settings.profile') ? route('admin.dashboard', [], false) : \App\Support\AdminModal::pageUrl(request()) }}">
            <div class="modal-header">
                <h2 class="modal-title" id="admin-settings-title">Account settings</h2>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close account settings"></button>
            </div>
            <div class="modal-body">
                @include('admin.components.modal-errors', ['bag' => 'settingsForm'])
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="form-label" for="settings-name">Full name</label>
                        <input class="form-control @error('name', 'settingsForm') is-invalid @enderror" id="settings-name" name="name" value="{{ $settingsOld('name', auth()->user()->name) }}" maxlength="255" autocomplete="name" data-modal-initial-focus required>
                        @error('name', 'settingsForm')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" for="settings-username">Username</label>
                        <input class="form-control @error('username', 'settingsForm') is-invalid @enderror" id="settings-username" name="username" value="{{ $settingsOld('username', auth()->user()->username) }}" maxlength="255" autocomplete="username" required>
                        @error('username', 'settingsForm')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="settings-phone">Phone number <span class="text-muted fw-normal">(optional)</span></label>
                        <input class="form-control @error('phone', 'settingsForm') is-invalid @enderror" type="tel" id="settings-phone" name="phone" value="{{ $settingsOld('phone', auth()->user()->phone) }}" maxlength="20" autocomplete="tel">
                        @error('phone', 'settingsForm')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="settings-address">Address <span class="text-muted fw-normal">(optional)</span></label>
                        <textarea class="form-control @error('address', 'settingsForm') is-invalid @enderror" id="settings-address" name="address" maxlength="255" rows="2" autocomplete="street-address">{{ $settingsOld('address', auth()->user()->address) }}</textarea>
                        @error('address', 'settingsForm')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12"><hr><h3 class="h6">Change password</h3><p class="form-text mb-0">Leave these fields blank to keep your current password.</p></div>
                    <div class="col-12">
                        <label class="form-label" for="settings-current-password">Current password</label>
                        <input class="form-control @error('current_password', 'settingsForm') is-invalid @enderror" type="password" id="settings-current-password" name="current_password" autocomplete="current-password">
                        @error('current_password', 'settingsForm')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" for="settings-password">New password</label>
                        <input class="form-control @error('password', 'settingsForm') is-invalid @enderror" type="password" id="settings-password" name="password" minlength="8" autocomplete="new-password">
                        @error('password', 'settingsForm')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" for="settings-password-confirmation">Confirm new password</label>
                        <input class="form-control" type="password" id="settings-password-confirmation" name="password_confirmation" minlength="8" autocomplete="new-password">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" type="submit">Save changes</button>
            </div>
        </form>
    </div>
</div>
