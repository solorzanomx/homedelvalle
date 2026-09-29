{{-- "Respuesta corta" arriba del primer scroll (Fase 5.1), solo en
     propiedad-sin-testamento-cdmx-como-regularizar-vender-2026. Los pasos resumen lo que el propio
     post ya explica más abajo (no son contenido nuevo); costo/tiempo se leen EN VIVO de
     SuccessionCalculatorConfig, mismo dato que la calculadora unos párrafos después. --}}
@php
    $sin = \App\Models\SuccessionCalculatorConfig::forScenario(\App\Models\SuccessionCalculatorConfig::SIN_TESTAMENTO);
    $steps = [
        'Confirma que no existe testamento registrado (Archivo General de Notarías CDMX o con cualquier notario).',
        'Reúne a los herederos y la documentación: actas, identificaciones, escritura, predial y agua al corriente.',
        'Tramita la sucesión ante notario si hay acuerdo entre todos — o ante el Juzgado de lo Familiar si hay menores, desacuerdo o herederos no localizables.',
        'Con la declaratoria de herederos y la adjudicación, escritura el inmueble a nombre de los herederos.',
    ];
@endphp
<div class="not-prose my-6 rounded-2xl border border-gray-200 bg-gray-50/60 p-5 sm:p-6">
    <div class="text-xs font-bold uppercase tracking-wide text-gray-400 mb-3">Respuesta corta</div>
    <ol class="space-y-2 text-sm text-gray-700">
        @foreach($steps as $i => $step)
        <li class="flex gap-3"><span class="shrink-0 flex items-center justify-center w-6 h-6 rounded-full bg-brand-500 text-white text-xs font-bold">{{ $i + 1 }}</span><span class="pt-0.5">{{ $step }}</span></li>
        @endforeach
    </ol>
    @if($sin)
    <div class="mt-4 flex flex-wrap gap-4 text-sm">
        <div><span class="text-gray-500">Costo aproximado:</span> <strong class="text-gray-900">{{ $sin->notarial_pct_min }}% – {{ $sin->notarial_pct_max }}%</strong> del valor del inmueble (+ISAI, registro, avalúo y otros)</div>
        <div><span class="text-gray-500">Tiempo estimado:</span> <strong class="text-gray-900">{{ $sin->tiempo_min_meses }} a {{ $sin->tiempo_max_meses }} meses</strong> con acuerdo entre herederos</div>
    </div>
    @if(! $sin->validated)
    <p class="mt-2 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">⚠ Rango de referencia, todavía no confirmado con un notario para todos los casos — la calculadora más abajo te da tu caso exacto.</p>
    @endif
    @endif
</div>
