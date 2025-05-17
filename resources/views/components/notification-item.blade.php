@props(['notification'])

@php
    $iconMap = [
        'order' => 'fas fa-shopping-cart',
        'task' => 'fas fa-tasks',
        'system' => 'fas fa-info-circle',
        'alert' => 'fas fa-exclamation-triangle',
    ];
    
    $icon = $iconMap[$notification['type'] ?? 'system'] ?? 'fas fa-bell';
    $timeAgo = \Carbon\Carbon::parse($notification['created_at'])->diffForHumans();
    $isUnread = $notification['read_at'] === null;
    
    $bgClass = $isUnread ? 'bg-light' : '';
    $textClass = $isUnread ? 'fw-semibold' : 'text-muted';
    $badgeClass = [
        'order' => 'bg-primary',
        'task' => 'bg-success',
        'system' => 'bg-info',
        'alert' => 'bg-warning',
    ][$notification['type'] ?? 'system'] ?? 'bg-secondary';
@endphp

<div class="notification-item {{ $isUnread ? 'unread' : '' }} p-3 border-bottom" data-notification-id="{{ $notification['id'] }}">
    <div class="d-flex">
        <div class="flex-shrink-0 me-3">
            <div class="icon-shape icon-sm {{ $badgeClass }} text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                <i class="{{ $icon }} fa-fw"></i>
            </div>
        </div>
        <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-start">
                <h6 class="mb-1 {{ $textClass }}">{{ $notification['title'] ?? 'New Notification' }}</h6>
                <small class="notification-time">{{ $timeAgo }}</small>
            </div>
            <p class="mb-0 small text-muted">{{ $notification['message'] ?? '' }}</p>
            @if(isset($notification['action_url']))
                <a href="{{ $notification['action_url'] }}" class="btn btn-sm btn-outline-primary mt-2">View Details</a>
            @endif
        </div>
    </div>
</div>
