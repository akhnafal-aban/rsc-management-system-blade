@include('components.UI.card', ['slot' =>
'<div class="mb-6">
  <h3 class="text-lg font-semibold text-card-foreground">{{ $title ?? "Judul Chart" }}</h3>
  @if(!empty($subtitle))
    <p class="text-sm text-muted-foreground mt-1">{{ $subtitle }}</p>
  @endif
</div>
<div class="h-80 flex items-center justify-center">
  {{ $slot ?? '' }}
</div>
])
