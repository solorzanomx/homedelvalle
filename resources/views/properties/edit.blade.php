@extends('layouts.app-sidebar')
@section('title', 'Editar Propiedad')

@section('styles')
<style>
/* Layout */
.edit-layout { display: grid; grid-template-columns: 1fr 320px; gap: 1.25rem; align-items: start; }
@media (max-width: 1024px) { .edit-layout { grid-template-columns: 1fr; } }

/* Tab nav — segmented control matching users module */
.tab-pills {
    display: flex; gap: 2px; background: var(--bg); border-radius: 8px; padding: 3px;
    border: 1px solid var(--border); overflow-x: auto; margin-bottom: 1.25rem;
}
.tab-pill {
    padding: 0.4rem 0.85rem; border-radius: 6px; font-size: 0.78rem; font-weight: 500;
    border: none; background: transparent; color: var(--text-muted);
    cursor: pointer; white-space: nowrap; transition: all 0.15s;
}
.tab-pill:hover { color: var(--text); }
.tab-pill.active { background: var(--card); color: var(--primary); font-weight: 600; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }

/* Tab panels */
.tab-panel { display: none; animation: panelFade 0.2s ease; }
.tab-panel.active { display: block; }
@keyframes panelFade { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }

/* Type selector — card style matching create wizard */
.type-selector { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem; margin-bottom: 1rem; }
@media (max-width: 768px) { .type-selector { grid-template-columns: repeat(2, 1fr); } }
.type-card {
    padding: 0.65rem 0.5rem; border: 2px solid var(--border); border-radius: var(--radius);
    text-align: center; cursor: pointer; transition: all 0.2s; position: relative;
}
.type-card:hover { border-color: var(--primary); }
.type-card.selected { border-color: var(--primary); background: rgba(102,126,234,0.04); box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
.type-card-icon { font-size: 1.25rem; display: block; margin-bottom: 0.15rem; }
.type-card-label { font-size: 0.72rem; font-weight: 600; }
.type-card .check-mark {
    position: absolute; top: 4px; right: 4px; width: 16px; height: 16px;
    border-radius: 50%; background: var(--primary); color: #fff;
    display: none; align-items: center; justify-content: center; font-size: 0.55rem; font-weight: 700;
}
.type-card.selected .check-mark { display: flex; }

/* Op selector */
.op-selector { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; margin-bottom: 1rem; }
.op-card {
    padding: 0.6rem; border: 2px solid var(--border); border-radius: var(--radius);
    text-align: center; cursor: pointer; transition: all 0.2s;
}
.op-card:hover { border-color: var(--primary); }
.op-card.selected { border-color: var(--primary); background: rgba(102,126,234,0.04); box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
.op-card-label { font-size: 0.82rem; font-weight: 600; }
.op-card-sub { font-size: 0.68rem; color: var(--text-muted); margin-top: 0.1rem; }

/* Feature cards — matching index card grid style */
.features-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem; }
@media (max-width: 768px) { .features-grid { grid-template-columns: repeat(2, 1fr); } }
.feature-card {
    background: var(--bg); border: 1px solid var(--border); border-radius: var(--radius);
    padding: 0.65rem; text-align: center; transition: border-color 0.2s;
}
.feature-card:focus-within { border-color: var(--primary); }
.feature-icon { font-size: 1.1rem; margin-bottom: 0.2rem; }
.feature-card label { display: block; font-size: 0.72rem; font-weight: 500; color: var(--text-muted); margin-bottom: 0.25rem; }
.feature-card input {
    width: 100%; text-align: center; font-size: 1rem; font-weight: 700;
    border: none; background: transparent; outline: none; padding: 0.1rem; color: var(--text);
}
.feature-card input::-webkit-inner-spin-button { -webkit-appearance: none; }
.feature-card input[type=number] { -moz-appearance: textfield; }

/* Price wrap */
.price-wrap { position: relative; }
.price-wrap .currency-tag {
    position: absolute; left: 0; top: 0; bottom: 0; width: 48px;
    display: flex; align-items: center; justify-content: center;
    background: var(--bg); border-right: 1px solid var(--border);
    border-radius: var(--radius) 0 0 var(--radius);
    font-size: 0.82rem; font-weight: 600; color: var(--text-muted); pointer-events: none;
}
.price-wrap .form-input { padding-left: 56px; }

/* Section label */
.section-label {
    font-size: 0.75rem; font-weight: 600; text-transform: uppercase;
    letter-spacing: 0.04em; color: var(--text-muted); margin-bottom: 0.65rem;
}

/* Photos panel */
.photos-panel { position: sticky; top: 72px; }
.photo-drop {
    border: 2px dashed var(--border); border-radius: var(--radius);
    padding: 1.25rem 0.75rem; text-align: center; cursor: pointer;
    transition: all 0.2s; margin-bottom: 0.75rem; position: relative; overflow: hidden;
}
.photo-drop:hover, .photo-drop.dragover { border-color: var(--primary); background: rgba(102,126,234,0.04); }
.photo-drop.dragover { transform: scale(1.01); }

/* Photo Grid */
.photo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 8px;
}
.photo-grid-item {
    position: relative; border-radius: 8px; overflow: hidden;
    aspect-ratio: 4/3; border: 2px solid var(--border);
    cursor: grab; transition: border-color 0.2s, box-shadow 0.2s, transform 0.15s;
    background: var(--bg);
}
.photo-grid-item:active { cursor: grabbing; }
.photo-grid-item.is-primary {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(102,126,234,0.15);
}
.photo-grid-item img {
    width: 100%; height: 100%; object-fit: cover; display: block;
    transition: transform 0.35s ease;
}
.photo-grid-item:hover img { transform: scale(1.06); }

