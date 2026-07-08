@extends('layouts.public')

@section('title', $page?->meta_title ?? 'Vende tu casa o predio a una desarrolladora en Benito Juárez')

@section('meta')
    <x-public.seo-meta
        :title="$page?->meta_title ?? 'Vende tu casa o predio a una desarrolladora en Benito Juárez'"
        :description="$page?->meta_description ?? 'Tu casa en Benito Juárez podría valer más como terreno. Tenemos cartera propia de constructoras buscando predios en Del Valle, Narvarte, Nápoles, Portales y Xoco. Evaluación confidencial y sin compromiso.'"
    />
    <x-public.json-ld type="FAQPage" :data="[
        'mainEntity' => [
            ['@type'=>'Question','name'=>'¿Por qué mi casa puede valer más como terreno?','acceptedAnswer'=>['@type'=>'Answer','text'=>'Cuando el uso de suelo de tu colonia permite construir un edificio, una desarrolladora no compra tu casa: compra el potencial de construir varios departamentos sobre tu terreno. Ese potencial puede valer significativamente más que la casa como vivienda usada.']],
            ['@type'=>'Question','name'=>'¿Tengo que comprometerme a algo para saber cuánto vale?','acceptedAnswer'=>['@type'=>'Answer','text'=>'No. La evaluación es gratuita, confidencial y sin compromiso. Te decimos si tu predio encaja con la demanda activa de nuestra cartera de constructoras y cuánto podría interesarles pagar antes de que decidas cualquier cosa.']],
            ['@type'=>'Question','name'=>'¿Qué pasa si mi casa está habitada o rentada?','acceptedAnswer'=>['@type'=>'Answer','text'=>'Es lo más común. Los tiempos de una operación con desarrolladora suelen dar margen para planear la mudanza o el fin del contrato de renta. Lo importante es evaluar primero si hay interés real.']],
            ['@type'=>'Question','name'=>'¿Cómo sé que la oferta de la desarrolladora es justa?','acceptedAnswer'=>['@type'=>'Answer','text'=>'Nosotros representamos al propietario, no a la constructora. Analizamos el potencial constructivo real de tu predio (uso de suelo, niveles permitidos, superficie) y lo comparamos con operaciones reales de la zona para que negocies con datos.']],
        ],
    ]" />
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
                    Demanda activa de constructoras
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight">
                    Tu casa podría valer más como terreno
                </h1>
                <p class="mt-5 text-lg text-brand-200/80 leading-relaxed">
                    Tenemos cartera propia de constructoras buscando predios en Del Valle, Narvarte, Nápoles, Portales y Xoco — ahora mismo. Operamos desde la demanda, no desde la oferta: no salimos a buscar comprador para tu casa, ya sabemos quién la quiere como terreno.
                </p>

                {{-- Trust signals --}}
                <div class="mt-10 space-y-3">
                    <div class="flex items-center gap-3">
                        <x-icon name="check" class="w-5 h-5 text-emerald-400 flex-shrink-0" />
                        <span class="text-sm text-brand-200/80">Evaluación gratuita, confidencial y sin compromiso</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-icon name="check" class="w-5 h-5 text-emerald-400 flex-shrink-0" />
                        <span class="text-sm text-brand-200/80">Te representamos a ti, no a la constructora</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-icon name="check" class="w-5 h-5 text-emerald-400 flex-shrink-0" />
                        <span class="text-sm text-brand-200/80">Análisis de potencial constructivo con datos reales de Benito Juárez</span>
                    </div>
                </div>
            </div>

            {{-- Right: form --}}
            <div class="rounded-2xl bg-white p-8 lg:p-10 shadow-premium-xl" x-data x-intersect.once="$el.classList.add('animate-slide-in-right')">
                <livewire:forms.land-seller-form />
            </div>
        </div>
    </div>
</section>

