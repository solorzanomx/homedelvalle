@extends('layouts.public')

@section('title', $page?->meta_title ?? 'Captación de predios e inversión inmobiliaria en Benito Juárez')

@section('meta')
    <x-public.seo-meta
        :title="$page?->meta_title ?? 'Captación de predios e inversión inmobiliaria en Benito Juárez | Home del Valle'"
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
                <h2 class="text-xl font-bold text-gray-900">Solicita tu brief calificador</h2>
                <p class="text-gray-500 mt-1.5 mb-6 text-sm">Agendamos llamada en menos de 48 horas.</p>
                <form method="POST" action="{{ route('landing.desarrolladores.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de operación <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(['compra_predio'=>'Compra de predio','compra_terminado'=>'Producto terminado','coinversion'=>'Coinversión / JV','asesoria'=>'Asesoría puntual'] as $v=>$l)
                            <label class="flex items-center gap-2 p-2 rounded-lg border border-gray-200 cursor-pointer text-xs hover:border-brand-400">
                                <input type="checkbox" name="tipo_operacion[]" value="{{ $v }}" {{ in_array($v,old('tipo_operacion',[])) ? 'checked':'' }} class="h-4 w-4 text-brand-600"> {{ $l }}
                            </label>
                            @endforeach
                        </div>
                        @error('tipo_operacion')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Uso objetivo <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach(['vertical'=>'Habitacional vertical','horizontal'=>'Horizontal','mixto'=>'Mixto','comercial'=>'Comercial','oficinas'=>'Oficinas','industrial'=>'Industrial'] as $v=>$l)
                            <label class="flex items-center gap-1.5 p-1.5 rounded-lg border border-gray-200 cursor-pointer text-xs hover:border-brand-400">
                                <input type="checkbox" name="uso[]" value="{{ $v }}" {{ in_array($v,old('uso',[])) ? 'checked':'' }} class="h-3.5 w-3.5 text-brand-600"> {{ $l }}
                            </label>
                            @endforeach
                        </div>
                        @error('uso')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rango m² terreno <span class="text-red-500">*</span></label>
                            <select name="m2_terreno" required class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/30">
                                <option value="">—</option>
                                <option value="menos_200">&lt; 200 m²</option><option value="200_400">200–400 m²</option>
                                <option value="400_800">400–800 m²</option><option value="800_1500">800–1500 m²</option><option value="1500_plus">1500+ m²</option>
                            </select>
                            @error('m2_terreno')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Presupuesto (MXN) <span class="text-red-500">*</span></label>
                            <select name="presupuesto" required class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/30">
                                <option value="">—</option>
                                <option value="menos_20m">&lt; $20M</option><option value="20m_50m">$20M–$50M</option>
                                <option value="50m_120m">$50M–$120M</option><option value="120m_300m">$120M–$300M</option><option value="300m_plus">$300M+</option>
                            </select>
                            @error('presupuesto')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Zonas Benito Juárez <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-1.5">
                            @foreach(['del_valle'=>'Del Valle','narvarte'=>'Narvarte','napoles'=>'Nápoles','portales'=>'Portales','alamos'=>'Álamos','roma_sur'=>'Roma Sur','deportes'=>'Cd. Deportes','cualquier'=>'Cualquier BJ'] as $v=>$l)
                            <label class="flex items-center gap-2 p-1.5 rounded-lg border border-gray-200 cursor-pointer text-xs hover:border-brand-400">
                                <input type="checkbox" name="zonas[]" value="{{ $v }}" {{ in_array($v,old('zonas',[])) ? 'checked':'' }} class="h-3.5 w-3.5 text-brand-600"> {{ $l }}
                            </label>
                            @endforeach
                        </div>
                        @error('zonas')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Horizonte de inversión <span class="text-red-500">*</span></label>
                        <select name="horizonte" required class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/30">
                            <option value="">—</option>
                            <option value="6m">≤ 6 meses</option><option value="6_12m">6–12 meses</option>
                            <option value="12_24m">12–24 meses</option><option value="24m_plus">24+ meses</option>
                        </select>
                        @error('horizonte')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-xs font-medium text-gray-700 mb-1">Empresa <span class="text-red-500">*</span></label>
                            <input type="text" name="empresa" value="{{ old('empresa') }}" required class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm" placeholder="Nombre o entidad">
                            @error('empresa')<p class="text-red-500 text-xs">{{ $message }}</p>@enderror</div>
                        <div><label class="block text-xs font-medium text-gray-700 mb-1">Nombre y rol <span class="text-red-500">*</span></label>
                            <input type="text" name="nombre_rol" value="{{ old('nombre_rol') }}" required class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm" placeholder="Nombre · Director">
                            @error('nombre_rol')<p class="text-red-500 text-xs">{{ $message }}</p>@enderror</div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-xs font-medium text-gray-700 mb-1">Email corporativo <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm" placeholder="tu@empresa.com">
                            @error('email')<p class="text-red-500 text-xs">{{ $message }}</p>@enderror</div>
                        <div><label class="block text-xs font-medium text-gray-700 mb-1">Teléfono <span class="text-red-500">*</span></label>
                            <input type="tel" name="telefono" value="{{ old('telefono') }}" required class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm" placeholder="55 1234 5678">
                            @error('telefono')<p class="text-red-500 text-xs">{{ $message }}</p>@enderror</div>
                    </div>
                    <div class="flex items-start gap-2">
                        <input type="checkbox" name="aviso" id="aviso_dev" value="1" {{ old('aviso') ? 'checked':'' }} required class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-600">
                        <label for="aviso_dev" class="text-xs text-gray-500">Acepto el <a href="{{ url('/aviso-de-privacidad') }}" target="_blank" class="text-brand-600 underline">Aviso de Privacidad</a>. Información tratada bajo confidencialidad.</label>
                    </div>
                    @error('aviso')<p class="text-red-500 text-xs -mt-2">{{ $message }}</p>@enderror
                    <button type="submit" class="w-full rounded-xl bg-brand-600 px-6 py-3.5 text-sm font-bold text-white hover:bg-brand-700 transition-all">
                        Enviar brief calificador →
                    </button>
                    <p class="text-center text-xs text-gray-400">Respuesta en &lt; 48 horas · Tratado bajo confidencialidad</p>
                </form>
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
