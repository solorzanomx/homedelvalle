{{-- Constructor Valuation — COS/CUS land feasibility tool --}}
<div>

@php
    $isCustom = $zonificacion === 'custom';

    // Colores del semáforo
    $svColor = match($result['viabilidad'] ?? '') {
        'green'  => ['bg' => '#f0fdf4', 'border' => '#86efac', 'text' => '#166534', 'badge' => '#059669', 'label' => 'VIABLE'],
        'yellow' => ['bg' => '#fefce8', 'border' => '#fde047', 'text' => '#713f12', 'badge' => '#d97706', 'label' => 'BORDERLINE'],
        'red'    => ['bg' => '#fef2f2', 'border' => '#fca5a5', 'text' => '#991b1b', 'badge' => '#dc2626', 'label' => 'NO VIABLE'],
        default  => [],
    };

    // Helper para indicador semáforo de un valor
    $indColor = fn(string $k, $v) => match($k) {
        'roi'   => $v >= 18 ? '#059669' : ($v >= 12 ? '#d97706' : '#dc2626'),
        'tierra'=> $v <= 8000 ? '#059669' : ($v <= 12000 ? '#d97706' : '#dc2626'),
        'ratio' => $v <= 20  ? '#059669' : ($v <= 30  ? '#d97706' : '#dc2626'),
        default => '#94a3b8',
    };
@endphp

{{-- ══════════════════════════════════════════════════════════════════════
     LAYOUT: Form (izq) + Panel de resultados (der)
     ══════════════════════════════════════════════════════════════════════ --}}
<div style="display:flex;gap:1.25rem;align-items:flex-start;">

{{-- ─── Columna izquierda: formulario ───────────────────────────────── --}}
<div style="flex:1;min-width:0;display:flex;flex-direction:column;gap:1rem;">