{{-- POR QUÉ VALE MÁS --}}
<section class="py-20 sm:py-24 bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
            <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">El valor oculto</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Una desarrolladora no compra tu casa. Compra lo que puede construir sobre tu terreno.</h2>
            <p class="mt-5 text-lg text-gray-500">Si el uso de suelo de tu colonia permite un edificio, tu predio vale por los departamentos que caben en él — no por la casa que hay hoy.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="p-6 rounded-2xl bg-brand-50/50 border border-brand-100">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-brand-500/15 mb-4">
                    <x-icon name="map-pin" class="w-6 h-6 text-brand-600" />
                </div>
                <h3 class="text-base font-bold text-gray-900 mt-4">La colonia</h3>
                <p class="text-sm text-gray-500 mt-2 leading-relaxed">Del Valle, Narvarte, Nápoles, Portales y Xoco concentran la demanda más activa de las constructoras.</p>
            </div>

            <div class="p-6 rounded-2xl bg-brand-50/50 border border-brand-100">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-brand-500/15 mb-4">
                    <x-icon name="building" class="w-6 h-6 text-brand-600" />
                </div>
                <h3 class="text-base font-bold text-gray-900 mt-4">El uso de suelo</h3>
                <p class="text-sm text-gray-500 mt-2 leading-relaxed">Los niveles que se pueden construir sobre tu predio definen cuánto vale para un desarrollador.</p>
            </div>

            <div class="p-6 rounded-2xl bg-brand-50/50 border border-brand-100">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-brand-500/15 mb-4">
                    <x-icon name="maximize-2" class="w-6 h-6 text-brand-600" />
                </div>
                <h3 class="text-base font-bold text-gray-900 mt-4">La superficie</h3>
                <p class="text-sm text-gray-500 mt-2 leading-relaxed">A partir de ~200 m² de terreno ya hay proyectos viables; los predios grandes y en esquina se disputan.</p>
            </div>

            <div class="p-6 rounded-2xl bg-brand-50/50 border border-brand-100">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-brand-500/15 mb-4">
                    <x-icon name="target" class="w-6 h-6 text-brand-600" />
                </div>
                <h3 class="text-base font-bold text-gray-900 mt-4">La demanda</h3>
                <p class="text-sm text-gray-500 mt-2 leading-relaxed">Nuestra cartera de constructoras nos dice qué busca antes de que tu predio salga al mercado.</p>
            </div>
        </div>

        <div class="text-center mt-10">
            <a href="{{ url('/blog/como-calcular-el-valor-real-de-tu-propiedad-en-benito-juarez-como-terreno-para-desarrollar') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-600 hover:text-brand-700 transition-colors">
                Lee la guía completa: cómo calcular el valor real de tu propiedad como terreno
                <x-icon name="arrow-right" class="w-4 h-4" />
            </a>
        </div>
    </div>
</section>

{{-- CÓMO FUNCIONA --}}
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
                <h3 class="text-lg font-bold text-gray-900">Cuéntanos de tu propiedad</h3>
                <p class="mt-3 text-sm text-gray-500 leading-relaxed">Colonia, superficie aproximada y situación actual. Con eso basta para la primera evaluación — no necesitas planos ni documentos todavía.</p>
            </div>

            <div class="relative p-8 rounded-2xl bg-white border border-gray-200/80 hover:border-brand-200 hover:shadow-premium-lg transition-all duration-500" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: 100ms">
                <div class="flex items-center justify-center w-12 h-12 rounded-2xl gradient-brand text-white text-lg font-extrabold mb-6 shadow-brand">
                    02
                </div>
                <h3 class="text-lg font-bold text-gray-900">La evaluamos contra la demanda real</h3>
                <p class="mt-3 text-sm text-gray-500 leading-relaxed">Analizamos el potencial constructivo de tu predio (uso de suelo, niveles, superficie) y lo cruzamos con lo que las constructoras de nuestra cartera están buscando hoy.</p>
            </div>

            <div class="relative p-8 rounded-2xl bg-white border border-gray-200/80 hover:border-brand-200 hover:shadow-premium-lg transition-all duration-500" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: 200ms">
                <div class="flex items-center justify-center w-12 h-12 rounded-2xl gradient-brand text-white text-lg font-extrabold mb-6 shadow-brand">
                    03
                </div>
                <h3 class="text-lg font-bold text-gray-900">Te conectamos y negociamos contigo</h3>
                <p class="mt-3 text-sm text-gray-500 leading-relaxed">Si hay interés, te presentamos a la desarrolladora y te acompañamos en la negociación y el cierre — representándote a ti, con datos, hasta la firma.</p>
            </div>
        </div>
    </div>
</section>

