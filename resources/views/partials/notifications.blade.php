@php
    $notificationRole = $notificationRole ?? auth('web')->user()?->role;
    $notificationOwner = $notificationOwner ?? auth('web')->user();
    $notificationRoute = $notificationRole.'.notifications';
    $notificationPanelId = $notificationRole.'-notifications';
    $notificationScope = $notificationRole === 'customer' ? url('/').'/' : url('/'.$notificationRole).'/';
    $notificationOrdersUrl = match($notificationRole) {
        'admin' => route('orders.index'),
        'staff' => route('staff.orders.index'),
        default => route('customer.orders.history'),
    };
    $notificationOrderPrefix = match($notificationRole) {
        'admin' => url('/admin/orders').'/',
        'staff' => url('/staff/orders').'/',
        default => url('/order-tracking').'/',
    };
    $notificationContext = $notificationRole === 'customer' ? 'this page' : 'the '.$notificationRole.' workspace';
@endphp
<dialog class="laundry-notifications {{ $notificationRole === 'staff' ? 'staff-notifications' : '' }}" id="{{ $notificationPanelId }}" aria-labelledby="{{ $notificationPanelId }}-title" data-notification-center
    data-feed="{{ route($notificationRoute.'.index') }}" data-read-all-url="{{ route($notificationRoute.'.read-all') }}"
    data-worker="{{ asset('laundry-notifications-sw.js') }}" data-scope="{{ $notificationScope }}"
    data-orders-url="{{ $notificationOrdersUrl }}" data-order-prefix="{{ $notificationOrderPrefix }}"
    data-icon="{{ asset('img/logo.png') }}" data-role="{{ $notificationRole }}" data-recipient-key="{{ $notificationRole.':'.$notificationOwner->id }}" data-owner-id="{{ $notificationOwner->id }}" data-context="{{ $notificationContext }}">
    <div class="laundry-notification-header"><div><span class="laundry-notification-eyebrow">{{ $notificationRole === 'customer' ? 'YOUR LAUNDRY' : strtoupper($notificationRole).' WORKSPACE' }}</span><h2 id="{{ $notificationPanelId }}-title">Notifications <span data-inbox-count>0</span></h2></div><button class="notification-icon-button" type="button" data-close-notifications aria-label="Close notifications" autofocus><i class="bi bi-x-lg" aria-hidden="true"></i></button></div>
    <div class="laundry-device-alerts"><span class="laundry-device-icon"><i class="bi bi-phone-vibrate" aria-hidden="true"></i></span><div><strong>Device alerts</strong><p id="{{ $notificationRole }}-device-status" data-device-status>Get order popups while {{ $notificationContext }} is open.</p></div><button class="laundry-alert-toggle" type="button" data-toggle-device-alerts aria-pressed="false" aria-describedby="{{ $notificationRole }}-device-status">Enable</button></div>
    <div class="laundry-inbox-toolbar"><span data-feed-status role="status">Loading updates…</span><button type="button" data-read-all>Mark all read</button></div>
    <div class="laundry-notification-list" data-notification-list aria-busy="true"><div class="laundry-inbox-empty"><i class="bi bi-bell" aria-hidden="true"></i><strong>Your updates live here</strong><p>{{ $notificationRole === 'customer' ? 'Checking your laundry updates…' : 'Checking the laundry queue…' }}</p></div></div>
    <div class="laundry-inbox-footer"><span><i class="bi bi-arrow-repeat" aria-hidden="true"></i>Updates automatically</span><button type="button" data-refresh-notifications>Refresh</button></div>
</dialog>
<div class="laundry-toast-stack" data-notification-toasts role="status" aria-live="polite" aria-atomic="false"></div>
