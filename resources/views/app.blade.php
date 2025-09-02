<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'RIKEAA') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet">

    <!-- PWA Meta Tags -->
    <meta name="description" content="Marketplace RIKEAA - Achetez et vendez des produits d'occasion en toute sécurité">
    <meta name="keywords" content="rikeaa, marketplace, occasion, vente, achat, produits">
    <meta name="author" content="RIKEAA">
    <meta name="theme-color" content="#0ea5e9">
    <meta name="color-scheme" content="light dark">
    
    <!-- iOS Specific Meta Tags -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="RIKEAA">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="format-detection" content="telephone=no">
    <meta name="mobile-web-app-capable" content="yes">
    
    <!-- iOS Status Bar -->
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <!-- Windows Meta Tags -->
    <meta name="msapplication-TileColor" content="#0ea5e9">
    <meta name="msapplication-config" content="/browserconfig.xml">
    <meta name="msapplication-TileImage" content="/logo.png">
    
    <!-- PWA Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="/logo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/logo.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/apple-touch-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="167x167" href="/apple-touch-icon-167x167.png">
    <link rel="manifest" href="/manifest.json">

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

        // PWA Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((registration) => {
                        console.log('SW registered: ', registration);
                        
                        // Check for updates
                        registration.addEventListener('updatefound', () => {
                            const newWorker = registration.installing;
                            newWorker.addEventListener('statechange', () => {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    // New content is available
                                    if (confirm('Une nouvelle version est disponible. Voulez-vous recharger la page ?')) {
                                        window.location.reload();
                                    }
                                }
                            });
                        });
                    })
                    .catch((registrationError) => {
                        console.log('SW registration failed: ', registrationError);
                    });
            });
        }

        // iOS PWA Installation Detection
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            
            // Show custom install button if needed
            if (window.App && window.App.showInstallPrompt) {
                window.App.showInstallPrompt();
            }
        });

        // iOS specific handling
        if (navigator.userAgent.includes('iPhone') || navigator.userAgent.includes('iPad')) {
            // Add iOS specific classes
            document.body.classList.add('ios-device');
            
            // Handle iOS viewport
            const viewport = document.querySelector('meta[name=viewport]');
            if (viewport) {
                viewport.setAttribute('content', 'width=device-width, initial-scale=1, viewport-fit=cover, user-scalable=no');
            }
        }
    </script>
</body>
</html>

