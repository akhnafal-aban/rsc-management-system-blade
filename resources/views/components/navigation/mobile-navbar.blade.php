<!-- Mobile Navbar markup (unchanged except JS) -->
<nav class="block sm:hidden bg-card border-b border-border px-4 py-3 shadow-sm fixed top-0 left-0 right-0 z-[1000]">
    <div class="flex items-center justify-between">
        <button id="mobile-menu-toggle"
            class="p-3 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-lg transition-colors touch-manipulation">
            <x-ui.icon name="menu" class="w-6 h-6" />
        </button>

        <div class="flex items-center space-x-1">
            <div class="relative">
                <button id="mobile-notification-btn"
                    class="p-3 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-lg transition-colors relative touch-manipulation"
                    title="Notifikasi" onclick="toggleMobileNotificationPopup()">
                    <div id="mobile-bell-icon" class="transition-all duration-300">
                        <x-ui.icon name="bell" class="w-5 h-5 transition-all duration-300" id="mobile-bell-svg" />
                    </div>
                    <div id="mobile-notification-badge"
                        class="absolute -top-1 -right-1 w-2 h-2 bg-red-500 mt-1 mr-1 rounded-full hidden"></div>
                </button>

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

            <button
                class="p-3 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-lg transition-colors touch-manipulation"
                title="Pengaturan" onclick="handleSettings()">
                <x-ui.icon name="settings" class="w-5 h-5" />
            </button>

            <button onclick="handleLogout()"
                class="p-3 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-lg transition-colors touch-manipulation"
                title="Keluar">
                <x-ui.icon name="log-out" class="w-5 h-5" />
            </button>
        </div>
    </div>
</nav>

<!-- Mobile Menu overlay/panel ... (keperluan navigasi tetap sama) -->
<!-- ... markup unchanged ... -->

<script>
    // Mobile menu functionality unchanged
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
        const mobileMenuPanel = document.getElementById('mobile-menu-panel');
        const mobileMenuOverlay = document.getElementById('mobile-menu-overlay');
        const mobileMenuClose = document.getElementById('mobile-menu-close');
        const mobileNotificationBtn = document.getElementById('mobile-notification-btn');
        const mobileNotificationPopup = document.getElementById('mobile-notification-popup');

        function openMobileMenu() {
            if (mobileMenuPanel) mobileMenuPanel.classList.remove('-translate-x-full');
            if (mobileMenuOverlay) mobileMenuOverlay.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeMobileMenu() {
            if (mobileMenuPanel) mobileMenuPanel.classList.add('-translate-x-full');
            if (mobileMenuOverlay) mobileMenuOverlay.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        if (mobileMenuToggle) {
            mobileMenuToggle.addEventListener('click', openMobileMenu);
        }
        if (mobileMenuClose) {
            mobileMenuClose.addEventListener('click', closeMobileMenu);
        }
        if (mobileMenuOverlay) {
            mobileMenuOverlay.addEventListener('click', closeMobileMenu);
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMobileMenu();
            }
        });

        // Touch swipe logic remains unchanged
        let startX = 0;
        let startY = 0;
        if (mobileMenuPanel) {
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
                if (Math.abs(diffX) > Math.abs(diffY) && diffX > 50) {
                    closeMobileMenu();
                    startX = 0;
                    startY = 0;
                }
            });

            mobileMenuPanel.addEventListener('touchend', function() {
                startX = 0;
                startY = 0;
            });
        }

        // Mobile notification button uses global functions defined in navbar script.
        if (mobileNotificationBtn) {
            // keep click handler in markup as onclick="toggleMobileNotificationPopup()"
            // Add an extra defensive click binding in case onclick was removed or fails.
            mobileNotificationBtn.addEventListener('click', function() {
                if (typeof window.toggleMobileNotificationPopup === 'function') {
                    window.toggleMobileNotificationPopup();
                }
            });
        }

        // Close mobile notification popup when clicking outside - keep local defensive check
        document.addEventListener('click', function(event) {
            if (mobileNotificationPopup && !mobileNotificationPopup.classList.contains('hidden') &&
                !mobileNotificationPopup.contains(event.target) &&
                mobileNotificationBtn && !mobileNotificationBtn.contains(event.target)) {
                mobileNotificationPopup.classList.add('hidden');
            }
        });

        // Note: mobile does not perform its own fetch or setInterval anymore.
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