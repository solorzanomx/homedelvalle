{{--
  Valuación de Terreno para Constructor — Vista educativa/didáctica
  Perspectiva: director de adquisiciones de una casa constructora
  Métrica principal: Incidencia del Terreno (precio / m² vendibles)
--}}
<div>

@php
    $m2Float    = (float)($m2Terreno ?: 0);
    $totalFloat = (float)str_replace([',',' '], '', $precioTerreno ?: '0');
    $perM2Float = (float)str_replace([',',' '], '', $precioTerrenoM2 ?: '0');
    $derivedM2  = ($precioMode==='total'  && $m2Float>0 && $totalFloat>0) ? (int)round($totalFloat/$m2Float) : null;
    $derivedTot = ($precioMode==='per_m2' && $m2Float>0 && $perM2Float>0) ? (int)round($perM2Float*$m2Float) : null;

    $vc = match($result['verdict'] ?? '') {
        'compra_directa' => ['bg'=>'#052e16','border'=>'#16a34a','text'=>'#bbf7d0','icon'=>'✅','label'=>'COMPRA DIRECTA',
            'sub'=>'ROI > 22% — el constructor comprará sin negociar mucho'],
        'viable'         => ['bg'=>'#f0fdf4','border'=>'#86efac','text'=>'#166534','icon'=>'✅','label'=>'VIABLE',
            'sub'=>'ROI 17-22% — buen negocio, hay interés real de una constructora'],
        'negocia'        => ['bg'=>'#fefce8','border'=>'#fde047','text'=>'#713f12','icon'=>'⚠️','label'=>'NEGOCIA EL PRECIO',
            'sub'=>'ROI 8-17% — el precio actual deja poco margen, hay que bajar'],
        'descarta'       => ['bg'=>'#fef2f2','border'=>'#fca5a5','text'=>'#991b1b','icon'=>'🚫','label'=>'DESCARTA O ASOCIACIÓN',
            'sub'=>'ROI < 8% — inviable a este precio; evalúa el esquema de asociación'],
        default => [],
    };

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
.cv-sec{font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#64748b;
        margin-bottom:.6rem;display:flex;align-items:center;gap:.4rem}
