<div class="text-center">
  <h3 class="text-lg font-semibold text-card-foreground mb-4">Scanner Barcode</h3>
  <div class="relative mb-6">
    <div class="w-full h-64 bg-muted rounded-lg flex items-center justify-center border-2 border-dashed border-border transition-colors">
      <!-- Camera placeholder -->
    </div>
    <!-- Scan result overlay (success/error) can be handled with Blade conditionals if needed -->
  </div>
  <div class="flex space-x-3 justify-center">
    @include('components.UI.button', ['size' => 'lg', 'slot' => 'Mulai Scan'])
    @include('components.UI.button', ['variant' => 'secondary', 'size' => 'lg', 'slot' => 'Hentikan Scan'])
  </div>
</div>
