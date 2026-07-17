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

    {{-- RealEstateAgent / LocalBusiness schema — presente en todas las páginas públicas --}}
    <script type="application/ld+json">
    {!! json_encode([
        '@context'    => 'https://schema.org',
        '@type'       => 'RealEstateAgent',
        'name'        => $siteSettings?->site_name ?? 'Home del Valle',
        'description' => 'Firma inmobiliaria boutique especializada en la alcaldía Benito Juárez, CDMX. Venta, renta, desarrollo y valuación de inmuebles.',
        'url'         => url('/'),
        'telephone'   => $siteSettings?->contact_phone ?? '+5215513450978',
        'email'       => $siteSettings?->contact_email ?? 'contacto@homedelvalle.mx',
        'slogan'      => 'Pocos inmuebles. Más control. Mejores resultados.',
        'address'     => [
            '@type'           => 'PostalAddress',
            'streetAddress'   => $siteSettings?->address ?? 'Heriberto Frías 903-A',
            'addressLocality' => 'Colonia del Valle',
            'addressRegion'   => 'Ciudad de México',
            'postalCode'      => '03100',
            'addressCountry'  => 'MX',
        ],
        'geo' => [
            '@type'     => 'GeoCoordinates',
            'latitude'  => 19.3738,
            'longitude' => -99.1677,
        ],
        'openingHoursSpecification' => [
            ['@type'=>'OpeningHoursSpecification','dayOfWeek'=>['Monday','Tuesday','Wednesday','Thursday','Friday'],'opens'=>'09:00','closes'=>'18:00'],
            ['@type'=>'OpeningHoursSpecification','dayOfWeek'=>['Saturday'],'opens'=>'10:00','closes'=>'14:00'],
        ],
        'areaServed' => array_merge(
            [['@type'=>'AdministrativeArea','name'=>'Alcaldía Benito Juárez, Ciudad de México']],
            array_map(
                fn ($colonia) => ['@type'=>'Place','name'=>"$colonia, Benito Juárez, CDMX"],
                ['Del Valle', 'Narvarte', 'Narvarte Poniente', 'Nápoles', 'Portales', 'Xoco']
            )
        ),
        'knowsAbout' => ['Venta de predios a desarrolladoras', 'Compra-venta residencial', 'Renta de inmuebles', 'Benito Juárez CDMX'],
        'memberOf'   => ['@type'=>'Organization','name'=>'AMPI - Asociación Mexicana de Profesionales Inmobiliarios'],
        'sameAs'     => array_values(array_filter([
            $siteSettings?->facebook_url  ?? 'https://www.facebook.com/homedelvalle',
            $siteSettings?->instagram_url ?? 'https://www.instagram.com/homedelvalle',
            'https://x.com/HomeDelValleMX',
        ])),
        'priceRange' => '$$',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>

    @livewireStyles

    {{-- Alpine.js viene incluido en Livewire 4 — no cargar desde CDN --}}

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

    {{-- El schema RealEstateAgent (NAP oficial) vive arriba, junto a @vite — un solo bloque por página. --}}

    {{-- Eventos de conversión → GA4 / GTM (clicks WhatsApp/tel/mailto + formularios Livewire) --}}
    @if(($siteSettings?->ga_enabled && $siteSettings?->google_analytics_id) || ($siteSettings?->gtm_enabled && $siteSettings?->gtm_id))
    <script>
    (function () {
        // Un solo punto de salida: gtag si GA4 está directo; dataLayer si solo hay GTM.
        // Nunca ambos — gtag() ya empuja al dataLayer y duplicaría los triggers de GTM.
        window.hdvTrack = function (name, params) {
            params = params || {};
            if (typeof window.gtag === 'function') {
                window.gtag('event', name, params);
            } else {
                window.dataLayer = window.dataLayer || [];
                window.dataLayer.push(Object.assign({ event: name }, params));
            }
        };

        document.addEventListener('click', function (e) {
            var a = e.target.closest ? e.target.closest('a[href]') : null;
            if (!a) return;
            var href = a.getAttribute('href') || '';
            var params = {
                page_path: window.location.pathname,
                link_text: (a.textContent || '').trim().replace(/\s+/g, ' ').slice(0, 80)
            };
            if (a.dataset.trackLocation) params.cta_location = a.dataset.trackLocation;

            if (/wa\.me\/\?/.test(href)) {
                window.hdvTrack('whatsapp_share', params); // compartir propiedad, sin número destino
            } else if (/wa\.me\/|api\.whatsapp\.com/.test(href)) {
                window.hdvTrack('whatsapp_click', params);
            } else if (href.lastIndexOf('tel:', 0) === 0) {
                window.hdvTrack('phone_click', params);
            } else if (href.lastIndexOf('mailto:', 0) === 0) {
                window.hdvTrack('email_click', params);
            }
        }, true);

        // form_start: primer campo tocado de cada formulario (una vez por
        // form por carga). Con generate_lead da la tasa de finalización
        // real por formulario y dispositivo — la base para decidir si los
        // formularios largos necesitan partirse en pasos.
        document.addEventListener('focusin', function (e) {
            var field = e.target;
            if (!field.matches || !field.matches('input, select, textarea')) return;
            var form = field.closest('form');
            if (!form || form.dataset.hdvStarted) return;
            form.dataset.hdvStarted = '1';
            window.hdvTrack('form_start', {
                form_id: form.getAttribute('id') || form.dataset.formName || 'form',
                page_path: window.location.pathname
            });
        }, true);

        // Formularios Livewire: cada form dispara 'lead-conversion' SOLO en el
        // camino de éxito real (honeypot y spam muestran éxito pero no disparan).
        document.addEventListener('livewire:init', function () {
            Livewire.on('lead-conversion', function (payload) {
                var p = Array.isArray(payload) ? payload[0] : (payload || {});
                window.hdvTrack('generate_lead', {
                    form_type: p.formType || 'desconocido',
                    page_path: window.location.pathname
                });
            });
            // Avance de paso en formularios multi-paso (piloto): funnel
            // form_start → form_step 2 → form_step 3 → generate_lead.
            Livewire.on('form-step', function (payload) {
                var p = Array.isArray(payload) ? payload[0] : (payload || {});
                window.hdvTrack('form_step', {
                    form_type: p.formType || 'desconocido',
                    step: p.step || 0,
                    page_path: window.location.pathname
                });
            });
        });
    })();
    </script>
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
    <x-public.whatsapp-float :siteSettings="$siteSettings" />

    @yield('scripts')

    {{-- Livewire Scripts --}}
    @livewireScripts

    {{-- Custom body scripts --}}
    @if($siteSettings?->custom_body_scripts)
    {!! $siteSettings->custom_body_scripts !!}
    @endif
</body>
</html>