/* Portada badge */
.photo-primary-badge {
    position: absolute; top: 5px; left: 5px;
    background: var(--primary); color: #fff;
    font-size: 0.58rem; font-weight: 700;
    padding: 2px 7px; border-radius: 3px;
    z-index: 3; pointer-events: none;
    box-shadow: 0 1px 4px rgba(0,0,0,0.2);
    display: flex; align-items: center; gap: 2px;
}
.photo-order-num {
    position: absolute; top: 5px; right: 5px;
    background: rgba(0,0,0,0.52); color: #fff;
    font-size: 0.57rem; font-weight: 700;
    width: 18px; height: 18px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    z-index: 3; pointer-events: none;
}

/* Hover overlay */
.photo-grid-overlay {
    position: absolute; inset: 0;
    background: rgba(0,0,0,0.52);
    display: flex; align-items: center; justify-content: center; gap: 7px;
    opacity: 0; transition: opacity 0.2s; z-index: 2;
}
.photo-grid-item:hover .photo-grid-overlay { opacity: 1; }
.photo-overlay-btn {
    width: 32px; height: 32px; border-radius: 50%;
    border: 2px solid rgba(255,255,255,0.75);
    background: rgba(255,255,255,0.12); color: #fff;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    font-size: 0.78rem; transition: all 0.15s;
    backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);
}
.photo-overlay-btn:hover { background: rgba(255,255,255,0.28); border-color: #fff; }
.photo-overlay-btn.star-active { color: #fbbf24; border-color: #fbbf24; }
.photo-overlay-btn.btn-del-overlay:hover { background: rgba(239,68,68,0.38); border-color: rgba(239,68,68,0.85); }

/* Description on hover */
.photo-desc-row {
    position: absolute; bottom: 0; left: 0; right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,0.68) 40%);
    padding: 22px 6px 5px; z-index: 2;
    transform: translateY(100%); transition: transform 0.22s ease;
}
.photo-grid-item:hover .photo-desc-row { transform: translateY(0); }
.photo-desc-input-sm {
    width: 100%; border: none; border-radius: 3px;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);
    color: #fff; font-size: 0.62rem; padding: 3px 6px; outline: none;
}
.photo-desc-input-sm::placeholder { color: rgba(255,255,255,0.55); }
.photo-desc-input-sm:focus { background: rgba(255,255,255,0.25); }

/* Sortable states */
.photo-grid-item.sortable-ghost { opacity: 0.35; border-style: dashed; }
.photo-grid-item.sortable-chosen { box-shadow: 0 6px 24px rgba(0,0,0,0.18); z-index: 10; transform: scale(1.04); }

/* Skeleton upload placeholder */
.photo-skeleton {
    position: relative; border-radius: 8px; aspect-ratio: 4/3;
    border: 2px dashed rgba(102,126,234,0.45);
    background: rgba(102,126,234,0.04); overflow: hidden;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center; gap: 6px;
}
.photo-skeleton::after {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(90deg, transparent 0%, rgba(102,126,234,0.1) 50%, transparent 100%);
    animation: skeletonShimmer 1.4s ease-in-out infinite;
}
@keyframes skeletonShimmer {
    from { transform: translateX(-100%); }
    to   { transform: translateX(100%); }
}
.photo-skeleton-spinner {
    width: 22px; height: 22px;
    border: 2px solid rgba(102,126,234,0.2);
    border-top-color: var(--primary); border-radius: 50%;
    animation: photoSpin 0.7s linear infinite; z-index: 1;
}
.photo-skeleton-label { font-size: 0.62rem; color: var(--primary); font-weight: 500; z-index: 1; max-width: 90%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.photo-skeleton-error { font-size: 0.62rem; color: var(--danger); font-weight: 500; z-index: 1; }

/* Spinner (drop zone) */
.photo-spinner {
    width: 24px; height: 24px; border: 2px solid var(--border);
    border-top-color: var(--primary); border-radius: 50%;
    animation: photoSpin 0.6s linear infinite; margin: 0 auto;
}
@keyframes photoSpin { to { transform: rotate(360deg); } }

/* Lightbox */
.photo-lightbox {
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(0,0,0,0.92);
    display: flex; align-items: center; justify-content: center;
    opacity: 0; pointer-events: none; transition: opacity 0.22s;
}
.photo-lightbox.open { opacity: 1; pointer-events: all; }
.photo-lightbox-img {
    max-width: 92vw; max-height: 88vh; object-fit: contain;
    border-radius: 8px; box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    transform: scale(0.9); transition: transform 0.22s ease;
    display: block;
}
.photo-lightbox.open .photo-lightbox-img { transform: scale(1); }
.photo-lightbox-close {
    position: absolute; top: 14px; right: 18px;
    color: #fff; font-size: 2rem; cursor: pointer; line-height: 1;
    opacity: 0.7; transition: opacity 0.15s;
    background: none; border: none; padding: 4px 8px;
}
.photo-lightbox-close:hover { opacity: 1; }

/* Side card */
.side-card { background: var(--card); border: 1px solid var(--border); border-radius: 10px; margin-bottom: 0.75rem; overflow: hidden; }
.side-card-header { padding: 0.7rem 1rem; border-bottom: 1px solid var(--border); font-weight: 600; font-size: 0.85rem; }
.side-card-body { padding: 0.85rem 1rem; }

/* EB section */
.eb-section { padding: 0.75rem; background: var(--bg); border-radius: var(--radius); border: 1px solid var(--border); }
.eb-status { display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.4rem; }
.eb-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
.eb-actions { display: flex; gap: 0.4rem; margin-top: 0.5rem; }

/* YT preview */
.yt-preview { border-radius: var(--radius); overflow: hidden; position: relative; padding-bottom: 40%; height: 0; background: #000; max-width: 480px; }
.yt-preview iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; }

/* Save bar */
.save-bar {
    background: var(--card); border: 1px solid var(--border); border-radius: 10px;
    padding: 0.75rem 1.25rem; margin-top: 1rem;
    display: flex; justify-content: space-between; align-items: center;
    position: sticky; bottom: 0; z-index: 10;
}
.save-bar-meta { font-size: 0.72rem; color: var(--text-muted); }
.save-toast {
    position: fixed; top: 16px; right: 16px; z-index: 1001;
    background: var(--success); color: #fff; padding: 0.6rem 1.25rem;
    border-radius: 8px; font-size: 0.82rem; font-weight: 500;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15); display: none;
    animation: toastIn 0.3s ease;
}
.save-toast.show { display: block; }
@keyframes toastIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

