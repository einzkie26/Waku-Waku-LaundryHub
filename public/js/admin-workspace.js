(() => {
    'use strict';

    document.querySelectorAll('[data-chart]').forEach(chart => {
        const caption = chart.querySelector('[data-chart-caption]');
        chart.querySelectorAll('[data-chart-bar]').forEach(bar => {
            const update = () => { caption.textContent = bar.dataset.caption; };
            bar.addEventListener('mouseenter', update);
            bar.addEventListener('focus', update);
            bar.addEventListener('click', update);
        });
    });

    if (window.bootstrap) {
        const modals = [...document.querySelectorAll('[data-admin-modal]')];
        modals.forEach(modal => {
            let returnFocus;
            modal.addEventListener('show.bs.modal', event => {
                const trigger = event.relatedTarget instanceof HTMLElement
                    ? event.relatedTarget
                    : document.querySelector('[data-bs-target="#' + modal.id + '"]');
                returnFocus = window.WakuAdminNavigation?.focusTarget(trigger)
                    || (trigger?.closest('.dropdown-menu')
                        ? trigger.closest('.dropdown')?.querySelector('[data-bs-toggle="dropdown"]')
                        : trigger);
            });
            modal.addEventListener('shown.bs.modal', () => {
                const target = modal.querySelector('[data-modal-errors]')
                    || modal.querySelector('[data-modal-initial-focus], input:not([type="hidden"]), select, textarea');
                target?.focus({ preventScroll: true });
            });
            modal.addEventListener('hidden.bs.modal', () => {
                const url = new URL(window.location.href);
                const parameter = modal.dataset.clearQuery;
                if (parameter && url.searchParams.has(parameter)) {
                    url.searchParams.delete(parameter);
                    history.replaceState(history.state, '', url.pathname + url.search + url.hash);
                }
                returnFocus?.focus({ preventScroll: true });
            });
        });
        const initialModal = modals.find(modal => modal.querySelector('[data-modal-errors]'))
            || modals.find(modal => modal.dataset.openOnLoad === 'true');
        if (initialModal) bootstrap.Modal.getOrCreateInstance(initialModal).show();
    }
})();
