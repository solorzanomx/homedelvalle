@extends('layouts.public')

@section('meta')
    <x-public.seo-meta
        title="Renta de inmuebles en Benito Juárez"
        description="Encuentra el inmueble correcto para rentar en Benito Juárez. Inmuebles verificados, pólizas jurídicas claras y proceso transparente. Sin sorpresas en el contrato."
        :canonical="url('/rentar')"
    />
@endsection

@section('content')

{{-- HERO + FORM --}}
<section class="relative overflow-hidden bg-brand-950" id="inicio">
    <div class="absolute inset-0 bg-gradient-to-br from-brand-950 via-brand-900/90 to-brand-800/80"></div>
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_rgba(59,130,196,0.15)_0%,_transparent_60%)]"></div>
    <div class="absolute top-20 right-10 w-72 h-72 bg-brand-500/10 rounded-full blur-3xl animate-float"></div>
    <div class="absolute bottom-20 left-10 w-96 h-96 bg-brand-400/5 rounded-full blur-3xl animate-float animation-delay-300"></div>

    <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20 sm:py-28 lg:py-32">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-start">

            {{-- Left: copy --}}
            <div x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <div class="inline-flex items-center gap-2 rounded-full bg-white/10 border border-white/10 px-4 py-1.5 text-sm text-brand-200 backdrop-blur-sm mb-6">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Renta asistida · Benito Juárez
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight">
                    Encuentra dónde rentar en Benito Juárez sin contratiempos
                </h1>
                <p class="mt-5 text-lg text-brand-200/80 leading-relaxed">
                    Inmuebles verificados, pólizas jurídicas claras y un proceso transparente. Sin agentes que insisten ni sorpresas en el contrato.
                </p>

                {{-- Trust signals --}}
                <div class="mt-10 space-y-3">
                    @foreach([
                        ['30+ años','Experiencia senior en Benito Juárez'],
                        ['< 72 h','Primera selección curada después de tu brief'],
                        ['8 zonas','Cobertura especializada en Benito Juárez'],
                        ['0 letras chicas','Pólizas jurídicas claras, sin cláusulas escondidas'],
                    ] as [$stat,$label])
                    <div class="flex items-center gap-4">
                        <span class="text-sm font-bold text-white bg-white/10 rounded-lg px-3 py-1.5 whitespace-nowrap">{{ $stat }}</span>
                        <span class="text-sm text-brand-200/80">{{ $label }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Right: form --}}
            <div class="rounded-2xl bg-white p-8 lg:p-10 shadow-premium-xl" x-data x-intersect.once="$el.classList.add('animate-slide-in-right')">
                <livewire:forms.renter-search-form />
            </div>
        </div>
    </div>
</section>

{{-- CÓMO FUNCIONA --}}
<section class="py-20 sm:py-24 bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
            <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Proceso</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Cómo funciona</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach([
                ['01','Cuéntanos qué buscas','Brief de 2 minutos: zona, presupuesto mensual, recámaras, mascotas, plazo y forma de garantizar la renta. Mientras más claro, mejor te encontramos lo correcto.'],
                ['02','Curamos opciones reales','Filtramos nuestro inventario y nuestra red. Te enviamos 3–5 opciones que cumplen con tu brief. Si no hay match, activamos alerta y te avisamos cuando entre algo.'],
                ['03','Te ayudamos a firmar con seguridad','Revisamos el contrato y la póliza jurídica antes de que firmes. Te explicamos cláusulas, plazos, depósitos y obligaciones. Tu protección es parte del servicio.'],
            ] as [$num,$title,$desc])
            <div class="relative" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <div class="flex items-start gap-5">
                    <span class="text-4xl font-black text-brand-100 leading-none select-none">{{ $num }}</span>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">{{ $title }}</h3>
                        <p class="mt-2 text-sm text-gray-500 leading-relaxed">{{ $desc }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- POR QUÉ RENTAR CON NOSOTROS --}}
