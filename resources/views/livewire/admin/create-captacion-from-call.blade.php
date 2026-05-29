<div>
{{-- ── Indicador de progreso ──────────────────────────────────────────────── --}}
<div style="display:flex;align-items:center;gap:0;margin-bottom:2rem;max-width:480px;">
    @foreach([1 => 'Cliente', 2 => 'Inmueble', 3 => 'Propuesta'] as $n => $label)
    <div style="display:flex;align-items:center;flex:1;">
        <div style="display:flex;flex-direction:column;align-items:center;gap:.3rem;flex:1;">
            <div style="
                width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;
                font-size:.8rem;font-weight:700;
                background:{{ $step >= $n ? 'var(--primary)' : 'var(--border)' }};
                color:{{ $step >= $n ? '#fff' : 'var(--text-muted)' }};
            ">{{ $n }}</div>
            <span style="font-size:.7rem;color:{{ $step === $n ? 'var(--primary)' : 'var(--text-muted)' }};font-weight:{{ $step === $n ? 600 : 400 }};">{{ $label }}</span>
        </div>
        @if($n < 3)
        <div style="height:2px;flex:1;margin-bottom:1.4rem;background:{{ $step > $n ? 'var(--primary)' : 'var(--border)' }};"></div>
        @endif
    </div>
    @endforeach
</div>

{{-- ── Errores de validación ──────────────────────────────────────────────── --}}
@if($errors->any())
<div class="alert alert-error" style="margin-bottom:1.5rem;">
    <x-icon name="triangle-alert" class="w-4 h-4" style="flex-shrink:0;" />
    <div>
        @foreach($errors->all() as $e)
        <div>{{ $e }}</div>
        @endforeach
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════════════════
     PASO 1 — CLIENTE
     ══════════════════════════════════════════════════════════════════════════ --}}
