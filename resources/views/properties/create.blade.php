@extends('layouts.app-sidebar')
@section('title', 'Nueva Propiedad')

@section('styles')
<style>
/* ===== STEPPER NAV ===== */
.step-nav {
    display: flex;
    gap: 0;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    margin-bottom: 1.5rem;
}
.step-nav-item {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.85rem 1rem;
    cursor: pointer;
    transition: all 0.2s;
    border-right: 1px solid var(--border);
    user-select: none;
    position: relative;
}
.step-nav-item:last-child { border-right: none; }
.step-nav-item:hover { background: var(--bg); }
.step-nav-item.active {
    background: linear-gradient(135deg, rgba(102,126,234,0.06), rgba(118,75,162,0.04));
}
.step-nav-item.active::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 2.5px;
    background: linear-gradient(90deg, var(--primary), var(--primary-dark, #764ba2));
    border-radius: 2px 2px 0 0;
}
.step-num {
    width: 28px; height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 700;
    background: var(--bg);
    color: var(--text-muted);
    border: 1.5px solid var(--border);
    flex-shrink: 0;
    transition: all 0.2s;
}
.step-nav-item.active .step-num {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
}
.step-nav-item.done .step-num {
    background: var(--success);
    color: #fff;
    border-color: var(--success);
}
.step-info { min-width: 0; }
.step-title {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.step-subtitle {
    font-size: 0.7rem;
    color: var(--text-muted);
}
.step-nav-item.active .step-title { color: var(--primary); }

/* ===== STEP PANELS ===== */
.step-panel { display: none; animation: stepFadeIn 0.25s ease; }
.step-panel.active { display: block; }
@keyframes stepFadeIn {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ===== TYPE SELECTOR ===== */
.type-selector {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.65rem;
    margin-bottom: 1.25rem;
}
@media (max-width: 768px) { .type-selector { grid-template-columns: repeat(2, 1fr); } }

.type-card {
    padding: 0.85rem 0.6rem;
    border: 2px solid var(--border);
    border-radius: var(--radius);
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    position: relative;
}
.type-card:hover { border-color: var(--primary); background: rgba(102,126,234,0.02); }
.type-card.selected {
    border-color: var(--primary);
    background: linear-gradient(135deg, rgba(102,126,234,0.06), rgba(118,75,162,0.04));
    box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
}
.type-card-icon {
    font-size: 1.5rem;
    margin-bottom: 0.3rem;
    display: block;
}
.type-card-label {
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--text);
}
.type-card .check-mark {
    position: absolute;
    top: 6px; right: 6px;
    width: 18px; height: 18px;
    border-radius: 50%;
    background: var(--primary);
    color: #fff;
    display: none;
    align-items: center;
    justify-content: center;
    font-size: 0.6rem;
    font-weight: 700;
}
.type-card.selected .check-mark { display: flex; }

/* ===== OP TYPE CARDS ===== */
.op-selector {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.65rem;
    margin-bottom: 1.25rem;
}
.op-card {
    padding: 0.75rem;
    border: 2px solid var(--border);
    border-radius: var(--radius);
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
}
.op-card:hover { border-color: var(--primary); }
.op-card.selected {
    border-color: var(--primary);
    background: linear-gradient(135deg, rgba(102,126,234,0.06), rgba(118,75,162,0.04));
    box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
}
.op-card-label { font-size: 0.82rem; font-weight: 600; color: var(--text); }
.op-card-sub { font-size: 0.7rem; color: var(--text-muted); margin-top: 0.15rem; }

/* ===== FEATURES GRID ===== */
.features-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.75rem;
}
@media (max-width: 768px) { .features-grid { grid-template-columns: repeat(2, 1fr); } }
.feature-card {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 0.75rem;
    text-align: center;
    transition: border-color 0.2s;
}
.feature-card:focus-within { border-color: var(--primary); }
.feature-icon { font-size: 1.2rem; margin-bottom: 0.25rem; }
.feature-card label {
    display: block;
    font-size: 0.75rem;
    font-weight: 500;
    color: var(--text-muted);
    margin-bottom: 0.35rem;
}
.feature-card input {
    width: 100%;
    text-align: center;
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text);
    border: none;
    background: transparent;
    outline: none;
    padding: 0.15rem;
}
.feature-card input::-webkit-inner-spin-button { -webkit-appearance: none; }
.feature-card input[type=number] { -moz-appearance: textfield; }

