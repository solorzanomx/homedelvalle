@extends('layouts.public')

@section('title', 'Servicios')

@section('content')

@php
    $content = $siteSettings?->servicios_content;

    $defaultServices = [
        [
            'title' => 'Desarrollo Inmobiliario',
            'slug' => 'desarrollo-inmobiliario',
            'icon' => 'building',
            'description' => 'Captación y colocación estratégica de predios con potencial habitacional o comercial. Identificamos las necesidades de desarrolladores e inversionistas para encontrar activos que se alineen con sus requerimientos técnicos y financieros.',
            'features' => ['Captación de terrenos y predios', 'Análisis de potencial de desarrollo', 'Vinculación con desarrolladores calificados', 'Due diligence inmobiliario'],
        ],
        [
            'title' => 'Corretaje Premium',
            'slug' => 'corretaje-premium',
            'icon' => 'key',
            'description' => 'Venta y renta de propiedades residenciales y comerciales seleccionadas. No somos un catálogo masivo: cada inmueble es evaluado, analizado y comercializado con estrategia para maximizar su valor.',
            'features' => ['Propiedades seleccionadas de alto valor', 'Estrategia de comercialización personalizada', 'Red de compradores calificados', 'Negociación profesional de cierre'],
        ],
        [
            'title' => 'Administración de Inmuebles',
            'slug' => 'administracion',
            'icon' => 'chart',
            'description' => 'Gestión profesional de activos inmobiliarios. Nos encargamos de la operación completa para que tu inversión trabaje sin que tengas que ocuparte de los detalles.',
            'features' => ['Gestión de inquilinos y cobranza', 'Mantenimiento preventivo y correctivo', 'Reportes financieros mensuales', 'Optimización de rentabilidad'],
        ],
        [
            'title' => 'Legal y Gestoría',
            'slug' => 'legal-gestoria',
            'icon' => 'shield',
            'description' => 'Regularización documental, sucesiones y blindaje jurídico en escrituración. Cada operación que ejecutamos cuenta con respaldo legal completo para proteger los intereses de todas las partes.',
            'features' => ['Regularización de escrituras', 'Trámites de sucesión', 'Blindaje jurídico en compraventa', 'Asesoría fiscal inmobiliaria'],
        ],
        [
            'title' => 'Property Transformation',
            'slug' => 'property-transformation',
            'icon' => 'sparkle',
            'description' => 'Aceleración de venta mediante home staging y acondicionamiento estratégico. Transformamos la percepción de valor de tu propiedad para vender más rápido y a mejor precio.',
            'features' => ['Home staging profesional', 'Mejoras funcionales y estéticas', 'Fotografía y video profesional', 'Optimización visual de espacios'],
        ],
    ];

    $services = $content['services'] ?? $defaultServices;
    $heading = $content['heading'] ?? 'Nuestros Servicios';
    $subheading = $content['subheading'] ?? 'Una firma inmobiliaria con servicio integral. Cada línea de negocio está diseñada para maximizar el valor de tu patrimonio con precisión y seguridad jurídica.';
    $ctaHeading = $content['cta_heading'] ?? '¿Tienes una propiedad?';
    $ctaSubheading = $content['cta_subheading'] ?? 'Conoce cuánto vale tu inmueble con una valuación profesional sin compromiso.';

    $icons = [
        'building' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>',
        'key' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>',
        'chart' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
        'shield' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
        'sparkle' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>',
    ];
@endphp

<x-public.seo-meta
    :title="$heading"
    :description="$subheading"
/>

{{-- HERO --}}
<x-public.hero
    :heading="$heading"
    :subheading="$subheading"
    :breadcrumb-items="[['label' => 'Servicios']]"
/>

{{-- SERVICES SECTIONS --}}
@foreach($services as $index => $service)
<section id="{{ $service['slug'] ?? Str::slug($service['title'] ?? '') }}" class="relative py-20 sm:py-28 {{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center"
             x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">

            {{-- Text side --}}
            <div class="{{ $index % 2 === 1 ? 'lg:order-2' : '' }}">
                <div class="flex items-center gap-3 mb-6">
                    <div class="flex items-center justify-center w-12 h-12 rounded-2xl gradient-brand text-white">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            {!! $icons[$service['icon'] ?? 'building'] ?? $icons['building'] !!}
                        </svg>
                    </div>
                    <span class="text-xs font-bold uppercase tracking-widest text-brand-600">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                </div>

                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">{{ $service['title'] ?? '' }}</h2>
                <p class="mt-5 text-lg text-gray-600 leading-relaxed">{{ $service['description'] ?? '' }}</p>

                @if(!empty($service['features']))
                <ul class="mt-8 space-y-3">
                    @foreach($service['features'] as $feature)
                    @if(!empty($feature))
                    <li class="flex items-start gap-3">
                        <x-icon name="check" class="w-5 h-5 text-brand-500 shrink-0 mt-0.5" />
                        <span class="text-gray-700">{{ $feature }}</span>
                    </li>
                    @endif
                    @endforeach
                </ul>
                @endif

                <div class="mt-8">
                    <a href="{{ $service['cta_url'] ?? route('contacto') }}" class="inline-flex items-center gap-2 rounded-xl gradient-brand px-6 py-3 text-sm font-semibold text-white shadow-brand hover:shadow-brand-lg hover:-translate-y-0.5 transition-all duration-300">
                        {{ $service['cta_text'] ?? 'Solicitar información' }}
                        <x-icon name="arrow-right" class="w-4 h-4" />
                    </a>
                </div>
            </div>

            {{-- Visual side --}}
            <div class="{{ $index % 2 === 1 ? 'lg:order-1' : '' }}">
                <div class="relative">
                    <div class="aspect-[4/3] rounded-3xl bg-gradient-to-br from-brand-50 via-brand-100/50 to-brand-50 border border-brand-100/80 flex items-center justify-center overflow-hidden">
                        <div class="text-center p-8">
                            <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-white shadow-lg mb-4">
                                <svg class="w-10 h-10 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    {!! $icons[$service['icon'] ?? 'building'] ?? $icons['building'] !!}
                                </svg>
                            </div>
                            <p class="text-brand-700 font-bold text-lg">{{ $service['title'] ?? '' }}</p>
                        </div>
                    </div>
                    {{-- Decorative dot --}}
                    <div class="absolute -top-4 {{ $index % 2 === 0 ? '-right-4' : '-left-4' }} w-24 h-24 rounded-2xl bg-brand-500/10 -z-10"></div>
                </div>
            </div>
        </div>
    </div>
</section>
@endforeach

{{-- CTA SECTION --}}
<section class="relative py-24 sm:py-32 overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-brand-900 via-brand-800 to-brand-900"></div>
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_rgba(255,255,255,0.05)_0%,_transparent_70%)]"></div>

    <div class="relative mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 text-center"
         x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">{{ $ctaHeading }}</h2>
        <p class="mt-5 text-lg text-brand-200/80 max-w-2xl mx-auto">{{ $ctaSubheading }}</p>
        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('landing.vende') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-8 py-4 text-sm font-bold text-brand-900 shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
                <x-icon name="shield-check" class="w-5 h-5" />
                Valúa tu propiedad
            </a>
            <a href="{{ route('contacto') }}" class="inline-flex items-center gap-2 rounded-xl border-2 border-white/30 px-8 py-4 text-sm font-semibold text-white hover:bg-white/10 transition-all duration-300">
                Contáctanos
                <x-icon name="arrow-right" class="w-4 h-4" />
            </a>
        </div>
    </div>
</section>

@endsection
