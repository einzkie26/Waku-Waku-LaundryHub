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
        const root = document.documentElement;
        const sidebar = document.getElementById('admin-sidebar');
        const sidebarToggle = document.getElementById('admin-sidebar-toggle');
        const backdrop = document.querySelector('.admin-sidebar-backdrop');
        const main = document.getElementById('admin-main');
        const topbar = document.querySelector('.admin-topbar');
        const modeControls = menu.querySelector('[data-radial-mode-controls]');
        const quickSwitch = menu.querySelector('.admin-radial-quick-switch');
        const modeButtons = [...document.querySelectorAll('[data-navigation-mode]')];
        const announcement = document.getElementById('admin-navigation-status');
        const fx = document.getElementById('admin-navigation-fx');
        const fluid = fx.querySelector('[data-nav-fluid]');
        const shine = fx.querySelector('[data-nav-fluid-shine]');
        const bubble = fx.querySelector('[data-nav-bubble]');
        const ring = fx.querySelector('[data-nav-pop-ring]');
        const swirl = fx.querySelector('[data-nav-swirl]');
        const echo = fx.querySelector('[data-nav-swirl-echo]');
        const droplets = [...fx.querySelectorAll('[data-nav-droplet]')];
        const modeStorageKey = 'waku-admin-navigation-mode';
        const narrow = matchMedia('(max-width: 991px)');
        const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
        let mode = root.dataset.adminNavMode === 'sidebar' ? 'sidebar' : 'radial';
        let drawerOpen = false;
        let previousOverflow;
        let transition = null;
        let frame;
        let arrivalTimer;
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
            root.dataset.adminNavDock = dock;
            try { localStorage.setItem(storageKey, dock); } catch { /* Keep this session's dock. */ }
            if (announce && status) status.textContent = 'Navigation docked at the ' + dock.replace('-', ' ') + (dock.includes('-') ? ' corner.' : ' edge.');
        };
        const place = () => {
            if (mode !== 'radial') return;
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
            root.dataset.adminNavDock = dock;
        };
        const setOpen = (next, restoreFocus = false) => {
            if (next && (mode !== 'radial' || transition)) return;
            clearTimeout(hideTimer);
            open = next;
            modeControls.hidden = !next;
            modeControls.inert = !next;
            quickSwitch.tabIndex = next ? -1 : 0;
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

        // Both layouts share one preference and expose only the active navigation to assistive tools.
        const setDrawer = (next, restoreFocus = false) => {
            const wasOpen = drawerOpen;
            drawerOpen = Boolean(next && mode === 'sidebar' && narrow.matches);
            if (drawerOpen && !wasOpen) {
                previousOverflow = document.body.style.overflow;
                document.body.style.overflow = 'hidden';
            } else if (wasOpen && !drawerOpen) {
                document.body.style.overflow = previousOverflow;
            }
            main.inert = drawerOpen;
            topbar.inert = drawerOpen;
            if (wasOpen && !drawerOpen && sidebar.contains(document.activeElement)) {
                (restoreFocus ? sidebarToggle : main).focus({ preventScroll: true });
            }
            sidebar.hidden = mode !== 'sidebar' || (narrow.matches && !drawerOpen);
            sidebar.inert = sidebar.hidden;
            sidebar.setAttribute('aria-hidden', String(sidebar.hidden));
            backdrop.hidden = !drawerOpen;
            sidebarToggle.setAttribute('aria-expanded', String(drawerOpen));
        };
        const syncPresentation = (showDrawer = false) => {
            root.dataset.adminNavMode = mode;
            menu.hidden = mode !== 'radial';
            menu.inert = menu.hidden;
            menu.setAttribute('aria-hidden', String(menu.hidden));
            modeButtons.forEach(button => button.setAttribute('aria-pressed', String(button.dataset.navigationMode === mode)));
            setDrawer(showDrawer);
            place();
        };
        const focusNavigation = () => {
            const target = mode === 'radial' ? toggle : narrow.matches && !drawerOpen ? sidebarToggle
                : sidebar.querySelector('[data-navigation-mode="sidebar"]');
            target?.focus({ preventScroll: true });
        };
        const focusTarget = trigger => {
            if (!trigger?.closest('#admin-navigation, #admin-sidebar')) return null;
            if (mode === 'radial') return toggle;
            if (narrow.matches) return sidebarToggle;
            return trigger.closest('#admin-sidebar') ? trigger
                : sidebar.querySelector('[data-bs-target="#admin-settings-modal"]');
        };
        window.WakuAdminNavigation = { focusTarget };
        sidebar.querySelectorAll('[data-sidebar-link]').forEach((link, index) => link.style.setProperty('--sidebar-index', index));

        const clamp = value => Math.max(0, Math.min(1, value));
        const mix = (start, end, progress) => start + (end - start) * progress;
        const ease = value => 1 - Math.pow(1 - clamp(value), 3);
        const smooth = value => { const p = clamp(value); return p * p * (3 - 2 * p); };
        const center = rect => ({ x: rect.left + rect.width / 2, y: rect.top + rect.height / 2 });
        const circleBox = (point, radius) => ({ x: point.x - radius, y: point.y - radius, width: radius * 2, height: radius * 2, radii: [radius, radius, radius, radius] });
        const sidebarBox = () => ({ x: 0, y: 0, width: sidebar.getBoundingClientRect().width || 264, height: window.innerHeight, radii: [0, 22, 22, 0] });
        const morphBox = (from, to, progress) => ({
            x: mix(from.x, to.x, progress), y: mix(from.y, to.y, progress),
            width: mix(from.width, to.width, progress), height: mix(from.height, to.height, progress),
            radii: from.radii.map((radius, index) => mix(radius, to.radii[index], progress)),
        });
        // Four rounded corners interpolate into a bead; a travelling wave shapes the liquid edge.
        const drawFluid = (box, wave = 0) => {
            const { x, y, width: w, height: h } = box;
            const [a, b, c, d] = box.radii;
            const right = x + w;
            const bottom = y + h;
            const k = .55228475;
            fluid.setAttribute('d', `M ${x + a} ${y} H ${right - b}
                C ${right - b + b * k} ${y} ${right} ${y + b - b * k} ${right} ${y + b}
                C ${right + wave} ${y + h * .32} ${right - wave * .6} ${y + h * .66} ${right} ${bottom - c}
                C ${right} ${bottom - c + c * k} ${right - c + c * k} ${bottom} ${right - c} ${bottom}
                H ${x + d} C ${x + d - d * k} ${bottom} ${x} ${bottom - d + d * k} ${x} ${bottom - d}
                V ${y + a} C ${x} ${y + a - a * k} ${x + a - a * k} ${y} ${x + a} ${y} Z`);
            shine.setAttribute('d', `M ${right - b} ${y + 1} Q ${right} ${y} ${right - 1} ${y + b}
                C ${right + wave - 1} ${y + h * .32} ${right - wave * .6 - 1} ${y + h * .66} ${right - 1} ${bottom - c}
                Q ${right} ${bottom} ${right - c} ${bottom - 1}`);
        };
        const drawBubble = (point, scale, angle, opacity) => {
            bubble.style.transform = `translate3d(${point.x - 38}px, ${point.y - 38}px, 0) scale(${scale}) rotate(${angle}deg)`;
            bubble.style.opacity = opacity;
        };
        const bezier = (from, controlA, controlB, to, progress) => {
            const q = 1 - progress;
            return {
                x: q ** 3 * from.x + 3 * q ** 2 * progress * controlA.x + 3 * q * progress ** 2 * controlB.x + progress ** 3 * to.x,
                y: q ** 3 * from.y + 3 * q ** 2 * progress * controlA.y + 3 * q * progress ** 2 * controlB.y + progress ** 3 * to.y,
            };
        };
        const drawSwirl = (point, progress) => {
            const path = (rotation, size) => {
                const points = [];
                for (let index = 0; index <= 64; index++) {
                    const t = index / 64;
                    const angle = rotation + t * Math.PI * 3.7;
                    const radius = mix(55, 7, t) * size;
                    points.push(`${index ? 'L' : 'M'} ${point.x + Math.cos(angle) * radius} ${point.y + Math.sin(angle) * radius}`);
                }
                return points.join(' ');
            };
            const visibility = Math.sin(clamp(progress) * Math.PI);
            swirl.setAttribute('d', path(progress * Math.PI * 3, mix(1, .63, progress)));
            echo.setAttribute('d', path(progress * Math.PI * 3 + Math.PI * .75, .85));
            swirl.style.opacity = visibility;
            echo.style.opacity = visibility * .7;
            ring.setAttribute('cx', point.x);
            ring.setAttribute('cy', point.y);
            ring.setAttribute('r', mix(29, 64, ease(progress)));
            ring.style.opacity = (1 - progress) * .7;
            droplets.forEach((drop, index) => {
                const angle = index / droplets.length * Math.PI * 2 + progress * .5;
                const radius = mix(22, 68, ease(progress));
                drop.setAttribute('cx', point.x + Math.cos(angle) * radius);
                drop.setAttribute('cy', point.y + Math.sin(angle) * radius);
                drop.setAttribute('r', (index % 3 === 0 ? 3 : 1.7) * (1 - progress));
                drop.style.opacity = visibility;
            });
        };
        const finishTransition = (restoreFocus = true) => {
            if (!transition) return;
            const showDrawer = transition.showDrawer;
            cancelAnimationFrame(frame);
            transition = null;
            fx.hidden = true;
            delete fx.dataset.phase;
            delete root.dataset.navTransition;
            document.body.classList.remove('is-navigation-switching');
            [menu, sidebar].forEach(element => {
                ['opacity', 'scale', 'rotate'].forEach(property => element.style.removeProperty(property));
            });
            modeButtons.forEach(button => button.disabled = false);
            syncPresentation(showDrawer);
            arrivalTimer = setTimeout(() => sidebar.classList.remove('is-arriving'), 500);
            announcement.textContent = mode === 'sidebar' ? 'Sidebar navigation selected.' : 'Menu plus navigation selected. Docked at the top left.';
            if (restoreFocus && !document.hidden) focusNavigation();
        };
        const switchMode = (next, { animate = true, trigger = null } = {}) => {
            if (!['radial', 'sidebar'].includes(next) || next === mode || transition) return;
            const from = center((mode === 'radial' ? toggle : trigger || sidebar).getBoundingClientRect());
            const oldSidebar = mode === 'sidebar' ? sidebarBox() : null;
            finishDrag();
            setOpen(false);
            clearTimeout(arrivalTimer);
            sidebar.classList.remove('is-arriving');
            if (menu.contains(document.activeElement) || sidebar.contains(document.activeElement)) main.focus({ preventScroll: true });
            setDrawer(false);
            mode = next;
            try { localStorage.setItem(modeStorageKey, mode); } catch { /* Keep the preference for this page. */ }
            if (mode === 'radial') { dock = 'top-left'; saveDock(false); }
            const showDrawer = next === 'sidebar' && narrow.matches;
            syncPresentation(showDrawer);
            if (!animate || reducedMotion.matches || document.hidden) { focusNavigation(); return; }

            transition = { showDrawer, start: performance.now(), duration: next === 'sidebar' ? 1000 : 1480 };
            modeButtons.forEach(button => button.disabled = true);
            menu.inert = true;
            sidebar.inert = true;
            root.dataset.navTransition = next;
            document.body.classList.add('is-navigation-switching');
            fx.hidden = false;
            [fluid, shine, ring, swirl, echo, ...droplets].forEach(element => element.style.opacity = 0);
            bubble.style.opacity = 0;
            const to = next === 'sidebar' ? sidebarBox() : center(toggle.getBoundingClientRect());
            if (next === 'sidebar') sidebar.style.opacity = 0;
            else menu.style.opacity = 0;
            const startBox = next === 'sidebar' ? circleBox(from, 36) : oldSidebar;
            let revealed = false;
            const draw = now => {
                if (!transition) return;
                const p = clamp((now - transition.start) / transition.duration);
                if (next === 'sidebar') {
                    fx.dataset.phase = 'wrap';
                    const stretch = ease(p / .84);
                    drawFluid(morphBox(startBox, to, stretch), Math.sin(p * Math.PI) * 29 * Math.sin(p * Math.PI * 1.6));
                    fluid.style.opacity = 1 - smooth((p - .76) / .24);
                    shine.style.opacity = Math.sin(p * Math.PI) * .5;
                    drawBubble({ x: mix(from.x, 49, stretch), y: mix(from.y, 51, stretch) }, mix(1, .62, stretch), -15 * Math.sin(p * Math.PI), 1 - smooth((p - .34) / .4));
                    if (p > .72) {
                        sidebar.style.opacity = smooth((p - .72) / .2);
                        if (!revealed) { sidebar.classList.add('is-arriving'); revealed = true; }
                    }
                } else {
                    const gather = ease(p / .28);
                    fx.dataset.phase = p < .28 ? 'gather' : p < .76 ? 'fly' : 'pop';
                    drawFluid(morphBox(startBox, circleBox(from, 36), gather), Math.sin(gather * Math.PI) * 32);
                    fluid.style.opacity = 1 - smooth((p - .17) / .14);
                    shine.style.opacity = (1 - gather) * .45;
                    const flight = smooth((p - .22) / .54);
                    const edge = Math.min(window.innerWidth - 48, Math.max(from.x + 150, 220));
                    const point = bezier(from, { x: edge, y: from.y - 70 }, { x: edge * .6, y: Math.max(8, to.y - 70) }, to, flight);
                    const pop = clamp((p - .76) / .24);
                    drawBubble(point, (1 + Math.sin(flight * Math.PI) * .08) * (1 - smooth(pop) * .75), Math.sin(flight * Math.PI * 2) * 12, smooth(p / .17) * (1 - smooth(pop / .55)));
                    if (p >= .76) {
                        drawSwirl(to, pop);
                        menu.style.opacity = smooth(pop / .62);
                        menu.style.scale = mix(.4, 1, ease(pop)) + Math.sin(pop * Math.PI) * .15;
                        menu.style.rotate = mix(-100, 0, ease(pop)) + 'deg';
                    }
                }
                if (p < 1) frame = requestAnimationFrame(draw);
                else finishTransition();
            };
            draw(transition.start);
        };
        modeButtons.forEach(button => button.addEventListener('click', () => switchMode(button.dataset.navigationMode, { trigger: button })));
        sidebarToggle.addEventListener('click', () => {
            if (transition) return;
            setDrawer(true);
            (sidebar.querySelector('[aria-current="page"]') || sidebar.querySelector('[data-sidebar-link]')).focus({ preventScroll: true });
        });
        document.querySelectorAll('[data-close-admin-sidebar]').forEach(button => button.addEventListener('click', () => {
            if (!transition) setDrawer(false, true);
        }));
        sidebar.addEventListener('keydown', event => {
            if (event.key !== 'Tab' || !drawerOpen) return;
            const focusable = [...sidebar.querySelectorAll('a[href], button:not(:disabled)')].filter(element => element.getClientRects().length);
            const first = focusable[0];
            const last = focusable.at(-1);
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        });
        narrow.addEventListener('change', () => {
            finishTransition(false);
            if (sidebar.contains(document.activeElement)) main.focus({ preventScroll: true });
            syncPresentation(false);
        });
        reducedMotion.addEventListener('change', () => finishTransition());
        document.addEventListener('visibilitychange', () => { if (document.hidden) finishTransition(false); });
        window.addEventListener('storage', event => {
            if (event.key !== modeStorageKey) return;
            finishTransition(false);
            switchMode(event.newValue === 'sidebar' ? 'sidebar' : 'radial', { animate: false });
        });

        document.addEventListener('click', event => {
            const pointerClick = event.detail !== 0 || ['touch', 'pen'].includes(event.pointerType);
            if (pointerClick && performance.now() - Math.max(lastDragAt, lastTouchTapAt) < 1000) {
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        }, true);
        toggle.addEventListener('click', () => setOpen(!open));
        toggle.addEventListener('pointerdown', event => {
            if (event.button !== 0 || !event.isPrimary || transition || mode !== 'radial') return;
            if (['touch', 'pen'].includes(event.pointerType)) {
                // The logo moves when expanded; keep native touch focus on the control.
                event.preventDefault();
                toggle.focus({ preventScroll: true });
            }
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
            } else if (event.key === 'Escape' && drawerOpen && !transition) {
                event.preventDefault();
                setDrawer(false, true);
            }
        });
        document.addEventListener('pointerdown', event => {
            lastDragAt = -Infinity;
            lastTouchTapAt = -Infinity;
            if (open && !menu.contains(event.target)) setOpen(false);
        });
        document.addEventListener('focusin', event => {
            if (open && !menu.contains(event.target)) setOpen(false);
        });
        document.addEventListener('show.bs.modal', () => {
            finishTransition(false);
            finishDrag();
            if (open) setOpen(false);
            setDrawer(false);
        });
        const resize = () => { finishTransition(false); if (drag) finishDrag(); place(); };
        window.addEventListener('resize', resize);
        window.visualViewport?.addEventListener('resize', resize);
        window.visualViewport?.addEventListener('scroll', resize);
        window.addEventListener('pageshow', () => { finishTransition(false); setOpen(false); syncPresentation(false); });
        syncPresentation(false);
    }

})();
