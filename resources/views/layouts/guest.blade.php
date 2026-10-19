@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['native-app' => is_native_app()]) x-data :class="{ 'dark': $store.darkMode.dark }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ? $title . ' - ' . app_name() : app_name() }}</title>

        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

        <x-anti-fouc-script />

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{-- Alpine stores, dark mode sync, teleport cleanup loaded via Vite (alpine-stores.js) --}}

        @if(config('services.recaptcha.enabled'))
        <!-- Google reCAPTCHA v2 -->
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        @endif
    </head>
    {{-- data-system-bars: halaman auth mobile berlatar gradien hijau gelap -> ikon status bar terang (native-bridge.js) --}}
    <body class="font-sans text-gray-900 antialiased" data-system-bars="dark">
        {{ $slot }}
        
        <x-toast />
        <x-font-scale-control />
    </body>
</html>
