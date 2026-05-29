{{--
    Partial reutilizable: tabla de precios por tipo × antigüedad
    Variables esperadas:
      $snaps       array ['apartment'=>['new'=>snap,'mid'=>snap,'old'=>snap], 'house'=>[...], 'office'=>[...]]
      $unitLabel   string  '/m²' o '/m²/mes'
      $accentColor string  '#1d4ed8' (venta) | '#7c3aed' (renta)
      $accentBg    string  '#eff6ff' | '#faf5ff'
      $showOffice  bool    true para mostrar local/oficina (renta comercial)
--}}
@php
    $unitLabel   ??= '/m²';
    $accentColor ??= '#1d4ed8';
    $accentBg    ??= '#eff6ff';
    $showOffice  ??= false;

    $ageMap = ['new' => 'Nuevo', 'mid' => 'Seminuevo', 'old' => 'Antiguo'];
    $ageDesc= ['new' => '0–5 años', 'mid' => '6–20 años', 'old' => '+20 años'];

    $types = ['apartment' => '🏢 Departamento', 'house' => '🏠 Casa'];
    if ($showOffice && !empty($snaps['office'])) {
        $types['office'] = '🏪 Local / Oficina';
    }

    // Determina qué columnas de edad tienen datos en CUALQUIER tipo
    $agesWithData = collect(['new','mid','old'])->filter(function($age) use ($snaps, $types) {
        foreach (array_keys($types) as $type) {
            if (!empty($snaps[$type][$age])) return true;
        }
        return false;
    })->values()->all();

    if (empty($agesWithData)) $agesWithData = ['new','mid','old'];
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
                    <th style="padding:.65rem 1.1rem;text-align:left;color:#6b7280;font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.3px;border-bottom:1px solid #e5e7eb;min-width:120px;">
                        Tipo
                    </th>
                    @foreach($agesWithData as $age)
                    <th style="padding:.65rem 1.1rem;text-align:center;font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.3px;border-bottom:1px solid #e5e7eb;min-width:130px;
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
                @php $isLastType = $typeKey === array_key_last($types); @endphp
                <tr>
                    <td style="padding:.8rem 1.1rem;{{ $isLastType ? '' : 'border-bottom:1px solid #f3f4f6;' }}font-weight:600;color:#111827;vertical-align:middle;">
                        {{ $typeLabel }}
                        <div style="font-size:.68rem;font-weight:400;color:#9ca3af;margin-top:.15rem;">{{ $unitLabel }}</div>
                    </td>
                    @foreach($agesWithData as $age)
                    @php $snap = $snaps[$typeKey][$age] ?? null; $isMid = $age === 'mid'; @endphp
                    <td style="padding:.8rem 1.1rem;{{ $isLastType ? '' : 'border-bottom:1px solid #f3f4f6;' }}text-align:center;vertical-align:middle;{{ $isMid ? "background:{$accentBg};" : '' }}">
                        @if($snap)
                            {{-- Rango como dato principal --}}
                            <div style="font-weight:700;font-size:{{ $isMid ? '1rem' : '.88rem' }};color:{{ $isMid ? $accentColor : '#111827' }};white-space:nowrap;">
                                ${{ number_format($snap->price_m2_low) }} – ${{ number_format($snap->price_m2_high) }}
                            </div>
                            {{-- Promedio secundario --}}
                            <div style="font-size:.68rem;color:#6b7280;margin-top:.1rem;">
                                promedio ${{ number_format($snap->price_m2_avg) }}{{ $unitLabel }}
                            </div>
                            {{-- Badge de confianza --}}
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
    {{-- Footer de la tabla --}}
    <div style="padding:.6rem 1.1rem;background:#fafafa;border-top:1px solid #f0f2f5;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.4rem;">
        <span style="font-size:.69rem;color:#9ca3af;">
            Precio promedio · rango mínimo–máximo de mercado
        </span>
        <span style="font-size:.69rem;color:#9ca3af;">
            Seminuevo resaltado por ser el segmento más representativo
        </span>
    </div>
</div>
@endif
