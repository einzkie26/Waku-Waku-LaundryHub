(() => {
    'use strict';
    const root = document.documentElement;
    const sidebar = document.getElementById('staff-sidebar');
    const menu = document.getElementById('staff-menu');
    const toggle = document.getElementById('staff-menu-toggle');
    const selections = document.getElementById('staff-menu-choices');
    if (!sidebar || !menu || !toggle || !selections) return;

    const main = document.getElementById('main-content');
    const topbar = document.querySelector('.staff-topbar');
    const opener = document.getElementById('staff-sidebar-toggle');
    const backdrop = document.querySelector('.staff-nav-backdrop');
    const modeSwitch = menu.querySelector('.staff-menu-mode-switch');
    const quickSwitch = menu.querySelector('.staff-menu-quick-switch');
    const choices = [...selections.querySelectorAll('[data-staff-choice]')];
    const modeButtons = [...document.querySelectorAll('[data-staff-mode]')];
    const status = document.getElementById('staff-navigation-status');
    const fx = document.getElementById('staff-nav-fold');
    const strips = [...fx.querySelectorAll('span')];
    const logo = fx.querySelector('img');
    const narrow = matchMedia('(max-width:991px)');
    const reduced = matchMedia('(prefers-reduced-motion:reduce)');
    const docks = ['top-left', 'top', 'top-right', 'right', 'bottom-right', 'bottom', 'bottom-left', 'left'];
    let mode = root.dataset.staffNavMode === 'radial' ? 'radial' : 'sidebar';
    let dock = 'bottom-left';
    let open = false;
    let drawer = false;
    let savedOverflow = '';
    let drag = null;
    let suppressClickUntil = 0;
    let hideTimer;
    let transition = null;
    let arrivalTimer;
    const persist = (key, value) => { try { localStorage.setItem(key, value); } catch {} };
    try { const saved = localStorage.getItem('waku-staff-navigation-dock'); if (docks.includes(saved)) dock = saved; } catch {}
    sidebar.querySelectorAll('.nav-link').forEach((link, index) => link.style.setProperty('--staff-link-index', index));

    const view = () => ({ x: window.visualViewport?.offsetLeft || 0, y: window.visualViewport?.offsetTop || 0,
        width: window.visualViewport?.width || innerWidth, height: window.visualViewport?.height || innerHeight });
    const expandedSize = () => Math.max(0, Math.min(340, view().width - 32, view().height - 32));
    const positions = size => {
        const v = view(), left = v.x + 16, top = v.y + 16;
        const right = Math.max(left, v.x + v.width - size - 16), bottom = Math.max(top, v.y + v.height - size - 16);
        const cx = (left + right) / 2, cy = (top + bottom) / 2;
        return { 'top-left':[left,top], top:[cx,top], 'top-right':[right,top], right:[right,cy],
            'bottom-right':[right,bottom], bottom:[cx,bottom], 'bottom-left':[left,bottom], left:[left,cy] };
    };
    const place = () => {
        if (mode !== 'radial') return;
        const size = open ? expandedSize() : 72;
        const choiceSize = Math.min(64, expandedSize() * .2);
        const radius = Math.max(0, (expandedSize() - choiceSize) / 2 - 12);
        const [x,y] = positions(size)[dock];
        if (!drag?.moved) Object.assign(menu.style, { left:x+'px', top:y+'px', bottom:'auto' });
        menu.style.setProperty('--menu-size', size+'px');
        menu.style.setProperty('--choice-size', choiceSize+'px');
        menu.style.setProperty('--toggle-size', open && size < 295 ? '56px' : '72px');
        choices.forEach((choice,index) => {
            const angle = index / choices.length * Math.PI * 2 - Math.PI / 2;
            choice.style.setProperty('--choice-index', index);
            choice.style.setProperty('--choice-x', Math.cos(angle) * radius+'px');
            choice.style.setProperty('--choice-y', Math.sin(angle) * radius+'px');
        });
        root.dataset.staffNavDock = dock;
    };
    const setOpen = (next, focus = false) => {
        if (next && (mode !== 'radial' || transition)) return;
        clearTimeout(hideTimer);
        open = next;
        modeSwitch.hidden = !next;
        modeSwitch.inert = !next;
        quickSwitch.tabIndex = next ? -1 : 0;
        selections.inert = !next;
        choices.forEach(choice => choice.tabIndex = next ? 0 : -1);
        if (next) { selections.hidden = false; void selections.offsetWidth; }
        else hideTimer = setTimeout(() => { if (!open) selections.hidden = true; }, reduced.matches ? 0 : 350);
        menu.classList.toggle('is-open', next);
        toggle.setAttribute('aria-expanded', String(next));
        toggle.setAttribute('aria-label', next ? 'Close staff navigation' : 'Open staff navigation');
        place();
        if (focus) toggle.focus({preventScroll:true});
    };
    const setDrawer = (next, focus = false) => {
        const wasOpen = drawer;
        drawer = Boolean(next && mode === 'sidebar' && narrow.matches);
        if (drawer && !wasOpen) { savedOverflow = document.body.style.overflow; document.body.style.overflow = 'hidden'; }
        if (!drawer && wasOpen) document.body.style.overflow = savedOverflow;
        main.inert = drawer;
        topbar.inert = drawer;
        if (!drawer && sidebar.contains(document.activeElement)) (focus ? opener : main).focus({preventScroll:true});
        sidebar.hidden = mode !== 'sidebar' || (narrow.matches && !drawer);
        sidebar.inert = sidebar.hidden;
        sidebar.setAttribute('aria-hidden', String(sidebar.hidden));
        backdrop.hidden = !drawer;
        opener.setAttribute('aria-expanded', String(drawer));
    };
    const sync = (showDrawer = false) => {
        root.dataset.staffNavMode = mode;
        menu.hidden = mode !== 'radial';
        menu.inert = menu.hidden;
        menu.setAttribute('aria-hidden', String(menu.hidden));
        modeButtons.forEach(button => button.setAttribute('aria-pressed', String(button.dataset.staffMode === mode)));
        setDrawer(showDrawer);
        place();
    };
    const focusNavigation = () => {
        const target = mode === 'radial' ? toggle : narrow.matches && !drawer ? opener : sidebar.querySelector('[data-staff-mode="sidebar"]');
        target.focus({preventScroll:true});
    };
    const finishTransition = (focus = true) => {
        if (!transition) return;
        const running = transition;
        transition = null;
        running.animations.forEach(animation => animation.cancel());
        root.classList.remove('is-staff-nav-switching');
        fx.hidden = true;
        sync(drawer);
        if (mode === 'sidebar') {
            sidebar.classList.add('is-unfolding');
            clearTimeout(arrivalTimer);
            arrivalTimer = setTimeout(() => sidebar.classList.remove('is-unfolding'), 650);
        }
        if (focus && !document.querySelector('dialog[open],.modal.show')) focusNavigation();
    };
    const switchMode = (next, animate = true) => {
        if (!['sidebar','radial'].includes(next) || next === mode || transition) return;
        const toSidebar = next === 'sidebar';
        const fromSidebar = sidebar.getBoundingClientRect();
        const fromLogo = (toSidebar ? toggle : sidebar.querySelector('.brand-link img')).getBoundingClientRect();
        setOpen(false);
        mode = next;
        if (!toSidebar) { dock = 'top-left'; persist('waku-staff-navigation-dock', dock); }
        persist('waku-staff-navigation-mode', mode);
        sync(toSidebar && narrow.matches);
        status.textContent = toSidebar ? 'Sidebar navigation selected.' : 'Menu+ navigation selected. Drag the logo to an edge or corner.';
        if (!animate || reduced.matches || !Element.prototype.animate || document.hidden) { focusNavigation(); return; }

        const targetSidebar = sidebar.getBoundingClientRect();
        const toLogo = (toSidebar ? sidebar.querySelector('.brand-link img') : toggle).getBoundingClientRect();
        const panel = toSidebar ? targetSidebar : fromSidebar;
        root.classList.add('is-staff-nav-switching');
        fx.hidden = false;
        const animations = [];
        // Six crisp pleats fold into the logo, or unfold into a white sidebar.
        strips.forEach((strip,index) => {
            const width = panel.width / strips.length;
            const panelFrame = { left:panel.left + width * index+'px', top:panel.top+'px', width:width+1+'px',
                height:panel.height+'px', transform:'rotateY(0deg) rotate(0deg)', borderRadius:'2px', opacity:1 };
            const logoRect = toSidebar ? fromLogo : toLogo;
            const foldedFrame = { left:logoRect.left+'px', top:logoRect.top + logoRect.height / 2 + (index - 3) * 5+'px',
                width:logoRect.width+'px', height:'12px', transform:'rotateY(65deg) rotate('+((index-2.5)*9)+'deg)', borderRadius:'8px', opacity:.5 };
            animations.push(strip.animate(toSidebar ? [foldedFrame,panelFrame] : [panelFrame,foldedFrame], {
                duration:570, delay:index*35, easing:'cubic-bezier(.22,.75,.2,1)', fill:'both'
            }));
        });
        const point = rect => ({ left:rect.left+'px', top:rect.top+'px', width:rect.width+'px', height:rect.height+'px' });
        animations.push(logo.animate([
            {...point(fromLogo), transform:'rotate(0deg) scale(1)'},
            {left:Math.max(90,(fromLogo.left+toLogo.left)/2)+'px',top:Math.max(12,(fromLogo.top+toLogo.top)/2-45)+'px', transform:'rotate('+(toSidebar?-18:18)+'deg) scale(.85)', offset:.5},
            {...point(toLogo), transform:'rotate(0deg) scale(1)'}
        ], {duration:760,easing:'cubic-bezier(.22,.75,.2,1)',fill:'both'}));
        transition = {animations};
        const current = transition;
        Promise.allSettled(animations.map(animation => animation.finished)).then(() => { if (transition === current) finishTransition(); });
    };
    const finishDrag = event => {
        if (!drag || (event?.pointerId !== undefined && drag.id !== event.pointerId)) return;
        const ended = drag;
        drag = null;
        if (ended.moved) {
            const rect = menu.getBoundingClientRect(), options = positions(72);
            dock = docks.reduce((nearest,candidate) => Math.hypot(options[candidate][0]-rect.left,options[candidate][1]-rect.top) < Math.hypot(options[nearest][0]-rect.left,options[nearest][1]-rect.top) ? candidate : nearest);
            persist('waku-staff-navigation-dock', dock);
            status.textContent = 'Menu+ docked at the '+dock.replace('-',' ')+'.';
            menu.classList.remove('is-dragging');
            suppressClickUntil = performance.now()+700;
            place();
        } else if (event?.type === 'pointerup' && ['touch','pen'].includes(event.pointerType)) {
            suppressClickUntil = performance.now()+700;
            setOpen(!open);
        }
        if (toggle.hasPointerCapture(ended.id)) toggle.releasePointerCapture(ended.id);
    };
    modeButtons.forEach(button => button.addEventListener('click', () => switchMode(button.dataset.staffMode)));
    opener.addEventListener('click', () => {
        if (transition) return;
        setDrawer(true);
        (sidebar.querySelector('[aria-current="page"]') || sidebar.querySelector('a')).focus({preventScroll:true});
    });
    document.querySelectorAll('[data-close-staff-sidebar]').forEach(button => button.addEventListener('click', () => setDrawer(false,true)));
    menu.addEventListener('click', event => {
        if (event.detail !== 0 && performance.now() < suppressClickUntil) { event.preventDefault(); event.stopImmediatePropagation(); }
    },true);
    menu.addEventListener('pointerdown', () => { suppressClickUntil = 0; });
    toggle.addEventListener('click', () => setOpen(!open));
    toggle.addEventListener('pointerdown', event => {
        if (event.button !== 0 || !event.isPrimary || transition) return;
        suppressClickUntil = 0;
        if (['touch','pen'].includes(event.pointerType)) { event.preventDefault(); toggle.focus({preventScroll:true}); }
        const rect = toggle.getBoundingClientRect();
        drag = {id:event.pointerId, x:event.clientX, y:event.clientY, offsetX:event.clientX-rect.left-rect.width/2, offsetY:event.clientY-rect.top-rect.height/2, moved:false};
    });
    window.addEventListener('pointermove', event => {
        if (!drag || drag.id !== event.pointerId) return;
        if (!drag.moved && Math.hypot(event.clientX-drag.x,event.clientY-drag.y) < 8) return;
        if (!drag.moved) { drag.moved = true; toggle.setPointerCapture(event.pointerId); menu.classList.add('is-dragging'); setOpen(false); }
        const v = view();
        menu.style.left = Math.max(v.x+16,Math.min(v.x+v.width-88,event.clientX-drag.offsetX-36))+'px';
        menu.style.top = Math.max(v.y+16,Math.min(v.y+v.height-88,event.clientY-drag.offsetY-36))+'px';
        if (event.cancelable) event.preventDefault();
    });
    ['pointerup','pointercancel'].forEach(type => window.addEventListener(type,finishDrag));
    window.addEventListener('blur', () => finishDrag());
    toggle.addEventListener('lostpointercapture',finishDrag);
    toggle.addEventListener('keydown', event => {
        const direction = {ArrowLeft:-1,ArrowUp:-1,ArrowRight:1,ArrowDown:1}[event.key];
        if (!direction) return;
        event.preventDefault();
        dock = docks[(docks.indexOf(dock)+direction+docks.length)%docks.length];
        persist('waku-staff-navigation-dock', dock);
        place();
        status.textContent = 'Menu+ docked at the '+dock.replace('-',' ')+'.';
    });
    selections.addEventListener('keydown', event => {
        const index = choices.indexOf(document.activeElement);
        if (index < 0) return;
        const direction = {ArrowLeft:-1,ArrowUp:-1,ArrowRight:1,ArrowDown:1}[event.key];
        if (direction) { event.preventDefault(); choices[(index+direction+choices.length)%choices.length].focus(); }
        else if (['Home','End'].includes(event.key)) { event.preventDefault(); choices[event.key==='Home'?0:choices.length-1].focus(); }
    });
    sidebar.addEventListener('keydown', event => {
        if (!drawer || event.key !== 'Tab') return;
        const nodes = [...sidebar.querySelectorAll('a[href],button:not(:disabled)')].filter(node => node.getClientRects().length);
        if (event.shiftKey && document.activeElement === nodes[0]) { event.preventDefault(); nodes.at(-1).focus(); }
        else if (!event.shiftKey && document.activeElement === nodes.at(-1)) { event.preventDefault(); nodes[0].focus(); }
    });
    document.addEventListener('keydown', event => {
        if (event.key !== 'Escape' || document.querySelector('dialog[open],.modal.show')) return;
        if (open) { event.preventDefault(); setOpen(false,true); }
        else if (drawer) { event.preventDefault(); setDrawer(false,true); }
    });
    document.addEventListener('pointerdown', event => { if (open && !menu.contains(event.target)) setOpen(false); });
    document.addEventListener('focusin', event => { if (open && !menu.contains(event.target)) setOpen(false); });
    window.WakuStaffNavigation = { prepareDialog(trigger) {
        finishTransition(false);
        const returnTo = menu.contains(trigger) ? toggle : sidebar.contains(trigger) && narrow.matches ? opener : trigger;
        setOpen(false);
        setDrawer(false);
        return returnTo;
    }};
    document.addEventListener('show.bs.modal', () => { finishTransition(false); setOpen(false); setDrawer(false); });
    const resize = () => { finishTransition(false); finishDrag(); sync(drawer && narrow.matches); };
    window.addEventListener('resize',resize);
    window.visualViewport?.addEventListener('resize',resize);
    window.visualViewport?.addEventListener('scroll',place);
    narrow.addEventListener('change', () => { finishTransition(false); sync(false); });
    reduced.addEventListener('change', () => finishTransition());
    document.addEventListener('visibilitychange', () => { if (document.hidden) finishTransition(false); });
    window.addEventListener('storage', event => {
        if (event.key !== 'waku-staff-navigation-mode') return;
        finishTransition(false);
        switchMode(event.newValue === 'radial' ? 'radial' : 'sidebar',false);
    });
    window.addEventListener('pageshow', () => { finishTransition(false); setOpen(false); sync(false); });
    sync(false);
})();
