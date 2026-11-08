<div class="modal fade admin-form-modal" id="customer-create-modal" tabindex="-1" aria-labelledby="customer-create-title" aria-hidden="true" data-admin-modal data-clear-query="add" data-open-on-load="{{ request()->boolean('add') || $errors->getBag('customerForm')->any() ? 'true' : 'false' }}">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content" id="customer-create-form" method="POST" action="{{ route('admin.customers.store') }}" data-single-submit>
            @csrf
            <input type="hidden" name="_admin_modal" value="customer-create">
            <input type="hidden" name="_return_to" value="{{ \App\Support\AdminModal::pageUrl(request()) }}">
            <div class="modal-header">
                <h2 class="modal-title" id="customer-create-title">Add customer</h2>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close add customer"></button>
            </div>
            <div class="modal-body">
                @include('admin.components.modal-errors', ['bag' => 'customerForm'])
                @include('admin.customers.form-fields', ['customerRecord' => null, 'customerFieldPrefix' => 'customer-create', 'customerErrorBag' => 'customerForm'])
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" type="submit">Add customer</button>
            </div>
        </form>
    </div>
</div>
