{{-- Constructor Valuation — COS/CUS land feasibility tool --}}
<div>

@php
    // Precio derivado (solo display, sin modificar el modelo)
    $m2Float     = (float) ($m2Terreno ?: 0);
    $totalFloat  = (float) str_replace([',',' '], '', $precioTerreno ?: '0');
    $perM2Float  = (float) str_replace([',',' '], '', $precioTerrenoM2 ?: '0');
    $derivedM2   = ($precioMode === 'total' && $m2Float > 0 && $totalFloat > 0)
                    ? (int) round($totalFloat / $m2Float) : null;
    $derivedTotal= ($precioMode === 'per_m2' && $m2Float > 0 && $perM2Float > 0)
                    ? (int) round($perM2Float * $m2Float) : null;

    // Colores del semáforo
    $sv = match($result['viabilidad'] ?? '') {
        'green'  => ['bg'=>'#f0fdf4','border'=>'#86efac','text'=>'#166534','dot'=>'#059669','label'=>'VIABLE'],
        'yellow' => ['bg'=>'#fefce8','border'=>'#fde047','text'=>'#713f12','dot'=>'#d97706','label'=>'BORDERLINE'],
        'red'    => ['bg'=>'#fef2f2','border'=>'#fca5a5','text'=>'#991b1b','dot'=>'#dc2626','label'=>'NO VIABLE'],
        default  => [],
    };
    $dot = fn(string $k, $v) => match($k) {
        'roi'   => $v >= 18 ? '#059669' : ($v >= 12 ? '#d97706' : '#dc2626'),
        'tierra'=> $v <= 8000 ? '#059669' : ($v <= 12000 ? '#d97706' : '#dc2626'),
        'ratio' => $v <= 20  ? '#059669' : ($v <= 30  ? '#d97706' : '#dc2626'),
        default => '#94a3b8',
    };
@endphp

