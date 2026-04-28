@extends('layouts.public')

@section('title', $page?->meta_title ?? 'Captación de predios e inversión inmobiliaria en Benito Juárez')

@section('meta')
    <x-public.seo-meta
        :title="$page?->meta_title ?? 'Captación de predios e inversión inmobiliaria en Benito Juárez'"
        :description="$page?->meta_description ?? 'Captación de terrenos y producto terminado en Benito Juárez bajo demanda activa. Red de inversionistas consolidada.'"
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
                    Captación B2B
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight">
                    Terrenos e inversión inmobiliaria en Benito Juárez
                </h1>
                <p class="mt-5 text-lg text-brand-200/80 leading-relaxed">
                    Captación de predios y producto terminado bajo demanda activa. Red consolidada de desarrolladores e inversionistas calificados.
                </p>

                {{-- Trust signals --}}
                <div class="mt-10 space-y-3">
                    <div class="flex items-center gap-3">
                        <x-icon name="check" class="w-5 h-5 text-emerald-400 flex-shrink-0" />
                        <span class="text-sm text-brand-200/80">Análisis de potencial e inversión</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-icon name="check" class="w-5 h-5 text-emerald-400 flex-shrink-0" />
                        <span class="text-sm text-brand-200/80">Red de compradores institucionales</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-icon name="check" class="w-5 h-5 text-emerald-400 flex-shrink-0" />
                        <span class="text-sm text-brand-200/80">Estructuración de operaciones complejas</span>
                    </div>
                </div>
            </div>

            {{-- Right: form --}}
            <div class="rounded-2xl bg-white p-8 lg:p-10 shadow-premium-xl" x-data x-intersect.once="$el.classList.add('animate-slide-in-right')">
                <livewire:forms.developer-brief-form />
            </div>
        </div>
    </div>
</section>

{{-- LINES OF BUSINESS SECTION --}}
<section class="py-20 sm:py-24 bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
            <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Líneas de Captación</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Buscamos tu tipo de activo</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="p-6 rounded-2xl bg-brand-50/50 border border-brand-100">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-brand-500/15 mb-4">
                    <x-icon name="home" class="w-6 h-6 text-brand-600" />
                </div>
                <h3 class="text-base font-bold text-gray-900 mt-4">Terrenos</h3>
                <p class="text-sm text-gray-500 mt-2 leading-relaxed">Predios para proyecto habitacional, comercial o mixto.</p>
            </div>

            <div class="p-6 rounded-2xl bg-brand-50/50 border border-brand-100">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-brand-500/15 mb-4">
                    <x-icon name="building" class="w-6 h-6 text-brand-600" />
                </div>
                <h3 class="text-base font-bold text-gray-900 mt-4">Producto Terminado</h3>
                <p class="text-sm text-gray-500 mt-2 leading-relaxed">Desarrollos listos con demanda calificada.</p>
            </div>

            <div class="p-6 rounded-2xl bg-brand-50/50 border border-brand-100">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-brand-500/15 mb-4">
                    <x-icon name="handshake" class="w-6 h-6 text-brand-600" />
                </div>
                <h3 class="text-base font-bold text-gray-900 mt-4">Coinversión</h3>
                <p class="text-sm text-gray-500 mt-2 leading-relaxed">Estructura de capital y alianzas estratégicas.</p>
            </div>

            <div class="p-6 rounded-2xl bg-brand-50/50 border border-brand-100">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-brand-500/15 mb-4">
                    <x-icon name="bar-chart-3" class="w-6 h-6 text-brand-600" />
                </div>
                <h3 class="text-base font-bold text-gray-900 mt-4">Asesoría</h3>
                <p class="text-sm text-gray-500 mt-2 leading-relaxed">Análisis de mercado e inversión especializados.</p>
            </div>
        </div>
    </div>
</section>

