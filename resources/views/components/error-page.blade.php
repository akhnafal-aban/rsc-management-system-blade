@extends('layouts.error')

@section('content')
    @php
        $code = $code ?? '500';
        $title = $title ?? 'Server Error';
        $description = $description ?? 'Terjadi kesalahan pada server. Silakan coba lagi nanti.';
        $icon = $icon ?? 'alert-circle';
        $showHomeButton = $showHomeButton ?? true;
        $showBackButton = $showBackButton ?? true;
    @endphp

    <div class="text-center">
        <!-- Error Icon -->
        <div class="mx-auto flex items-center justify-center w-24 h-24 rounded-full bg-destructive/10 mb-6">
            <x-ui.icon name="{{ $icon }}" class="w-12 h-12 text-destructive" />
        </div>

        <!-- Error Code & Title -->
        <div class="mb-4">
            <h1 class="text-6xl font-bold text-foreground mb-2">{{ $code }}</h1>
            <h2 class="text-2xl font-semibold text-foreground mb-4">{{ $title }}</h2>
        </div>

        <!-- Description -->
        <div class="mb-8">
            <p class="text-muted-foreground text-lg leading-relaxed">
                {{ $description }}
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            @if($showHomeButton)
                <a href="{{ route('dashboard') }}" 
                   class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-primary-foreground bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                    <x-ui.icon name="home" class="w-5 h-5 mr-2" />
                    Kembali ke Dashboard
                </a>
            @endif

            @if($showBackButton)
                <button onclick="history.back()" 
                        class="inline-flex items-center justify-center px-6 py-3 border border-border text-base font-medium rounded-lg text-foreground bg-card hover:bg-accent hover:text-accent-foreground focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-ring transition-colors">
                    <x-ui.icon name="arrow-left" class="w-5 h-5 mr-2" />
                    Kembali
                </button>
            @endif
        </div>

        <!-- Additional Help -->
        <div class="mt-8 text-sm text-muted-foreground">
            <p>Jika masalah berlanjut, silakan hubungi administrator sistem.</p>
        </div>
    </div>
@endsection

