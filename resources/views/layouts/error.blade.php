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
                            <img src="{{ Vite::asset('resources/images/rsc_logo.png') }}" 
                                 alt="RSC Logo" 
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
            <div class="max-w-md w-full">
                {{ $slot }}
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

