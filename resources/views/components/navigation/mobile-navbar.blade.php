<!-- Mobile Navbar -->
<nav class="block sm:hidden bg-card border-b border-border px-4 py-3 shadow-sm fixed top-0 left-0 right-0 z-[1000]">
    <div class="flex items-center justify-between">
        <!-- Mobile Menu Button -->
        <button id="mobile-menu-toggle" 
                class="p-3 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-lg transition-colors touch-manipulation">
            <x-ui.icon name="menu" class="w-6 h-6" />
        </button>

        <!-- Right Actions -->
        <div class="flex items-center space-x-1">
            <!-- Notification Button -->
            <div class="relative">
                <button id="mobile-notification-btn"
                    class="p-3 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-lg transition-colors relative touch-manipulation"
                    title="Notifikasi">
                    <div id="mobile-bell-icon" class="transition-all duration-300">
                        <x-ui.icon name="bell" class="w-5 h-5 transition-all duration-300" id="mobile-bell-svg" />
                    </div>
                </button>

                <!-- Mobile Notification Popup -->
                <div id="mobile-notification-popup"
                    class="fixed inset-x-4 top-16 bg-card border border-border rounded-lg shadow-lg z-50 hidden">
                    <div class="p-4 border-b border-border">
                        <h3 class="text-sm font-semibold text-card-foreground">Notifikasi Sistem</h3>
                    </div>
                    <div id="mobile-notification-content" class="max-h-80 overflow-y-auto">
                        <div class="p-4 text-center text-muted-foreground">
                            <p>Memuat notifikasi...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Button -->
            <button class="p-3 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-lg transition-colors touch-manipulation"
                title="Pengaturan">
                <x-ui.icon name="settings" class="w-5 h-5" />
            </button>

            <!-- Logout Button -->
            <button onclick="handleLogout()"
                class="p-3 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-lg transition-colors touch-manipulation"
                title="Keluar">
                <x-ui.icon name="log-out" class="w-5 h-5" />
            </button>
        </div>
    </div>
</nav>

<!-- Mobile Menu Overlay -->
<div id="mobile-menu-overlay" class="fixed inset-0 bg-black/50 z-[999] hidden sm:hidden"></div>

