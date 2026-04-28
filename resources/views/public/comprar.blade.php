@extends('layouts.public')

@section('title', $page?->meta_title ?? 'Búsqueda asistida de inmuebles en Benito Juárez')

@section('meta')
    <x-public.seo-meta
        :title="$page?->meta_title ?? 'Búsqueda asistida de inmuebles en Benito Juárez'"
        :description="$page?->meta_description ?? 'Encuentra tu próximo hogar en Benito Juárez sin perder fines de semana en visitas. Asesoría personalizada de expertos.'"
    />
@endsection

@section('content')

{{-- HERO + FORM --}}
<section class="relative overflow-hidden bg-brand-950" id="inicio">
    <div class="absolute inset-0 bg-gradient-to-br from-brand-950 via-brand-900/90 to-brand-800/80"></div>
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_rgba(59,130,196,0.15)_0%,_transparent_60%)]"></div>
    <div class="absolute top-20 right-10 w-72 h-72 bg-brand-500/10 rounded-full blur-3xl animate-float"></div>

    <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20 sm:py-28 lg:py-32">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-start">
            {{-- Left: copy --}}
            <div x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <div class="inline-flex items-center gap-2 rounded-full bg-white/10 border border-white/10 px-4 py-1.5 text-sm text-brand-200 backdrop-blur-sm mb-6">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Búsqueda asistida
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight">
                    Encuentra tu próximo hogar en Benito Juárez
                </h1>
                <p class="mt-5 text-lg text-brand-200/80 leading-relaxed">
                    Propiedades verificadas con asesoría personalizada de expertos. Sin perder tiempo en visitas innecesarias.
                </p>

                {{-- Trust signals --}}
                <div class="mt-10 space-y-3">
                    <div class="flex items-center gap-3">
                        <x-icon name="check" class="w-5 h-5 text-emerald-400 flex-shrink-0" />
                        <span class="text-sm text-brand-200/80">Propiedades pre-calificadas</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-icon name="check" class="w-5 h-5 text-emerald-400 flex-shrink-0" />
                        <span class="text-sm text-brand-200/80">Asesoría especializada 100% gratuita</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-icon name="check" class="w-5 h-5 text-emerald-400 flex-shrink-0" />
                        <span class="text-sm text-brand-200/80">Seguridad jurídica garantizada</span>
                    </div>
                </div>
            </div>

            {{-- Right: form --}}
            <div class="rounded-2xl bg-white p-8 lg:p-10 shadow-premium-xl" x-data x-intersect.once="$el.classList.add('animate-slide-in-right')">
                <livewire:forms.buyer-search-form />
            </div>
        </div>
    </div>
</section>

{{-- VENTAJAS SECTION --}}
<section class="py-20 sm:py-24 bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
            <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Ventajas</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">¿Por qué buscar con nosotros?</h2>
            <p class="mt-5 text-lg text-gray-500">Especialistas en Benito Juárez con acceso a propiedades antes del mercado abierto.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">
            <div class="p-6 rounded-2xl bg-brand-50/50" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-brand-500/15 mb-4">
                    <x-icon name="zap" class="w-6 h-6 text-brand-600" />
                </div>
                <h3 class="text-base font-bold text-gray-900 mt-4">Búsqueda rápida</h3>
                <p class="text-sm text-gray-500 mt-2 leading-relaxed">Propiedades pre-calificadas sin perder tiempo en visitas innecesarias.</p>
            </div>

            <div class="p-6 rounded-2xl bg-brand-50/50" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: 100ms">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-brand-500/15 mb-4">
                    <x-icon name="shield-check" class="w-6 h-6 text-brand-600" />
                </div>
                <h3 class="text-base font-bold text-gray-900 mt-4">100% verificadas</h3>
                <p class="text-sm text-gray-500 mt-2 leading-relaxed">Todas las propiedades verificadas con blindaje legal completo.</p>
            </div>

            <div class="p-6 rounded-2xl bg-brand-50/50" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: 200ms">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-brand-500/15 mb-4">
                    <x-icon name="users" class="w-6 h-6 text-brand-600" />
                </div>
                <h3 class="text-base font-bold text-gray-900 mt-4">Asesoría experta</h3>
                <p class="text-sm text-gray-500 mt-2 leading-relaxed">Profesionales con 30+ años especializados en Benito Juárez.</p>
            </div>
        </div>
    </div>
</section>

