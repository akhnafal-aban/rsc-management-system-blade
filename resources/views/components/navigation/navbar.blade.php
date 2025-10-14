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
        <button
          class="p-2.5 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-lg transition-colors"
          title="Notifikasi"
        >
          <x-ui.icon name="bell" class="w-5 h-5" />
        </button>
        <button
          class="p-2.5 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-lg transition-colors"
          title="Pengaturan"
        >
          <x-ui.icon name="settings" class="w-5 h-5" />
        </button>
      </div>

      <!-- User Info -->
      @if(isset($user))
      <div class="flex items-center space-x-3 pl-4 border-l border-border">
        <div class="text-right leading-tight">
          <p class="text-sm font-medium text-card-foreground">{{ $user['name'] ?? '' }}</p>
          <p class="text-xs text-muted-foreground">{{ $user['role'] ?? '' }}</p>
        </div>
        <div class="relative">
          <div class="w-9 h-9 bg-secondary/20 rounded-full flex items-center justify-center ring-1 ring-border">
            <x-ui.icon name="user" class="w-4 h-4 text-secondary" />
          </div>
        </div>
        <button
          onclick="handleLogout()"
          class="p-2 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-lg transition-colors"
          title="Keluar"
        >
          <x-ui.icon name="log-out" class="w-4 h-4" />
        </button>
      </div>
      @endif
    </div>
  </div>
</nav>

<script>
function handleLogout() {
  if (confirm('Apakah Anda yakin ingin keluar?')) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("logout") }}';

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
