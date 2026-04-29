@extends('layouts.public')

@section('meta')
    <x-public.seo-meta
        title="Firma inmobiliaria boutique en Benito Juárez"
        description="¿Quieres vender tu propiedad en la Benito Juárez? Valuación gratuita, venta en 45 días promedio y seguridad jurídica completa. Firma inmobiliaria boutique en CDMX."
        :canonical="url('/')"
    />
    <x-public.json-ld type="RealEstateAgent" :data="array_filter([
        'name'        => $siteSettings?->site_name ?? 'Home del Valle Bienes Raíces',
        'description' => $siteSettings?->site_tagline ?? 'Firma inmobiliaria boutique de alta precisión en Benito Juárez, CDMX.',
        'url'         => url('/'),
        'telephone'   => $siteSettings?->contact_phone,
        'email'       => $siteSettings?->contact_email,
        'logo'        => $siteSettings?->logo_path
                            ? ['@type' => 'ImageObject', 'url' => asset('storage/' . $siteSettings->logo_path)]
                            : null,
        'image'       => $siteSettings?->hero_image_path
                            ? asset('storage/' . $siteSettings->hero_image_path)
                            : null,
        'address' => [
            '@type'           => 'PostalAddress',
            'streetAddress'   => $siteSettings?->address ?? '',
            'addressLocality' => 'Ciudad de México',
            'addressRegion'   => 'Alcaldía Benito Juárez',
            'postalCode'      => '03100',
            'addressCountry'  => 'MX',
        ],
        'geo' => [
            '@type'     => 'GeoCoordinates',
            'latitude'  => 19.3910,
            'longitude' => -99.1677,
        ],
        'areaServed' => [
            '@type' => 'City',
            'name'  => 'Ciudad de México',
        ],
        'openingHoursSpecification' => [
            [
                '@type'     => 'OpeningHoursSpecification',
                'dayOfWeek' => ['Monday','Tuesday','Wednesday','Thursday','Friday'],
                'opens'     => '09:00',
                'closes'    => '18:00',
            ],
            [
                '@type'     => 'OpeningHoursSpecification',
                'dayOfWeek' => ['Saturday'],
                'opens'     => '10:00',
                'closes'    => '14:00',
            ],
        ],
        'sameAs' => array_values(array_filter([
            $siteSettings?->facebook_url,
            $siteSettings?->instagram_url,
            $siteSettings?->tiktok_url,
        ])),
        'priceRange' => '$$',
    ])" />
@endsection

