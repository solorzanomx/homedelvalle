@extends('layouts.public')

@section('title', $page?->meta_title ?? 'Búsqueda asistida de inmuebles en Benito Juárez')

@section('meta')
    <x-public.seo-meta
        :title="$page?->meta_title ?? 'Búsqueda asistida de inmuebles en Benito Juárez | Home del Valle'"
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
                <h2 class="text-xl font-bold text-gray-900">Cuéntanos qué buscas</h2>
                <p class="text-gray-500 mt-1.5 mb-6 text-sm">Respuesta curada en menos de 72 horas.</p>
                <form method="POST" action="{{ route('landing.compra.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de inmueble <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(['departamento'=>'Departamento','casa'=>'Casa','terreno'=>'Terreno','oficina'=>'Oficina','comercial'=>'Comercial'] as $v=>$l)
                            <label class="flex items-center gap-2 p-2 rounded-lg border border-gray-200 cursor-pointer text-xs hover:border-brand-400">
                                <input type="checkbox" name="tipo_inmueble[]" value="{{ $v }}" {{ in_array($v,old('tipo_inmueble',[])) ? 'checked':'' }} class="h-4 w-4 text-brand-600"> {{ $l }}
                            </label>
                            @endforeach
                        </div>
                        @error('tipo_inmueble')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Presupuesto <span class="text-red-500">*</span></label>
                            <select name="presupuesto" required class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/30">
                                <option value="">—</option>
                                <option value="hasta_4m">Hasta $4M</option><option value="4m_6m">$4M–$6M</option>
                                <option value="6m_9m">$6M–$9M</option><option value="9m_14m">$9M–$14M</option><option value="14m_plus">$14M+</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Recámaras mín. <span class="text-red-500">*</span></label>
                            <select name="recamaras" required class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/30">
                                <option value="">—</option>
                                <option value="1">1+</option><option value="2">2+</option><option value="3">3+</option><option value="4+">4+</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Zonas de interés <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-1.5">
                            @foreach(['del_valle'=>'Del Valle','narvarte'=>'Narvarte','napoles'=>'Nápoles','portales'=>'Portales','alamos'=>'Álamos','roma_sur'=>'Roma Sur','otra'=>'Otra BJ'] as $v=>$l)
                            <label class="flex items-center gap-2 p-1.5 rounded-lg border border-gray-200 cursor-pointer text-xs hover:border-brand-400">
                                <input type="checkbox" name="zonas[]" value="{{ $v }}" {{ in_array($v,old('zonas',[])) ? 'checked':'' }} class="h-3.5 w-3.5 text-brand-600"> {{ $l }}
                            </label>
                            @endforeach
                        </div>
                        @error('zonas')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Forma de pago <span class="text-red-500">*</span></label>
                            <select name="pago" required class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/30">
                                <option value="">—</option>
                                <option value="contado">Contado</option><option value="credito">Crédito</option>
                                <option value="infonavit">INFONAVIT</option><option value="fovissste">FOVISSSTE</option><option value="mixto">Mixto</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Timing <span class="text-red-500">*</span></label>
                            <select name="timing" required class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/30">
                                <option value="">—</option>
                                <option value="inmediato">Inmediato</option><option value="1_3m">1–3 meses</option>
                                <option value="3_6m">3–6 meses</option><option value="explorando">Solo explorando</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div><label class="block text-xs font-medium text-gray-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                            <input type="text" name="nombre" value="{{ old('nombre') }}" required class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm" placeholder="Tu nombre">
                            @error('nombre')<p class="text-red-500 text-xs">{{ $message }}</p>@enderror</div>
                        <div><label class="block text-xs font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm" placeholder="tu@email.com">
                            @error('email')<p class="text-red-500 text-xs">{{ $message }}</p>@enderror</div>
                        <div><label class="block text-xs font-medium text-gray-700 mb-1">WhatsApp <span class="text-red-500">*</span></label>
                            <input type="tel" name="whatsapp" value="{{ old('whatsapp') }}" required class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm" placeholder="55 1234 5678">
                            @error('whatsapp')<p class="text-red-500 text-xs">{{ $message }}</p>@enderror</div>
                    </div>
                    <div class="flex items-start gap-2">
                        <input type="checkbox" name="aviso" id="aviso_compra" value="1" {{ old('aviso') ? 'checked':'' }} required class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-600">
                        <label for="aviso_compra" class="text-xs text-gray-500">Acepto el <a href="{{ url('/aviso-de-privacidad') }}" target="_blank" class="text-brand-600 underline">Aviso de Privacidad</a></label>
                    </div>
                    @error('aviso')<p class="text-red-500 text-xs -mt-2">{{ $message }}</p>@enderror
                    <button type="submit" class="w-full rounded-xl bg-brand-600 px-6 py-3.5 text-sm font-bold text-white hover:bg-brand-700 transition-all">
                        Recibir mi selección curada →
                    </button>
                    <p class="text-center text-xs text-gray-400">Respuesta en &lt; 72 horas · Sin compromiso · Sin spam</p>
                </form>
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