{{-- VENTAJAS SECTION --}}
<section class="py-20 sm:py-24 bg-gray-50/60">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
            <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Ventajas</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">¿Por qué asociarse con nosotros?</h2>
            <p class="mt-5 text-lg text-gray-500">Especialistas en Benito Juárez con acceso a fuentes de oportunidad y capital institucional.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">
            <div class="p-6 rounded-2xl bg-white border border-gray-200/80 hover:border-brand-200 hover:shadow-premium-lg transition-all duration-500" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-brand-500/15 mb-4">
                    <x-icon name="target" class="w-6 h-6 text-brand-600" />
                </div>
                <h3 class="text-base font-bold text-gray-900 mt-4">Demanda Verificada</h3>
                <p class="text-sm text-gray-500 mt-2 leading-relaxed">Acceso a cartera de compradores institucionales y privados pre-calificados.</p>
            </div>

            <div class="p-6 rounded-2xl bg-white border border-gray-200/80 hover:border-brand-200 hover:shadow-premium-lg transition-all duration-500" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: 100ms">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-brand-500/15 mb-4">
                    <x-icon name="briefcase" class="w-6 h-6 text-brand-600" />
                </div>
                <h3 class="text-base font-bold text-gray-900 mt-4">Expertise Integral</h3>
                <p class="text-sm text-gray-500 mt-2 leading-relaxed">Análisis de inversión, valuación, legal y estructuración de operaciones complejas.</p>
            </div>

            <div class="p-6 rounded-2xl bg-white border border-gray-200/80 hover:border-brand-200 hover:shadow-premium-lg transition-all duration-500" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: 200ms">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-brand-500/15 mb-4">
                    <x-icon name="users" class="w-6 h-6 text-brand-600" />
                </div>
                <h3 class="text-base font-bold text-gray-900 mt-4">Red Consolidada</h3>
                <p class="text-sm text-gray-500 mt-2 leading-relaxed">Conexiones de 10+ años con desarrolladores, arquitectos y asesores inmobiliarios.</p>
            </div>
        </div>
    </div>
</section>

{{-- CÓMO FUNCIONA SECTION --}}
<section class="py-20 sm:py-24 bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
            <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Proceso</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Cómo trabajamos juntos</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="relative p-8 rounded-2xl bg-brand-50/50 border border-gray-200/80 hover:border-brand-200 hover:shadow-premium-lg transition-all duration-500" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <div class="flex items-center justify-center w-12 h-12 rounded-2xl gradient-brand text-white text-lg font-extrabold mb-6 shadow-brand">
                    01
                </div>
                <h3 class="text-lg font-bold text-gray-900">Presenta tu activo</h3>
                <p class="mt-3 text-sm text-gray-500 leading-relaxed">Comparte detalles del proyecto: ubicación, tipología, presupuesto, timeline y expectativas financieras.</p>
            </div>

            <div class="relative p-8 rounded-2xl bg-brand-50/50 border border-gray-200/80 hover:border-brand-200 hover:shadow-premium-lg transition-all duration-500" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: 100ms">
                <div class="flex items-center justify-center w-12 h-12 rounded-2xl gradient-brand text-white text-lg font-extrabold mb-6 shadow-brand">
                    02
                </div>
                <h3 class="text-lg font-bold text-gray-900">Análisis y valuación</h3>
                <p class="mt-3 text-sm text-gray-500 leading-relaxed">Evaluamos potencial de inversión, ROI, riesgos y oportunidades de mercado con datos actuales.</p>
            </div>

            <div class="relative p-8 rounded-2xl bg-brand-50/50 border border-gray-200/80 hover:border-brand-200 hover:shadow-premium-lg transition-all duration-500" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: 200ms">
                <div class="flex items-center justify-center w-12 h-12 rounded-2xl gradient-brand text-white text-lg font-extrabold mb-6 shadow-brand">
                    03
                </div>
                <h3 class="text-lg font-bold text-gray-900">Intermediación y cierre</h3>
                <p class="mt-3 text-sm text-gray-500 leading-relaxed">Te conectamos con compradores calificados y acompañamos la negociación hasta la firma de contrato.</p>
            </div>
        </div>
    </div>
