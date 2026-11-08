(() => {
    'use strict';

    const menu = document.getElementById('admin-navigation');
    const toggle = document.getElementById('admin-menu-toggle');
    const selections = document.getElementById('admin-radial-selections');

    if (menu && toggle && selections) {
        const storageKey = 'waku-admin-navigation-dock';
        const docks = ['top-left', 'top', 'top-right', 'right', 'bottom-right', 'bottom', 'bottom-left', 'left'];
        const choices = [...selections.querySelectorAll('[data-nav-choice]')];
        const status = menu.querySelector('[data-dock-status]');
        let dock = 'bottom-left';
        let open = false;
        let drag = null;
        let lastDragAt = -Infinity;
        let lastTouchTapAt = -Infinity;
        let hideTimer;

        try {
            const saved = localStorage.getItem(storageKey);
            if (docks.includes(saved)) dock = saved;
        } catch { /* Navigation also works when browser storage is unavailable. */ }

        const viewport = () => {
            const visual = window.visualViewport;
            return {
                x: visual?.offsetLeft || 0,
                y: visual?.offsetTop || 0,
                width: visual?.width || window.innerWidth,
                height: visual?.height || window.innerHeight,
            };
        };
        const expandedSize = () => {
            const view = viewport();
            return Math.max(0, Math.min(352, view.width - 32, view.height - 32));
        };
        const menuSize = () => open ? expandedSize() : 72;
        const positions = size => {
            const view = viewport();
            const left = view.x + 16;
            const top = view.y + 16;
            const right = Math.max(left, view.x + view.width - size - 16);
            const bottom = Math.max(top, view.y + view.height - size - 16);
            const middleX = (left + right) / 2;
            const middleY = (top + bottom) / 2;
            return {
                'top-left': [left, top], top: [middleX, top], 'top-right': [right, top],
                right: [right, middleY], 'bottom-right': [right, bottom], bottom: [middleX, bottom],
                'bottom-left': [left, bottom], left: [left, middleY],
            };
        };
        const saveDock = announce => {
            menu.dataset.dock = dock;
            try { localStorage.setItem(storageKey, dock); } catch { /* Keep this session's dock. */ }
            if (announce && status) status.textContent = 'Navigation docked at the ' + dock.replace('-', ' ') + (dock.includes('-') ? ' corner.' : ' edge.');
        };
        const place = () => {
            const size = menuSize();
            const popupSize = expandedSize();
            const choiceSize = Math.min(68, Math.max(40, popupSize * .2));
            const radius = Math.max(0, (popupSize - choiceSize) / 2 - 12);
            const [x, y] = positions(size)[dock];
            menu.style.setProperty('--menu-size', size + 'px');
            menu.style.setProperty('--choice-size', choiceSize + 'px');
            menu.style.setProperty('--toggle-size', open && popupSize < 290 ? '56px' : '72px');
            menu.style.bottom = 'auto';
            menu.style.left = x + 'px';
            menu.style.top = y + 'px';
            menu.classList.toggle('is-small', popupSize < 300);
            choices.forEach((choice, index) => {
                const angle = (index / choices.length) * Math.PI * 2 - Math.PI / 2;
                choice.style.setProperty('--choice-index', String(index));
                choice.style.setProperty('--choice-x', Math.cos(angle) * radius + 'px');
                choice.style.setProperty('--choice-y', Math.sin(angle) * radius + 'px');
            });
            menu.dataset.dock = dock;
        };
        const setOpen = (next, restoreFocus = false) => {
            clearTimeout(hideTimer);
            open = next;
            if (next) {
                selections.hidden = false;
                selections.inert = false;
                choices.forEach(choice => choice.tabIndex = 0);
                void selections.offsetWidth;
            } else {
                selections.inert = true;
                choices.forEach(choice => choice.tabIndex = -1);
                hideTimer = setTimeout(() => { if (!open) selections.hidden = true; }, 260);
            }
            menu.classList.toggle('is-open', next);
            toggle.setAttribute('aria-expanded', String(next));
            toggle.setAttribute('aria-label', next ? 'Close admin navigation' : 'Open admin navigation');
            place();
            if (restoreFocus) toggle.focus({ preventScroll: true });
        };
        const finishDrag = event => {
            if (!drag || (event?.pointerId !== undefined && event.pointerId !== drag.id)) return;
            const pointer = drag;
            drag = null;
            if (pointer.moved) {
                const rect = menu.getBoundingClientRect();
                const candidates = positions(72);
                dock = docks.reduce((nearest, candidate) => {
                    const distance = point => Math.hypot(candidates[point][0] - rect.left, candidates[point][1] - rect.top);
                    return distance(candidate) < distance(nearest) ? candidate : nearest;
                }, docks[0]);
                lastDragAt = performance.now();
                menu.classList.remove('is-dragging');
                saveDock(true);
                place();
            } else if (event?.type === 'pointerup' && ['touch', 'pen'].includes(event.pointerType)) {
                // Touch drags can suppress the browser's later click, so handle taps at release.
                lastTouchTapAt = performance.now();
                setOpen(!open);
            }
            if (pointer.moved && event?.type !== 'pointerup' && toggle.hasPointerCapture(pointer.id)) {
                toggle.releasePointerCapture(pointer.id);
            }
        };

        toggle.addEventListener('click', event => {
            const pointerClick = event.detail !== 0 || ['touch', 'pen'].includes(event.pointerType);
            if (pointerClick && performance.now() - Math.max(lastDragAt, lastTouchTapAt) < 1000) {
                event.preventDefault();
                return;
            }
            setOpen(!open);
        });
        toggle.addEventListener('pointerdown', event => {
            if (event.button !== 0 || !event.isPrimary) return;
            lastDragAt = -Infinity;
            lastTouchTapAt = -Infinity;
            const rect = toggle.getBoundingClientRect();
            drag = {
                id: event.pointerId, startX: event.clientX, startY: event.clientY, moved: false,
                offsetX: event.clientX - (rect.left + rect.width / 2),
                offsetY: event.clientY - (rect.top + rect.height / 2),
            };
        });
        window.addEventListener('pointermove', event => {
            if (!drag || drag.id !== event.pointerId) return;
            if (!drag.moved && Math.hypot(event.clientX - drag.startX, event.clientY - drag.startY) < 8) return;
            if (!drag.moved) {
                drag.moved = true;
                toggle.setPointerCapture(event.pointerId);
                menu.classList.add('is-dragging');
                setOpen(false);
            }
            const view = viewport();
            const x = Math.max(view.x + 16, Math.min(view.x + view.width - 88, event.clientX - drag.offsetX - 36));
            const y = Math.max(view.y + 16, Math.min(view.y + view.height - 88, event.clientY - drag.offsetY - 36));
            menu.style.left = x + 'px';
            menu.style.top = y + 'px';
            if (event.cancelable) event.preventDefault();
        });
        window.addEventListener('pointerup', finishDrag);
        window.addEventListener('pointercancel', finishDrag);
        window.addEventListener('blur', finishDrag);
        toggle.addEventListener('lostpointercapture', finishDrag);
        toggle.addEventListener('keydown', event => {
            const direction = { ArrowLeft: -1, ArrowUp: -1, ArrowRight: 1, ArrowDown: 1 }[event.key];
            if (!direction) return;
            event.preventDefault();
            dock = docks[(docks.indexOf(dock) + direction + docks.length) % docks.length];
            saveDock(true);
            place();
        });
        selections.addEventListener('keydown', event => {
            const index = choices.indexOf(document.activeElement);
            if (index < 0) return;
            const direction = { ArrowLeft: -1, ArrowUp: -1, ArrowRight: 1, ArrowDown: 1 }[event.key];
            if (direction) {
                event.preventDefault();
                choices[(index + direction + choices.length) % choices.length].focus();
            } else if (event.key === 'Home' || event.key === 'End') {
                event.preventDefault();
                choices[event.key === 'Home' ? 0 : choices.length - 1].focus();
            }
        });
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && open) {
                event.preventDefault();
                setOpen(false, true);
            }
        });
        document.addEventListener('pointerdown', event => {
            if (open && !menu.contains(event.target)) setOpen(false);
        });
        document.addEventListener('focusin', event => {
            if (open && !menu.contains(event.target)) setOpen(false);
        });
        document.addEventListener('show.bs.modal', () => {
            finishDrag();
            if (open) setOpen(false);
        });
        const resize = () => { if (drag) finishDrag(); place(); };
        window.addEventListener('resize', resize);
        window.visualViewport?.addEventListener('resize', resize);
        window.visualViewport?.addEventListener('scroll', resize);
        window.addEventListener('pageshow', () => { setOpen(false); place(); });
        place();
    }

    document.querySelectorAll('[data-chart]').forEach(chart => {
        const caption = chart.querySelector('[data-chart-caption]');
        chart.querySelectorAll('[data-chart-bar]').forEach(bar => {
            const update = () => { caption.textContent = bar.dataset.caption; };
            bar.addEventListener('mouseenter', update);
            bar.addEventListener('focus', update);
            bar.addEventListener('click', update);
        });
    });

    const employeeModal = document.getElementById('employee-create-modal');
    if (employeeModal && window.bootstrap) {
        employeeModal.addEventListener('shown.bs.modal', () => {
            const error = employeeModal.querySelector('#employee-form-errors');
            (error || employeeModal.querySelector('[name="name"]'))?.focus();
        });
        employeeModal.addEventListener('hidden.bs.modal', () => {
            const url = new URL(window.location.href);
            if (url.searchParams.has('add')) {
                url.searchParams.delete('add');
                history.replaceState(history.state, '', url.pathname + url.search + url.hash);
            }
        });
        if (employeeModal.dataset.openOnLoad === 'true') {
            bootstrap.Modal.getOrCreateInstance(employeeModal).show();
        }
    }
})();
