@extends('layouts.public')

@section('meta')
    <x-public.seo-meta
        title="Renta tu propiedad en Benito Juárez"
        description="Renta tu inmueble en Benito Juárez con seguridad jurídica, póliza profesional y administración integral si la necesitas. Sin sorpresas, sin morosidad inesperada."
        :canonical="url('/renta-tu-propiedad')"
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
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                    Renta segura · Cupo limitado
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight">
                    Renta tu inmueble en Benito Juárez con cero dolores de cabeza
                </h1>
                <p class="mt-5 text-lg text-brand-200/80 leading-relaxed">
                    Calificamos al inquilino, gestionamos la póliza jurídica y, si lo prefieres, administramos el inmueble por ti. Sin morosidad inesperada, sin meses vacíos.
                </p>

                {{-- Stats --}}
                <div class="mt-10 grid grid-cols-2 gap-4">
                    @foreach([
                        ['30+','Años gestionando rentas en BJ'],
                        ['< 30 días','Tiempo promedio para colocar un inmueble bien presentado'],
                        ['98%','Pago puntual con póliza jurídica activa'],
                        ['50+','Inmuebles bajo administración integral'],
                    ] as [$num,$desc])
                    <div class="bg-white/8 backdrop-blur-sm rounded-2xl p-4 border border-white/10">
                        <p class="text-2xl font-black text-white">{{ $num }}</p>
                        <p class="text-xs text-brand-300/80 mt-1 leading-snug">{{ $desc }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Right: form --}}
            <div class="rounded-2xl bg-white p-8 lg:p-10 shadow-premium-xl" x-data x-intersect.once="$el.classList.add('animate-slide-in-right')">
                <livewire:forms.rental-owner-form />
            </div>
        </div>
    </div>
</section>

{{-- POR QUÉ CON NOSOTROS --}}
<section class="py-20 sm:py-24 bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
            <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Ventajas</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">¿Por qué rentar tu inmueble con nosotros?</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            @foreach([
                ['user-check','Calificación seria del inquilino','Verificamos identidad, ingresos, historial crediticio (cuando aplica) y referencias. Si no pasa, no firma. Tu inmueble no entra en riesgo por presión de cerrar.'],
                ['shield','Póliza jurídica profesional','Trabajamos con afianzadoras reconocidas. La póliza protege tu ingreso ante incumplimiento, daños o juicios. Si lo prefieres, estructuramos aval con propiedad.'],
                ['settings','Administración integral si la necesitas','Si no quieres preocuparte por cobranza, mantenimiento o trámites, nosotros lo hacemos. Reportes mensuales, cuenta clara, intervención inmediata cuando algo falla.'],
                ['target','Marketing y matching dirigidos','No publicamos en portales saturados. Tu inmueble llega sólo a inquilinos calificados que ya pasaron filtro por nuestro brief. Menos visitas, mejor calidad.'],
            ] as [$icon,$title,$desc])
            <div class="flex items-start gap-5 p-6 rounded-2xl bg-gray-50/60 border border-gray-200/60 hover:shadow-lg transition-all duration-300" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-brand-100 flex-shrink-0">
                    <x-icon name="{{ $icon }}" class="w-6 h-6 text-brand-600" />
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">{{ $title }}</h3>
                    <p class="mt-1.5 text-sm text-gray-500 leading-relaxed">{{ $desc }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- PROCESO --}}