/* Owner search */
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
</style>
@endsection

@section('content')
@php
    $types = ['House'=>['Casa','&#127968;'],'Apartment'=>['Depto','&#127959;'],'Land'=>['Terreno','&#127966;'],'Office'=>['Oficina','&#128188;'],'Commercial'=>['Comercial','&#127978;'],'Warehouse'=>['Bodega','&#127981;'],'Building'=>['Edificio','&#127970;']];
    $typeLabels = array_map(fn($v) => $v[0], $types);
@endphp

{{-- Breadcrumb --}}
<div style="margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
    <a href="{{ route('properties.index') }}" style="font-size:0.82rem; color:var(--text-muted);">&#8592; Propiedades</a>
    <span style="font-size:0.72rem; color:var(--text-muted);">/</span>
    <a href="{{ route('properties.show', $property) }}" style="font-size:0.82rem; color:var(--text-muted);">{{ Str::limit($property->title, 30) }}</a>
    <span style="font-size:0.72rem; color:var(--text-muted);">/</span>
    <span style="font-size:0.82rem; color:var(--text);">Editar</span>
</div>

@if($errors->any())
<div style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); border-radius:var(--radius); padding:0.75rem 1rem; margin-bottom:1.25rem;">
    @foreach($errors->all() as $error)
        <p style="color:var(--danger); font-size:0.82rem; margin:0.15rem 0;">{{ $error }}</p>
    @endforeach
</div>
@endif

{{-- Tab pills --}}
<div class="tab-pills" id="tabPills" style="display:flex; flex-direction:row; flex-wrap:nowrap;">
    <button class="tab-pill active" onclick="switchTab('general', this)">General</button>
    <button class="tab-pill" onclick="switchTab('features', this)">Caracteristicas</button>
    <button class="tab-pill" onclick="switchTab('media', this)">Media e Integraciones</button>
</div>

