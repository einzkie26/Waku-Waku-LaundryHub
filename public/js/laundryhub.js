(() => {
    'use strict';
    document.querySelector('[data-print-receipt]')?.addEventListener('click', () => window.print());
    const money = value => new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(value);
    const booking = document.getElementById('laundry-booking');
    const catalogElement = document.getElementById('laundry-catalog');
    if (booking && catalogElement) {
        const catalog = JSON.parse(catalogElement.textContent);
        const show = (selector, value) => { const target = booking.querySelector(selector); if (target) target.textContent = value; };
        const update = () => {
            const service = booking.querySelector('[name="service"]:checked')?.value;
            booking.querySelectorAll('[data-extra-option]').forEach(option => {
                const included = option.dataset.extraOption === service;
                const input = option.querySelector('input');
                option.hidden = included;
                input.disabled = included;
                if (included) input.checked = false;
            });
            const weight = Math.max(0, Number(booking.elements.weight.value) || 0);
            const base = (catalog.services[service]?.price || 0) * weight;
            const extras = [...booking.querySelectorAll('[name="additional_services[]"]:checked')].map(input => input.value);
            const additional = extras.reduce((sum, extra) => sum + (catalog.extras[extra] || 0), 0);
            show('[data-summary-service]', service || 'Choose a service');
            show('[data-summary-weight]', weight ? weight + ' kg × ' + money(catalog.services[service]?.price || 0) + ' / kg' : 'Add a weight to see your total');
            show('[data-summary-base]', money(base));
            show('[data-summary-extras-total]', money(additional));
            show('[data-summary-total]', money(base + additional));
            const extraList = booking.querySelector('[data-summary-extras]');
            extraList.replaceChildren(...extras.map(extra => { const li = document.createElement('li'); li.textContent = extra; return li; }));
            const date = booking.elements.date.value;
            const time = booking.elements.time;
            [...time.options].forEach(option => {
                option.disabled = Boolean(option.value && date === time.dataset.today && option.value <= time.dataset.currentTime);
            });
            if (time.selectedOptions[0]?.disabled) time.value = '';
            show('[data-summary-pickup]', date && time.value ? new Date(date + 'T12:00:00').toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' }) + ' · ' + time.selectedOptions[0].textContent : 'Choose a date & time');
            const customer = booking.elements.user_id?.selectedOptions[0];
            if (customer) {
                show('[data-customer-name]', customer.dataset.name || 'Choose a customer above');
                show('[data-customer-phone]', customer.dataset.phone || '');
                show('[data-customer-address]', customer.dataset.address || '');
            }
        };
        booking.addEventListener('input', update);
        booking.addEventListener('change', update);
        update();
    }

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