/* ===== PHOTO UPLOAD ===== */
.photo-upload-zone {
    border: 2px dashed var(--border);
    border-radius: var(--radius);
    padding: 2rem 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    background: var(--bg);
}
.photo-upload-zone:hover { border-color: var(--primary); background: rgba(102,126,234,0.02); }
.photo-upload-zone.dragover { border-color: var(--primary); background: rgba(102,126,234,0.05); }
.photo-upload-icon { font-size: 2rem; margin-bottom: 0.5rem; opacity: 0.5; }
.photo-upload-zone p { margin: 0; font-size: 0.85rem; color: var(--text-muted); }
.photo-upload-zone .hint { font-size: 0.75rem; margin-top: 0.25rem; }

.preview-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.5rem;
    margin-top: 1rem;
}
@media (max-width: 768px) { .preview-grid { grid-template-columns: repeat(3, 1fr); } }
.preview-item {
    aspect-ratio: 1;
    border-radius: var(--radius);
    overflow: hidden;
    position: relative;
    border: 2px solid var(--border);
}
.preview-item.is-primary { border-color: var(--primary); }
.preview-item img { width: 100%; height: 100%; object-fit: cover; }
.preview-remove {
    position: absolute; top: 4px; right: 4px;
    width: 20px; height: 20px;
    border-radius: 50%;
    background: rgba(0,0,0,0.6);
    color: #fff;
    border: none;
    font-size: 0.65rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}
.preview-primary-badge {
    position: absolute; bottom: 4px; left: 4px;
    font-size: 0.6rem;
    background: var(--primary);
    color: #fff;
    padding: 0.1rem 0.35rem;
    border-radius: 3px;
    font-weight: 600;
}

/* ===== YOUTUBE PREVIEW ===== */
.yt-preview {
    margin-top: 0.75rem;
    border-radius: var(--radius);
    overflow: hidden;
    position: relative;
    padding-bottom: 56.25%;
    height: 0;
    background: #000;
}
.yt-preview iframe {
    position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;
}

/* ===== PRICE INPUT ===== */
.price-wrap {
    position: relative;
}
.price-wrap .currency-tag {
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg);
    border-right: 1px solid var(--border);
    border-radius: var(--radius) 0 0 var(--radius);
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--text-muted);
    pointer-events: none;
}
.price-wrap .form-input { padding-left: 58px; }

/* ===== STEP ACTIONS ===== */
.step-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1.25rem;
    margin-top: 1.25rem;
    border-top: 1px solid var(--border);
}
.step-actions .btn { min-width: 120px; justify-content: center; }

/* ===== SECTION LABEL ===== */
.section-label {
    font-size: 0.78rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-muted);
    margin-bottom: 0.75rem;
}

/* ===== OWNER SEARCH ===== */
.owner-search-wrap { position: relative; }
.owner-search-results {
    position: absolute; top: 100%; left: 0; right: 0; z-index: 50;
    background: var(--card); border: 1px solid var(--border); border-radius: 0 0 var(--radius) var(--radius);
    max-height: 200px; overflow-y: auto; display: none; box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.owner-search-results.visible { display: block; }
.owner-result {
    padding: 0.5rem 0.75rem; cursor: pointer; font-size: 0.82rem; transition: background 0.1s;
    display: flex; justify-content: space-between; align-items: center;
}
.owner-result:hover { background: rgba(102,126,234,0.06); }
.owner-result-name { font-weight: 500; }
.owner-result-email { font-size: 0.72rem; color: var(--text-muted); }
.owner-selected {
    display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.75rem;
    background: rgba(102,126,234,0.06); border: 1px solid var(--primary); border-radius: var(--radius);
    font-size: 0.85rem;
}
.owner-selected .remove-owner {
    margin-left: auto; cursor: pointer; color: var(--text-muted); font-size: 0.9rem;
    background: none; border: none; padding: 0 0.25rem;
}
.owner-selected .remove-owner:hover { color: var(--danger); }

@media (max-width: 768px) {
    .step-nav { flex-direction: column; }
    .step-nav-item { border-right: none; border-bottom: 1px solid var(--border); }
    .step-nav-item:last-child { border-bottom: none; }
    .step-nav-item.active::after { bottom: auto; top: 0; left: 0; right: auto; width: 3px; height: 100%; border-radius: 0 2px 2px 0; }
}
</style>
@endsection

@section('content')
<div style="margin-bottom:1rem; display:flex; align-items:center; justify-content:space-between;">
    <div>
        <a href="{{ route('properties.index') }}" style="font-size:0.82rem; color:var(--text-muted);">&#8592; Propiedades</a>
        <h2 style="margin:0.25rem 0 0; font-size:1.15rem; font-weight:600;">Nueva Propiedad</h2>
    </div>
</div>

@if($errors->any())
<div style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); border-radius:var(--radius); padding:0.75rem 1rem; margin-bottom:1.25rem;">
    @foreach($errors->all() as $error)
        <p style="color:var(--danger); font-size:0.82rem; margin:0.15rem 0;">{{ $error }}</p>
    @endforeach