{{-- ── Sección 1: Terreno ────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header" style="border-bottom:2px solid #f1f5f9;">
        <h3 style="font-size:.9rem;font-weight:700;color:var(--text);display:flex;align-items:center;gap:.5rem;">
            <span style="width:22px;height:22px;background:#f1f5f9;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:800;color:#64748b;">1</span>
            Datos del terreno
        </h3>
    </div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">

            {{-- Colonia --}}
            <div class="form-group" style="grid-column:1/-1;">
                <label class="form-label">Colonia (para precio de mercado)</label>
                <select wire:model.live="coloniaId" class="form-select">
                    <option value="">— Sin colonia / ingresa precio manual —</option>
                    @foreach($colonias as $zoneName => $cols)
                    <optgroup label="{{ $zoneName }}">
                        @foreach($cols as $col)
                        <option value="{{ $col->id }}">{{ $col->name }}</option>
                        @endforeach
                    </optgroup>
                    @endforeach
                </select>
            </div>

            {{-- m² terreno --}}
            <div class="form-group">
                <label class="form-label">m² de terreno <span class="required">*</span></label>
                <input wire:model.live.debounce.400ms="m2Terreno" type="number" min="10" step="1"
                       placeholder="200" class="form-input">
                @if($m2Terreno && (float)$m2Terreno < 200)
                <span style="font-size:.7rem;color:#d97706;margin-top:.2rem;display:block;">
                    ⚠ Lote < 200 m² · verificar Norma 10 CDMX
                </span>
                @endif
            </div>

            {{-- Frente / Fondo --}}
            <div class="form-group">
                <label class="form-label">Frente × Fondo (m) <span style="color:#9ca3af;font-weight:400;">(opcional)</span></label>
                <div style="display:flex;gap:.5rem;align-items:center;">
                    <input wire:model="frente" type="number" min="0" step="0.5"
                           placeholder="10" class="form-input" style="flex:1;">
                    <span style="color:#94a3b8;font-weight:700;">×</span>
                    <input wire:model="fondo" type="number" min="0" step="0.5"
                           placeholder="20" class="form-input" style="flex:1;">
                </div>
                @if($frente && $fondo)
                <span style="font-size:.7rem;color:#059669;margin-top:.2rem;display:block;">
                    ✓ {{ number_format((float)$frente * (float)$fondo, 0) }} m² según frente × fondo
                </span>
                @endif
            </div>

        </div>
    </div>
</div>

{{-- ── Sección 2: Zonificación ────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header" style="border-bottom:2px solid #f1f5f9;">
        <h3 style="font-size:.9rem;font-weight:700;color:var(--text);display:flex;align-items:center;gap:.5rem;">
            <span style="width:22px;height:22px;background:#f1f5f9;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:800;color:#64748b;">2</span>
            Zonificación urbana (PDDU CDMX)
        </h3>
    </div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.85rem;">

            {{-- Selector de zonificación --}}
            <div class="form-group" style="grid-column:1/-1;">
                <label class="form-label">Clave de zonificación</label>
                <select wire:model.live="zonificacion" class="form-select">
                    @foreach($zonificaciones as $key => $z)
                    <option value="{{ $key }}">{{ $z['label'] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- COS --}}
            <div class="form-group">
                <label class="form-label">
                    COS
                    <span style="font-size:.68rem;color:#6366f1;font-weight:400;" title="Coeficiente de Ocupación del Suelo — % máximo del terreno que puede tener huella">ⓘ % huella</span>
                </label>
                <input wire:model.live.debounce.400ms="cos" type="number" min="0.01" max="1.00" step="0.01"
                       placeholder="0.60" class="form-input"
                       {{ !$isCustom ? 'style=background:#f8fafc;' : '' }}>
                @if(!$isCustom && $cos)
                <span style="font-size:.68rem;color:#059669;margin-top:.2rem;display:block;">
                    = {{ round((float)$cos * 100) }}% del terreno
                </span>
                @endif
            </div>

            {{-- CUS --}}
            <div class="form-group">
                <label class="form-label">
                    CUS
                    <span style="font-size:.68rem;color:#6366f1;font-weight:400;" title="Coeficiente de Utilización del Suelo — m² brutos totales / m² terreno">ⓘ total bruto/m²</span>
                </label>
                <input wire:model.live.debounce.400ms="cus" type="number" min="0.1" max="20" step="0.1"
                       placeholder="3.60" class="form-input"
                       {{ !$isCustom ? 'style=background:#f8fafc;' : '' }}>
                @if(!$isCustom && $cus && $m2Terreno)
                <span style="font-size:.68rem;color:#059669;margin-top:.2rem;display:block;">
                    = {{ number_format((float)$cus * (float)$m2Terreno, 0) }} m² brutos
                </span>
                @endif
            </div>

            {{-- Pisos --}}
            <div class="form-group">
                <label class="form-label">Niveles</label>
                <input wire:model.live.debounce.400ms="pisos" type="number" min="1" max="30" step="1"
                       placeholder="6" class="form-input"
                       {{ !$isCustom ? 'style=background:#f8fafc;' : '' }}>
            </div>

        </div>

        @if(!$isCustom)
        <div style="background:#eff6ff;border-radius:6px;padding:.45rem .75rem;font-size:.72rem;color:#1e40af;margin-top:.25rem;">
            💡 COS y CUS se autocompletaron con la norma seleccionada. Elige <strong>Personalizado</strong> para editarlos.
        </div>
        @endif
    </div>
</div>

{{-- ── Sección 3: Precio del terreno ─────────────────────────────────── --}}
<div class="card">
    <div class="card-header" style="border-bottom:2px solid #f1f5f9;">
        <h3 style="font-size:.9rem;font-weight:700;color:var(--text);display:flex;align-items:center;gap:.5rem;">
            <span style="width:22px;height:22px;background:#f1f5f9;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:800;color:#64748b;">3</span>
            Precio del terreno
        </h3>
    </div>
    <div class="card-body">
        {{-- Toggle: total vs por m² --}}
        <div style="display:flex;gap:.5rem;margin-bottom:.85rem;">
            <button wire:click="$set('precioMode','total')"
                    class="{{ $precioMode === 'total' ? 'btn btn-primary btn-sm' : 'btn btn-outline btn-sm' }}">
                Precio total
            </button>
            <button wire:click="$set('precioMode','per_m2')"
                    class="{{ $precioMode === 'per_m2' ? 'btn btn-primary btn-sm' : 'btn btn-outline btn-sm' }}">
                Precio por m²
            </button>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">

            @if($precioMode === 'total')
            <div class="form-group">
                <label class="form-label">Precio total (MXN) <span class="required">*</span></label>
                <input wire:model.live.debounce.500ms="precioTerreno" type="text"
                       placeholder="4,500,000" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Equivale a $/m²</label>
                <div class="form-input" style="background:#f8fafc;color:#64748b;cursor:default;">
                    @if($precioTerrenoM2)
                    ${{ number_format((int)$precioTerrenoM2) }}/m²
                    @else
                    —
                    @endif
                </div>
            </div>
            @else
            <div class="form-group">
                <label class="form-label">Precio por m² (MXN) <span class="required">*</span></label>
                <input wire:model.live.debounce.500ms="precioTerrenoM2" type="text"
                       placeholder="22,500" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Total calculado</label>
                <div class="form-input" style="background:#f8fafc;color:#64748b;cursor:default;">
                    @if($precioTerreno)
                    ${{ number_format((int)$precioTerreno) }}
                    @else
                    —
                    @endif
                </div>
            </div>
            @endif

        </div>
    </div>
</div>

{{-- ── Sección 4: Parámetros de construcción ─────────────────────────── --}}
<div class="card">
    <div class="card-header" style="border-bottom:2px solid #f1f5f9;cursor:pointer;"
         x-data="{ open: false }" @click="open=!open">
        <h3 style="font-size:.9rem;font-weight:700;color:var(--text);display:flex;align-items:center;gap:.5rem;width:100%;">
            <span style="width:22px;height:22px;background:#f1f5f9;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:800;color:#64748b;">4</span>
            Parámetros de construcción
            <span style="font-size:.72rem;font-weight:400;color:#64748b;margin-left:.25rem;">(valores de referencia CDMX 2025)</span>
            <span x-text="open ? '▲' : '▼'" style="margin-left:auto;font-size:.7rem;color:#94a3b8;"></span>
        </h3>
    </div>
    <div x-data="{ open: false }" x-show="open" style="display:none;"
         x-init="$watch('open', v => {})">
        {{-- This section hidden by default, uses Alpine --}}
    </div>
    <div class="card-body"
         x-data="{ open: true }" x-show="open">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">

            {{-- Costo de construcción --}}
            <div class="form-group">
                <label class="form-label">
                    Costo construcción ($/m² bruto)
                    <span style="font-size:.68rem;color:#94a3b8;font-weight:400;">ref: $18k-$28k</span>
                </label>
                <input wire:model.live.debounce.500ms="costoConstruccion" type="number"
                       min="10000" max="60000" step="500" class="form-input"
                       placeholder="22000">
                <span class="form-hint">Deptos medios en BJ: ~$22,000/m²</span>
            </div>

            {{-- Eficiencia vendible --}}
            <div class="form-group">
                <label class="form-label">
                    Factor vendible (%)
                    <span style="font-size:.68rem;color:#94a3b8;font-weight:400;">ref: 78-85%</span>
                </label>
                <div style="display:flex;align-items:center;gap:.5rem;">
                    <input wire:model.live.debounce.300ms="eficiencia" type="range"
                           min="60" max="90" step="1" style="flex:1;accent-color:#6366f1;">
                    <span style="font-size:.9rem;font-weight:700;color:#6366f1;min-width:40px;">{{ $eficiencia }}%</span>
                </div>
                <span class="form-hint">m² vendibles vs m² brutos construidos</span>
            </div>

            {{-- Tamaño promedio depto --}}
            <div class="form-group">
                <label class="form-label">
                    Tamaño promedio depto (m²)
                    <span style="font-size:.68rem;color:#94a3b8;font-weight:400;">ref: 55-90m²</span>
                </label>
                <input wire:model.live.debounce.300ms="tamanoDepto" type="number"
                       min="30" max="200" step="5" class="form-input"
                       placeholder="65">
            </div>

            {{-- Precio de venta $/m² (override de mercado) --}}
            <div class="form-group">
                <label class="form-label">
                    Precio venta depto nuevo ($/m²)
                    @if($result && $result['precio_venta_fuente'] === 'observatorio')
                    <span style="font-size:.68rem;color:#059669;font-weight:600;">· del Observatorio</span>
                    @endif
                </label>
                <input wire:model.live.debounce.500ms="precioVentaM2" type="text"
                       placeholder="{{ $coloniaId ? 'Automático del Observatorio' : 'ej. 65000' }}"
                       class="form-input">
                <span class="form-hint">Vacío = usar precio de mercado de la colonia seleccionada</span>
            </div>

        </div>
    </div>
</div>

</div>
{{-- /columna izquierda --}}

{{-- ─── Columna derecha: resultados ──────────────────────────────────── --}}
<div style="width:360px;flex-shrink:0;">

@if($result && $result['available'])
@php $r = $result; @endphp

    {{-- ╔═══════════════════════════════════╗ --}}
    {{-- ║   SEMÁFORO DE VIABILIDAD          ║ --}}
    {{-- ╚═══════════════════════════════════╝ --}}
    <div style="background:{{ $svColor['bg'] }};border:1.5px solid {{ $svColor['border'] }};border-radius:12px;padding:1rem 1.25rem;margin-bottom:1rem;display:flex;align-items:center;gap:.85rem;">
        <div style="width:42px;height:42px;border-radius:50%;background:{{ $svColor['badge'] }};display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;">
            {{ $r['viabilidad'] === 'green' ? '✅' : ($r['viabilidad'] === 'yellow' ? '⚠️' : '🚫') }}
        </div>
        <div>
            <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:{{ $svColor['text'] }};">
                {{ $svColor['label'] }} PARA CONSTRUCTOR
            </div>
            <div style="font-size:.78rem;color:{{ $svColor['text'] }};opacity:.85;margin-top:.15rem;">
                ROI {{ $r['roi_constructor'] }}%
                · Tierra/m²vend ${{ number_format($r['tierra_m2_vendible']) }}
                · {{ $r['ratio_tierra_venta'] }}% de las ventas
            </div>
        </div>
        <div wire:loading style="margin-left:auto;flex-shrink:0;">
            <span style="width:14px;height:14px;border:2px solid {{ $svColor['badge'] }}30;border-top-color:{{ $svColor['badge'] }};border-radius:50%;display:inline-block;animation:spin 1s linear infinite;"></span>
        </div>
    </div>

    {{-- ╔═══════════════════════════════════╗ --}}
    {{-- ║   POTENCIAL CONSTRUCTIVO          ║ --}}
    {{-- ╚═══════════════════════════════════╝ --}}
    <div class="card" style="margin-bottom:1rem;border-top:3px solid #6366f1;">
        <div class="card-body" style="padding:.9rem 1.1rem;">
            <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#6366f1;margin-bottom:.75rem;">
                📐 Potencial constructivo
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem 1rem;margin-bottom:.75rem;">
                <div>
                    <div style="font-size:.6rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;">m² vendibles</div>
                    <div style="font-size:1.5rem;font-weight:900;color:#6366f1;line-height:1.1;">{{ number_format($r['m2_vendibles'], 0) }}</div>
                    <div style="font-size:.65rem;color:#94a3b8;">de {{ number_format($r['m2_brutos'], 0) }} m² brutos</div>
                </div>
                <div>
                    <div style="font-size:.6rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;">Deptos estimados</div>
                    <div style="font-size:1.5rem;font-weight:900;color:#0f172a;line-height:1.1;">~{{ $r['deptos_estimados'] }}</div>
                    <div style="font-size:.65rem;color:#94a3b8;">de {{ $tamanoDepto }} m² promedio</div>
                </div>
            </div>

            {{-- Barra COS/CUS visual --}}
            <div style="background:#f1f5f9;border-radius:6px;padding:.55rem .75rem;font-size:.7rem;">
                <div style="display:flex;justify-content:space-between;margin-bottom:.3rem;">
                    <span style="color:#64748b;">Huella (COS {{ $cos }})</span>
                    <strong>{{ number_format($r['m2_huella'], 0) }} m²</strong>
                </div>
                <div style="height:8px;background:#e2e8f0;border-radius:4px;margin-bottom:.5rem;overflow:hidden;">
                    <div style="height:100%;width:{{ min(100, (float)$cos * 100) }}%;background:linear-gradient(90deg,#6366f1,#818cf8);border-radius:4px;"></div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.3rem;text-align:center;">
                    <div>
                        <div style="font-size:.58rem;color:#94a3b8;">CUS</div>
                        <div style="font-weight:700;color:#0f172a;">{{ $cus }}</div>
                    </div>
                    <div>
                        <div style="font-size:.58rem;color:#94a3b8;">Pisos</div>
                        <div style="font-weight:700;color:#0f172a;">{{ $pisos }}</div>
                    </div>
                    <div>
                        <div style="font-size:.58rem;color:#94a3b8;">Eficiencia</div>
                        <div style="font-weight:700;color:#0f172a;">{{ $eficiencia }}%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ╔═══════════════════════════════════╗ --}}
    {{-- ║   ANÁLISIS FINANCIERO             ║ --}}
    {{-- ╚═══════════════════════════════════╝ --}}
    <div class="card" style="margin-bottom:1rem;">
        <div class="card-body" style="padding:.9rem 1.1rem;">
            <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#0f172a;margin-bottom:.65rem;">
                💰 Análisis financiero
            </div>

            {{-- Precio de venta --}}
            <div style="font-size:.68rem;color:#64748b;margin-bottom:.5rem;">
                Precio depto nuevo: <strong style="color:#0f172a;">${{ number_format($r['precio_venta_m2']) }}/m²</strong>
                @if($r['precio_venta_fuente'] === 'observatorio')
                <span style="color:#059669;">· Observatorio HDV</span>
                @else
                <span style="color:#94a3b8;">· ingresado manualmente</span>
                @endif
            </div>

            <table style="width:100%;font-size:.75rem;border-collapse:collapse;">
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:.35rem 0;color:#64748b;">Valor de venta total</td>
                    <td style="text-align:right;font-weight:600;color:#0f172a;">${{ number_format($r['valor_venta_total']) }}</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:.35rem 0;color:#ef4444;">− Costo construcción</td>
                    <td style="text-align:right;color:#ef4444;">−${{ number_format($r['costo_construccion']) }}</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:.35rem 0;color:#f97316;">− Costos indirectos (20%)</td>
                    <td style="text-align:right;color:#f97316;">−${{ number_format($r['costos_indirectos']) }}</td>
                </tr>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:.35rem 0;color:#f97316;">− Gasto financiero (15%)</td>
                    <td style="text-align:right;color:#f97316;">−${{ number_format($r['gasto_financiero']) }}</td>
                </tr>
                <tr style="border-bottom:2px solid #e2e8f0;">
                    <td style="padding:.35rem 0;color:#dc2626;font-weight:600;">− Precio del terreno</td>
                    <td style="text-align:right;font-weight:600;color:#dc2626;">−${{ number_format((int)str_replace([',',' '],'', $precioTerreno)) }}</td>
                </tr>
                <tr>
                    <td style="padding:.45rem 0;font-weight:700;font-size:.82rem;">
                        = Utilidad neta
                    </td>
                    <td style="text-align:right;font-weight:800;font-size:.9rem;
                        color:{{ $r['utilidad_neta'] > 0 ? '#059669' : '#dc2626' }};">
                        {{ $r['utilidad_neta'] >= 0 ? '' : '−' }}${{ number_format(abs($r['utilidad_neta'])) }}
                    </td>
                </tr>
            </table>

            {{-- ROI badge --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-top:.65rem;">
                <div style="background:#f8fafc;border-radius:8px;padding:.55rem .7rem;text-align:center;border:1px solid #e2e8f0;">
                    <div style="font-size:.58rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;">ROI total</div>
                    <div style="font-size:1.15rem;font-weight:900;color:{{ $indColor('roi', $r['roi_constructor']) }};">
                        {{ $r['roi_constructor'] }}%
                    </div>
                    <div style="font-size:.6rem;color:#94a3b8;">mínimo recomendado: 18%</div>
                </div>
                <div style="background:#f8fafc;border-radius:8px;padding:.55rem .7rem;text-align:center;border:1px solid #e2e8f0;">
                    <div style="font-size:.58rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;">Margen s/ venta</div>
                    <div style="font-size:1.15rem;font-weight:900;color:{{ $r['margen_sobre_venta'] >= 15 ? '#059669' : ($r['margen_sobre_venta'] >= 10 ? '#d97706' : '#dc2626') }};">
                        {{ $r['margen_sobre_venta'] }}%
                    </div>
                    <div style="font-size:.6rem;color:#94a3b8;">sobre precio de venta</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ╔═══════════════════════════════════╗ --}}
    {{-- ║   VALOR RESIDUAL DEL TERRENO      ║ --}}
    {{-- ╚═══════════════════════════════════╝ --}}
    <div class="card" style="margin-bottom:1rem;border-left:4px solid {{ $r['brecha_pct'] >= 0 ? '#059669' : '#dc2626' }};">
        <div class="card-body" style="padding:.9rem 1.1rem;">
            <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#0f172a;margin-bottom:.5rem;">
                🏗 Valor residual del terreno
                <span style="font-size:.6rem;font-weight:400;color:#94a3b8;text-transform:none;">· Método de desarrollo (ROI 22%)</span>
            </div>
            <div style="font-size:1.3rem;font-weight:900;
                color:{{ $r['valor_residual_terreno'] > 0 ? '#0f172a' : '#dc2626' }};">
                ${{ $r['valor_residual_terreno'] > 0 ? number_format($r['valor_residual_terreno']) : '—' }}
            </div>
            <div style="font-size:.72rem;color:#64748b;margin-bottom:.65rem;">
                ${{ number_format($r['precio_residual_m2']) }}/m² · precio máximo que debería pagar el constructor para un ROI de 22%
            </div>

            {{-- Comparación precio pedido vs residual --}}
            @php
                $precioNum = (float) str_replace([',',' '], '', $precioTerreno);
                $brecha = $r['brecha_pct'];
            @endphp
            <div style="background:{{ $brecha >= 0 ? '#f0fdf4' : '#fef2f2' }};border:1px solid {{ $brecha >= 0 ? '#86efac' : '#fca5a5' }};border-radius:8px;padding:.55rem .75rem;font-size:.72rem;">
                @if($brecha >= 0)
                <div style="color:#166534;font-weight:700;">
                    ✅ Terreno <strong>{{ $brecha }}% bajo</strong> el valor residual
                </div>
                <div style="color:#166534;margin-top:.15rem;">
                    El constructor podría pagar hasta ${{ number_format($r['valor_residual_terreno']) }}
                    y aún obtener el ROI objetivo.
                </div>
                @else
                <div style="color:#991b1b;font-weight:700;">
                    🚫 Terreno <strong>{{ abs($brecha) }}% sobre</strong> el valor residual
                </div>
                <div style="color:#991b1b;margin-top:.15rem;">
                    Para ser viable al precio pedido, el constructor necesita un precio de venta
                    ≥ ${{ number_format((int)(($precioNum * (1 + 0.15) + $r['costo_construccion'] + $r['costos_indirectos']) / ($r['m2_vendibles'] ?: 1) * (1 / (1 - 0.22)))) }}/m²
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ╔═══════════════════════════════════╗ --}}
    {{-- ║   INDICADORES CLAVE               ║ --}}
    {{-- ╚═══════════════════════════════════╝ --}}
    <div class="card" style="margin-bottom:1rem;">
        <div class="card-body" style="padding:.85rem 1.1rem;">
            <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#0f172a;margin-bottom:.65rem;">
                📊 Indicadores clave para constructor
            </div>
            <div style="display:flex;flex-direction:column;gap:.4rem;">

                {{-- Tierra/m² vendible --}}
                <div style="display:flex;align-items:center;justify-content:space-between;padding:.4rem .6rem;background:#f8fafc;border-radius:6px;">
                    <div>
                        <div style="font-size:.7rem;font-weight:600;color:#0f172a;">Tierra por m² vendible</div>
                        <div style="font-size:.62rem;color:#94a3b8;">< $8,000 = excelente · < $12,000 = aceptable</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:.95rem;font-weight:800;color:{{ $indColor('tierra', $r['tierra_m2_vendible']) }};">
                            ${{ number_format($r['tierra_m2_vendible']) }}
                        </div>
                        <div style="font-size:.6rem;color:#94a3b8;">/m² vendible</div>
                    </div>
                </div>

                {{-- Ratio tierra/ventas --}}
                <div style="display:flex;align-items:center;justify-content:space-between;padding:.4rem .6rem;background:#f8fafc;border-radius:6px;">
                    <div>
                        <div style="font-size:.7rem;font-weight:600;color:#0f172a;">Ratio terreno / ventas</div>
                        <div style="font-size:.62rem;color:#94a3b8;">< 20% = óptimo · 20-30% = aceptable</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:.95rem;font-weight:800;color:{{ $indColor('ratio', $r['ratio_tierra_venta']) }};">
                            {{ $r['ratio_tierra_venta'] }}%
                        </div>
                    </div>
                </div>

                {{-- ROI constructor --}}
                <div style="display:flex;align-items:center;justify-content:space-between;padding:.4rem .6rem;background:#f8fafc;border-radius:6px;">
                    <div>
                        <div style="font-size:.7rem;font-weight:600;color:#0f172a;">ROI del constructor</div>
                        <div style="font-size:.62rem;color:#94a3b8;">> 18% = viable · 12-18% = borderline</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:.95rem;font-weight:800;color:{{ $indColor('roi', $r['roi_constructor']) }};">
                            {{ $r['roi_constructor'] }}%
                        </div>
                    </div>
                </div>

                {{-- Precio terreno/m² --}}
                <div style="display:flex;align-items:center;justify-content:space-between;padding:.4rem .6rem;background:#f8fafc;border-radius:6px;">
                    <div>
                        <div style="font-size:.7rem;font-weight:600;color:#0f172a;">Precio terreno por m²</div>
                        <div style="font-size:.62rem;color:#94a3b8;">Para referencia de mercado</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:.95rem;font-weight:800;color:#0f172a;">
                            ${{ number_format($r['precio_terreno_m2']) }}
                        </div>
                        <div style="font-size:.6rem;color:#94a3b8;">/m² terreno</div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Norma 10 notice --}}
    @if($r['norma10_aplica'])
    <div style="background:#fffbeb;border:1px solid #fde047;border-radius:8px;padding:.65rem .9rem;font-size:.72rem;color:#713f12;margin-bottom:1rem;">
        💡 <strong>Norma 10 CDMX:</strong> Este lote ({{ $m2Terreno }} m²) podría calificar para incentivos de Norma 10
        que permiten mayor densidad en lotes de 60–200 m². Verifica con el PAOT o en la consulta de SEDUVI.
    </div>
    @endif

    {{-- Disclaimer --}}
    <div style="font-size:.62rem;color:#94a3b8;line-height:1.5;text-align:center;">
        Valuación por Método de Desarrollo · Costos de referencia CDMX 2025<br>
        Datos de mercado: Observatorio de Precios HDV · Benito Juárez<br>
        No constituye Opinión de Valor formal (requiere visita técnica)
    </div>

