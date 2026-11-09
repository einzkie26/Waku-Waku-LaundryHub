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

    const sidebar = document.querySelector('.staff-sidebar');
    const toggle = document.getElementById('sidebarToggle');
    const backdrop = document.querySelector('.sidebar-backdrop');
    const setSidebar = open => {
        if (!sidebar || !toggle) return;
        sidebar.classList.toggle('show', open);
        backdrop?.classList.toggle('show', open);
        toggle.setAttribute('aria-expanded', String(open));
        document.body.style.overflow = open ? 'hidden' : '';
        if (open) sidebar.querySelector('[data-close-sidebar]')?.focus();
        else toggle.focus();
    };
    toggle?.addEventListener('click', () => setSidebar(!sidebar.classList.contains('show')));
    backdrop?.addEventListener('click', () => setSidebar(false));
    document.querySelector('[data-close-sidebar]')?.addEventListener('click', () => setSidebar(false));
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && sidebar?.classList.contains('show')) setSidebar(false); });
    window.addEventListener('resize', () => { if (window.innerWidth >= 992 && sidebar?.classList.contains('show')) setSidebar(false); });

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
