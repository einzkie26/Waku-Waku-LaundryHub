/* Shared device alerts. Pages are never cached; the open page checks for updates. */
'use strict';
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', event => event.waitUntil(self.clients.claim()));
self.addEventListener('notificationclick', event => {
    event.notification.close();
    const app = new URL('./',self.location.href);
    const role = event.notification.data?.role;
    const paths = {
        admin: {prefix:'admin/orders/',orders:'admin/orders'},
        staff: {prefix:'staff/orders/',orders:'staff/orders'},
        customer: {prefix:'order-tracking/',orders:'customer/orders/history'},
    };
    if (!Object.hasOwn(paths,role)) return;
    const orders = new URL(paths[role].orders,app);
    const prefix = new URL(paths[role].prefix,app);
    let url;
    try { url = new URL(event.notification.data?.url,app); } catch { url = orders; }
    if (url.origin !== app.origin || !(url.pathname.startsWith(prefix.pathname) || url.href === orders.href)) url = orders;
    event.waitUntil((async () => {
        const windows = await self.clients.matchAll({type:'window',includeUncontrolled:true});
        const exact = windows.find(client => client.url === url.href);
        if (exact) return exact.focus();
        // Use a window already on this order area; other roles keep their current page.
        const current = windows.find(client => client.url.startsWith(prefix.href) || client.url === orders.href);
        if (current) { await current.navigate(url.href); return current.focus(); }
        return self.clients.openWindow(url.href);
    })());
});
