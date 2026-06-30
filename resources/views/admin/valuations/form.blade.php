@extends('layouts.app-sidebar')
@section('title', isset($valuation) ? 'Editar valuación' : 'Nueva valuación')

@section('content')
@php
    $editing = isset($valuation) && $valuation->exists;
    $prefill = $prefill ?? [];
    // When creating new, use prefill values as defaults (unless old() is present)
    $pColoniaId   = $prefill['input_colonia_id'] ?? null;
    $pType        = $prefill['input_type'] ?? null;
    $pBedrooms    = $prefill['input_bedrooms'] ?? null;
    $pBathrooms   = $prefill['input_bathrooms'] ?? null;
    $pParking     = $prefill['input_parking'] ?? null;
    $pAreaTotal   = $prefill['input_area_total'] ?? null;
    $pAreaPrivada = $prefill['input_area_privada'] ?? null;
    $pAge         = $prefill['input_age'] ?? null;
@endphp

<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>

<div class="page-header">
    <div>
        <h2>{{ $editing ? 'Editar valuación' : 'Nueva opinión de valor' }}</h2>
        @if($property)
            <p class="text-muted" style="font-size:.83rem;margin-top:4px;">
                Vinculada a: <strong>{{ $property->title }}</strong>
            </p>
        @endif
    </div>
    <a href="{{ route('admin.valuations.index') }}" class="btn btn-outline">← Volver</a>
</div>

