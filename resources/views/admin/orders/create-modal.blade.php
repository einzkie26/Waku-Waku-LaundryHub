<div class="modal fade admin-form-modal admin-order-modal" id="order-create-modal" tabindex="-1" aria-labelledby="order-create-title" aria-hidden="true" data-admin-modal data-clear-query="new" data-open-on-load="{{ request()->boolean('new') || $errors->getBag('orderForm')->any() ? 'true' : 'false' }}">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="order-create-title">New order</h2>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close new order"></button>
            </div>
            <div class="modal-body">
                @include('admin.components.modal-errors', ['bag' => 'orderForm'])
                @include('components.laundry-booking-form', ['operator' => true, 'action' => route('orders.store'), 'customers' => $bookingCustomers, 'modal' => true, 'bookingErrorBag' => 'orderForm', 'customerCreateUrl' => route('admin.customers.create')])
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" type="submit" form="laundry-booking">Create order</button>
            </div>
        </div>
    </div>
</div>