@if($step === 1)
<div class="card">
    <div class="card-header">
        <h3>Datos del propietario</h3>
        <span style="font-size:.75rem;color:var(--text-muted);">Paso 1 de 3</span>
    </div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">

            {{-- Nombre --}}
            <div class="form-group" style="grid-column:1/-1;">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:.4rem;">
                    Nombre completo <span style="color:var(--danger)">*</span>
                </label>
                <input wire:model="name" type="text" placeholder="Ej. María García López"
                    style="width:100%;padding:.55rem .8rem;border:1px solid var(--border);border-radius:var(--radius);font-family:inherit;font-size:.88rem;background:var(--card);color:var(--text);"
                    autofocus>
                @error('name') <span style="font-size:.75rem;color:var(--danger);margin-top:.25rem;display:block;">{{ $message }}</span> @enderror
            </div>

            {{-- Teléfono --}}
            <div class="form-group">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:.4rem;">
                    Teléfono <span style="color:var(--danger)">*</span>
                </label>
                <input wire:model="phone" type="tel" placeholder="55 1234 5678"
                    style="width:100%;padding:.55rem .8rem;border:1px solid var(--border);border-radius:var(--radius);font-family:inherit;font-size:.88rem;">
                @error('phone') <span style="font-size:.75rem;color:var(--danger);margin-top:.25rem;display:block;">{{ $message }}</span> @enderror
                <span style="font-size:.72rem;color:var(--text-muted);margin-top:.3rem;display:block;">
                    Pregunta el medio preferido para recibir la presentación (correo / WhatsApp / ambos).
                </span>
            </div>

            {{-- WhatsApp --}}
            <div class="form-group">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:.4rem;">
                    WhatsApp <span style="font-size:.72rem;font-weight:400;color:var(--text-muted);">(opcional)</span>
                </label>
                <input wire:model="whatsapp" type="tel" placeholder="55 1234 5678 (si es diferente)"
                    style="width:100%;padding:.55rem .8rem;border:1px solid var(--border);border-radius:var(--radius);font-family:inherit;font-size:.88rem;">
            </div>

            {{-- Email --}}
            <div class="form-group" style="grid-column:1/-1;">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:.4rem;">
                    Correo electrónico <span style="font-size:.72rem;font-weight:400;color:var(--text-muted);">(opcional)</span>
                </label>
                <input wire:model="email" type="email" placeholder="correo@ejemplo.com"
                    style="width:100%;padding:.55rem .8rem;border:1px solid var(--border);border-radius:var(--radius);font-family:inherit;font-size:.88rem;">
                @error('email') <span style="font-size:.75rem;color:var(--danger);margin-top:.25rem;display:block;">{{ $message }}</span> @enderror
                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;padding:.5rem .75rem;margin-top:.5rem;display:flex;align-items:flex-start;gap:.5rem;">
                    <x-icon name="info" class="w-[14px] h-[14px]" style="color:#1d4ed8;flex-shrink:0;margin-top:2px;" />
                    <span style="font-size:.75rem;color:#1e40af;line-height:1.5;">
                        Si el propietario te dio su correo y WhatsApp, captúralos ahora — la presentación se envía en seguida.
                    </span>
                </div>
            </div>

            {{-- RFC --}}
            <div class="form-group">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:.4rem;">
                    RFC <span style="font-size:.72rem;font-weight:400;color:var(--text-muted);">(opcional)</span>
                </label>
                <input wire:model="rfc" type="text" placeholder="GALO800101ABC"
                    style="width:100%;padding:.55rem .8rem;border:1px solid var(--border);border-radius:var(--radius);font-family:inherit;font-size:.88rem;">
            </div>

            {{-- Estado civil --}}
            <div class="form-group">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:.4rem;">
                    Estado civil <span style="font-size:.72rem;font-weight:400;color:var(--text-muted);">(opcional)</span>
                </label>
                <select wire:model="civil_status"
                    style="width:100%;padding:.55rem .8rem;border:1px solid var(--border);border-radius:var(--radius);font-family:inherit;font-size:.88rem;background:var(--card);">
                    <option value="">— Selecciona —</option>
                    <option value="soltero">Soltero/a</option>
                    <option value="casado">Casado/a</option>
                    <option value="divorciado">Divorciado/a</option>
                    <option value="viudo">Viudo/a</option>
                    <option value="union_libre">Unión libre</option>
                </select>
            </div>

            {{-- Dirección actual del cliente --}}
            <div class="form-group" style="grid-column:1/-1;">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:.4rem;">
                    Dirección actual del propietario <span style="font-size:.72rem;font-weight:400;color:var(--text-muted);">(opcional)</span>
                </label>
                <input wire:model="client_address" type="text" placeholder="Calle, número, colonia, ciudad"
                    style="width:100%;padding:.55rem .8rem;border:1px solid var(--border);border-radius:var(--radius);font-family:inherit;font-size:.88rem;">
            </div>

        </div>
    </div>
</div>

<div style="display:flex;justify-content:flex-end;gap:.75rem;margin-top:.5rem;">
    <button wire:click="nextStep" class="btn btn-primary">
        Siguiente: Inmueble
        <x-icon name="arrow-right" class="w-4 h-4" />
    </button>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════════════════
     PASO 2 — INMUEBLE
     ══════════════════════════════════════════════════════════════════════════ --}}
@if($step === 2)
<div style="display:flex;gap:1rem;align-items:flex-start;">

