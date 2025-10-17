<!-- Modal Component -->
<div id="modal-overlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[9999] hidden opacity-0 transition-opacity duration-300">
    <div id="modal-container" class="fixed inset-0 flex items-center justify-center p-4">
        <div id="modal-content" class="bg-card border border-border rounded-xl shadow-2xl max-w-md w-full mx-4 transform scale-95 transition-transform duration-300">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-border">
                <div id="modal-icon" class="flex items-center justify-center w-10 h-10 rounded-full mr-3">
                    <!-- Icon akan diisi oleh JavaScript -->
                </div>
                <div class="flex-1">
                    <h3 id="modal-title" class="text-lg font-semibold text-card-foreground">
                        <!-- Title akan diisi oleh JavaScript -->
                    </h3>
                    <p id="modal-subtitle" class="text-sm text-muted-foreground mt-1">
                        <!-- Subtitle akan diisi oleh JavaScript -->
                    </p>
                </div>
                <button id="modal-close" type="button" class="text-muted-foreground hover:text-foreground transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="p-6">
                <p id="modal-message" class="text-card-foreground">
                    <!-- Message akan diisi oleh JavaScript -->
                </p>
            </div>
            
            <!-- Modal Footer -->
            <div id="modal-footer" class="flex items-center justify-end gap-3 p-6 border-t border-border">
                <!-- Buttons akan diisi oleh JavaScript -->
            </div>
        </div>
    </div>
</div>

<script>
class ModalManager {
    constructor() {
        this.overlay = document.getElementById('modal-overlay');
        this.container = document.getElementById('modal-container');
        this.content = document.getElementById('modal-content');
        this.icon = document.getElementById('modal-icon');
        this.title = document.getElementById('modal-title');
        this.subtitle = document.getElementById('modal-subtitle');
        this.message = document.getElementById('modal-message');
        this.footer = document.getElementById('modal-footer');
        this.closeBtn = document.getElementById('modal-close');
        
        this.setupEventListeners();
    }
    
    setupEventListeners() {
        // Close modal when clicking overlay
        this.overlay.addEventListener('click', (e) => {
            if (e.target === this.overlay) {
                this.close();
            }
        });
        
        // Close modal when clicking close button
        this.closeBtn.addEventListener('click', () => {
            this.close();
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !this.overlay.classList.contains('hidden')) {
                this.close();
            }
        });
    }
    
    show(options) {
        const {
            type = 'info',
            title = 'Konfirmasi',
            subtitle = '',
            message = '',
            confirmText = 'Ya',
            cancelText = 'Batal',
            onConfirm = null,
            onCancel = null,
            showCancel = true
        } = options;
        
        // Set icon and colors based on type
        this.setIcon(type);
        
        // Set content
        this.title.textContent = title;
        this.subtitle.textContent = subtitle;
        this.message.textContent = message;
        
        // Create buttons
        this.footer.innerHTML = '';
        
        if (showCancel) {
            const cancelBtn = this.createButton(cancelText, 'secondary', () => {
                this.close();
                if (onCancel) onCancel();
            });
            this.footer.appendChild(cancelBtn);
        }
        
        const confirmBtn = this.createButton(confirmText, type, () => {
            this.close();
            if (onConfirm) onConfirm();
        });
        this.footer.appendChild(confirmBtn);
        
        // Show modal
        this.overlay.classList.remove('hidden');
        this.overlay.classList.remove('opacity-0');
        this.content.classList.remove('scale-95');
        this.content.classList.add('scale-100');
        
        // Focus on confirm button
        setTimeout(() => {
            confirmBtn.focus();
        }, 100);
    }
    
    setIcon(type) {
        const iconConfig = {
            success: {
                icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>`,
                bgColor: 'bg-green-100',
                textColor: 'text-green-600'
            },
            warning: {
                icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>`,
                bgColor: 'bg-yellow-100',
                textColor: 'text-yellow-600'
            },
            error: {
                icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>`,
                bgColor: 'bg-red-100',
                textColor: 'text-red-600'
            },
            info: {
                icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>`,
                bgColor: 'bg-blue-100',
                textColor: 'text-blue-600'
            }
        };
        
        const config = iconConfig[type] || iconConfig.info;
        this.icon.className = `flex items-center justify-center w-10 h-10 rounded-full mr-3 ${config.bgColor} ${config.textColor}`;
        this.icon.innerHTML = config.icon;
    }
    
    createButton(text, type, onClick) {
        const button = document.createElement('button');
        button.textContent = text;
        button.type = 'button';
        
        const typeClasses = {
            success: 'bg-green-600 hover:bg-green-700 text-white',
            warning: 'bg-yellow-600 hover:bg-yellow-700 text-white',
            error: 'bg-red-600 hover:bg-red-700 text-white',
            info: 'bg-blue-600 hover:bg-blue-700 text-white',
            secondary: 'bg-muted hover:bg-muted/80 text-muted-foreground'
        };
        
        button.className = `px-4 py-2 rounded-lg font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 ${typeClasses[type] || typeClasses.info}`;
        button.addEventListener('click', onClick);
        
        return button;
    }
    
    close() {
        this.overlay.classList.add('opacity-0');
        this.content.classList.remove('scale-100');
        this.content.classList.add('scale-95');
        
        setTimeout(() => {
            this.overlay.classList.add('hidden');
        }, 300);
    }
}

// Global modal instance
window.modal = new ModalManager();

// Helper functions for backward compatibility
window.showAlert = function(message, title = 'Informasi', type = 'info') {
    window.modal.show({
        type: type,
        title: title,
        message: message,
        confirmText: 'OK',
        showCancel: false
    });
};

window.showConfirm = function(message, onConfirm, title = 'Konfirmasi', type = 'warning') {
    window.modal.show({
        type: type,
        title: title,
        message: message,
        onConfirm: onConfirm,
        confirmText: 'Ya',
        cancelText: 'Batal'
    });
};
</script>