</div>
@endif

{{-- Step Navigation --}}
<div class="step-nav" id="stepNav">
    <div class="step-nav-item active" onclick="goStep(1)">
        <span class="step-num">1</span>
        <div class="step-info">
            <div class="step-title">Tipo y Operacion</div>
            <div class="step-subtitle">Que tipo de propiedad es</div>
        </div>
    </div>
    <div class="step-nav-item" onclick="goStep(2)">
        <span class="step-num">2</span>
        <div class="step-info">
            <div class="step-title">Detalles</div>
            <div class="step-subtitle">Precio, ubicacion, broker</div>
        </div>
    </div>
    <div class="step-nav-item" onclick="goStep(3)">
        <span class="step-num">3</span>
        <div class="step-info">
            <div class="step-title">Caracteristicas</div>
            <div class="step-subtitle">Recamaras, banos, area</div>
        </div>
    </div>
    <div class="step-nav-item" onclick="goStep(4)">
        <span class="step-num">4</span>
        <div class="step-info">
            <div class="step-title">Media</div>
            <div class="step-subtitle">Fotos, video, descripcion</div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('properties.store') }}" enctype="multipart/form-data" id="propertyForm">
    @csrf

    {{-- ===== STEP 1: Type & Operation ===== --}}
    <div class="step-panel active" id="step1">
        <div class="card">
            <div class="card-body">
                <div class="section-label">Tipo de propiedad</div>
                <div class="type-selector">
                    @foreach([
                        'House' => ['Casa', '&#127968;'],
                        'Apartment' => ['Departamento', '&#127959;'],
                        'Land' => ['Terreno', '&#127966;'],
                        'Office' => ['Oficina', '&#128188;'],
                        'Commercial' => ['Comercial', '&#127978;'],
                        'Warehouse' => ['Bodega', '&#127981;'],
                        'Building' => ['Edificio', '&#127970;'],
                    ] as $val => [$label, $icon])
                    <div class="type-card {{ old('property_type', 'House') === $val ? 'selected' : '' }}" onclick="selectType(this, '{{ $val }}')">
                        <span class="check-mark">&#10003;</span>
                        <span class="type-card-icon">{!! $icon !!}</span>
                        <span class="type-card-label">{{ $label }}</span>
                    </div>
                    @endforeach
                </div>
                <input type="hidden" name="property_type" id="propertyType" value="{{ old('property_type', 'House') }}">

                <div class="section-label" style="margin-top:1.5rem;">Tipo de operacion</div>
                <div class="op-selector">
                    <div class="op-card {{ old('operation_type', 'sale') === 'sale' ? 'selected' : '' }}" onclick="selectOp(this, 'sale')">
                        <div class="op-card-label">Venta</div>
                        <div class="op-card-sub">Venta definitiva</div>
                    </div>
                    <div class="op-card {{ old('operation_type') === 'rental' ? 'selected' : '' }}" onclick="selectOp(this, 'rental')">
                        <div class="op-card-label">Renta</div>
                        <div class="op-card-sub">Arrendamiento mensual</div>
                    </div>
                    <div class="op-card {{ old('operation_type') === 'temporary_rental' ? 'selected' : '' }}" onclick="selectOp(this, 'temporary_rental')">
                        <div class="op-card-label">Renta Temporal</div>
                        <div class="op-card-sub">Corto plazo / vacacional</div>
                    </div>
                </div>
                <input type="hidden" name="operation_type" id="operationType" value="{{ old('operation_type', 'sale') }}">

                <div class="step-actions">
                    <div></div>
                    <button type="button" class="btn btn-primary" onclick="goStep(2)">Siguiente &rarr;</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== STEP 2: Details ===== --}}
    <div class="step-panel" id="step2">
        <div class="card">
            <div class="card-body">
                <div class="section-label">Informacion principal</div>
                <div class="form-group">
                    <label class="form-label">Titulo del anuncio <span class="required">*</span></label>
                    <input type="text" name="title" class="form-input" value="{{ old('title') }}" required placeholder="Ej: Hermosa casa en la zona dorada">
                    @error('title') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Precio <span class="required">*</span></label>
                        <div class="price-wrap">
                            <span class="currency-tag" id="currTag">$</span>
                            <input type="number" name="price" class="form-input" value="{{ old('price') }}" required step="0.01" min="0" placeholder="0.00">
                        </div>
                        @error('price') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Moneda</label>
                        <select name="currency" class="form-select" id="currSelect" onchange="updateCurrency()">
                            <option value="MXN" {{ old('currency') === 'USD' ? '' : 'selected' }}>MXN — Peso Mexicano</option>
                            <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD — Dolar</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Estado</label>
                        <select name="status" class="form-select">
                            <option value="available" {{ old('status', 'available') === 'available' ? 'selected' : '' }}>Disponible</option>
                            <option value="sold" {{ old('status') === 'sold' ? 'selected' : '' }}>Vendido</option>
                            <option value="rented" {{ old('status') === 'rented' ? 'selected' : '' }}>Rentado</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Broker</label>
                        <select name="broker_id" class="form-select">
                            <option value="">Sin asignar</option>
                            @foreach($brokers as $broker)
                                <option value="{{ $broker->id }}" {{ old('broker_id') == $broker->id ? 'selected' : '' }}>{{ $broker->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="section-label" style="margin-top:1.25rem;">Propietario</div>
                <div class="form-group">
                    <input type="hidden" name="client_id" id="ownerIdInput" value="{{ old('client_id') }}">
                    <div id="ownerDisplay" style="{{ old('client_id') ? '' : 'display:none;' }}">
                        <div class="owner-selected">
                            <span id="ownerName">{{ old('client_id') ? \App\Models\Client::find(old('client_id'))?->name : '' }}</span>
                            <button type="button" class="remove-owner" onclick="clearOwner()">&times;</button>
                        </div>
                    </div>
                    <div id="ownerSearchWrap" class="owner-search-wrap" style="{{ old('client_id') ? 'display:none;' : '' }}">
                        <input type="text" class="form-input" id="ownerSearchInput" placeholder="Buscar cliente por nombre, email o telefono..." autocomplete="off" oninput="searchOwner(this.value)">
                        <div class="owner-search-results" id="ownerResults"></div>
                    </div>
                    <p class="form-hint">Cliente dueno de la propiedad (opcional)</p>
                </div>

                <div class="section-label" style="margin-top:1.25rem;">Ubicacion</div>
                <div class="form-group">
                    <label class="form-label">Direccion</label>
                    <input type="text" name="address" class="form-input" value="{{ old('address') }}" placeholder="Calle, numero, interior...">
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Ciudad</label>
                        <input type="text" name="city" class="form-input" value="{{ old('city') }}" placeholder="Ej: Guadalajara">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Colonia</label>
                        <input type="text" name="colony" class="form-input" value="{{ old('colony') }}" placeholder="Ej: Providencia">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Codigo Postal</label>
                        <input type="text" name="zipcode" class="form-input" value="{{ old('zipcode') }}" maxlength="10" placeholder="44600">
                    </div>
                </div>

                <div class="step-actions">
                    <button type="button" class="btn btn-outline" onclick="goStep(1)">&larr; Anterior</button>
                    <button type="button" class="btn btn-primary" onclick="goStep(3)">Siguiente &rarr;</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== STEP 3: Features ===== --}}
    <div class="step-panel" id="step3">
        <div class="card">
            <div class="card-body">
                <div class="section-label">Espacios</div>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">&#128716;</div>
                        <label>Recamaras</label>
                        <input type="number" name="bedrooms" value="{{ old('bedrooms') }}" min="0" placeholder="0">
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">&#128705;</div>
                        <label>Banos completos</label>
                        <input type="number" name="bathrooms" value="{{ old('bathrooms') }}" min="0" placeholder="0">
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">&#128704;</div>
                        <label>Medios banos</label>
                        <input type="number" name="half_bathrooms" value="{{ old('half_bathrooms') }}" min="0" placeholder="0">
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">&#128663;</div>
                        <label>Estacionamiento</label>
                        <input type="number" name="parking" value="{{ old('parking') }}" min="0" placeholder="0">
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">&#127970;</div>
                        <label>Pisos / Niveles</label>
                        <input type="number" name="floors" value="{{ old('floors') }}" min="0" placeholder="0">
                    </div>
                </div>

                <div class="section-label" style="margin-top:1.25rem;">Superficies</div>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">&#128207;</div>
                        <label>Terreno m&sup2;</label>
                        <input type="number" name="lot_area" value="{{ old('lot_area') }}" min="0" step="0.01" placeholder="0">
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">&#128208;</div>
                        <label>Construccion m&sup2;</label>
                        <input type="number" name="construction_area" value="{{ old('construction_area') }}" min="0" step="0.01" placeholder="0">
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">&#128209;</div>
                        <label>Area total m&sup2;</label>
                        <input type="number" name="area" value="{{ old('area') }}" min="0" step="0.01" placeholder="0">
                    </div>
                </div>

                <div class="section-label" style="margin-top:1.25rem;">Detalles adicionales</div>
                <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:0.75rem;">
                    <div class="form-group">
                        <label class="form-label">Ano de construccion</label>
                        <input type="number" name="year_built" class="form-input" value="{{ old('year_built') }}" min="1900" max="{{ date('Y') }}" placeholder="{{ date('Y') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mantenimiento mensual</label>
                        <input type="number" name="maintenance_fee" class="form-input" value="{{ old('maintenance_fee') }}" min="0" step="0.01" placeholder="$0.00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Amueblado</label>
                        <select name="furnished" class="form-input">
                            <option value="">-- Seleccionar --</option>
                            <option value="sin_amueblar" {{ old('furnished') === 'sin_amueblar' ? 'selected' : '' }}>Sin amueblar</option>
                            <option value="semi_amueblado" {{ old('furnished') === 'semi_amueblado' ? 'selected' : '' }}>Semi amueblado</option>
                            <option value="amueblado" {{ old('furnished') === 'amueblado' ? 'selected' : '' }}>Amueblado</option>
                        </select>
                    </div>
                </div>

                <div class="section-label" style="margin-top:1.25rem;">Amenidades</div>
                <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:0.4rem 1rem;">
                    @foreach([
                        'alberca' => 'Alberca',
                        'jardin' => 'Jardin',
                        'gym' => 'Gimnasio',
                        'roof_garden' => 'Roof Garden',
                        'elevador' => 'Elevador',
                        'seguridad' => 'Seguridad 24/7',
                        'area_juegos' => 'Area de juegos',
                        'salon_eventos' => 'Salon de eventos',
                        'bodega' => 'Bodega',
                        'cuarto_servicio' => 'Cuarto de servicio',
                        'cocina_integral' => 'Cocina integral',
                        'aire_acondicionado' => 'Aire acondicionado',
                        'calefaccion' => 'Calefaccion',
                        'terraza' => 'Terraza',
                        'balcon' => 'Balcon',
                    ] as $key => $label)
                    <label style="display:flex; align-items:center; gap:0.4rem; font-size:0.85rem; cursor:pointer; padding:0.3rem 0;">
                        <input type="checkbox" name="amenities[]" value="{{ $key }}" {{ in_array($key, old('amenities', [])) ? 'checked' : '' }}>
                        {{ $label }}
                    </label>
                    @endforeach
                </div>

                <div class="step-actions">
                    <button type="button" class="btn btn-outline" onclick="goStep(2)">&larr; Anterior</button>
                    <button type="button" class="btn btn-primary" onclick="goStep(4)">Siguiente &rarr;</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== STEP 4: Media ===== --}}
    <div class="step-panel" id="step4">
        <div class="card">
            <div class="card-body">
                <div class="section-label">Fotografias <span style="font-weight:400; font-size:0.72rem; color:var(--text-muted);">(max 20, la primera sera la principal)</span></div>
                <div class="photo-upload-zone" id="photoZone" onclick="document.getElementById('photoInput').click()">
                    <input type="file" id="photoInput" name="photos[]" accept="image/*" multiple style="display:none" onchange="previewPhotos(this)">
                    <div id="photoPlaceholder">
                        <div class="photo-upload-icon">&#128247;</div>
                        <p>Arrastra o haz clic para subir fotos</p>
                        <p class="hint">JPG, PNG, GIF, WebP — max 5MB cada una, hasta 20 fotos</p>
                    </div>
                </div>
                <div class="preview-grid" id="previewGrid" style="display:none;"></div>

                <div class="section-label" style="margin-top:1.5rem;">Video de YouTube</div>
                <div class="form-group">
                    <input type="url" name="youtube_url" id="youtubeInput" class="form-input" value="{{ old('youtube_url') }}" placeholder="https://www.youtube.com/watch?v=..." oninput="previewYoutube()">
                    @error('youtube_url') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                </div>
                <div id="ytPreviewContainer"></div>

                <div class="section-label" style="margin-top:1.5rem;">Descripcion</div>
                <div class="form-group">
                    <textarea name="description" class="form-textarea" rows="5" placeholder="Describe la propiedad: ambientes, acabados, amenidades, ubicacion...">{{ old('description') }}</textarea>
                </div>

                <div class="step-actions">
                    <button type="button" class="btn btn-outline" onclick="goStep(3)">&larr; Anterior</button>
                    <button type="submit" class="btn btn-primary">Crear Propiedad</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
