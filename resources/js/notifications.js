// Global Notification System
class NotificationSystem {
    constructor() {
        this.notifications = new Map();
        this.init();
    }

    init() {
        // Listen for session flash messages
        this.handleSessionMessages();
    }

    handleSessionMessages() {
        // Check for success messages
        const successMessage = document.querySelector('[data-session-success]');
        if (successMessage) {
            this.show('success', successMessage.textContent);
            successMessage.remove();
        }

        // Check for error messages
        const errorMessage = document.querySelector('[data-session-error]');
        if (errorMessage) {
            this.show('error', errorMessage.textContent);
            errorMessage.remove();
        }

        // Check for warning messages
        const warningMessage = document.querySelector('[data-session-warning]');
        if (warningMessage) {
            this.show('warning', warningMessage.textContent);
            warningMessage.remove();
        }
    }

    show(type, message, options = {}) {
        const id = this.generateId();
        const duration = options.duration || 5000;
        const autoHide = options.autoHide !== false;

        // Create notification element
        const notification = this.createNotificationElement(id, type, message);
        
        // Add to DOM
        document.body.appendChild(notification);
        this.notifications.set(id, notification);

        // Show animation
        setTimeout(() => {
            // Remove mobile slide down classes
            notification.classList.remove('-translate-y-full', 'opacity-0');
            // Remove desktop slide left classes  
            notification.classList.remove('sm:translate-x-full');
            // Add visible classes
            notification.classList.add('translate-y-0', 'opacity-100');
            notification.classList.add('sm:translate-x-0');
        }, 100);

        // Auto hide
        if (autoHide) {
            setTimeout(() => {
                this.hide(id);
            }, duration);
        }

        return id;
    }

    createNotificationElement(id, type, message) {
        const div = document.createElement('div');
        div.id = id;
        div.className = `
            fixed z-[9999] transition-all duration-500 ease-in-out transform -translate-y-full opacity-0
            sm:translate-x-full sm:translate-y-0
            top-4 left-4 right-4 sm:top-4 sm:right-4 sm:left-auto
            max-w-sm mx-auto sm:mx-0 w-auto sm:w-auto
        `;

        const colors = {
            success: 'text-green-200',
            error: 'text-pink-200',
            warning: 'text-yellow-200',
            info: 'text-blue-200'
        };

        const titles = {
            success: 'Berhasil!',
            error: 'Gagal!',
            warning: 'Peringatan!',
            info: 'Informasi'
        };

        const iconSymbols = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };

        div.innerHTML = `
            <div class="bg-card border border-border rounded-lg shadow-lg p-4">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <span class="text-xl ${colors[type]}">${iconSymbols[type]}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-medium text-card-foreground">${titles[type]}</h4>
                        <p class="mt-1 text-sm text-muted-foreground">${message}</p>
                    </div>
                    <button onclick="notificationSystem.hide('${id}')" 
                            class="flex-shrink-0 p-1 text-muted-foreground hover:text-foreground transition-colors">
                        <span class="text-lg">×</span>
                    </button>
                </div>
            </div>
        `;

        return div;
    }


    hide(id) {
        const notification = this.notifications.get(id);
        if (!notification) return;

        // Hide animation
        // Remove visible classes
        notification.classList.remove('translate-y-0', 'opacity-100');
        notification.classList.remove('sm:translate-x-0');
        // Add hide classes
        notification.classList.add('-translate-y-full', 'opacity-0');
        notification.classList.add('sm:translate-x-full');

        // Remove from DOM after animation
        setTimeout(() => {
            notification.remove();
            this.notifications.delete(id);
        }, 500);
    }

    generateId() {
        return 'notification-' + Math.random().toString(36).substr(2, 9);
    }
}

// Initialize global notification system
window.notificationSystem = new NotificationSystem();
