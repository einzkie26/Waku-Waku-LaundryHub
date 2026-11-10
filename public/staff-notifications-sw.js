/* Device notifications only. No page caching, push service, or background polling. */
'use strict';
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', event => event.waitUntil(self.clients.claim()));
self.addEventListener('notificationclick', event => {
    event.notification.close();
    const base = new URL(self.registration.scope);
    let url;
    try { url = new URL(event.notification.data?.url,base); } catch { url = base; }
    if (url.origin !== base.origin || !url.pathname.startsWith(base.pathname)) url = base;
    event.waitUntil((async () => {
        const windows = await self.clients.matchAll({type:'window',includeUncontrolled:true});
        const current = windows.find(client => client.url === url.href);
        if (current) return current.focus();
        const staff = windows.find(client => client.url.startsWith(base.href));
        if (staff) { await staff.navigate(url.href); return staff.focus(); }
        return self.clients.openWindow(url.href);
    })());
});