<form method="POST"
      action="{{ $editing ? route('admin.valuations.update', $valuation) : route('admin.valuations.store') }}"
      x-data="{ propType: '{{ old('input_type', $valuation->input_type ?? $pType ?? 'apartment') }}', submitting: false }"
      @submit="submitting = true">
    @csrf
    @if($editing) @method('PUT') @endif

    @if($property)
        <input type="hidden" name="property_id" value="{{ $property->id }}">
    @endif

    {{-- Errores de validación --}}
    @if($errors->any())
    <div class="alert alert-error" style="margin-bottom:1.25rem;">
        <div>
            <strong>Hay errores en el formulario. Por favor corrígelos antes de guardar:</strong>
            <ul style="margin:.35rem 0 0 1.25rem;font-size:.85rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <div style="display:grid;grid-template-columns:1fr 340px;gap:1.5rem;align-items:start;">

        {{-- ══ COLUMNA PRINCIPAL ══ --}}
        <div style="display:flex;flex-direction:column;gap:1.5rem;">

            {{-- Ubicación y tipo --}}
            <div class="card">
                <div class="card-header"><h3 class="card-title">Ubicación y tipo</h3></div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label class="form-label">Colonia <span class="required">*</span></label>
                            <select name="input_colonia_id" class="form-select"
                                    onchange="syncColoniaRaw(this)">
                                <option value="">— Seleccionar colonia —</option>
                                @foreach($colonias as $zoneName => $cols)
                                <optgroup label="{{ $zoneName }}">
                                    @foreach($cols as $col)
                                    <option value="{{ $col->id }}"
                                        {{ old('input_colonia_id', $valuation->input_colonia_id ?? $pColoniaId ?? '') == $col->id ? 'selected' : '' }}>
                                        {{ $col->name }}
                                    </option>
                                    @endforeach
                                </optgroup>
                                @endforeach
                                <optgroup label="Otra">
                                    <option value="">Otra (escribir abajo)</option>
                                </optgroup>
                            </select>
                            <div style="margin-top:.4rem;">
                                <input type="text" name="input_colonia_raw" class="form-input"
                                       placeholder="Si no aparece en la lista, escribe la colonia aquí"
                                       value="{{ old('input_colonia_raw', $valuation->input_colonia_raw ?? '') }}"
                                       style="font-size:.83rem;">
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">Dirección del inmueble</label>
                            <input type="text" name="input_address" class="form-input"
                                   placeholder="Ej. Calle Moctezuma 45, Narvarte Poniente"
                                   value="{{ old('input_address', $valuation->input_address ?? $property?->address ?? '') }}">
                            <div class="form-hint">Se usará para centrar el mapa en el PDF — incluye calle y número</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Tipo de inmueble <span class="required">*</span></label>
                            <select name="input_type" class="form-select" x-model="propType">
                                @foreach(['apartment'=>'Departamento','house'=>'Casa','land'=>'Terreno','office'=>'Oficina'] as $val => $lbl)
                                <option value="{{ $val }}"
                                    {{ old('input_type', $valuation->input_type ?? $pType ?? 'apartment') === $val ? 'selected' : '' }}>
                                    {{ $lbl }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Condición del edificio (solo departamentos) --}}
                        <div class="form-group" x-show="propType === 'apartment'" x-transition>
                            <label class="form-label">Estado del edificio</label>
                            <select name="input_building_condition" class="form-select">
                                <option value="">— No especificado —</option>
                                @foreach(['excellent'=>'Excelente','good'=>'Bueno','fair'=>'Regular','poor'=>'Necesita remodelación'] as $val => $lbl)
                                <option value="{{ $val }}"
                                    {{ old('input_building_condition', $valuation->input_building_condition ?? '') === $val ? 'selected' : '' }}>
                                    {{ $lbl }}
                                </option>
                                @endforeach
                            </select>
                            <div class="form-hint">Fachada, áreas comunes, estructura del edificio</div>
                        </div>

                        {{-- Condición de la unidad (siempre visible) --}}
                        <div class="form-group">
                            <label class="form-label">
                                <span x-show="propType === 'apartment'">Estado del departamento</span>
                                <span x-show="propType !== 'apartment'">Estado de conservación</span>
                                <span class="required">*</span>
                            </label>
                            <select name="input_condition" class="form-select">
                                @foreach(['excellent'=>'Excelente / Remodelado','good'=>'Bueno','fair'=>'Regular','poor'=>'Necesita remodelación'] as $val => $lbl)
                                <option value="{{ $val }}"
                                    {{ old('input_condition', $valuation->input_condition ?? 'good') === $val ? 'selected' : '' }}>
                                    {{ $lbl }}
                                </option>
                                @endforeach
                            </select>
                            <div class="form-hint" x-show="propType === 'apartment'">Interior del departamento: acabados, instalaciones, estado general</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Superficie y antigüedad --}}
            <div class="card">
                <div class="card-header"><h3 class="card-title">Superficie y antigüedad</h3></div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">m² totales (terreno) <span class="required">*</span></label>
                            <input type="number" name="input_m2_total" class="form-input"
                                   min="10" max="5000" step="0.5"
                                   value="{{ old('input_m2_total', $valuation->input_m2_total ?? $pAreaTotal ?? '') }}"
                                   placeholder="Ej. 80">
                        </div>
                        <div class="form-group">
                            <label class="form-label">m² de construcción</label>
                            <input type="number" name="input_m2_const" class="form-input"
                                   min="10" max="5000" step="0.5"
                                   value="{{ old('input_m2_const', $valuation->input_m2_const ?? $pAreaPrivada ?? '') }}"
                                   placeholder="Dejar vacío si igual que total">
                            <div class="form-hint">Se usará para el cálculo si se especifica</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Antigüedad (años) <span class="required">*</span></label>
                            <input type="number" name="input_age_years" class="form-input {{ $errors->has('input_age_years') ? 'border-red-400' : '' }}"
                                   min="0" max="150"
                                   value="{{ old('input_age_years', $valuation->input_age_years ?? $pAge ?? '') }}"
                                   placeholder="Ej. 25">
                            @error('input_age_years')<div style="font-size:.75rem;color:#dc2626;margin-top:.2rem;">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Características --}}
            <div class="card">
                <div class="card-header"><h3 class="card-title">Características</h3></div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Recámaras <span class="required">*</span></label>
                            <input type="number" name="input_bedrooms" class="form-input"
                                   min="0" max="20"
                                   value="{{ old('input_bedrooms', $valuation->input_bedrooms ?? $pBedrooms ?? 2) }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Baños completos <span class="required">*</span></label>
                            <input type="number" name="input_bathrooms" class="form-input"
                                   min="0" max="20"
                                   value="{{ old('input_bathrooms', $valuation->input_bathrooms ?? $pBathrooms ?? 1) }}"
                                   placeholder="Ej. 2">
                            <div class="form-hint">Con regadera, W.C. y lavabo</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Medios baños</label>
                            <input type="number" name="input_half_bathrooms" class="form-input"
                                   min="0" max="10"
                                   value="{{ old('input_half_bathrooms', $valuation->input_half_bathrooms ?? 0) }}"
                                   placeholder="0">
                            <div class="form-hint">Solo W.C. y lavabo, sin regadera</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Cajones de estacionamiento <span class="required">*</span></label>
                            <input type="number" name="input_parking" class="form-input"
                                   id="parkingCount"
                                   min="0" max="10"
                                   value="{{ old('input_parking', $valuation->input_parking ?? $pParking ?? 0) }}"
                                   onchange="toggleParkingType(this.value)">
                        </div>

                        {{-- Tipo de estacionamiento --}}
                        <div class="form-group" id="parkingTypeGroup" style="{{ (old('input_parking', $valuation->input_parking ?? $pParking ?? 0)) > 0 ? '' : 'display:none;' }}">
                            <label class="form-label">Tipo de estacionamiento</label>
                            <div style="display:flex;flex-direction:column;gap:.4rem;margin-top:.35rem;">
                                @php
                                    $parkingTypeOptions = [
                                        'regular' => ['label'=>'Independiente (cajones regulares)','desc'=>'Cada cajón es accesible de forma independiente. Sin penalización.','color'=>'#16a34a'],
                                        'tandem'  => ['label'=>'En fila (tándem)','desc'=>'Un vehículo bloquea al otro — descuento -5% por inconveniencia percibida.','color'=>'#d97706'],
                                        'lift'    => ['label'=>'Eleva autos (elevador mecánico)','desc'=>'Menor percepción de calidad, riesgo mecánico y acceso lento — descuento -8%.','color'=>'#dc2626'],
                                    ];
                                @endphp
                                @foreach($parkingTypeOptions as $val => $cfg)
                                @php $checked = old('input_parking_type', $valuation->input_parking_type ?? 'regular') === $val; @endphp
                                <label style="display:flex;align-items:flex-start;gap:.65rem;cursor:pointer;padding:.5rem .7rem;border:1px solid {{ $checked ? $cfg['color'] : 'var(--border)' }};border-radius:var(--radius);background:{{ $checked ? $cfg['color'].'12' : 'var(--bg)' }};">
                                    <input type="radio" name="input_parking_type" value="{{ $val }}"
                                           {{ $checked ? 'checked' : '' }}
                                           style="margin-top:2px;accent-color:{{ $cfg['color'] }};">
                                    <div>
                                        <div style="font-size:.83rem;font-weight:600;color:var(--text);">{{ $cfg['label'] }}</div>
                                        <div style="font-size:.73rem;color:var(--text-muted);">{{ $cfg['desc'] }}</div>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Piso del inmueble</label>
                            <input type="number" name="input_floor" class="form-input {{ $errors->has('input_floor') ? 'border-red-400' : '' }}"
                                   min="1" max="50"
                                   value="{{ old('input_floor', $valuation->input_floor ?? '') }}"
                                   placeholder="Ej. 4">
                            @error('input_floor')<div style="font-size:.75rem;color:#dc2626;margin-top:.2rem;">{{ $message }}</div>@enderror
                            <div class="form-hint">Dejar vacío si es casa o dato desconocido</div>
                        </div>
                    </div>

                    {{-- Checkboxes --}}
                    <div style="margin-top:.75rem;display:grid;grid-template-columns:repeat(2,1fr);gap:.5rem;">
                        @foreach([
                            'input_has_elevator'    => 'Tiene elevador',
                            'input_has_rooftop'     => 'Rooftop privado',
                            'input_has_balcony'     => 'Balcón o terraza',
                            'input_has_service_room'=> 'Cuarto de servicio',
                            'input_has_storage'     => 'Bodega',
                        ] as $field => $label)
                        <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.85rem;padding:.35rem 0;">
                            <input type="hidden" name="{{ $field }}" value="0">
                            <input type="checkbox" name="{{ $field }}" value="1"
                                   {{ old($field, $valuation->{$field} ?? false) ? 'checked' : '' }}>
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>

                    {{-- Atributos específicos de departamento --}}
                    <div x-show="propType === 'apartment'" x-transition style="margin-top:1rem;border-top:1px solid var(--border);padding-top:1rem;">
                        <div style="font-size:0.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:.75rem;">
                            Atributos del departamento
                        </div>
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:.85rem;">

                            {{-- Posición --}}
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label">Posición</label>
                                <select name="input_unit_position" class="form-select">
                                    <option value="">— No especificada —</option>
                                    <option value="exterior" {{ old('input_unit_position', $valuation->input_unit_position ?? '') === 'exterior' ? 'selected' : '' }}>Exterior (vista a calle/jardín)</option>
                                    <option value="interior" {{ old('input_unit_position', $valuation->input_unit_position ?? '') === 'interior' ? 'selected' : '' }}>Interior (patio interior)</option>
                                </select>
                            </div>

                            {{-- Orientación --}}
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label">Orientación principal</label>
                                <select name="input_orientation" class="form-select">
                                    <option value="">— No especificada —</option>
                                    @foreach(['sur'=>'Sur ☀️ (mejor)','sureste'=>'Sureste','suroeste'=>'Suroeste','este'=>'Este','oeste'=>'Oeste','noreste'=>'Noreste','noroeste'=>'Noroeste','norte'=>'Norte (menos luz)'] as $val => $lbl)
                                    <option value="{{ $val }}" {{ old('input_orientation', $valuation->input_orientation ?? '') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                                <div class="form-hint">En CDMX la orientación sur tiene prima de mercado</div>
                            </div>
                        </div>

                        {{-- Daño sísmico --}}
                        <div class="form-group" style="margin-top:.85rem;margin-bottom:0;">
                            <label class="form-label">Historial sísmico del edificio</label>
                            <div style="display:grid;gap:.4rem;margin-top:.35rem;">
                                @foreach([
                                    'none'                => ['label'=>'Sin daño sísmico conocido','desc'=>'Sin impacto en valuación','color'=>'#16a34a'],
                                    'damaged_reinforced'  => ['label'=>'Dañado en sismo — reforzado estructuralmente','desc'=>'Descuento leve (-4%): el reforzamiento certifica la reparación pero persiste algo de percepción','color'=>'#d97706'],
                                    'damaged_repaired'    => ['label'=>'Dañado en sismo — reparado (sin reforzamiento)','desc'=>'Descuento moderado (-8%): factor psicológico de mercado','color'=>'#dc2626'],
                                    'unknown'             => ['label'=>'Se desconoce si tuvo daño','desc'=>'Descuento de precaución (-3%)','color'=>'#94a3b8'],
                                ] as $val => $cfg)
                                @php $checked = old('input_seismic_status', $valuation->input_seismic_status ?? 'none') === $val; @endphp
                                <label style="display:flex;align-items:flex-start;gap:.65rem;cursor:pointer;padding:.55rem .75rem;border:1px solid {{ $checked ? $cfg['color'] : 'var(--border)' }};border-radius:var(--radius);background:{{ $checked ? $cfg['color'].'12' : 'var(--bg)' }};">
                                    <input type="radio" name="input_seismic_status" value="{{ $val }}"
                                           {{ $checked ? 'checked' : '' }}
                                           style="margin-top:2px;accent-color:{{ $cfg['color'] }};">
                                    <div>
                                        <div style="font-size:.83rem;font-weight:600;color:var(--text);">{{ $cfg['label'] }}</div>
                                        <div style="font-size:.73rem;color:var(--text-muted);">{{ $cfg['desc'] }}</div>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Seguridad --}}
            <div class="card">
                <div class="card-header"><h3 class="card-title">Seguridad</h3></div>
                <div class="card-body">
                    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:.5rem;">
                        @foreach([
                            'input_has_doorman'           => 'Portero / Guardia 24h (+3.5%)',
                            'input_has_security_cameras'  => 'Cámaras de seguridad (+1.5%)',
                            'input_has_intercom'          => 'Intercomunicador / Videoportero (+1%)',
                            'input_has_alarm'             => 'Alarma (+0.5%)',
                        ] as $field => $label)
                        <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.85rem;padding:.35rem 0;">
                            <input type="hidden" name="{{ $field }}" value="0">
                            <input type="checkbox" name="{{ $field }}" value="1"
                                   {{ old($field, $valuation->{$field} ?? false) ? 'checked' : '' }}>
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Amenidades del edificio + Infraestructura --}}
            <div class="card">
                <div class="card-header"><h3 class="card-title">Amenidades del edificio e infraestructura</h3></div>
                <div class="card-body">
                    <div style="font-size:.76rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:.6rem;">
                        Amenidades comunales
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:.5rem;margin-bottom:1rem;">
                        @foreach([
                            'input_has_gym'   => 'Gimnasio (+3%)',
                            'input_has_pool'  => 'Alberca (+4%)',
                            'input_has_lobby' => 'Lobby / Recepción (+2%)',
                        ] as $field => $label)
                        <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.85rem;padding:.35rem 0;">
                            <input type="hidden" name="{{ $field }}" value="0">
                            <input type="checkbox" name="{{ $field }}" value="1"
                                   {{ old($field, $valuation->{$field} ?? false) ? 'checked' : '' }}>
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                    <div style="font-size:.76rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:.6rem;border-top:1px solid var(--border);padding-top:.85rem;">
                        Infraestructura básica
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:.5rem;">
                        @foreach([
                            'input_has_natural_gas' => 'Gas natural (red)',
                            'input_has_cistern'     => 'Cisterna propia',
                        ] as $field => $label)
                        <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.85rem;padding:.35rem 0;">
                            <input type="hidden" name="{{ $field }}" value="0">
                            <input type="checkbox" name="{{ $field }}" value="1"
                                   {{ old($field, $valuation->{$field} ?? false) ? 'checked' : '' }}>
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Entorno, vistas y estado legal --}}
            <div class="card">
                <div class="card-header"><h3 class="card-title">Entorno, vistas y situación legal</h3></div>
                <div class="card-body">
                    <div class="form-grid">

                        <div class="form-group">
                            <label class="form-label">Tipo de calle / entorno</label>
                            <select name="input_street_type" class="form-select">
                                <option value="">— No especificado —</option>
                                @foreach([
                                    'quiet'       => ['Calle tranquila / interior','Sin salida o privada, bajo tráfico. +2%','#16a34a'],
                                    'residential' => ['Calle residencial','Tráfico moderado, entorno habitacional. +1%','#2563eb'],
                                    'principal'   => ['Avenida principal','Alto tráfico, ruido. -2%','#dc2626'],
                                    'commercial'  => ['Zona comercial / concurrida','Comercios, tráfico alto. -1.5%','#d97706'],
                                    'dead_end'    => ['Callejón / cerrada sin infraestructura','Difícil acceso. -3%','#6b7280'],
                                ] as $val => [$lbl, $hint, $color])
                                <option value="{{ $val }}"
                                    {{ old('input_street_type', $valuation->input_street_type ?? '') === $val ? 'selected' : '' }}>
                                    {{ $lbl }} — {{ $hint }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Vistas principales</label>
                            <select name="input_views" class="form-select">
                                <option value="">— No especificado —</option>
                                @foreach([
                                    'city'     => 'Vista a la ciudad / panorámica (+5%)',
                                    'park'     => 'Vista a parque / área verde (+3.5%)',
                                    'garden'   => 'Vista a jardín (+2%)',
                                    'street'   => 'Vista a calle (+1%)',
                                    'interior' => 'Vista a patio interior (sin ajuste)',
                                ] as $val => $lbl)
                                <option value="{{ $val }}"
                                    {{ old('input_views', $valuation->input_views ?? '') === $val ? 'selected' : '' }}>
                                    {{ $lbl }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Estado legal</label>
                            <select name="input_legal_status" class="form-select">
                                <option value="">— No especificado —</option>
                                @foreach([
                                    'clear'        => ['Libre de gravámenes (escriturado)','Sin ajuste','#16a34a'],
                                    'mortgage'     => ['Con hipoteca / gravamen activo','-1.5%','#d97706'],
                                    'pending_deed' => ['Escrituración pendiente','-3%','#dc2626'],
                                    'unknown'      => ['Estado legal desconocido','-1%','#6b7280'],
                                ] as $val => [$lbl, $adj, $color])
                                <option value="{{ $val }}"
                                    {{ old('input_legal_status', $valuation->input_legal_status ?? '') === $val ? 'selected' : '' }}>
                                    {{ $lbl }} ({{ $adj }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group" x-show="propType === 'apartment'" x-transition>
                            <label class="form-label">Cuota de mantenimiento (MXN/mes)</label>
                            <input type="number" name="input_maintenance_fee" class="form-input"
                                   min="0" max="99999" step="100"
                                   value="{{ old('input_maintenance_fee', $valuation->input_maintenance_fee ?? '') }}"
                                   placeholder="Ej. 1800">
                            <div class="form-hint">Afecta la valuación si supera $1,500/mes</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Año de última remodelación</label>
                            <input type="number" name="input_renovation_year" class="form-input"
                                   min="1900" max="{{ date('Y') }}"
                                   value="{{ old('input_renovation_year', $valuation->input_renovation_year ?? '') }}"
                                   placeholder="Ej. 2019">
                            <div class="form-hint">Opcional — se incluye en el análisis narrativo</div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Notas --}}
            <div class="card">
                <div class="card-header"><h3 class="card-title">Notas internas</h3></div>
                <div class="card-body">
                    <textarea name="input_notes" class="form-textarea" rows="3"
                              placeholder="Observaciones del inmueble, contexto de la reunión, etc.">{{ old('input_notes', $valuation->input_notes ?? '') }}</textarea>
                </div>
            </div>

        </div>{{-- /columna principal --}}

        {{-- ══ SIDEBAR ══ --}}
        <div style="display:flex;flex-direction:column;gap:1.5rem;position:sticky;top:72px;">

            {{-- Acción --}}
            <div class="card">
                <div class="card-header"><h3 class="card-title">Calcular</h3></div>
                <div class="card-body" style="display:flex;flex-direction:column;gap:.6rem;">
                    <button type="submit" class="btn btn-primary" style="width:100%;"
                            :disabled="submitting"
                            :style="submitting ? 'opacity:.7;cursor:not-allowed;' : ''">
                        <span x-show="!submitting">{{ $editing ? '🔄 Recalcular valuación' : '📊 Calcular valuación' }}</span>
                        <span x-show="submitting" style="display:flex;align-items:center;justify-content:center;gap:.5rem;">
                            <svg style="width:16px;height:16px;animation:spin 1s linear infinite;" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="60" stroke-dashoffset="20" stroke-linecap="round"/>
                            </svg>
                            {{ $editing ? 'Recalculando…' : 'Calculando…' }}
                        </span>
                    </button>
                    @if($editing)
                    <a href="{{ route('admin.valuations.show', $valuation) }}" class="btn btn-outline" style="width:100%;text-align:center;">
                        Ver resultado actual
                    </a>
                    @endif
                    <p style="font-size:.76rem;color:#9ca3af;line-height:1.5;">
                        El cálculo usa el último precio de mercado registrado para la colonia seleccionada.
                    </p>
                </div>
            </div>

            {{-- Info del proceso --}}
            <div class="card" style="background:#f8fafc;">
                <div class="card-body" style="font-size:.8rem;color:#6b7280;line-height:1.6;">
                    <strong style="color:#374151;display:block;margin-bottom:.4rem;">¿Cómo funciona?</strong>
                    Se toma el precio base por m² de la colonia, luego se aplican ajustes por:
                    <ul style="margin:.4rem 0 0 1rem;display:flex;flex-direction:column;gap:.15rem;">
                        <li>Antigüedad y conservación</li>
                        <li>Baños completos y medios</li>
                        <li>Piso y elevador</li>
                        <li>Estacionamiento (tipo)</li>
                        <li>Amenidades (unidad + edificio)</li>
                        <li>Seguridad y vigilancia</li>
                        <li>Vistas y entorno</li>
                        <li>Estado legal</li>
                        <li>Mantenimiento mensual</li>
                        <li>Superficie</li>
                    </ul>
                </div>
            </div>

        </div>{{-- /sidebar --}}
    </div>
</form>
@endsection

@section('scripts')
<script>
function syncColoniaRaw(select) {
    // Si se selecciona una colonia del dropdown, limpiar el campo libre
    if (select.value) {
        document.querySelector('[name="input_colonia_raw"]').value = '';
    }
}

function toggleParkingType(count) {
    var el = document.getElementById('parkingTypeGroup');
    if (el) el.style.display = parseInt(count) > 0 ? '' : 'none';
}
</script>
@endsection
