<!-- SIDEBAR -->
<div id="sidebar"
    class="fixed lg:static top-0 left-0 h-full bg-background-muted text-sidebar-foreground transition-all duration-500 ease-in-out w-64 flex flex-col shadow-lg overflow-hidden z-40 lg:translate-x-0 -translate-x-full">
    <div class="sidebar-header p-4 border-b border-sidebar-border flex items-center justify-between">
        <span id="sidebar-title" class="font-semibold transition-all duration-500 ease-in-out">Navigation</span>
        <button onclick="toggleSidebar()"
            class="p-2 rounded-lg hover:bg-sidebar-accent/20 transition-colors duration-200 flex items-center justify-center">
            <x-ui.icon name="chevron-left" class="w-5 h-5 transition-transform duration-500 ease-in-out"
                id="sidebar-toggle-icon" />
        </button>
    </div>

    <nav class="flex-1 p-4 space-y-2">
        @php(
    $menuItems = [
        ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home', 'route' => 'dashboard'],
        ['id' => 'attendance', 'label' => 'Absensi', 'icon' => 'user-check', 'route' => 'attendance.index'],
        ['id' => 'members', 'label' => 'Members', 'icon' => 'users', 'route' => 'member.index'],
    ]
)

        @foreach ($menuItems as $item)
            @php($isActive = request()->routeIs($item['route']) || (request()->routeIs('dashboard') && $item['id'] === 'dashboard'))
            <a href="{{ $item['route'] === '#' ? '#' : route($item['route']) }}"
                class="menu-item grid grid-cols-[2.5rem_1fr] items-center gap-3 p-3 rounded-lg transition-all duration-500 ease-in-out {{ $isActive ? 'bg-sidebar-primary text-sidebar-primary-foreground' : 'text-sidebar-foreground/70 hover:bg-sidebar-accent/20 hover:text-sidebar-foreground' }}">
                <x-ui.icon name="{{ $item['icon'] }}"
                    class="w-5 h-5 flex-shrink-0 justify-self-center transition-transform duration-500 ease-in-out" />
                <span class="sidebar-label truncate transition-all duration-500 ease-in-out">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>
</div>

<!-- BACKDROP (for mobile overlay) -->
<div id="sidebar-backdrop"
    class="fixed inset-0 bg-black/40 backdrop-blur-sm z-30 hidden opacity-0 transition-opacity duration-300 ease-in-out lg:hidden"
    onclick="toggleSidebar()"></div>

<!-- NAVBAR (shows toggle button on mobile) -->
<header
    class="lg:hidden fixed top-0 left-0 right-0 h-14 bg-card border-b border-border flex items-center justify-between px-4 z-50">
    <button onclick="toggleSidebar()" class="p-2 rounded-md hover:bg-muted/50 transition">
        <x-ui.icon name="menu" class="w-6 h-6" />
    </button>
    <h1 class="text-lg font-semibold">Really Sports Center</h1>
</header>

<style>
    /* === COLLAPSED STATE (Desktop only) === */
    #sidebar.collapsed {
        width: 5rem;
    }

    #sidebar.collapsed #sidebar-title {
        opacity: 0;
        max-width: 0;
        overflow: hidden;
        white-space: nowrap;
    }

    #sidebar.collapsed .sidebar-label {
        max-width: 0;
        opacity: 0;
        overflow: hidden;
        white-space: nowrap;
    }

    #sidebar.collapsed .menu-item {
        grid-template-columns: 1fr;
        gap: 0;
        justify-items: center;
    }

    #sidebar.collapsed .menu-item .w-5 {
        transform: translateX(0) scale(1.05);
    }

    #sidebar.collapsed .sidebar-header {
        justify-content: center;
        padding: 1rem 0.5rem;
    }

    /* === MOBILE SIDEBAR === */
    #sidebar.open {
        transform: translateX(0);
    }

    #sidebar-backdrop.show {
        display: block;
        opacity: 1;
    }
</style>

<script>
    let sidebarCollapsed = false;
    let sidebarOpen = false;

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const icon = document.getElementById('sidebar-toggle-icon');
        const backdrop = document.getElementById('sidebar-backdrop');

        // Mobile toggle behavior
        if (window.innerWidth < 1024) {
            sidebarOpen = !sidebarOpen;
            if (sidebarOpen) {
                sidebar.classList.add('open');
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.add('show');
            } else {
                sidebar.classList.remove('open');
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.remove('show');
            }
            return;
        }

        // Desktop collapse behavior
        sidebarCollapsed = !sidebarCollapsed;
        if (sidebarCollapsed) {
            sidebar.classList.add('collapsed');
            icon.classList.remove('chevron-left');
            icon.classList.add('chevron-right');
        } else {
            sidebar.classList.remove('collapsed');
            icon.classList.remove('chevron-right');
            icon.classList.add('chevron-left');
        }
    }
</script>
