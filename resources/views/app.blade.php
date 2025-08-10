<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'sellam') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">

    <!-- Meta Tags -->
    <meta name="description" content="Vendez, achetez et participez à des lives shopping sur la plateforme sellam">
    <meta name="keywords" content="vinted, marketplace, live shopping, ecommerce, vente, achat">
    <meta name="author" content="sellam">

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div id="app"></div>

    <!-- Global App Configuration -->
    <script>
        window.App = {
            url: '{{ url('/') }}',
            api_url: '{{ url('/api/v1') }}',
            csrf_token: '{{ csrf_token() }}',
            locale: '{{ app()->getLocale() }}',
            user: @json(auth()->user())
        };
    </script>
</body>
</html>

