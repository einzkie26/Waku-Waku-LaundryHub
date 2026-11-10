(() => {
    'use strict';
    document.querySelector('[data-print-receipt]')?.addEventListener('click', () => window.print());
    const updatePayment = form => {
        const status = form.querySelector('[name="payment_status"]');
        const method = form.querySelector('[name="payment_method"]');
        const reference = form.querySelector('[name="payment_reference"]');
        const paid = !status || status.value === 'paid';
        const fields = form.querySelector('[data-payment-fields]');
        if (fields) fields.hidden = !paid;
        if (method) { method.disabled = !paid; method.required = paid; }
        if (reference) { reference.disabled = !paid; reference.required = paid && ['GCash', 'Bank'].includes(method?.value); }
    };
    document.querySelectorAll('#laundry-booking, [data-payment-record]').forEach(form => {
        form.addEventListener('change', () => updatePayment(form));
        updatePayment(form);
    });

    document.querySelectorAll('form[data-single-submit]').forEach(form => {
        form.addEventListener('submit', event => {
            if (form.dataset.submitting) { event.preventDefault(); return; }
            form.dataset.submitting = 'true';
            const button = event.submitter || form.querySelector('[type="submit"]');
            if (button) { button.dataset.originalLabel = button.innerHTML; button.disabled = true; button.textContent = 'Saving…'; }
        });
    });
    window.addEventListener('pageshow', () => {
        document.querySelectorAll('form[data-single-submit]').forEach(form => {
            delete form.dataset.submitting;
            form.querySelectorAll('[data-original-label]').forEach(button => { button.innerHTML = button.dataset.originalLabel; button.disabled = false; delete button.dataset.originalLabel; });
        });
    });
    document.querySelectorAll('input[type="password"]').forEach(input => {
        const button = document.querySelector('[data-toggle-password="' + input.id + '"]');
        button?.addEventListener('click', () => { const visible = input.type === 'password'; input.type = visible ? 'text' : 'password'; button.textContent = visible ? 'Hide' : 'Show'; button.setAttribute('aria-pressed', String(visible)); });
    });
    document.getElementById('validation-errors')?.focus();
})();
