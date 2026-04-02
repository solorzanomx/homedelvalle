@extends('layouts.public')

@section('meta')
    <x-public.seo-meta
        title="Nosotros"
        description="Conoce a Home del Valle, tu inmobiliaria de confianza en Ciudad de México. Experiencia, transparencia y resultados."
        :canonical="route('nosotros')"
    />
@endsection

@section('content')
    <x-public.hero heading="Quiénes somos" subheading="Experiencia, confianza y resultados en el mercado inmobiliario de la Ciudad de México."
        :breadcrumb-items="[['label' => 'Nosotros']]" />

    {{-- Mission & Vision --}}
    <section class="py-20 sm:py-24 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="rounded-2xl border border-gray-200/60 p-8 hover:shadow-premium-lg hover:border-brand-100 transition-all duration-500"
                     x-data x-intersect.once="$el.classList.add('animate-slide-in-left')">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-brand-500/10 mb-5">
                        <svg class="w-6 h-6 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h3 class="text-xl font-extrabold text-gray-900 tracking-tight">Nuestra Misión</h3>
                    <p class="mt-3 text-gray-500 leading-relaxed">Transformar la experiencia inmobiliaria en la Ciudad de México, haciendo que encontrar tu hogar ideal sea un proceso transparente, profesional y personalizado.</p>
                </div>
                <div class="rounded-2xl border border-gray-200/60 p-8 hover:shadow-premium-lg hover:border-brand-100 transition-all duration-500"
                     x-data x-intersect.once="$el.classList.add('animate-slide-in-right')">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-brand-500/10 mb-5">
                        <svg class="w-6 h-6 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </div>
                    <h3 class="text-xl font-extrabold text-gray-900 tracking-tight">Nuestra Visión</h3>
                    <p class="mt-3 text-gray-500 leading-relaxed">Ser la inmobiliaria de referencia en la Ciudad de México, reconocida por la calidad de nuestro servicio, la confianza de nuestros clientes y la innovación en cada operación.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Story --}}
    <section class="py-20 sm:py-24 bg-gray-50/60">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Nuestra historia</p>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Una trayectoria de confianza</h2>
                <div class="mt-6 text-gray-600 leading-relaxed space-y-4 text-lg">
                    @if($siteSettings?->about_text)
                        {!! nl2br(e($siteSettings->about_text)) !!}
                    @else
                        <p>Home del Valle nació con la misión de transformar la experiencia inmobiliaria en la Ciudad de México. Creemos que encontrar tu hogar ideal o vender tu propiedad no debería ser un proceso complicado.</p>
                        <p>Nuestro equipo de asesores especializados combina conocimiento profundo del mercado con tecnología de punta para ofrecerte resultados excepcionales. Cada operación está respaldada por transparencia total y acompañamiento profesional en cada paso.</p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Stats --}}
    <section class="py-20 sm:py-24 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                @php $stats = [
                    ['value' => '200+', 'label' => 'Propiedades gestionadas'],
                    ['value' => '10+', 'label' => 'Años de experiencia'],
                    ['value' => '98%', 'label' => 'Clientes satisfechos'],
                    ['value' => '50+', 'label' => 'Operaciones al año'],
                ]; @endphp
                @foreach($stats as $index => $stat)
                <div class="text-center rounded-2xl border border-gray-200/60 p-8 hover:shadow-premium hover:border-brand-100 transition-all duration-500"
                     x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: {{ $index * 150 }}ms">
                    <p class="text-3xl sm:text-4xl font-extrabold text-brand-600 tracking-tight">{{ $stat['value'] }}</p>
                    <p class="mt-2 text-sm text-gray-500 font-medium">{{ $stat['label'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Team --}}
    @if(isset($brokers) && $brokers->count())
    <section class="py-20 sm:py-24 bg-gray-50/60">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Nuestro equipo</p>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Profesionales a tu servicio</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($brokers as $index => $broker)
                <div class="group text-center rounded-2xl border border-gray-200/60 bg-white p-8 hover:shadow-premium-lg hover:border-brand-100 hover:-translate-y-1 transition-all duration-500"
                     x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: {{ $index * 150 }}ms">
                    <div class="flex items-center justify-center w-20 h-20 mx-auto rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-white text-2xl font-bold mb-4">
                        {{ strtoupper(substr($broker->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', $broker->name)[1] ?? '', 0, 1)) }}
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">{{ $broker->name }}</h3>
                    @if($broker->specialty)
                    <p class="mt-1 text-sm text-brand-500 font-medium">{{ $broker->specialty }}</p>
                    @endif
                    @if($broker->phone)
                    <p class="mt-2 text-sm text-gray-400">{{ $broker->phone }}</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Google Maps --}}
    @if($siteSettings?->google_maps_embed)
    <section class="py-20 sm:py-24 bg-white">
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
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">{{ $siteSettings?->cta_heading ?? '¿Listo para encontrar tu hogar ideal?' }}</h2>
            <p class="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">{{ $siteSettings?->cta_subheading ?? 'Explora nuestro catálogo de propiedades o contáctanos para asesoría personalizada.' }}</p>
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('propiedades.index') }}" class="rounded-xl gradient-brand px-7 py-4 text-sm font-semibold text-white shadow-brand hover:shadow-brand-lg hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300">
                    Explorar propiedades
                </a>
                <a href="{{ route('contacto') }}" class="rounded-xl border border-gray-200 bg-white px-7 py-4 text-sm font-semibold text-gray-700 hover:border-brand-200 hover:text-brand-600 transition-all duration-300">
                    Contáctanos
                </a>
            </div>
        </div>
    </section>
@endsection