@elseif($result && !$result['available'])
    <div class="alert alert-warning" style="margin-bottom:1rem;">
        {{ $result['reason'] }}
    </div>
@else
    {{-- Placeholder cuando no hay datos suficientes --}}
    <div style="border:2px dashed #e2e8f0;border-radius:12px;padding:2rem 1.5rem;text-align:center;color:#94a3b8;">
        <div style="font-size:2.5rem;margin-bottom:.75rem;">🏗</div>
        <div style="font-size:.88rem;font-weight:600;margin-bottom:.4rem;color:#64748b;">Análisis de viabilidad</div>
        <div style="font-size:.75rem;line-height:1.6;">
            Ingresa los datos del terreno<br>
            (m², zonificación y precio)<br>
            para ver el análisis completo
        </div>
        <div style="margin-top:1rem;display:flex;flex-direction:column;gap:.3rem;font-size:.7rem;color:#94a3b8;text-align:left;background:#f8fafc;border-radius:8px;padding:.75rem 1rem;">
            <div>✓ Potencial constructivo (m² vendibles, deptos)</div>
            <div>✓ ROI y margen del constructor</div>
            <div>✓ Valor residual del terreno (Método de Desarrollo)</div>
            <div>✓ Indicadores de viabilidad COS/CUS</div>
            <div>✓ Semáforo verde / amarillo / rojo</div>
        </div>
    </div>
@endif

</div>
{{-- /columna derecha --}}

</div>
{{-- /layout flex --}}

</div>
