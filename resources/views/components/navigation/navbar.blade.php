<nav class="bg-card border-b border-border px-4 py-3 shadow-sm">
  <div class="flex items-center justify-between">
    <div class="flex items-center space-x-4">
      <div class="flex items-center space-x-2">
        <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
          <span class="text-primary-foreground font-bold text-sm">RS</span>
        </div>
        <h1 class="text-xl font-bold text-card-foreground">Really Sports Center</h1>
      </div>
    </div>
    
    <div class="flex items-center space-x-4">
      <button class="p-2 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-lg transition-colors">
        <x-ui.icon name="bell" class="w-5 h-5" />
      </button>
      <button class="p-2 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-lg transition-colors">
        <x-ui.icon name="settings" class="w-5 h-5" />
      </button>
      
      @if(isset($user))
      <div class="flex items-center space-x-3">
        <div class="text-right">
          <p class="text-sm font-medium text-card-foreground">{{ $user['name'] ?? '' }}</p>
          <p class="text-xs text-muted-foreground">{{ $user['role'] ?? '' }}</p>
        </div>
        <div class="w-8 h-8 bg-secondary/20 rounded-full flex items-center justify-center">
          <x-ui.icon name="user" class="w-4 h-4 text-secondary" />
        </div>
        <button 
          onclick="handleLogout()"
          class="p-2 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-lg transition-colors"
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
    // Submit logout form
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
