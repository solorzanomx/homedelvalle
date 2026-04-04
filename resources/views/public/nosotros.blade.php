@extends('layouts.public')

@section('meta')
    <x-public.seo-meta
        title="Nosotros — {{ $siteSettings?->site_name ?? 'Home del Valle' }}"
        description="Firma inmobiliaria boutique de alta precisión en la Benito Juárez, CDMX. Más de 30 años de experiencia en el sector."
        :canonical="route('nosotros')"
    />
@endsection

@php
    $content = $siteSettings?->nosotros_content ?? [];
    $defaultValues = [
        ['title' => 'Control', 'description' => 'Gestión precisa de cada operación con seguimiento detallado en cada etapa.'],
        ['title' => 'Transparencia', 'description' => 'Información clara y oportuna. Sin comisiones ocultas, sin sorpresas.'],
        ['title' => 'Seguridad Jurídica', 'description' => 'Blindaje legal completo en cada transacción para proteger tu patrimonio.'],
        ['title' => 'Ejecución', 'description' => 'Resultados consistentes y eficientes. Cerramos operaciones complejas.'],
    ];
    $defaultStats = [
        ['value' => '30+', 'label' => 'Años de experiencia senior'],
        ['value' => '200+', 'label' => 'Propiedades gestionadas'],
        ['value' => '98%', 'label' => 'Clientes satisfechos'],
        ['value' => '50+', 'label' => 'Operaciones al año'],
    ];
@endphp