<style>
.cv-label { display:block;font-size:.8rem;font-weight:600;margin-bottom:.35rem;color:var(--text); }
.cv-hint  { font-size:.68rem;color:#94a3b8;margin-top:.2rem;display:block; }
.cv-input { width:100%;padding:.55rem .8rem;border:1px solid var(--border);border-radius:var(--radius);
            font-family:inherit;font-size:.88rem;background:var(--card);color:var(--text); }
.cv-input:focus { outline:none;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12); }
.cv-input-ro { background:#f8fafc!important;color:#64748b;cursor:default; }
.cv-section-title {
    font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;
    color:#64748b;margin-bottom:.65rem;display:flex;align-items:center;gap:.4rem;
}
.cv-section-title .n {
    width:20px;height:20px;border-radius:50%;background:#f1f5f9;
    display:inline-flex;align-items:center;justify-content:center;
    font-size:.65rem;font-weight:800;color:#64748b;flex-shrink:0;
}
.preset-btn {
    font-size:.68rem;font-weight:600;padding:.22rem .55rem;border-radius:5px;cursor:pointer;
    border:1px solid var(--border);background:var(--card);color:#64748b;transition:all .12s;
    white-space:nowrap;
}
.preset-btn:hover { background:#eff6ff;border-color:#6366f1;color:#6366f1; }
.preset-btn.active { background:#6366f1;border-color:#6366f1;color:#fff; }
</style>

<div style="display:flex;gap:1.25rem;align-items:flex-start;">

{{-- ════════════════════════════════════════════════════
     Columna izquierda — Formulario
     ════════════════════════════════════════════════════ --}}
<div style="flex:1;min-width:0;display:flex;flex-direction:column;gap:.9rem;">

{{-- ── 1. Terreno ─────────────────────────────────────────────────────── --}}
<div class="card">
<div class="card-body" style="padding:1rem 1.25rem;">
<div class="cv-section-title"><span class="n">1</span> Datos del terreno</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">

    {{-- Colonia --}}
    <div style="grid-column:1/-1;">
        <label class="cv-label">Colonia <span style="font-weight:400;color:#94a3b8;">(para precio de mercado)</span></label>
        <select wire:model.live="coloniaId" class="cv-input">
            <option value="">— Sin colonia / precio manual —</option>
            @foreach($colonias as $zoneName => $cols)
            <optgroup label="{{ $zoneName }}">
                @foreach($cols as $col)
                <option value="{{ $col->id }}">{{ $col->name }}</option>
                @endforeach
            </optgroup>
            @endforeach
        </select>
    </div>

    {{-- m² terreno — wire:model.blur para evitar que se borre mientras se escribe --}}
    <div>
        <label class="cv-label">m² de terreno <span style="color:var(--danger)">*</span></label>
        <input wire:model.blur="m2Terreno" wire:change="recalculate"
               type="number" min="10" step="1" placeholder="200" class="cv-input">
        @if($frente && $fondo)
        <span class="cv-hint" style="color:#059669;">
            ✓ Frente×Fondo = {{ number_format((float)$frente * (float)$fondo, 0) }} m²
        </span>
        @endif
        @if($m2Terreno && (float)$m2Terreno < 200)
        <span class="cv-hint" style="color:#d97706;">⚠ < 200 m² · revisar Norma 10</span>
        @endif
    </div>

    {{-- Frente × Fondo --}}
    <div>
        <label class="cv-label">Frente × Fondo (m) <span style="font-weight:400;color:#94a3b8;">opcional</span></label>
        <div style="display:flex;gap:.4rem;align-items:center;">
            <input wire:model="frente" type="number" min="0" step="0.5" placeholder="10" class="cv-input" style="flex:1;">
            <span style="color:#94a3b8;font-weight:700;font-size:.9rem;">×</span>
            <input wire:model="fondo"  type="number" min="0" step="0.5" placeholder="20" class="cv-input" style="flex:1;">
        </div>
    </div>

</div>
</div>
</div>

{{-- ── 2. Zonificación ──────────────────────────────────────────────────── --}}
<div class="card">
<div class="card-body" style="padding:1rem 1.25rem;">
<div class="cv-section-title"><span class="n">2</span> Zonificación urbana · PDDU CDMX</div>

    {{-- Campo libre para clave de zonificación — parseo automático --}}
    <div style="margin-bottom:.75rem;">
        <label class="cv-label">
            Clave de zonificación SEDUVI
            <span style="font-weight:400;color:#94a3b8;font-size:.72rem;"> · auto-calcula COS, CUS y niveles</span>
        </label>
        <input wire:model.live.debounce.600ms="zonificacionLabel"
               type="text"
               placeholder="ej. HM 6/30  ó  H4/40  ó  HC4/Z/30  ó  CB5/30"
               class="cv-input"
               style="font-family:monospace;font-size:.95rem;font-weight:700;letter-spacing:.5px;
                      border-color:{{ $parsedZone ? '#059669' : 'var(--border)' }};">

        {{-- Feedback del parser --}}
        @if($parsedZone)
        <div style="margin-top:.35rem;background:#f0fdf4;border:1px solid #86efac;border-radius:6px;
                    padding:.35rem .7rem;font-size:.72rem;color:#166534;display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
            <span>✅ Uso <strong>{{ $parsedZone['uso'] }}</strong></span>
            <span>·</span>
            <span><strong>{{ $parsedZone['pisos'] }}</strong> niveles</span>
            <span>·</span>
            <span>COS <strong>{{ $parsedZone['cos'] }}</strong></span>
            <span>·</span>
            <span>CUS <strong>{{ $parsedZone['cus'] }}</strong></span>
            @if($parsedZone['lote_min'])
            <span>·</span>
            <span>Lote mín. <strong>{{ $parsedZone['lote_min'] }} m²</strong></span>
            @endif
        </div>
        @elseif($zonificacionLabel)
        <div style="margin-top:.35rem;background:#fefce8;border:1px solid #fde047;border-radius:6px;
                    padding:.35rem .7rem;font-size:.72rem;color:#713f12;">
            ⚠ No se reconoce el formato — ajusta COS, CUS y niveles manualmente, o usa un preset
        </div>
        @else
        <span class="cv-hint">Escribe la clave del certificado SEDUVI o usa un preset rápido ↓</span>
        @endif
    </div>

    {{-- Presets rápidos --}}
    <div style="display:flex;flex-wrap:wrap;gap:.3rem;margin-bottom:1rem;">
        @foreach($presets as $key => $p)
        @if($key !== 'custom')
        @php
            $shortLabel = match($key) {
                'H3_30'  => 'H 3/30',
                'H4_30'  => 'H 4/30',
                'HM4_30' => 'HM 4/30',
                'HM5_30' => 'HM 5/30',
                'HM6_30' => 'HM 6/30',
                'HM8_30' => 'HM 8/30',
                'HC4_30' => 'HC 4/30',
                'CB5_30' => 'CB 5/30',
                'N10'    => 'Norma 10',
                default  => $key,
            };
            $isActive = $zonificacionLabel === $shortLabel;
        @endphp
        <button wire:click="applyPreset('{{ $key }}')"
                class="preset-btn {{ $isActive ? 'active' : '' }}"
                title="{{ $p['label'] }} · COS {{ $p['cos'] }} · CUS {{ $p['cus'] }}">
            {{ $shortLabel }}
        </button>
        @endif
        @endforeach
    </div>

    {{-- COS / CUS / Pisos — siempre editables --}}
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;">
        <div>
            <label class="cv-label">
                COS
                <span style="font-size:.66rem;color:#6366f1;font-weight:400;cursor:help;"
                      title="Coeficiente de Ocupación del Suelo: % del terreno que puede ocupar la huella de la construcción"> ⓘ</span>
            </label>
            <input wire:model.live.debounce.400ms="cos" type="number" min="0.01" max="1.00" step="0.01"
                   placeholder="0.60" class="cv-input">
            @if($cos && $m2Terreno)
            <span class="cv-hint" style="color:#059669;">= {{ number_format((float)$cos * (float)$m2Terreno, 0) }} m² huella</span>
            @else
            <span class="cv-hint">% del terreno con huella</span>
            @endif
        </div>
        <div>
            <label class="cv-label">
                CUS
                <span style="font-size:.66rem;color:#6366f1;font-weight:400;cursor:help;"
                      title="Coeficiente de Utilización del Suelo: total de m² brutos construibles / m² de terreno"> ⓘ</span>
            </label>
            <input wire:model.live.debounce.400ms="cus" type="number" min="0.1" max="30" step="0.1"
                   placeholder="3.60" class="cv-input">
            @if($cus && $m2Terreno)
            <span class="cv-hint" style="color:#059669;">= {{ number_format((float)$cus * (float)$m2Terreno, 0) }} m² brutos</span>
            @else
            <span class="cv-hint">m² brutos / m² terreno</span>
            @endif
        </div>
        <div>
            <label class="cv-label">Niveles</label>
            <input wire:model.live.debounce.400ms="pisos" type="number" min="1" max="40" step="1"
                   placeholder="6" class="cv-input">
            @if($cos && $cus && (float)$cos > 0)
            <span class="cv-hint">{{ round((float)$cus / (float)$cos, 1) }} pisos según CUS/COS</span>
            @endif
        </div>
    </div>

</div>
</div>

{{-- ── 3. Precio del terreno ────────────────────────────────────────────── --}}
<div class="card">
<div class="card-body" style="padding:1rem 1.25rem;">
<div class="cv-section-title"><span class="n">3</span> Precio del terreno</div>

    {{-- Toggle modo --}}
    <div style="display:inline-flex;border:1px solid var(--border);border-radius:8px;overflow:hidden;margin-bottom:.85rem;">
        <button wire:click="$set('precioMode','total')"
                style="padding:.35rem .9rem;font-size:.78rem;font-weight:600;border:none;cursor:pointer;
                       background:{{ $precioMode==='total' ? '#6366f1' : 'transparent' }};
                       color:{{ $precioMode==='total' ? '#fff' : '#64748b' }};">
            Precio total
        </button>
        <button wire:click="$set('precioMode','per_m2')"
                style="padding:.35rem .9rem;font-size:.78rem;font-weight:600;border:none;cursor:pointer;
                       background:{{ $precioMode==='per_m2' ? '#6366f1' : 'transparent' }};
                       color:{{ $precioMode==='per_m2' ? '#fff' : '#64748b' }};">
            Por m²
        </button>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">

        @if($precioMode === 'total')
        {{-- MODO TOTAL --}}
        <div>
            <label class="cv-label">Precio total (MXN) <span style="color:var(--danger)">*</span></label>
            {{-- wire:model.blur + wire:change para evitar que se borre al escribir --}}
            <input wire:model.blur="precioTerreno"
                   wire:change="recalculate"
                   type="text" inputmode="numeric" placeholder="4,500,000" class="cv-input">
            @if($derivedM2)
            <span class="cv-hint" style="color:#059669;">= ${{ number_format($derivedM2) }}/m²</span>
            @else
            <span class="cv-hint">ej. 4500000 (sin comas)</span>
            @endif
        </div>
        <div>
            <label class="cv-label">Precio por m² (referencia)</label>
            <div class="cv-input cv-input-ro">
                {{ $derivedM2 ? '$' . number_format($derivedM2) . '/m²' : '— ingresar m² y precio total' }}
            </div>
        </div>

        @else
        {{-- MODO POR M² --}}
        <div>
            <label class="cv-label">Precio por m² (MXN) <span style="color:var(--danger)">*</span></label>
            <input wire:model.blur="precioTerrenoM2"
                   wire:change="recalculate"
                   type="text" inputmode="numeric" placeholder="22,500" class="cv-input">
            @if($derivedTotal)
            <span class="cv-hint" style="color:#059669;">= ${{ number_format($derivedTotal) }} total</span>
            @else
            <span class="cv-hint">ej. 22500 (sin comas)</span>
            @endif
        </div>
        <div>
            <label class="cv-label">Total calculado</label>
            <div class="cv-input cv-input-ro">
                {{ $derivedTotal ? '$' . number_format($derivedTotal) : '— ingresar m² y precio/m²' }}
            </div>
        </div>
        @endif

    </div>
</div>
</div>

{{-- ── 4. Parámetros de construcción ──────────────────────────────────── --}}
<div class="card" x-data="{ open: false }">
<div class="card-header" style="cursor:pointer;border-bottom:1px solid var(--border);" @click="open=!open">
    <h3 style="font-size:.82rem;font-weight:600;color:var(--text);display:flex;align-items:center;width:100%;">
        <span class="n" style="width:20px;height:20px;border-radius:50%;background:#f1f5f9;display:inline-flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:800;color:#64748b;margin-right:.4rem;flex-shrink:0;">4</span>
        Parámetros de construcción
        <span style="font-size:.7rem;font-weight:400;color:#94a3b8;margin-left:.35rem;">
            · ${{ number_format((int)$costoConstruccion) }}/m² · {{ $eficiencia }}% vendible · {{ $tamanoDepto }} m²/depto
        </span>
        <span x-text="open ? '▲' : '▼'" style="margin-left:auto;font-size:.65rem;color:#94a3b8;"></span>
    </h3>
</div>
<div x-show="open" x-transition style="display:none;">
<div class="card-body" style="padding:1rem 1.25rem;">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">

    <div>
        <label class="cv-label">Costo construcción ($/m² bruto)</label>
        <input wire:model.blur="costoConstruccion" wire:change="recalculate"
               type="number" min="10000" max="80000" step="500"
               placeholder="22000" class="cv-input">
        <span class="cv-hint">Deptos medios BJ 2025: ~$20k-$25k/m²</span>
    </div>

    <div>
        <label class="cv-label">Factor vendible ({{ $eficiencia }}%)</label>
        <input wire:model.live="eficiencia" type="range" min="60" max="90" step="1"
               style="width:100%;accent-color:#6366f1;margin-top:.45rem;">
        <span class="cv-hint">m² vendibles / m² brutos · ref: 78-85%</span>
    </div>

    <div>
        <label class="cv-label">Tamaño promedio depto (m²)</label>
        <input wire:model.live.debounce.400ms="tamanoDepto" type="number"
               min="30" max="250" step="5" placeholder="65" class="cv-input">
        <span class="cv-hint">Para estimar # de departamentos</span>
    </div>

    <div>
        <label class="cv-label">
            Precio venta depto nuevo ($/m²)
            @if($result && ($result['precio_venta_fuente'] ?? '') === 'observatorio')
            <span style="font-size:.66rem;color:#059669;font-weight:600;">· Observatorio HDV</span>
            @endif
        </label>
        <input wire:model.blur="precioVentaM2" wire:change="recalculate"
               type="text" inputmode="numeric"
               placeholder="{{ $coloniaId ? 'Auto del Observatorio' : 'ej. 72000' }}"
               class="cv-input">
        <span class="cv-hint">Vacío = tomar del Observatorio de Precios</span>
    </div>

</div>
</div>
</div>
</div>

</div>
{{-- /columna izquierda --}}


{{-- ════════════════════════════════════════════════════
     Columna derecha — Resultados
     ════════════════════════════════════════════════════ --}}
<div style="width:355px;flex-shrink:0;">

@if($result && $result['available'])
@php $r = $result; @endphp

{{-- ■ Semáforo de viabilidad ─────────────────────────────────────── --}}
<div style="background:{{ $sv['bg'] }};border:1.5px solid {{ $sv['border'] }};border-radius:12px;
            padding:.9rem 1.1rem;margin-bottom:.85rem;display:flex;align-items:center;gap:.75rem;">
    <div style="width:38px;height:38px;border-radius:50%;background:{{ $sv['dot'] }};
                display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">
        {{ $r['viabilidad']==='green' ? '✅' : ($r['viabilidad']==='yellow' ? '⚠️' : '🚫') }}
    </div>
    <div style="flex:1;min-width:0;">
        <div style="font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:{{ $sv['text'] }};">
            {{ $sv['label'] }} PARA CONSTRUCTOR
        </div>
        <div style="font-size:.7rem;color:{{ $sv['text'] }};opacity:.8;margin-top:.1rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
            ROI {{ $r['roi_constructor'] }}% · Tierra ${{ number_format($r['tierra_m2_vendible']) }}/m²vend · {{ $r['ratio_tierra_venta'] }}% s/venta
        </div>
    </div>
    <div wire:loading style="flex-shrink:0;">
        <span style="width:12px;height:12px;border:2px solid {{ $sv['dot'] }}44;border-top-color:{{ $sv['dot'] }};
                     border-radius:50%;display:inline-block;animation:spin 1s linear infinite;"></span>
    </div>
</div>

{{-- ■ Potencial constructivo ─────────────────────────────────────── --}}
<div class="card" style="margin-bottom:.85rem;border-top:3px solid #6366f1;">
<div class="card-body" style="padding:.85rem 1.1rem;">
    <div class="cv-section-title" style="margin-bottom:.65rem;">📐 Potencial constructivo</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem .9rem;margin-bottom:.7rem;">
        <div>
            <div style="font-size:.58rem;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;">m² vendibles</div>
            <div style="font-size:1.55rem;font-weight:900;color:#6366f1;line-height:1.1;">{{ number_format($r['m2_vendibles'], 0) }}</div>
            <div style="font-size:.63rem;color:#94a3b8;">de {{ number_format($r['m2_brutos'], 0) }} m² brutos</div>
        </div>
        <div>
            <div style="font-size:.58rem;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;">Deptos estimados</div>
            <div style="font-size:1.55rem;font-weight:900;color:#0f172a;line-height:1.1;">~{{ $r['deptos_estimados'] }}</div>
            <div style="font-size:.63rem;color:#94a3b8;">de {{ $tamanoDepto }} m² c/u</div>
        </div>
    </div>
    {{-- Barra visual COS --}}
    <div style="background:#f1f5f9;border-radius:6px;padding:.5rem .7rem;font-size:.68rem;">
        <div style="display:flex;justify-content:space-between;margin-bottom:.25rem;">
            <span style="color:#64748b;">Huella (COS {{ $cos }})</span>
            <strong>{{ number_format($r['m2_huella'], 0) }} m²</strong>
        </div>
        <div style="height:6px;background:#e2e8f0;border-radius:3px;overflow:hidden;margin-bottom:.45rem;">
            <div style="height:100%;width:{{ min(100,(float)$cos*100) }}%;background:linear-gradient(90deg,#6366f1,#818cf8);border-radius:3px;"></div>
        </div>
        <div style="display:flex;justify-content:space-between;color:#64748b;">
            <span>CUS <strong style="color:#0f172a;">{{ $cus }}</strong></span>
            <span>{{ $pisos }} pisos</span>
            <span>Eficiencia <strong style="color:#0f172a;">{{ $eficiencia }}%</strong></span>
            <span>Clave <strong style="color:#6366f1;font-family:monospace;">{{ $zonificacionLabel ?: '—' }}</strong></span>
        </div>
    </div>
</div>
</div>

{{-- ■ Análisis financiero ─────────────────────────────────────────── --}}
<div class="card" style="margin-bottom:.85rem;">
<div class="card-body" style="padding:.85rem 1.1rem;">
    <div class="cv-section-title" style="margin-bottom:.55rem;">
        💰 Análisis financiero
        <span style="font-weight:400;text-transform:none;font-size:.65rem;color:#94a3b8;">
            · ${{ number_format($r['precio_venta_m2']) }}/m²
            @if($r['precio_venta_fuente']==='observatorio') (Observatorio) @else (manual) @endif
        </span>
    </div>
    <table style="width:100%;font-size:.75rem;border-collapse:collapse;">
        <tr style="border-bottom:1px solid #f1f5f9;">
            <td style="padding:.32rem 0;color:#64748b;">Valor de venta total</td>
            <td style="text-align:right;font-weight:600;color:#0f172a;">${{ number_format($r['valor_venta_total']) }}</td>
        </tr>
        <tr style="border-bottom:1px solid #f1f5f9;">
            <td style="padding:.32rem 0;color:#ef4444;">− Construcción</td>
            <td style="text-align:right;color:#ef4444;">−${{ number_format($r['costo_construccion']) }}</td>
        </tr>
        <tr style="border-bottom:1px solid #f1f5f9;">
            <td style="padding:.32rem 0;color:#f97316;">− Indirectos (20%)</td>
            <td style="text-align:right;color:#f97316;">−${{ number_format($r['costos_indirectos']) }}</td>
        </tr>
        <tr style="border-bottom:1px solid #f1f5f9;">
            <td style="padding:.32rem 0;color:#f97316;">− Financiero terreno (15%)</td>
            <td style="text-align:right;color:#f97316;">−${{ number_format($r['gasto_financiero']) }}</td>
        </tr>
        <tr style="border-bottom:2px solid #e2e8f0;">
            <td style="padding:.32rem 0;color:#dc2626;font-weight:600;">− Precio del terreno</td>
            <td style="text-align:right;font-weight:600;color:#dc2626;">
                −${{ number_format((int)($precioMode==='per_m2' ? $derivedTotal : str_replace([',',' '],'',$precioTerreno))) }}
            </td>
        </tr>
        <tr>
            <td style="padding:.4rem 0;font-weight:700;font-size:.8rem;">= Utilidad neta</td>
            <td style="text-align:right;font-weight:900;font-size:.9rem;
                color:{{ $r['utilidad_neta']>=0 ? '#059669' : '#dc2626' }};">
                {{ $r['utilidad_neta']>=0 ? '' : '−' }}${{ number_format(abs($r['utilidad_neta'])) }}
            </td>
        </tr>
    </table>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.45rem;margin-top:.6rem;">
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:.5rem .65rem;text-align:center;">
            <div style="font-size:.57rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;">ROI total</div>
            <div style="font-size:1.1rem;font-weight:900;color:{{ $dot('roi',$r['roi_constructor']) }};">{{ $r['roi_constructor'] }}%</div>
            <div style="font-size:.57rem;color:#94a3b8;">mín. 18%</div>
        </div>
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:.5rem .65rem;text-align:center;">
            <div style="font-size:.57rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;">Margen s/venta</div>
            <div style="font-size:1.1rem;font-weight:900;
                color:{{ $r['margen_sobre_venta']>=15 ? '#059669' : ($r['margen_sobre_venta']>=10 ? '#d97706' : '#dc2626') }};">
                {{ $r['margen_sobre_venta'] }}%
            </div>
            <div style="font-size:.57rem;color:#94a3b8;">sobre precio venta</div>
        </div>
    </div>
</div>
</div>

{{-- ■ Valor residual ──────────────────────────────────────────────── --}}
<div class="card" style="margin-bottom:.85rem;border-left:4px solid {{ $r['brecha_pct']>=0 ? '#059669' : '#dc2626' }};">
<div class="card-body" style="padding:.85rem 1.1rem;">
    <div class="cv-section-title" style="margin-bottom:.4rem;">
        🏗 Valor residual del terreno
    </div>
    <div style="font-size:.65rem;color:#94a3b8;margin-bottom:.5rem;">Método de desarrollo · ROI objetivo 22%</div>
    <div style="font-size:1.3rem;font-weight:900;color:{{ $r['valor_residual_terreno']>0 ? '#0f172a' : '#dc2626' }};">
        {{ $r['valor_residual_terreno']>0 ? '$'.number_format($r['valor_residual_terreno']) : 'No viable' }}
    </div>
    <div style="font-size:.7rem;color:#64748b;margin-bottom:.6rem;">
        ${{ number_format($r['precio_residual_m2']) }}/m² · precio máximo que debería pagar el constructor
    </div>
    <div style="background:{{ $r['brecha_pct']>=0 ? '#f0fdf4' : '#fef2f2' }};
                border:1px solid {{ $r['brecha_pct']>=0 ? '#86efac' : '#fca5a5' }};
                border-radius:8px;padding:.5rem .75rem;font-size:.72rem;">
        @if($r['brecha_pct'] >= 0)
        <strong style="color:#166534;">✅ {{ $r['brecha_pct'] }}% bajo el valor residual</strong>
        <div style="color:#166534;margin-top:.15rem;">
            El constructor tiene ${{ number_format($r['brecha_valor']) }} de margen adicional sobre el precio pedido.
        </div>
        @else
        <strong style="color:#991b1b;">🚫 {{ abs($r['brecha_pct']) }}% sobre el valor residual</strong>
        <div style="color:#991b1b;margin-top:.15rem;">
            El terreno está ${{ number_format(abs($r['brecha_valor'])) }} por encima del precio viable para el constructor.
        </div>
        @endif
    </div>
</div>
</div>

{{-- ■ Indicadores clave ───────────────────────────────────────────── --}}
<div class="card" style="margin-bottom:.85rem;">
<div class="card-body" style="padding:.85rem 1.1rem;">
    <div class="cv-section-title" style="margin-bottom:.55rem;">📊 Indicadores clave</div>
    @foreach([
        ['tierra', $r['tierra_m2_vendible'], 'Tierra/m² vendible', '$'.number_format($r['tierra_m2_vendible']), '< $8k = excelente · < $12k = aceptable'],
        ['ratio',  $r['ratio_tierra_venta'], 'Ratio terreno / ventas', $r['ratio_tierra_venta'].'%', '< 20% = óptimo · 20-30% = aceptable'],
        ['roi',    $r['roi_constructor'], 'ROI del constructor', $r['roi_constructor'].'%', '> 18% = viable · 12-18% = borderline'],
    ] as [$key, $val, $label, $display, $hint])
    <div style="display:flex;align-items:center;justify-content:space-between;padding:.38rem .6rem;
                background:#f8fafc;border-radius:6px;margin-bottom:.3rem;">
        <div>
            <div style="font-size:.7rem;font-weight:600;color:#0f172a;">{{ $label }}</div>
            <div style="font-size:.6rem;color:#94a3b8;">{{ $hint }}</div>
        </div>
        <div style="font-size:.95rem;font-weight:800;color:{{ $dot($key,$val) }};text-align:right;">
            {{ $display }}
        </div>
    </div>
    @endforeach
    <div style="display:flex;align-items:center;justify-content:space-between;padding:.38rem .6rem;
                background:#f8fafc;border-radius:6px;">
        <div>
            <div style="font-size:.7rem;font-weight:600;color:#0f172a;">Precio terreno / m²</div>
            <div style="font-size:.6rem;color:#94a3b8;">Referencia de mercado</div>
        </div>
        <div style="font-size:.95rem;font-weight:800;color:#0f172a;">${{ number_format($r['precio_terreno_m2']) }}/m²</div>
    </div>
</div>
</div>

{{-- Norma 10 --}}
@if($r['norma10_aplica'])
<div style="background:#fffbeb;border:1px solid #fde047;border-radius:8px;padding:.6rem .85rem;font-size:.7rem;color:#713f12;margin-bottom:.75rem;">
    💡 <strong>Norma 10 CDMX:</strong> Lote {{ $m2Terreno }} m² puede calificar para mayor densidad bajo Norma 10 (lotes 60–200 m²). Consultar con SEDUVI.
</div>
@endif

<div style="font-size:.6rem;color:#94a3b8;line-height:1.5;text-align:center;">
    Método de Desarrollo · Costos de referencia CDMX 2025 · Observatorio de Precios HDV<br>
    No constituye Opinión de Valor formal — requiere visita técnica y consulta SEDUVI
</div>

@elseif($result && !$result['available'])
<div class="alert alert-warning">{{ $result['reason'] }}</div>

@else
<div style="border:2px dashed #e2e8f0;border-radius:12px;padding:2rem 1.25rem;text-align:center;color:#94a3b8;">
    <div style="font-size:2.2rem;margin-bottom:.65rem;">🏗</div>
    <div style="font-size:.85rem;font-weight:600;color:#64748b;margin-bottom:.4rem;">Análisis de viabilidad</div>
    <div style="font-size:.72rem;line-height:1.7;margin-bottom:1rem;">
        Ingresa m², zonificación y precio del terreno<br>para ver el análisis completo en tiempo real
    </div>
    <div style="background:#f8fafc;border-radius:8px;padding:.7rem 1rem;text-align:left;font-size:.7rem;color:#64748b;display:flex;flex-direction:column;gap:.3rem;">
        <div>✓ Potencial constructivo (m² vendibles, deptos)</div>
        <div>✓ Análisis financiero (ROI, margen, waterfall)</div>
        <div>✓ Valor residual · Método de Desarrollo</div>
        <div>✓ Indicadores COS/CUS con semáforo</div>
        <div>✓ Verifica viabilidad con cualquier clave PDDU</div>
    </div>
</div>
@endif

</div>
{{-- /columna derecha --}}

</div>
{{-- /layout --}}

</div>
