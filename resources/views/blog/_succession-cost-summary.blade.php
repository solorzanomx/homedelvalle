{{-- "Respuesta corta" arriba del primer scroll (Fase 5.1 del prompt de leads del blog), solo en
     cuanto-cuesta-sucesion-cdmx-2026. Las cifras se leen EN VIVO de SuccessionCalculatorConfig — el
     mismo dato que usa la calculadora un poco más abajo en el post, así nunca se desalinean. --}}
@php
    $con = \App\Models\SuccessionCalculatorConfig::forScenario(\App\Models\SuccessionCalculatorConfig::CON_TESTAMENTO);
    $sin = \App\Models\SuccessionCalculatorConfig::forScenario(\App\Models\SuccessionCalculatorConfig::SIN_TESTAMENTO);
    $allValidated = $con && $sin && $con->validated && $sin->validated;
@endphp
@if($con && $sin)
<div class="not-prose my-6 rounded-2xl border border-gray-200 bg-gray-50/60 p-5 sm:p-6">
    <div class="text-xs font-bold uppercase tracking-wide text-gray-400 mb-3">Respuesta corta</div>
    @if(! $allValidated)
    <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 mb-3">⚠ Rango de referencia, todavía no confirmado con un notario para todos los casos — abajo tienes la calculadora con tu caso exacto.</p>
    @endif
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500">
                    <th class="py-1.5 pr-3 font-bold">Escenario</th>
                    <th class="py-1.5 pr-3 font-bold">Trámite (% del valor)</th>
                    <th class="py-1.5 font-bold">Tiempo estimado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <tr>
                    <td class="py-2 pr-3 font-semibold text-gray-900">Con testamento</td>
                    <td class="py-2 pr-3">{{ $con->notarial_pct_min }}% – {{ $con->notarial_pct_max }}%</td>
                    <td class="py-2">{{ $con->tiempo_min_meses }} a {{ $con->tiempo_max_meses }} meses</td>
                </tr>
                <tr>
                    <td class="py-2 pr-3 font-semibold text-gray-900">Sin testamento</td>
                    <td class="py-2 pr-3">{{ $sin->notarial_pct_min }}% – {{ $sin->notarial_pct_max }}%</td>
                    <td class="py-2">{{ $sin->tiempo_min_meses }} a {{ $sin->tiempo_max_meses }} meses</td>
                </tr>
            </tbody>
        </table>
    </div>
    <p class="mt-3 text-xs text-gray-500">Además del trámite hay ISAI, registro público, avalúo y otros gastos fijos — usa la calculadora más abajo para ver tu desglose completo con tu valor y número de herederos.</p>
</div>
@endif