{{-- CÓMO FUNCIONA SECTION --}}
<section class="py-20 sm:py-24 bg-gray-50/60">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
            <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Proceso</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Cómo funciona</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="relative p-8 rounded-2xl bg-white border border-gray-200/80 hover:border-brand-200 hover:shadow-premium-lg transition-all duration-500" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <div class="flex items-center justify-center w-12 h-12 rounded-2xl gradient-brand text-white text-lg font-extrabold mb-6 shadow-brand">
                    01
                </div>
                <h3 class="text-lg font-bold text-gray-900">Cuéntanos qué buscas</h3>
                <p class="mt-3 text-sm text-gray-500 leading-relaxed">Completa el formulario de búsqueda con tus preferencias: ubicación, tipo, presupuesto y más.</p>
            </div>

            <div class="relative p-8 rounded-2xl bg-white border border-gray-200/80 hover:border-brand-200 hover:shadow-premium-lg transition-all duration-500" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: 100ms">
                <div class="flex items-center justify-center w-12 h-12 rounded-2xl gradient-brand text-white text-lg font-extrabold mb-6 shadow-brand">
                    02
                </div>
                <h3 class="text-lg font-bold text-gray-900">Asesoría personalizada</h3>
                <p class="mt-3 text-sm text-gray-500 leading-relaxed">Nuestros asesores te contactan en menos de 24 horas para entender mejor tu perfil.</p>
            </div>

            <div class="relative p-8 rounded-2xl bg-white border border-gray-200/80 hover:border-brand-200 hover:shadow-premium-lg transition-all duration-500" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: 200ms">
                <div class="flex items-center justify-center w-12 h-12 rounded-2xl gradient-brand text-white text-lg font-extrabold mb-6 shadow-brand">
                    03
                </div>
                <h3 class="text-lg font-bold text-gray-900">Cierra tu hogar</h3>
                <p class="mt-3 text-sm text-gray-500 leading-relaxed">Te presentamos opciones verificadas con acompañamiento legal en toda la operación.</p>
            </div>
        </div>
    </div>
</section>

{{-- FAQ SECTION --}}
<section class="py-20 sm:py-24 bg-white">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Preguntas frecuentes</h2>
        </div>

        <div class="space-y-4">
            <details class="group rounded-2xl border border-gray-200/80 p-6 hover:border-brand-200 transition-all duration-300 [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex cursor-pointer items-center justify-between font-medium text-gray-900">
                    ¿Cuál es el proceso de búsqueda?
                    <span class="transition group-open:rotate-180">
                        <x-icon name="chevron-down" class="w-5 h-5 text-gray-500" />
                    </span>
                </summary>
                <p class="mt-4 text-sm text-gray-500 leading-relaxed">Completas el formulario con tus preferencias, nuestro equipo analiza tu perfil y te presenta opciones de propiedades verificadas en menos de 24 horas.</p>
            </details>

            <details class="group rounded-2xl border border-gray-200/80 p-6 hover:border-brand-200 transition-all duration-300 [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex cursor-pointer items-center justify-between font-medium text-gray-900">
                    ¿Hay costo por la asesoría?
                    <span class="transition group-open:rotate-180">
                        <x-icon name="chevron-down" class="w-5 h-5 text-gray-500" />
                    </span>
                </summary>
                <p class="mt-4 text-sm text-gray-500 leading-relaxed">No. La asesoría y búsqueda son completamente gratuitas. Solo si decides comprar a través de nosotros, aplicamos una comisión estándar del mercado.</p>
            </details>

            <details class="group rounded-2xl border border-gray-200/80 p-6 hover:border-brand-200 transition-all duration-300 [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex cursor-pointer items-center justify-between font-medium text-gray-900">
                    ¿Qué sucede después de enviar el formulario?
                    <span class="transition group-open:rotate-180">
                        <x-icon name="chevron-down" class="w-5 h-5 text-gray-500" />
                    </span>
                </summary>
                <p class="mt-4 text-sm text-gray-500 leading-relaxed">Uno de nuestros asesores especializados en Benito Juárez se pondrá en contacto contigo vía WhatsApp o teléfono en menos de 24 horas para validar tus requerimientos y comenzar la búsqueda.</p>
            </details>

            <details class="group rounded-2xl border border-gray-200/80 p-6 hover:border-brand-200 transition-all duration-300 [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex cursor-pointer items-center justify-between font-medium text-gray-900">
                    ¿Están todas las propiedades verificadas?
                    <span class="transition group-open:rotate-180">
                        <x-icon name="chevron-down" class="w-5 h-5 text-gray-500" />
                    </span>
                </summary>
                <p class="mt-4 text-sm text-gray-500 leading-relaxed">Sí. Todas nuestras propiedades son verificadas legalmente y cuentan con blindaje jurídico completo. Garantizamos la seguridad en cada operación.</p>
            </details>
        </div>
    </div>
</section>

@endsection