</section>

{{-- FAQ SECTION --}}
<section class="py-20 sm:py-24 bg-gray-50/60">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Preguntas frecuentes</h2>
        </div>

        <div class="space-y-4">
            <details class="group rounded-2xl border border-gray-200/80 p-6 hover:border-brand-200 transition-all duration-300 [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex cursor-pointer items-center justify-between font-medium text-gray-900">
                    ¿Cuánto cuesta usar los servicios de Home del Valle?
                    <span class="transition group-open:rotate-180">
                        <x-icon name="chevron-down" class="w-5 h-5 text-gray-500" />
                    </span>
                </summary>
                <p class="mt-4 text-sm text-gray-500 leading-relaxed">No cobramos por análisis ni asesoría inicial. Solo aplicamos una comisión por intermediación al cerrar exitosamente la operación, negociada según la complejidad y montos involucrados.</p>
            </details>

            <details class="group rounded-2xl border border-gray-200/80 p-6 hover:border-brand-200 transition-all duration-300 [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex cursor-pointer items-center justify-between font-medium text-gray-900">
                    ¿Qué tan rápido pueden colocar un proyecto?
                    <span class="transition group-open:rotate-180">
                        <x-icon name="chevron-down" class="w-5 h-5 text-gray-500" />
                    </span>
                </summary>
                <p class="mt-4 text-sm text-gray-500 leading-relaxed">Depende del tipo de activo y mercado. Terrenos calificados en zonas premium se colocan en 30-60 días. Producto terminado con demanda activa, en 15-45 días. Operaciones complejas requieren 60-90 días de negociación.</p>
            </details>

            <details class="group rounded-2xl border border-gray-200/80 p-6 hover:border-brand-200 transition-all duration-300 [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex cursor-pointer items-center justify-between font-medium text-gray-900">
                    ¿Cuáles son los requisitos para presentar un proyecto?
                    <span class="transition group-open:rotate-180">
                        <x-icon name="chevron-down" class="w-5 h-5 text-gray-500" />
                    </span>
                </summary>
                <p class="mt-4 text-sm text-gray-500 leading-relaxed">Información básica: ubicación exacta, m² totales y vendibles, tipo de proyecto, precio/presupuesto, documentos de propiedad (si hay producto), planos (si disponible) y timeline esperado. Luego se solicitan documentos complementarios según la etapa.</p>
            </details>

            <details class="group rounded-2xl border border-gray-200/80 p-6 hover:border-brand-200 transition-all duration-300 [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex cursor-pointer items-center justify-between font-medium text-gray-900">
                    ¿Cómo funciona la intermediación con compradores institucionales?
                    <span class="transition group-open:rotate-180">
                        <x-icon name="chevron-down" class="w-5 h-5 text-gray-500" />
                    </span>
                </summary>
                <p class="mt-4 text-sm text-gray-500 leading-relaxed">Evaluamos tu proyecto y lo presentamos selectivamente a inversionistas, fondos inmobiliarios y desarrolladores en nuestra cartera que cumplen perfil. Gestionamos la negociación, DD (due diligence) y cierre legal-financiero. Confidencialidad garantizada en todo el proceso.</p>
            </details>

            <details class="group rounded-2xl border border-gray-200/80 p-6 hover:border-brand-200 transition-all duration-300 [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex cursor-pointer items-center justify-between font-medium text-gray-900">
                    ¿Ofrecen asesoría en estructuración de operaciones?
                    <span class="transition group-open:rotate-180">
                        <x-icon name="chevron-down" class="w-5 h-5 text-gray-500" />
                    </span>
                </summary>
                <p class="mt-4 text-sm text-gray-500 leading-relaxed">Sí. Podemos asesorar en coinversión, financiamiento, garantías, esquemas fiscales y legales complejos. Trabajamos con abogados, notarios y asesores fiscales especializados para estructurar operaciones de alto valor.</p>
            </details>
        </div>
    </div>
</section>

@endsection
