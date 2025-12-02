<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Error' }} | {{ config('app.name', 'RSC Management') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full bg-background text-foreground font-sans antialiased">
    <div class="min-h-full flex flex-col">
        <!-- Header -->
        <header class="bg-card border-b border-border">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo -->
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center shadow-sm overflow-hidden">
                            <img src="{{ Vite::asset('resources/images/rsc_logo.png') }}" alt="RSC Logo"
                                class="w-full h-full object-cover rounded-xl">
                        </div>
                        <div>
                            <h1 class="text-lg font-semibold text-card-foreground">Really Sports Center</h1>
                            <p class="text-xs text-muted-foreground">Management System</p>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <nav class="hidden md:flex items-center space-x-4">
                        <a href="{{ route('dashboard') }}"
                            class="text-muted-foreground hover:text-primary transition-colors">
                            Dashboard
                        </a>
                        <a href="{{ route('attendance.index') }}"
                            class="text-muted-foreground hover:text-primary transition-colors">
                            Absensi
                        </a>
                        <a href="{{ route('member.index') }}"
                            class="text-muted-foreground hover:text-primary transition-colors">
                            Members
                        </a>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-8">
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
                    @if ($showHomeButton)
                        <a href="{{ route('dashboard') }}"
                            class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-primary-foreground bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                            <x-ui.icon name="home" class="w-5 h-5 mr-2" />
                            Kembali ke Dashboard
                        </a>
                    @endif

                    @if ($showBackButton)
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
        </main>

        <!-- Footer -->
        <footer class="bg-card border-t border-border">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="text-center text-sm text-muted-foreground">
                    <p>&copy; {{ date('Y') }} Really Sports Center. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</body>

</html>
