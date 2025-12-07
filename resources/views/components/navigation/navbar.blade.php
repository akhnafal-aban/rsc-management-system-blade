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
                <img src="{{ Vite::asset('resources/images/rsc_logo.png') }}" alt="RSC Logo"
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
                        <!-- Bell icon -->
                        <div id="bell-icon" class="transition-all duration-300">
                            <x-ui.icon name="bell" class="w-5 h-5 transition-all duration-300" id="bell-svg" />
                        </div>
                        <div id="notification-badge" class="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full hidden"></div>
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
                    title="Notifikasi" onclick="toggleMobileNotificationPopup()">
                    <div id="bell-icon-mobile" class="transition-all duration-300">
                        <x-ui.icon name="bell" class="w-5 h-5 transition-all duration-300" id="bell-svg-mobile" />
                    </div>
                    <div id="notification-badge-mobile" class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full hidden"></div>
                </button>

                <!-- Mobile Notification Popup (keberadaan markup tetap, tetapi logic di-handle global) -->
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
    // Global guard mencegah inisialisasi ganda
    if (!window.__notifInit) {
        window.__notifInit = true;

        const NOTIF_ROUTE = '{{ route('notifications.scheduled-commands') }}';
        const MARK_READ_ROUTE = '{{ route('notifications.mark-read') }}';

        function fetchNotifications() {
            return fetch(NOTIF_ROUTE, { credentials: 'same-origin' })
                .then(r => {
                    if (!r.ok) throw new Error('Network response was not ok');
                    return r.json();
                });
        }

        function renderNotificationsTo(targetEl, data) {
            if (!targetEl) return;
            if (!Array.isArray(data.notifications) || data.notifications.length === 0) {
                targetEl.innerHTML = `
                    <div class="p-4 text-center text-muted-foreground">
                        <span class="icon-bell w-8 h-8 mx-auto mb-2 opacity-50 block"></span>
                        <p>Tidak ada notifikasi</p>
                    </div>
                `;
                return;
            }

            let html = '';
            data.notifications.forEach(notification => {
                const statusColor = notification.status === 'success' ? 'text-green-600' : 'text-red-600';
                const statusIcon = notification.status === 'success' ? 'check-circle' : 'x-circle';
                let notificationContent = '';
                if (notification.command === 'Auto Check-out Process' && notification.member_name && notification.checkout_time) {
                    notificationContent = `
                        <p class="text-sm font-medium text-card-foreground">${notification.command}</p>
                        <p class="text-xs ${statusColor} capitalize">${notification.status}</p>
                        <p class="text-xs text-muted-foreground mt-1">${notification.member_name} berhasil di check-out otomatis ${notification.checkout_time}</p>
                        <p class="text-xs text-muted-foreground mt-1">${notification.date} ${notification.time}</p>
                    `;
                } else if (notification.command === 'Membership Expiration Check' && notification.member_name && !notification.checkout_time) {
                    notificationContent = `
                        <p class="text-sm font-medium text-card-foreground">${notification.command}</p>
                        <p class="text-xs ${statusColor} capitalize">${notification.status}</p>
                        <p class="text-xs text-muted-foreground mt-1">${notification.member_name} keanggotaan menjadi inactive dikarenakan expired</p>
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
                                <span class="${statusIcon === 'check-circle' ? 'icon-check' : 'icon-x'} w-4 h-4 ${statusColor}"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                ${notificationContent}
                            </div>
                        </div>
                    </div>
                `;
            });

            targetEl.innerHTML = html;
        }

        function updateDesktopBellIcon(hasNew) {
            const bellIcon = document.getElementById('bell-icon');
            const bellSvg = document.getElementById('bell-svg');
            const notificationBadge = document.getElementById('notification-badge');

            if (!bellIcon) return;

            if (hasNew) {
                bellIcon.classList.add('text-orange-500', 'animate-pulse');
                bellIcon.classList.remove('text-muted-foreground');
                if (bellSvg) {
                    bellSvg.classList.add('text-orange-500');
                    bellSvg.classList.remove('text-muted-foreground');
                }
                if (notificationBadge) notificationBadge.classList.remove('hidden');
            } else {
                bellIcon.classList.remove('text-orange-500', 'animate-pulse');
                bellIcon.classList.add('text-muted-foreground');
                if (bellSvg) {
                    bellSvg.classList.remove('text-orange-500');
                    bellSvg.classList.add('text-muted-foreground');
                }
                if (notificationBadge) notificationBadge.classList.add('hidden');
            }
        }

        function updateMobileBellIcon(hasNew) {
            const bellIcon = document.getElementById('bell-icon-mobile');
            const bellSvg = document.getElementById('bell-svg-mobile');
            const notificationBadge = document.getElementById('notification-badge-mobile');

            if (!bellIcon) return;

            if (hasNew) {
                bellIcon.classList.add('text-orange-500', 'animate-pulse');
                bellIcon.classList.remove('text-muted-foreground');
                if (bellSvg) {
                    bellSvg.classList.add('text-orange-500');
                    bellSvg.classList.remove('text-muted-foreground');
                }
                if (notificationBadge) notificationBadge.classList.remove('hidden');
            } else {
                bellIcon.classList.remove('text-orange-500', 'animate-pulse');
                bellIcon.classList.add('text-muted-foreground');
                if (bellSvg) {
                    bellSvg.classList.remove('text-orange-500');
                    bellSvg.classList.add('text-muted-foreground');
                }
                if (notificationBadge) notificationBadge.classList.add('hidden');
            }
        }

        function populateNotifications(data) {
            const content = document.getElementById('notification-content');
            const contentMobile = document.getElementById('notification-content-mobile');
            renderNotificationsTo(content, data);
            renderNotificationsTo(contentMobile, data);
            updateDesktopBellIcon(data.has_new);
            updateMobileBellIcon(data.has_new);
        }

        function markNotificationsAsRead() {
            fetch(MARK_READ_ROUTE, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                credentials: 'same-origin'
            }).then(() => {
                updateDesktopBellIcon(false);
                updateMobileBellIcon(false);
            }).catch(err => console.error('Error marking notifications as read:', err));
        }

        function toggleNotificationPopup() {
            const popup = document.getElementById('notification-popup');
            const activePopup = popup;
            if (!activePopup) return;
            const isHidden = activePopup.classList.contains('hidden');
            if (isHidden) {
                fetchNotifications().then(populateNotifications).catch(err => {
                    const content = document.getElementById('notification-content');
                    if (content) content.innerHTML = `<div class="p-4 text-center text-red-600"><p>Gagal memuat notifikasi</p></div>`;
                });
                activePopup.classList.remove('hidden');
                markNotificationsAsRead();
            } else {
                activePopup.classList.add('hidden');
            }
        }

        function toggleMobileNotificationPopup() {
            const popupMobile = document.getElementById('notification-popup-mobile');
            if (!popupMobile) return;
            const isHidden = popupMobile.classList.contains('hidden');
            if (isHidden) {
                fetchNotifications().then(populateNotifications).catch(err => {
                    const contentMobile = document.getElementById('notification-content-mobile');
                    if (contentMobile) contentMobile.innerHTML = `<div class="p-4 text-center text-red-600"><p>Gagal memuat notifikasi</p></div>`;
                });
                popupMobile.classList.remove('hidden');
                markNotificationsAsRead();
            } else {
                popupMobile.classList.add('hidden');
            }
        }

        // Expose necessary functions to global scope so mobile partial can call them
        window.toggleNotificationPopup = toggleNotificationPopup;
        window.toggleMobileNotificationPopup = toggleMobileNotificationPopup;
        window.markNotificationsAsRead = markNotificationsAsRead;
        window.loadNotifications = fetchNotifications;

        // Close popup when clicking outside (single global handler)
        document.addEventListener('click', function(event) {
            const popup = document.getElementById('notification-popup');
            const popupMobile = document.getElementById('notification-popup-mobile');
            const button = document.getElementById('notification-btn');
            const buttonMobile = document.getElementById('notification-btn-mobile');

            if (popup && !popup.classList.contains('hidden') && !popup.contains(event.target) && button && !button.contains(event.target)) {
                popup.classList.add('hidden');
            }

            if (popupMobile && !popupMobile.classList.contains('hidden') && !popupMobile.contains(event.target) && buttonMobile && !buttonMobile.contains(event.target)) {
                popupMobile.classList.add('hidden');
            }
        });
    } // end guard
</script>
