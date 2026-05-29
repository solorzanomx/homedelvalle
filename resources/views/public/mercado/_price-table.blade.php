{{--
    Partial reutilizable: tabla de precios por tipo × antigüedad
    Variables esperadas:
      $snaps       array ['apartment'=>['new'=>snap,'mid'=>snap,'old'=>snap], 'house'=>[...], 'office'=>[...]]
      $unitLabel   string  '/m²' o '/m²/mes'
      $accentColor string  '#1d4ed8' (venta) | '#7c3aed' (renta)
      $accentBg    string  '#eff6ff' | '#faf5ff'
      $showOffice  bool    true para mostrar local/oficina (renta comercial)
      $showMonthly bool    true para renta residencial — muestra monto mensual en vez de $/m²/mes
--}}
@php
    $unitLabel   ??= '/m²';
    $accentColor ??= '#1d4ed8';
    $accentBg    ??= '#eff6ff';
    $showOffice  ??= false;
    $showMonthly ??= false;   // true = renta residencial → muestra monto mensual

    $ageMap  = ['new' => 'Nuevo',     'mid' => 'Seminuevo', 'old' => 'Antiguo'];
    $ageDesc = ['new' => '0–5 años',  'mid' => '6–20 años', 'old' => '+20 años'];

    // m² típico por tipo de inmueble en BJ (para estimar renta mensual)
    $typicalM2 = ['apartment' => 75, 'house' => 120, 'office' => null];

    $types = ['apartment' => '🏢 Departamento', 'house' => '🏠 Casa'];
    if ($showOffice && !empty($snaps['office'])) {
        $types['office'] = '🏪 Local / Oficina';
    }

    // Columnas de edad que tienen datos en ALGÚN tipo
    $agesWithData = collect(['new','mid','old'])->filter(function($age) use ($snaps, $types) {
        foreach (array_keys($types) as $type) {
            if (!empty($snaps[$type][$age])) return true;
        }
        return false;
    })->values()->all();

    if (empty($agesWithData)) $agesWithData = ['new','mid','old'];

    /**
     * Calcula el rango a mostrar:
     * - Usa P25–P75 del snapshot
     * - Garantiza spread mínimo de 20% (±10% sobre el avg)
     * - Redondea: venta → mil más cercano; renta mensual → 500 más cercano
     */
    $displayRange = function($snap, bool $monthly = false, int $m2 = 75) use ($unitLabel): array {
        $avg  = (float) $snap->price_m2_avg;
        $low  = (float) $snap->price_m2_low;
        $high = (float) $snap->price_m2_high;

        // Asegurar spread mínimo del 20%
        if ($avg > 0 && ($high - $low) / $avg < 0.20) {
            $low  = $avg * 0.90;
            $high = $avg * 1.10;
        }

        if ($monthly) {
            // Renta mensual estimada = $/m²/mes × m² típico
            $low  = $low  * $m2;
            $high = $high * $m2;
            // Redondear al 500 más cercano
            $low  = round($low  / 500)  * 500;
            $high = round($high / 500)  * 500;
        } else {
            // Venta o renta comercial $/m²: redondear al 1,000 más cercano
            $low  = round($low  / 1000) * 1000;
            $high = round($high / 1000) * 1000;
        }

        return [(int) $low, (int) $high];
    };
@endphp

@php $hasAnyData = false; foreach(array_keys($types) as $t) { if(!empty($snaps[$t])) { $hasAnyData = true; break; } } @endphp
@if(!$hasAnyData)
<div style="text-align:center;color:#9ca3af;padding:2rem;background:#f8fafc;border-radius:10px;font-size:.85rem;">
    Sin datos de mercado disponibles para esta sección aún.
</div>
@else
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
            <thead>
                <tr style="background:#f8fafc;">
                    <th style="padding:.65rem 1.1rem;text-align:left;color:#6b7280;font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.3px;border-bottom:1px solid #e5e7eb;min-width:140px;">
                        Tipo
                    </th>
                    @foreach($agesWithData as $age)
                    <th style="padding:.65rem 1.1rem;text-align:center;font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.3px;border-bottom:1px solid #e5e7eb;min-width:160px;
                                {{ $age === 'mid' ? "color:{$accentColor};background:{$accentBg};" : 'color:#6b7280;' }}">
                        {{ $ageMap[$age] }}<br>
                        <span style="font-weight:400;text-transform:none;font-size:.65rem;opacity:.75;">{{ $ageDesc[$age] }}</span>
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($types as $typeKey => $typeLabel)
                @if(!empty($snaps[$typeKey]))
                @php
                    $isLastType  = $typeKey === array_key_last($types);
                    $isCommercial = $typeKey === 'office';
                    // Renta residencial → monto mensual; renta comercial → $/m²/mes; venta → $/m²
                    $useMonthly  = $showMonthly && !$isCommercial;
                    $m2typical   = $typicalM2[$typeKey] ?? 75;
                    $unitDisplay = $useMonthly
                        ? '/mes (estimado ~' . $m2typical . ' m²)'
                        : $unitLabel;
                @endphp
                <tr>
                    <td style="padding:.8rem 1.1rem;{{ $isLastType ? '' : 'border-bottom:1px solid #f3f4f6;' }}font-weight:600;color:#111827;vertical-align:middle;">
                        {{ $typeLabel }}
                        <div style="font-size:.68rem;font-weight:400;color:#9ca3af;margin-top:.15rem;">{{ $unitDisplay }}</div>
                    </td>
                    @foreach($agesWithData as $age)
                    @php
                        $snap  = $snaps[$typeKey][$age] ?? null;
                        $isMid = $age === 'mid';
                    @endphp
                    <td style="padding:.8rem 1.1rem;{{ $isLastType ? '' : 'border-bottom:1px solid #f3f4f6;' }}text-align:center;vertical-align:middle;{{ $isMid ? "background:{$accentBg};" : '' }}">
                        @if($snap)
                        @php [$rLow, $rHigh] = $displayRange($snap, $useMonthly, $m2typical); @endphp
                            {{-- Rango principal --}}
                            <div style="font-weight:700;font-size:{{ $isMid ? '1rem' : '.88rem' }};color:{{ $isMid ? $accentColor : '#111827' }};white-space:nowrap;">
                                ${{ number_format($rLow) }} – ${{ number_format($rHigh) }}
                            </div>
                            {{-- Promedio secundario (solo para venta y renta comercial) --}}
                            @if(!$useMonthly)
                            <div style="font-size:.67rem;color:#9ca3af;margin-top:.1rem;">
                                promedio ${{ number_format(round($snap->price_m2_avg / 1000) * 1000) }}
                            </div>
                            @endif
                            {{-- Badge confianza --}}
                            @if($snap->confidence === 'low')
                            <div style="font-size:.6rem;color:#9ca3af;margin-top:.15rem;font-style:italic;">estimado</div>
                            @elseif($snap->sample_size >= 5)
                            <div style="font-size:.6rem;color:#16a34a;margin-top:.15rem;">● {{ $snap->sample_size }} anuncios</div>
                            @endif
                        @else
                            <span style="color:#d1d5db;font-size:.8rem;">Sin datos</span>
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
    {{-- Footer --}}
    <div style="padding:.6rem 1.1rem;background:#fafafa;border-top:1px solid #f0f2f5;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.4rem;">
        <span style="font-size:.69rem;color:#9ca3af;">
            Rango de mercado · referencia aproximada, no avalúo formal
        </span>
        <span style="font-size:.69rem;color:#9ca3af;">
            Seminuevo resaltado por ser el segmento más representativo
        </span>
    </div>
</div>
@endif