<div class="edit-layout">
    {{-- LEFT: Form --}}
    <div>
        <form method="POST" action="{{ route('properties.update', $property) }}" enctype="multipart/form-data" id="editForm">
            @csrf @method('PUT')

            {{-- TAB: General --}}
            <div class="tab-panel active" data-tab="general">
                <div class="card">
                    <div class="card-body">
                        <div class="section-label">Tipo de propiedad</div>
                        <div class="type-selector">
                            @foreach($types as $val => [$label, $icon])
                            <div class="type-card {{ old('property_type', $property->property_type ?? 'House') === $val ? 'selected' : '' }}" onclick="selectType(this, '{{ $val }}')">
                                <span class="check-mark">&#10003;</span>
                                <span class="type-card-icon">{!! $icon !!}</span>
                                <span class="type-card-label">{{ $label }}</span>
                            </div>
                            @endforeach
                        </div>
                        <input type="hidden" name="property_type" id="propertyType" value="{{ old('property_type', $property->property_type ?? 'House') }}">

                        <div class="section-label">Tipo de operacion</div>
                        <div class="op-selector">
                            @foreach(['sale'=>['Venta','Venta definitiva'],'rental'=>['Renta','Arrendamiento mensual'],'temporary_rental'=>['Renta Temporal','Corto plazo']] as $val => [$label, $sub])
                            <div class="op-card {{ old('operation_type', $property->operation_type ?? 'sale') === $val ? 'selected' : '' }}" onclick="selectOp(this, '{{ $val }}')">
                                <div class="op-card-label">{{ $label }}</div>
                                <div class="op-card-sub">{{ $sub }}</div>
                            </div>
                            @endforeach
                        </div>
                        <input type="hidden" name="operation_type" id="operationType" value="{{ old('operation_type', $property->operation_type ?? 'sale') }}">

                        <div class="section-label" style="margin-top:1.25rem;">Informacion principal</div>
                        <div class="form-group">
                            <label class="form-label">Titulo del anuncio <span class="required">*</span></label>
                            <input type="text" name="title" class="form-input" value="{{ old('title', $property->title) }}" required>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Precio <span class="required">*</span></label>
                                <div class="price-wrap">
                                    <span class="currency-tag" id="currTag">$</span>
                                    <input type="number" name="price" class="form-input" value="{{ old('price', $property->price) }}" required step="0.01" min="0">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Moneda</label>
                                <select name="currency" class="form-select" id="currSelect" onchange="updateCurrency()">
                                    <option value="MXN" {{ old('currency', $property->currency ?? 'MXN') === 'MXN' ? 'selected' : '' }}>MXN</option>
                                    <option value="USD" {{ old('currency', $property->currency) === 'USD' ? 'selected' : '' }}>USD</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Estado</label>
                                <select name="status" class="form-select">
                                    <option value="captacion" {{ old('status', $property->status) === 'captacion' ? 'selected' : '' }}>En Captación</option>
                                    <option value="available" {{ old('status', $property->status) === 'available' ? 'selected' : '' }}>Disponible</option>
                                    <option value="reserved" {{ old('status', $property->status) === 'reserved' ? 'selected' : '' }}>Reservada</option>
                                    <option value="sold" {{ old('status', $property->status) === 'sold' ? 'selected' : '' }}>Vendida</option>
                                    <option value="rented" {{ old('status', $property->status) === 'rented' ? 'selected' : '' }}>Rentada</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Broker</label>
                                <select name="broker_id" class="form-select">
                                    <option value="">Sin asignar</option>
                                    @foreach($brokers as $broker)
                                        <option value="{{ $broker->id }}" {{ old('broker_id', $property->broker_id) == $broker->id ? 'selected' : '' }}>{{ $broker->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="section-label" style="margin-top:1.25rem;">Propietario</div>
                        <div class="form-group">
                            @php $currentOwner = old('client_id', $property->client_id); @endphp
                            <input type="hidden" name="client_id" id="ownerIdInput" value="{{ $currentOwner }}">
                            <div id="ownerDisplay" style="{{ $currentOwner ? '' : 'display:none;' }}">
                                <div class="owner-selected">
                                    <span id="ownerName">{{ $currentOwner ? ($property->owner->name ?? \App\Models\Client::find($currentOwner)?->name ?? '') : '' }}</span>
                                    <button type="button" class="remove-owner" onclick="clearOwner()">&times;</button>
                                </div>
                            </div>
                            <div id="ownerSearchWrap" class="owner-search-wrap" style="{{ $currentOwner ? 'display:none;' : '' }}">
                                <input type="text" class="form-input" id="ownerSearchInput" placeholder="Buscar cliente por nombre, email o telefono..." autocomplete="off" oninput="searchOwner(this.value)">
                                <div class="owner-search-results" id="ownerResults"></div>
                            </div>
                            <p class="form-hint">Cliente dueno de la propiedad (opcional)</p>
                        </div>

                        <div class="section-label" style="margin-top:1.25rem;">Descripcion del anuncio</div>
                        <div class="form-group">
                            <textarea name="description" class="form-textarea" rows="5" placeholder="Describe la propiedad con detalle...">{{ old('description', $property->description) }}</textarea>
                        </div>

                        <div class="section-label" style="margin-top:1.25rem;">Ubicacion</div>
                        <div class="form-group">
                            <label class="form-label">Direccion</label>
                            <input type="text" name="address" class="form-input" value="{{ old('address', $property->address) }}" placeholder="Calle, numero, interior...">
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Ciudad</label>
                                <input type="text" name="city" class="form-input" value="{{ old('city', $property->city) }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Colonia</label>
                                <input type="text" name="colony" class="form-input" value="{{ old('colony', $property->colony) }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Colonia (catalogo valuacion)</label>
                                <select name="market_colonia_id" class="form-input">
                                    <option value="">-- Sin vincular --</option>
                                    @foreach($colonias ?? [] as $zona => $cols)
                                        <optgroup label="{{ $zona }}">
                                            @foreach($cols as $col)
                                                <option value="{{ $col->id }}" {{ old('market_colonia_id', $property->market_colonia_id) == $col->id ? 'selected' : '' }}>{{ $col->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                <small style="color:var(--text-muted)">Vincular con el catalogo de colonias del sistema de valuacion</small>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Codigo Postal</label>
                                <input type="text" name="zipcode" class="form-input" value="{{ old('zipcode', $property->zipcode) }}" maxlength="10">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB: Features --}}
            <div class="tab-panel" data-tab="features">
                <div class="card">
                    <div class="card-body">
                        <div class="section-label">Espacios</div>
                        <div class="features-grid">
                            <div class="feature-card">
                                <div class="feature-icon">&#128716;</div>
                                <label>Recamaras</label>
                                <input type="number" name="bedrooms" value="{{ old('bedrooms', $property->bedrooms) }}" min="0" placeholder="0">
                            </div>
                            <div class="feature-card">
                                <div class="feature-icon">&#128705;</div>
                                <label>Banos completos</label>
                                <input type="number" name="bathrooms" value="{{ old('bathrooms', $property->bathrooms) }}" min="0" placeholder="0">
                            </div>
                            <div class="feature-card">
                                <div class="feature-icon">&#128704;</div>
                                <label>Medios banos</label>
                                <input type="number" name="half_bathrooms" value="{{ old('half_bathrooms', $property->half_bathrooms) }}" min="0" placeholder="0">
                            </div>
                            <div class="feature-card">
                                <div class="feature-icon">&#128663;</div>
                                <label>Estacionamiento</label>
                                <input type="number" name="parking" value="{{ old('parking', $property->parking) }}" min="0" placeholder="0">
                            </div>
                            <div class="feature-card">
                                <div class="feature-icon">&#127970;</div>
                                <label>Pisos / Niveles</label>
                                <input type="number" name="floors" value="{{ old('floors', $property->floors) }}" min="0" placeholder="0">
                            </div>
                        </div>

                        <div class="section-label" style="margin-top:1.25rem;">Superficies</div>
                        <div class="features-grid">
                            <div class="feature-card">
                                <div class="feature-icon">&#128207;</div>
                                <label>Terreno m&sup2;</label>
                                <input type="number" name="lot_area" value="{{ old('lot_area', $property->lot_area) }}" min="0" step="0.01" placeholder="0">
                            </div>
                            <div class="feature-card">
                                <div class="feature-icon">&#128208;</div>
                                <label>Construccion m&sup2;</label>
                                <input type="number" name="construction_area" value="{{ old('construction_area', $property->construction_area) }}" min="0" step="0.01" placeholder="0">
                            </div>
                            <div class="feature-card">
                                <div class="feature-icon">&#128209;</div>
                                <label>Area total m&sup2;</label>
                                <input type="number" name="area" value="{{ old('area', $property->area) }}" min="0" step="0.01" placeholder="0">
                            </div>
                        </div>

                        <div class="section-label" style="margin-top:1.25rem;">Detalles adicionales</div>
                        <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:0.75rem;">
                            <div class="form-group">
                                <label class="form-label">Ano de construccion</label>
                                <input type="number" name="year_built" class="form-input" value="{{ old('year_built', $property->year_built) }}" min="1900" max="{{ date('Y') }}" placeholder="{{ date('Y') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Mantenimiento mensual</label>
                                <input type="number" name="maintenance_fee" class="form-input" value="{{ old('maintenance_fee', $property->maintenance_fee) }}" min="0" step="0.01" placeholder="$0.00">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Amueblado</label>
                                <select name="furnished" class="form-input">
                                    <option value="">-- Seleccionar --</option>
                                    <option value="sin_amueblar" {{ old('furnished', $property->furnished) === 'sin_amueblar' ? 'selected' : '' }}>Sin amueblar</option>
                                    <option value="semi_amueblado" {{ old('furnished', $property->furnished) === 'semi_amueblado' ? 'selected' : '' }}>Semi amueblado</option>
                                    <option value="amueblado" {{ old('furnished', $property->furnished) === 'amueblado' ? 'selected' : '' }}>Amueblado</option>
                                </select>
                            </div>
                        </div>

                        @php $currentAmenities = old('amenities', $property->amenities ?? []); @endphp
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
                                <input type="checkbox" name="amenities[]" value="{{ $key }}" {{ in_array($key, $currentAmenities) ? 'checked' : '' }}>
                                {{ $label }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB: Media & Integrations --}}
            <div class="tab-panel" data-tab="media">
                <div class="card" style="margin-bottom:1rem;">
                    <div class="card-body">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
                            <div class="section-label" style="margin:0;">Fotografias</div>
                            @php $photoCount = $property->photos->count(); @endphp
                            <span id="photoCountBadge" style="font-size:0.68rem; color:var(--text-muted); background:var(--bg); padding:0.15rem 0.5rem; border-radius:10px; font-weight:600;">{{ $photoCount }}/20</span>
                        </div>
                        <div id="photoPanel">
                            <div id="photoDrop" class="photo-drop" onclick="document.getElementById('photoFiles').click()">
                                <input type="file" id="photoFiles" name="photos[]" accept="image/*" multiple style="display:none" onchange="uploadPhotos(this.files)">
                                <div style="font-size:1.2rem; opacity:0.35;">&#10010;</div>
                                <p style="margin:0.2rem 0 0; font-size:0.75rem; color:var(--text-muted);">Subir fotos</p>
                                <p style="margin:0.1rem 0 0; font-size:0.65rem; color:var(--text-muted); opacity:0.7;">Arrastra aqu&iacute; o clic &mdash; JPG, PNG, WebP</p>
                            </div>

                            <div class="photo-grid" id="photoList">
                                @foreach($property->photos->sortBy('sort_order') as $photo)
                                <div class="photo-grid-item {{ $photo->is_primary ? 'is-primary' : '' }}" id="photo-{{ $photo->id }}" data-id="{{ $photo->id }}">
                                    <img src="{{ asset('storage/' . $photo->path) }}" alt="{{ $photo->description ?? '' }}" loading="lazy">
                                    @if($photo->is_primary)
                                    <div class="photo-primary-badge">&#9733; Portada</div>
                                    @endif
                                    <div class="photo-order-num">{{ $loop->iteration }}</div>
                                    <div class="photo-grid-overlay">
                                        <button type="button" class="photo-overlay-btn" onclick="openLightbox(this)" data-src="{{ asset('storage/' . $photo->path) }}" title="Ver foto">&#128065;</button>
                                        <button type="button" class="photo-overlay-btn {{ $photo->is_primary ? 'star-active' : '' }}" onclick="setPrimary({{ $photo->id }})" title="Marcar como portada">&#9733;</button>
                                        <button type="button" class="photo-overlay-btn btn-del-overlay" onclick="deletePhoto({{ $photo->id }})" title="Eliminar">&#10005;</button>
                                    </div>
                                    <div class="photo-desc-row">
                                        <input type="text" class="photo-desc-input-sm" placeholder="Alt / descripci&oacute;n..." value="{{ $photo->description ?? '' }}" onchange="saveDescription({{ $photo->id }}, this.value)">
                                    </div>
                                </div>
                                @endforeach
                                @if($photoCount === 0)
                                <div id="photoEmpty" style="text-align:center; padding:1.75rem 0.5rem; color:var(--text-muted); grid-column:1/-1;">
                                    <div style="font-size:2.2rem; opacity:0.3; margin-bottom:0.4rem;">&#127976;</div>
                                    <p style="font-size:0.82rem; margin:0;">Sin fotos aun</p>
                                    <p style="font-size:0.72rem; margin:0.2rem 0 0; opacity:0.7;">Sube fotos para mostrar la propiedad</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="section-label">Video de YouTube</div>
                        <div class="form-group">
                            <input type="url" name="youtube_url" id="youtubeInput" class="form-input" value="{{ old('youtube_url', $property->youtube_url) }}" placeholder="https://www.youtube.com/watch?v=..." oninput="previewYoutube()">
                        </div>
                        <div id="ytPreviewContainer">
                            @if($property->youtube_url)
                                @php
                                    $ytId = null;
                                    if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([\w-]{11})/', $property->youtube_url, $m)) $ytId = $m[1];
                                @endphp
                                @if($ytId)
                                    <div class="yt-preview"><iframe src="https://www.youtube.com/embed/{{ $ytId }}" allowfullscreen></iframe></div>
                                @endif
                            @endif
                        </div>

                        <div class="section-label" style="margin-top:1.5rem;">EasyBroker</div>
                        <div class="eb-section">
                            <div class="eb-status">
                                @if($property->isPublishedToEasyBroker())
                                    <span class="eb-dot" style="background:var(--success);"></span>
                                    <span style="font-size:0.85rem; font-weight:500; color:var(--success);">Publicada</span>
                                @elseif($property->hasEasyBrokerId())
                                    <span class="eb-dot" style="background:#eab308;"></span>
                                    <span style="font-size:0.85rem; font-weight:500; color:#a16207;">Despublicada</span>
                                @else
                                    <span class="eb-dot" style="background:var(--border);"></span>
                                    <span style="font-size:0.85rem; color:var(--text-muted);">No publicada</span>
                                @endif
                            </div>
                            @if($property->isPublishedToEasyBroker() && $property->easybroker_published_at)
                                <p style="font-size:0.78rem; color:var(--text-muted); margin-bottom:0.4rem;">Sincronizada: {{ $property->easybroker_published_at->format('d/m/Y H:i') }}</p>
                            @endif
                            @if($property->easybroker_public_url)
                                <a href="{{ $property->easybroker_public_url }}" target="_blank" style="font-size:0.82rem; color:var(--primary);">Ver en EasyBroker &rarr;</a>
                            @endif
                            <div class="eb-actions">
                                @if($property->isPublishedToEasyBroker())
                                    <button type="button" class="btn btn-sm btn-primary" onclick="ebAction('publish')">Actualizar</button>
                                    <button type="button" class="btn btn-sm btn-outline" style="color:#a16207; border-color:#a16207;" onclick="ebAction('unpublish')">Despublicar</button>
                                @else
                                    <button type="button" class="btn btn-sm btn-primary" onclick="ebAction('publish')">Publicar en EasyBroker</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Save bar --}}
            <div class="save-bar">
                <div class="save-bar-meta">
                    Creado {{ $property->created_at->format('d/m/Y') }} &middot; Actualizado {{ $property->updated_at->diffForHumans() }}
                </div>
                <div style="display:flex; gap:0.5rem;">
                    <a href="{{ route('properties.show', $property) }}" class="btn btn-outline">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </form>
    </div>

    {{-- RIGHT: Info panel --}}
    <div class="photos-panel">
        {{-- Quick Info --}}
        <div class="side-card">
            <div class="side-card-body">
                <div style="display:flex; gap:0.4rem; flex-wrap:wrap; margin-bottom:0.4rem;">
                    <span class="badge badge-blue">{{ $typeLabels[$property->property_type] ?? $property->property_type }}</span>
                    @if($property->status === 'sold')<span class="badge badge-red">Vendido</span>
                    @elseif($property->status === 'rented')<span class="badge badge-yellow">Rentado</span>
                    @else<span class="badge badge-green">Disponible</span>@endif
                </div>
                <div style="font-size:1.1rem; font-weight:700; color:var(--primary);">${{ number_format($property->price, 0) }} {{ $property->currency ?? 'MXN' }}</div>
                <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.15rem;">{{ implode(', ', array_filter([$property->colony, $property->city])) ?: 'Sin ubicacion' }}</div>
            </div>
        </div>

        {{-- Delete --}}
        <div class="side-card" style="border-color:rgba(239,68,68,0.2);">
            <div class="side-card-body" style="text-align:center;">
                <form method="POST" action="{{ route('properties.destroy', $property) }}" onsubmit="return confirm('Eliminar esta propiedad permanentemente?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" style="width:100%;">Eliminar propiedad</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- EasyBroker forms (outside main form to avoid nesting) --}}
