{{-- Quick Quote — Calculadora de 4 escenarios de valor --}}
<div>

{{-- ── FORMULARIO (modo calculadora o widget sin datos) ───────── --}}
@if(!$widgetMode || !$result)
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-header">
        <h3 class="card-title">
            📊 Calculadora de Valor de Mercado
        </h3>
        <p class="text-muted" style="font-size:.82rem;margin:0;">
            Estima los 4 escenarios de valor en segundos usando datos del Observatorio
        </p>
    </div>
    <div class="card-body">
        <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr 1fr;gap:1rem;">

            {{-- Colonia --}}
            <div class="form-group full-width">
                <label class="form-label">Colonia <span class="required">*</span></label>
                <select wire:model="coloniaId" class="form-select">
                    <option value="">— Seleccionar colonia —</option>
                    @foreach($colonias as $zoneName => $cols)
                    <optgroup label="{{ $zoneName }}">
                        @foreach($cols as $col)
                        <option value="{{ $col->id }}">{{ $col->name }}</option>
                        @endforeach
                    </optgroup>
                    @endforeach
                </select>
            </div>

            {{-- Tipo --}}
            <div class="form-group">
                <label class="form-label">Tipo de inmueble <span class="required">*</span></label>
                <select wire:model="propertyType" class="form-select">
                    <option value="apartment">🏢 Departamento</option>
                    <option value="house">🏠 Casa</option>
                    <option value="office">🏪 Oficina / Local</option>
                </select>
            </div>

            {{-- Antigüedad --}}
            <div class="form-group">
                <label class="form-label">Antigüedad</label>
                <select wire:model="ageCategory" class="form-select">
                    <option value="new">Nuevo (0–5 años)</option>
                    <option value="mid">Seminuevo (6–20 años)</option>
                    <option value="old">Antiguo (+20 años)</option>
                </select>
            </div>

            {{-- M² construcción --}}
            <div class="form-group">
                <label class="form-label">M² construcción <span class="required">*</span></label>
                <input type="number" wire:model="m2Construction" class="form-input"
                       placeholder="ej. 80" min="10" step="1">
                <span class="form-hint">Solo área construida, no terreno</span>
            </div>

            {{-- M² terreno --}}
            <div class="form-group">
                <label class="form-label">M² terreno <span style="color:#9ca3af;font-weight:400;">(opcional)</span></label>
                <input type="number" wire:model="m2Land" class="form-input"
                       placeholder="ej. 120" min="0" step="1">
                <span class="form-hint">Para precisar precio a constructor</span>
            </div>

            {{-- Edad exacta --}}
            <div class="form-group">
                <label class="form-label">Antigüedad exacta <span style="color:#9ca3af;font-weight:400;">(años)</span></label>
                <input type="number" wire:model="exactAge" class="form-input"
                       placeholder="ej. 12" min="0" max="100" step="1">
                <span class="form-hint">Afina el rango dentro de la categoría</span>
            </div>

            {{-- Recámaras --}}
            <div class="form-group">
                <label class="form-label">Recámaras</label>
                <select wire:model="bedrooms" class="form-select">
                    <option value="0">— No especificado —</option>
                    <option value="1">Studio / 1 rec.</option>
                    <option value="2">2 recámaras</option>
                    <option value="3">3 recámaras</option>
                    <option value="4">4+ recámaras</option>
                </select>
            </div>

            {{-- Baños --}}
            <div class="form-group">
                <label class="form-label">Baños</label>
                <select wire:model="bathrooms" class="form-select">
                    <option value="0">— No especificado —</option>
                    <option value="1">1 baño</option>
                    <option value="2">2 baños</option>
                    <option value="3">3+ baños</option>
                </select>
            </div>

            {{-- Estacionamiento --}}
            <div class="form-group">
                <label class="form-label">Estacionamiento</label>
                <select wire:model="parking" class="form-select">
                    <option value="-1">— No especificado —</option>
                    <option value="0">Sin cajón</option>
                    <option value="1">1 cajón</option>
                    <option value="2">2 cajones</option>
                    <option value="3">3+ cajones</option>
                </select>
            </div>

        </div>

        @error('coloniaId') <p class="form-error">{{ $message }}</p> @enderror
        @error('m2Construction') <p class="form-error">{{ $message }}</p> @enderror

        <div style="margin-top:1rem;">
            <button wire:click="calculate"
                    wire:loading.attr="disabled"
                    wire:loading.class="btn-loading"
                    class="btn btn-primary"
                    style="display:inline-flex;align-items:center;gap:.4rem;">
                <span wire:loading.remove wire:target="calculate">📊 Calcular 4 escenarios</span>
                <span wire:loading wire:target="calculate" style="display:none;">
                    <span class="btn-spinner"></span> Calculando...
                </span>
            </button>
        </div>
    </div>
