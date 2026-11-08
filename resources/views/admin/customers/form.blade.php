<div class="admin-split-form">
    <section class="admin-panel"><div class="admin-panel-header"><h2>{{ isset($customer) ? 'Contact details' : 'Contact & sign-in details' }}</h2></div><div class="admin-panel-body">
        <form method="POST" action="{{ isset($customer) ? route('admin.customers.update', $customer->id) : route('admin.customers.store') }}" data-single-submit>@csrf @if(isset($customer)) @method('PUT') @endif
            @include('admin.customers.form-fields', ['customerRecord' => $customer ?? null])
            <div class="d-flex flex-wrap gap-2 mt-4"><button class="btn btn-primary" type="submit">{{ isset($customer) ? 'Save customer details' : 'Add customer' }}</button><a class="btn btn-light" href="{{ isset($customer) ? route('admin.customers.show', $customer->id) : route('admin.customers.index') }}">Cancel</a></div>
        </form>
    </div></section>
    <aside class="admin-panel admin-actions-panel"><div class="admin-panel-body"><div class="admin-section-kicker">THOUGHTFUL CARE STARTS HERE</div><h2 class="h5 mb-3">Make the next pickup easier.</h2><p>Check the phone number and full address with the customer so our team knows where to go.</p><p class="mb-0">Their username lets them sign in, book a pickup, and follow their laundry.</p></div></aside>
</div>