<form method="POST" action="{{ route('properties.publish-easybroker', $property) }}" id="ebPublishForm" style="display:none;">@csrf</form>
<form method="POST" action="{{ route('properties.unpublish-easybroker', $property) }}" id="ebUnpublishForm" style="display:none;">@csrf</form>

{{-- Save toast --}}
<div class="save-toast" id="saveToast">&#10003; Propiedad guardada</div>

{{-- Lightbox --}}
<div class="photo-lightbox" id="photoLightbox" onclick="closeLightbox()">
    <button class="photo-lightbox-close" onclick="event.stopPropagation(); closeLightbox();">&times;</button>
    <img class="photo-lightbox-img" id="photoLightboxImg" src="" alt="Vista previa" onclick="event.stopPropagation()">
</div>
@endsection

@section('scripts')
<script>
function switchTab(name, btn) {
    document.querySelectorAll('.tab-panel').forEach(function(p) { p.classList.toggle('active', p.dataset.tab === name); });
    document.querySelectorAll('.tab-pill').forEach(function(b) { b.classList.remove('active'); });
    btn.classList.add('active');
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
    document.getElementById('currTag').textContent = document.getElementById('currSelect').value === 'USD' ? 'US$' : '$';
}

// AJAX form save
document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;
    var btn = form.querySelector('button[type="submit"]');
    var origText = btn.textContent;
    btn.textContent = 'Guardando...';
    btn.disabled = true;

    var fd = new FormData(form);
    fetch(form.action, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: fd
    })
    .then(function(r) { return r.json().catch(function() { return { success: true }; }); })
    .then(function(data) {
        var toast = document.getElementById('saveToast');
        if (data.errors) {
            toast.textContent = 'Error: ' + Object.values(data.errors).flat().join(', ');
            toast.style.background = 'var(--danger)';
        } else {
            toast.textContent = '\u2713 Propiedad guardada';
            toast.style.background = 'var(--success)';
        }
        toast.classList.add('show');
        setTimeout(function() { toast.classList.remove('show'); }, 3000);
    })
    .catch(function() {
        var toast = document.getElementById('saveToast');
        toast.textContent = 'Error al guardar';
        toast.style.background = 'var(--danger)';
        toast.classList.add('show');
        setTimeout(function() { toast.classList.remove('show'); }, 3000);
    })
    .finally(function() {
        btn.textContent = origText;
        btn.disabled = false;
    });
});
function previewYoutube() {
    var url = document.getElementById('youtubeInput').value;
    var container = document.getElementById('ytPreviewContainer');
    var match = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([\w-]{11})/);
    container.innerHTML = match ? '<div class="yt-preview" style="margin-top:0.75rem;"><iframe src="https://www.youtube.com/embed/' + match[1] + '" allowfullscreen></iframe></div>' : '';
}
// ─── Photo AJAX ────────────────────────────────────
var csrfToken = '{{ csrf_token() }}';
var propertyId = {{ $property->id }};
var photoStoreUrl = '{{ route("properties.photos.store", $property) }}';
var photoReorderUrl = '{{ route("properties.photos.reorder", $property) }}';