<section class="py-20 sm:py-24 bg-brand-950">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
            <p class="text-sm font-semibold text-brand-400 uppercase tracking-widest mb-3">Proceso</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">Cómo trabajamos juntos</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach([
                ['01','Asesoría y precio de salida','Visitamos tu inmueble, analizamos comparables del mercado en tu colonia y te proponemos un rango de renta realista (no inflado para captar la firma).'],
                ['02','Marketing dirigido y filtro','Tomamos fotos profesionales, redactamos la ficha y la enviamos a inquilinos calificados de nuestra red. Te entregamos una shortlist con perfil, ingresos y referencias.'],
                ['03','Firma, póliza y entrega','Coordinamos firma de contrato, póliza jurídica activa, entrega del inmueble con inventario fotográfico y, si aplica, arranque de administración integral.'],
            ] as [$num,$title,$desc])
            <div class="relative" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <div class="flex items-start gap-5">
                    <span class="text-4xl font-black text-white/10 leading-none select-none">{{ $num }}</span>
                    <div>
                        <h3 class="text-lg font-bold text-white">{{ $title }}</h3>
                        <p class="mt-2 text-sm text-brand-300/70 leading-relaxed">{{ $desc }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- FAQ --}}
<section class="py-20 sm:py-24 bg-white">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
            <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Preguntas frecuentes</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Lo que necesitas saber</h2>
        </div>

        <div class="space-y-4" x-data="{ open: null }">
            @foreach([
                ['¿Cuánto cuesta poner mi inmueble en renta con ustedes?','Cero por adelantado. Cobramos comisión sólo cuando se firma contrato. La comisión estándar de mercado en CDMX es un mes de renta, negociable según el caso.'],
                ['¿En cuánto tiempo se renta un inmueble bien presentado?','Promedio en BJ: 15–30 días si el precio está alineado al mercado y la presentación es buena. Inmuebles muy específicos pueden tardar más; en la asesoría te decimos plazo realista.'],
                ['¿Qué pasa si el inquilino no paga?','Si tienes póliza jurídica activa, la afianzadora cubre y procede legalmente. Si optaste por aval, ejecutamos el aval con respaldo legal. En ambos casos, te acompañamos hasta resolver.'],
                ['¿Necesito firmar exclusividad?','No exigimos exclusividad. Si decides trabajar con varias inmobiliarias, está bien para nosotros. Sólo recuerda que múltiples publicaciones simultáneas pueden enviar señal de inmueble difícil de colocar.'],
                ['¿Cómo funciona la administración integral?','Cobramos un porcentaje mensual (típicamente 6–10% de la renta dependiendo del servicio). Cubre: cobranza, atención al inquilino, mantenimiento, reportes mensuales e intervención legal si es necesario.'],
                ['¿Aceptan rentas vacacionales o por días?','Hoy nuestro foco es renta tradicional (mínimo 6 meses). Si buscas vacacional, podemos referirte a operadores especializados con quienes trabajamos.'],
            ] as $i => [$q,$a])
            <div class="rounded-2xl border border-gray-200/80 overflow-hidden" x-data="{ open: null }">
                <button @click="open = open === {{ $i }} ? null : {{ $i }}"
                        class="w-full text-left flex items-center justify-between gap-4 px-6 py-5 text-sm font-semibold text-gray-900 hover:bg-gray-50 transition-colors">
                    {{ $q }}
                    <x-icon name="chevron-down" class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform duration-200" ::class="open === {{ $i }} ? 'rotate-180' : ''" />
                </button>
                <div x-show="open === {{ $i }}" x-collapse class="px-6 pb-5 text-sm text-gray-500 leading-relaxed">
                    {{ $a }}
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- BANDA FINAL --}}
<section class="py-20 sm:py-24 bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">¿Listo para rentar tu inmueble con tranquilidad?</h2>
        <p class="mt-3 text-brand-200 text-lg">Asesoría gratuita en menos de 24 horas. Sin exclusividad forzada. Cero compromiso.</p>
        <div class="mt-8">
            <a href="#inicio" class="inline-flex items-center gap-2 px-8 py-3.5 rounded-xl bg-white text-brand-700 font-bold text-sm hover:bg-brand-50 transition-all shadow-lg hover:shadow-xl">
                Solicitar mi asesoría
                <x-icon name="arrow-right" class="w-4 h-4" />
            </a>
        </div>
    </div>
</section>

@endsection