<section class="py-20 sm:py-24 bg-gray-50/60">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
            <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Ventajas</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">¿Por qué rentar con nosotros?</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            @foreach([
                ['key','Inventario fuera de portales','Trabajamos con propietarios que prefieren publicar discretamente. Una parte del inventario sólo se ofrece a través de nosotros.'],
                ['shield-check','Pólizas jurídicas claras','Si optas por póliza, te explicamos qué cubre, qué cuesta y cuál es la cobertura. Trabajamos sólo con afianzadoras autorizadas y reconocidas.'],
                ['heart','Pet-friendly cuando aplica','Tenemos propietarios que aceptan mascotas. Te matcheamos sólo con inmuebles donde tu mascota es bienvenida desde el día uno.'],
                ['handshake','Sin "comisión por hablar"','No cobramos al inquilino por buscar ni por mostrar. Nuestra remuneración la cubre el propietario al cierre.'],
            ] as [$icon,$title,$desc])
            <div class="flex items-start gap-5 p-6 rounded-2xl bg-white border border-gray-200/60 hover:shadow-lg transition-all duration-300" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-brand-50 flex-shrink-0">
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

{{-- FAQ --}}
<section class="py-20 sm:py-24 bg-white">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
            <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Preguntas frecuentes</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Resolvemos tus dudas</h2>
        </div>

        <div class="space-y-4" x-data="{ open: null }">
            @foreach([
                ['¿Cuánto cobran al inquilino?','Cero. La búsqueda y asesoría son gratuitas para ti. Nuestra comisión la paga el propietario al firmar contrato.'],
                ['¿Qué necesito para rentar?','Generalmente: identificación oficial, comprobante de ingresos (3 últimos meses) o aval con propiedad, comprobante de domicilio actual y RFC. Si vas con póliza jurídica, los requisitos los marca la afianzadora.'],
                ['¿Qué es una póliza jurídica y por qué la pedirían?','Es un instrumento que reemplaza al fiador tradicional. Una afianzadora cubre al propietario en caso de incumplimiento. Para ti como inquilino, suele ser más rápido de tramitar que conseguir un aval con propiedad.'],
                ['¿Aceptan inquilinos con mascotas?','Sí, dentro del inventario que las acepta. Cuando llenas tu brief y marcas que tienes mascota, sólo te enviamos opciones donde se permiten.'],
                ['¿Puedo cambiar mi brief después?','Sí, en cualquier momento. Si después de la primera curaduría quieres ajustar zona, presupuesto o plazo, lo actualizamos y volvemos a buscar.'],
                ['¿Cuánto suele tardar todo el proceso?','Desde la primera curaduría hasta firmar contrato, entre 7 y 21 días si el inquilino tiene documentación lista y elige una opción que ya tiene póliza pre-aprobada.'],
            ] as $i => [$q,$a])
            <div class="rounded-2xl border border-gray-200/80 overflow-hidden" x-data>
                <button @click="$data.open === {{ $i }} ? $data.open = null : $data.open = {{ $i }}"
                        class="w-full text-left flex items-center justify-between gap-4 px-6 py-5 text-sm font-semibold text-gray-900 hover:bg-gray-50 transition-colors"
                        x-data="{ open: null }"
                        @click="open = open === {{ $i }} ? null : {{ $i }}">
                    {{ $q }}
                    <x-icon name="chevron-down" class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform duration-200" ::class="open === {{ $i }} ? 'rotate-180' : ''" />
                </button>
                <div x-data="{ open: null }" x-show="open === {{ $i }}" x-collapse class="px-6 pb-5 text-sm text-gray-500 leading-relaxed">
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
        <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">¿Listo para encontrar dónde rentar?</h2>
        <p class="mt-3 text-brand-200 text-lg">Brief de 2 minutos. Curaduría en 72 horas. Cero compromiso.</p>
        <div class="mt-8">
            <a href="#inicio" class="inline-flex items-center gap-2 px-8 py-3.5 rounded-xl bg-white text-brand-700 font-bold text-sm hover:bg-brand-50 transition-all shadow-lg hover:shadow-xl">
                Iniciar mi búsqueda
                <x-icon name="arrow-right" class="w-4 h-4" />
            </a>
        </div>
    </div>
</section>

@endsection