{{-- ── Columna izquierda: formulario ──────────────────────────────────── --}}
<div style="flex:1;min-width:0;">
<div class="card">
    <div class="card-header">
        <h3>Datos del inmueble</h3>
        <span style="font-size:.75rem;color:var(--text-muted);">Paso 2 de 3</span>
    </div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">

            {{-- Tipo de inmueble --}}
            <div class="form-group">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:.4rem;">
                    Tipo de inmueble <span style="color:var(--danger)">*</span>
                </label>
                <select wire:model.live="property_type"
                    style="width:100%;padding:.55rem .8rem;border:1px solid var(--border);border-radius:var(--radius);font-family:inherit;font-size:.88rem;background:var(--card);">
                    <option value="">— Selecciona —</option>
                    @foreach($propertyTypes as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('property_type') <span style="font-size:.75rem;color:var(--danger);margin-top:.25rem;display:block;">{{ $message }}</span> @enderror
            </div>

            {{-- Colonia --}}
            <div class="form-group">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:.4rem;">
                    Colonia <span style="color:var(--danger)">*</span>
                </label>

                {{-- Select con colonias del observatorio --}}
                <select wire:model.live="colony_id"
                        style="width:100%;padding:.55rem .8rem;border:1px solid var(--border);border-radius:var(--radius);font-family:inherit;font-size:.88rem;background:var(--card);color:var(--text);">
                    <option value="">— Selecciona colonia —</option>
                    @foreach($coloniasByZone as $zoneName => $colonias)
                    <optgroup label="{{ $zoneName }}">
                        @foreach($colonias as $col)
                        <option value="{{ $col->id }}">{{ $col->name }}{{ $col->cp ? ' · CP '.$col->cp : '' }}</option>
                        @endforeach
                    </optgroup>
                    @endforeach
                    <option value="otra" style="font-style:italic;color:#6366f1;">✏ Otra colonia (escribir)</option>
                </select>

                {{-- CP auto-llenado --}}
                @if($colony_cp && !$colony_is_custom)
                <div style="font-size:.75rem;color:#059669;margin-top:.3rem;display:flex;align-items:center;gap:.3rem;">
                    ✓ CP {{ $colony_cp }} · Benito Juárez, CDMX
                </div>
                @endif

                {{-- Input manual cuando elige "Otra" --}}
                @if($colony_is_custom)
                <div style="margin-top:.5rem;">
                    <input wire:model="colony" type="text"
                           placeholder="Escribe el nombre de la colonia"
                           style="width:100%;padding:.55rem .8rem;border:1px solid #6366f1;border-radius:var(--radius);font-family:inherit;font-size:.88rem;"
                           autofocus>
                    <div style="display:flex;gap:.5rem;margin-top:.4rem;">
                        <input wire:model="colony_cp" type="text"
                               placeholder="CP (opcional)"
                               style="width:120px;padding:.45rem .7rem;border:1px solid var(--border);border-radius:var(--radius);font-family:inherit;font-size:.85rem;">
                        <span style="font-size:.75rem;color:var(--text-muted);align-self:center;">
                            Esta colonia no tiene datos en el Observatorio todavía
                        </span>
                    </div>
                </div>
                @endif

                @error('colony') <span style="font-size:.75rem;color:var(--danger);margin-top:.25rem;display:block;">{{ $message }}</span> @enderror
            </div>

            {{-- Ciudad (oculta — se toma de la colonia, editable solo si es "otra") --}}
            @if($colony_is_custom)
            <div class="form-group">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:.4rem;">Ciudad</label>
                <input wire:model="city" type="text" placeholder="CDMX"
                    style="width:100%;padding:.55rem .8rem;border:1px solid var(--border);border-radius:var(--radius);font-family:inherit;font-size:.88rem;">
            </div>
            @endif

            {{-- Dirección exacta --}}
            <div class="form-group">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:.4rem;">
                    Dirección exacta <span style="font-size:.72rem;font-weight:400;color:var(--text-muted);">(opcional)</span>
                </label>
                <input wire:model="address" type="text" placeholder="Calle, número"
                    style="width:100%;padding:.55rem .8rem;border:1px solid var(--border);border-radius:var(--radius);font-family:inherit;font-size:.88rem;">
            </div>

            {{-- m² --}}
            <div class="form-group">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:.4rem;">
                    m² totales <span style="font-size:.72rem;font-weight:400;color:var(--text-muted);">(opcional)</span>
                </label>
                <input wire:model.live="area" type="number" min="0" step="0.1" placeholder="120"
                    style="width:100%;padding:.55rem .8rem;border:1px solid var(--border);border-radius:var(--radius);font-family:inherit;font-size:.88rem;">
            </div>

            {{-- Precio esperado --}}
            <div class="form-group">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:.4rem;">
                    Precio esperado por el propietario <span style="font-size:.72rem;font-weight:400;color:var(--text-muted);">(opcional)</span>
                </label>
                <input wire:model="price_expected" type="text" placeholder="3,500,000"
                    style="width:100%;padding:.55rem .8rem;border:1px solid var(--border);border-radius:var(--radius);font-family:inherit;font-size:.88rem;">
            </div>

            {{-- Recámaras / Baños / Estacionamientos --}}
            <div class="form-group">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:.4rem;">Recámaras</label>
                <input wire:model.live="bedrooms" type="number" min="0" max="20" placeholder="3"
                    style="width:100%;padding:.55rem .8rem;border:1px solid var(--border);border-radius:var(--radius);font-family:inherit;font-size:.88rem;">
            </div>

            <div class="form-group">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:.4rem;">Baños</label>
                <input wire:model.live="bathrooms" type="number" min="0" max="20" placeholder="2"
                    style="width:100%;padding:.55rem .8rem;border:1px solid var(--border);border-radius:var(--radius);font-family:inherit;font-size:.88rem;">
            </div>

            <div class="form-group">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:.4rem;">Estacionamientos</label>
                <input wire:model.live="parking" type="number" min="0" max="10" placeholder="1"
                    style="width:100%;padding:.55rem .8rem;border:1px solid var(--border);border-radius:var(--radius);font-family:inherit;font-size:.88rem;">
            </div>

            {{-- Fotos --}}
            <div class="form-group" style="grid-column:1/-1;">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:.4rem;">
                    Fotos del inmueble <span style="font-size:.72rem;font-weight:400;color:var(--text-muted);">(opcional — máx. 5 imágenes, 10 MB c/u)</span>
                </label>
                <input wire:model="photos" type="file" multiple accept="image/*"
                    style="width:100%;padding:.4rem 0;font-size:.85rem;color:var(--text-muted);">
                <div wire:loading wire:target="photos"
                    style="font-size:.78rem;color:var(--text-muted);margin-top:.3rem;">
                    Subiendo fotos...
                </div>
                @if(!empty($photos))
                <div style="display:flex;flex-wrap:wrap;gap:.5rem;margin-top:.75rem;">
                    @foreach($photos as $photo)
                    <img src="{{ $photo->temporaryUrl() }}"
                        style="width:80px;height:80px;object-fit:cover;border-radius:6px;border:1px solid var(--border);">
                    @endforeach
                </div>
                @endif
                @error('photos.*') <span style="font-size:.75rem;color:var(--danger);margin-top:.25rem;display:block;">{{ $message }}</span> @enderror
            </div>

        </div>
    </div>
</div>
</div>{{-- /columna izquierda --}}

{{-- ── Columna derecha: cotización en vivo ─────────────────────────────── --}}
@if($liveQuote && $liveQuote['available'])
@php $q = $liveQuote; @endphp
<div style="width:320px;flex-shrink:0;" wire:loading.class.remove="opacity-0" wire:loading.class="opacity-50">

    {{-- Cabecera del panel --}}
    <div style="background:linear-gradient(135deg,#1d4ed8 0%,#4f46e5 100%);border-radius:10px 10px 0 0;padding:.75rem 1rem;display:flex;align-items:center;gap:.5rem;">
        <span style="font-size:1rem;">📊</span>
        <div>
            <div style="font-size:.78rem;font-weight:700;color:#fff;letter-spacing:.3px;">Valor estimado en vivo</div>
            <div style="font-size:.68rem;color:#bfdbfe;">{{ $q['colonia_name'] }} · {{ $q['m2_construction'] }} m²</div>
        </div>
        <div wire:loading wire:target="refreshLiveQuote" style="margin-left:auto;">
            <span style="width:14px;height:14px;border:2px solid #fff;border-top-color:transparent;border-radius:50%;display:inline-block;animation:spin 1s linear infinite;"></span>
        </div>
    </div>

    {{-- Ajustes aplicados (solo si hay) --}}
    @if(!empty($q['adjustments']))
    <div style="background:#eff6ff;border-left:3px solid #3b82f6;border-right:1px solid #bfdbfe;padding:.45rem .8rem;font-size:.7rem;color:#1e40af;">
        @foreach($q['adjustments'] as $adj)
        <span style="margin-right:.4rem;">
            {{ $adj['label'] }}
            <strong>{{ $adj['pct'] > 0 ? '+' : '' }}{{ round($adj['pct'] * 100) }}%</strong>
        </span>
        @endforeach
    </div>
    @endif

    {{-- Escenarios --}}
    <div style="border:1px solid #e2e8f0;border-top:none;border-radius:0 0 10px 10px;overflow:hidden;">

        {{-- 1. Venta vivienda --}}
        @if($q['sale_residential'])
        @php $s = $q['sale_residential']; @endphp
        <div style="padding:.7rem 1rem;border-bottom:1px solid #f1f5f9;">
            <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:#2563eb;margin-bottom:.3rem;">🏠 Venta habitacional</div>
            <div style="font-size:1.1rem;font-weight:800;color:var(--text,#0f172a);">
                ${{ number_format($s['low']) }}
                <span style="font-size:.78rem;font-weight:400;color:#94a3b8;">–</span>
                ${{ number_format($s['high']) }}
            </div>
            <div style="font-size:.7rem;color:#64748b;">Promedio: <strong>${{ number_format($s['mid']) }}</strong> · ${{ number_format($s['per_m2']) }}/m²</div>
        </div>
        @endif

        {{-- 2. Venta constructor --}}
        @if($q['sale_constructor'])
        @php $s = $q['sale_constructor']; @endphp
        <div style="padding:.7rem 1rem;border-bottom:1px solid #f1f5f9;">
            <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:#d97706;margin-bottom:.3rem;">🏗️ Venta a constructor</div>
            <div style="font-size:1.1rem;font-weight:800;color:var(--text,#0f172a);">
                ${{ number_format($s['low']) }}
                <span style="font-size:.78rem;font-weight:400;color:#94a3b8;">–</span>
                ${{ number_format($s['high']) }}
            </div>
            <div style="font-size:.7rem;color:#64748b;">Promedio: <strong>${{ number_format($s['mid']) }}</strong>
                @if(!empty($s['per_m2'])) · ${{ number_format($s['per_m2']) }}/m² @endif
            </div>
            @if(!empty($s['note']))
            <div style="font-size:.65rem;color:#92400e;margin-top:.2rem;">{{ $s['note'] }}</div>
            @endif
        </div>
        @endif

        {{-- 3. Renta habitacional --}}
        @if($q['rent_residential'])
        @php $s = $q['rent_residential']; @endphp
        <div style="padding:.7rem 1rem;border-bottom:1px solid #f1f5f9;">
            <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:#7c3aed;margin-bottom:.3rem;">🔑 Renta habitacional</div>
            <div style="font-size:1.1rem;font-weight:800;color:var(--text,#0f172a);">
                ${{ number_format($s['low']) }}
                <span style="font-size:.78rem;font-weight:400;color:#94a3b8;">–</span>
                ${{ number_format($s['high']) }}
                <span style="font-size:.7rem;font-weight:500;color:#94a3b8;">/mes</span>
            </div>
            <div style="font-size:.7rem;color:#64748b;">Promedio: <strong>${{ number_format($s['mid']) }}/mes</strong> · ${{ number_format($s['per_m2']) }}/m²</div>
        </div>
        @endif

        {{-- 4. Renta comercial --}}
        @if($q['rent_commercial'])
        @php $s = $q['rent_commercial']; @endphp
        <div style="padding:.7rem 1rem;">
            <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:#059669;margin-bottom:.3rem;">🏪 Renta comercial</div>
            <div style="font-size:1.1rem;font-weight:800;color:var(--text,#0f172a);">
                ${{ number_format($s['low']) }}
                <span style="font-size:.78rem;font-weight:400;color:#94a3b8;">–</span>
                ${{ number_format($s['high']) }}
                <span style="font-size:.7rem;font-weight:500;color:#94a3b8;">/mes</span>
            </div>
            <div style="font-size:.7rem;color:#64748b;">Promedio: <strong>${{ number_format($s['mid']) }}/mes</strong> · ${{ number_format($s['per_m2']) }}/m²</div>
        </div>
        @endif

    </div>

    {{-- Precio esperado vs estimado --}}
    @if($price_expected && $q['sale_residential'])
    @php
        $expected = (float) str_replace(',', '', $price_expected);
        $mid      = $q['sale_residential']['mid'];
        $diff     = $mid > 0 ? round(($expected - $mid) / $mid * 100, 1) : null;
    @endphp
    @if($diff !== null)
    <div style="margin-top:.6rem;background:{{ abs($diff) <= 10 ? '#f0fdf4' : ($diff > 10 ? '#fefce8' : '#fef2f2') }};border:1px solid {{ abs($diff) <= 10 ? '#86efac' : ($diff > 10 ? '#fde047' : '#fca5a5') }};border-radius:8px;padding:.5rem .8rem;font-size:.72rem;color:{{ abs($diff) <= 10 ? '#166534' : ($diff > 10 ? '#713f12' : '#991b1b') }};">
        @if(abs($diff) <= 10)
        ✅ Precio esperado alineado con el mercado ({{ $diff > 0 ? '+' : '' }}{{ $diff }}%)
        @elseif($diff > 10)
        ⚠️ Precio esperado <strong>{{ $diff }}% arriba</strong> del estimado — argumentar con datos
        @else
        ✅ Precio esperado <strong>{{ abs($diff) }}% debajo</strong> del estimado — buena oportunidad
        @endif
    </div>
    @endif
    @endif

    {{-- Nota --}}
    <div style="margin-top:.5rem;font-size:.65rem;color:#94a3b8;line-height:1.4;text-align:center;">
        Referencia rápida · Datos del Observatorio HDV<br>
        Requiere visita técnica para OdV formal
    </div>

</div>
@elseif($step === 2 && (!$colony_id || $colony_id === 'otra' || !$area || (float)$area < 10))
<div style="width:280px;flex-shrink:0;">
    <div style="border:2px dashed #e2e8f0;border-radius:10px;padding:1.5rem 1rem;text-align:center;color:#94a3b8;">
        <div style="font-size:2rem;margin-bottom:.5rem;">📊</div>
        <div style="font-size:.8rem;font-weight:600;margin-bottom:.3rem;">Cotización en vivo</div>
        <div style="font-size:.72rem;line-height:1.5;">
            Selecciona la colonia<br>e ingresa los m² para ver<br>el valor estimado al instante
        </div>
    </div>
</div>
@endif

</div>{{-- /flex row --}}

<div style="display:flex;justify-content:space-between;gap:.75rem;margin-top:.5rem;">
    <button wire:click="prevStep" class="btn btn-outline">
        <x-icon name="arrow-left" class="w-4 h-4" />
        Anterior
    </button>
    <button wire:click="nextStep" class="btn btn-primary">
        Siguiente: Propuesta
        <x-icon name="arrow-right" class="w-4 h-4" />
    </button>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════════════════
     PASO 3 — INTENCIÓN Y PROPUESTA
     ══════════════════════════════════════════════════════════════════════════ --}}
