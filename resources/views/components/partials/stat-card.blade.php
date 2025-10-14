<div class="bg-card p-6 rounded-lg shadow-sm border border-border hover:shadow-md transition-shadow">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-muted-foreground mb-1">{{ $title ?? 'Stat Title' }}</p>
            <p class="text-2xl font-bold text-card-foreground">{{ $value ?? '' }}</p>

            @if (!empty($change))
                @if (isset($change['type']) && in_array($change['type'], ['increase', 'decrease']))
                    <p class="text-sm mt-1 {{ $change['type'] === 'increase' ? 'text-chart-2' : 'text-destructive' }}">
                        {{ $change['type'] === 'increase' ? '↑' : '↓' }} {{ $change['value'] ?? '' }}
                    </p>
                @elseif(isset($change['type']) && $change['type'] === 'stable')
                    <p class="text-sm mt-1 text-muted-foreground">Stabil (0%)</p>
                @elseif(isset($change['type']) && $change['type'] === 'neutral')
                    <p class="text-sm mt-1 text-muted-foreground">Belum ada data pembanding</p>
                @else
                    <p class="text-sm mt-1 text-muted-foreground">Tidak ada perubahan</p>
                @endif
            @else
                <p class="text-sm mt-1 text-muted-foreground">Tidak ada perubahan</p>
            @endif
        </div>

        @if (!empty($icon))
            <div class="p-3 rounded-lg bg-chart-1/20 text-chart-1">
                <x-ui.icon name="{{ $icon }}" class="w-8 h-8" />
            </div>
        @endif
    </div>
</div>
