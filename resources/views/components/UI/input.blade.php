<div class="space-y-1">
  @if(!empty($label))
    <label for="{{ $id ?? $name }}" class="block text-sm font-medium text-foreground">
      {{ $label }}
    </label>
  @endif

  <input
    id="{{ $id ?? $name }}"
    name="{{ $name ?? 'input' }}"
    type="{{ $type ?? 'text' }}"
    value="{{ old($name, $value ?? '') }}"
    placeholder="{{ $placeholder ?? '' }}"
    autocomplete="{{ $autocomplete ?? 'on' }}"
    class="w-full px-3 py-2 bg-input border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
    @if(!empty($required)) required @endif
  />

  @if(!empty($error))
    <p class="text-sm text-destructive">{{ $error }}</p>
  @endif

  @if(!empty($helper) && empty($error))
    <p class="text-sm text-muted-foreground">{{ $helper }}</p>
  @endif
</div>
