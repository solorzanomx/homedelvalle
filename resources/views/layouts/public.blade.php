<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @yield('meta')

    {{-- Favicon --}}
    @if($siteSettings?->favicon_path)
    <link rel="icon" type="image/png" href="{{ asset('storage/' . $siteSettings->favicon_path) }}">
    <link rel="apple-touch-icon" href="{{ asset('storage/' . $siteSettings->favicon_path) }}">
    @endif

    {{-- Fonts: Inter with optical sizing --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Alpine.js + plugins --}}
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        :root {
            --color-primary: {{ $siteSettings?->primary_color ?? '#3B82C4' }};
            --color-secondary: {{ $siteSettings?->secondary_color ?? '#1E3A5F' }};
        }
    </style>

    {{-- Google Tag Manager (head) --}}
    @if($siteSettings?->gtm_enabled && $siteSettings?->gtm_id)
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','{{ $siteSettings->gtm_id }}');</script>
    @endif

    {{-- Google Analytics --}}
    @if($siteSettings?->ga_enabled && $siteSettings?->google_analytics_id)
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $siteSettings->google_analytics_id }}"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{{ $siteSettings->google_analytics_id }}');</script>
    @endif

    {{-- Facebook Pixel --}}
    @if($siteSettings?->fb_pixel_enabled && $siteSettings?->facebook_pixel_id)
    <script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
    fbq('init','{{ $siteSettings->facebook_pixel_id }}');fbq('track','PageView');</script>
    <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ $siteSettings->facebook_pixel_id }}&ev=PageView&noscript=1"/></noscript>
    @endif

    {{-- Custom head scripts --}}
    @if($siteSettings?->custom_head_scripts)
    {!! $siteSettings->custom_head_scripts !!}
    @endif
</head>
<body class="font-sans antialiased text-gray-900 bg-white overflow-x-hidden">

    {{-- GTM noscript fallback --}}
    @if($siteSettings?->gtm_enabled && $siteSettings?->gtm_id)
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $siteSettings->gtm_id }}"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    @endif

    <x-public.navbar />

    <main>
        {{-- Flash success --}}
        @if(session('success'))
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-4"
             x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 5000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2">
            <div class="rounded-2xl bg-emerald-50 border border-emerald-200/60 p-4 text-sm text-emerald-800 flex items-center justify-between shadow-premium">
                <div class="flex items-center gap-2.5">
                    <div class="flex items-center justify-center w-8 h-8 rounded-full bg-emerald-100">
                        <x-icon name="check" class="w-4 h-4 text-emerald-600" />
                    </div>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
                <button @click="show = false" class="text-emerald-400 hover:text-emerald-600 p-1 rounded-full hover:bg-emerald-100 transition-colors">&times;</button>
            </div>
        </div>
        @endif

        @yield('content')
    </main>

    <x-public.footer />
    <x-public.lead-popup />

    @yield('scripts')

    {{-- Custom body scripts --}}
    @if($siteSettings?->custom_body_scripts)
    {!! $siteSettings->custom_body_scripts !!}
    @endif
</body>
</html>
