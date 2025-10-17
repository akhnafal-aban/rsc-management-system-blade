<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('build/assets/img/rsc-logo.png') }}">
    <link rel="shortcut icon" type="image/jpeg" href="{{ asset('build/assets/img/rsc-logo.png') }}">

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>

<body class="h-screen bg-background text-foreground">
    @include('components.navigation.mobile-navbar')
    @include('components.navigation.sidebar')
    @include('components.ui.session-notifications')

    <div class="main-content flex flex-col flex-1 transition-all duration-500 ease-in-out">
        <main class="flex-1 overflow-y-auto">
            @include('components.navigation.navbar')

            <div class="pt-24 sm:pt-20 p-4 sm:p-6 max-w-8xl mx-auto">
                @yield('content')
            </div>
        </main>
    </div>
    @stack('scripts')
</body>

</html>
