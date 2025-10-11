<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    </head>

    <body class="h-screen bg-background text-foreground flex flex-col">
        {{-- @include('components.navbar')
        <div class="flex flex-1 overflow-hidden">
            @include('components.sidebar')
            <main class="flex-1 overflow-y-auto">
                <div class="p-6 max-w-7xl mx-auto">
                    @yield('content')
                </div>
            </main>
        </div> --}}
        @yield('content')
    </body>
    
</html>