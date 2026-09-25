@extends('layouts.public')

@section('meta')
    <x-public.seo-meta
        title="Pólizas jurídicas de arrendamiento: los 3 planes de Previsión Legal"
        description="Si rentas sin un aval con propiedad en CDMX, la póliza jurídica respalda tu renta. Conoce qué incluye cada plan —Básica, Superior e Integral— y cómo funciona el proceso con Home del Valle."
        :canonical="url('/rentar/polizas-juridicas')"
    />
@endsection

@section('content')

<x-public.hero
    heading="Pólizas jurídicas de arrendamiento"
    subheading="Si no tienes un aval con propiedad en la CDMX, una póliza jurídica respalda tu renta. Estos son los tres planes de Previsión Legal y lo que incluye cada uno."
    :breadcrumb-items="[['label' => 'Rentar', 'url' => route('landing.rentar')], ['label' => 'Pólizas jurídicas']]"
/>

{{-- Cómo funciona --}}
<section class="relative py-16 sm:py-20 bg-white">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Cómo funciona</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Del plan a la firma, acompañado</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach([
                ['01', 'Reúnes tus documentos', 'Identificación, comprobante de domicilio y comprobante de ingresos, en tu portal.'],
                ['02', 'Se elige el plan', 'Tu propietario elige el plan (con el precio según la renta) y decide si lo cubres tú al 100% o mitad y mitad.'],
                ['03', 'Previsión Legal investiga', 'Ellos realizan la investigación del inquilino y emiten tu póliza. Tu asesor tramita el alta contigo.'],
                ['04', 'Contrato y firma', 'Previsión Legal emite el contrato de arrendamiento; lo revisas y lo firmas desde tu portal.'],
            ] as [$n, $title, $desc])
            <div class="p-6 rounded-2xl bg-white border border-gray-200/60">
                <div class="text-sm font-bold text-brand-500">{{ $n }}</div>
                <h3 class="mt-2 text-base font-bold text-gray-900">{{ $title }}</h3>
                <p class="mt-1.5 text-sm text-gray-500 leading-relaxed">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
        <p class="mt-6 text-center text-sm text-gray-500">Tu asesor te indica cómo y cuándo pagar tu parte. En Home del Valle no cobramos al inquilino por buscar ni por asesorar.</p>
    </div>
</section>

{{-- Calculadora por renta --}}
<section class="py-16 sm:py-20 bg-gray-50/70" x-data>
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">¿Cuánto cuesta?</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Calcula según tu renta</h2>
            <p class="mt-3 text-gray-500 max-w-2xl mx-auto">El precio de la póliza depende de la renta mensual. Escríbela y compara los tres planes.</p>
        </div>

        <div class="mx-auto max-w-md">
            <label for="polizaRent" class="block text-sm font-semibold text-gray-700 mb-2">Renta mensual (MXN)</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-4 flex items-center text-gray-400 font-semibold">$</span>
                <input id="polizaRent" type="text" inputmode="numeric" autocomplete="off" placeholder="15,000" class="w-full rounded-xl border border-gray-300 bg-white py-3.5 pl-9 pr-4 text-lg font-bold text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30">
            </div>
        </div>

        <div id="polizaResults" class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
            @foreach($plans as $plan)
            <div class="relative flex flex-col rounded-3xl bg-white p-7 {{ $plan->is_recommended ? 'border-2 border-brand-500 shadow-xl' : 'border border-gray-200/70 shadow-sm' }}" data-plan="{{ $plan->id }}">
                @if($plan->tagline)<span class="absolute -top-3 left-7 rounded-full bg-brand-600 px-3 py-1 text-xs font-bold text-white">{{ $plan->tagline }}</span>@endif
                <h3 class="text-2xl font-extrabold text-gray-900">{{ $plan->name }}</h3>
                @if($plan->description)<p class="mt-2 text-sm text-gray-500 leading-relaxed flex-1">{{ $plan->description }}</p>@endif
                <div class="mt-5 rounded-xl bg-brand-50/70 px-4 py-4">
                    <div class="poliza-price text-3xl font-extrabold text-brand-700">—</div>
                    <div class="poliza-basis text-xs text-gray-500 mt-1">Escribe tu renta para ver el precio.</div>
                </div>
                <p class="mt-3 text-xs text-gray-400">Incluye {{ $plan->includedCoverages()->count() }} de {{ $coverages->flatten()->count() }} conceptos de cobertura.</p>
            </div>
            @endforeach
        </div>

        @if($sheet)
        <p class="mt-6 text-center text-xs text-gray-400 max-w-3xl mx-auto leading-relaxed">
            Tarifas de la {{ $sheet->name }}{{ $sheet->zone_label ? ' — zona: ' . $sheet->zone_label : '' }}. Para todo trámite se cubren gastos de emisión de ${{ number_format($sheet->emission_fee) }} al iniciar; se acreditan al precio si la operación se concreta y no se reembolsan si no. Precios de referencia: pueden cambiar según la zona y las condiciones de la renta; confirma con tu asesor antes de contratar.
        </p>
        @endif
    </div>
