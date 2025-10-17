@if (session('success'))
    <div data-session-success class="hidden">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div data-session-error class="hidden">{{ session('error') }}</div>
@endif

@if (session('warning'))
    <div data-session-warning class="hidden">{{ session('warning') }}</div>
@endif

@if (session('info'))
    <div data-session-info class="hidden">{{ session('info') }}</div>
@endif

@if ($errors->any())
    <div data-session-error class="hidden">
        @foreach ($errors->all() as $error)
            {{ $error }}
            @if (!$loop->last)
                <br>
            @endif
        @endforeach
    </div>
@endif