function truncateFilename(name, max) {
    return name.length > max ? name.substring(0, max - 2) + '..' : name;
}

function buildPhotoItem(photo) {
    var div = document.createElement('div');
    div.className = 'photo-grid-item' + (photo.is_primary ? ' is-primary' : '');
    div.id = 'photo-' + photo.id;
    div.dataset.id = photo.id;
    div.innerHTML =
        '<img src="' + photo.url + '" alt="" loading="lazy">' +
        (photo.is_primary ? '<div class="photo-primary-badge">&#9733; Portada</div>' : '') +
        '<div class="photo-order-num"></div>' +
        '<div class="photo-grid-overlay">' +
            '<button type="button" class="photo-overlay-btn" onclick="openLightbox(this)" data-src="' + photo.url + '" title="Ver foto">&#128065;</button>' +
            '<button type="button" class="photo-overlay-btn' + (photo.is_primary ? ' star-active' : '') + '" onclick="setPrimary(' + photo.id + ')" title="Marcar como portada">&#9733;</button>' +
            '<button type="button" class="photo-overlay-btn btn-del-overlay" onclick="deletePhoto(' + photo.id + ')" title="Eliminar">&#10005;</button>' +
        '</div>' +
        '<div class="photo-desc-row">' +
            '<input type="text" class="photo-desc-input-sm" placeholder="Alt / descripci\u00f3n..." value="" onchange="saveDescription(' + photo.id + ', this.value)">' +
        '</div>';
    div.style.opacity = '0';
    div.style.transform = 'scale(0.88)';
    div.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
    requestAnimationFrame(function() {
        requestAnimationFrame(function() {
            div.style.opacity = '1';
            div.style.transform = 'scale(1)';
        });
    });
    return div;
}

