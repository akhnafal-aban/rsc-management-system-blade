<style>
    .sidebar.open~.main-content nav {
        left: 250px !important;
    }
</style>


<nav class="hidden sm:block bg-card border-b border-border px-4 sm:px-6 py-3 shadow-sm fixed top-0 z-40 transition-all duration-500 ease-in-out"
    style="left: 78px; right: 0;">
    <div class="flex items-center justify-between">
        <!-- Logo + Brand -->
        <div class="flex items-center space-x-2 sm:space-x-3 min-w-0 flex-1">
            <div
                class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl flex items-center justify-center shadow-sm overflow-hidden flex-shrink-0">
                <img src="{{ asset('build/assets/img/rsc-logo.png') }}" alt="RSC Logo"
                    class="w-full h-full object-cover rounded-xl">
            </div>
            <div class="min-w-0 flex-1">
                <h1 class="text-sm sm:text-lg font-semibold text-card-foreground leading-tight truncate">Really Sports
                    Center</h1>
                <p class="text-xs text-muted-foreground tracking-wide hidden sm:block">Management System</p>
            </div>
        </div>

        <!-- Mobile menu button -->
        <button id="mobile-menu-btn"
            class="lg:hidden p-2 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-lg transition-colors">
            <x-ui.icon name="menu" class="w-5 h-5" />
        </button>

        <!-- Right section -->
        <div class="hidden lg:flex items-center space-x-3 xl:space-x-5">
            <!-- Icons -->
            <div class="flex items-center space-x-1 xl:space-x-2">
                <div class="relative">
                    <button id="notification-btn"
                        class="p-2.5 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-lg transition-colors relative"
                        title="Notifikasi" onclick="toggleNotificationPopup()">
                        <!-- Bell icon - will be dynamically switched between normal and notification state -->
                        <div id="bell-icon" class="transition-all duration-300">
                            <x-ui.icon name="bell" class="w-5 h-5 transition-all duration-300" id="bell-svg" />
                        </div>
                    </button>

                    <!-- Notification Popup -->
                    <div id="notification-popup"
                        class="absolute right-0 top-12 w-72 xl:w-80 bg-card border border-border rounded-lg shadow-lg z-50 hidden">
                        <div class="p-4 border-b border-border">
                            <h3 class="text-sm font-semibold text-card-foreground">Notifikasi Sistem</h3>
                        </div>
                        <div id="notification-content" class="max-h-80 overflow-y-auto">
                            <div class="p-4 text-center text-muted-foreground">
                                <p>Memuat notifikasi...</p>
                            </div>
                        </div>
                    </div>
                </div>
                <button
                    class="p-2.5 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-lg transition-colors"
                    title="Pengaturan">
                    <x-ui.icon name="settings" class="w-5 h-5" />
                </button>
            </div>
        </div>

        <!-- Mobile notification button -->
        <div class="lg:hidden flex items-center space-x-1">
            <div class="relative">
                <button id="notification-btn-mobile"
                    class="p-2 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-lg transition-colors relative"
                    title="Notifikasi" onclick="toggleNotificationPopup()">
                    <div id="bell-icon-mobile" class="transition-all duration-300">
                        <x-ui.icon name="bell" class="w-5 h-5 transition-all duration-300" id="bell-svg-mobile" />
                    </div>
                </button>

                <!-- Mobile Notification Popup -->
                <div id="notification-popup-mobile"
                    class="fixed inset-x-4 top-20 bg-card border border-border rounded-lg shadow-lg z-50 hidden">
                    <div class="p-4 border-b border-border">
                        <h3 class="text-sm font-semibold text-card-foreground">Notifikasi Sistem</h3>
                    </div>
                    <div id="notification-content-mobile" class="max-h-80 overflow-y-auto">
                        <div class="p-4 text-center text-muted-foreground">
                            <p>Memuat notifikasi...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
    let notificationInterval;

    function handleLogout() {
        if (confirm('Apakah Anda yakin ingin keluar?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('logout') }}';

            const token = document.createElement('input');
            token.type = 'hidden';
            token.name = '_token';
            token.value = '{{ csrf_token() }}';

            form.appendChild(token);
            document.body.appendChild(form);
            form.submit();
        }
    }

    function toggleNotificationPopup() {
        const popup = document.getElementById('notification-popup');
        const popupMobile = document.getElementById('notification-popup-mobile');

        // Determine which popup to use based on screen size
        const isMobile = window.innerWidth < 1024;
        const activePopup = isMobile ? popupMobile : popup;

        if (!activePopup) return;

        const isHidden = activePopup.classList.contains('hidden');

        if (isHidden) {
            loadNotifications();
            activePopup.classList.remove('hidden');
            // Mark notifications as read when popup is opened
            markNotificationsAsRead();
        } else {
            activePopup.classList.add('hidden');
        }
    }

    function markNotificationsAsRead() {
        fetch('{{ route('notifications.mark-read') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Update bell icon to normal state
                updateBellIcon(false);
            })
            .catch(error => {
                console.error('Error marking notifications as read:', error);
            });
    }

    function loadNotifications() {
        fetch('{{ route('notifications.scheduled-commands') }}')
            .then(response => response.json())
            .then(data => {
                const content = document.getElementById('notification-content');
                const contentMobile = document.getElementById('notification-content-mobile');

                const updateContent = (targetContent) => {
                    if (!targetContent) return;

                    if (data.notifications.length === 0) {
                        targetContent.innerHTML = `
          <div class="p-4 text-center text-muted-foreground">
            <span class="icon-bell w-8 h-8 mx-auto mb-2 opacity-50 block"></span>
            <p>Tidak ada notifikasi</p>
          </div>
        `;
                    } else {
                        let html = '';
                        data.notifications.forEach(notification => {
                            const statusColor = notification.status === 'success' ? 'text-green-600' :
                                'text-red-600';
                            const statusIcon = notification.status === 'success' ? 'check-circle' :
                                'x-circle';

                            const iconClass = statusIcon === 'check-circle' ? 'icon-check' : 'icon-x';
                            // Format notification content based on command type
                            let notificationContent = '';
                            if (notification.command === 'Auto Check-out Process' && notification
                                .member_name && notification.checkout_time) {
                                notificationContent = `
                                <p class="text-sm font-medium text-card-foreground">${notification.command}</p>
                                <p class="text-xs ${statusColor} capitalize">${notification.status}</p>
                                <p class="text-xs text-muted-foreground mt-1">${notification.member_name} berhasil di check-out otomatis ${notification.checkout_time}</p>
                                <p class="text-xs text-muted-foreground mt-1">${notification.date} ${notification.time}</p>
                            `;
                            } else if (notification.command === 'Membership Expiration Check' &&
                                notification.member_name && !notification.checkout_time) {
                                notificationContent = `
                                <p class="text-sm font-medium text-card-foreground">${notification.command}</p>
                                <p class="text-xs ${statusColor} capitalize">${notification.status}</p>
                                <p class="text-xs text-muted-foreground mt-1">${notification.member_name} keanggotaan berhasil diubah menjadi inactive dikarenakan expired</p>
                                <p class="text-xs text-muted-foreground mt-1">${notification.date} ${notification.time}</p>
                            `;
                            } else {
                                notificationContent = `
                                <p class="text-sm font-medium text-card-foreground">${notification.command}</p>
                                <p class="text-xs ${statusColor} capitalize">${notification.status}</p>
                                ${notification.message ? `<p class="text-xs text-muted-foreground mt-1">${notification.message}</p>` : ''}
                                <p class="text-xs text-muted-foreground mt-1">${notification.date} ${notification.time}</p>
                            `;
                            }

                            html += `
            <div class="p-3 border-b border-border last:border-b-0 hover:bg-muted/50">
              <div class="flex items-start space-x-3">
                <div class="flex-shrink-0 mt-0.5">
                  <span class="${iconClass} w-4 h-4 ${statusColor}"></span>
                </div>
                <div class="flex-1 min-w-0">
                  ${notificationContent}
                </div>
              </div>
            </div>
          `;
                        });
                        targetContent.innerHTML = html;
                    }
                };

                // Update both desktop and mobile content
                updateContent(content);
                updateContent(contentMobile);

                // Update bell icon based on notification status
                updateBellIcon(data.has_new);
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
                const errorHtml = `
        <div class="p-4 text-center text-red-600">
          <p>Gagal memuat notifikasi</p>
        </div>
      `;
                if (content) content.innerHTML = errorHtml;
                if (contentMobile) contentMobile.innerHTML = errorHtml;
            });
    }

    function updateBellIcon(hasNew) {
        // Update desktop bell icon
        const bellIcon = document.getElementById('bell-icon');
        const bellSvg = document.getElementById('bell-svg');

        // Update mobile bell icon
        const bellIconMobile = document.getElementById('bell-icon-mobile');
        const bellSvgMobile = document.getElementById('bell-svg-mobile');

        const updateIcon = (icon, svg) => {
            if (!icon || !svg) return;

            if (hasNew) {
                // Add notification state classes - orange color with pulse animation
                icon.classList.add('text-orange-500', 'animate-pulse');
                icon.classList.remove('text-muted-foreground');

                // Add visual indicator that there are new notifications
                svg.classList.add('text-orange-500');
                svg.classList.remove('text-muted-foreground');
            } else {
                // Remove notification state classes - back to normal muted color
                icon.classList.remove('text-orange-500', 'animate-pulse');
                icon.classList.add('text-muted-foreground');

                svg.classList.remove('text-orange-500');
                svg.classList.add('text-muted-foreground');
            }
        };

        updateIcon(bellIcon, bellSvg);
        updateIcon(bellIconMobile, bellSvgMobile);
    }

    function startNotificationPolling() {
        // Load notifications immediately
        loadNotifications();

        // Poll every 2 minutes
        notificationInterval = setInterval(() => {
            fetch('{{ route('notifications.scheduled-commands') }}')
                .then(response => response.json())
                .then(data => {
                    updateBellIcon(data.has_new);
                })
                .catch(error => {
                    console.error('Error polling notifications:', error);
                });
        }, 120000); // 2 minutes
    }

    // Close popup when clicking outside
    document.addEventListener('click', function(event) {
        const popup = document.getElementById('notification-popup');
        const popupMobile = document.getElementById('notification-popup-mobile');
        const button = document.getElementById('notification-btn');
        const buttonMobile = document.getElementById('notification-btn-mobile');

        // Check desktop popup
        if (popup && !popup.classList.contains('hidden') &&
            !popup.contains(event.target) &&
            !button.contains(event.target)) {
            popup.classList.add('hidden');
        }

        // Check mobile popup
        if (popupMobile && !popupMobile.classList.contains('hidden') &&
            !popupMobile.contains(event.target) &&
            !buttonMobile.contains(event.target)) {
            popupMobile.classList.add('hidden');
        }
    });

    // Mobile menu button functionality
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const sidebar = document.getElementById('sidebar');

        if (mobileMenuBtn && sidebar) {
            mobileMenuBtn.addEventListener('click', function() {
                // Toggle sidebar by clicking the sidebar button
                const sidebarBtn = document.getElementById('btn');
                if (sidebarBtn) {
                    sidebarBtn.click();
                }
            });
        }

        startNotificationPolling();
    });

    // Cleanup interval when page unloads
    window.addEventListener('beforeunload', function() {
        if (notificationInterval) {
            clearInterval(notificationInterval);
        }
    });
</script>