</section>

{{-- Tabla comparativa (matriz oficial) --}}
<section class="py-16 sm:py-20 bg-white">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Coberturas</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Qué incluye cada plan</h2>
        </div>
        <div class="overflow-x-auto rounded-2xl border border-gray-200">
            <table class="w-full min-w-[560px] text-sm">
                <thead>
                    <tr class="bg-brand-50/70 text-left">
                        <th class="px-4 py-3 font-bold text-gray-700">Concepto</th>
                        @foreach($plans as $plan)<th class="px-3 py-3 text-center font-bold text-brand-700">{{ $plan->name }}</th>@endforeach
                    </tr>
                </thead>
                <tbody>
                @foreach($coverages as $section => $items)
                    <tr><td colspan="{{ 1 + $plans->count() }}" class="bg-gray-50 px-4 py-2 text-xs font-bold uppercase tracking-wide text-gray-500">{{ \App\Models\PolizaCoverage::SECTIONS[$section] ?? $section }}</td></tr>
                    @foreach($items as $cov)
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-3 text-gray-700 leading-snug">{{ $cov->label }}@if($cov->note)<span class="text-gray-400"> *</span>@endif</td>
                        @foreach($plans as $plan)
                            @php $inc = (bool) optional($cov->plans->firstWhere('id', $plan->id))->pivot?->included; @endphp
                            <td class="px-3 py-3 text-center">@if($inc)<span class="inline-flex h-6 w-6 items-center justify-center rounded-full gradient-brand text-white"><x-icon name="check" class="w-3.5 h-3.5" /></span>@else<span class="text-gray-300">—</span>@endif</td>
                        @endforeach
                    </tr>
                    @endforeach
                @endforeach
                </tbody>
            </table>
        </div>
        <p class="mt-3 text-xs text-gray-400">* Sujeto a disponibilidad o previa cita.</p>
    </div>
</section>

<script>
(function () {
    var RATES = @json($rates);      // { planId: [{over, upTo, fixed, percent}, …] }
    var input = document.getElementById('polizaRent');
    var money = function (n) { return '$' + Math.round(n).toLocaleString('es-MX'); };
    function priceFor(planId, rent) {
        var rows = RATES[planId] || [];
        for (var i = 0; i < rows.length; i++) {
            var r = rows[i];
            if (rent > r.over && (r.upTo === null || rent <= r.upTo)) {
                return r.percent !== null ? { amount: rent * r.percent / 100, percent: r.percent } : { amount: r.fixed };
            }
        }
        return null;
    }
    function render() {
        var rent = parseFloat((input.value || '').replace(/[^0-9.]/g, ''));
        document.querySelectorAll('#polizaResults [data-plan]').forEach(function (card) {
            var p = rent > 0 ? priceFor(card.dataset.plan, rent) : null;
            card.querySelector('.poliza-price').textContent = p ? money(p.amount) + ' MXN' : '—';
            card.querySelector('.poliza-basis').textContent = p ? (p.percent ? p.percent + '% de tu renta mensual' : 'Para una renta de ' + money(rent) + ' al mes') : 'Escribe tu renta para ver el precio.';
        });
    }
    input.addEventListener('input', function () {
        var raw = input.value.replace(/[^0-9]/g, '');
        input.value = raw ? parseInt(raw, 10).toLocaleString('es-MX') : '';
        render();
    });
})();
</script>

{{-- ¿Cuál me toca? --}}
<section class="py-16 sm:py-20 bg-white">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight mb-6">¿La póliza es para mí?</h2>
        <div class="space-y-4 text-gray-600 leading-relaxed">
            <p><strong class="text-gray-900">Si no tienes un aval con propiedad en la CDMX</strong>, tu garantía es una póliza jurídica: eliges uno de los tres planes y Previsión Legal se encarga de la investigación.</p>
            <p><strong class="text-gray-900">Si sí tienes aval con propiedad en la CDMX</strong>, el camino es la investigación de aval con Home del Valle (cuota de $3,500 MXN, no reembolsable). Revisa los <a href="{{ route('landing.rentar.requisitos') }}" class="font-semibold text-brand-600 hover:underline">requisitos para rentar</a>.</p>
        </div>

        <div class="mt-10 rounded-3xl gradient-brand px-8 py-10 text-center">
            <h3 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">¿Cuál plan te conviene?</h3>
            <p class="mt-2 text-brand-100">Cuéntanos de tu renta y te ayudamos a elegir.</p>
            <a href="{{ route('contacto') }}" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-semibold text-brand-700 shadow-lg hover:-translate-y-0.5 transition-all">Hablar con un asesor</a>
        </div>
    </div>
</section>

@endsection
