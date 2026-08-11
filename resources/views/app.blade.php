<!DOCTYPE html>
<html lang="es-CO">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title inertia>terracosismos · Monitoreo Sísmico de Colombia</title>
    <meta name="description" content="terracosismos permite consultar sismos recientes de Colombia en un mapa y activar alertas personalizadas por correo.">
    <meta name="keywords" content="terracosismos, sismos Colombia, terremotos Colombia, monitoreo sísmico, alertas sísmicas, mapa de sismos">
    <meta name="author" content="terracosismos">
    <meta name="robots" content="index, follow, max-image-preview:large">
    <meta name="theme-color" content="#71159d">
    <meta name="application-name" content="terracosismos">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="terracosismos">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('icons/app-icon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('icons/favicon-32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('icons/favicon-16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icons/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('build/manifest.webmanifest') }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="es_CO">
    <meta property="og:site_name" content="terracosismos">
    <meta property="og:title" content="terracosismos · Monitoreo Sísmico de Colombia">
    <meta property="og:description" content="Sismos recientes de Colombia, mapa en tiempo real y alertas personalizadas de terracosismos.">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('icons/pwa-512.png') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="terracosismos · Monitoreo Sísmico de Colombia">
    <meta name="twitter:description" content="Sismos recientes de Colombia, mapa en tiempo real y alertas personalizadas.">
    <meta name="twitter:image" content="{{ asset('icons/pwa-512.png') }}">
    <link rel="alternate" type="text/plain" href="{{ asset('llms.txt') }}" title="Información de terracosismos para asistentes de IA">
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [[
            '@type' => 'WebSite',
            '@id' => config('app.url').'#website',
            'name' => 'terracosismos',
            'url' => config('app.url'),
            'inLanguage' => 'es-CO',
            'description' => 'Plataforma de monitoreo de sismos en Colombia.',
        ], [
            '@type' => 'WebApplication',
            '@id' => config('app.url').'#application',
            'name' => 'terracosismos',
            'url' => config('app.url'),
            'description' => 'Monitoreo de sismos en Colombia y alertas personalizadas por correo.',
            'applicationCategory' => 'WeatherApplication',
            'operatingSystem' => 'Web, Android, iOS',
            'browserRequirements' => 'Requiere JavaScript y un navegador web moderno.',
            'isAccessibleForFree' => true,
            'inLanguage' => 'es-CO',
            'about' => ['@type' => 'Thing', 'name' => 'Actividad sísmica de Colombia'],
        ]],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
