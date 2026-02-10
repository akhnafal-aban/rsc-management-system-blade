<style>
    /* Custom styles for JavaScript functionality and complex animations */
    .sidebar.open {
        width: 250px;
    }

    .sidebar.open .logo_name {
        opacity: 1;
    }

    .sidebar.open .links_name {
        opacity: 1;
        pointer-events: auto;
    }

    .sidebar.open .profile-name,
    .sidebar.open .profile-role {
        opacity: 1;
        pointer-events: auto;
    }

    .sidebar.open .tooltip {
        display: none;
    }

    /* Profile dropdown styles */
    .profile-dropdown {
        z-index: 1001;
    }

    .sidebar:not(.open) .profile-dropdown {
        left: 0;
        width: 78px;
    }

    .sidebar.open .profile-dropdown {
        left: 0;
        width: 250px;
    }

    .sidebar:not(.open) .profile-dropdown .links_name {
        display: none;
    }

    .sidebar.open .profile-dropdown .links_name {
        opacity: 1;
        pointer-events: auto;
    }

    .sidebar li:hover .tooltip {
        opacity: 1;
        pointer-events: auto;
        transition: all 0.4s ease;
        top: 50%;
        transform: translateY(-50%);
    }

    .main-content {
        margin-left: 78px;
        transition: all 0.5s ease;
        min-height: 100vh;
    }

    .sidebar.open+.main-content {
        margin-left: 250px;
    }

    /* Responsive adjustments */
    @media (max-width: 640px) {
        .main-content {
            margin-left: 0 !important;
        }

        .sidebar.open+.main-content {
            margin-left: 0 !important;
        }
    }
</style>