var currentStep = 1;
var totalSteps = 4;

function goStep(n) {
    if (n < 1 || n > totalSteps) return;
    // Validate required fields before advancing
    if (n > currentStep) {
        var panel = document.getElementById('step' + currentStep);
        var required = panel.querySelectorAll('[required]');
        for (var i = 0; i < required.length; i++) {
            if (!required[i].value.trim()) {
                required[i].focus();
                required[i].style.borderColor = 'var(--danger)';
                setTimeout(function(el) { el.style.borderColor = ''; }.bind(null, required[i]), 2000);
                return;
            }
        }
    }

    currentStep = n;

    // Update panels
    document.querySelectorAll('.step-panel').forEach(function(p, i) {
        p.classList.toggle('active', i + 1 === n);
    });

    // Update nav
    document.querySelectorAll('.step-nav-item').forEach(function(item, i) {
        item.classList.remove('active', 'done');
        if (i + 1 === n) item.classList.add('active');
        else if (i + 1 < n) item.classList.add('done');
    });

    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function selectType(card, val) {
    document.querySelectorAll('.type-card').forEach(function(c) { c.classList.remove('selected'); });
    card.classList.add('selected');
    document.getElementById('propertyType').value = val;
}

function selectOp(card, val) {
    document.querySelectorAll('.op-card').forEach(function(c) { c.classList.remove('selected'); });
    card.classList.add('selected');
    document.getElementById('operationType').value = val;
}

function updateCurrency() {
    var sel = document.getElementById('currSelect');
    document.getElementById('currTag').textContent = sel.value === 'USD' ? 'US$' : '$';
}

var selectedFiles = [];
function previewPhotos(input) {
    if (!input.files || input.files.length === 0) return;
    for (var i = 0; i < input.files.length && selectedFiles.length < 20; i++) {
        selectedFiles.push(input.files[i]);
    }
    renderPreviews();
}
function renderPreviews() {
    var grid = document.getElementById('previewGrid');
    var placeholder = document.getElementById('photoPlaceholder');
    if (selectedFiles.length === 0) {
        grid.style.display = 'none';
        placeholder.style.display = '';
        return;
    }
    placeholder.style.display = 'none';
    grid.style.display = '';
    grid.innerHTML = '';
    selectedFiles.forEach(function(file, idx) {
        var div = document.createElement('div');
        div.className = 'preview-item' + (idx === 0 ? ' is-primary' : '');
        var img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        div.appendChild(img);
        if (idx === 0) {
            div.innerHTML += '<span class="preview-primary-badge">Principal</span>';
        }
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'preview-remove';
        btn.innerHTML = '&#10005;';
        btn.onclick = function() { removePhoto(idx); };
        div.appendChild(btn);
        grid.appendChild(div);
    });
    // Add "add more" tile
    if (selectedFiles.length < 20) {
        var addTile = document.createElement('div');
        addTile.className = 'preview-item';
        addTile.style.cssText = 'display:flex; align-items:center; justify-content:center; cursor:pointer; background:var(--bg); border-style:dashed;';
        addTile.innerHTML = '<span style="font-size:1.5rem; color:var(--text-muted);">+</span>';
        addTile.onclick = function() { document.getElementById('photoInput').click(); };
        grid.appendChild(addTile);
    }
    rebuildFileInput();
}
function removePhoto(idx) {
    selectedFiles.splice(idx, 1);
    renderPreviews();
}
function rebuildFileInput() {
    var dt = new DataTransfer();
    selectedFiles.forEach(function(f) { dt.items.add(f); });
    document.getElementById('photoInput').files = dt.files;
}

function previewYoutube() {
    var url = document.getElementById('youtubeInput').value;
    var container = document.getElementById('ytPreviewContainer');
    var match = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([\w-]{11})/);
    if (match) {
        container.innerHTML = '<div class="yt-preview"><iframe src="https://www.youtube.com/embed/' + match[1] + '" allowfullscreen></iframe></div>';
    } else {
        container.innerHTML = '';
    }
}

