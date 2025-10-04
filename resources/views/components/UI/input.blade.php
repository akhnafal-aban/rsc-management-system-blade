<div class="space-y-1">
  @if(!empty($label))
    <label class="block text-sm font-medium text-foreground">{{ $label }}</label>
  @endif
  <input class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent" />
  @if(!empty($error))
    <p class="text-sm text-destructive">{{ $error }}</p>
  @endif
  @if(!empty($helper) && empty($error))
    <p class="text-sm text-muted-foreground">{{ $helper }}</p>
  @endif
</div>
