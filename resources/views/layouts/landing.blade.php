<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @yield('meta')

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        :root {
            --color-primary: {{ $siteSettings?->primary_color ?? '#4f46e5' }};
            --color-secondary: {{ $siteSettings?->secondary_color ?? '#7c3aed' }};
        }
    </style>
</head>
<body class="font-['Inter'] antialiased text-gray-900 bg-white">

    @yield('content')

    @yield('scripts')
</body>
</html>
