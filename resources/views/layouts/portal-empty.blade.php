<!DOCTYPE html>
<html lang="es" class="h-full bg-brand-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Acceso') — Portal Home del Valle</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/portal.css', 'resources/js/portal.js'])
    @livewireStyles
</head>
<body class="h-full font-sans antialiased">

    {{-- Fondo decorativo --}}
    <div class="fixed inset-0 bg-brand-950 overflow-hidden pointer-events-none" aria-hidden="true">
        <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-brand-500/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-brand-700/10 rounded-full blur-3xl translate-y-1/2 -translate-x-1/2"></div>
    </div>

    <div class="relative min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">

        {{-- Logo --}}
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center mb-8">
            <a href="https://homedelvalle.mx" class="inline-flex flex-col items-center gap-2 group">
                <div class="flex items-center justify-center w-12 h-12 rounded-2xl bg-brand-500 shadow-lg group-hover:bg-brand-600 transition-colors">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                        <polyline stroke-linecap="round" stroke-linejoin="round" points="9,22 9,12 15,12 15,22"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-brand-300 tracking-wide">Home del Valle</span>
            </a>
        </div>

        {{-- Card principal --}}
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white rounded-2xl shadow-2xl px-8 py-10">

                {{-- Flash status --}}
                @if(session('status'))
                <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-100 text-sm text-green-700 flex items-start gap-3">
                    <svg class="w-5 h-5 shrink-0 mt-0.5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ session('status') }}
                </div>
                @endif

                @yield('content')
            </div>

            {{-- Footer mínimo --}}
            <p class="mt-6 text-center text-xs text-brand-500/60">
                Pocos inmuebles · Más control · Mejores resultados
            </p>
        </div>
    </div>

    @livewireScripts
</body>
</html>