</div>
@endif

{{-- ── RESULTADOS ───────────────────────────────────────────────── --}}
@if($result)

@if(!$result['available'])
<div class="alert alert-warning">{{ $result['reason'] ?? 'Sin datos disponibles para esta zona.' }}</div>
@else

{{-- Meta info --}}
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;margin-bottom:1rem;">
    <div>
        <span style="font-size:.95rem;font-weight:700;color:var(--text);">
            {{ $result['colonia_name'] }} · {{ $result['m2_construction'] }} m²
            @if($result['m2_land'] > 0) · {{ $result['m2_land'] }} m² terreno @endif
        </span>
        <span class="badge badge-outline" style="margin-left:.5rem;">
            {{ ['new'=>'Nuevo','mid'=>'Seminuevo','old'=>'Antiguo'][$result['age_category']] }}
        </span>
    </div>
    <div style="font-size:.78rem;color:var(--text-muted);">
        Datos: {{ $result['period'] ?? 'recientes' }} · Zona {{ $result['zone_name'] }}
    </div>
</div>

{{-- Panel de ajustes aplicados --}}
@if(!empty($result['adjustments']))
<div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:.65rem 1rem;margin-bottom:1rem;font-size:.78rem;">
    <span style="font-weight:600;color:#0369a1;">Ajustes aplicados:</span>
    @foreach($result['adjustments'] as $adj)
    <span style="display:inline-block;margin-left:.5rem;background:#fff;border:1px solid #bae6fd;border-radius:4px;padding:.1rem .4rem;color:{{ $adj['pct'] > 0 ? '#16a34a' : '#dc2626' }};">
        {{ $adj['label'] }}
        {{ $adj['pct'] > 0 ? '+' : '' }}{{ round($adj['pct'] * 100, 0) }}%
    </span>
    @endforeach
    <span style="margin-left:.5rem;color:#0369a1;font-weight:600;">
        Total: {{ $result['total_adjustment'] > 0 ? '+' : '' }}{{ $result['total_adjustment'] }}%
    </span>
</div>
@endif