// Drag & drop for main photo
(function() {
    var zone = document.getElementById('photoZone');
    if (!zone) return;
    ['dragenter','dragover'].forEach(function(ev) {
        zone.addEventListener(ev, function(e) { e.preventDefault(); zone.classList.add('dragover'); });
    });
    ['dragleave','drop'].forEach(function(ev) {
        zone.addEventListener(ev, function(e) { e.preventDefault(); zone.classList.remove('dragover'); });
    });
    zone.addEventListener('drop', function(e) {
        var input = document.getElementById('photoInput');
        for (var i = 0; i < e.dataTransfer.files.length && selectedFiles.length < 20; i++) {
            selectedFiles.push(e.dataTransfer.files[i]);
        }
        renderPreviews();
    });
})();

// Init currency tag
updateCurrency();
// Init YouTube preview on load
if (document.getElementById('youtubeInput').value) previewYoutube();

// Owner search
var ownerSearchTimer;
function searchOwner(q) {
    clearTimeout(ownerSearchTimer);
    var results = document.getElementById('ownerResults');
    if (q.length < 2) { results.classList.remove('visible'); return; }
    ownerSearchTimer = setTimeout(function() {
        fetch('{{ route("api.clients.search") }}?q=' + encodeURIComponent(q), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(clients) {
            if (clients.length === 0) {
                results.innerHTML = '<div class="owner-result" style="color:var(--text-muted); cursor:default;">Sin resultados</div>';
            } else {
                results.innerHTML = clients.map(function(c) {
                    return '<div class="owner-result" onclick="selectOwner(' + c.id + ', \'' + c.name.replace(/'/g, "\\'") + '\')">'
                        + '<span class="owner-result-name">' + c.name + '</span>'
                        + '<span class="owner-result-email">' + (c.email || c.phone || '') + '</span>'
                        + '</div>';
                }).join('');
            }
            results.classList.add('visible');
        });
    }, 300);
}
function selectOwner(id, name) {
    document.getElementById('ownerIdInput').value = id;
    document.getElementById('ownerName').textContent = name;
    document.getElementById('ownerDisplay').style.display = '';
    document.getElementById('ownerSearchWrap').style.display = 'none';
    document.getElementById('ownerResults').classList.remove('visible');
}
function clearOwner() {
    document.getElementById('ownerIdInput').value = '';
    document.getElementById('ownerDisplay').style.display = 'none';
    document.getElementById('ownerSearchWrap').style.display = '';
    document.getElementById('ownerSearchInput').value = '';
}
// Close results on click outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.owner-search-wrap')) {
        document.getElementById('ownerResults').classList.remove('visible');
    }
});
</script>
@endsection
