@extends('layouts.public')

@section('meta')
    <x-public.seo-meta
        :title="$colonia->meta_title ?: 'Propiedades en ' . $colonia->name . ', Benito Juárez'"
        :description="$colonia->meta_description ?: 'Casas y departamentos en venta y renta en ' . $colonia->name . ', CDMX. Asesoría inmobiliaria personalizada de expertos en Benito Juárez.'"
        :canonical="url('/' . $colonia->slug)"
    />

    {{-- LocalBusiness + specific area schema --}}
    <x-public.json-ld type="RealEstateAgent" :data="array_filter([
        'name'        => ($siteSettings?->site_name ?? 'Home del Valle') . ' — ' . $colonia->name,
        'description' => $colonia->meta_description ?: 'Especialistas inmobiliarios en ' . $colonia->name . ', Benito Juárez, CDMX.',
        'url'         => url('/' . $colonia->slug),
        'telephone'   => $siteSettings?->contact_phone,
        'address'     => [
            '@type'           => 'PostalAddress',
            'addressLocality' => $colonia->name,
            'addressRegion'   => 'Alcaldía Benito Juárez',
            'addressCountry'  => 'MX',
        ],
        'areaServed' => ['@type' => 'City', 'name' => $colonia->name . ', Benito Juárez'],
        'priceRange' => '$$',
    ])" />

    {{-- BreadcrumbList --}}
    <x-public.json-ld type="BreadcrumbList" :data="[
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Inicio',      'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Propiedades', 'item' => url('/propiedades')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $colonia->name,'item' => url('/' . $colonia->slug)],
        ],
    ]" />

    {{-- FAQPage schema --}}
    @if($colonia->faqs && count($colonia->faqs))
    <x-public.json-ld type="FAQPage" :data="[
        'mainEntity' => collect($colonia->faqs)->map(fn($f) => [
            '@type'          => 'Question',
            'name'           => $f['q'] ?? '',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a'] ?? ''],
        ])->all(),
    ]" />
    @endif
@endsection

