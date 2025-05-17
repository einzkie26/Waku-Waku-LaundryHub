<!-- Notification Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 shadow-sm">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="notificationModalLabel">
                    <i class="bi bi-bell-fill text-danger me-2"></i> Notifications
                </h5>
                <button type="button" class="btn btn-sm btn-outline-primary" id="markAllReadBtn">
                    <i class="bi bi-check2-all me-1"></i> Mark all as read
                </button>
            </div>

            <div class="modal-body">
                @auth
                    <ul class="list-group list-group-flush" id="notificationsList">
                        @forelse(auth()->user()->logs()->latest()->take(5)->get() as $log)
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="d-flex">
                                    @php
                                        $icon = 'bi-bell-fill';
                                        $iconColor = 'text-primary';
                                        
                                        if (str_contains($log->action, 'completed')) {
                                            $icon = 'bi-check-circle-fill';
                                            $iconColor = 'text-success';
                                        } elseif (str_contains($log->action, 'ready')) {
                                            $icon = 'bi-box-seam-fill';
                                            $iconColor = 'text-info';
                                        } elseif (str_contains($log->action, 'washing') || str_contains($log->action, 'drying')) {
                                            $icon = 'bi-water';
                                            $iconColor = 'text-primary';
                                        } elseif (str_contains($log->action, 'picked up')) {
                                            $icon = 'bi-truck';
                                            $iconColor = 'text-warning';
                                        }
                                    @endphp
                                    <i class="bi {{ $icon }} {{ $iconColor }} me-3 fs-5"></i>
                                    <div>
                                        <div class="fw-semibold">{{ $log->action }}</div>
                                        <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                                <form action="{{ route('notifications.destroy', $log->id) }}" method="POST" onsubmit="return confirm('Delete this notification?')" class="ms-2">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </form>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted">No recent notifications</li>
                        @endforelse
                    </ul>
                @endauth
            </div>

            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notifications -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055">
    @if (session('success') || session('error'))
        <audio id="notification-sound" autoplay>
            <source src="{{ asset('sounds/notify.wav') }}" type="audio/mpeg">
        </audio>
    @endif

    <!-- Success Toast Notification -->
    @if (session('success'))
        <div class="toast align-items-center text-white bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    {{ session('success') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    @endif

    <!-- Error Toast Notification -->
    @if (session('error'))
        <div class="toast align-items-center text-white bg-danger border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    {{ session('error') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Function to update notification badge
    function updateNotificationBadge() {
        fetch('/notifications/count')
            .then(response => response.json())
            .then(data => {
                const badge = document.querySelector('.bi-bell-fill + .badge');
                if (data.count > 0) {
                    if (badge) {
                        badge.textContent = data.count;
                    } else {
                        const newBadge = document.createElement('span');
                        newBadge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                        newBadge.style.fontSize = '0.5rem';
                        newBadge.textContent = data.count;
                        document.querySelector('.bi-bell-fill').appendChild(newBadge);
                    }
                } else if (badge) {
                    badge.remove();
                }
            });
    }

    // Update badge every 30 seconds
    setInterval(updateNotificationBadge, 30000);

    // Update badge when modal is closed
    document.getElementById('notificationModal').addEventListener('hidden.bs.modal', function () {
        updateNotificationBadge();
    });

    // Mark all as read functionality
    document.getElementById('markAllReadBtn').addEventListener('click', function() {
        fetch('/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the notification badge
                const badge = document.querySelector('.bi-bell-fill + .badge');
                if (badge) {
                    badge.remove();
                }
                // Show success toast
                const toast = new bootstrap.Toast(document.createElement('div'));
                toast.show();
            }
        });
    });
</script>
@endpush
