<div class="fixed inset-0 z-50 overflow-y-auto">
  <div class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
    <div class="inline-block align-bottom bg-card text-card-foreground rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle w-full max-w-lg">
      @if(!empty($title))
        <div class="flex items-center justify-between px-6 py-4 border-b border-border">
          <h3 class="text-lg font-medium text-card-foreground">{{ $title }}</h3>
          <button class="text-muted-foreground hover:text-foreground p-1 rounded-lg hover:bg-muted transition-colors">&times;</button>
        </div>
      @endif
      <div class="px-6 py-4">
        {{ $slot ?? '' }}
      </div>
    </div>
  </div>
</div>