@section('content')

    <x-public.hero
        heading="{{ $colonia->heading ?? 'Propiedades en ' . $colonia->name }}"
        subheading="{{ $colonia->subheading ?? 'Encuentra tu próxima propiedad en ' . $colonia->name . ', Benito Juárez, con asesoría personalizada de expertos.' }}"
        :breadcrumb-items="[
            ['label' => 'Propiedades', 'url' => route('propiedades.index')],
            ['label' => $colonia->name],
        ]"
    />

    {{-- ================================================ --}}
    {{-- PROPIEDADES DISPONIBLES --}}
    {{-- ================================================ --}}
    <section class="py-16 sm:py-20 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row items-start sm:items-end justify-between gap-4 mb-10" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <div>
                    <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-2">Disponibles ahora</p>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                        @if($properties->count())
                            {{ $properties->count() }} {{ $properties->count() === 1 ? 'propiedad' : 'propiedades' }} en {{ $colonia->name }}
                        @else
                            Próximas propiedades en {{ $colonia->name }}
                        @endif
                    </h2>
                </div>
                <a href="{{ route('propiedades.index', ['search' => $colonia->name]) }}"
                   class="inline-flex items-center gap-2 text-sm font-semibold text-brand-600 hover:text-brand-700 transition-colors shrink-0">
                    Ver catálogo completo
                    <x-icon name="arrow-right" class="w-4 h-4" />
                </a>
            </div>

            @if($properties->count())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($properties as $index => $property)
                    <div x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: {{ ($index % 6) * 100 }}ms">
                        <x-public.property-card :property="$property" />
                    </div>
                    @endforeach
                </div>
            @else
                {{-- Empty state — still show CTA to get notified --}}
                <div class="rounded-2xl border border-brand-100 bg-brand-50 p-10 text-center" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                    <div class="flex items-center justify-center w-14 h-14 mx-auto rounded-2xl bg-brand-100 mb-4">
                        <x-icon name="home" class="w-7 h-7 text-brand-500" />
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Sin propiedades activas en este momento</h3>
                    <p class="mt-2 text-gray-500 max-w-sm mx-auto">Trabajamos con una selección curada. Contáctanos y te avisamos cuando tengamos algo en {{ $colonia->name }}.</p>
                    <a href="{{ route('contacto') }}?colonia={{ urlencode($colonia->name) }}"
                       class="mt-6 inline-flex items-center gap-2 rounded-xl gradient-brand px-6 py-3 text-sm font-semibold text-white shadow-brand hover:shadow-brand-lg hover:-translate-y-0.5 transition-all duration-300">
                        Quiero ser avisado
                        <x-icon name="arrow-right" class="w-4 h-4" />
                    </a>
                </div>
            @endif
        </div>
    </section>

    {{-- ================================================ --}}
    {{-- SOBRE LA COLONIA --}}
    {{-- ================================================ --}}
    @if($colonia->about)
    <section class="py-16 sm:py-20 bg-gray-50">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Conoce la zona</p>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Vivir en {{ $colonia->name }}</h2>
            </div>
            <div class="prose prose-gray prose-lg max-w-none" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                {!! $colonia->about !!}
            </div>
        </div>
    </section>
    @endif

    {{-- ================================================ --}}
    {{-- PREGUNTAS FRECUENTES --}}
    {{-- ================================================ --}}
    @if($colonia->faqs && count($colonia->faqs))
    <section class="py-16 sm:py-20 bg-white">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Preguntas frecuentes</h2>
                <p class="mt-3 text-gray-500">Todo lo que necesitas saber sobre el mercado inmobiliario en {{ $colonia->name }}</p>
            </div>
            <div class="space-y-4">
                @foreach($colonia->faqs as $i => $faq)
                <div x-data="{ open: false }"
                     class="rounded-2xl border border-gray-200/80 overflow-hidden transition-all duration-300 hover:border-brand-200"
                     x-data x-intersect.once="$el.classList.add('animate-fade-in-up')"
                     style="animation-delay: {{ $i * 80 }}ms">
                    <button @click="open = !open" class="w-full flex items-center justify-between p-6 text-left">
                        <span class="font-semibold text-gray-900 pr-4">{{ $faq['q'] }}</span>
                        <span class="shrink-0 transition-transform duration-300" :class="{ 'rotate-180': open }">
                            <x-icon name="chevron-down" class="w-5 h-5 text-gray-400" />
                        </span>
                    </button>
                    <div x-show="open" x-collapse class="px-6 pb-6">
                        <p class="text-gray-500 leading-relaxed">{{ $faq['a'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ================================================ --}}
    {{-- ARTÍCULOS RELACIONADOS --}}
    {{-- ================================================ --}}
    @if($posts->count())
    <section class="py-16 sm:py-20 bg-gray-50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Recursos</p>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Artículos sobre {{ $colonia->name }}</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($posts as $index => $post)
                <div x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: {{ $index * 120 }}ms">
                    @include('blog._card', ['post' => $post])
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ================================================ --}}
    {{-- CTA FINAL --}}
    {{-- ================================================ --}}
    <section class="py-24 sm:py-32 bg-brand-950 relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_rgba(59,130,196,0.1)_0%,_transparent_70%)]"></div>
        <div class="relative mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 text-center" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
            <p class="text-sm font-semibold text-brand-400 uppercase tracking-widest mb-4">Especialistas en {{ $colonia->name }}</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">¿Buscas o vendes en {{ $colonia->name }}?</h2>
            <p class="mt-5 text-lg text-brand-200/70">Valuación gratuita, asesoría personalizada y respuesta en menos de 24 horas.</p>
            <div class="mt-10 flex flex-wrap justify-center gap-4">
                <a href="{{ route('landing.vende') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white px-8 py-4 text-sm font-bold text-brand-900 shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
                    <x-icon name="home" class="w-4 h-4" />
                    Quiero vender mi propiedad
                </a>
                <a href="{{ route('landing.compra') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-brand-500/20 border border-brand-400/30 px-8 py-4 text-sm font-bold text-white hover:bg-brand-500/30 hover:-translate-y-0.5 transition-all duration-300">
                    <x-icon name="search" class="w-4 h-4" />
                    Busco propiedad aquí
                </a>
            </div>
        </div>
    </section>

@endsection