function uploadPhotos(files) {
    if (!files || files.length === 0) return;
    var list = document.getElementById('photoList');
    var empty = document.getElementById('photoEmpty');
    if (empty) empty.style.display = 'none';
    document.getElementById('photoFiles').value = '';

    Array.from(files).forEach(function(file, idx) {
        var currentGridItems = list.querySelectorAll('.photo-grid-item').length;
        var pendingSkeletons = list.querySelectorAll('.photo-skeleton').length;
        if (currentGridItems + pendingSkeletons >= 20) return;

        var skId = 'sk-' + Date.now() + '-' + idx;
        var sk = document.createElement('div');
        sk.className = 'photo-skeleton';
        sk.id = skId;
        sk.innerHTML =
            '<div class="photo-skeleton-spinner"></div>' +
            '<span class="photo-skeleton-label">' + truncateFilename(file.name, 18) + '</span>';
        list.appendChild(sk);

        var fd = new FormData();
        fd.append('_token', csrfToken);
        fd.append('photos[]', file);

        fetch(photoStoreUrl, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var skEl = document.getElementById(skId);
            if (data.photos && data.photos.length > 0) {
                var photo = data.photos[0];
                var newItem = buildPhotoItem(photo);
                if (skEl) {
                    skEl.replaceWith(newItem);
                } else {
                    list.appendChild(newItem);
                }
                updatePhotoCount(data.total);
                renumberPhotos();
            } else {
                if (skEl) {
                    skEl.innerHTML = '<span class="photo-skeleton-error">&#10005; Error</span>';
                    setTimeout(function() {
                        skEl.style.transition = 'opacity 0.3s';
                        skEl.style.opacity = '0';
                        setTimeout(function() { skEl.remove(); }, 320);
                    }, 1800);
                }
            }
        })
        .catch(function() {
            var skEl = document.getElementById(skId);
            if (skEl) {
                skEl.innerHTML = '<span class="photo-skeleton-error">&#10005; Error</span>';
                setTimeout(function() {
                    skEl.style.transition = 'opacity 0.3s';
                    skEl.style.opacity = '0';
                    setTimeout(function() { skEl.remove(); }, 320);
                }, 1800);
            }
        });
    });
}

