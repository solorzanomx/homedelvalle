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
    $unitLabel   = $unitLabel   ?? '/m²';
    $accentColor = $accentColor ?? '#1d4ed8';
    $accentBg    = $accentBg    ?? '#eff6ff';
    $showOffice  = $showOffice  ?? false;
    $showMonthly = $showMonthly ?? false;

    $ageMap  = ['new' => 'Nuevo',    'mid' => 'Seminuevo', 'old' => 'Antiguo'];
    $ageDesc = ['new' => '0–5 años', 'mid' => '6–20 años', 'old' => '+20 años'];

    // m² típico por tipo de inmueble en BJ
    $typicalM2 = ['apartment' => 75, 'house' => 120, 'office' => null];

    $types = ['apartment' => '🏢 Departamento', 'house' => '🏠 Casa'];
    if ($showOffice && !empty($snaps['office'])) {
        $types['office'] = '🏪 Local / Oficina';
    }

    // Columnas de edad con datos en algún tipo
    $agesWithData = [];
    foreach (['new','mid','old'] as $_age) {
        foreach (array_keys($types) as $_type) {
            if (!empty($snaps[$_type][$_age])) {
                $agesWithData[] = $_age;
                break;
            }
        }
    }
    if (empty($agesWithData)) {
        $agesWithData = ['new','mid','old'];
    }

    // ¿Hay algún dato?
    $hasAnyData = false;
    foreach (array_keys($types) as $_t) {
        if (!empty($snaps[$_t])) {
            $hasAnyData = true;
            break;
        }
    }
@endphp

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
                    <th style="padding:.65rem 1.1rem;text-align:center;font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.3px;border-bottom:1px solid #e5e7eb;min-width:160px;{{ $age === 'mid' ? 'color:'.$accentColor.';background:'.$accentBg.';' : 'color:#6b7280;' }}">
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
                    $isLastType   = ($typeKey === array_key_last($types));
                    $isCommercial = ($typeKey === 'office');
                    $useMonthly   = $showMonthly && !$isCommercial;
                    $m2typical    = $typicalM2[$typeKey] ?? 75;
                    $unitDisplay  = $useMonthly
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
                        $isMid = ($age === 'mid');
                        // Calcular rango con spread mínimo del 20%
                        if ($snap) {
                            $rAvg  = (float) $snap->price_m2_avg;
                            $rLow  = (float) $snap->price_m2_low;
                            $rHigh = (float) $snap->price_m2_high;
                            if ($rAvg > 0 && ($rHigh - $rLow) / $rAvg < 0.20) {
                                $rLow  = $rAvg * 0.90;
                                $rHigh = $rAvg * 1.10;
                            }
                            if ($useMonthly) {
                                $rLow  = round($rLow  * $m2typical / 500) * 500;
                                $rHigh = round($rHigh * $m2typical / 500) * 500;
                            } else {
                                $rLow  = round($rLow  / 1000) * 1000;
                                $rHigh = round($rHigh / 1000) * 1000;
                            }
                        }
                    @endphp
                    <td style="padding:.8rem 1.1rem;{{ $isLastType ? '' : 'border-bottom:1px solid #f3f4f6;' }}text-align:center;vertical-align:middle;{{ $isMid ? 'background:'.$accentBg.';' : '' }}">
                        @if($snap)
                            <div style="font-weight:700;font-size:{{ $isMid ? '1rem' : '.88rem' }};color:{{ $isMid ? $accentColor : '#111827' }};white-space:nowrap;">
                                ${{ number_format((int)$rLow) }} – ${{ number_format((int)$rHigh) }}
                            </div>
                            @if(!$useMonthly)
                            <div style="font-size:.67rem;color:#9ca3af;margin-top:.1rem;">
                                promedio ${{ number_format((int)round($snap->price_m2_avg / 1000) * 1000) }}
                            </div>
                            @endif
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
    <div style="padding:.6rem 1.1rem;background:#fafafa;border-top:1px solid #f0f2f5;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.4rem;">
        <span style="font-size:.69rem;color:#9ca3af;">Rango de mercado · referencia aproximada, no avalúo formal</span>
        <span style="font-size:.69rem;color:#9ca3af;">Seminuevo resaltado por ser el segmento más representativo</span>
    </div>
</div>
@endif
