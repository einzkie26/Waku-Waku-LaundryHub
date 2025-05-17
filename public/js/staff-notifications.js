class StaffNotifications {
    constructor() {
        this.notificationBadge = document.getElementById('notificationBadge');
        this.notificationCount = document.getElementById('notificationCount');
        this.notificationList = document.getElementById('notificationList');
        this.notificationDropdown = document.getElementById('notificationDropdown');
        this.unreadCount = 0;
        this.notifications = [];
        
        this.initialize();
    }
    
    async initialize() {
        // Load initial notifications
        await this.loadNotifications();
        
        // Set up event listeners
        this.setupEventListeners();
        
        // Initialize Pusher for real-time updates
        this.initializePusher();
    }
    
    async loadNotifications() {
        try {
            // In a real app, you would fetch this from your Laravel backend
            // const response = await fetch('/api/staff/notifications');
            // this.notifications = await response.json();
            
            // Mock data for demonstration
            this.notifications = [
                {
                    id: 1,
                    title: 'New Order Received',
                    message: 'Order #1234 has been placed by John Doe',
                    type: 'order',
                    action_url: '/staff/orders/1234',
                    created_at: new Date().toISOString(),
                    read_at: null
                },
                {
                    id: 2,
                    title: 'Task Assigned',
                    message: 'You have been assigned a new task: Process laundry batch',
                    type: 'task',
                    action_url: '/staff/tasks/5',
                    created_at: new Date(Date.now() - 1000 * 60 * 30).toISOString(),
                    read_at: null
                },
                {
                    id: 3,
                    title: 'System Update',
                    message: 'Scheduled maintenance this weekend',
                    type: 'system',
                    created_at: new Date(Date.now() - 1000 * 60 * 60 * 2).toISOString(),
                    read_at: new Date().toISOString()
                }
            ];
            
            this.updateUnreadCount();
            this.renderNotifications();
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    }
    
    initializePusher() {
        // Enable pusher logging - don't include this in production
        // Pusher.logToConsole = true;
        
        // In a real app, you would initialize Pusher with your credentials
        /*
        const pusher = new Pusher(process.env.MIX_PUSHER_APP_KEY, {
            cluster: process.env.MIX_PUSHER_APP_CLUSTER,
            encrypted: true
        });
        
        const channel = pusher.subscribe('staff.' + window.Laravel.userId);
        channel.bind('App\\Events\\NewNotification', (data) => {
            this.addNewNotification(data.notification);
        });
        */
        
        // For demo purposes, we'll simulate a new notification every 30 seconds
        // setInterval(() => {
        //     if (Math.random() > 0.7) { // 30% chance of a new notification
        //         this.simulateNewNotification();
        //     }
        // }, 30000);
    }
    
    simulateNewNotification() {
        const types = ['order', 'task', 'system', 'alert'];
        const messages = [
            'New order received',
            'Task completed by another staff member',
            'System update available',
            'Urgent: Low inventory alert',
            'New message from admin'
        ];
        
        const newNotification = {
            id: Date.now(),
            title: messages[Math.floor(Math.random() * messages.length)],
            message: 'This is a simulated notification for demonstration purposes.',
            type: types[Math.floor(Math.random() * types.length)],
            created_at: new Date().toISOString(),
            read_at: null,
            action_url: '#'
        };
        
        this.addNewNotification(newNotification);
    }
    
    addNewNotification(notification) {
        // Add to the beginning of the array
        this.notifications.unshift(notification);
        this.updateUnreadCount();
        this.renderNotifications();
        
        // Show desktop notification if not focused
        if (document.hidden) {
            this.showDesktopNotification(notification);
        }
        
        // Play sound (DISABLED)
        // this.playNotificationSound();
        
        // Animate bell icon
        this.animateBell();
    }
    
    updateUnreadCount() {
        this.unreadCount = this.notifications.filter(n => !n.read_at).length;
        this.updateBadge();
    }
    
    updateBadge() {
        if (this.notificationBadge) {
            this.notificationBadge.textContent = this.unreadCount;
            this.notificationBadge.style.display = this.unreadCount > 0 ? 'block' : 'none';
        }
        
        if (this.notificationCount) {
            this.notificationCount.textContent = `${this.unreadCount} New`;
            this.notificationCount.style.display = this.unreadCount > 0 ? 'inline-block' : 'none';
        }
        
        // Always set the tab title to 'Laundry System' (no unread count)
        document.title = 'Laundry System';
    }
    
    renderNotifications() {
        if (!this.notificationList) return;
        
        if (this.notifications.length === 0) {
            this.notificationList.innerHTML = `
                <div class="text-center py-4">
                    <i class="far fa-bell-slash fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-0">No notifications</p>
                </div>`;
            return;
        }
        
        // Clear current notifications
        this.notificationList.innerHTML = '';
        
        // Render each notification
        this.notifications.forEach(notification => {
            const notificationElement = this.createNotificationElement(notification);
            this.notificationList.appendChild(notificationElement);
        });
    }
    
    createNotificationElement(notification) {
        const template = document.createElement('div');
        template.innerHTML = `
            <x-notification-item :notification="${JSON.stringify(notification).replace(/"/g, '&quot;')}" />
        `;
        
        const element = template.firstElementChild;
        
        // Mark as read when clicked
        element.addEventListener('click', (e) => {
            if (!e.target.closest('a, button')) {
                this.markAsRead(notification.id);
            }
        });
        
        return element;
    }
    
    async markAsRead(notificationId) {
        const notification = this.notifications.find(n => n.id === notificationId);
        if (notification && !notification.read_at) {
            // In a real app, you would make an API call to mark the notification as read
            // await fetch(`/api/staff/notifications/${notificationId}/read`, { method: 'POST' });
            
            notification.read_at = new Date().toISOString();
            this.updateUnreadCount();
            this.updateNotificationElement(notificationId);
        }
    }
    
    async markAllAsRead() {
        // In a real app, you would make an API call to mark all notifications as read
        // await fetch('/api/staff/notifications/read-all', { method: 'POST' });
        
        this.notifications.forEach(notification => {
            if (!notification.read_at) {
                notification.read_at = new Date().toISOString();
            }
        });
        
        this.updateUnreadCount();
        this.renderNotifications();
    }
    
    updateNotificationElement(notificationId) {
        const element = this.notificationList.querySelector(`[data-notification-id="${notificationId}"]`);
        if (element) {
            const notification = this.notifications.find(n => n.id === notificationId);
            if (notification) {
                const newElement = this.createNotificationElement(notification);
                element.parentNode.replaceChild(newElement, element);
            }
        }
    }
    
    showDesktopNotification(notification) {
        if (!('Notification' in window)) return;
        
        if (Notification.permission === 'granted') {
            this.createNotification(notification);
        } else if (Notification.permission !== 'denied') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    this.createNotification(notification);
                }
            });
        }
    }
    
    createNotification(notification) {
        const options = {
            body: notification.message,
            icon: '/favicon.ico',
            badge: '/favicon.ico'
        };
        
        const notificationInstance = new Notification(notification.title, options);
        
        notificationInstance.onclick = () => {
            window.focus();
            this.markAsRead(notification.id);
            
            if (notification.action_url) {
                window.location.href = notification.action_url;
            }
            
            notificationInstance.close();
        };
        
        // Close the notification after 5 seconds
        setTimeout(() => {
            notificationInstance.close();
        }, 5000);
    }
    
    animateBell() {
        const bell = this.notificationDropdown.querySelector('i');
        if (bell) {
            bell.classList.add('bell-ring');
            
            // Remove the animation class after it completes
            setTimeout(() => {
                bell.classList.remove('bell-ring');
            }, 1000);
        }
    }
    
    setupEventListeners() {
        // Mark all as read when clicking the notification count
        if (this.notificationCount) {
            this.notificationCount.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.markAllAsRead();
            });
        }
        
        // Mark all as read when clicking the mark all button (if it exists)
        const markAllButton = document.getElementById('markAllAsRead');
        if (markAllButton) {
            markAllButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.markAllAsRead();
            });
        }
    }
}

// Initialize the notification system when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', () => {
    window.staffNotifications = new StaffNotifications();
});
