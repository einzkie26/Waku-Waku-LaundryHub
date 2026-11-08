(() => {
    'use strict';

    const hero = document.querySelector('[data-laundry-hero]');
    const washer = hero?.querySelector('[data-washer]');
    const drum = washer?.querySelector('[data-washer-drum]');
    if (!hero || !washer || !drum) return;

    const controls = [...hero.querySelectorAll('[data-hero-machine]')];
    const cycle = washer.querySelector('[data-washer-cycle]');
    const readout = washer.querySelector('[data-washer-value]');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const inputs = new Map(controls.map(control => [control, { hover: false, focus: false, pressed: false, time: 0, pointer: null, release: null }]));
    let mode = 'idle';
    let modeStarted = 0;
    let angle = 0;
    let speed = 0;
    let frame = 0;
    let lastFrame = 0;
    let lastReadout = 0;
    let inView = true;
    let pendingControl = null;
    let navigationTimer;

    const canAnimate = () => !reducedMotion.matches && inView && !document.hidden;
    const stopFrame = () => { cancelAnimationFrame(frame); frame = 0; lastFrame = 0; };
    const animate = now => {
        frame = 0;
        if (!canAnimate()) { lastFrame = 0; return; }
        const delta = Math.min(40, lastFrame ? now - lastFrame : 16);
        lastFrame = now;
        const elapsed = now - modeStarted;
        const ramp = Math.min(1, elapsed / 1100);
        const eased = ramp * ramp * (3 - 2 * ramp);
        const target = mode === 'spin' ? 55 + 805 * eased
            : mode === 'wash' ? Math.cos(elapsed / 340) * 85 : 0;
        const response = mode === 'idle' ? 280 : 110;
        speed += (target - speed) * (1 - Math.exp(-delta / response));
        angle = (angle + speed * delta / 1000) % 360;
        drum.style.transform = 'rotate(' + angle.toFixed(3) + 'deg)';
        if (mode === 'spin' && now - lastReadout > 100) {
            readout.textContent = String(Math.round((120 + 1080 * eased) / 10) * 10);
            lastReadout = now;
        }
        if (mode !== 'idle' || Math.abs(speed) > .3) frame = requestAnimationFrame(animate);
        else { speed = 0; lastFrame = 0; }
    };
    const startFrame = () => { if (!frame && canAnimate()) frame = requestAnimationFrame(animate); };
    const setMode = next => {
        if (reducedMotion.matches) next = 'idle';
        if (next === mode) return;
        mode = next;
        modeStarted = performance.now();
        washer.dataset.machineMode = next;
        cycle.textContent = next === 'wash' ? 'Wash' : next === 'spin' ? 'Spin' : 'Ready';
        readout.textContent = next === 'wash' ? '30:00' : next === 'spin' ? '120' : '00:00';
        startFrame();
    };
    const update = () => {
        const active = pendingControl || controls
            .filter(control => { const input = inputs.get(control); return input.hover || input.focus || input.pressed; })
            .sort((a, b) => inputs.get(b).time - inputs.get(a).time)[0];
        setMode(active?.dataset.heroMachine || 'idle');
    };
    const clearPending = () => {
        clearTimeout(navigationTimer);
        pendingControl?.classList.remove('is-activating');
        pendingControl?.removeAttribute('aria-busy');
        pendingControl = null;
    };
    const resetInputs = () => {
        inputs.forEach(input => {
            clearTimeout(input.release);
            input.hover = false;
            input.focus = false;
            input.pressed = false;
            input.pointer = null;
        });
    };
    const navigate = () => {
        const destination = pendingControl?.href;
        clearPending();
        resetInputs();
        setMode('idle');
        if (destination) window.location.assign(destination);
    };

    controls.forEach(control => {
        const input = inputs.get(control);
        const activate = key => { input[key] = true; input.time = performance.now(); update(); };
        control.addEventListener('pointerenter', event => { if (event.pointerType !== 'touch') activate('hover'); });
        control.addEventListener('pointerleave', () => { input.hover = false; update(); });
        control.addEventListener('focus', () => { if (control.matches(':focus-visible')) activate('focus'); });
        control.addEventListener('blur', () => { input.focus = false; update(); });
        control.addEventListener('pointerdown', event => {
            if (event.button !== 0 || !event.isPrimary) return;
            clearTimeout(input.release);
            input.pointer = event.pointerId;
            activate('pressed');
        });
        control.addEventListener('click', event => {
            if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey
                || control.hasAttribute('download') || (control.target && control.target !== '_self') || reducedMotion.matches) return;
            clearPending();
            pendingControl = control;
            update();
            const rectangle = washer.getBoundingClientRect();
            const visible = rectangle.bottom > 0 && rectangle.top < window.innerHeight;
            const previewLength = control.dataset.heroMachine === 'wash' ? 600 : 1400;
            const remaining = visible ? Math.max(0, previewLength - (performance.now() - modeStarted)) : 0;
            if (remaining < 30) { clearPending(); return; }
            event.preventDefault();
            control.classList.add('is-activating');
            control.setAttribute('aria-busy', 'true');
            navigationTimer = setTimeout(navigate, remaining);
        });
    });
    const release = event => {
        inputs.forEach(input => {
            if (input.pointer !== event.pointerId) return;
            input.pointer = null;
            clearTimeout(input.release);
            const finish = () => { input.pressed = false; update(); };
            if (event.type === 'pointercancel') finish();
            else input.release = setTimeout(finish, 180);
        });
    };
    window.addEventListener('pointerup', release);
    window.addEventListener('pointercancel', release);
    const refreshPause = () => {
        washer.dataset.machinePaused = String(!canAnimate());
        if (canAnimate()) { if (mode !== 'idle' || Math.abs(speed) > .3) startFrame(); }
        else stopFrame();
    };
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(entries => { inView = entries[0].isIntersecting; refreshPause(); });
        observer.observe(washer);
    }
    document.addEventListener('visibilitychange', refreshPause);
    reducedMotion.addEventListener('change', () => {
        if (reducedMotion.matches) { speed = 0; setMode('idle'); if (pendingControl) navigate(); }
        else update();
        refreshPause();
    });
    document.addEventListener('keydown', event => {
        if (event.key !== 'Escape') return;
        clearPending();
        resetInputs();
        setMode('idle');
    });
    const reset = () => { clearPending(); resetInputs(); speed = 0; setMode('idle'); stopFrame(); };
    window.addEventListener('pagehide', reset);
    window.addEventListener('pageshow', () => { reset(); refreshPause(); });
    refreshPause();
})();
