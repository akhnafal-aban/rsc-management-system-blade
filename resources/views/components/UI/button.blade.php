@props([
  'type' => 'button',
  'class' => '',
])

<button 
  type="{{ $type }}"
  {{ $attributes->merge([
    'class' => "inline-flex items-center justify-center font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 focus:ring-ring px-4 py-2 text-sm " . $class
  ]) }}
>
  {{ $slot ?? 'Button' }}
</button>