<!-- Mobile Menu Slide Panel -->
<div id="mobile-menu-panel" class="fixed left-0 top-0 h-full w-80 bg-background-muted z-[1000] transform -translate-x-full transition-transform duration-300 ease-in-out sm:hidden">
    <div class="h-16 flex items-center justify-between px-4 border-b border-border">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center shadow-sm overflow-hidden">
                <img src="{{ Vite::asset('resources/images/rsc_logo.png') }}" alt="RSC Logo" class="w-full h-full object-cover rounded-xl">
            </div>
            <h2 class="text-lg font-semibold text-foreground">Navigation</h2>
        </div>
        <button id="mobile-menu-close" class="p-3 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-lg transition-colors touch-manipulation">
            <x-ui.icon name="x" class="w-6 h-6" />
        </button>
    </div>

    <!-- Navigation Menu -->
    <div class="flex-1 overflow-y-auto px-4 py-4">
        <nav class="space-y-2">
            @php($menuItems = [
                ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home', 'route' => 'dashboard'], 
                ['id' => 'attendance', 'label' => 'Absensi', 'icon' => 'user-check', 'route' => 'attendance.index'], 
                ['id' => 'members', 'label' => 'Members', 'icon' => 'users', 'route' => 'member.index']
            ])

            @foreach ($menuItems as $item)
                @php($isActive = request()->routeIs($item['route']) || (request()->routeIs('dashboard') && $item['id'] === 'dashboard'))
                <a href="{{ $item['route'] === '#' ? '#' : route($item['route']) }}"
                    class="flex items-center space-x-4 px-4 py-4 rounded-lg transition-colors touch-manipulation {{ $isActive ? 'bg-primary text-primary-foreground' : 'text-foreground hover:bg-accent hover:text-accent-foreground' }}"
                    onclick="closeMobileMenu()">
                    <x-ui.icon name="{{ $item['icon'] }}" class="w-6 h-6" />
                    <span class="font-medium text-base">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    <!-- User Profile Section -->
    @if (isset($user))
        <div class="border-t border-border p-4">
            <div class="flex items-center space-x-3 p-3 rounded-lg bg-muted/50">
                <div class="w-10 h-10 bg-primary/20 rounded-full flex items-center justify-center">
                    <x-ui.icon name="user" class="w-5 h-5 text-primary" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-foreground truncate">{{ $user['name'] ?? '' }}</p>
                    @if (isset($user['role']) && $user['role']->isAdmin())
                        <p class="text-xs text-muted-foreground">Admin</p>
                    @elseif (isset($user['role']) && $user['role']->isStaff())
                        <p class="text-xs text-muted-foreground">Staff</p>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    // Mobile menu functionality
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
        const mobileMenuPanel = document.getElementById('mobile-menu-panel');
        const mobileMenuOverlay = document.getElementById('mobile-menu-overlay');
        const mobileMenuClose = document.getElementById('mobile-menu-close');
        const mobileNotificationBtn = document.getElementById('mobile-notification-btn');
        const mobileNotificationPopup = document.getElementById('mobile-notification-popup');

        function openMobileMenu() {
            mobileMenuPanel.classList.remove('-translate-x-full');
            mobileMenuOverlay.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeMobileMenu() {
            mobileMenuPanel.classList.add('-translate-x-full');
            mobileMenuOverlay.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        // Toggle mobile menu
        if (mobileMenuToggle) {
            mobileMenuToggle.addEventListener('click', openMobileMenu);
        }

        // Close mobile menu
        if (mobileMenuClose) {
            mobileMenuClose.addEventListener('click', closeMobileMenu);
        }

        // Close mobile menu when clicking overlay
        if (mobileMenuOverlay) {
            mobileMenuOverlay.addEventListener('click', closeMobileMenu);
        }

        // Close mobile menu on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMobileMenu();
            }
        });

        // Add swipe gesture support for closing menu
        let startX = 0;
        let startY = 0;

        mobileMenuPanel.addEventListener('touchstart', function(e) {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
        });

        mobileMenuPanel.addEventListener('touchmove', function(e) {
            if (!startX || !startY) return;

            const currentX = e.touches[0].clientX;
            const currentY = e.touches[0].clientY;
            const diffX = startX - currentX;
            const diffY = startY - currentY;

            // Check if it's a horizontal swipe (not vertical scroll)
            if (Math.abs(diffX) > Math.abs(diffY) && diffX > 50) {
                // Swipe left detected - close menu
                closeMobileMenu();
                startX = 0;
                startY = 0;
            }
        });

        mobileMenuPanel.addEventListener('touchend', function() {
            startX = 0;
            startY = 0;
        });

        // Mobile notification functionality
        function toggleMobileNotificationPopup() {
            const isHidden = mobileNotificationPopup.classList.contains('hidden');

            if (isHidden) {
                loadMobileNotifications();
                mobileNotificationPopup.classList.remove('hidden');
                markNotificationsAsRead();
            } else {
                mobileNotificationPopup.classList.add('hidden');
            }
        }

        if (mobileNotificationBtn) {
            mobileNotificationBtn.addEventListener('click', toggleMobileNotificationPopup);
        }

        // Close mobile notification popup when clicking outside
        document.addEventListener('click', function(event) {
            if (!mobileNotificationPopup.classList.contains('hidden') &&
                !mobileNotificationPopup.contains(event.target) &&
                !mobileNotificationBtn.contains(event.target)) {
                mobileNotificationPopup.classList.add('hidden');
            }
        });

        function loadMobileNotifications() {
            fetch('{{ route('notifications.scheduled-commands') }}')
                .then(response => response.json())
                .then(data => {
                    const content = document.getElementById('mobile-notification-content');

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
                            const statusColor = notification.status === 'success' ? 'text-green-600' : 'text-red-600';
                            const statusIcon = notification.status === 'success' ? 'check-circle' : 'x-circle';
                            const iconClass = statusIcon === 'check-circle' ? 'icon-check' : 'icon-x';

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

                    // Update mobile bell icon based on notification status
                    updateMobileBellIcon(data.has_new);
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                    document.getElementById('mobile-notification-content').innerHTML = `
                        <div class="p-4 text-center text-red-600">
                            <p>Gagal memuat notifikasi</p>
                        </div>
                    `;
                });
        }

        function updateMobileBellIcon(hasNew) {
            const bellIcon = document.getElementById('mobile-bell-icon');
            const bellSvg = document.getElementById('mobile-bell-svg');

            if (!bellIcon || !bellSvg) return;

            if (hasNew) {
                bellIcon.classList.add('text-orange-500', 'animate-pulse');
                bellIcon.classList.remove('text-muted-foreground');
                bellSvg.classList.add('text-orange-500');
                bellSvg.classList.remove('text-muted-foreground');
            } else {
                bellIcon.classList.remove('text-orange-500', 'animate-pulse');
                bellIcon.classList.add('text-muted-foreground');
                bellSvg.classList.remove('text-orange-500');
                bellSvg.classList.add('text-muted-foreground');
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
                updateMobileBellIcon(false);
            })
            .catch(error => {
                console.error('Error marking notifications as read:', error);
            });
        }

        // Load notifications on page load
        loadMobileNotifications();

        // Poll for notifications every 2 minutes
        setInterval(() => {
            fetch('{{ route('notifications.scheduled-commands') }}')
                .then(response => response.json())
                .then(data => {
                    updateMobileBellIcon(data.has_new);
                })
                .catch(error => {
                    console.error('Error polling notifications:', error);
                });
        }, 120000);
    });

    function handleLogout() {
        showConfirm(
            'Apakah Anda yakin ingin keluar dari sistem?',
            function() {
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
            },
            'Konfirmasi Logout',
            'warning'
        );
    }
</script>
