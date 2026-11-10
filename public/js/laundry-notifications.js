(() => {
    'use strict';
    const panel = document.querySelector('[data-notification-center]');
    if (!panel || panel.dataset.notificationsInitialized) return;
    panel.dataset.notificationsInitialized = 'true';
    const role = panel.dataset.role;
    const context = panel.dataset.context;
    const list = panel.querySelector('[data-notification-list]');
    const feedStatus = panel.querySelector('[data-feed-status]');
    const readAll = panel.querySelector('[data-read-all]');
    const deviceButton = panel.querySelector('[data-toggle-device-alerts]');
    const deviceStatus = panel.querySelector('[data-device-status]');
    const toasts = document.querySelector('[data-notification-toasts]');
    const base = new URL(panel.dataset.scope, location.href);
    const prefix = 'waku-'+role+'-'+panel.dataset.ownerId;
    const ordersUrl = new URL(panel.dataset.ordersUrl, location.href);
    const orderPrefix = new URL(panel.dataset.orderPrefix, location.href);
    const readUrl = id => panel.dataset.feed.replace(/\/$/,'')+'/'+id+'/read';
    const cursorKey = prefix+'-notification-cursor';
    const deviceKey = prefix+'-device-alerts';
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const get = key => { try { return localStorage.getItem(key); } catch { return null; } };
    const put = (key,value) => { try { localStorage.setItem(key,String(value)); } catch {} };
    const readCursor = () => { const raw = get(cursorKey); return raw !== null && /^\d+$/.test(raw) ? Number(raw) : null; };
    let cursor = readCursor();
    let enabled = get(deviceKey) === 'true';
    let registration;
    let workerPromise;
    let fetching = false;
    let refreshPending = false;
    let stopped = false;
    let timer;
    let returnFocus;
    let savedOverflow;
    let controller;
    let unread = 0;
    const safeUrl = raw => {
        if (typeof raw !== 'string' || !raw.trim()) return null;
        try { const url = new URL(raw,location.href); return url.origin === base.origin && (url.pathname.startsWith(orderPrefix.pathname) || url.href === ordersUrl.href) ? url.href : null; } catch { return null; }
    };
    const node = (tag,className,text) => { const element = document.createElement(tag); if (className) element.className = className; if (text !== undefined) element.textContent = text; return element; };
    const icon = className => { const element = node('i','bi '+className); element.setAttribute('aria-hidden','true'); return element; };
    const setCount = count => {
        unread = count;
        document.querySelectorAll('[data-notification-unread],[data-staff-unread]').forEach(badge => { badge.hidden = count === 0; badge.textContent = count > 99 ? '99+' : String(count); });
        panel.querySelector('[data-inbox-count]').textContent = String(count);
        document.querySelectorAll('[data-open-notifications],[data-open-staff-notifications]').forEach(button => button.setAttribute('aria-label',count ? 'Notifications, '+count+' unread' : 'Notifications'));
        readAll.disabled = !count;
    };
    const endSession = message => {
        stopped = true; clearTimeout(timer); refreshPending = false;
        setCount(0); list.replaceChildren(); toasts.replaceChildren();
        feedStatus.textContent = message;
        deviceButton.disabled = true;
        deviceStatus.textContent = 'Refresh this page after signing in to resume alerts.';
        if (registration) void registration.getNotifications().then(items => items.filter(item => item.tag.startsWith(prefix+'-')).forEach(item => item.close())).catch(() => {});
    };
    const toast = (title,message,url) => {
        const element = node('div','laundry-toast'+(role === 'staff' ? ' staff-toast' : ''));
        const body = node('div'); body.append(node('strong','',title),node('p','',message));
        if (url) { const link = node('a','','Open order'); link.href = url; body.append(link); }
        const close = node('button'); close.type = 'button'; close.setAttribute('aria-label','Dismiss notification'); close.append(icon('bi-x-lg'));
        close.addEventListener('click', () => element.remove());
        element.append(icon('bi-bell'),body,close); toasts.append(element);
        while (toasts.children.length > 3) toasts.firstElementChild.remove();
        let timeout = setTimeout(() => element.remove(),10000);
        element.addEventListener('pointerenter', () => clearTimeout(timeout));
        element.addEventListener('focusin', () => clearTimeout(timeout));
        element.addEventListener('pointerleave', () => { if (!element.contains(document.activeElement)) timeout = setTimeout(() => element.remove(),5000); });
    };
    const ensureWorker = () => {
        if (!workerPromise) {
            workerPromise = navigator.serviceWorker.register(panel.dataset.worker,{scope:base.pathname})
                .then(async worker => {
                    if (!worker.active) await new Promise((resolve,reject) => {
                        const pending = worker.installing || worker.waiting;
                        if (!pending) { reject(new Error('No notification worker.')); return; }
                        const timeout = setTimeout(() => reject(new Error('Worker activation timed out.')),10000);
                        const check = () => {
                            if (pending.state === 'activated') { clearTimeout(timeout); resolve(); }
                            else if (pending.state === 'redundant') { clearTimeout(timeout); reject(new Error('Worker could not activate.')); }
                        };
                        pending.addEventListener('statechange',check); check();
                    });
                    registration = worker;
                    return worker;
                }).catch(error => { workerPromise = null; throw error; });
        }
        return workerPromise;
    };
    const supported = () => window.isSecureContext && 'Notification' in window && 'serviceWorker' in navigator;
    const updateDevice = () => {
        if (stopped) { deviceButton.disabled = true; return; }
        deviceButton.disabled = !supported();
        deviceButton.setAttribute('aria-pressed',String(enabled && supported() && Notification.permission === 'granted'));
        deviceButton.textContent = enabled && supported() && Notification.permission === 'granted' ? 'Turn off' : 'Enable';
        deviceStatus.textContent = !window.isSecureContext ? 'Open the HTTPS site to enable device alerts. Page popups still work.'
            : !supported() ? 'Use the notification inbox and page popups in this browser.'
            : Notification.permission === 'denied' ? 'Blocked in browser settings. Page popups still work.'
            : enabled && Notification.permission === 'granted' ? 'On for this device while '+context+' is open.'
            : 'Get order popups while '+context+' is open.';
    };
    const deviceAlert = async (title,message,url,tag) => {
        if (!enabled || !supported() || Notification.permission !== 'granted' || stopped) return;
        try {
            const worker = registration || await ensureWorker();
            if (stopped || !enabled) return;
            await worker.showNotification(title,{body:message,icon:panel.dataset.icon,
                tag:prefix+'-'+tag,data:{url:url || ordersUrl.href,role,recipientKey:panel.dataset.recipientKey},timestamp:Date.now()});
        } catch { deviceStatus.textContent = 'Device alert unavailable. Updates still appear here and on the page.'; }
    };
    deviceButton.addEventListener('click', async () => {
        if (!supported()) return;
        if (enabled && Notification.permission === 'granted') {
            enabled = false; put(deviceKey,false); updateDevice();
            try { const worker = registration || await ensureWorker(); (await worker.getNotifications()).filter(item => item.tag.startsWith(prefix+'-')).forEach(item => item.close()); } catch {}
            return;
        }
        deviceButton.disabled = true;
        try {
            // Permission is requested only in direct response to this button.
            const permission = Notification.permission === 'granted' ? 'granted' : await Notification.requestPermission();
            enabled = permission === 'granted';
            put(deviceKey,enabled); updateDevice();
            if (enabled) {
                await ensureWorker();
                await deviceAlert('Waku-Waku device alerts are on','Laundry updates will appear here while '+context+' is open.',ordersUrl.href,'enabled');
            }
        } catch { enabled = false; put(deviceKey,false); updateDevice(); deviceStatus.textContent = 'Could not enable device alerts. You can try again.'; }
        finally { deviceButton.disabled = !supported(); }
    });
    const post = async url => {
        const response = await fetch(url,{method:'POST',credentials:'same-origin',headers:{Accept:'application/json','X-CSRF-TOKEN':csrf,'X-Notification-Recipient':panel.dataset.recipientKey}});
        if ([401,403,419].includes(response.status)) endSession('Session ended or account changed. Refresh this page for updates.');
        if (!response.ok) throw new Error('Could not save notification state.');
    };
    const render = items => {
        list.replaceChildren();
        if (!items.length) {
            const empty = node('div','laundry-inbox-empty');
            empty.append(icon('bi-bell-slash'),node('strong','','You’re all caught up'),node('p','',role === 'customer' ? 'Your bookings, laundry progress and payments will appear here.' : 'New orders and laundry updates will appear here.'));
            list.append(empty); return;
        }
        items.forEach(item => {
            const article = node('article','laundry-notification-item'+(!item.read_at?' is-unread':''));
            const body = node('div'); body.append(node('h3','',item.title),node('p','',item.message));
            const date = new Date(item.created_at);
            const time = node('time','',Number.isNaN(date.getTime())?'':date.toLocaleString([], {month:'short',day:'numeric',hour:'numeric',minute:'2-digit'}));
            time.dateTime = item.created_at; body.append(time);
            const footer = node('footer');
            const url = safeUrl(item.url);
            if (url) {
                const link = node('a','','Open order'); link.href = url;
                link.addEventListener('click', () => { if (!item.read_at) fetch(readUrl(item.id),{method:'POST',keepalive:true,headers:{Accept:'application/json','X-CSRF-TOKEN':csrf,'X-Notification-Recipient':panel.dataset.recipientKey}}).catch(() => {}); });
                footer.append(link);
            }
            if (!item.read_at) {
                const read = node('button','','Mark read'); read.type = 'button';
                read.addEventListener('click', async () => {
                    read.disabled = true;
                    try { await post(readUrl(item.id)); item.read_at = new Date().toISOString(); article.classList.remove('is-unread'); read.remove(); setCount(Math.max(0,unread-1)); }
                    catch { if (!stopped) feedStatus.textContent = 'Could not mark read. Try again.'; read.disabled = false; }
                }); footer.append(read);
            }
            body.append(footer); article.append(icon('bi-basket'),body); list.append(article);
        });
    };
    const load = async () => {
        if (stopped) return;
        if (fetching) { refreshPending = true; return; }
        if (!navigator.onLine) { feedStatus.textContent = 'Offline. Updates resume when you reconnect.'; list.setAttribute('aria-busy','false'); return; }
        fetching = true;
        controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(),10000);
        try {
            const url = new URL(panel.dataset.feed);
            if (cursor !== null) url.searchParams.set('after',String(cursor));
            const response = await fetch(url,{credentials:'same-origin',headers:{Accept:'application/json','X-Notification-Recipient':panel.dataset.recipientKey},cache:'no-store',signal:controller.signal});
            if ([401,403,419].includes(response.status)) { endSession('Session ended or account changed. Refresh this page for updates.'); return; }
            if (!response.ok) throw new Error('Feed unavailable.');
            const data = await response.json();
            if (stopped) return;
            if (data.recipient_key !== panel.dataset.recipientKey) { endSession('Account changed. Refresh this page for updates.'); return; }
            if (!Array.isArray(data.notifications) || !Array.isArray(data.events)) throw new Error('Invalid feed.');
            const claimed = Math.max(cursor ?? 0,readCursor() ?? 0);
            const fresh = cursor === null ? [] : data.events.filter(item => Number(item.id) > claimed);
            cursor = Math.max(claimed,Number(data.latest_id)||0);
            // Claim before awaiting device alerts so other open tabs do not repeat them.
            put(cursorKey,cursor);
            setCount(Number(data.unread_count)||0);
            render(data.notifications);
            feedStatus.textContent = 'Up to date';
            if (fresh.length > 3) {
                const message = fresh.length+' new laundry updates. Open Notifications to review them.';
                if (!document.hidden && !panel.open) toast(role === 'customer' ? 'You have laundry updates' : 'Your queue has updates',message);
                void deviceAlert(role === 'customer' ? 'You have laundry updates' : 'Your queue has updates',message,ordersUrl.href,'batch-'+cursor);
            } else fresh.forEach(item => {
                const url = safeUrl(item.url);
                if (!document.hidden && !panel.open) toast(item.title,item.message,url);
                void deviceAlert(item.title,item.message,url,item.id);
            });
        } catch { if (!stopped) feedStatus.textContent = 'Updates paused. Retrying shortly…'; }
        finally {
            clearTimeout(timeout); fetching = false; list.setAttribute('aria-busy','false');
            if (refreshPending && !stopped) { refreshPending = false; void load(); }
        }
    };
    const schedule = () => { clearTimeout(timer); if (!stopped) timer = setTimeout(async () => { await load(); schedule(); },15000); };
    const refresh = () => { void load(); schedule(); };
    readAll.addEventListener('click', async () => {
        readAll.disabled = true;
        try { await post(panel.dataset.readAllUrl); await load(); }
        catch { if (!stopped) feedStatus.textContent = 'Could not mark all read. Try again.'; readAll.disabled = !unread; }
    });
    panel.querySelector('[data-refresh-notifications]').addEventListener('click',refresh);
    document.querySelectorAll('[data-open-notifications],[data-open-staff-notifications]').forEach(button => button.addEventListener('click', () => {
        if (panel.open) return;
        returnFocus = (window.WakuStaffNavigation || window.WakuAdminNavigation)?.prepareDialog(button) || button;
        savedOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        panel.showModal(); updateDevice(); refresh();
    }));
    panel.querySelector('[data-close-notifications]').addEventListener('click', () => panel.close());
    panel.addEventListener('click', event => { if (event.target === panel) { const rect = panel.getBoundingClientRect(); if (event.clientX<rect.left || event.clientX>rect.right || event.clientY<rect.top || event.clientY>rect.bottom) panel.close(); } });
    panel.addEventListener('close', () => { document.body.style.overflow = savedOverflow; returnFocus?.focus({preventScroll:true}); });
    document.addEventListener('visibilitychange', () => { if (!document.hidden) { updateDevice(); refresh(); } });
    window.addEventListener('online',refresh);
    window.addEventListener('offline', () => { feedStatus.textContent = 'Offline. Updates resume when you reconnect.'; });
    window.addEventListener('storage', event => { if (event.key === deviceKey) { enabled = event.newValue === 'true'; updateDevice(); } });
    window.addEventListener('pagehide', () => { clearTimeout(timer); controller?.abort(); });
    window.addEventListener('pageshow',refresh);
    document.querySelectorAll('form[action$="/logout"]').forEach(form => form.addEventListener('submit', () => {
        stopped = true; clearTimeout(timer); controller?.abort();
        if (registration) void registration.getNotifications().then(items => items.filter(item => item.tag.startsWith(prefix+'-')).forEach(item => item.close()));
    }));
    updateDevice();
    if (enabled && supported() && Notification.permission === 'granted') void ensureWorker().catch(() => {});
    refresh();
})();
