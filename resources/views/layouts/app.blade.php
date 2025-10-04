<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Really Sports Center')</title>
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
</head>
<body class="bg-background text-foreground">
    @yield('content')
</body>
</html>