{{-- Grid de 4 escenarios --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">

    {{-- 1. Venta como vivienda --}}
    @if($result['sale_residential'])
    @php $s = $result['sale_residential']; @endphp
    <div class="card" style="border-left:4px solid #2563eb;">
        <div class="card-body" style="padding:1rem 1.25rem;">
            <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#2563eb;margin-bottom:.5rem;">
                🏠 Venta como vivienda
            </div>
            <div style="font-size:1.35rem;font-weight:800;color:var(--text);margin-bottom:.25rem;">
                ${{ number_format($s['low']) }}
                <span style="font-size:.9rem;font-weight:400;color:var(--text-muted);">–</span>
                ${{ number_format($s['high']) }}
            </div>
            <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:.5rem;">
                Promedio: <strong>${{ number_format($s['mid']) }}</strong>
                · ${{ number_format($s['per_m2']) }}/m²
            </div>
            @include('livewire.admin._quick-quote-badge', ['s' => $s])
        </div>
    </div>
    @endif

    {{-- 2. Venta a constructor --}}
    @if($result['sale_constructor'])
    @php $s = $result['sale_constructor']; @endphp
    <div class="card" style="border-left:4px solid #d97706;">
        <div class="card-body" style="padding:1rem 1.25rem;">
            <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#d97706;margin-bottom:.5rem;">
                🏗️ Venta a constructor
            </div>
            <div style="font-size:1.35rem;font-weight:800;color:var(--text);margin-bottom:.25rem;">
                ${{ number_format($s['low']) }}
                <span style="font-size:.9rem;font-weight:400;color:var(--text-muted);">–</span>
                ${{ number_format($s['high']) }}
            </div>
            <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:.5rem;">
                Promedio: <strong>${{ number_format($s['mid']) }}</strong>
            </div>
            @if($s['note'])
            <div style="font-size:.72rem;color:#92400e;background:#fffbeb;border-radius:6px;padding:.3rem .5rem;margin-bottom:.4rem;">
                {{ $s['note'] }}
            </div>
            @endif
            @include('livewire.admin._quick-quote-badge', ['s' => $s])
        </div>
    </div>
    @endif

    {{-- 3. Renta habitacional --}}
    @if($result['rent_residential'])
    @php $s = $result['rent_residential']; @endphp
    <div class="card" style="border-left:4px solid #7c3aed;">
        <div class="card-body" style="padding:1rem 1.25rem;">
            <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#7c3aed;margin-bottom:.5rem;">
                🔑 Renta habitacional
            </div>
            <div style="font-size:1.35rem;font-weight:800;color:var(--text);margin-bottom:.25rem;">
                ${{ number_format($s['low']) }}
                <span style="font-size:.9rem;font-weight:400;color:var(--text-muted);">–</span>
                ${{ number_format($s['high']) }}
                <span style="font-size:.75rem;font-weight:500;color:var(--text-muted);">/mes</span>
            </div>
            <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:.5rem;">
                Promedio: <strong>${{ number_format($s['mid']) }}/mes</strong>
                · ${{ number_format($s['per_m2']) }}/m²/mes
            </div>
            @include('livewire.admin._quick-quote-badge', ['s' => $s])
        </div>
    </div>
    @endif

    {{-- 4. Renta comercial --}}
    @if($result['rent_commercial'])
    @php $s = $result['rent_commercial']; @endphp
    <div class="card" style="border-left:4px solid #059669;">
        <div class="card-body" style="padding:1rem 1.25rem;">
            <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#059669;margin-bottom:.5rem;">
                🏪 Renta comercial / oficina
            </div>
            <div style="font-size:1.35rem;font-weight:800;color:var(--text);margin-bottom:.25rem;">
                ${{ number_format($s['low']) }}
                <span style="font-size:.9rem;font-weight:400;color:var(--text-muted);">–</span>
                ${{ number_format($s['high']) }}
                <span style="font-size:.75rem;font-weight:500;color:var(--text-muted);">/mes</span>
            </div>
            <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:.5rem;">
                Promedio: <strong>${{ number_format($s['mid']) }}/mes</strong>
                · ${{ number_format($s['per_m2']) }}/m²/mes
            </div>
            @include('livewire.admin._quick-quote-badge', ['s' => $s])
        </div>
    </div>
    @endif

</div>

{{-- Disclaimer --}}
<div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:.65rem 1rem;font-size:.75rem;color:#92400e;">
    ⚠️ <strong>Estimado de referencia rápida</strong> — basado en datos del Observatorio de Precios de Benito Juárez.
    Los valores reales dependen de estado de conservación, piso, amenidades y condiciones de negociación.
    Para una <strong>Opinión de Valor formal</strong> se requiere visita técnica.
</div>

{{-- Botón recalcular (modo widget) --}}
@if($widgetMode)
<div style="margin-top:.75rem;text-align:right;">
    <button wire:click="$set('result', null)" class="btn btn-outline btn-sm">
        ↺ Recalcular
    </button>
</div>
@endif

@endif {{-- available --}}
@endif {{-- result --}}

</div>