.cv-n{width:22px;height:22px;border-radius:50%;background:#6366f1;display:inline-flex;align-items:center;
      justify-content:center;font-size:.7rem;font-weight:800;color:#fff;flex-shrink:0}
.cv-preset{font-size:.68rem;font-weight:600;padding:.2rem .5rem;border-radius:5px;cursor:pointer;
           border:1px solid var(--border);background:var(--card);color:#64748b;transition:all .12s;white-space:nowrap}
.cv-preset:hover{background:#eff6ff;border-color:#6366f1;color:#6366f1}
.cv-preset.on{background:#6366f1;border-color:#6366f1;color:#fff}
.edu-box{border-radius:8px;padding:.6rem .85rem;font-size:.72rem;line-height:1.6;cursor:pointer}
.edu-box.blue{background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af}
.edu-box.violet{background:#f5f3ff;border:1px solid #ddd6fe;color:#4c1d95}
.edu-box.amber{background:#fffbeb;border:1px solid #fde047;color:#713f12}
.edu-box.emerald{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534}
.edu-box.slate{background:#f8fafc;border:1px solid #e2e8f0;color:#475569}
.wf-row{display:flex;align-items:center;justify-content:space-between;padding:.3rem 0;
        border-bottom:1px solid #f1f5f9;font-size:.74rem}
.wf-row:last-child{border-bottom:none}
.kpi-chip{display:inline-flex;align-items:center;gap:.25rem;padding:.15rem .5rem;
          border-radius:12px;font-size:.65rem;font-weight:600;white-space:nowrap}
</style>

{{-- ══════════════════════════════════════════════════════════════════════
     BANNER INTRODUCTORIO
     ══════════════════════════════════════════════════════════════════════ --}}
<div style="background:linear-gradient(135deg,#1e1b4b 0%,#312e81 100%);border-radius:12px;
            padding:1rem 1.4rem;margin-bottom:1.1rem;display:flex;align-items:flex-start;gap:1rem;">
    <span style="font-size:1.8rem;flex-shrink:0;margin-top:.1rem;">🏗</span>
    <div>
        <div style="font-size:.88rem;font-weight:800;color:#fff;margin-bottom:.25rem;">
            ¿Vale la pena este terreno para una constructora?
        </div>
        <div style="font-size:.72rem;color:#c7d2fe;line-height:1.6;">
            Esta herramienta simula exactamente cómo evalúa la compra de un terreno un
            <strong style="color:#a5b4fc;">Director de Adquisiciones</strong> de una casa constructora.
            Ingresa la zona, la zonificación y el precio — y la calculadora te dice si el negocio
            funciona, qué precio debería ofrecer el constructor y cuánto ganaría.
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:.4rem;margin-top:.5rem;">
            @foreach(['Incidencia del terreno','Método Residual','ROI del constructor','Waterfall de costos','Esquema de asociación'] as $tag)
            <span style="background:rgba(99,102,241,.3);color:#c7d2fe;border-radius:12px;
                         padding:.1rem .55rem;font-size:.65rem;font-weight:600;">{{ $tag }}</span>
            @endforeach
        </div>
    </div>
</div>

<div style="display:flex;gap:1.25rem;align-items:flex-start;">

{{-- ════════════════════════════════════════
     Columna izquierda — Formulario
     ════════════════════════════════════════ --}}
<div style="flex:1;min-width:0;display:flex;flex-direction:column;gap:.9rem;">

{{-- ─────────────────────────────────────────────────────────────────────
     SECCIÓN 1 — Datos del terreno
     ───────────────────────────────────────────────────────────────────── --}}
<div class="card">
<div class="card-body" style="padding:1rem 1.25rem;">

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.75rem;">
        <div style="display:flex;align-items:center;gap:.5rem;">
            <span class="cv-n">1</span>
            <span style="font-size:.82rem;font-weight:700;color:var(--text);">Datos del terreno</span>
        </div>
    </div>

    {{-- Info educativa sección 1 --}}
    <div class="edu-box blue" x-data="{open:true}" style="margin-bottom:.9rem;">
        <div @click="open=!open" style="display:flex;align-items:center;justify-content:space-between;">
            <span style="font-weight:700;">ℹ️ ¿Por qué importa la colonia y los m²?</span>
            <span x-text="open?'▲':'▼'" style="font-size:.65rem;color:#3b82f6;"></span>
        </div>
        <div x-show="open" x-transition style="margin-top:.45rem;">
            <p style="margin:0 0 .4rem;">
                La <strong>colonia</strong> determina el techo de ingresos del proyecto: a mayor demanda,
                mayor precio de venta de los departamentos, y por ende el terreno puede valer más.
                El <strong>tamaño del terreno</strong> define el volumen del negocio: más m² = más departamentos
                = más ventas, pero también más inversión requerida.
            </p>
            <p style="margin:0;font-size:.68rem;opacity:.85;">
                💡 Un constructor en Benito Juárez generalmente busca terrenos de <strong>150–600 m²</strong>
                para proyectos de 8–40 departamentos. Menos de 150 m² es difícil de justificar la operación.
            </p>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.8rem;">

        <div style="grid-column:1/-1;">
            <label class="cv-label">Colonia <span style="font-weight:400;color:#94a3b8;">— determina el precio de venta de los departamentos</span></label>
            <select wire:model.live="coloniaId" class="cv-input">
                <option value="">— Sin colonia / ingresaré el precio manualmente —</option>
                @foreach($colonias as $zoneName => $cols)
                <optgroup label="{{ $zoneName }}">
                    @foreach($cols as $col)
                    <option value="{{ $col->id }}">{{ $col->name }}</option>
                    @endforeach
                </optgroup>
                @endforeach
            </select>
            @if(!$coloniaId)
            <span class="cv-hint">Sin colonia no hay precio de mercado — tendrás que ingresarlo manualmente en la sección 3</span>
            @endif
        </div>

        <div>
            <label class="cv-label">m² del terreno <span style="color:var(--danger)">*</span></label>
            <input wire:model.blur="m2Terreno" wire:change="recalculate"
                   type="number" min="10" step="1" placeholder="200" class="cv-input">
            @if($frente && $fondo)
            <span class="cv-hint" style="color:#059669;">✓ {{ number_format((float)$frente*(float)$fondo,0) }} m² según frente × fondo</span>
            @elseif($m2Terreno && (float)$m2Terreno >= 10)
            <span class="cv-hint">
                @if((float)$m2Terreno < 150) ⚠ Lote muy pequeño — difícil de justificar la operación para un constructor
                @elseif((float)$m2Terreno < 200) ⚠ Revisar Norma 10 CDMX (puede dar más densidad)
                @elseif((float)$m2Terreno <= 600) ✓ Tamaño típico para proyectos de constructora en BJ
                @else ✓ Lote grande — ideal para desarrollo mayor o construcción por fases
                @endif
            </span>
            @endif
        </div>

        <div>
            <label class="cv-label">Frente × Fondo (m) <span style="font-weight:400;color:#94a3b8;">opcional</span></label>
            <div style="display:flex;gap:.4rem;align-items:center;">
                <input wire:model="frente" type="number" min="0" step="0.5" placeholder="10" class="cv-input" style="flex:1;">
                <span style="color:#94a3b8;font-weight:700;">×</span>
                <input wire:model="fondo"  type="number" min="0" step="0.5" placeholder="20" class="cv-input" style="flex:1;">
            </div>
            @if($frente && (float)$frente < 6)
            <span class="cv-hint" style="color:#dc2626;">⚠ Frente < 6 m — casi todos los reglamentos exigen frente mínimo de 6 m</span>
            @elseif($frente && $fondo)
            <span class="cv-hint">Frente {{ $frente }} m × Fondo {{ $fondo }} m = {{ number_format((float)$frente*(float)$fondo,0) }} m²</span>
            @endif
        </div>

    </div>
</div>
</div>

{{-- ─────────────────────────────────────────────────────────────────────
     SECCIÓN 2 — Zonificación
     ───────────────────────────────────────────────────────────────────── --}}
<div class="card">
<div class="card-body" style="padding:1rem 1.25rem;">

    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.75rem;">
        <span class="cv-n">2</span>
        <span style="font-size:.82rem;font-weight:700;color:var(--text);">Zonificación urbana · PDDU CDMX</span>
    </div>

    {{-- Info educativa sección 2 --}}
    <div class="edu-box violet" x-data="{open:true}" style="margin-bottom:.9rem;">
        <div @click="open=!open" style="display:flex;align-items:center;justify-content:space-between;">
            <span style="font-weight:700;">📐 ¿Qué son el COS y el CUS? (lo más importante)</span>
            <span x-text="open?'▲':'▼'" style="font-size:.65rem;color:#7c3aed;"></span>
        </div>
        <div x-show="open" x-transition style="margin-top:.45rem;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-bottom:.5rem;">
                <div style="background:#ede9fe;border-radius:6px;padding:.45rem .6rem;">
                    <div style="font-weight:700;font-size:.75rem;color:#5b21b6;margin-bottom:.2rem;">COS — Huella máxima</div>
                    <div style="font-size:.68rem;">% del terreno que puede tener <strong>construcción en planta baja</strong>.
                    COS 0.60 = sólo 60% puede tener techo.</div>
                    @if($cos && $m2Terreno && (float)$m2Terreno>0)
                    <div style="margin-top:.3rem;font-weight:700;color:#5b21b6;font-size:.72rem;">
                        {{ (float)$cos*100 }}% × {{ $m2Terreno }} m² = <strong>{{ number_format((float)$cos*(float)$m2Terreno,0) }} m² de huella</strong>
                    </div>
                    @endif
                </div>
                <div style="background:#ede9fe;border-radius:6px;padding:.45rem .6rem;">
                    <div style="font-weight:700;font-size:.75rem;color:#5b21b6;margin-bottom:.2rem;">CUS — Potencial total</div>
                    <div style="font-size:.68rem;"><strong>Total de m² construibles</strong> dividido entre el terreno.
                    CUS 3.6 = puedes construir 3.6 veces el área del terreno.</div>
                    @if($cus && $m2Terreno && (float)$m2Terreno>0)
                    <div style="margin-top:.3rem;font-weight:700;color:#5b21b6;font-size:.72rem;">
                        {{ $cus }} × {{ $m2Terreno }} m² = <strong>{{ number_format((float)$cus*(float)$m2Terreno,0) }} m² brutos</strong>
                    </div>
                    @endif
                </div>
            </div>
            @if($cos && $cus && $m2Terreno && (float)$m2Terreno>0)
            @php
                $huellaCalc   = round((float)$cos * (float)$m2Terreno, 0);
                $brutosCalc   = round((float)$cus * (float)$m2Terreno, 0);
                $vendiblesCalc= round($brutosCalc * 0.80, 0);
                $pisosCalc    = (float)$cos > 0 ? round((float)$cus / (float)$cos, 1) : 0;
            @endphp
            <div style="background:#fff;border:1px solid #ddd6fe;border-radius:6px;padding:.45rem .7rem;font-size:.7rem;">
                🔢 <strong>Lo que da este terreno:</strong>
                {{ number_format($huellaCalc, 0) }} m² de huella por piso
                × {{ $pisosCalc }} pisos
                = <strong>{{ number_format($brutosCalc, 0) }} m² brutos</strong>
                → con 80% de eficiencia vendible = <strong style="color:#6d28d9;">{{ number_format($vendiblesCalc, 0) }} m² vendibles</strong>
                ≈ <strong>{{ max(1, (int)floor($vendiblesCalc / (float)max(1,$tamanoDepto))) }} departamentos</strong> de {{ $tamanoDepto }} m²
            </div>
            @else
            <div style="font-size:.68rem;opacity:.8;">💡 Ingresa los m² del terreno arriba para ver los cálculos en tiempo real.</div>
            @endif
        </div>
    </div>

    {{-- Clave SEDUVI --}}
    <div style="margin-bottom:.75rem;">
        <label class="cv-label">
            Clave SEDUVI del certificado de uso de suelo
            <span style="font-weight:400;color:#94a3b8;font-size:.72rem;">· auto-calcula COS, CUS y niveles</span>
        </label>
        <input wire:model.live.debounce.600ms="zonificacionLabel" type="text"
               placeholder="ej. HM 6/30  ·  H4/Z/20  ·  HC4/30  ·  CB5/30"
               class="cv-input"
               style="font-family:monospace;font-size:.95rem;font-weight:700;letter-spacing:.5px;
                      {{ $parsedZone ? 'border-color:#059669;' : '' }}">

        @if($parsedZone)
        <div style="margin-top:.3rem;background:#f0fdf4;border:1px solid #86efac;border-radius:6px;padding:.4rem .75rem;">
            <div style="display:flex;gap:.6rem;flex-wrap:wrap;font-size:.72rem;color:#166534;margin-bottom:{{ ($parsedZone['area_libre']??null) ? '.2rem' : '0' }};">
                <strong>Uso {{ $parsedZone['uso'] }}</strong>
                <span>{{ $parsedZone['pisos'] }} pisos</span>
                <span>COS <strong>{{ $parsedZone['cos'] }}</strong></span>
                <span>CUS <strong>{{ $parsedZone['cus'] }}</strong></span>
                @if($parsedZone['zona_var']??null)<span>Zona {{ $parsedZone['zona_var'] }}</span>@endif
                @if($parsedZone['lote_min']??null)<span>Lote mín. {{ $parsedZone['lote_min'] }} m²</span>@endif
            </div>
            @if($parsedZone['area_libre']??null)
            <div style="font-size:.65rem;color:#166534;opacity:.8;">
                /{{ $parsedZone['zona_var'] }}/{{ $parsedZone['area_libre'] }} significa {{ $parsedZone['area_libre'] }}% del terreno debe quedar libre →
                el {{ 100-$parsedZone['area_libre'] }}% restante puede construirse → COS = {{ $parsedZone['cos'] }}
            </div>
            @endif
        </div>
        @elseif($zonificacionLabel)
        <span class="cv-hint" style="color:#d97706;">⚠ Código no reconocido en la base de claves — ajusta COS/CUS/pisos manualmente abajo</span>
        @else
        <span class="cv-hint">Escribe la clave tal como aparece en el certificado SEDUVI, o usa un preset rápido:</span>
        @endif
    </div>

    {{-- Presets rápidos --}}
    <div style="margin-bottom:.75rem;">
        <div style="font-size:.68rem;color:#94a3b8;margin-bottom:.35rem;font-weight:600;">Presets comunes en Benito Juárez:</div>
        <div style="display:flex;flex-wrap:wrap;gap:.3rem;">
            @foreach([
                'H3_30'=>['H 3/30','3p COS.60'],'H4_30'=>['H 4/30','4p COS.60'],
                'HM4_30'=>['HM 4/30','4p mix'],'HM5_30'=>['HM 5/30','5p mix'],
                'HM6_30'=>['HM 6/30','6p mix ★'],'HM8_30'=>['HM 8/30','8p mix'],
                'HC4_30'=>['HC 4/30','4p+comerc'],'CB5_30'=>['CB 5/30','CB 100%'],
                'N10'=>['Norma 10','lote<200'],
            ] as $key => [$short, $desc])
            @php $act = $zonificacionLabel===$short; $pr = $presets[$key]??null; @endphp
            <button wire:click="applyPreset('{{ $key }}')" class="cv-preset {{ $act?'on':'' }}"
                    title="COS {{ $pr['cos']??'' }} · CUS {{ $pr['cus']??'' }} — {{ $desc }}">
                {{ $short }}
                <span style="font-size:.58rem;opacity:{{ $act?1:.6 }};"> {{ $desc }}</span>
            </button>
            @endforeach
        </div>
    </div>

    {{-- COS / CUS / Pisos --}}
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;">
        <div>
            <label class="cv-label">COS</label>
            <input wire:model.live.debounce.400ms="cos" type="number" min="0.01" max="1" step="0.01" placeholder="0.60" class="cv-input">
            <span class="cv-hint">
                @if($cos) {{ (float)$cos*100 }}% del terreno → huella
                @else % de cobertura en planta baja @endif
            </span>
        </div>
        <div>
            <label class="cv-label">CUS</label>
            <input wire:model.live.debounce.400ms="cus" type="number" min="0.1" max="30" step="0.1" placeholder="3.60" class="cv-input">
            <span class="cv-hint">
                @if($cus && $m2Terreno) {{ number_format((float)$cus*(float)$m2Terreno,0) }} m² brutos posibles
                @else m² totales / m² terreno @endif
            </span>
        </div>
        <div>
            <label class="cv-label">Niveles</label>
            <input wire:model.live.debounce.400ms="pisos" type="number" min="1" max="40" step="1" placeholder="6" class="cv-input">
            <span class="cv-hint">
                @if($cos && $cus && (float)$cos>0) CUS/COS = {{ round((float)$cus/(float)$cos,1) }} pisos teóricos @else pisos máximos @endif
            </span>
        </div>
    </div>

</div>
</div>

{{-- ─────────────────────────────────────────────────────────────────────
     SECCIÓN 3 — Precios (terreno + venta)
     ───────────────────────────────────────────────────────────────────── --}}
<div class="card">
<div class="card-body" style="padding:1rem 1.25rem;">

    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.75rem;">
        <span class="cv-n">3</span>
        <span style="font-size:.82rem;font-weight:700;color:var(--text);">Los dos precios que definen el negocio</span>
    </div>

    {{-- Info educativa sección 3 --}}
    <div class="edu-box amber" x-data="{open:true}" style="margin-bottom:.9rem;">
        <div @click="open=!open" style="display:flex;align-items:center;justify-content:space-between;">
            <span style="font-weight:700;">💡 ¿Cómo piensa el constructor al evaluar el precio?</span>
            <span x-text="open?'▲':'▼'" style="font-size:.65rem;color:#d97706;"></span>
        </div>
        <div x-show="open" x-transition style="margin-top:.45rem;">
            <p style="margin:0 0 .4rem;">
                El constructor compara dos precios: <strong>(A) ¿cuánto cuesta el terreno?</strong>
                y <strong>(B) ¿a cuánto puede vender los departamentos que construya?</strong>
                La diferencia entre B y A tiene que alcanzar para construir, pagar permisos,
                financiamiento de 2–3 años y obtener una ganancia mínima del <strong>18–22%</strong>.
            </p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.4rem;font-size:.68rem;">
                <div style="background:#fef3c7;border-radius:5px;padding:.3rem .5rem;">
                    <strong>❌ Si el terreno es caro:</strong><br>
                    No hay margen suficiente. El constructor ofrecerá menos o no comprará.
                </div>
                <div style="background:#d1fae5;border-radius:5px;padding:.3rem .5rem;">
                    <strong>✅ Si el terreno tiene buen precio:</strong><br>
                    El constructor puede ganar > 22% ROI. Cierre rápido, pocas negociaciones.
                </div>
            </div>
        </div>
    </div>

    {{-- Precio del terreno --}}
    <div style="font-size:.75rem;font-weight:700;color:#0f172a;margin-bottom:.5rem;
                display:flex;align-items:center;gap:.4rem;">
        <span style="width:18px;height:18px;background:#fef08a;border-radius:4px;display:inline-flex;
                     align-items:center;justify-content:center;font-size:.65rem;">A</span>
        Precio del terreno (lo que pide el propietario)
    </div>

    <div style="display:inline-flex;border:1px solid var(--border);border-radius:8px;overflow:hidden;margin-bottom:.65rem;">
        <button wire:click="$set('precioMode','total')"
                style="padding:.3rem .75rem;font-size:.76rem;font-weight:600;border:none;cursor:pointer;
                       background:{{ $precioMode==='total'?'#6366f1':'transparent' }};
                       color:{{      $precioMode==='total'?'#fff':'#64748b' }};">
            Precio total
        </button>
        <button wire:click="$set('precioMode','per_m2')"
                style="padding:.3rem .75rem;font-size:.76rem;font-weight:600;border:none;cursor:pointer;
                       background:{{ $precioMode==='per_m2'?'#6366f1':'transparent' }};
                       color:{{      $precioMode==='per_m2'?'#fff':'#64748b' }};">
            Por m² de terreno
        </button>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.8rem;margin-bottom:.85rem;">
        @if($precioMode==='total')
        <div>
            <label class="cv-label">Precio total pedido (MXN) <span style="color:var(--danger)">*</span></label>
            <input wire:model.blur="precioTerreno" wire:change="recalculate"
                   x-init="let v=$el.value.replace(/[^0-9]/g,'');$el.value=v?Number(v).toLocaleString('en-US'):'';"
                   @input="let v=$event.target.value.replace(/[^0-9]/g,'');let f=v?Number(v).toLocaleString('en-US'):'';let diff=f.length-$event.target.value.length;let pos=Math.max(0,$event.target.selectionEnd+diff);$event.target.value=f;$event.target.setSelectionRange(pos,pos);"
                   type="text" inputmode="numeric" placeholder="4,500,000" class="cv-input">
            @if($derivedM2)<span class="cv-hint" style="color:#059669;">= ${{ number_format($derivedM2) }}/m² de terreno</span>
            @else<span class="cv-hint">ej: 4,500,000</span>@endif
        </div>
        <div>
            <label class="cv-label">$/m² de terreno</label>
            <div class="cv-input cv-ro">{{ $derivedM2 ? '$'.number_format($derivedM2).'/m²' : '— ingresa los m² arriba' }}</div>
            <span class="cv-hint">Se calcula automáticamente</span>
        </div>
        @else
        <div>
            <label class="cv-label">Precio por m² de terreno <span style="color:var(--danger)">*</span></label>
            <input wire:model.blur="precioTerrenoM2" wire:change="recalculate"
                   x-init="let v=$el.value.replace(/[^0-9]/g,'');$el.value=v?Number(v).toLocaleString('en-US'):'';"
                   @input="let v=$event.target.value.replace(/[^0-9]/g,'');let f=v?Number(v).toLocaleString('en-US'):'';let diff=f.length-$event.target.value.length;let pos=Math.max(0,$event.target.selectionEnd+diff);$event.target.value=f;$event.target.setSelectionRange(pos,pos);"
                   type="text" inputmode="numeric" placeholder="22,500" class="cv-input">
            @if($derivedTot)<span class="cv-hint" style="color:#059669;">= ${{ number_format($derivedTot) }} total</span>
            @else<span class="cv-hint">ej: 22,500</span>@endif
        </div>
        <div>
            <label class="cv-label">Total calculado</label>
            <div class="cv-input cv-ro">{{ $derivedTot ? '$'.number_format($derivedTot) : '— ingresa m² del terreno arriba' }}</div>
        </div>
        @endif
    </div>

    <div style="border-top:1.5px dashed #e2e8f0;margin:.1rem 0 .85rem;"></div>

    {{-- Precio de venta (B) --}}
    <div style="font-size:.75rem;font-weight:700;color:#0f172a;margin-bottom:.5rem;
                display:flex;align-items:center;gap:.4rem;">
        <span style="width:18px;height:18px;background:#bbf7d0;border-radius:4px;display:inline-flex;
                     align-items:center;justify-content:center;font-size:.65rem;">B</span>
        Precio de venta de los departamentos nuevos que construirá el constructor
    </div>

    @if($observatorioPrice)
    <div class="edu-box blue" style="margin-bottom:.65rem;">
        <div style="font-weight:700;margin-bottom:.35rem;">
            📊 Observatorio HDV — precio promedio de anuncios nuevos (0–5 años) en la zona:
            <strong style="font-size:.85rem;">${{ number_format((int)$observatorioPrice) }}/m²</strong>
        </div>
        <p style="margin:0 0 .4rem;font-size:.68rem;">
            ⚠️ Este promedio <strong>incluye departamentos de 2–5 años</strong> que ya tuvieron cierta depreciación.
            Un <strong>desarrollo 100% nuevo</strong> de constructora — con acabados actuales, elevador, amenidades —
            cotiza <strong>15–30% arriba</strong> de este promedio. Elige el premium que corresponde a la zona:
        </p>
        <div style="display:flex;flex-wrap:wrap;gap:.3rem;">
            @foreach([
                '1.00'=>['+0% — Sin premium (zona de menor demanda)','#64748b'],
                '1.10'=>['+10%','#0369a1'],
                '1.15'=>['+15%','#0369a1'],
                '1.20'=>['+20% ★ Typical BJ','#6d28d9'],
                '1.25'=>['+25%','#b45309'],
                '1.30'=>['+30% (Polanco/Santa Fe level)','#b45309'],
            ] as $mult => [$tip, $clr])
            @php $active = $precioMultiplier === $mult; $px = (int)round($observatorioPrice*(float)$mult); @endphp
            <button wire:click="applyPremium('{{ $mult }}')"
                    title="{{ $tip }}"
                    style="font-size:.68rem;font-weight:600;padding:.22rem .55rem;border-radius:5px;cursor:pointer;border:1px solid;
                           background:{{ $active?'#6366f1':'#fff' }};
                           border-color:{{ $active?'#6366f1':'#bfdbfe' }};
                           color:{{ $active?'#fff':$clr }};">
                {{ $mult==='1.00' ? 'Base' : '+'.((int)round(((float)$mult-1)*100)).'%' }}
                <span style="font-weight:400;font-size:.62rem;">&nbsp;${{ number_format($px) }}</span>
            </button>
            @endforeach
        </div>
    </div>
    @else
    <div class="edu-box amber" style="margin-bottom:.5rem;">
        Sin datos del Observatorio para esta zona — ingresa el precio de venta manualmente.
        Tip: busca proyectos similares en Inmuebles24 o Lamudi en esa colonia.
    </div>
    @endif

    <div style="display:flex;align-items:center;gap:.5rem;">
        <input wire:model.blur="precioVentaM2" wire:change="recalculate"
               x-init="let v=$el.value.replace(/[^0-9]/g,'');$el.value=v?Number(v).toLocaleString('en-US'):'';"
               @input="let v=$event.target.value.replace(/[^0-9]/g,'');let f=v?Number(v).toLocaleString('en-US'):'';let diff=f.length-$event.target.value.length;let pos=Math.max(0,$event.target.selectionEnd+diff);$event.target.value=f;$event.target.setSelectionRange(pos,pos);"
               type="text" inputmode="numeric"
               placeholder="{{ $coloniaId ? 'Se calcula automáticamente — o ingresa manualmente' : 'ej. 70,000' }}"
               class="cv-input" style="flex:1;">
        @if($precioVentaM2 && $observatorioPrice)
        @php $pvNum = (int)str_replace([',',' '], '', $precioVentaM2); $pctVsObs = round(($pvNum - $observatorioPrice) / $observatorioPrice * 100); @endphp
        <span class="kpi-chip" style="background:{{ $pvNum > $observatorioPrice ? '#f0fdf4' : '#fef2f2' }};
              color:{{ $pvNum > $observatorioPrice ? '#166534' : '#991b1b' }};
              border:1px solid {{ $pvNum > $observatorioPrice ? '#86efac' : '#fca5a5' }};">
            {{ $pvNum > $observatorioPrice ? '+' : '' }}{{ $pctVsObs }}% vs obs.
        </span>
        @endif
    </div>
    <span class="cv-hint">A este precio calcularemos cuánto puede ganar el constructor y cuánto puede pagar por el terreno</span>

</div>
</div>

{{-- ─────────────────────────────────────────────────────────────────────
     SECCIÓN 4 — Parámetros de construcción (colapsable)
     ───────────────────────────────────────────────────────────────────── --}}
<div class="card" x-data="{open:false}">
<div class="card-header" style="cursor:pointer;border-bottom:1px solid var(--border);" @click="open=!open">
    <div style="display:flex;align-items:center;gap:.5rem;width:100%;">
        <span class="cv-n">4</span>
        <span style="font-size:.82rem;font-weight:700;color:var(--text);">Parámetros de construcción</span>
        <span style="font-size:.7rem;font-weight:400;color:#94a3b8;margin-left:.2rem;">
            · ${{ number_format((int)$costoConstruccion) }}/m² · {{ $eficiencia }}% vendible · {{ $tamanoDepto }} m²/depto
        </span>
        <span x-text="open?'▲':'▼'" style="margin-left:auto;font-size:.65rem;color:#94a3b8;"></span>
    </div>
</div>
<div x-show="open" x-transition style="display:none;">
<div class="card-body" style="padding:1rem 1.25rem;">

    {{-- Info educativa sección 4 --}}
    <div class="edu-box slate" x-data="{open:true}" style="margin-bottom:.85rem;">
        <div @click="open=!open" style="display:flex;align-items:center;justify-content:space-between;">
            <span style="font-weight:700;">🔩 ¿Qué son estos parámetros y cómo afectan el análisis?</span>
            <span x-text="open?'▲':'▼'" style="font-size:.65rem;"></span>
        </div>
        <div x-show="open" x-transition style="margin-top:.45rem;font-size:.69rem;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.4rem;">
                <div><strong>Costo de construcción ($/m² bruto):</strong> Lo que cuesta construir cada metro cuadrado bruto. En CDMX 2025, departamentos medios costan ~$18k–$22k/m². Lujo puede llegar a $30k+.</div>
                <div><strong>Factor vendible (%):</strong> No todo lo que se construye se puede vender. Escaleras, pasillos, cuarto de máquinas y lobby restan ~15–22% del total. El 80% es estándar en BJ.</div>
                <div><strong>Tamaño promedio por depto (m²):</strong> Afecta el número estimado de unidades. En BJ, la tendencia es 50–75m² para optimizar ingresos totales.</div>
                <div><strong>Estructura de costos aplicada:</strong> Construcción + 10% proyecto + 5% permisos CDMX + 4% ventas + 10% financiamiento obra + 20% financiamiento terreno.</div>
            </div>
            <div style="margin-top:.4rem;background:#f1f5f9;border-radius:5px;padding:.3rem .5rem;font-size:.65rem;color:#475569;">
                💡 Los permisos en CDMX pueden tardar <strong>6–24 meses</strong> — ese tiempo tiene costo financiero.
                Es por eso que el financiamiento del terreno (20%) es más alto que lo que parece.
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.8rem;">
        <div>
            <label class="cv-label">Costo construcción ($/m² bruto)</label>
            <input wire:model.blur="costoConstruccion" wire:change="recalculate"
                   type="number" min="10000" max="80000" step="500" placeholder="18000" class="cv-input">
            <span class="cv-hint">CEICO-CMIC CDMX 2025: media $18k · semilujо $22k · lujo $28k+</span>
        </div>
        <div>
            <label class="cv-label">Factor vendible — {{ $eficiencia }}%</label>
            <input wire:model.live="eficiencia" type="range" min="60" max="90" step="1"
                   style="width:100%;accent-color:#6366f1;margin-top:.4rem;">
            <span class="cv-hint">m² vendibles / m² brutos — estándar BJ: 78–83%</span>
        </div>
        <div>
            <label class="cv-label">Tamaño promedio de depto (m²)</label>
            <input wire:model.live.debounce.400ms="tamanoDepto" type="number" min="30" max="250" step="5" placeholder="65" class="cv-input">
            <span class="cv-hint">BJ trend 2025: 50–70 m² para maximizar # de unidades</span>
        </div>
        <div style="background:#f8fafc;border-radius:8px;padding:.6rem .75rem;font-size:.68rem;color:#475569;">
            <div style="font-weight:700;color:#0f172a;margin-bottom:.35rem;">Costos adicionales aplicados automáticamente:</div>
            <div style="display:flex;flex-direction:column;gap:.15rem;">
                <span>🔸 Proyecto + supervisión: <strong>10%</strong> de construcción</span>
                <span>🔸 Permisos y licencias CDMX: <strong>5%</strong> de construcción</span>
                <span>🔸 Comercialización (ventas): <strong>4%</strong> de ventas</span>
                <span>🔸 Financiamiento obra: <strong>10%</strong> de construcción</span>
                <span>🔸 Financiamiento terreno: <strong>20%</strong> del precio</span>
                <span>🔸 Utilidad objetivo: <strong>22%</strong> de ventas</span>
            </div>
        </div>
    </div>
</div>
</div>
</div>

</div>{{-- /columna izquierda --}}


{{-- ════════════════════════════════════════
     Columna derecha — Panel de resultados
     ════════════════════════════════════════ --}}
<div style="width:365px;flex-shrink:0;">

@if($result && $result['available'])
@php $r = $result; @endphp

{{-- ╔══════════════════════════════════╗
     ║  VEREDICTO                       ║
     ╚══════════════════════════════════╝ --}}
<div style="background:{{ $vc['bg'] }};border:1.5px solid {{ $vc['border'] }};border-radius:12px;
            padding:.85rem 1.1rem;margin-bottom:.85rem;">
    <div style="display:flex;align-items:center;gap:.7rem;margin-bottom:.5rem;">
        <span style="font-size:1.4rem;flex-shrink:0;">{{ $vc['icon'] }}</span>
        <div style="flex:1;">
            <div style="font-size:.78rem;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:{{ $vc['text'] }};">
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

    @if($r['precio_oferta'] > 0)
    <div style="border-top:1px solid {{ $vc['border'] }};padding-top:.6rem;">
        @if($r['brecha_pct'] <= 0)
        <div style="font-size:.7rem;color:{{ $vc['text'] }};font-weight:700;">✓ El precio pedido está dentro del rango viable</div>
        <div style="font-size:.68rem;color:{{ $vc['text'] }};opacity:.8;margin-top:.15rem;">
            Puedes ofrecerle al constructor a <strong>${{ number_format($r['precio_oferta']) }}</strong>
            (${{ number_format($r['precio_oferta_m2']) }}/m²) — tiene 12% de margen de contingencias sobre el máximo teórico.
        </div>
        @else
        <div style="font-size:.68rem;color:{{ $vc['text'] }};font-weight:700;">Precio de oferta que debe hacer el constructor:</div>
        <div style="font-size:1.15rem;font-weight:900;color:{{ $vc['text'] }};">
            ${{ number_format($r['precio_oferta']) }}
            <span style="font-size:.7rem;font-weight:400;opacity:.8;">(${{ number_format($r['precio_oferta_m2']) }}/m²)</span>
        </div>
        <div style="font-size:.68rem;color:{{ $vc['text'] }};opacity:.8;margin-top:.15rem;">
            El propietario pide <strong>${{ number_format($r['brecha_oferta']) }}</strong> más de lo que el constructor puede pagar
            para cumplir su ROI mínimo ({{ $r['brecha_pct'] }}% sobre lo viable)
        </div>
        @endif
    </div>
    @endif
</div>

{{-- ╔══════════════════════════════════╗
     ║  NARRATIVA — EL PANORAMA        ║
     ╚══════════════════════════════════╝ --}}
<div class="edu-box slate" style="margin-bottom:.85rem;font-size:.72rem;">
    <div style="font-weight:700;color:#0f172a;margin-bottom:.4rem;">📋 El panorama de este terreno</div>
    <p style="margin:0 0 .3rem;">
        Con <strong>{{ number_format($r['m2_vendibles'],0) }} m² vendibles</strong> y precio de venta a
        <strong>${{ number_format($r['precio_venta_m2']) }}/m²</strong>, el proyecto generaría
        <strong>${{ number_format($r['ventas']) }}</strong> en ventas totales.
    </p>
    <p style="margin:0 0 .3rem;">
        Después de pagar construcción, permisos, comercialización y financiamiento
        (total <strong>${{ number_format($r['costos_sin_terreno']) }}</strong> sin contar el terreno),
        al constructor le quedan <strong>${{ number_format($r['ventas'] - $r['costos_sin_terreno']) }}</strong>
        para pagar el terreno y tener ganancia.
    </p>
    @php $precioNum = $r['precioTerreno'] ?? ((float)str_replace([',',' '],'', $precioTerreno)); @endphp
    <p style="margin:0;">
        El propietario pide {{ $precioMode==='per_m2' && $derivedTot ? '$'.number_format($derivedTot) : '$'.number_format((int)str_replace([',',' '],'',$precioTerreno)) }},
        lo que dejará al constructor una utilidad de
        <strong style="color:{{ $r['utilidad_neta']>0?'#059669':'#dc2626' }};">
            {{ $r['utilidad_neta']>=0?'':'-' }}${{ number_format(abs($r['utilidad_neta'])) }}</strong>
        = ROI {{ $r['roi'] }}%
        @if($r['roi']>=22) — <span style="color:#059669;">✅ excelente retorno</span>
        @elseif($r['roi']>=17) — <span style="color:#d97706;">✅ retorno aceptable</span>
        @elseif($r['roi']>=8)  — <span style="color:#dc2626;">⚠ retorno insuficiente para cubrir el riesgo</span>
        @else — <span style="color:#dc2626;">🚫 proyecto con pérdidas al precio pedido</span>
        @endif
    </p>
</div>

{{-- ╔══════════════════════════════════╗
     ║  ★ INCIDENCIA DEL TERRENO       ║
     ╚══════════════════════════════════╝ --}}
<div class="card" style="margin-bottom:.85rem;border-left:4px solid {{ $incColor($r['incidencia_pct']) }};">
<div class="card-body" style="padding:.85rem 1.1rem;">
    <div style="font-size:.7rem;font-weight:800;color:#0f172a;margin-bottom:.55rem;display:flex;align-items:center;gap:.4rem;">
        <span style="font-size:.85rem;">★</span>
        INCIDENCIA DEL TERRENO
        <span style="font-weight:400;color:#94a3b8;font-size:.62rem;">LA métrica que usa el developer</span>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-bottom:.55rem;">
        <div style="text-align:center;background:#f8fafc;border-radius:8px;padding:.55rem;">
            <div style="font-size:.57rem;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;">$/m² vendible</div>
            <div style="font-size:1.4rem;font-weight:900;color:{{ $incColor($r['incidencia_pct']) }};line-height:1.1;">
                ${{ number_format($r['incidencia_m2']) }}
            </div>
            <div style="font-size:.6rem;color:#94a3b8;">precio terreno ÷ m² vendibles</div>
        </div>
        <div style="text-align:center;background:#f8fafc;border-radius:8px;padding:.55rem;">
            <div style="font-size:.57rem;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;">% del precio venta/m²</div>
            <div style="font-size:1.4rem;font-weight:900;color:{{ $incColor($r['incidencia_pct']) }};line-height:1.1;">
                {{ $r['incidencia_pct'] }}%
            </div>
            <div style="font-size:.6rem;color:#94a3b8;">incidencia / precio venta</div>
        </div>
    </div>

    <div style="height:8px;background:#e2e8f0;border-radius:4px;margin-bottom:.35rem;position:relative;">
        <div style="height:100%;border-radius:4px;background:{{ $incColor($r['incidencia_pct']) }};
                    width:{{ min(100, $r['incidencia_pct'] / 30 * 100) }}%;transition:width .3s;"></div>
        <div style="position:absolute;top:10px;left:40%;font-size:.55rem;color:#059669;">12%</div>
        <div style="position:absolute;top:10px;left:60%;font-size:.55rem;color:#d97706;">18%</div>
        <div style="position:absolute;top:10px;left:83.3%;font-size:.55rem;color:#dc2626;">25%</div>
    </div>
    <div style="display:flex;justify-content:space-between;font-size:.58rem;color:#94a3b8;margin-bottom:.5rem;">
        <span>Excelente<br><span style="color:#059669;">< 12%</span></span>
        <span style="text-align:center;">Aceptable<br><span style="color:#d97706;">12-18%</span></span>
        <span style="text-align:right;">Caro · Inviable<br><span style="color:#dc2626;">18–25% · >25%</span></span>
    </div>

    <div style="font-size:.68rem;color:#64748b;background:#f1f5f9;border-radius:6px;padding:.4rem .65rem;line-height:1.5;">
        De cada ${{ number_format($r['precio_venta_m2']) }} que el constructor cobra por m²,
        <strong style="color:{{ $incColor($r['incidencia_pct']) }};">${{ number_format($r['incidencia_m2']) }} ({{ $r['incidencia_pct'] }}%)</strong>
        van sólo a pagar el terreno —
        @if($r['incidencia_pct'] <= 12) el restante {{ 100-$r['incidencia_pct'] }}% cubre construcción, costos y ganancia. Muy competitivo.
        @elseif($r['incidencia_pct'] <= 18) quedan {{ round(100-$r['incidencia_pct']) }}% para todo lo demás. Ajustado pero funciona.
        @elseif($r['incidencia_pct'] <= 25) sólo quedan {{ round(100-$r['incidencia_pct']) }}% para construir y ganar. El margen es muy estrecho.
        @else quedan apenas {{ round(100-$r['incidencia_pct']) }}% para todo — prácticamente inviable.
        @endif
    </div>
</div>
</div>

{{-- ╔══════════════════════════════════╗
     ║  POTENCIAL CONSTRUCTIVO          ║
     ╚══════════════════════════════════╝ --}}
<div class="card" style="margin-bottom:.85rem;border-top:3px solid #6366f1;">
<div class="card-body" style="padding:.85rem 1.1rem;">
    <div style="font-size:.7rem;font-weight:700;color:#0f172a;margin-bottom:.55rem;">📐 Potencial constructivo</div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.35rem;margin-bottom:.55rem;">
        @foreach([
            ['m² vendibles', number_format($r['m2_vendibles'],0), '#6366f1', 'm²v = brutos × '.($eficiencia/100).'%'],
            ['~Deptos', $r['deptos_estimados'], '#0f172a', 'm²v ÷ '.$tamanoDepto.'m²/dpto'],
            ['m² brutos', number_format($r['m2_brutos'],0), '#64748b', 'terreno × CUS '.$cus],
            ['Pisos', $pisos, '#64748b', 'según CUS/COS'],
        ] as [$lbl, $val, $clr, $formula])
        <div style="text-align:center;background:#f8fafc;border-radius:6px;padding:.4rem .2rem;" title="{{ $formula }}">
            <div style="font-size:.55rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.4px;margin-bottom:.1rem;">{{ $lbl }}</div>
            <div style="font-size:.88rem;font-weight:800;color:{{ $clr }};">{{ $val }}</div>
            <div style="font-size:.55rem;color:#c4ccd4;margin-top:.1rem;">{{ $formula }}</div>
        </div>
        @endforeach
    </div>
</div>
</div>

{{-- ╔══════════════════════════════════╗
     ║  WATERFALL FINANCIERO            ║
     ╚══════════════════════════════════╝ --}}
<div class="card" style="margin-bottom:.85rem;">
<div class="card-body" style="padding:.85rem 1.1rem;">
    <div style="font-size:.7rem;font-weight:700;color:#0f172a;margin-bottom:.1rem;">💰 Desglose financiero completo</div>
    <div style="font-size:.65rem;color:#94a3b8;margin-bottom:.55rem;">
        (así ve los números el constructor antes de tomar la decisión)
    </div>

    <div class="wf-row" style="color:#059669;font-weight:700;font-size:.78rem;border-bottom:2px solid #e2e8f0!important;">
        <span>Valor de venta total <span style="font-weight:400;font-size:.68rem;color:#94a3b8;">{{ number_format($r['m2_vendibles'],0) }} m² × ${{ number_format($r['precio_venta_m2']) }}</span></span>
        <span>${{ number_format($r['ventas']) }}</span>
    </div>
    @foreach([
        ['ef4444', '− Construcción directa', number_format($r['construccion_direct']), number_format((float)$m2Terreno * (float)$cus, 0).'m² × $'.$costoConstruccion],
        ['f97316', '− Proyecto + supervisión (10%)', number_format($r['indirectos_tecnicos']), '10% de construcción'],
        ['f97316', '− Permisos CDMX (5%)', number_format($r['permisos_licencias']), 'licencias, dictámenes, derechos'],
        ['f97316', '− Comercialización (4% ventas)', number_format($r['comercializacion']), 'ventas, marketing, comisiones'],
        ['f97316', '− Financiamiento obra (10%)', number_format($r['financiero_obra']), 'crédito constructor ~13%×1.5 años'],
        ['dc2626', '− Financiamiento terreno (20%)', number_format($r['financiero_terreno']), 'crédito puente ~13%×2.5 años'],
        ['dc2626', '− Precio del terreno pedido', number_format((int)str_replace([',',' '],'', $precioTerreno ?? '0')), ''],
    ] as [$clr, $lbl, $val, $tip])
    <div class="wf-row" style="color:#{{ $clr }};" title="{{ $tip }}">
        <span>{{ $lbl }} @if($tip)<span style="font-size:.62rem;opacity:.6;"> · {{ $tip }}</span>@endif</span>
        <span>−${{ $val }}</span>
    </div>
    @endforeach
    <div class="wf-row" style="border-top:2px solid #e2e8f0!important;padding-top:.45rem;font-size:.82rem;font-weight:800;
         color:{{ $r['utilidad_neta']>=0?'#059669':'#dc2626' }};">
        <span>= Utilidad neta del constructor</span>
        <span>{{ $r['utilidad_neta']>=0?'':'−' }}${{ number_format(abs($r['utilidad_neta'])) }}</span>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.4rem;margin-top:.6rem;">
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:.5rem;text-align:center;">
            <div style="font-size:.57rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.4px;">ROI total</div>
            <div style="font-size:1.05rem;font-weight:900;color:{{ $roiColor($r['roi']) }};">{{ $r['roi'] }}%</div>
            <div style="font-size:.57rem;color:#94a3b8;">objetivo ≥ 18% para CDMX</div>
        </div>
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:.5rem;text-align:center;">
            <div style="font-size:.57rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.4px;">Margen / venta</div>
            <div style="font-size:1.05rem;font-weight:900;
                color:{{ $r['margen_ventas']>=15?'#059669':($r['margen_ventas']>=10?'#d97706':'#dc2626') }};">
                {{ $r['margen_ventas'] }}%
            </div>
            <div style="font-size:.57rem;color:#94a3b8;">objetivo ≥ 15%</div>
        </div>
    </div>

    {{-- Valor residual --}}
    <div style="margin-top:.65rem;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:.55rem .75rem;">
        <div style="font-size:.65rem;font-weight:700;color:#1e40af;margin-bottom:.15rem;">
            🏗 Método Residual — ¿cuánto puede pagar el constructor por el terreno para ROI 22%?
        </div>
        <div style="font-size:.68rem;color:#1e40af;margin-bottom:.2rem;">
            Ventas − todos los costos − utilidad objetivo = terreno máximo
        </div>
        <div style="display:flex;align-items:baseline;gap:.4rem;">
            <span style="font-size:1.05rem;font-weight:900;color:#1d4ed8;">
                ${{ number_format($r['valor_residual']) }}
            </span>
            <span style="font-size:.7rem;color:#3b82f6;">máximo teórico (${{ number_format($r['valor_residual_m2']) }}/m²)</span>
        </div>
        <div style="font-size:.65rem;color:#1e40af;margin-top:.2rem;">
            Precio de oferta sugerido (residual −12% contingencias):
            <strong>${{ number_format($r['precio_oferta']) }}</strong>
        </div>
    </div>
</div>
</div>

{{-- ╔══════════════════════════════════╗
     ║  TABLA DE ESCENARIOS             ║
     ╚══════════════════════════════════╝ --}}
@if($observatorioPrice && $r['m2_vendibles'] > 0)
@php
    // Cálculo de escenarios usando la misma estructura de costos fijos
    // Solo varía el precio de venta (y en consecuencia: ventas, comercialización, residual)
    $sc_m2v       = $r['m2_vendibles'];
    $sc_brutos    = $r['m2_brutos'];
    $sc_cc        = (float)$costoConstruccion;
    $sc_pterreno  = $precioMode === 'per_m2' && $derivedTot
                    ? (float)$derivedTot
                    : (float)str_replace([',',' '], '', $precioTerreno ?: '0');

    // Costos que NO cambian con el precio de venta
    $sc_construccion = $sc_brutos * $sc_cc;
    $sc_indirectos   = $sc_construccion * 0.10;
    $sc_permisos     = $sc_construccion * 0.05;
    $sc_fin_obra     = $sc_construccion * 0.10;
    $sc_fijos        = $sc_construccion + $sc_indirectos + $sc_permisos + $sc_fin_obra;

    $sc_mults = [
        '1.00' => ['label' => 'Base Obs.', 'color' => '#94a3b8'],
        '1.10' => ['label' => '+10%',      'color' => '#64748b'],
        '1.15' => ['label' => '+15%',      'color' => '#0369a1'],
        '1.20' => ['label' => '+20%',      'color' => '#6d28d9'],
        '1.25' => ['label' => '+25%',      'color' => '#b45309'],
        '1.30' => ['label' => '+30%',      'color' => '#b91c1c'],
    ];

    $sc_rows = [];
    foreach ($sc_mults as $mult_key => $meta) {
        $mult       = (float)$mult_key;
        $pvm2       = (int)round($observatorioPrice * $mult);
        $ventas     = $sc_m2v * $pvm2;
        $comerc     = $ventas * 0.04;
        $costos_st  = $sc_fijos + $comerc;
        $utilidad_obj = $ventas * 0.22;
        $residual   = ($ventas - $costos_st - $utilidad_obj) / 1.20;
        $oferta     = $residual * 0.88;
        $oferta_m2  = $residual > 0 && ($m2Float > 0) ? (int)round($oferta / $m2Float) : 0;

        // ROI al precio pedido por el propietario
        $costo_total_sc = $costos_st + $sc_pterreno + ($sc_pterreno * 0.20);
        $roi_sc = $costo_total_sc > 0 ? round(($ventas - $costo_total_sc) / $costo_total_sc * 100, 1) : -999;
        $inc_sc = ($pvm2 > 0 && $sc_m2v > 0) ? round($sc_pterreno / $sc_m2v / $pvm2 * 100, 1) : 0;

        $verdict_sc = $roi_sc >= 22 ? 'compra_directa' : ($roi_sc >= 17 ? 'viable' : ($roi_sc >= 8 ? 'negocia' : 'descarta'));
        $verdict_icon = match($verdict_sc) {
            'compra_directa' => ['✅', '#059669'],
            'viable'         => ['👍', '#d97706'],
            'negocia'        => ['⚠️', '#d97706'],
            default          => ['🚫', '#dc2626'],
        };
        $is_current = ($mult_key === $precioMultiplier) || (abs($pvm2 - (int)str_replace([',',' '],'', $precioVentaM2 ?: '0')) < 500);

        $sc_rows[] = compact('mult_key','meta','pvm2','residual','oferta','oferta_m2','roi_sc','inc_sc','verdict_sc','verdict_icon','is_current');
    }
@endphp
<div class="card" style="margin-bottom:.85rem;border-top:3px solid #f59e0b;">
<div class="card-body" style="padding:.85rem 1.1rem;">
    <div style="font-size:.7rem;font-weight:700;color:#0f172a;margin-bottom:.2rem;">
        🎯 Tabla de escenarios — ¿a qué precio de venta puede el constructor pagar más por el terreno?
    </div>
    <div style="font-size:.65rem;color:#64748b;margin-bottom:.75rem;line-height:1.5;">
        Cambiando el precio al que el constructor venderá los departamentos, cambia cuánto puede pagar
        por el terreno. Úsala para saber <strong>a qué precio debes salir a vender el terreno</strong>
        y qué precio de nuevos deptos hace el negocio viable.
        @if($sc_pterreno > 0)
        Precio del terreno analizado: <strong>${{ number_format((int)$sc_pterreno) }}</strong>
        @endif
    </div>

    {{-- Tabla --}}
    <div style="overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;font-size:.7rem;">
        <thead>
            <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                <th style="padding:.4rem .5rem;text-align:left;font-weight:700;color:#475569;white-space:nowrap;">Premium</th>
                <th style="padding:.4rem .5rem;text-align:right;font-weight:700;color:#475569;white-space:nowrap;">$/m² venta</th>
                <th style="padding:.4rem .5rem;text-align:right;font-weight:700;color:#475569;white-space:nowrap;">Residual máx</th>
                <th style="padding:.4rem .5rem;text-align:right;font-weight:700;color:#475569;white-space:nowrap;">Oferta sugerida</th>
                <th style="padding:.4rem .5rem;text-align:right;font-weight:700;color:#475569;white-space:nowrap;">ROI actual</th>
                <th style="padding:.4rem .5rem;text-align:center;font-weight:700;color:#475569;">Verdict</th>
            </tr>
        </thead>
        <tbody>
        @foreach($sc_rows as $sc)
        @php
            $rowBg = $sc['is_current'] ? '#faf5ff' : 'transparent';
            $rowBorder = $sc['is_current'] ? '2px solid #6366f1' : '1px solid #f1f5f9';
        @endphp
        <tr style="background:{{ $rowBg }};border-left:{{ $rowBorder }};border-bottom:1px solid #f1f5f9;
                   {{ $sc['is_current'] ? 'font-weight:700;' : '' }}">
            <td style="padding:.45rem .5rem;">
                @if($sc['is_current'])<span style="color:#6366f1;font-size:.65rem;margin-right:.2rem;">★</span>@endif
                <span style="color:{{ $sc['meta']['color'] }};font-weight:600;">{{ $sc['meta']['label'] }}</span>
                <div style="font-size:.6rem;color:#94a3b8;font-weight:400;">${{ number_format((int)($observatorioPrice * (float)$sc['mult_key'])) }}/m²</div>
            </td>
            <td style="padding:.45rem .5rem;text-align:right;font-weight:600;color:#0f172a;">
                ${{ number_format($sc['pvm2']) }}
            </td>
            <td style="padding:.45rem .5rem;text-align:right;color:{{ $sc['residual']>0?'#1d4ed8':'#dc2626' }};">
                @if($sc['residual'] > 0)
                ${{ number_format((int)round($sc['residual'])) }}
                <div style="font-size:.58rem;color:#94a3b8;font-weight:400;">${{ $sc['oferta_m2'] > 0 ? number_format($sc['oferta_m2']).'/' : '' }}m²</div>
                @else
                <span style="color:#dc2626;">—</span>
                @endif
            </td>
            <td style="padding:.45rem .5rem;text-align:right;font-weight:700;
                color:{{ $sc['oferta'] > 0 ? '#059669' : '#dc2626' }};">
                @if($sc['oferta'] > 0)
                ${{ number_format((int)round($sc['oferta'])) }}
                @else —
                @endif
            </td>
            <td style="padding:.45rem .5rem;text-align:right;
                color:{{ $sc['roi_sc'] >= 20 ? '#059669' : ($sc['roi_sc'] >= 15 ? '#d97706' : '#dc2626') }};">
                @if($sc['roi_sc'] > -500)
                {{ $sc['roi_sc'] }}%
                @else —
                @endif
            </td>
            <td style="padding:.45rem .5rem;text-align:center;font-size:.78rem;">
                {{ $sc['verdict_icon'][0] }}
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    </div>

    {{-- Leyenda y recomendación --}}
    <div style="margin-top:.75rem;background:#f8fafc;border-radius:6px;padding:.55rem .75rem;font-size:.68rem;color:#475569;line-height:1.6;">
        <div style="font-weight:700;color:#0f172a;margin-bottom:.2rem;">💡 Cómo usar esta tabla para negociar el terreno:</div>
        @php
            // Encontrar el escenario mínimo viable (ROI >= 17%)
            $primer_viable = collect($sc_rows)->first(fn($s) => $s['roi_sc'] >= 17);
            $mejor_viable  = collect($sc_rows)->last(fn($s) => $s['roi_sc'] >= 17);
        @endphp
        @if($primer_viable)
        <div>
            → Con un precio de venta de departamentos de
            <strong>${{ number_format($primer_viable['pvm2']) }}/m²</strong>
            (+{{ round(($primer_viable['pvm2'] - $observatorioPrice) / $observatorioPrice * 100) }}% sobre Obs.),
            el constructor puede pagar hasta
            <strong style="color:#1d4ed8;">${{ number_format((int)round($primer_viable['residual'])) }}</strong>
            por el terreno con ROI viable.
        </div>
        @if($sc_pterreno > 0)
        @php $gap = $sc_pterreno - $primer_viable['oferta']; @endphp
        @if($gap > 0)
        <div style="margin-top:.25rem;">
            → <strong style="color:#dc2626;">El terreno está ${{ number_format((int)$gap) }} por encima</strong>
            de lo que el constructor puede pagar en el escenario más favorable de precio mínimo viable.
            Necesitas que el precio de venta sea al menos <strong>${{ number_format($primer_viable['pvm2']) }}/m²</strong>
            o negociar el precio del terreno a la baja.
        </div>
        @elseif($mejor_viable)
        <div style="margin-top:.25rem;">
            → <strong style="color:#059669;">El terreno encaja bien</strong>
            en el rango de escenarios viables.
            Precio de salida recomendado:
            <strong style="color:#1d4ed8;">${{ number_format((int)round($mejor_viable['oferta'])) }}</strong>
            (escenario +{{ round(($mejor_viable['pvm2'] - $observatorioPrice) / $observatorioPrice * 100) }}%
            sobre Obs.).
        </div>
        @endif
        @endif
        @else
        <div style="color:#dc2626;">
            → Con el precio actual del terreno, ningún escenario de precio de venta llega a ROI viable (17%+).
            El terreno requiere una baja significativa de precio para ser atractivo para un constructor.
        </div>
        @endif
    </div>
</div>
</div>
@endif

{{-- ╔══════════════════════════════════╗
     ║  INDICADORES SECUNDARIOS         ║
     ╚══════════════════════════════════╝ --}}
<div class="card" style="margin-bottom:.85rem;">
<div class="card-body" style="padding:.85rem 1.1rem;">
    <div style="font-size:.7rem;font-weight:700;color:#0f172a;margin-bottom:.55rem;">📊 Indicadores del constructor</div>
    @foreach([
        ['ROI total del proyecto', $r['roi'].'%', $roiColor($r['roi']), '> 22% excelente · 18-22% bueno · 15-18% límite · < 15% difícil en CDMX'],
        ['Ratio terreno / ventas totales', $r['ratio_tierra_venta'].'%', $r['ratio_tierra_venta']<=20?'#059669':($r['ratio_tierra_venta']<=30?'#d97706':'#dc2626'), 'Cuánto del ingreso total va sólo a pagar el terreno — < 20% es óptimo'],
        ['Precio terreno por m² de terreno', '$'.number_format($r['precio_terreno_m2']), '#0f172a', 'Referencia de mercado del suelo en la zona'],
    ] as [$lbl, $val, $clr, $hint])
    <div style="display:flex;align-items:center;justify-content:space-between;padding:.38rem .65rem;background:#f8fafc;border-radius:6px;margin-bottom:.3rem;">
        <div style="flex:1;min-width:0;">
            <div style="font-size:.7rem;font-weight:600;color:#0f172a;">{{ $lbl }}</div>
            <div style="font-size:.6rem;color:#94a3b8;line-height:1.4;">{{ $hint }}</div>
        </div>
        <div style="font-size:.95rem;font-weight:800;color:{{ $clr }};margin-left:.5rem;white-space:nowrap;">{{ $val }}</div>
    </div>
    @endforeach
</div>
</div>

{{-- ╔══════════════════════════════════╗
     ║  ¿QUÉ LE DIGO AL CONSTRUCTOR?   ║
     ╚══════════════════════════════════╝ --}}
<div class="card" style="margin-bottom:.85rem;border-left:4px solid #6366f1;">
<div class="card-body" style="padding:.85rem 1.1rem;">
    <div style="font-size:.7rem;font-weight:700;color:#0f172a;margin-bottom:.55rem;">
        💬 ¿Qué le dices al constructor?
        <span style="font-weight:400;color:#94a3b8;font-size:.62rem;">— talking points para la presentación</span>
    </div>
    <div style="display:flex;flex-direction:column;gap:.35rem;font-size:.7rem;color:#0f172a;">
        <div style="display:flex;gap:.4rem;align-items:flex-start;">
            <span style="flex-shrink:0;color:#6366f1;font-size:.75rem;">→</span>
            <span>"El terreno tiene una <strong>incidencia del {{ $r['incidencia_pct'] }}%</strong>
            sobre el precio de venta —
            @if($r['incidencia_pct']<=12) está por debajo del 12% que es el sweet spot."
            @elseif($r['incidencia_pct']<=18) está en el rango aceptable de 12–18%."
            @else está por encima del 18%, por eso necesitamos negociar el precio."@endif
            </span>
        </div>
        <div style="display:flex;gap:.4rem;align-items:flex-start;">
            <span style="flex-shrink:0;color:#6366f1;font-size:.75rem;">→</span>
            <span>"Con <strong>{{ $zonificacionLabel }}</strong> puedes construir
            <strong>{{ number_format($r['m2_brutos'],0) }} m² brutos</strong>,
            de los cuales <strong>{{ number_format($r['m2_vendibles'],0) }} m² son vendibles</strong>
            — aproximadamente <strong>{{ $r['deptos_estimados'] }} departamentos</strong> de {{ $tamanoDepto }} m²."</span>
        </div>
        <div style="display:flex;gap:.4rem;align-items:flex-start;">
            <span style="flex-shrink:0;color:#6366f1;font-size:.75rem;">→</span>
            <span>"Vendiendo a <strong>${{ number_format($r['precio_venta_m2']) }}/m²</strong>,
            el proyecto genera <strong>${{ number_format($r['ventas']) }}</strong> en ventas totales."</span>
        </div>
        <div style="display:flex;gap:.4rem;align-items:flex-start;">
            <span style="flex-shrink:0;color:{{ $roiColor($r['roi']) }};font-size:.75rem;">→</span>
            @if($r['brecha_pct'] <= 0)
            <span>"Al precio pedido, el ROI estimado es <strong>{{ $r['roi'] }}%</strong>
            @if($r['roi']>=22) — supera el mínimo de 18%, es un buen negocio."
            @else — es aceptable pero vale la pena negociar hasta ${{ number_format($r['precio_oferta']) }}."@endif
            </span>
            @else
            <span>"Para que el proyecto tenga ROI de 18%+, el constructor no puede pagar más de
            <strong>${{ number_format($r['precio_oferta']) }}</strong>
            — hay que negociar una baja de <strong>${{ number_format($r['brecha_oferta']) }}</strong>."</span>
            @endif
        </div>
        <div style="display:flex;gap:.4rem;align-items:flex-start;">
            <span style="flex-shrink:0;color:#6366f1;font-size:.75rem;">→</span>
            <span>"El valor residual por método de desarrollo indica que el terreno vale
            <strong>hasta ${{ number_format($r['valor_residual']) }}</strong> para quien lo vaya a desarrollar
            con este uso de suelo."</span>
        </div>
    </div>
</div>
</div>

{{-- ╔══════════════════════════════════╗
     ║  ESQUEMA DE ASOCIACIÓN           ║
     ╚══════════════════════════════════╝ --}}
@if($r['asociacion'])
@php $a = $r['asociacion']; @endphp
<div class="card" style="margin-bottom:.85rem;border-left:4px solid #7c3aed;">
<div class="card-body" style="padding:.85rem 1.1rem;">
    <div style="font-size:.7rem;font-weight:700;color:#0f172a;margin-bottom:.3rem;">
        🤝 Alternativa: Esquema de Asociación
    </div>
    <div class="edu-box violet" style="margin-bottom:.65rem;font-size:.68rem;" x-data="{open:false}">
        <div @click="open=!open" style="display:flex;align-items:center;justify-content:space-between;cursor:pointer;">
            <strong>¿Qué es y cuándo funciona? (clic para ver)</strong>
            <span x-text="open?'▲':'▼'" style="font-size:.6rem;color:#7c3aed;"></span>
        </div>
        <div x-show="open" x-transition style="margin-top:.4rem;line-height:1.6;">
            <p style="margin:0 0 .3rem;">Cuando el terreno es demasiado caro para compra directa, muchos constructores en CDMX
            proponen un <strong>esquema de asociación o coproducción</strong>:</p>
            <ul style="margin:0;padding-left:1.2rem;">
                <li>El <strong>dueño aporta el terreno</strong> al fideicomiso de desarrollo (no lo vende)</li>
                <li>El <strong>constructor aporta toda la inversión</strong> de construcción y operación</li>
                <li>Al terminar, <strong>se reparten las utilidades</strong> según el capital aportado por cada uno</li>
                <li>El dueño recibe MÁS que si vendiera barato, el constructor NO desembolsa el terreno de golpe</li>
            </ul>
        </div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.4rem;margin-bottom:.5rem;">
        <div style="background:#f5f3ff;border:1px solid #ddd6fe;border-radius:8px;padding:.5rem .65rem;text-align:center;">
            <div style="font-size:.58rem;color:#7c3aed;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.15rem;">Dueño del terreno</div>
            <div style="font-size:1.2rem;font-weight:900;color:#6d28d9;">{{ $a['split_dono'] }}%</div>
            <div style="font-size:.65rem;color:#7c3aed;font-weight:600;">~${{ number_format($a['parte_dono']) }}</div>
            <div style="font-size:.58rem;color:#8b5cf6;margin-top:.1rem;">≈ ${{ number_format($a['equivalente_m2']) }}/m² de terreno</div>
        </div>
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:.5rem .65rem;text-align:center;">
            <div style="font-size:.58rem;color:#059669;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.15rem;">Constructor</div>
            <div style="font-size:1.2rem;font-weight:900;color:#16a34a;">{{ $a['split_developer'] }}%</div>
            <div style="font-size:.65rem;color:#059669;font-weight:600;">${{ number_format($a['utilidad_total'] - $a['parte_dono']) }}</div>
            <div style="font-size:.58rem;color:#16a34a;margin-top:.1rem;">aporta toda la construcción</div>
        </div>
    </div>
    <div style="font-size:.65rem;color:#7c3aed;background:#f5f3ff;border-radius:6px;padding:.35rem .6rem;line-height:1.5;">
        Utilidad total estimada: <strong>${{ number_format($a['utilidad_total']) }}</strong> ·
        Típicamente el dueño sale mejor que vendiendo barato, y el constructor reduce su riesgo de capital.
    </div>
</div>
</div>
@endif

{{-- Norma 10 --}}
@if($r['norma10_aplica'])
<div class="edu-box amber" style="margin-bottom:.75rem;">
    💡 <strong>Norma 10 CDMX:</strong> Lote {{ $m2Terreno }} m² puede calificar para mayor densidad bajo Norma 10
    (aplica a lotes de 60–200 m²). Esto podría aumentar el potencial constructivo.
    Consulta con SEDUVI antes de cotizar — puede cambiar el análisis significativamente.
</div>
@endif

<div style="font-size:.6rem;color:#94a3b8;line-height:1.5;text-align:center;padding:.25rem 0;">
    Método de Desarrollo · Costos ref. CEICO-CMIC 2025 · Observatorio de Precios HDV<br>
    No constituye Opinión de Valor formal — requiere visita técnica y consulta SEDUVI
</div>

@elseif($result && !$result['available'])
<div class="alert alert-warning">{{ $result['reason'] }}</div>

@else
{{-- Estado vacío educativo --}}
<div style="border:2px dashed #e2e8f0;border-radius:12px;padding:1.75rem 1.25rem;text-align:center;">
    <div style="font-size:2.2rem;margin-bottom:.7rem;">🏗</div>
    <div style="font-size:.9rem;font-weight:700;color:#475569;margin-bottom:.3rem;">
        ¿Vale la pena para una constructora?
    </div>
    <div style="font-size:.75rem;color:#94a3b8;line-height:1.7;margin-bottom:1.1rem;">
        Completa la sección 1 (terreno), 2 (zonificación) y 3 (precios)
        para ver el análisis completo
    </div>
    <div style="background:#f8fafc;border-radius:8px;padding:.75rem 1rem;text-align:left;display:flex;flex-direction:column;gap:.35rem;">
        @foreach([
            ['★', '#6366f1', 'Incidencia del terreno', '¿cuánto cuesta el m² vendible?'],
            ['🏗', '#0f172a', 'Método Residual', '¿cuánto DEBE costar el terreno?'],
            ['✅', '#059669', 'Veredicto + precio de oferta', 'compra directa / negocia / descarta'],
            ['💰', '#d97706', 'Waterfall financiero', 'construcción, permisos, financiamiento'],
            ['🤝', '#7c3aed', 'Esquema de asociación', 'alternativa si el terreno es caro'],
            ['💬', '#6366f1', 'Talking points', '¿qué le dices al constructor?'],
        ] as [$icon, $clr, $title, $sub])
        <div style="display:flex;align-items:center;gap:.5rem;font-size:.7rem;">
            <span style="color:{{ $clr }};font-size:.75rem;flex-shrink:0;">{{ $icon }}</span>
            <span><strong>{{ $title }}</strong> — {{ $sub }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

</div>{{-- /columna derecha --}}

</div>{{-- /flex --}}
</div>
