<nav class="bg-card border-b border-border px-6 py-3 shadow-sm">
    <div class="flex items-center justify-between">
        <!-- Logo + Brand -->
        <div class="flex items-center space-x-3">
            <div class="w-9 h-9 bg-primary rounded-xl flex items-center justify-center shadow-sm">
                <span class="text-primary-foreground font-semibold text-base tracking-wide">RS</span>
            </div>
            <div>
                <h1 class="text-lg font-semibold text-card-foreground leading-tight">Really Sports Center</h1>
                <p class="text-xs text-muted-foreground tracking-wide">Management System</p>
            </div>
        </div>

        <!-- Right section -->
        <div class="flex items-center space-x-5">
            <!-- Icons -->
            <div class="flex items-center space-x-2">
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
                        class="absolute right-0 top-12 w-80 bg-card border border-border rounded-lg shadow-lg z-50 hidden">
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

            <!-- User Info -->
            @if (isset($user))
                <div class="flex items-center space-x-3 pl-4 border-l border-border">
                    <div class="text-right leading-tight">
                        <p class="text-sm font-medium text-card-foreground">{{ $user['name'] ?? '' }}</p>
                        @if (isset($user['role']) && $user['role']->isAdmin())
                            <p class="text-xs text-muted-foreground">Admin</p>
                        @elseif (isset($user['role']) && $user['role']->isStaff())
                            <p class="text-xs text-muted-foreground">Staff</p>
                        @endif
                    </div>
                    <div class="relative">
                        <div
                            class="w-9 h-9 bg-secondary/20 rounded-full flex items-center justify-center ring-1 ring-border">
                            <x-ui.icon name="user" class="w-4 h-4 text-secondary" />
                        </div>
                    </div>
                    <button onclick="handleLogout()"
                        class="p-2 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-lg transition-colors"
                        title="Keluar">
                        <x-ui.icon name="log-out" class="w-4 h-4" />
                    </button>
                </div>
            @endif
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
        const isHidden = popup.classList.contains('hidden');

        if (isHidden) {
            loadNotifications();
            popup.classList.remove('hidden');
            // Mark notifications as read when popup is opened
            markNotificationsAsRead();
        } else {
            popup.classList.add('hidden');
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

                if (data.notifications.length === 0) {
                    content.innerHTML = `
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
                        const statusIcon = notification.status === 'success' ? 'check-circle' : 'x-circle';

                        const iconClass = statusIcon === 'check-circle' ? 'icon-check' : 'icon-x';
                        // Format notification content based on command type
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
                    content.innerHTML = html;
                }

                // Update bell icon based on notification status
                updateBellIcon(data.has_new);
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
                document.getElementById('notification-content').innerHTML = `
        <div class="p-4 text-center text-red-600">
          <p>Gagal memuat notifikasi</p>
        </div>
      `;
            });
    }

    function updateBellIcon(hasNew) {
        const bellIcon = document.getElementById('bell-icon');
        const bellSvg = document.getElementById('bell-svg');
        if (!bellIcon || !bellSvg) return;

        if (hasNew) {
            // Add notification state classes - orange color with pulse animation
            bellIcon.classList.add('text-orange-500', 'animate-pulse');
            bellIcon.classList.remove('text-muted-foreground');

            // Add visual indicator that there are new notifications
            bellSvg.classList.add('text-orange-500');
            bellSvg.classList.remove('text-muted-foreground');
        } else {
            // Remove notification state classes - back to normal muted color
            bellIcon.classList.remove('text-orange-500', 'animate-pulse');
            bellIcon.classList.add('text-muted-foreground');

            bellSvg.classList.remove('text-orange-500');
            bellSvg.classList.add('text-muted-foreground');
        }
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
        const button = document.getElementById('notification-btn');

        if (!popup.classList.contains('hidden') &&
            !popup.contains(event.target) &&
            !button.contains(event.target)) {
            popup.classList.add('hidden');
        }
    });

    // Start polling when page loads
    document.addEventListener('DOMContentLoaded', function() {
        startNotificationPolling();
    });

    // Cleanup interval when page unloads
    window.addEventListener('beforeunload', function() {
        if (notificationInterval) {
            clearInterval(notificationInterval);
        }
    });
</script>