@if($step === 3)
<div class="card">
    <div class="card-header">
        <h3>Intención y propuesta</h3>
        <span style="font-size:.75rem;color:var(--text-muted);">Paso 3 de 3</span>
    </div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">

            {{-- Intent --}}
            <div class="form-group">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:.4rem;">
                    Tipo de operación <span style="color:var(--danger)">*</span>
                </label>
                <select wire:model.live="intent"
                    style="width:100%;padding:.55rem .8rem;border:1px solid var(--border);border-radius:var(--radius);font-family:inherit;font-size:.88rem;background:var(--card);">
                    @foreach($intents as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('intent') <span style="font-size:.75rem;color:var(--danger);margin-top:.25rem;display:block;">{{ $message }}</span> @enderror
            </div>

            {{-- Comisión --}}
            <div class="form-group">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:.4rem;">
                    Comisión propuesta (%) <span style="color:var(--danger)">*</span>
                </label>
                <div style="display:flex;align-items:center;gap:.5rem;">
                    <input wire:model="commission_pct" type="number" min="0" max="100" step="0.5"
                        style="width:100px;padding:.55rem .8rem;border:1px solid var(--border);border-radius:var(--radius);font-family:inherit;font-size:.88rem;font-weight:600;">
                    <input wire:model="commission_pct" type="range" min="0" max="10" step="0.5"
                        style="flex:1;accent-color:var(--primary);">
                    <span style="font-size:1rem;font-weight:700;color:var(--success);min-width:36px;">{{ $commission_pct }}%</span>
                </div>
                @error('commission_pct') <span style="font-size:.75rem;color:var(--danger);margin-top:.25rem;display:block;">{{ $message }}</span> @enderror
            </div>

            {{-- Fuente de la captación --}}
            <div class="form-group">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:.4rem;">
                    Origen del contacto
                </label>
                <select wire:model="source"
                    style="width:100%;padding:.55rem .8rem;border:1px solid var(--border);border-radius:var(--radius);font-family:inherit;font-size:.88rem;background:var(--card);">
                    @foreach($sources as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Plan de marketing --}}
            <div class="form-group" style="grid-column:1/-1;">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:.4rem;">
                    Plan de marketing para la presentación
                    <span style="font-size:.72rem;font-weight:400;color:var(--text-muted);"> (editable — aparece en el PDF)</span>
                </label>
                <textarea wire:model="marketing_plan" rows="7"
                    style="width:100%;padding:.55rem .8rem;border:1px solid var(--border);border-radius:var(--radius);font-family:inherit;font-size:.85rem;line-height:1.6;resize:vertical;">{{ $marketing_plan }}</textarea>
                @error('marketing_plan') <span style="font-size:.75rem;color:var(--danger);margin-top:.25rem;display:block;">{{ $message }}</span> @enderror
            </div>

            {{-- Notas de la llamada --}}
            <div class="form-group" style="grid-column:1/-1;">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:.4rem;">
                    Notas internas de la llamada <span style="font-size:.72rem;font-weight:400;color:var(--text-muted);">(no aparecen en el PDF)</span>
                </label>
                <textarea wire:model="notes_from_call" rows="3" placeholder="Ej. El propietario viaja en junio, quiere cerrar antes. Tiene copropietario (hermano). Llamar en la mañana..."
                    style="width:100%;padding:.55rem .8rem;border:1px solid var(--border);border-radius:var(--radius);font-family:inherit;font-size:.85rem;line-height:1.6;resize:vertical;"></textarea>
            </div>

            {{-- Advertencia si no hay email --}}
            @if(empty($email))
            <div style="grid-column:1/-1;background:#fefce8;border:1px solid #fde047;border-radius:6px;padding:.6rem .9rem;display:flex;align-items:flex-start;gap:.5rem;">
                <x-icon name="triangle-alert" class="w-[14px] h-[14px]" style="color:#854d0e;flex-shrink:0;margin-top:2px;" />
                <span style="font-size:.78rem;color:#713f12;line-height:1.5;">
                    Sin correo electrónico, solo podrás enviar la presentación por WhatsApp.
                    <a href="#" wire:click="prevStep; prevStep" style="color:#92400e;font-weight:600;text-decoration:underline;">Agregar email en Paso 1</a>
                </span>
            </div>
            @endif

        </div>
    </div>
</div>

<div style="display:flex;justify-content:space-between;gap:.75rem;margin-top:.5rem;flex-wrap:wrap;">
    <button wire:click="prevStep" class="btn btn-outline">
        <x-icon name="arrow-left" class="w-4 h-4" />
        Anterior
    </button>
    <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
        <button wire:click="save(false)" wire:loading.attr="disabled" class="btn btn-outline">
            <span wire:loading.remove wire:target="save">
                <x-icon name="check" class="w-4 h-4" />
                Guardar sin presentación
            </span>
            <span wire:loading wire:target="save">Guardando...</span>
        </button>
        <button wire:click="save(true)" wire:loading.attr="disabled" class="btn btn-primary">
            <span wire:loading.remove wire:target="save">
                <x-icon name="file-text" class="w-4 h-4" />
                Guardar y generar presentación
            </span>
            <span wire:loading wire:target="save">Guardando...</span>
        </button>
    </div>
</div>
@endif

</div>