@section('content')
    <x-public.hero
        :heading="'Quienes somos'"
        :subheading="'Firma inmobiliaria boutique especializada en la Alcaldía Benito Juárez, Ciudad de México.'"
        :breadcrumb-items="[['label' => 'Nosotros']]"
    />

    {{-- Mission & Vision --}}
    <section class="py-20 sm:py-24 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="rounded-2xl border border-gray-200/60 p-8 hover:shadow-premium-lg hover:border-brand-100 transition-all duration-500"
                     x-data x-intersect.once="$el.classList.add('animate-slide-in-left')">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-brand-500/10 mb-5">
                        <x-icon name="zap" class="w-6 h-6 text-brand-500" />
                    </div>
                    <h3 class="text-xl font-extrabold text-gray-900 tracking-tight">Misión</h3>
                    <p class="mt-3 text-gray-500 leading-relaxed">{{ $content['mission'] ?? 'Conectar propiedades estratégicas con compradores calificados a través de procesos eficientes, seguros y transparentes.' }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200/60 p-8 hover:shadow-premium-lg hover:border-brand-100 transition-all duration-500"
                     x-data x-intersect.once="$el.classList.add('animate-slide-in-right')">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-brand-500/10 mb-5">
                        <x-icon name="eye" class="w-6 h-6 text-brand-500" />
                    </div>
                    <h3 class="text-xl font-extrabold text-gray-900 tracking-tight">Visión</h3>
                    <p class="mt-3 text-gray-500 leading-relaxed">{{ $content['vision'] ?? 'Ser la firma referente en la Benito Juárez por nuestra precisión operativa y efectividad en el cierre de operaciones complejas.' }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Story --}}
    <section class="py-20 sm:py-24 bg-gray-50/60">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Nuestra historia</p>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">{{ $content['story_heading'] ?? 'Una trayectoria de precisión' }}</h2>
                <div class="mt-6 text-gray-600 leading-relaxed space-y-4 text-lg">
                    @if($siteSettings?->about_text)
                        {!! nl2br(e($siteSettings->about_text)) !!}
                    @else
                        <p>Home del Valle es una consultora especializada en la captación estratégica, análisis y comercialización de propiedades de alto valor. A diferencia del modelo tradicional de volumen, operamos bajo un esquema de control total, priorizando la calidad del inventario y la seguridad jurídica para asegurar cierres consistentes y eficientes.</p>
                        <p>Con más de 30 años de experiencia senior en el sector, nuestra operación no comienza con la oferta, sino con la demanda activa. Identificamos las necesidades de desarrolladores e inversionistas, captamos activos alineados y ejecutamos estrategias de salida rápidas mediante una red de contactos consolidada.</p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Philosophy & Values --}}
    <section class="py-20 sm:py-24 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Filosofía</p>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">{{ $content['philosophy_heading'] ?? 'Pocos inmuebles. Más control. Mejores resultados.' }}</h2>
                @if(!empty($content['philosophy_text']))
                <p class="mt-5 text-lg text-gray-500 leading-relaxed">{{ $content['philosophy_text'] }}</p>
                @else
                <p class="mt-5 text-lg text-gray-500 leading-relaxed">Nuestra estructura boutique nos permite dar atención personalizada con enfoque en ejecución, no en catálogo. Cada propiedad que gestionamos recibe nuestra completa dedicación.</p>
                @endif
            </div>

            @php $values = $content['values'] ?? $defaultValues; @endphp
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($values as $vi => $value)
                <div class="group text-center p-8 rounded-2xl border border-gray-200/60 hover:border-brand-200 hover:shadow-premium-lg transition-all duration-500"
                     x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: {{ $vi * 100 }}ms">
                    <div class="mx-auto flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-50 group-hover:bg-brand-500 transition-all duration-500 group-hover:shadow-brand">
                        <x-icon name="check" class="w-6 h-6 text-brand-500 group-hover:text-white transition-colors duration-500" />
                    </div>
                    <h3 class="mt-5 text-base font-bold text-gray-900">{{ $value['title'] ?? '' }}</h3>
                    <p class="mt-2 text-sm text-gray-500 leading-relaxed">{{ $value['description'] ?? '' }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Stats --}}
    @php $stats = $content['stats'] ?? $defaultStats; @endphp
    <section class="py-20 sm:py-24 bg-brand-950 relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_rgba(59,130,196,0.1)_0%,_transparent_70%)]"></div>
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8">
                @foreach($stats as $index => $stat)
                <div class="text-center" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: {{ $index * 150 }}ms">
                    <p class="text-4xl sm:text-5xl font-extrabold text-white tracking-tight">{{ $stat['value'] }}</p>
                    <p class="mt-2 text-sm text-brand-300/60 font-medium">{{ $stat['label'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Team --}}
    @if(isset($teamMembers) && $teamMembers->count())
    <section class="py-20 sm:py-24 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">{{ $content['team_heading'] ?? 'Nuestro equipo' }}</p>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">{{ $content['team_subheading'] ?? 'Profesionales a tu servicio' }}</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($teamMembers as $index => $member)
                <div class="group text-center rounded-2xl border border-gray-200/60 bg-white p-8 hover:shadow-premium-lg hover:border-brand-100 hover:-translate-y-1 transition-all duration-500"
                     x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: {{ $index * 150 }}ms">
                    {{-- Avatar --}}
                    <div class="w-24 h-24 mx-auto rounded-full overflow-hidden mb-5 ring-4 ring-brand-100 group-hover:ring-brand-200 transition-all duration-500">
                        @if($member->avatar_path)
                            <img src="{{ Storage::url($member->avatar_path) }}" alt="{{ $member->full_name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-white text-2xl font-bold">
                                {{ strtoupper(substr($member->name, 0, 1)) }}{{ strtoupper(substr($member->last_name ?? '', 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    {{-- Info --}}
                    <h3 class="text-lg font-bold text-gray-900">{{ $member->full_name }}</h3>
                    @if($member->title)
                    <p class="mt-1 text-sm text-brand-500 font-medium">{{ $member->title }}</p>
                    @endif
                    @if($member->bio)
                    <p class="mt-3 text-sm text-gray-500 leading-relaxed">{{ $member->bio }}</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Google Maps --}}
    @if($siteSettings?->google_maps_embed)
    <section class="py-20 sm:py-24 bg-gray-50/60">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
            <div class="text-center mb-10">
                <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Ubicación</p>
                <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Encuéntranos</h2>
            </div>
            <div class="rounded-2xl overflow-hidden border border-gray-200/60 shadow-premium-lg aspect-[16/6]">
                {!! $siteSettings->google_maps_embed !!}
            </div>
        </div>
    </section>
    @endif

    {{-- CTA --}}
    <section class="py-24 sm:py-32 gradient-brand-soft" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">¿Tienes una propiedad en la Benito Juárez?</h2>
            <p class="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">Conoce cuánto vale tu inmueble con una valuación profesional gratuita y sin compromiso.</p>
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('landing.vende') }}" class="rounded-xl gradient-brand px-7 py-4 text-sm font-semibold text-white shadow-brand hover:shadow-brand-lg hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300">
                    Valúa tu propiedad
                </a>
                <a href="{{ route('contacto') }}" class="rounded-xl border border-gray-200 bg-white px-7 py-4 text-sm font-semibold text-gray-700 hover:border-brand-200 hover:text-brand-600 transition-all duration-300">
                    Contáctanos
                </a>
            </div>
        </div>
    </section>
@endsection
