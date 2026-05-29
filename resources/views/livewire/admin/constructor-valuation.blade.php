{{--
  Valuación de Terreno para Constructor
  Perspectiva: director de adquisiciones de una casa constructora
  Métrica principal: Incidencia del Terreno (precio / m² vendibles)
--}}
<div>

@php
    // Precio derivado (solo display)
    $m2Float    = (float)($m2Terreno ?: 0);
    $totalFloat = (float)str_replace([',',' '], '', $precioTerreno ?: '0');
    $perM2Float = (float)str_replace([',',' '], '', $precioTerrenoM2 ?: '0');
    $derivedM2  = ($precioMode==='total'  && $m2Float>0 && $totalFloat>0) ? (int)round($totalFloat/$m2Float) : null;
    $derivedTot = ($precioMode==='per_m2' && $m2Float>0 && $perM2Float>0) ? (int)round($perM2Float*$m2Float) : null;

    // Paleta del veredicto
    $vc = match($result['verdict'] ?? '') {
        'compra_directa' => ['bg'=>'#052e16','border'=>'#16a34a','text'=>'#bbf7d0','icon'=>'✅','label'=>'COMPRA DIRECTA','sub'=>'ROI > 22% — el constructor comprará sin negociar mucho'],
        'viable'         => ['bg'=>'#f0fdf4','border'=>'#86efac','text'=>'#166534','icon'=>'✅','label'=>'VIABLE','sub'=>'ROI 17-22% — buen negocio, hay interés real de una constructora'],
        'negocia'        => ['bg'=>'#fefce8','border'=>'#fde047','text'=>'#713f12','icon'=>'⚠️','label'=>'NEGOCIA EL PRECIO','sub'=>'ROI 8-17% — el precio actual deja poco margen, hay que bajar'],
        'descarta'       => ['bg'=>'#fef2f2','border'=>'#fca5a5','text'=>'#991b1b','icon'=>'🚫','label'=>'DESCARTA O ASOCIACIÓN','sub'=>'ROI < 8% — inviable a este precio; evalúa el esquema de asociación'],
        default          => [],
    };

    // Color semáforo de incidencia %
    $incColor = fn($pct) => $pct <= 12 ? '#059669' : ($pct <= 18 ? '#d97706' : '#dc2626');
    $roiColor = fn($roi)  => $roi >= 20 ? '#059669' : ($roi >= 15 ? '#d97706' : '#dc2626');
@endphp