function setPrimary(photoId) {
    var url = '/properties/' + propertyId + '/photos/' + photoId + '/primary';
    fetch(url, { method: 'PATCH', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.success) return;
        document.querySelectorAll('#photoList .photo-grid-item').forEach(function(t) {
            t.classList.remove('is-primary');
            var badge = t.querySelector('.photo-primary-badge');
            if (badge) badge.remove();
            var btns = t.querySelectorAll('.photo-overlay-btn');
            if (btns[1]) btns[1].classList.remove('star-active');
        });
        var el = document.getElementById('photo-' + photoId);
        if (el) {
            el.classList.add('is-primary');
            var badge = document.createElement('div');
            badge.className = 'photo-primary-badge';
            badge.innerHTML = '&#9733; Portada';
            el.insertBefore(badge, el.firstChild);
            var btns = el.querySelectorAll('.photo-overlay-btn');
            if (btns[1]) btns[1].classList.add('star-active');
        }
    });
}

function deletePhoto(photoId) {
    if (!confirm('¿Eliminar esta foto?')) return;
    var el = document.getElementById('photo-' + photoId);
    if (el) { el.style.opacity = '0.4'; el.style.transform = 'scale(0.95)'; }
    var url = '/properties/' + propertyId + '/photos/' + photoId;
    fetch(url, { method: 'DELETE', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.success) return;
        if (el) {
            el.style.transition = 'opacity 0.22s ease, transform 0.22s ease';
            el.style.opacity = '0';
            el.style.transform = 'scale(0.75)';
            setTimeout(function() { el.remove(); }, 240);
        }
        updatePhotoCount(data.total);
        renumberPhotos();
        if (data.total === 0) {
            var emptyDiv = document.createElement('div');
            emptyDiv.id = 'photoEmpty';
            emptyDiv.style.cssText = 'text-align:center;padding:1.75rem 0.5rem;color:var(--text-muted);grid-column:1/-1;';
            emptyDiv.innerHTML = '<div style="font-size:2.2rem;opacity:0.3;margin-bottom:0.4rem;">&#127976;</div><p style="font-size:0.82rem;margin:0;">Sin fotos</p>';
            document.getElementById('photoList').appendChild(emptyDiv);
        }
    })
    .catch(function() { if (el) { el.style.opacity = '1'; el.style.transform = 'scale(1)'; } });
}

var descTimers = {};
function saveDescription(photoId, value) {
    clearTimeout(descTimers[photoId]);
    descTimers[photoId] = setTimeout(function() {
        fetch('/properties/' + propertyId + '/photos/' + photoId, {
            method: 'PATCH',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' },
            body: JSON.stringify({ description: value })
        });
    }, 600);
}

function updatePhotoCount(total) {
    document.getElementById('photoCountBadge').textContent = total + '/20';
}

function renumberPhotos() {
    document.querySelectorAll('#photoList .photo-grid-item').forEach(function(t, i) {
        var num = t.querySelector('.photo-order-num');
        if (num) num.textContent = i + 1;
    });
}

function saveOrder() {
    var items = document.querySelectorAll('#photoList .photo-grid-item');
    var order = [];
    items.forEach(function(el) { order.push(parseInt(el.dataset.id)); });
    fetch(photoReorderUrl, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' },
        body: JSON.stringify({ order: order })
    });
}

// Drag & drop upload
(function() {
    var drop = document.getElementById('photoDrop');
    if (!drop) return;
    ['dragenter','dragover'].forEach(function(ev) { drop.addEventListener(ev, function(e) { e.preventDefault(); drop.classList.add('dragover'); }); });
    ['dragleave','drop'].forEach(function(ev) { drop.addEventListener(ev, function(e) { e.preventDefault(); drop.classList.remove('dragover'); }); });
    drop.addEventListener('drop', function(e) { uploadPhotos(e.dataTransfer.files); });
})();
updateCurrency();

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
document.addEventListener('click', function(e) {
    if (!e.target.closest('.owner-search-wrap')) {
        document.getElementById('ownerResults').classList.remove('visible');
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
<script>
// Init SortableJS on photo grid
var photoList = document.getElementById('photoList');
if (photoList) {
    new Sortable(photoList, {
        animation: 200,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        filter: '.photo-skeleton',
        onEnd: function() {
            renumberPhotos();
            saveOrder();
        }
    });
}

// Lightbox
function openLightbox(btn) {
    var src = (typeof btn === 'string') ? btn : btn.dataset.src;
    var lb = document.getElementById('photoLightbox');
    var img = document.getElementById('photoLightboxImg');
    img.src = src;
    lb.classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeLightbox() {
    document.getElementById('photoLightbox').classList.remove('open');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeLightbox();
});

// ─── EasyBroker AJAX ───────────────────────────────────────────────
function ebAction(action) {
    var isPublish = action === 'publish';
    if (!isPublish && !confirm('¿Despublicar esta propiedad de EasyBroker?')) return;

    var url = isPublish
        ? '{{ route('properties.publish-easybroker', $property) }}'
        : '{{ route('properties.unpublish-easybroker', $property) }}';

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        window.toast(data.message, data.success ? 'success' : 'error');
        if (data.success) setTimeout(function() { location.reload(); }, 1500);
    })
    .catch(function() {
        window.toast('Error de conexión con el servidor.', 'error');
    });
}
</script>
@endsection
