<div class="bg-card p-6 rounded-lg shadow-sm border border-border hover:shadow-md transition-shadow">
  <div class="flex items-center justify-between">
    <div>
      <p class="text-sm font-medium text-muted-foreground mb-1">{{ $title ?? 'Stat Title' }}</p>
      <p class="text-2xl font-bold text-card-foreground">{{ $value ?? '' }}</p>
      @if(!empty($change))
        <p class="text-sm mt-1 {{ $change['type'] === 'increase' ? 'text-chart-2' : 'text-destructive' }}">
          {{ $change['type'] === 'increase' ? '↑' : '↓' }} {{ $change['value'] ?? '' }}
        </p>
      @endif
    </div>
    @if(!empty($icon))
    <div class="p-3 rounded-lg bg-chart-1/20 text-chart-1">
      <x-ui.icon :name="$icon" class="w-5 h-5" />
    </div>
    @endif
  </div>
</div>