<style>
.cv-label{display:block;font-size:.8rem;font-weight:600;margin-bottom:.35rem;color:var(--text)}
.cv-hint{font-size:.68rem;color:#94a3b8;margin-top:.2rem;display:block}
.cv-input{width:100%;padding:.55rem .8rem;border:1px solid var(--border);border-radius:var(--radius);
          font-family:inherit;font-size:.88rem;background:var(--card);color:var(--text)}
.cv-input:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12)}
.cv-ro{background:#f8fafc!important;color:#64748b;cursor:default}
.cv-sec{font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#64748b;margin-bottom:.6rem;display:flex;align-items:center;gap:.4rem}
.cv-n{width:20px;height:20px;border-radius:50%;background:#f1f5f9;display:inline-flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:800;color:#64748b;flex-shrink:0}
.cv-preset{font-size:.68rem;font-weight:600;padding:.2rem .5rem;border-radius:5px;cursor:pointer;border:1px solid var(--border);background:var(--card);color:#64748b;transition:all .12s;white-space:nowrap}
.cv-preset:hover{background:#eff6ff;border-color:#6366f1;color:#6366f1}
.cv-preset.on{background:#6366f1;border-color:#6366f1;color:#fff}
.wf-row{display:flex;align-items:center;justify-content:space-between;padding:.3rem 0;border-bottom:1px solid #f1f5f9;font-size:.74rem}
.wf-row:last-child{border-bottom:none}
.indicator-row{display:flex;align-items:center;justify-content:space-between;padding:.38rem .65rem;background:#f8fafc;border-radius:6px;margin-bottom:.3rem}
</style>

<div style="display:flex;gap:1.25rem;align-items:flex-start;">

{{-- ════════════════════════════════════════
     Columna izquierda — Formulario
     ════════════════════════════════════════ --}}
<div style="flex:1;min-width:0;display:flex;flex-direction:column;gap:.9rem;">

{{-- 1. Terreno ──────────────────────────────────────────────────────── --}}
<div class="card">
<div class="card-body" style="padding:1rem 1.25rem;">
<div class="cv-sec"><span class="cv-n">1</span> Datos del terreno</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:.8rem;">

    <div style="grid-column:1/-1;">
        <label class="cv-label">Colonia <span style="font-weight:400;color:#94a3b8;">· para tomar precio de mercado automáticamente</span></label>
        <select wire:model.live="coloniaId" class="cv-input">
            <option value="">— Sin colonia / ingreso manual de precio —</option>
            @foreach($colonias as $zoneName => $cols)
            <optgroup label="{{ $zoneName }}">
                @foreach($cols as $col)
                <option value="{{ $col->id }}">{{ $col->name }}</option>
                @endforeach
            </optgroup>
            @endforeach
        </select>
    </div>

    <div>
        <label class="cv-label">m² de terreno <span style="color:var(--danger)">*</span></label>
        <input wire:model.blur="m2Terreno" wire:change="recalculate"
               type="number" min="10" step="1" placeholder="200" class="cv-input">
        @if($frente && $fondo)
        <span class="cv-hint" style="color:#059669;">= {{ number_format((float)$frente*(float)$fondo,0) }} m² según frente × fondo</span>
        @endif
        @if($m2Terreno && (float)$m2Terreno < 200)
        <span class="cv-hint" style="color:#d97706;">⚠ < 200 m² · revisar Norma 10 CDMX</span>
        @endif
    </div>

    <div>
        <label class="cv-label">Frente × Fondo (m) <span style="font-weight:400;color:#94a3b8;">opcional</span></label>
        <div style="display:flex;gap:.4rem;align-items:center;">
            <input wire:model="frente" type="number" min="0" step="0.5" placeholder="10" class="cv-input" style="flex:1;">
            <span style="color:#94a3b8;font-weight:700;">×</span>
            <input wire:model="fondo"  type="number" min="0" step="0.5" placeholder="20" class="cv-input" style="flex:1;">
        </div>
    </div>

</div>
</div>
</div>

{{-- 2. Zonificación ────────────────────────────────────────────────── --}}
<div class="card">
<div class="card-body" style="padding:1rem 1.25rem;">
<div class="cv-sec"><span class="cv-n">2</span> Zonificación urbana · PDDU CDMX</div>

    <div style="margin-bottom:.75rem;">
        <label class="cv-label">
            Clave SEDUVI <span style="font-weight:400;color:#94a3b8;font-size:.72rem;">· auto-calcula COS, CUS y niveles</span>
        </label>
        <input wire:model.live.debounce.600ms="zonificacionLabel" type="text"
               placeholder="ej. HM 6/30  ·  H4/Z/20  ·  HC4/30  ·  CB5/30"
               class="cv-input"
               style="font-family:monospace;font-size:.95rem;font-weight:700;letter-spacing:.5px;
                      {{ $parsedZone ? 'border-color:#059669;' : '' }}">

        @if($parsedZone)
        <div style="margin-top:.3rem;background:#f0fdf4;border:1px solid #86efac;border-radius:6px;padding:.4rem .75rem;font-size:.72rem;color:#166534;">
            <div style="display:flex;gap:.6rem;flex-wrap:wrap;margin-bottom:{{ ($parsedZone['area_libre']??null) ? '.15rem' : '0' }};">
                <strong>{{ $parsedZone['uso'] }}</strong>
                <span>{{ $parsedZone['pisos'] }} pisos</span>
                <span>COS <strong>{{ $parsedZone['cos'] }}</strong></span>
                <span>CUS <strong>{{ $parsedZone['cus'] }}</strong></span>
                @if($parsedZone['zona_var']??null)<span>Zona {{ $parsedZone['zona_var'] }}</span>@endif
                @if($parsedZone['lote_min']??null)<span>Lote mín. {{ $parsedZone['lote_min'] }} m²</span>@endif
            </div>
            @if($parsedZone['area_libre']??null)
            <div style="font-size:.65rem;opacity:.8;">/{{ $parsedZone['zona_var'] }}/{{ $parsedZone['area_libre'] }} = {{ $parsedZone['area_libre'] }}% área libre → COS {{ $parsedZone['cos'] }}</div>
            @endif
        </div>
        @elseif($zonificacionLabel)
        <span class="cv-hint" style="color:#d97706;">⚠ Código no reconocido — ajusta COS/CUS/pisos manualmente</span>
        @else
        <span class="cv-hint">Escribe la clave o selecciona un preset:</span>
        @endif
    </div>

    {{-- Presets -------------------------------------------------------}}
    <div style="display:flex;flex-wrap:wrap;gap:.3rem;margin-bottom:.9rem;">
        @foreach([
            'H3_30'=>'H 3/30','H4_30'=>'H 4/30','HM4_30'=>'HM 4/30',
            'HM5_30'=>'HM 5/30','HM6_30'=>'HM 6/30','HM8_30'=>'HM 8/30',
            'HC4_30'=>'HC 4/30','CB5_30'=>'CB 5/30','N10'=>'Norma 10',
        ] as $key => $short)
        @php $presetObj = $presets[$key] ?? null; $act = $zonificacionLabel===$short; @endphp
        <button wire:click="applyPreset('{{ $key }}')" class="cv-preset {{ $act?'on':'' }}"
                title="{{ $presetObj['label']??'' }} · COS {{ $presetObj['cos']??'' }} · CUS {{ $presetObj['cus']??'' }}">
            {{ $short }}
        </button>
        @endforeach
    </div>

    {{-- COS / CUS / Pisos --------------------------------------------}}
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;">
        <div>
            <label class="cv-label">COS <span style="font-size:.66rem;color:#6366f1;cursor:help;" title="% del terreno que puede ocupar la huella de construcción">ⓘ</span></label>
            <input wire:model.live.debounce.400ms="cos" type="number" min="0.01" max="1" step="0.01" placeholder="0.60" class="cv-input">
            @if($cos && $m2Terreno)
            <span class="cv-hint" style="color:#059669;">= {{ number_format((float)$cos*(float)$m2Terreno,0) }} m² huella</span>
            @endif
        </div>
        <div>
            <label class="cv-label">CUS <span style="font-size:.66rem;color:#6366f1;cursor:help;" title="m² brutos totales construibles / m² de terreno">ⓘ</span></label>
            <input wire:model.live.debounce.400ms="cus" type="number" min="0.1" max="30" step="0.1" placeholder="3.60" class="cv-input">
            @if($cus && $m2Terreno)
            <span class="cv-hint" style="color:#059669;">= {{ number_format((float)$cus*(float)$m2Terreno,0) }} m² brutos</span>
            @endif
        </div>
        <div>
            <label class="cv-label">Niveles</label>
            <input wire:model.live.debounce.400ms="pisos" type="number" min="1" max="40" step="1" placeholder="6" class="cv-input">
            @if($cos && $cus && (float)$cos > 0)
            <span class="cv-hint">{{ round((float)$cus/(float)$cos,1) }} según CUS/COS</span>
            @endif
        </div>
    </div>

</div>
</div>

{{-- 3. Precio del terreno ────────────────────────────────────────── --}}
<div class="card">
<div class="card-body" style="padding:1rem 1.25rem;">
<div class="cv-sec"><span class="cv-n">3</span> Precio del terreno</div>

    <div style="display:inline-flex;border:1px solid var(--border);border-radius:8px;overflow:hidden;margin-bottom:.85rem;">
        <button wire:click="$set('precioMode','total')"
                style="padding:.32rem .85rem;font-size:.78rem;font-weight:600;border:none;cursor:pointer;
                       background:{{ $precioMode==='total'  ? '#6366f1':'transparent' }};
                       color:{{      $precioMode==='total'  ? '#fff':'#64748b' }};">
            Precio total
        </button>
        <button wire:click="$set('precioMode','per_m2')"
                style="padding:.32rem .85rem;font-size:.78rem;font-weight:600;border:none;cursor:pointer;
                       background:{{ $precioMode==='per_m2' ? '#6366f1':'transparent' }};
                       color:{{      $precioMode==='per_m2' ? '#fff':'#64748b' }};">
            Por m²
        </button>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.8rem;">
        @if($precioMode==='total')
        <div>
            <label class="cv-label">Precio total (MXN) <span style="color:var(--danger)">*</span></label>
            <input wire:model.blur="precioTerreno" wire:change="recalculate"
                   type="text" inputmode="numeric" placeholder="4,500,000" class="cv-input">
            @if($derivedM2)<span class="cv-hint" style="color:#059669;">= ${{ number_format($derivedM2) }}/m²</span>
            @else<span class="cv-hint">ej. 4500000</span>@endif
        </div>
        <div>
            <label class="cv-label">$/m² terreno</label>
            <div class="cv-input cv-ro">{{ $derivedM2 ? '$'.number_format($derivedM2).'/m²' : '—' }}</div>
        </div>
        @else
        <div>
            <label class="cv-label">$/m² terreno <span style="color:var(--danger)">*</span></label>
            <input wire:model.blur="precioTerrenoM2" wire:change="recalculate"
                   type="text" inputmode="numeric" placeholder="22,500" class="cv-input">
            @if($derivedTot)<span class="cv-hint" style="color:#059669;">= ${{ number_format($derivedTot) }} total</span>
            @else<span class="cv-hint">ej. 22500</span>@endif
        </div>
        <div>
            <label class="cv-label">Total calculado</label>
            <div class="cv-input cv-ro">{{ $derivedTot ? '$'.number_format($derivedTot) : '—' }}</div>
        </div>
        @endif
    </div>

    {{-- Separador ──────────────────────────────────────────────────── --}}
    <div style="border-top:1px solid #f1f5f9;margin:.9rem 0;"></div>

    {{-- Precio de venta de nuevos desarrollos ──────────────────────── --}}
    <div>
        <label class="cv-label" style="color:#0f172a;">
            Precio de venta de departamentos nuevos ($/m²)
            <span style="font-weight:400;color:#94a3b8;font-size:.72rem;">— el constructor vende aquí</span>
        </label>

        @if($observatorioPrice)
        {{-- Referencia del Observatorio con selector de premium --}}
        <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:.6rem .85rem;margin-bottom:.6rem;">
            <div style="font-size:.7rem;font-weight:600;color:#1e40af;margin-bottom:.45rem;">
                📊 Observatorio HDV (promedio anuncios nuevos 0-5 años en la zona):
                <strong style="font-size:.85rem;">${{ number_format((int)$observatorioPrice) }}/m²</strong>
            </div>
            <div style="font-size:.68rem;color:#3b82f6;margin-bottom:.5rem;">
                Los nuevos desarrollos tipo constructora suelen cotizarse <strong>15-30% sobre el promedio</strong>
                del mercado, porque ofrecen 100% nuevo con amenidades.
                Ajusta con el premium real de la zona:
            </div>
            {{-- Botones de premium rápido --}}
            <div style="display:flex;flex-wrap:wrap;gap:.3rem;">
                @foreach([
                    '1.00' => 'Base ('  . number_format((int)$observatorioPrice)       . ')',
                    '1.10' => '+10% (' . number_format((int)round($observatorioPrice*1.10)) . ')',
                    '1.15' => '+15% (' . number_format((int)round($observatorioPrice*1.15)) . ')',
                    '1.20' => '+20% (' . number_format((int)round($observatorioPrice*1.20)) . ')',
                    '1.25' => '+25% (' . number_format((int)round($observatorioPrice*1.25)) . ')',
                    '1.30' => '+30% (' . number_format((int)round($observatorioPrice*1.30)) . ')',
                ] as $mult => $label)
                <button wire:click="applyPremium('{{ $mult }}')"
                        style="font-size:.68rem;font-weight:600;padding:.22rem .55rem;border-radius:5px;cursor:pointer;border:1px solid;
                               background:{{ $precioMultiplier===$mult ? '#6366f1' : '#fff' }};
                               border-color:{{ $precioMultiplier===$mult ? '#6366f1' : '#bfdbfe' }};
                               color:{{ $precioMultiplier===$mult ? '#fff' : '#1e40af' }};">
                    {{ $label }}
                </button>
                @endforeach
            </div>
        </div>
        @else
        <div style="background:#fefce8;border:1px solid #fde047;border-radius:6px;padding:.45rem .75rem;margin-bottom:.5rem;font-size:.7rem;color:#713f12;">
            ⚠ Sin datos del Observatorio para esta zona — ingresa el precio manualmente
        </div>
        @endif

        {{-- Input manual (siempre visible) --}}
        <div style="display:flex;align-items:center;gap:.5rem;">
            <input wire:model.blur="precioVentaM2" wire:change="recalculate"
                   type="text" inputmode="numeric"
                   placeholder="{{ $coloniaId ? 'Auto del Observatorio' : 'ej. 70000' }}"
                   class="cv-input" style="flex:1;">
            @if($precioVentaM2 && $observatorioPrice)
            @php $pvNum = (int) str_replace([',',' '], '', $precioVentaM2); @endphp
            <div style="font-size:.7rem;color:{{ $pvNum > $observatorioPrice ? '#059669' : '#d97706' }};white-space:nowrap;font-weight:600;">
                {{ $pvNum > $observatorioPrice ? '+' : '' }}{{ round(($pvNum - $observatorioPrice) / $observatorioPrice * 100) }}%
                vs obs.
            </div>
            @endif
        </div>
        <span class="cv-hint">Precio al que el constructor venderá los departamentos nuevos que construya</span>
    </div>

</div>
</div>

{{-- 4. Parámetros de construcción (colapsable) ────────────────────── --}}
<div class="card" x-data="{open:false}">
<div class="card-header" style="cursor:pointer;border-bottom:1px solid var(--border);" @click="open=!open">
    <h3 style="font-size:.82rem;font-weight:600;color:var(--text);display:flex;align-items:center;width:100%;gap:.4rem;">
        <span class="cv-n">4</span>
        Parámetros de construcción
        <span style="font-size:.7rem;font-weight:400;color:#94a3b8;">
            · ${{ number_format((int)$costoConstruccion) }}/m² · {{ $eficiencia }}% vendible · {{ $tamanoDepto }} m²/depto
        </span>
        <span x-text="open?'▲':'▼'" style="margin-left:auto;font-size:.65rem;color:#94a3b8;"></span>
    </h3>
</div>
<div x-show="open" x-transition style="display:none;">
<div class="card-body" style="padding:1rem 1.25rem;">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:.8rem;">

    <div>
        <label class="cv-label">Costo construcción ($/m² bruto)</label>
        <input wire:model.blur="costoConstruccion" wire:change="recalculate"
               type="number" min="10000" max="80000" step="500" placeholder="18000" class="cv-input">
        <span class="cv-hint">CEICO-CMIC media CDMX 2025: ~$18k/m² (semilujо: $22k, lujo: $28k+)</span>
    </div>

    <div>
        <label class="cv-label">Factor vendible ({{ $eficiencia }}%)</label>
        <input wire:model.live="eficiencia" type="range" min="60" max="90" step="1"
               style="width:100%;accent-color:#6366f1;margin-top:.45rem;">
        <span class="cv-hint">m² vendibles / m² brutos — ref: 78-85%</span>
    </div>

    <div>
        <label class="cv-label">Tamaño promedio depto (m²)</label>
        <input wire:model.live.debounce.400ms="tamanoDepto" type="number"
               min="30" max="250" step="5" placeholder="65" class="cv-input">
    </div>

</div>

{{-- Estructura de costos aplicada --}}
<div style="margin-top:.85rem;background:#f8fafc;border-radius:8px;padding:.7rem 1rem;font-size:.7rem;color:#64748b;">
    <div style="font-weight:600;color:#0f172a;margin-bottom:.4rem;">Estructura de costos aplicada (ref. CDMX 2025):</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.15rem .5rem;">
        <span>Construcción directa</span><span>${{ number_format((int)$costoConstruccion) }}/m² bruto</span>
        <span>Proyecto + supervisión</span><span>10% de construcción</span>
        <span>Permisos CDMX</span><span>5% de construcción</span>
        <span>Comercialización</span><span>4% de ventas</span>
        <span>Financiamiento obra</span><span>10% de construcción</span>
        <span>Financiamiento terreno</span><span>20% del precio terreno</span>
        <span>Utilidad objetivo</span><span>22% de ventas</span>
    </div>
</div>
</div>
</div>
</div>

</div>
{{-- /columna izquierda --}}


{{-- ════════════════════════════════════════
     Columna derecha — Panel de análisis
     ════════════════════════════════════════ --}}
<div style="width:360px;flex-shrink:0;">

@if($result && $result['available'])
@php $r = $result; @endphp

{{-- ╔══════════════════════════════════╗
     ║  VEREDICTO                       ║
     ╚══════════════════════════════════╝ --}}
<div style="background:{{ $vc['bg'] }};border:1.5px solid {{ $vc['border'] }};border-radius:12px;
            padding:.85rem 1.1rem;margin-bottom:.85rem;">
    <div style="display:flex;align-items:center;gap:.7rem;">
        <span style="font-size:1.4rem;flex-shrink:0;">{{ $vc['icon'] }}</span>
        <div style="flex:1;">
            <div style="font-size:.75rem;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:{{ $vc['text'] }};">
                {{ $vc['label'] }}
            </div>
            <div style="font-size:.7rem;color:{{ $vc['text'] }};opacity:.8;margin-top:.1rem;">
                {{ $vc['sub'] }}
            </div>
        </div>
        <div wire:loading style="flex-shrink:0;">
            <span style="width:12px;height:12px;border:2px solid {{ $vc['border'] }};border-top-color:{{ $vc['text'] }};
                         border-radius:50%;display:inline-block;animation:spin 1s linear infinite;"></span>
        </div>
    </div>

    {{-- Precio de oferta sugerido --}}
    @if($r['precio_oferta'] > 0)
    <div style="margin-top:.75rem;padding-top:.7rem;border-top:1px solid {{ $vc['border'] }};">
        @if($r['brecha_pct'] <= 0)
        {{-- Terreno bien precio --}}
        <div style="font-size:.68rem;color:{{ $vc['text'] }};font-weight:600;">
            ✓ Precio pedido dentro del rango viable
        </div>
        <div style="font-size:.68rem;color:{{ $vc['text'] }};opacity:.8;margin-top:.1rem;">
            Puedes ofrecer <strong>${{ number_format($r['precio_oferta']) }}</strong>
            (${{ number_format($r['precio_oferta_m2']) }}/m²) con margen de contingencias
        </div>
        @else
        {{-- Terreno caro --}}
        <div style="font-size:.68rem;color:{{ $vc['text'] }};font-weight:600;">
            Precio de oferta sugerido
        </div>
        <div style="font-size:1.1rem;font-weight:900;color:{{ $vc['text'] }};">
            ${{ number_format($r['precio_oferta']) }}
            <span style="font-size:.7rem;font-weight:500;opacity:.8;">(${{ number_format($r['precio_oferta_m2']) }}/m²)</span>
        </div>
        <div style="font-size:.68rem;color:{{ $vc['text'] }};opacity:.8;margin-top:.15rem;">
            El propietario pide <strong>${{ number_format($r['brecha_oferta']) }}</strong> más de lo viable
            ({{ $r['brecha_pct'] }}% sobre el precio de oferta)
        </div>
        @endif
    </div>
    @endif
</div>

{{-- ╔══════════════════════════════════╗
     ║  ★ INCIDENCIA DEL TERRENO       ║
     ╚══════════════════════════════════╝ --}}
<div class="card" style="margin-bottom:.85rem;border-left:4px solid {{ $incColor($r['incidencia_pct']) }};">
<div class="card-body" style="padding:.85rem 1.1rem;">
    <div class="cv-sec" style="margin-bottom:.5rem;">
        ★ Incidencia del Terreno
        <span style="font-size:.62rem;font-weight:400;text-transform:none;color:#94a3b8;">— la métrica clave del developer</span>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-bottom:.65rem;">
        <div style="text-align:center;background:#f8fafc;border-radius:8px;padding:.6rem;">
            <div style="font-size:.57rem;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;">$/m² vendible</div>
            <div style="font-size:1.4rem;font-weight:900;color:{{ $incColor($r['incidencia_pct']) }};line-height:1.1;">
                ${{ number_format($r['incidencia_m2']) }}
            </div>
        </div>
        <div style="text-align:center;background:#f8fafc;border-radius:8px;padding:.6rem;">
            <div style="font-size:.57rem;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;">% del precio venta</div>
            <div style="font-size:1.4rem;font-weight:900;color:{{ $incColor($r['incidencia_pct']) }};line-height:1.1;">
                {{ $r['incidencia_pct'] }}%
            </div>
        </div>
    </div>

    {{-- Barra de incidencia --}}
    <div style="height:8px;background:#e2e8f0;border-radius:4px;margin-bottom:.4rem;position:relative;">
        <div style="height:100%;border-radius:4px;background:{{ $incColor($r['incidencia_pct']) }};
                    width:{{ min(100, $r['incidencia_pct'] / 30 * 100) }}%;transition:width .3s;"></div>
        {{-- Markers --}}
        <div style="position:absolute;top:10px;left:40%;font-size:.55rem;color:#059669;">12%</div>
        <div style="position:absolute;top:10px;left:60%;font-size:.55rem;color:#d97706;">18%</div>
        <div style="position:absolute;top:10px;left:83.3%;font-size:.55rem;color:#dc2626;">25%</div>
    </div>
    <div style="display:flex;justify-content:space-between;font-size:.6rem;color:#94a3b8;margin-bottom:.5rem;">
        <span>Excelente<br><span style="color:#059669;">< 12%</span></span>
        <span style="text-align:center;">Aceptable<br><span style="color:#d97706;">12-18%</span></span>
        <span style="text-align:right;">Caro · Inviable<br><span style="color:#dc2626;">18-25% · > 25%</span></span>
    </div>

    <div style="font-size:.68rem;color:#64748b;background:#f1f5f9;border-radius:6px;padding:.35rem .6rem;">
        Incidencia = Precio terreno ÷ m² vendibles<br>
        Indica cuánto paga el constructor por cada m² que puede vender.
        @if($r['incidencia_pct'] <= 12)
        <strong style="color:#059669;">En este caso el terreno representa sólo el {{ $r['incidencia_pct'] }}% del precio de venta — muy competitivo.</strong>
        @elseif($r['incidencia_pct'] <= 18)
        <strong style="color:#d97706;">{{ $r['incidencia_pct'] }}% del precio de venta va a pagar el terreno — está en el límite aceptable.</strong>
        @else
        <strong style="color:#dc2626;">{{ $r['incidencia_pct'] }}% del precio de venta va sólo a pagar el terreno — deja poco margen para construir con ganancia.</strong>
        @endif
    </div>
</div>
</div>

{{-- ╔══════════════════════════════════╗
     ║  POTENCIAL CONSTRUCTIVO          ║
     ╚══════════════════════════════════╝ --}}
<div class="card" style="margin-bottom:.85rem;border-top:3px solid #6366f1;">
<div class="card-body" style="padding:.85rem 1.1rem;">
    <div class="cv-sec" style="margin-bottom:.55rem;">📐 Potencial constructivo</div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.4rem;margin-bottom:.6rem;">
        @foreach([
            ['m² vendibles', number_format($r['m2_vendibles'],0), '#6366f1'],
            ['Deptos ~', $r['deptos_estimados'], '#0f172a'],
            ['m² brutos', number_format($r['m2_brutos'],0), '#64748b'],
            ['Pisos', $pisos, '#64748b'],
        ] as [$lbl, $val, $clr])
        <div style="text-align:center;background:#f8fafc;border-radius:6px;padding:.45rem .3rem;">
            <div style="font-size:.57rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.4px;margin-bottom:.1rem;">{{ $lbl }}</div>
            <div style="font-size:.95rem;font-weight:800;color:{{ $clr }};">{{ $val }}</div>
        </div>
        @endforeach
    </div>
    <div style="font-size:.68rem;color:#64748b;background:#f1f5f9;border-radius:6px;padding:.4rem .6rem;">
        Clave <strong style="font-family:monospace;color:#6366f1;">{{ $zonificacionLabel ?: '—' }}</strong>
        · COS {{ $cos }} · CUS {{ $cus }} · Eficiencia {{ $eficiencia }}%
        · {{ $tamanoDepto }} m²/depto
    </div>
</div>
</div>

{{-- ╔══════════════════════════════════╗
     ║  WATERFALL FINANCIERO            ║
     ╚══════════════════════════════════╝ --}}
<div class="card" style="margin-bottom:.85rem;">
<div class="card-body" style="padding:.85rem 1.1rem;">
    <div class="cv-sec" style="margin-bottom:.45rem;">
        💰 Análisis financiero
        <span style="font-size:.62rem;font-weight:400;text-transform:none;color:#94a3b8;">
            · ${{ number_format($r['precio_venta_m2']) }}/m²
            @if($r['precio_venta_fuente']==='observatorio')(Observatorio HDV)@else(manual)@endif
        </span>
    </div>

    <div class="wf-row" style="color:#0f172a;font-weight:700;font-size:.78rem;border-bottom:2px solid #e2e8f0!important;">
        <span>Valor de venta total</span>
        <span>${{ number_format($r['ventas']) }}</span>
    </div>
    <div class="wf-row" style="color:#ef4444;">
        <span>− Construcción directa</span>
        <span>−${{ number_format($r['construccion_direct']) }}</span>
    </div>
    <div class="wf-row" style="color:#f97316;">
        <span>− Proyecto + supervisión (10%)</span>
        <span>−${{ number_format($r['indirectos_tecnicos']) }}</span>
    </div>
    <div class="wf-row" style="color:#f97316;">
        <span>− Permisos CDMX (5%)</span>
        <span>−${{ number_format($r['permisos_licencias']) }}</span>
    </div>
    <div class="wf-row" style="color:#f97316;">
        <span>− Comercialización (4% ventas)</span>
        <span>−${{ number_format($r['comercializacion']) }}</span>
    </div>
    <div class="wf-row" style="color:#f97316;">
        <span>− Financiamiento obra (10%)</span>
        <span>−${{ number_format($r['financiero_obra']) }}</span>
    </div>
    <div class="wf-row" style="color:#dc2626;font-weight:600;">
        <span>− Terreno pedido + intereses (20%)</span>
        <span>−${{ number_format((int)str_replace([',',' '],'',$precioTerreno) + $r['financiero_terreno']) }}</span>
    </div>
    <div class="wf-row" style="border-top:2px solid #e2e8f0!important;padding-top:.45rem;margin-top:.15rem;font-size:.8rem;font-weight:800;color:{{ $r['utilidad_neta']>=0?'#059669':'#dc2626' }};">
        <span>= Utilidad neta</span>
        <span>{{ $r['utilidad_neta']>=0?'':'−' }}${{ number_format(abs($r['utilidad_neta'])) }}</span>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.4rem;margin-top:.6rem;">
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:.5rem;text-align:center;">
            <div style="font-size:.57rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;">ROI total</div>
            <div style="font-size:1.05rem;font-weight:900;color:{{ $roiColor($r['roi']) }};">{{ $r['roi'] }}%</div>
            <div style="font-size:.57rem;color:#94a3b8;">objetivo ≥ 18%</div>
        </div>
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:.5rem;text-align:center;">
            <div style="font-size:.57rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;">Margen / venta</div>
            <div style="font-size:1.05rem;font-weight:900;
                color:{{ $r['margen_ventas']>=15?'#059669':($r['margen_ventas']>=10?'#d97706':'#dc2626') }};">
                {{ $r['margen_ventas'] }}%
            </div>
        </div>
    </div>

    {{-- Valor residual --}}
    <div style="margin-top:.65rem;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:.55rem .75rem;">
        <div style="font-size:.65rem;font-weight:700;color:#1e40af;margin-bottom:.2rem;">
            Método Residual · Precio máximo viable (ROI 22%)
        </div>
        <div style="display:flex;align-items:baseline;gap:.4rem;">
            <span style="font-size:1.05rem;font-weight:900;color:#1d4ed8;">
                ${{ number_format($r['valor_residual']) }}
            </span>
            <span style="font-size:.7rem;color:#3b82f6;">(${{ number_format($r['valor_residual_m2']) }}/m²)</span>
        </div>
        <div style="font-size:.65rem;color:#1e40af;margin-top:.1rem;">
            Precio de oferta sugerido: <strong>${{ number_format($r['precio_oferta']) }}</strong>
            <span style="opacity:.8;">(12% margen contingencias)</span>
        </div>
    </div>
</div>
</div>

{{-- ╔══════════════════════════════════╗
     ║  INDICADORES SECUNDARIOS         ║
     ╚══════════════════════════════════╝ --}}
<div class="card" style="margin-bottom:.85rem;">
<div class="card-body" style="padding:.85rem 1.1rem;">
    <div class="cv-sec" style="margin-bottom:.55rem;">📊 Indicadores secundarios</div>
    @foreach([
        ['ROI del constructor', $r['roi'].'%', $roiColor($r['roi']), '> 18% viable · 15-18% borderline'],
        ['Ratio terreno / ventas', $r['ratio_tierra_venta'].'%', $r['ratio_tierra_venta']<=20?'#059669':($r['ratio_tierra_venta']<=30?'#d97706':'#dc2626'), '< 20% óptimo · 20-30% aceptable'],
        ['Precio terreno/m²', '$'.number_format($r['precio_terreno_m2']), '#0f172a', 'referencia comparativa'],
    ] as [$lbl, $val, $clr, $hint])
    <div class="indicator-row">
        <div><div style="font-size:.7rem;font-weight:600;color:#0f172a;">{{ $lbl }}</div><div style="font-size:.6rem;color:#94a3b8;">{{ $hint }}</div></div>
        <div style="font-size:.95rem;font-weight:800;color:{{ $clr }};">{{ $val }}</div>
    </div>
    @endforeach
</div>
</div>

{{-- ╔══════════════════════════════════╗
     ║  ESQUEMA DE ASOCIACIÓN           ║
     ╚══════════════════════════════════╝ --}}
@if($r['asociacion'])
@php $a = $r['asociacion']; @endphp
<div class="card" style="margin-bottom:.85rem;border-left:4px solid #7c3aed;">
<div class="card-body" style="padding:.85rem 1.1rem;">
    <div class="cv-sec" style="margin-bottom:.4rem;color:#7c3aed;">
        🤝 Alternativa: Esquema de Asociación
    </div>
    <div style="font-size:.7rem;color:#64748b;margin-bottom:.65rem;line-height:1.5;">
        El propietario <strong>no vende el terreno</strong> — lo aporta al fideicomiso de desarrollo.
        El constructor aporta toda la obra. Se reparten utilidades según capital aportado.
        Esquema popular en CDMX ante terrenos con precios altos.
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.4rem;margin-bottom:.5rem;">
        <div style="background:#f5f3ff;border:1px solid #ddd6fe;border-radius:8px;padding:.5rem .65rem;text-align:center;">
            <div style="font-size:.58rem;color:#7c3aed;text-transform:uppercase;letter-spacing:.5px;">Dueño del terreno</div>
            <div style="font-size:1.2rem;font-weight:900;color:#6d28d9;">{{ $a['split_dono'] }}%</div>
            <div style="font-size:.65rem;color:#7c3aed;font-weight:600;">~${{ number_format($a['parte_dono']) }}</div>
            <div style="font-size:.58rem;color:#8b5cf6;">≈ ${{ number_format($a['equivalente_m2']) }}/m² terreno</div>
        </div>
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:.5rem .65rem;text-align:center;">
            <div style="font-size:.58rem;color:#059669;text-transform:uppercase;letter-spacing:.5px;">Constructor</div>
            <div style="font-size:1.2rem;font-weight:900;color:#16a34a;">{{ $a['split_developer'] }}%</div>
            <div style="font-size:.65rem;color:#059669;font-weight:600;">${{ number_format($a['utilidad_total'] - $a['parte_dono']) }}</div>
            <div style="font-size:.58rem;color:#16a34a;">aporta toda la construcción</div>
        </div>
    </div>
    <div style="font-size:.65rem;color:#7c3aed;background:#f5f3ff;border-radius:6px;padding:.35rem .6rem;">
        Utilidad total estimada: <strong>${{ number_format($a['utilidad_total']) }}</strong>
        · El dueño recibe más que una venta a bajo precio, el constructor reduce su riesgo de capital.
    </div>
</div>
</div>
@endif

{{-- Norma 10 --}}
@if($r['norma10_aplica'])
<div style="background:#fffbeb;border:1px solid #fde047;border-radius:8px;padding:.6rem .85rem;font-size:.7rem;color:#713f12;margin-bottom:.75rem;">
    💡 <strong>Norma 10 CDMX:</strong> Lote {{ $m2Terreno }} m² puede calificar para mayor densidad bajo Norma 10 (60–200 m²). Verificar con SEDUVI antes de finalizar.
</div>
@endif

<div style="font-size:.6rem;color:#94a3b8;line-height:1.5;text-align:center;">
    Método de Desarrollo · Costos ref. CEICO-CMIC 2025 · Observatorio HDV<br>
    No constituye Opinión de Valor formal · Requiere visita técnica y consulta SEDUVI
</div>

@elseif($result && !$result['available'])
<div class="alert alert-warning">{{ $result['reason'] }}</div>

@else
<div style="border:2px dashed #e2e8f0;border-radius:12px;padding:2rem 1.25rem;text-align:center;color:#94a3b8;">
    <div style="font-size:2.2rem;margin-bottom:.7rem;">🏗</div>
    <div style="font-size:.88rem;font-weight:700;color:#475569;margin-bottom:.3rem;">Análisis de viabilidad para constructor</div>
    <div style="font-size:.75rem;line-height:1.7;margin-bottom:1.1rem;">
        Ingresa los datos del terreno para ver<br>si conviene ofrecérselo a una constructora
    </div>
    <div style="background:#f8fafc;border-radius:8px;padding:.75rem 1rem;text-align:left;font-size:.7rem;color:#64748b;display:flex;flex-direction:column;gap:.35rem;">
        <div>★ <strong>Incidencia del terreno</strong> — la métrica principal que usa cualquier developer</div>
        <div>✓ Precio de oferta sugerido basado en método residual</div>
        <div>✓ Veredicto: Compra directa / Viable / Negocia / Descarta</div>
        <div>✓ Waterfall financiero completo (construcción, permisos, financiamiento)</div>
        <div>✓ Esquema de asociación como alternativa a la venta directa</div>
        <div>✓ Parser automático de clave SEDUVI → COS/CUS/pisos</div>
    </div>
</div>
@endif

</div>
{{-- /columna derecha --}}

</div>

</div>