@section('content')

    {{-- ============================================ --}}
    {{-- 1. HERO SECTION — Selector de intención --}}
    {{-- ============================================ --}}
    <section class="relative overflow-hidden bg-brand-950" id="inicio">
        @if($siteSettings?->hero_image_path)
        <img src="{{ asset('storage/' . $siteSettings->hero_image_path) }}" alt="Propiedades en Benito Juárez" class="absolute inset-0 w-full h-full object-cover opacity-20">
        @endif

        <div class="absolute inset-0 bg-gradient-to-br from-brand-950 via-brand-900/90 to-brand-800/80"></div>
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_rgba(59,130,196,0.15)_0%,_transparent_60%)]"></div>
        <div class="absolute top-20 right-10 w-72 h-72 bg-brand-500/10 rounded-full blur-3xl animate-float"></div>
        <div class="absolute bottom-20 left-10 w-96 h-96 bg-brand-400/5 rounded-full blur-3xl animate-float animation-delay-300"></div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-24 pb-16 sm:pt-32 sm:pb-20 lg:pt-36 lg:pb-24"
             x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">

            {{-- Eyebrow --}}
            <div class="inline-flex items-center gap-2 rounded-full bg-white/10 border border-white/10 px-4 py-1.5 text-sm text-brand-200 backdrop-blur-sm mb-8">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                {{ $siteSettings?->hero_badge ?? 'Firma boutique en Benito Juárez · 30+ años' }}
            </div>

            {{-- Headline + subheadline --}}
            <div class="max-w-3xl">
                <h1 class="text-4xl sm:text-5xl lg:text-[3.5rem] font-extrabold text-white tracking-tight leading-[1.08]">
                    {{ $siteSettings?->hero_heading ?? 'Pocos inmuebles. Más control. Mejores resultados.' }}
                </h1>
                <p class="mt-6 text-lg sm:text-xl text-brand-200/80 max-w-2xl leading-relaxed">
                    {{ $siteSettings?->hero_subheading ?? 'Vendemos, rentamos y conseguimos inmuebles en Benito Juárez. Elige tu perfil y recibe asesoría personalizada en menos de 24 horas.' }}
                </p>
            </div>

            {{-- ── Tarjetas de intención ── --}}
            <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                {{-- Propietarios — destacado --}}
                <a href="{{ route('landing.vende') }}"
                   class="group relative flex flex-col justify-between bg-brand-500/20 backdrop-blur-sm border border-brand-400/40 rounded-2xl p-6 hover:bg-brand-500/28 hover:border-brand-400/60 hover:shadow-2xl transition-all duration-300 text-left ring-1 ring-brand-400/20">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-brand-500 text-white text-[0.6rem] font-bold tracking-widest uppercase px-3 py-1 rounded-full shadow-lg whitespace-nowrap">
                        ★ Más solicitado
                    </div>
                    <div>
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-brand-400/20 group-hover:bg-brand-500 transition-all duration-300 mb-5">
                            <x-icon name="home" class="w-5 h-5 text-white" />
                        </div>
                        <p class="text-[0.65rem] font-bold tracking-[0.12em] uppercase text-brand-300 mb-1">Propietarios</p>
                        <p class="text-white font-semibold text-base leading-snug">Quiero vender mi propiedad</p>
                        <p class="mt-2 text-sm text-brand-300/70 leading-relaxed">Valuación gratuita, venta en 45 días promedio y seguridad jurídica completa.</p>
                    </div>
                    <div class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-white transition-colors duration-300">
                        Solicitar valuación
                        <x-icon name="arrow-right" class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1" />
                    </div>
                </a>

                {{-- Compradores --}}
                <a href="{{ route('landing.compra') }}"
                   class="group relative flex flex-col justify-between bg-white/8 backdrop-blur-sm border border-white/12 rounded-2xl p-6 hover:bg-white/14 hover:border-white/25 hover:shadow-2xl transition-all duration-300 text-left">
                    <div>
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-white/10 group-hover:bg-brand-500 transition-all duration-300 mb-5">
                            <x-icon name="search" class="w-5 h-5 text-white" />
                        </div>
                        <p class="text-[0.65rem] font-bold tracking-[0.12em] uppercase text-brand-300 mb-1">Compradores</p>
                        <p class="text-white font-semibold text-base leading-snug">Estoy buscando dónde vivir o invertir</p>
                        <p class="mt-2 text-sm text-brand-300/70 leading-relaxed">Propiedades verificadas, asesoría personalizada y acompañamiento hasta el cierre.</p>
                    </div>
                    <div class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-300 group-hover:text-white transition-colors duration-300">
                        Iniciar búsqueda
                        <x-icon name="arrow-right" class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1" />
                    </div>
                </a>

                {{-- Rentar para vivir --}}
                <a href="{{ route('landing.rentar') }}"
                   class="group relative flex flex-col justify-between bg-white/8 backdrop-blur-sm border border-white/12 rounded-2xl p-6 hover:bg-white/14 hover:border-white/25 hover:shadow-2xl transition-all duration-300 text-left">
                    <div>
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-white/10 group-hover:bg-brand-500 transition-all duration-300 mb-5">
                            <x-icon name="key" class="w-5 h-5 text-white" />
                        </div>
                        <p class="text-[0.65rem] font-bold tracking-[0.12em] uppercase text-brand-300 mb-1">Arrendatarios</p>
                        <p class="text-white font-semibold text-base leading-snug">Quiero rentar un inmueble</p>
                        <p class="mt-2 text-sm text-brand-300/70 leading-relaxed">Curación personalizada sin catálogos masivos. Solo opciones que coinciden con tu brief.</p>
                    </div>
                    <div class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-300 group-hover:text-white transition-colors duration-300">
                        Ver opciones
                        <x-icon name="arrow-right" class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1" />
                    </div>
                </a>

                {{-- Desarrollo e Inversión --}}
                <a href="{{ route('landing.desarrolladores') }}"
                   class="group relative flex flex-col justify-between bg-white/8 backdrop-blur-sm border border-white/12 rounded-2xl p-6 hover:bg-white/14 hover:border-white/25 hover:shadow-2xl transition-all duration-300 text-left">
                    <div>
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-white/10 group-hover:bg-brand-500 transition-all duration-300 mb-5">
                            <x-icon name="landmark" class="w-5 h-5 text-white" />
                        </div>
                        <p class="text-[0.65rem] font-bold tracking-[0.12em] uppercase text-brand-300 mb-1">Desarrollo e Inversión</p>
                        <p class="text-white font-semibold text-base leading-snug">Soy desarrollador o inversionista</p>
                        <p class="mt-2 text-sm text-brand-300/70 leading-relaxed">Captación de terrenos y producto terminado bajo demanda activa verificada.</p>
                    </div>
                    <div class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-300 group-hover:text-white transition-colors duration-300">
                        Solicitar brief
                        <x-icon name="arrow-right" class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1" />
                    </div>
                </a>

            </div>

            {{-- Trust metrics --}}
            <div class="mt-10 flex flex-wrap gap-x-10 gap-y-4 text-sm border-t border-white/8 pt-8">
                @php
                $trustMetrics = [
                    '30+ años de experiencia senior',
                    'Respuesta en menos de 24–72 horas',
                    'Seguridad jurídica en cada operación',
                    'Especialistas en Benito Juárez',
                ];
                @endphp
                @foreach($trustMetrics as $metric)
                <div class="flex items-center gap-2 text-brand-300/70">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    {{ $metric }}
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================ --}}
    {{-- 2. DIFERENCIADORES --}}
    {{-- ============================================ --}}
    @php
        $defaultBenefits = [
            ['icon' => 'location', 'title' => 'Dominio Territorial', 'description' => 'Especialización profunda en la Alcaldía Benito Juárez. Conocemos cada calle, cada oportunidad y cada tendencia del mercado local.'],
            ['icon' => 'shield', 'title' => 'Estructura Boutique', 'description' => 'Pocos inmuebles, más control. Atención personalizada con enfoque en ejecución y calidad, no en catálogo masivo.'],
            ['icon' => 'chart', 'title' => 'Inteligencia de Datos', 'description' => 'Herramientas tecnológicas para análisis de mercado, valuación precisa y automatización de flujos de trabajo.'],
            ['icon' => 'star', 'title' => 'Respaldo Senior', 'description' => 'Dirección con más de 30 años de experiencia técnica y legal en el sector inmobiliario de la Ciudad de México.'],
        ];
        $benefits = $siteSettings?->benefits_section ?? $defaultBenefits;
        $iconSvgs = [
            'shield' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
            'location' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>',
            'chart' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>',
            'clock' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'star' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>',
            'heart' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>',
        ];
    @endphp
    <section class="py-24 sm:py-32 bg-white" id="por-que-nosotros">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Ventajas competitivas</p>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">{{ $siteSettings?->benefits_heading ?? '¿Por qué Home del Valle?' }}</h2>
                <p class="mt-5 text-lg text-gray-500 leading-relaxed">{{ $siteSettings?->benefits_subheading ?? 'No somos una inmobiliaria de volumen. Somos una firma boutique que prioriza la calidad del inventario y la seguridad jurídica.' }}</p>
            </div>

            <div class="mt-16 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($benefits as $bi => $benefit)
                <div class="group relative text-center p-8 rounded-2xl bg-white border border-gray-200/60 hover:border-brand-200 hover:shadow-xl transition-all duration-500"
                     x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: {{ $bi * 100 }}ms">
                    <div class="mx-auto flex items-center justify-center w-20 h-20 rounded-3xl bg-brand-50 group-hover:bg-brand-500 transition-all duration-500 group-hover:shadow-brand group-hover:scale-105">
                        <svg class="w-10 h-10 text-brand-500 group-hover:text-white transition-colors duration-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">{!! $iconSvgs[$benefit['icon'] ?? 'shield'] ?? $iconSvgs['shield'] !!}</svg>
                    </div>
                    <h3 class="mt-7 text-lg font-bold text-gray-900 tracking-tight">{{ $benefit['title'] ?? '' }}</h3>
                    <p class="mt-3 text-sm text-gray-500 leading-relaxed">{{ $benefit['description'] ?? '' }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================ --}}
    {{-- 3. MODELO DE NEGOCIO --}}
    {{-- ============================================ --}}
    @php
        $defaultSteps = [
            ['num' => '01', 'title' => 'Identificamos la demanda', 'description' => 'Analizamos las necesidades de desarrolladores, inversionistas y compradores calificados.'],
            ['num' => '02', 'title' => 'Captamos activos estratégicos', 'description' => 'Buscamos propiedades que se alineen con los requerimientos técnicos y comerciales identificados.'],
            ['num' => '03', 'title' => 'Ejecutamos la operación', 'description' => 'Estrategia de salida rápida con red de contactos consolidada y blindaje jurídico completo.'],
        ];
        $bmSteps = $siteSettings?->business_model_steps ?? $defaultSteps;
    @endphp
    <section class="py-24 sm:py-32 bg-gray-50/60" id="modelo">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 lg:gap-20 items-center">
                {{-- Left: text --}}
                <div x-data x-intersect.once="$el.classList.add('animate-slide-in-left')">
                    <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Nuestro enfoque</p>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">{{ $siteSettings?->business_model_heading ?? 'Operamos desde la demanda, no desde la oferta' }}</h2>
                    <p class="mt-5 text-lg text-gray-600 leading-relaxed">
                        {{ $siteSettings?->business_model_subheading ?? 'A diferencia del modelo tradicional de volumen, nuestra operación no comienza con la oferta. Identificamos las necesidades de compradores e inversionistas calificados y captamos activos alineados a sus requerimientos.' }}
                    </p>
                    @if($siteSettings?->business_model_content)
                    <div class="mt-6 text-gray-600 leading-relaxed">{!! nl2br(e($siteSettings->business_model_content)) !!}</div>
                    @endif
                </div>

                {{-- Right: steps --}}
                <div class="space-y-6" x-data x-intersect.once="$el.classList.add('animate-slide-in-right')">
                    @foreach($bmSteps as $si => $step)
                    <div class="group flex gap-5 p-6 rounded-2xl bg-white border border-gray-200/80 hover:border-brand-200 hover:shadow-premium-lg transition-all duration-500" style="animation-delay: {{ $si * 120 }}ms">
                        <div class="flex items-center justify-center w-14 h-14 rounded-2xl gradient-brand text-white text-lg font-extrabold shrink-0 shadow-brand">
                            {{ $step['num'] ?? str_pad($si + 1, 2, '0', STR_PAD_LEFT) }}
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">{{ $step['title'] ?? '' }}</h3>
                            <p class="mt-2 text-sm text-gray-500 leading-relaxed">{{ $step['description'] ?? '' }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================ --}}
    {{-- 4. PROPIEDADES DESTACADAS --}}
    {{-- ============================================ --}}
    @if($featuredProperties->isNotEmpty())
    <section class="py-24 sm:py-32 bg-white" id="propiedades">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <div>
                    <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Inventario seleccionado</p>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">{{ $siteSettings?->featured_heading ?? 'Propiedades seleccionadas' }}</h2>
                    <p class="mt-3 text-lg text-gray-500">{{ $siteSettings?->featured_subheading ?? 'Cada inmueble es evaluado, analizado y comercializado con estrategia.' }}</p>
                </div>
                <a href="{{ route('propiedades.index') }}" class="group inline-flex items-center gap-2 text-sm font-semibold text-brand-600 hover:text-brand-700 transition-colors shrink-0">
                    Ver todas
                    <x-icon name="arrow-right" class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1" />
                </a>
            </div>

            <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-7">
                @foreach($featuredProperties as $pi => $property)
                <div x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: {{ $pi * 100 }}ms">
                    <x-public.property-card :property="$property" />
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ============================================ --}}
    {{-- 5. LÍNEAS DE NEGOCIO (4 funnels) --}}
    {{-- ============================================ --}}
    @php
        $defaultServices = [
            [
                'title'       => 'Vende tu propiedad',
                'label'       => 'Propietarios',
                'description' => 'Valuación profesional gratuita, compradores calificados y cierre seguro. Sin catálogo masivo, con atención personalizada.',
                'features'    => ['Valuación gratuita en 24 h', 'Venta en 45 días promedio', 'Seguridad jurídica completa'],
                'link_text'   => 'Solicitar valuación',
                'link_url'    => '/vende-tu-propiedad',
                'highlighted' => true,
                'icon'        => 'home',
            ],
            [
                'title'       => 'Compra tu propiedad',
                'label'       => 'Compradores',
                'description' => 'Búsqueda curada con propiedades verificadas en Benito Juárez. Te acompañamos desde el primer recorrido hasta la escritura.',
                'features'    => ['Solo opciones que coinciden con tu brief', 'Negociación y due diligence', 'Acompañamiento hasta el cierre'],
                'link_text'   => 'Iniciar búsqueda',
                'link_url'    => '/comprar',
                'highlighted' => false,
                'icon'        => 'search',
            ],
            [
                'title'       => 'Renta tu inmueble',
                'label'       => 'Propietarios en renta',
                'description' => 'Colocamos tu inmueble con el inquilino correcto, póliza jurídica activa y, si lo prefieres, administración integral incluida.',
                'features'    => ['Calificación rigurosa del inquilino', 'Póliza jurídica profesional', 'Administración integral opcional'],
                'link_text'   => 'Solicitar asesoría',
                'link_url'    => '/renta-tu-propiedad',
                'highlighted' => false,
                'icon'        => 'building-2',
            ],
            [
                'title'       => 'Renta para vivir',
                'label'       => 'Arrendatarios',
                'description' => 'Curación personalizada sin catálogos masivos. Te enviamos 3–5 opciones que realmente coinciden con tu perfil en menos de 72 horas.',
                'features'    => ['Sin spam ni portales masivos', 'Filtrado por zona, presupuesto y mascota', 'Acompañamiento en contrato y firma'],
                'link_text'   => 'Ver opciones',
                'link_url'    => '/rentar',
                'highlighted' => false,
                'icon'        => 'key',
            ],
        ];
        $services = $siteSettings?->services_section ?? $defaultServices;
    @endphp
    <section class="py-24 sm:py-32 bg-gray-50/60" id="servicios">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Servicio integral</p>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">{{ $siteSettings?->services_heading ?? 'Líneas de negocio' }}</h2>
                <p class="mt-5 text-lg text-gray-500 leading-relaxed">{{ $siteSettings?->services_subheading ?? 'Cuatro funnels especializados para cada perfil del mercado inmobiliario.' }}</p>
            </div>

            <div class="mt-16 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($services as $si => $service)
                <div x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: {{ $si * 80 }}ms">
                @if(!empty($service['highlighted']))
                {{-- Card destacada --}}
                <div class="relative rounded-2xl p-7 gradient-brand text-white shadow-brand-lg overflow-hidden h-full flex flex-col">
                    <div class="absolute top-0 right-0 w-36 h-36 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl"></div>
                    <div class="absolute -top-3 right-5">
                        <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-[0.65rem] font-bold text-brand-700 shadow-premium">★ Más solicitado</span>
                    </div>
                    <div class="relative flex flex-col flex-1">
                        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-white/15 backdrop-blur-sm mb-6">
                            <x-icon name="{{ $service['icon'] ?? 'home' }}" class="w-6 h-6 text-white" />
                        </div>
                        <p class="text-[0.6rem] font-bold tracking-[0.12em] uppercase text-white/60 mb-1">{{ $service['label'] ?? '' }}</p>
                        <h3 class="text-lg font-bold leading-snug">{{ $service['title'] ?? '' }}</h3>
                        <p class="mt-3 text-sm text-white/70 leading-relaxed flex-1">{{ $service['description'] ?? '' }}</p>
                        <ul class="mt-6 space-y-2.5">
                            @foreach(($service['features'] ?? []) as $feature)
                            <li class="flex items-start gap-2.5 text-sm text-white/80">
                                <x-icon name="check" class="w-4 h-4 mt-0.5 text-white/50 shrink-0" />
                                {{ $feature }}
                            </li>
                            @endforeach
                        </ul>
                        <a href="{{ $service['link_url'] ?? '/servicios' }}" class="group/link mt-7 inline-flex items-center gap-2 text-sm font-semibold text-white hover:underline underline-offset-4">
                            {{ $service['link_text'] ?? 'Conocer más' }}
                            <x-icon name="arrow-right" class="w-4 h-4 transition-transform duration-300 group-hover/link:translate-x-1" />
                        </a>
                    </div>
                </div>
                @else
                {{-- Card normal --}}
                <div class="group relative rounded-2xl border border-gray-200/80 p-7 hover:border-brand-200 hover:shadow-premium-lg transition-all duration-500 bg-white h-full flex flex-col">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-brand-50 group-hover:bg-brand-500 transition-all duration-500 group-hover:shadow-brand group-hover:scale-105 mb-6">
                        <x-icon name="{{ $service['icon'] ?? 'home' }}" class="w-6 h-6 text-brand-500 group-hover:text-white transition-colors duration-500" />
                    </div>
                    <p class="text-[0.6rem] font-bold tracking-[0.12em] uppercase text-brand-400 mb-1">{{ $service['label'] ?? '' }}</p>
                    <h3 class="text-lg font-bold text-gray-900 leading-snug">{{ $service['title'] ?? '' }}</h3>
                    <p class="mt-3 text-sm text-gray-500 leading-relaxed flex-1">{{ $service['description'] ?? '' }}</p>
                    <ul class="mt-6 space-y-2.5">
                        @foreach(($service['features'] ?? []) as $feature)
                        <li class="flex items-start gap-2.5 text-sm text-gray-600">
                            <x-icon name="check" class="w-4 h-4 mt-0.5 text-brand-400 shrink-0" />
                            {{ $feature }}
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ $service['link_url'] ?? '/servicios' }}" class="group/link mt-7 inline-flex items-center gap-2 text-sm font-semibold text-brand-600 hover:text-brand-700">
                        {{ $service['link_text'] ?? 'Conocer más' }}
                        <x-icon name="arrow-right" class="w-4 h-4 transition-transform duration-300 group-hover/link:translate-x-1" />
                    </a>
                </div>
                @endif
                </div>
                @endforeach
            </div>

            <div class="mt-10 text-center" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <a href="{{ route('servicios') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-600 hover:text-brand-700 transition-colors">
                    Ver todos los servicios
                    <x-icon name="arrow-right" class="w-4 h-4" />
                </a>
            </div>
        </div>
    </section>

    {{-- ============================================ --}}
    {{-- 6. ESTADISTICAS DE CONFIANZA --}}
    {{-- ============================================ --}}
    @php
        $defaultStats = [
            ['value' => '30+', 'label' => 'Años de experiencia senior'],
            ['value' => '12+', 'label' => 'Propiedades gestionadas'],
            ['value' => '98%', 'label' => 'Clientes satisfechos'],
            ['value' => '50+', 'label' => 'Operaciones al año'],
        ];
        $stats = $siteSettings?->stats_section ?? $defaultStats;
    @endphp
    <section class="py-20 sm:py-24 bg-brand-950 relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_rgba(59,130,196,0.1)_0%,_transparent_70%)]"></div>
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">{{ $siteSettings?->stats_heading ?? 'Resultados que hablan' }}</h2>
                <p class="mt-4 text-lg text-brand-200/60">{{ $siteSettings?->stats_subheading ?? 'Números que respaldan nuestra trayectoria.' }}</p>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12">
                @foreach($stats as $sti => $stat)
                <div class="text-center" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: {{ $sti * 100 }}ms">
                    <p class="text-4xl sm:text-5xl font-extrabold text-white tracking-tight">{{ $stat['value'] ?? '' }}</p>
                    <p class="mt-2 text-sm text-brand-300/60 font-medium">{{ $stat['label'] ?? '' }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================ --}}
    {{-- 7. TESTIMONIOS --}}
    {{-- ============================================ --}}
    @php
        $defaultTestimonials = [
            ['name' => 'María González', 'role' => 'Vendedora en Del Valle', 'text' => 'Vendieron mi departamento en tiempo récord. La estrategia fue impecable y el acompañamiento legal me dio total tranquilidad durante todo el proceso.', 'initials' => 'MG'],
            ['name' => 'Carlos Ramírez', 'role' => 'Desarrollador inmobiliario', 'text' => 'Su conocimiento del mercado en Benito Juárez es excepcional. Encontraron el predio perfecto para nuestro proyecto en menos de tres semanas.', 'initials' => 'CR'],
            ['name' => 'Ana Martínez', 'role' => 'Inversionista', 'text' => 'El enfoque boutique hace toda la diferencia. No son un catálogo masivo, realmente entienden lo que necesitas y entregan resultados consistentes.', 'initials' => 'AM'],
        ];
        $useDbTestimonials = isset($homeTestimonials) && $homeTestimonials->isNotEmpty();
    @endphp
    <section class="py-24 sm:py-32 bg-white" id="testimonios">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Testimonios</p>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">{{ $siteSettings?->testimonials_heading ?? 'Lo que dicen nuestros clientes' }}</h2>
                <p class="mt-5 text-lg text-gray-500 leading-relaxed">{{ $siteSettings?->testimonials_subheading ?? 'La satisfacción de nuestros clientes es nuestra mejor carta de presentación.' }}</p>
            </div>

            <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8">
                @if($useDbTestimonials)
                    @foreach($homeTestimonials as $ti => $t)
                    <article class="group relative rounded-2xl bg-white border border-gray-200/60 p-8 lg:p-10 hover:shadow-premium-lg hover:border-brand-100 transition-all duration-500"
                             x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: {{ $ti * 120 }}ms">
                        <svg class="absolute top-8 right-8 w-10 h-10 text-brand-100 group-hover:text-brand-200 transition-colors duration-500" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10H14.017zM0 21v-7.391c0-5.704 3.731-9.57 8.983-10.609L9.978 5.151c-2.432.917-3.995 3.638-3.995 5.849h4v10H0z"/></svg>
                        <div class="relative">
                            <div class="flex items-center gap-0.5 mb-6">
                                @for($i = 0; $i < $t->rating; $i++)
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                            </div>
                            @if($t->content)
                                <blockquote class="text-[15px] text-gray-600 leading-relaxed">"{{ Str::limit($t->content, 200) }}"</blockquote>
                            @endif
                            <div class="mt-8 flex items-center gap-3.5">
                                <div class="flex items-center justify-center w-11 h-11 rounded-full overflow-hidden flex-shrink-0 {{ $t->avatar ? '' : 'gradient-brand text-white text-xs font-bold shadow-brand/30' }}">
                                    @if($t->avatar)
                                        <img src="{{ Storage::url($t->avatar) }}" alt="{{ $t->name }}" class="w-full h-full object-cover">
                                    @else
                                        {{ strtoupper(substr($t->name, 0, 1)) . strtoupper(substr(explode(' ', $t->name)[1] ?? '', 0, 1)) }}
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">{{ $t->name }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $t->role ?? $t->location ?? '' }}</p>
                                </div>
                            </div>
                        </div>
                    </article>
                    @endforeach
                @else
                    @foreach($defaultTestimonials as $ti => $testimonial)
                    <article class="group relative rounded-2xl bg-white border border-gray-200/60 p-8 lg:p-10 hover:shadow-premium-lg hover:border-brand-100 transition-all duration-500"
                             x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: {{ $ti * 120 }}ms">
                        <svg class="absolute top-8 right-8 w-10 h-10 text-brand-100 group-hover:text-brand-200 transition-colors duration-500" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10H14.017zM0 21v-7.391c0-5.704 3.731-9.57 8.983-10.609L9.978 5.151c-2.432.917-3.995 3.638-3.995 5.849h4v10H0z"/></svg>
                        <div class="relative">
                            <div class="flex items-center gap-0.5 mb-6">
                                @for($i = 0; $i < 5; $i++)
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                            </div>
                            <blockquote class="text-[15px] text-gray-600 leading-relaxed">"{{ $testimonial['text'] ?? '' }}"</blockquote>
                            <div class="mt-8 flex items-center gap-3.5">
                                <div class="flex items-center justify-center w-11 h-11 rounded-full gradient-brand text-white text-xs font-bold shadow-brand/30">{{ $testimonial['initials'] ?? '' }}</div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">{{ $testimonial['name'] ?? '' }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $testimonial['role'] ?? '' }}</p>
                                </div>
                            </div>
                        </div>
                    </article>
                    @endforeach
                @endif
            </div>

            @if($useDbTestimonials)
            <div class="mt-10 text-center">
                <a href="{{ route('testimonios') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-600 hover:text-brand-700 transition-colors">
                    Ver todos los testimonios <x-icon name="arrow-right" class="w-4 h-4" />
                </a>
            </div>
            @endif
        </div>
    </section>

    {{-- ============================================ --}}
    {{-- 8. CTA CAPTACION --}}
    {{-- ============================================ --}}
    <section class="py-24 sm:py-32 gradient-brand-soft relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_rgba(59,130,196,0.06)_0%,_transparent_70%)]"></div>
        <div class="relative mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 text-center" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">{{ $siteSettings?->cta_heading ?? '¿Listo para vender tu propiedad?' }}</h2>
            <p class="mt-5 text-lg text-gray-500 leading-relaxed">{{ $siteSettings?->cta_subheading ?? 'Recibe una valuación profesional gratuita en menos de 24 horas. Sin compromiso, sin letra chica. Solo necesitamos los datos básicos de tu inmueble.' }}</p>
            <div class="mt-10 flex flex-wrap justify-center gap-4">
                <a href="{{ route('landing.vende') }}" class="group inline-flex items-center gap-2.5 rounded-xl gradient-brand px-8 py-4 text-sm font-semibold text-white shadow-brand-lg hover:shadow-brand hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300">
                    Solicita tu valuación gratuita
                    <x-icon name="arrow-right" class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1" />
                </a>
                @if($siteSettings?->whatsapp_number)
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $siteSettings->whatsapp_number) }}?text={{ urlencode('Hola, quiero vender mi propiedad y me gustaría una valuación.') }}" target="_blank" rel="noopener noreferrer" class="group inline-flex items-center gap-2.5 rounded-xl bg-white border border-gray-200 px-8 py-4 text-sm font-semibold text-gray-700 hover:border-brand-200 hover:text-brand-600 hover:shadow-premium transition-all duration-300">
                    <x-icon name="brands/whatsapp" class="w-4 h-4 text-[#25D366]" />
                    WhatsApp
                </a>
                @endif
            </div>
        </div>
    </section>

    {{-- ============================================ --}}
    {{-- 9. BLOG PREVIEW --}}
    {{-- ============================================ --}}
    @if($latestPosts->isNotEmpty())
    <section class="py-24 sm:py-32 bg-white" id="blog">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <div>
                    <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Recursos</p>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">{{ $siteSettings?->blog_heading ?? 'Últimos artículos' }}</h2>
                    <p class="mt-3 text-lg text-gray-500">{{ $siteSettings?->blog_subheading ?? 'Consejos, tendencias y guías del mercado inmobiliario en Benito Juárez.' }}</p>
                </div>
                <a href="{{ route('blog.index') }}" class="group inline-flex items-center gap-2 text-sm font-semibold text-brand-600 hover:text-brand-700 transition-colors shrink-0">
                    Ver todos
                    <x-icon name="arrow-right" class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1" />
                </a>
            </div>

            <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($latestPosts as $pi => $post)
                <article class="group" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: {{ $pi * 100 }}ms">
                    <a href="{{ route('blog.show', $post->slug) }}" class="block">
                        <div class="aspect-[16/10] rounded-2xl overflow-hidden bg-gray-100 shadow-premium group-hover:shadow-premium-lg transition-all duration-500">
                            @if($post->featured_image)
                                <picture>
                                    @if($post->featured_image_webp_md)
                                    <source type="image/webp" srcset="{{ $post->featured_image_webp_md }}">
                                    @endif
                                    <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover img-zoom" loading="lazy" width="700" height="438">
                                </picture>
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-brand-50 to-brand-100 flex items-center justify-center">
                                    <svg class="w-10 h-10 text-brand-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                                </div>
                            @endif
                        </div>
                        <div class="mt-5">
                            @if($post->category)
                                <span class="text-xs font-bold text-brand-500 uppercase tracking-wider">{{ $post->category->name }}</span>
                            @endif
                            <h3 class="mt-2 text-lg font-bold text-gray-900 group-hover:text-brand-600 transition-colors duration-300 line-clamp-2">{{ $post->title }}</h3>
                            @if($post->excerpt)
                                <p class="mt-2.5 text-sm text-gray-500 line-clamp-2 leading-relaxed">{{ $post->excerpt }}</p>
                            @endif
                            <time datetime="{{ $post->published_at?->toDateString() }}" class="mt-4 block text-xs text-gray-400 font-medium">
                                {{ $post->published_at?->translatedFormat('d M, Y') }}
                            </time>
                        </div>
                    </a>
                </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ============================================ --}}
    {{-- 10. CONTACTO + FORMULARIO --}}
    {{-- ============================================ --}}
    <section class="py-24 sm:py-32 bg-brand-950 relative overflow-hidden" id="contacto">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_left,_rgba(59,130,196,0.12)_0%,_transparent_60%)]"></div>
        <div class="absolute top-0 right-0 w-96 h-96 bg-brand-500/5 rounded-full blur-3xl"></div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 lg:gap-20 items-center">
                <div x-data x-intersect.once="$el.classList.add('animate-slide-in-left')">
                    <p class="text-sm font-semibold text-brand-400 uppercase tracking-widest mb-4">Contáctanos</p>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight leading-tight">{{ $siteSettings?->contact_heading ?? 'Cuéntanos qué necesitas.' }}</h2>
                    <p class="mt-5 text-lg text-brand-200/70 leading-relaxed">{{ $siteSettings?->contact_subheading ?? 'Ya sea que quieras vender, comprar o explorar una inversión, respondemos en menos de 24 horas hábiles sin compromiso.' }}</p>

                    <div class="mt-12 space-y-6">
                        @if($siteSettings?->contact_phone)
                        <a href="tel:{{ $siteSettings->contact_phone }}" class="group flex items-center gap-4 p-4 rounded-2xl hover:bg-white/5 transition-colors duration-300">
                            <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-brand-500/15 group-hover:bg-brand-500/25 transition-colors">
                                <x-icon name="phone" class="w-5 h-5 text-brand-400" />
                            </div>
                            <div>
                                <p class="text-xs text-brand-400/60 uppercase tracking-wider font-medium">Teléfono</p>
                                <p class="text-white font-semibold mt-0.5 group-hover:text-brand-200 transition-colors">{{ $siteSettings->contact_phone }}</p>
                            </div>
                        </a>
                        @endif
                        @if($siteSettings?->contact_email)
                        <a href="mailto:{{ $siteSettings->contact_email }}" class="group flex items-center gap-4 p-4 rounded-2xl hover:bg-white/5 transition-colors duration-300">
                            <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-brand-500/15 group-hover:bg-brand-500/25 transition-colors">
                                <x-icon name="mail" class="w-5 h-5 text-brand-400" />
                            </div>
                            <div>
                                <p class="text-xs text-brand-400/60 uppercase tracking-wider font-medium">Email</p>
                                <p class="text-white font-semibold mt-0.5 group-hover:text-brand-200 transition-colors">{{ $siteSettings->contact_email }}</p>
                            </div>
                        </a>
                        @endif
                        @if($siteSettings?->address)
                        <div class="flex items-center gap-4 p-4">
                            <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-brand-500/15">
                                <x-icon name="map-pin" class="w-5 h-5 text-brand-400" />
                            </div>
                            <div>
                                <p class="text-xs text-brand-400/60 uppercase tracking-wider font-medium">Dirección</p>
                                <p class="text-white font-semibold mt-0.5">{{ $siteSettings->address }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="rounded-2xl bg-white p-8 lg:p-10 shadow-premium-xl" x-data x-intersect.once="$el.classList.add('animate-slide-in-right')">
                    <h3 class="text-xl font-bold text-gray-900">¿En qué te podemos ayudar?</h3>
                    <p class="text-sm text-gray-500 mt-1.5 mb-8">Responderemos en menos de 24 horas hábiles.</p>
                    @livewire('forms.contact-segmented-form')
                </div>
            </div>
        </div>
    </section>

@endsection
