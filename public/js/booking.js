(() => {
    'use strict';
    const form = document.getElementById('laundry-booking');
    const catalogNode = document.getElementById('laundry-catalog');
    if (!form || !catalogNode) return;
    const catalog = JSON.parse(catalogNode.textContent);
    const products = catalog.products;
    const money = cents => new Intl.NumberFormat('en-PH', {style: 'currency', currency: 'PHP'}).format(cents / 100);
    const number = value => new Intl.NumberFormat('en-PH', {maximumFractionDigits: 3}).format(value);
    const show = (selector, value) => { const element = form.querySelector(selector); if (element) element.textContent = value; };
    const selected = name => form.querySelector('[name="' + name + '"]:checked')?.value;
    const sachets = form.elements.detergent_sachets;
    const cups = form.elements.softener_cups;
    const measure = form.querySelector('[data-softener-controls]');
    const cup = form.querySelector('[data-cup-control]');
    let adjustingTimer;

    const enableQuantity = (input, enabled) => {
        if (input.disabled && enabled && !(Number(input.value) > 0)) input.value = '1';
        input.disabled = !enabled;
        input.required = enabled;
        form.querySelectorAll('[data-quantity-target="' + input.id + '"]').forEach(button => { button.disabled = !enabled; });
    };
    const update = () => {
        const service = selected('service');
        const liquids = products.services.includes(service);
        form.querySelector('[data-liquid-products]').hidden = !liquids;
        show('[data-products-title]', liquids ? 'Products & extras' : 'Optional extras');
        form.querySelectorAll('[name="detergent_brand"], [name="softener_brand"]').forEach(input => {
            input.disabled = !liquids;
            if (input.name === 'detergent_brand') input.required = liquids;
        });
        const detergent = liquids ? products.detergents[selected('detergent_brand')] : null;
        const softener = liquids ? products.softeners[selected('softener_brand')] : null;
        form.querySelector('[data-detergent-controls]').hidden = !detergent;
        measure.hidden = !softener;
        enableQuantity(sachets, Boolean(detergent));
        enableQuantity(cups, Boolean(softener));
        cup.setAttribute('aria-disabled', String(!softener));
        cup.tabIndex = softener ? 0 : -1;
        const sachetCount = detergent ? Math.max(0, Math.min(50, Math.round(Number(sachets.value) || 0))) : 0;
        const halfCups = softener ? Math.max(0, Math.min(products.maxCups * 2, Math.round((Number(cups.value) || 0) * 2))) : 0;
        const cupCount = halfCups / 2;
        const microlitres = halfCups * (products.cupMicrolitres / 2);
        const detergentCents = sachetCount * products.detergentSachetCents;
        const softenerCents = Math.floor((products.softenerLitreCents * microlitres + 500000) / 1000000);
        const weight = Math.min(1000, Math.max(0, Number(form.elements.weight.value) || 0));
        const serviceCents = Math.round((catalog.services[service]?.price || 0) * weight * 100);
        const extras = [...form.querySelectorAll('[name="additional_services[]"]:checked')].map(input => input.value);
        const extrasCents = extras.reduce((sum, name) => sum + (catalog.extras[name] || 0) * 100, 0);

        show('[data-sachet-count]', sachetCount);
        show('[data-detergent-price]', money(detergentCents));
        show('[data-softener-volume]', number(microlitres / 1000) + ' mL total');
        show('[data-softener-price]', money(softenerCents));
        show('[data-cup-multiplier]', '×' + number(cupCount));
        form.querySelector('.cup-liquid').style.setProperty('--cup-fill', Math.min(1, cupCount) * 90 + '%');
        measure.dataset.brand = selected('softener_brand') || '';
        cup.setAttribute('aria-valuenow', String(cupCount));
        cup.setAttribute('aria-valuetext', number(cupCount) + ' cups, ' + number(microlitres / 1000) + ' milliliters, ' + money(softenerCents));
        show('[data-summary-service]', service || 'Choose a service');
        show('[data-summary-weight]', weight ? number(weight) + ' kg × ' + money((catalog.services[service]?.price || 0) * 100) + ' / kg' : 'Enter your laundry weight');
        show('[data-summary-base]', money(serviceCents));
        show('[data-summary-detergent]', money(detergentCents));
        show('[data-summary-detergent-detail]', detergent ? detergent.name + ' · ' + sachetCount + (sachetCount === 1 ? ' sachet' : ' sachets') : selected('detergent_brand') === 'own' ? 'Bring my own' : 'Choose a detergent');
        show('[data-summary-softener]', money(softenerCents));
        show('[data-summary-softener-detail]', softener ? softener.name + ' · ' + number(cupCount) + ' cups / ' + number(microlitres / 1000) + ' mL' : 'No softener');
        form.querySelectorAll('[data-product-summary-row]').forEach(row => { row.hidden = !liquids; });
        show('[data-summary-extras-total]', money(extrasCents));
        const list = form.querySelector('[data-summary-extras]');
        list.replaceChildren(...extras.map(name => {
            const item = document.createElement('li'); const label = document.createElement('span'); const value = document.createElement('span');
            label.textContent = name; value.textContent = money(catalog.extras[name] * 100); item.append(label, value); return item;
        }));
        show('[data-summary-total]', money(serviceCents + detergentCents + softenerCents + extrasCents));
        const date = form.elements.date.value;
        const time = form.elements.time;
        [...time.options].forEach(option => { option.disabled = Boolean(option.value && date === time.dataset.today && option.value <= time.dataset.currentTime); });
        if (time.selectedOptions[0]?.disabled) time.value = '';
        show('[data-summary-pickup]', date && time.value ? new Date(date + 'T12:00:00').toLocaleDateString('en-PH', {month: 'short', day: 'numeric'}) + ' · ' + time.selectedOptions[0].textContent : 'Choose date & time');
        const customer = form.elements.user_id?.selectedOptions[0];
        if (customer) {
            show('[data-customer-name]', customer.dataset.name || 'Choose a customer');
            show('[data-customer-phone]', customer.dataset.phone || '');
            show('[data-customer-address]', customer.dataset.address || '');
        }
    };
    const normalise = input => {
        const step = Number(input.step) || 1;
        const minimum = Number(input.min) || 0;
        const maximum = Number(input.max) || 1000;
        input.value = String(Math.max(minimum, Math.min(maximum, Math.round((Number(input.value) || minimum) / step) * step)));
    };
    const markAdjusting = () => {
        clearTimeout(adjustingTimer);
        measure.classList.add('is-adjusting');
        adjustingTimer = setTimeout(() => measure.classList.remove('is-adjusting'), 450);
    };
    const adjust = (input, delta) => {
        if (input.disabled) return;
        input.value = String((Number(input.value) || 0) + delta);
        normalise(input);
        if (input === cups) markAdjusting();
        input.dispatchEvent(new Event('input', {bubbles: true}));
    };
    const stops = [];
    form.querySelectorAll('[data-quantity-target]').forEach(button => {
        const input = document.getElementById(button.dataset.quantityTarget);
        const step = Number(button.dataset.quantityStep);
        let delay, repeat, repeated = false, started, handledTouchAt = -Infinity;
        const stop = () => { clearTimeout(delay); clearInterval(repeat); };
        stops.push(stop);
        button.addEventListener('pointerdown', event => {
            if (event.button !== 0 || button.disabled) return;
            stop(); repeated = false; started = performance.now(); handledTouchAt = -Infinity;
            if (['touch', 'pen'].includes(event.pointerType)) {
                event.preventDefault();
                button.focus({preventScroll: true});
            }
            button.setPointerCapture(event.pointerId);
            delay = setTimeout(() => {
                repeated = true;
                adjust(input, step);
                repeat = setInterval(() => adjust(input, step * (performance.now() - started > 1800 ? 5 : 1)), 100);
            }, 380);
        });
        button.addEventListener('click', event => {
            const pointerClick = event.detail !== 0 || ['touch', 'pen'].includes(event.pointerType);
            if (pointerClick && performance.now() - handledTouchAt < 1000) { event.preventDefault(); return; }
            if (event.detail === 0 || !repeated) adjust(input, step);
        });
        button.addEventListener('pointerup', event => {
            stop();
            if (['touch', 'pen'].includes(event.pointerType)) {
                handledTouchAt = performance.now();
                if (!repeated) adjust(input, step);
            }
        });
        button.addEventListener('pointercancel', stop);
        button.addEventListener('lostpointercapture', stop);
    });
    let drag;
    cup.addEventListener('pointerdown', event => {
        if (event.button !== 0 || cups.disabled || !event.isPrimary) return;
        event.preventDefault();
        cup.focus({preventScroll: true});
        drag = {pointer: event.pointerId, y: event.clientY, value: Number(cups.value) || 1};
        cup.setPointerCapture(event.pointerId);
    });
    cup.addEventListener('pointermove', event => {
        if (!drag || drag.pointer !== event.pointerId) return;
        event.preventDefault();
        cups.value = String(drag.value + Math.round((drag.y - event.clientY) / 12) * .5);
        normalise(cups); markAdjusting(); update();
    });
    const endDrag = () => { drag = null; };
    cup.addEventListener('pointerup', endDrag); cup.addEventListener('pointercancel', endDrag); cup.addEventListener('lostpointercapture', endDrag);
    cup.addEventListener('keydown', event => {
        if (cups.disabled) return;
        if (['ArrowUp', 'ArrowRight', 'ArrowDown', 'ArrowLeft', 'PageUp', 'PageDown', 'Home', 'End'].includes(event.key)) {
            event.preventDefault();
            if (event.key === 'Home') { cups.value = cups.min; update(); }
            else if (event.key === 'End') { cups.value = cups.max; update(); }
            else adjust(cups, {ArrowUp: .5, ArrowRight: .5, ArrowDown: -.5, ArrowLeft: -.5, PageUp: 5, PageDown: -5}[event.key]);
        }
    });
    [sachets, cups].forEach(input => input.addEventListener('change', () => { normalise(input); if (input === cups) markAdjusting(); update(); }));
    form.addEventListener('input', update); form.addEventListener('change', update);
    window.addEventListener('blur', () => { stops.forEach(stop => stop()); endDrag(); });
    window.addEventListener('pagehide', () => { stops.forEach(stop => stop()); clearTimeout(adjustingTimer); endDrag(); });
    update();
})();
