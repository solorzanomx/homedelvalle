@extends('layouts.public')

@section('meta')
    <x-public.seo-meta
        title="Firma Inmobiliaria Boutique en Benito Juarez — {{ $siteSettings?->site_name ?? 'Home del Valle' }}"
        description="Pocos inmuebles. Mas control. Mejores resultados. Consultora especializada en captacion estrategica y comercializacion de propiedades de alto valor en la Benito Juarez, CDMX."
        :canonical="url('/')"
    />
    <x-public.json-ld type="RealEstateAgent" :data="[
        'name' => $siteSettings?->site_name ?? 'Home del Valle',
        'description' => $siteSettings?->site_tagline ?? 'Firma inmobiliaria boutique de alta precision',
        'url' => url('/'),
        'telephone' => $siteSettings?->contact_phone,
        'email' => $siteSettings?->contact_email,
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => 'Ciudad de Mexico',
            'addressRegion' => 'Benito Juarez',
            'addressCountry' => 'MX',
            'streetAddress' => $siteSettings?->address ?? '',
        ],
        'areaServed' => [
            '@type' => 'City',
            'name' => 'Ciudad de Mexico',
        ],
    ]" />
@endsection

@section('content')

    {{-- ============================================ --}}
    {{-- 1. HERO SECTION --}}
    {{-- ============================================ --}}
    <section class="relative overflow-hidden bg-brand-950" id="inicio">
        @if($siteSettings?->hero_image_path)
        <img src="{{ asset('storage/' . $siteSettings->hero_image_path) }}" alt="Propiedades en Benito Juarez" class="absolute inset-0 w-full h-full object-cover opacity-20">
        @endif

        <div class="absolute inset-0 bg-gradient-to-br from-brand-950 via-brand-900/90 to-brand-800/80"></div>
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_rgba(59,130,196,0.15)_0%,_transparent_60%)]"></div>
        <div class="absolute top-20 right-10 w-72 h-72 bg-brand-500/10 rounded-full blur-3xl animate-float"></div>
        <div class="absolute bottom-20 left-10 w-96 h-96 bg-brand-400/5 rounded-full blur-3xl animate-float animation-delay-300"></div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-24 pb-32 sm:pt-32 sm:pb-40 lg:pt-40 lg:pb-48"
             x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">

            <div class="inline-flex items-center gap-2 rounded-full bg-white/10 border border-white/10 px-4 py-1.5 text-sm text-brand-200 backdrop-blur-sm mb-8">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                {{ $siteSettings?->hero_badge ?? 'Inmobiliaria boutique en Benito Juarez' }}
            </div>

            <div class="max-w-3xl">
                <h1 class="text-4xl sm:text-5xl lg:text-[3.5rem] font-extrabold text-white tracking-tight leading-[1.08]">
                    {{ $siteSettings?->hero_heading ?? 'Pocos inmuebles. Mas control. Mejores resultados.' }}
                </h1>
                <p class="mt-6 text-lg sm:text-xl text-brand-200/80 max-w-2xl leading-relaxed">
                    {{ $siteSettings?->hero_subheading ?? 'Consultora inmobiliaria especializada en captacion estrategica, analisis y comercializacion de propiedades de alto valor en la Benito Juarez, CDMX.' }}
                </p>
                <div class="mt-10 flex flex-wrap gap-4">
                    <a href="{{ $siteSettings?->hero_cta_url ?? route('landing.vende') }}" class="group inline-flex items-center gap-2.5 rounded-xl bg-white px-7 py-4 text-sm font-semibold text-brand-900 hover:bg-brand-50 transition-all duration-300 shadow-premium-xl hover:-translate-y-0.5 active:translate-y-0">
                        {{ $siteSettings?->hero_cta_text ?? 'Valua tu propiedad' }}
                        <svg class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                    <a href="{{ $siteSettings?->hero_secondary_cta_url ?? route('propiedades.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/20 px-7 py-4 text-sm font-semibold text-white hover:bg-white/10 hover:border-white/40 transition-all duration-300">
                        {{ $siteSettings?->hero_secondary_cta_text ?? 'Ver propiedades' }}
                    </a>
                </div>
            </div>

            {{-- Search bar --}}
            <form action="{{ route('propiedades.index') }}" method="GET" class="mt-14 max-w-4xl" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <div class="glass rounded-2xl p-2.5 shadow-premium-xl border border-white/10">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
                        <select name="operation_type" class="rounded-xl border-0 bg-white/80 px-4 py-3.5 text-sm text-gray-900 focus:ring-2 focus:ring-brand-500/30 transition-shadow">
                            <option value="">Operacion</option>
                            <option value="sale">Comprar</option>
                            <option value="rental">Rentar</option>
                        </select>
                        <select name="property_type" class="rounded-xl border-0 bg-white/80 px-4 py-3.5 text-sm text-gray-900 focus:ring-2 focus:ring-brand-500/30 transition-shadow">
                            <option value="">Tipo de propiedad</option>
                            <option value="House">Casa</option>
                            <option value="Apartment">Departamento</option>
                            <option value="Land">Terreno</option>
                            <option value="Office">Oficina</option>
                            <option value="Commercial">Comercial</option>
                        </select>
                        <input type="text" name="search" placeholder="Colonia o zona..." class="rounded-xl border-0 bg-white/80 px-4 py-3.5 text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-brand-500/30 transition-shadow">
                        <button type="submit" class="rounded-xl gradient-brand px-6 py-3.5 text-sm font-semibold text-white hover:opacity-90 transition-all duration-200 shadow-brand flex items-center justify-center gap-2.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            Buscar
                        </button>
                    </div>
                </div>
            </form>

            {{-- Trust metrics --}}
            <div class="mt-12 flex flex-wrap gap-x-10 gap-y-4 text-sm" x-data x-intersect.once="$el.classList.add('animate-fade-in')">
                <div class="flex items-center gap-2 text-brand-300/70">
                    <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Especialistas en Benito Juarez
                </div>
                <div class="flex items-center gap-2 text-brand-300/70">
                    <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Asesoria sin costo
                </div>
                <div class="flex items-center gap-2 text-brand-300/70">
                    <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Seguridad juridica en cada operacion
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================ --}}
    {{-- 2. DIFERENCIADORES --}}
    {{-- ============================================ --}}
    @php
        $defaultBenefits = [
            ['icon' => 'location', 'title' => 'Dominio Territorial', 'description' => 'Especializacion profunda en la Alcaldia Benito Juarez. Conocemos cada calle, cada oportunidad y cada tendencia del mercado local.'],
            ['icon' => 'shield', 'title' => 'Estructura Boutique', 'description' => 'Pocos inmuebles, mas control. Atencion personalizada con enfoque en ejecucion y calidad, no en catalogo masivo.'],
            ['icon' => 'chart', 'title' => 'Inteligencia de Datos', 'description' => 'Herramientas tecnologicas para analisis de mercado, valuacion precisa y automatizacion de flujos de trabajo.'],
            ['icon' => 'star', 'title' => 'Respaldo Senior', 'description' => 'Direccion con mas de 30 años de experiencia tecnica y legal en el sector inmobiliario de la Ciudad de Mexico.'],
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
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">{{ $siteSettings?->benefits_heading ?? '¿Por que Home del Valle?' }}</h2>
                <p class="mt-5 text-lg text-gray-500 leading-relaxed">{{ $siteSettings?->benefits_subheading ?? 'No somos una inmobiliaria de volumen. Somos una firma boutique que prioriza la calidad del inventario y la seguridad juridica.' }}</p>
            </div>

            <div class="mt-20 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-6">
                @foreach($benefits as $bi => $benefit)
                <div class="group relative text-center p-8 rounded-2xl border border-transparent hover:border-brand-100 hover:bg-brand-50/30 transition-all duration-500"
                     x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: {{ $bi * 100 }}ms">
                    <div class="mx-auto flex items-center justify-center w-16 h-16 rounded-2xl bg-brand-50 group-hover:bg-brand-500 transition-all duration-500 group-hover:shadow-brand group-hover:scale-110">
                        <svg class="w-7 h-7 text-brand-500 group-hover:text-white transition-colors duration-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $iconSvgs[$benefit['icon'] ?? 'shield'] ?? $iconSvgs['shield'] !!}</svg>
                    </div>
                    <h3 class="mt-6 text-base font-bold text-gray-900 tracking-tight">{{ $benefit['title'] ?? '' }}</h3>
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
            ['num' => '02', 'title' => 'Captamos activos estrategicos', 'description' => 'Buscamos propiedades que se alineen con los requerimientos tecnicos y comerciales identificados.'],
            ['num' => '03', 'title' => 'Ejecutamos la operacion', 'description' => 'Estrategia de salida rapida con red de contactos consolidada y blindaje juridico completo.'],
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
                        {{ $siteSettings?->business_model_subheading ?? 'A diferencia del modelo tradicional de volumen, nuestra operacion no comienza con la oferta. Identificamos las necesidades de compradores e inversionistas calificados y captamos activos alineados a sus requerimientos.' }}
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
                    <svg class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
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
    {{-- 5. SERVICIOS (5 lineas de negocio) --}}
    {{-- ============================================ --}}
    @php
        $defaultServices = [
            ['title' => 'Desarrollo Inmobiliario', 'description' => 'Captacion y colocacion de predios con potencial habitacional o comercial.', 'features' => ['Captacion de terrenos', 'Analisis de potencial', 'Vinculacion con desarrolladores'], 'link_text' => 'Conocer mas', 'link_url' => '/servicios#desarrollo-inmobiliario', 'highlighted' => false],
            ['title' => 'Corretaje Premium', 'description' => 'Venta y renta de propiedades residenciales y comerciales seleccionadas con estrategia personalizada.', 'features' => ['Propiedades seleccionadas', 'Red de compradores calificados', 'Negociacion profesional'], 'link_text' => 'Conocer mas', 'link_url' => '/servicios#corretaje-premium', 'highlighted' => true],
            ['title' => 'Administracion', 'description' => 'Gestion profesional de activos inmobiliarios para maximizar tu inversion.', 'features' => ['Gestion de inquilinos', 'Reportes financieros', 'Mantenimiento integral'], 'link_text' => 'Conocer mas', 'link_url' => '/servicios#administracion', 'highlighted' => false],
            ['title' => 'Legal y Gestoria', 'description' => 'Regularizacion documental, sucesiones y blindaje juridico en escrituracion.', 'features' => ['Regularizacion de escrituras', 'Tramites de sucesion', 'Blindaje juridico'], 'link_text' => 'Conocer mas', 'link_url' => '/servicios#legal-gestoria', 'highlighted' => false],
            ['title' => 'Property Transformation', 'description' => 'Home staging y acondicionamiento estrategico para acelerar la venta.', 'features' => ['Home staging profesional', 'Mejoras esteticas', 'Fotografia profesional'], 'link_text' => 'Conocer mas', 'link_url' => '/servicios#property-transformation', 'highlighted' => false],
        ];
        $services = $siteSettings?->services_section ?? $defaultServices;
        $serviceIcons = [
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>',
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>',
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>',
        ];
    @endphp
    <section class="py-24 sm:py-32 bg-gray-50/60" id="servicios">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Servicio integral</p>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">{{ $siteSettings?->services_heading ?? 'Lineas de negocio' }}</h2>
                <p class="mt-5 text-lg text-gray-500 leading-relaxed">{{ $siteSettings?->services_subheading ?? 'Soluciones completas para cada etapa del ciclo inmobiliario.' }}</p>
            </div>

            <div class="mt-16 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($services as $si => $service)
                <div x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: {{ $si * 100 }}ms">
                @if(!empty($service['highlighted']))
                <div class="relative rounded-2xl p-8 lg:p-10 gradient-brand text-white shadow-brand-lg overflow-hidden h-full">
                    <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl"></div>
                    <div class="absolute -top-3 right-6">
                        <span class="inline-flex items-center rounded-full bg-white px-3.5 py-1 text-xs font-bold text-brand-700 shadow-premium">Mas solicitado</span>
                    </div>
                    <div class="relative">
                        <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-white/15 backdrop-blur-sm">
                            <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $serviceIcons[$si] ?? $serviceIcons[0] !!}</svg>
                        </div>
                        <h3 class="mt-7 text-xl font-bold">{{ $service['title'] ?? '' }}</h3>
                        <p class="mt-3 text-sm text-white/70 leading-relaxed">{{ $service['description'] ?? '' }}</p>
                        <ul class="mt-7 space-y-3">
                            @foreach(($service['features'] ?? []) as $feature)
                            <li class="flex items-start gap-3 text-sm text-white/80">
                                <svg class="w-5 h-5 mt-0.5 text-white/50 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ $feature }}
                            </li>
                            @endforeach
                        </ul>
                        <a href="{{ $service['link_url'] ?? '/servicios' }}" class="group/link mt-8 inline-flex items-center gap-2 text-sm font-semibold text-white hover:underline underline-offset-4">
                            {{ $service['link_text'] ?? 'Conocer mas' }}
                            <svg class="w-4 h-4 transition-transform duration-300 group-hover/link:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                    </div>
                </div>
                @else
                <div class="group relative rounded-2xl border border-gray-200/80 p-8 lg:p-10 hover:border-brand-200 hover:shadow-premium-lg transition-all duration-500 bg-white h-full">
                    <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-50 group-hover:bg-brand-500 transition-all duration-500 group-hover:shadow-brand group-hover:scale-105">
                        <svg class="w-7 h-7 text-brand-500 group-hover:text-white transition-colors duration-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $serviceIcons[$si] ?? $serviceIcons[0] !!}</svg>
                    </div>
                    <h3 class="mt-7 text-xl font-bold text-gray-900">{{ $service['title'] ?? '' }}</h3>
                    <p class="mt-3 text-sm text-gray-500 leading-relaxed">{{ $service['description'] ?? '' }}</p>
                    <ul class="mt-7 space-y-3">
                        @foreach(($service['features'] ?? []) as $feature)
                        <li class="flex items-start gap-3 text-sm text-gray-600">
                            <svg class="w-5 h-5 mt-0.5 text-brand-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            {{ $feature }}
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ $service['link_url'] ?? '/servicios' }}" class="group/link mt-8 inline-flex items-center gap-2 text-sm font-semibold text-brand-600 hover:text-brand-700">
                        {{ $service['link_text'] ?? 'Conocer mas' }}
                        <svg class="w-4 h-4 transition-transform duration-300 group-hover/link:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                </div>
                @endif
                </div>
                @endforeach
            </div>

            <div class="mt-12 text-center" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <a href="{{ route('servicios') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-600 hover:text-brand-700 transition-colors">
                    Ver todos los servicios
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
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
            ['value' => '200+', 'label' => 'Propiedades gestionadas'],
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
                <p class="mt-4 text-lg text-brand-200/60">{{ $siteSettings?->stats_subheading ?? 'Numeros que respaldan nuestra trayectoria.' }}</p>
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
            ['name' => 'Maria Gonzalez', 'role' => 'Vendedora en Del Valle', 'text' => 'Vendieron mi departamento en tiempo record. La estrategia fue impecable y el acompañamiento legal me dio total tranquilidad durante todo el proceso.', 'initials' => 'MG'],
            ['name' => 'Carlos Ramirez', 'role' => 'Desarrollador inmobiliario', 'text' => 'Su conocimiento del mercado en Benito Juarez es excepcional. Encontraron el predio perfecto para nuestro proyecto en menos de tres semanas.', 'initials' => 'CR'],
            ['name' => 'Ana Martinez', 'role' => 'Inversionista', 'text' => 'El enfoque boutique hace toda la diferencia. No son un catalogo masivo, realmente entienden lo que necesitas y entregan resultados consistentes.', 'initials' => 'AM'],
        ];
        $testimonials = $siteSettings?->testimonials_section ?? $defaultTestimonials;
    @endphp
    <section class="py-24 sm:py-32 bg-white" id="testimonios">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Testimonios</p>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">{{ $siteSettings?->testimonials_heading ?? 'Lo que dicen nuestros clientes' }}</h2>
                <p class="mt-5 text-lg text-gray-500 leading-relaxed">{{ $siteSettings?->testimonials_subheading ?? 'La satisfaccion de nuestros clientes es nuestra mejor carta de presentacion.' }}</p>
            </div>

            <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach($testimonials as $ti => $testimonial)
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
            </div>
        </div>
    </section>

    {{-- ============================================ --}}
    {{-- 8. CTA CAPTACION --}}
    {{-- ============================================ --}}
    <section class="py-24 sm:py-32 gradient-brand-soft relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_rgba(59,130,196,0.06)_0%,_transparent_70%)]"></div>
        <div class="relative mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 text-center" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">{{ $siteSettings?->cta_heading ?? '¿Tienes una propiedad en la Benito Juarez?' }}</h2>
            <p class="mt-5 text-lg text-gray-500 leading-relaxed">{{ $siteSettings?->cta_subheading ?? 'Conoce cuanto vale tu inmueble con una valuacion profesional gratuita y sin compromiso. Pocos inmuebles, mejores resultados.' }}</p>
            <div class="mt-10 flex flex-wrap justify-center gap-4">
                <a href="{{ route('landing.vende') }}" class="group inline-flex items-center gap-2.5 rounded-xl gradient-brand px-8 py-4 text-sm font-semibold text-white shadow-brand-lg hover:shadow-brand hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300">
                    Valua tu propiedad
                    <svg class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
                @if($siteSettings?->whatsapp_number)
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $siteSettings->whatsapp_number) }}?text={{ urlencode('Hola, me interesa una valuacion de mi propiedad.') }}" target="_blank" rel="noopener noreferrer" class="group inline-flex items-center gap-2.5 rounded-xl bg-white border border-gray-200 px-8 py-4 text-sm font-semibold text-gray-700 hover:border-brand-200 hover:text-brand-600 hover:shadow-premium transition-all duration-300">
                    <svg class="w-4 h-4 text-[#25D366]" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
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
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">{{ $siteSettings?->blog_heading ?? 'Ultimos articulos' }}</h2>
                    <p class="mt-3 text-lg text-gray-500">{{ $siteSettings?->blog_subheading ?? 'Consejos, tendencias y guias del mercado inmobiliario en Benito Juarez.' }}</p>
                </div>
                <a href="{{ route('blog.index') }}" class="group inline-flex items-center gap-2 text-sm font-semibold text-brand-600 hover:text-brand-700 transition-colors shrink-0">
                    Ver todos
                    <svg class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>

            <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($latestPosts as $pi => $post)
                <article class="group" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: {{ $pi * 100 }}ms">
                    <a href="{{ route('blog.show', $post->slug) }}" class="block">
                        <div class="aspect-[16/10] rounded-2xl overflow-hidden bg-gray-100 shadow-premium group-hover:shadow-premium-lg transition-all duration-500">
                            @if($post->featured_image)
                                <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover img-zoom" loading="lazy">
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
                    <p class="text-sm font-semibold text-brand-400 uppercase tracking-widest mb-4">Hablemos</p>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight leading-tight">{{ $siteSettings?->contact_heading ?? '¿Listo para dar el siguiente paso?' }}</h2>
                    <p class="mt-5 text-lg text-brand-200/70 leading-relaxed">{{ $siteSettings?->contact_subheading ?? 'Ya sea que busques comprar, vender o invertir, nuestro equipo de expertos esta aqui para ayudarte.' }}</p>

                    <div class="mt-12 space-y-6">
                        @if($siteSettings?->contact_phone)
                        <a href="tel:{{ $siteSettings->contact_phone }}" class="group flex items-center gap-4 p-4 rounded-2xl hover:bg-white/5 transition-colors duration-300">
                            <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-brand-500/15 group-hover:bg-brand-500/25 transition-colors">
                                <svg class="w-5 h-5 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs text-brand-400/60 uppercase tracking-wider font-medium">Telefono</p>
                                <p class="text-white font-semibold mt-0.5 group-hover:text-brand-200 transition-colors">{{ $siteSettings->contact_phone }}</p>
                            </div>
                        </a>
                        @endif
                        @if($siteSettings?->contact_email)
                        <a href="mailto:{{ $siteSettings->contact_email }}" class="group flex items-center gap-4 p-4 rounded-2xl hover:bg-white/5 transition-colors duration-300">
                            <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-brand-500/15 group-hover:bg-brand-500/25 transition-colors">
                                <svg class="w-5 h-5 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
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
                                <svg class="w-5 h-5 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs text-brand-400/60 uppercase tracking-wider font-medium">Direccion</p>
                                <p class="text-white font-semibold mt-0.5">{{ $siteSettings->address }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="rounded-2xl bg-white p-8 lg:p-10 shadow-premium-xl" x-data x-intersect.once="$el.classList.add('animate-slide-in-right')">
                    <h3 class="text-xl font-bold text-gray-900">Envíanos un mensaje</h3>
                    <p class="text-sm text-gray-500 mt-1.5 mb-8">Responderemos en menos de 24 horas.</p>
                    <x-public.contact-form />
                </div>
            </div>
        </div>
    </section>

@endsection