{{-- CONFIDENCIALIDAD / SIN COMPROMISO --}}
<section class="py-20 sm:py-24 bg-white">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl bg-brand-950 p-10 sm:p-14 text-center relative overflow-hidden" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_rgba(59,130,196,0.2)_0%,_transparent_60%)]"></div>
            <div class="relative">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-white/10 mb-6">
                    <x-icon name="shield-check" class="w-7 h-7 text-emerald-400" />
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Preguntar no te compromete a nada</h2>
                <p class="mt-4 text-brand-200/80 leading-relaxed max-w-2xl mx-auto">
                    Saber cuánto vale tu propiedad para una desarrolladora es información — no una decisión. La evaluación es confidencial: nadie de tu colonia, ni ninguna constructora, se entera de que preguntaste. Tú decides si quieres avanzar, y cuándo.
                </p>
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
                    ¿Por qué mi casa puede valer más como terreno?
                    <span class="transition group-open:rotate-180">
                        <x-icon name="chevron-down" class="w-5 h-5 text-gray-500" />
                    </span>
                </summary>
                <p class="mt-4 text-sm text-gray-500 leading-relaxed">Cuando el uso de suelo de tu colonia permite construir un edificio, una desarrolladora no compra tu casa: compra el potencial de construir varios departamentos sobre tu terreno. Ese potencial puede valer significativamente más que la casa como vivienda usada — sobre todo en colonias con alta demanda como Del Valle, Narvarte o Nápoles.</p>
            </details>

            <details class="group rounded-2xl border border-gray-200/80 p-6 hover:border-brand-200 transition-all duration-300 [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex cursor-pointer items-center justify-between font-medium text-gray-900">
                    ¿Tengo que comprometerme a algo para saber cuánto vale?
                    <span class="transition group-open:rotate-180">
                        <x-icon name="chevron-down" class="w-5 h-5 text-gray-500" />
                    </span>
                </summary>
                <p class="mt-4 text-sm text-gray-500 leading-relaxed">No. La evaluación es gratuita, confidencial y sin compromiso. Te decimos si tu predio encaja con la demanda activa de nuestra cartera de constructoras y cuánto podría interesarles pagar — antes de que decidas cualquier cosa.</p>
            </details>

            <details class="group rounded-2xl border border-gray-200/80 p-6 hover:border-brand-200 transition-all duration-300 [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex cursor-pointer items-center justify-between font-medium text-gray-900">
                    ¿Qué pasa si mi casa está habitada o rentada?
                    <span class="transition group-open:rotate-180">
                        <x-icon name="chevron-down" class="w-5 h-5 text-gray-500" />
                    </span>
                </summary>
                <p class="mt-4 text-sm text-gray-500 leading-relaxed">Es lo más común, y no es un impedimento. Los tiempos de una operación con desarrolladora suelen dar margen suficiente para planear la mudanza o el fin del contrato de renta. Lo importante es evaluar primero si hay interés real por tu predio.</p>
            </details>

            <details class="group rounded-2xl border border-gray-200/80 p-6 hover:border-brand-200 transition-all duration-300 [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex cursor-pointer items-center justify-between font-medium text-gray-900">
                    ¿Cómo sé que la oferta de la desarrolladora es justa?
                    <span class="transition group-open:rotate-180">
                        <x-icon name="chevron-down" class="w-5 h-5 text-gray-500" />
                    </span>
                </summary>
                <p class="mt-4 text-sm text-gray-500 leading-relaxed">Porque nosotros representamos al propietario, no a la constructora. Analizamos el potencial constructivo real de tu predio (uso de suelo, niveles permitidos, superficie vendible) y lo comparamos con operaciones reales de la zona, para que negocies con datos y no a ciegas.</p>
            </details>

            <details class="group rounded-2xl border border-gray-200/80 p-6 hover:border-brand-200 transition-all duration-300 [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex cursor-pointer items-center justify-between font-medium text-gray-900">
                    ¿Y si prefiero vender mi casa de forma tradicional?
                    <span class="transition group-open:rotate-180">
                        <x-icon name="chevron-down" class="w-5 h-5 text-gray-500" />
                    </span>
                </summary>
                <p class="mt-4 text-sm text-gray-500 leading-relaxed">También lo hacemos. Si tu propiedad vale más como vivienda que como terreno — o simplemente prefieres ese camino — te acompañamos con el proceso tradicional de venta. Empieza en <a href="{{ route('landing.vende') }}" class="text-brand-500 hover:text-brand-600 font-medium">vende tu propiedad</a>. Parte de la evaluación es justamente decirte cuál de los dos caminos te conviene.</p>
            </details>
        </div>

        <div class="text-center mt-12">
            <a href="#inicio" class="inline-flex items-center gap-2 rounded-xl gradient-brand px-8 py-4 text-sm font-semibold text-white shadow-brand hover:shadow-brand-lg hover:-translate-y-0.5 transition-all duration-300">
                Evaluar mi propiedad sin compromiso
                <x-icon name="arrow-right" class="w-4 h-4" />
            </a>
        </div>
    </div>
</section>

@endsection
