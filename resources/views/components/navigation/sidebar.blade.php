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

    .sidebar.open + .main-content {
        margin-left: 250px;
    }

    /* Responsive adjustments */
    @media (max-width: 640px) {
        .main-content {
            margin-left: 0 !important;
        }
        
        .sidebar.open + .main-content {
            margin-left: 0 !important;
        }
    }

</style>

<!-- SIDEBAR -->
<div id="sidebar"
    class="hidden sm:block fixed left-0 top-0 h-full w-[78px] lg:w-[78px] bg-background-muted px-[14px] py-[6px] z-[1000] transition-all duration-500 ease-in-out border-r border-border sidebar">
    <div class="h-[60px] flex items-center relative border-b border-border pl-4 pr-2">
        <div class="text-foreground text-xl font-semibold opacity-0 transition-all duration-500 ease-in-out whitespace-nowrap logo_name">
            Navigation
        </div>
    
        <button id="btn"
            class="absolute top-1/2 right-1 -translate-y-1/2 p-2 rounded-lg text-foreground hover:bg-accent hover:text-accent-foreground transition-all duration-500 ease-in-out">
            <x-ui.icon name="menu" class="w-6 h-6" />
        </button>
    </div>
    

    <ul class="h-[calc(100%-190px)] overflow-y-auto overflow-x-hidden nav-list">
        @php($menuItems = [['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home', 'route' => 'dashboard'], ['id' => 'attendance', 'label' => 'Absensi', 'icon' => 'user-check', 'route' => 'attendance.index'], ['id' => 'members', 'label' => 'Members', 'icon' => 'users', 'route' => 'member.index']])

        @foreach ($menuItems as $item)
            @php($isActive = request()->routeIs($item['route']) || (request()->routeIs('dashboard') && $item['id'] === 'dashboard'))
            <li class="relative my-2 list-none">
                <a href="{{ $item['route'] === '#' ? '#' : route($item['route']) }}"
                    class="flex h-[50px] w-full rounded-xl items-center no-underline transition-all duration-[400ms] ease-in-out bg-transparent text-foreground px-3 hover:bg-destructive hover:text-destructive-foreground {{ $isActive ? 'bg-destructive text-destructive-foreground' : '' }}">
                    <x-ui.icon name="{{ $item['icon'] }}"
                        class="w-6 h-6 leading-[50px] text-lg min-w-[50px] max-w-[50px] text-center rounded-xl transition-all duration-[400ms] ease-in-out flex items-center justify-center shrink-0 -ml-3 sidebar-icon" />
                    <span
                        class="text-foreground text-[15px] font-normal whitespace-nowrap opacity-0 pointer-events-none transition-[400ms] ml-3 links_name">{{ $item['label'] }}</span>
                </a>
                <span
                    class="absolute -top-5 left-[calc(100%+15px)] bg-card text-card-foreground shadow-[0_5px_10px_rgba(0,0,0,0.3)] px-3 py-[6px] rounded text-[15px] font-normal opacity-0 whitespace-nowrap pointer-events-none transition-none border border-border tooltip">{{ $item['label'] }}</span>
            </li>
        @endforeach

    </ul>

    <!-- User Profile Section -->
    @if (isset($user))
        <div class="flex flex-col gap-2">
            <!-- Logout Button -->
            <button onclick="handleLogout()"
                class="flex h-[50px] w-full rounded-xl items-center no-underline transition-all duration-[400ms] ease-in-out bg-transparent text-foreground px-3 border-none cursor-pointer hover:bg-destructive hover:text-destructive-foreground profile-logout-btn"
                title="Keluar">
                <x-ui.icon name="log-out"
                    class="w-6 h-6  leading-[50px] text-lg min-w-[50px] max-w-[50px] text-center rounded-xl transition-all duration-[400ms] ease-in-out flex items-center justify-center shrink-0 -ml-2 sidebar-icon" />
                <span
                    class="text-foreground text-[15px] font-normal whitespace-nowrap opacity-0 pointer-events-none transition-[400ms] ml-3 links_name">Keluar</span>
            </button>

            <!-- Profile Details -->
            <li
                class="relative my-2 list-none hover:text-destructive-foreground transition-all duration-[400ms] ease-in-out">
                <div
                    class="flex h-[50px] w-full rounded-xl items-center no-underline transition-all duration-[400ms] ease-in-out bg-transparent text-foreground px-3 cursor-pointer hover:bg-primary hover:text-destructive-foreground profile-details-btn">
                    <x-ui.icon name="user"
                        class="w-6 h-6 leading-[50px] text-lg min-w-[50px] max-w-[50px] text-center rounded-xl transition-all duration-[400ms] ease-in-out flex items-center justify-center shrink-0 -ml-3 sidebar-icon" />
                    <div class="flex flex-col ml-3 min-w-0 flex-1 profile-info">
                        <span
                            class="text-foreground text-[15px] font-medium whitespace-nowrap opacity-0 pointer-events-none transition-[400ms] overflow-hidden text-ellipsis links_name profile-name">{{ $user['name'] ?? '' }}</span>
                        @if (isset($user['role']) && $user['role']->isAdmin())
                            <span
                                class="text-muted-foreground text-xs font-normal whitespace-nowrap opacity-0 pointer-events-none transition-[400ms] overflow-hidden text-ellipsis links_name profile-role">Admin</span>
                        @elseif (isset($user['role']) && $user['role']->isStaff())
                            <span
                                class="text-muted-foreground text-xs font-normal whitespace-nowrap opacity-0 pointer-events-none transition-[400ms] overflow-hidden text-ellipsis links_name profile-role">Staff</span>
                        @endif
                    </div>
                </div>
            </li>
        </div>
    @endif
</div>

<script>
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
</script>