<!-- SIDEBAR -->
<div id="sidebar"
    class="hidden sm:block fixed left-0 top-0 h-full w-[78px] lg:w-[78px] bg-background-muted px-[14px] py-[6px] z-[1000] transition-all duration-500 ease-in-out border-r border-border sidebar">

    <!-- Header -->
    <div class="h-[60px] flex items-center relative border-b border-border pl-4 pr-2">
        <div
            class="text-foreground text-[15px] font-semibold opacity-0 transition-all duration-500 ease-in-out whitespace-nowrap logo_name">
            Navigation
        </div>

        <button id="btn"
            class="absolute top-1/2 right-1.5 -translate-y-1/2 p-2 rounded-lg text-foreground hover:bg-accent hover:text-accent-foreground transition-all duration-500 ease-in-out">
            <x-ui.icon name="menu" class="w-3 h-3" />
        </button>
    </div>

    <!-- Navigation List -->
    <ul class="h-[calc(100%-150px)] overflow-y-auto overflow-x-hidden nav-list pb-[60px]">
        @php
            $authUser = auth()->user();
            $isAdmin = $authUser && $authUser->role && $authUser->role->isAdmin();
            $isStaff = $authUser && $authUser->role && $authUser->role->isStaff();
            $menuItems = [
                ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home', 'route' => 'dashboard'],
                ['id' => 'attendance', 'label' => 'Absensi', 'icon' => 'user-check', 'route' => 'attendance.index'],
                ['id' => 'members', 'label' => 'Members', 'icon' => 'users', 'route' => 'member.index'],
                ['id' => 'nonmember', 'label' => 'Non-Member', 'icon' => 'user-plus', 'route' => 'non-member-visit.index'],
            ];
            if ($isAdmin) {
                $menuItems = array_merge($menuItems, [
                    ['id' => 'payment', 'label' => 'Pembayaran', 'icon' => 'credit-card', 'route' => 'admin.payment.index'],
                    ['id' => 'staff-schedule', 'label' => 'Jadwal Staf', 'icon' => 'calendar', 'route' => 'admin.staff-schedule.index'],
                    ['id' => 'business-report', 'label' => 'Laporan', 'icon' => 'bar-chart-3', 'route' => 'admin.business-report.index'],
                    ['id' => 'settings', 'label' => 'Pengaturan', 'icon' => 'settings', 'route' => 'admin.settings.index'],
                ]);
            }
            if ($isStaff) {
                $menuItems[] = ['id' => 'my-schedule', 'label' => 'Jadwal Saya', 'icon' => 'calendar-clock', 'route' => 'staff.shift.schedule'];
            }
        @endphp

        @foreach ($menuItems as $item)
            @php($isActive = request()->routeIs($item['route']))
            <li class="relative my-2 list-none">
                <a href="{{ $item['route'] === '#' ? '#' : route($item['route']) }}"
                    class="flex h-[50px] w-full rounded-xl items-center no-underline transition-all duration-[400ms] ease-in-out bg-transparent text-foreground px-3 hover:bg-destructive hover:text-destructive-foreground {{ $isActive ? 'bg-destructive text-destructive-foreground' : '' }}">
                    <x-ui.icon name="{{ $item['icon'] }}"
                        class="w-3 h-3 leading-[50px] text-lg min-w-[50px] max-w-[50px] text-center rounded-xl transition-all duration-[400ms] ease-in-out flex items-center justify-center shrink-0 -ml-3 sidebar-icon" />
                    <span
                        class="text-foreground text-[15px] font-normal whitespace-nowrap opacity-0 pointer-events-none transition-[400ms] ml-3 links_name">{{ $item['label'] }}</span>
                </a>
                <span
                    class="absolute -top-5 left-[calc(100%+15px)] bg-card text-card-foreground shadow-[0_5px_10px_rgba(0,0,0,0.3)] px-3 py-[6px] rounded text-[15px] font-normal opacity-0 whitespace-nowrap pointer-events-none transition-none border border-border tooltip">{{ $item['label'] }}</span>
            </li>
        @endforeach
    </ul>

    <!-- User Profile Section (Pinned Bottom) -->
    @if (isset($user))
        <div class="absolute bottom-4 left-0 w-full px-[14px]">
            <!-- Profile Button -->
            <div class="bg-card/50 rounded-xl flex h-[50px] w-full items-center no-underline transition-all duration-[400ms] ease-in-out text-foreground px-3 cursor-pointer hover:bg-primary/30 hover:text-destructive-foreground profile-details-btn"
                onclick="toggleProfileDropdown()">

                <!-- Profile Icon -->
                <div
                    class="w-8 h-8 bg-yellow-400/20 rounded-full flex items-center justify-center shrink-0 -ml-1 sidebar-icon">
                    <x-ui.icon name="user" class="w-4 h-4 text-yellow-600" />
                </div>

                <!-- Profile Info -->
                <div class="flex flex-col ml-3 min-w-0 flex-1 profile-info">
                    <span
                        class="text-foreground text-[15px] font-medium whitespace-nowrap opacity-0 pointer-events-none transition-[400ms] overflow-hidden text-ellipsis links_name profile-name">
                        {{ $user['name'] ?? '' }}
                    </span>
                    @if (isset($user['role']) && $user['role']->isAdmin())
                        <span
                            class="text-muted-foreground text-xs font-normal whitespace-nowrap opacity-0 pointer-events-none transition-[400ms] overflow-hidden text-ellipsis links_name profile-role">Admin</span>
                    @elseif (isset($user['role']) && $user['role']->isStaff())
                        <span
                            class="text-muted-foreground text-xs font-normal whitespace-nowrap opacity-0 pointer-events-none transition-[400ms] overflow-hidden text-ellipsis links_name profile-role">Staff</span>
                    @endif
                </div>
            </div>

            <!-- Profile Dropdown -->
            <div id="profile-dropdown"
                class="absolute bottom-[60px] left-[14px] w-[calc(100%-28px)] bg-card border border-border rounded-lg shadow-lg opacity-0 pointer-events-none transition-all duration-300 ease-in-out transform scale-95 profile-dropdown">
                <div class="py-1">
                    <button
                        class="flex w-full items-center justify-center sm:justify-start px-3 py-2 text-sm text-foreground hover:bg-muted/50 transition-colors"
                        onclick="handleSettings()" aria-label="Pengaturan">
                        <x-ui.icon name="settings" class="w-4 h-4 sm:mr-2" />
                        <span class="hidden sm:inline links_name">Pengaturan</span>
                    </button>

                    <button
                        class="flex w-full items-center justify-center sm:justify-start px-3 py-2 text-sm text-destructive hover:bg-destructive/10 transition-colors"
                        onclick="handleLogout()" aria-label="Keluar">
                        <x-ui.icon name="log-out" class="w-4 h-4 sm:mr-2" />
                        <span class="hidden sm:inline links_name">Keluar</span>
                    </button>
                </div>
            </div>

        </div>
    @endif
</div>


<script>
    function toggleProfileDropdown() {
        const dropdown = document.getElementById('profile-dropdown');
        const isVisible = !dropdown.classList.contains('opacity-0');

        if (isVisible) {
            // Hide dropdown
            dropdown.classList.add('opacity-0', 'pointer-events-none', 'scale-95');
            dropdown.classList.remove('opacity-100', 'pointer-events-auto', 'scale-100');
        } else {
            // Show dropdown
            dropdown.classList.remove('opacity-0', 'pointer-events-none', 'scale-95');
            dropdown.classList.add('opacity-100', 'pointer-events-auto', 'scale-100');
        }
    }

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

    function handleSettings() {
        // Close dropdown first
        const dropdown = document.getElementById('profile-dropdown');
        dropdown.classList.add('opacity-0', 'pointer-events-none', 'scale-95');
        dropdown.classList.remove('opacity-100', 'pointer-events-auto', 'scale-100');

        // Show settings modal or redirect to settings page
        showAlert(
            'Fitur pengaturan akan segera tersedia!',
            'Informasi',
            'info'
        );
        // You can replace this with actual settings functionality later
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('profile-dropdown');
        const profileBtn = document.querySelector('.profile-details-btn');

        if (dropdown && profileBtn &&
            !dropdown.contains(event.target) &&
            !profileBtn.contains(event.target)) {
            dropdown.classList.add('opacity-0', 'pointer-events-none', 'scale-95');
            dropdown.classList.remove('opacity-100', 'pointer-events-auto', 'scale-100');
        }
    });
</script>
