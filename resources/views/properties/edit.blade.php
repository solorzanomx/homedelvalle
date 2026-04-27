@extends('layouts.app-sidebar')
@section('title', 'Editar Propiedad')

@section('styles')
<style>
@import url('https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css');

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
    padding: 0.75rem; text-align: center; cursor: pointer; transition: all 0.2s; margin-bottom: 0.75rem;
}
.photo-drop:hover, .photo-drop.dragover { border-color: var(--primary); background: rgba(102,126,234,0.03); }
/* Photo list items */
.photo-list { display: flex; flex-direction: column; gap: 6px; }
.photo-item {
    display: flex; gap: 8px; align-items: flex-start; padding: 6px;
    border: 1px solid var(--border); border-radius: 8px; background: var(--card);
    transition: all 0.15s; cursor: grab; position: relative;
}
.photo-item:active { cursor: grabbing; }
.photo-item.is-primary { border-color: var(--primary); background: rgba(102,126,234,0.03); }
.photo-item.sortable-ghost { opacity: 0.4; border-style: dashed; }
.photo-item.sortable-chosen { box-shadow: 0 4px 16px rgba(0,0,0,0.12); z-index: 10; }
.photo-item-thumb {
    width: 72px; height: 72px; border-radius: 6px; overflow: hidden; flex-shrink: 0; position: relative;
}
.photo-item-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.photo-item-info { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 4px; }
.photo-item-top { display: flex; align-items: center; gap: 4px; }
.photo-item-order {
    width: 20px; height: 20px; border-radius: 50%; background: var(--bg);
    border: 1px solid var(--border); display: flex; align-items: center;
    justify-content: center; font-size: 0.6rem; font-weight: 700; color: var(--text-muted);
    flex-shrink: 0;
}
.photo-item.is-primary .photo-item-order { background: var(--primary); color: #fff; border-color: var(--primary); }
.photo-item-badges { display: flex; gap: 3px; align-items: center; }
.photo-badge-primary {
    font-size: 0.6rem; background: var(--primary); color: #fff;
    padding: 0.05rem 0.3rem; border-radius: 3px; font-weight: 600;
}
.photo-item-actions { margin-left: auto; display: flex; gap: 3px; }
.photo-item-actions button {
    width: 22px; height: 22px; border-radius: 50%; border: 1px solid var(--border);
    background: var(--bg); cursor: pointer; display: flex; align-items: center;
    justify-content: center; font-size: 0.6rem; transition: all 0.15s; color: var(--text-muted);
}
.photo-item-actions button:hover { border-color: var(--primary); color: var(--primary); }
.photo-item-actions button.btn-del:hover { border-color: var(--danger); color: var(--danger); }
.photo-desc-input {
    width: 100%; border: 1px solid var(--border); border-radius: 4px; padding: 0.25rem 0.4rem;
    font-size: 0.7rem; color: var(--text); background: var(--bg); outline: none; transition: border-color 0.15s;
}
.photo-desc-input:focus { border-color: var(--primary); }
.photo-desc-input::placeholder { color: var(--text-muted); opacity: 0.6; }
.photo-drag-handle {
    display: flex; align-items: center; color: var(--text-muted); opacity: 0.4;
    font-size: 0.8rem; cursor: grab; padding: 0 2px;
}
.photo-drag-handle:hover { opacity: 0.8; }
/* Spinner */
.photo-spinner {
    width: 28px; height: 28px; border: 3px solid var(--border);
    border-top-color: var(--primary); border-radius: 50%;
    animation: photoSpin 0.6s linear infinite; margin: 0 auto;
}
@keyframes photoSpin { to { transform: rotate(360deg); } }
.photo-item-uploading {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    padding: 10px; border: 1px dashed var(--primary); border-radius: 8px; background: rgba(102,126,234,0.03);
}
.photo-item-uploading .photo-spinner { width: 20px; height: 20px; border-width: 2px; margin: 0; }

/* Photo editor modal */
.photo-editor-modal {
    position: fixed; inset: 0; z-index: 1400; display: none;
    align-items: center; justify-content: center; padding: 1.25rem;
}
.photo-editor-modal.is-open { display: flex; }
.photo-editor-backdrop {
    position: absolute; inset: 0; background: rgba(15, 23, 42, 0.72); backdrop-filter: blur(4px);
}
.photo-editor-dialog {
    position: relative; width: min(1120px, 100%); max-height: calc(100vh - 2.5rem);
    background: #231d1b; color: #f5efe9; border: 1px solid rgba(255,255,255,0.08);
    border-radius: 18px; overflow: hidden; box-shadow: 0 28px 90px rgba(0,0,0,0.35);
}
.photo-editor-grid { display: grid; grid-template-columns: minmax(0, 1fr) 320px; min-height: 640px; }
.photo-editor-stage {
    position: relative; background:
        linear-gradient(45deg, rgba(255,255,255,0.05) 25%, transparent 25%, transparent 75%, rgba(255,255,255,0.05) 75%),
        linear-gradient(45deg, rgba(255,255,255,0.05) 25%, transparent 25%, transparent 75%, rgba(255,255,255,0.05) 75%),
        #2d2624;
    background-size: 24px 24px;
    background-position: 0 0, 12px 12px;
    display: flex; align-items: center; justify-content: center; padding: 1rem;
}
.photo-editor-stage img { display: block; max-width: 100%; }
.photo-editor-sidebar {
    background: #2b2422; border-left: 1px solid rgba(255,255,255,0.08);
    display: flex; flex-direction: column; padding: 1rem;
}
.photo-editor-header { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; margin-bottom: 1rem; }
.photo-editor-title { font-size: 1rem; font-weight: 700; letter-spacing: 0.01em; }
.photo-editor-meta { font-size: 0.75rem; color: rgba(245,239,233,0.68); }
.photo-editor-close {
    width: 34px; height: 34px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.12);
    background: rgba(255,255,255,0.03); color: #f5efe9; cursor: pointer; font-size: 1rem;
}
.photo-editor-close:hover { background: rgba(255,255,255,0.08); }
.photo-editor-panel-label {
    font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.08em;
    color: rgba(245,239,233,0.56); margin-bottom: 0.6rem;
}
.photo-editor-fields { display: grid; gap: 0.65rem; margin-bottom: 1rem; }
.photo-editor-field {
    display: grid; grid-template-columns: 1fr auto auto; align-items: center;
    gap: 0.5rem; border: 1px solid rgba(255,255,255,0.12); border-radius: 12px;
    overflow: hidden; background: rgba(255,255,255,0.02);
}
.photo-editor-field label { font-size: 0.78rem; color: rgba(245,239,233,0.7); padding-left: 0.8rem; }
.photo-editor-field input {
    width: 74px; border: none; background: transparent; color: #f5efe9;
    font-size: 0.92rem; padding: 0.85rem 0.4rem; outline: none;
}
.photo-editor-field span { font-size: 0.74rem; color: rgba(245,239,233,0.6); padding-right: 0.8rem; }
.photo-editor-tools { display: grid; grid-template-columns: repeat(5, 1fr); gap: 0.5rem; margin-bottom: 0.6rem; }
.photo-editor-tool {
    min-height: 38px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.12);
    background: rgba(255,255,255,0.03); color: #f5efe9; cursor: pointer;
    font-size: 0.88rem; display: flex; align-items: center; justify-content: center;
}
.photo-editor-tool:hover { background: rgba(255,255,255,0.08); }
.photo-editor-tool.is-active {
    background: rgba(242, 95, 76, 0.16);
    border-color: rgba(242, 95, 76, 0.55);
    color: #ffb4a9;
}
.photo-editor-hint {
    font-size: 0.76rem; line-height: 1.5; color: rgba(245,239,233,0.58);
    margin-top: auto; padding-top: 1rem;
}
.photo-editor-actions {
    display: flex; gap: 0.75rem; justify-content: flex-end; padding-top: 1rem; margin-top: 1rem;
    border-top: 1px solid rgba(255,255,255,0.08);
}
.photo-editor-btn {
    border: none; border-radius: 12px; padding: 0.9rem 1rem; cursor: pointer;
    font-size: 0.9rem; font-weight: 600;
}
.photo-editor-btn-secondary { background: rgba(255,255,255,0.06); color: #f5efe9; }
.photo-editor-btn-secondary:hover { background: rgba(255,255,255,0.12); }
.photo-editor-btn-primary { background: #11b981; color: #fff; }
.photo-editor-btn-primary:hover { background: #0ea371; }
.photo-editor-progress {
    font-size: 0.76rem; color: rgba(245,239,233,0.65); margin-bottom: 0.85rem;
}
@media (max-width: 960px) {
    .photo-editor-dialog { max-height: calc(100vh - 1rem); }
    .photo-editor-grid { grid-template-columns: 1fr; }
    .photo-editor-stage { min-height: 48vh; }
    .photo-editor-sidebar { border-left: none; border-top: 1px solid rgba(255,255,255,0.08); }
}

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
                                <input type="file" id="photoFiles" name="photos[]" accept="image/*" multiple style="display:none" onchange="startPhotoEditing(this.files)">
                                <div id="photoDropContent">
                                    <div style="font-size:1.2rem; opacity:0.4;">&#10010;</div>
                                    <p style="margin:0; font-size:0.75rem; color:var(--text-muted);">Subir fotos</p>
                                    <p style="margin:0.15rem 0 0; font-size:0.65rem; color:var(--text-muted); opacity:0.7;">Arrastra aqui o clic — JPG, PNG, WebP</p>
                                </div>
                                <div id="photoDropLoading" style="display:none;">
                                    <div class="photo-spinner"></div>
                                    <p style="margin:0.4rem 0 0; font-size:0.75rem; color:var(--primary); font-weight:500;">Subiendo...</p>
                                </div>
                            </div>

                            <div class="photo-list" id="photoList">
                                @foreach($property->photos->sortBy('sort_order') as $photo)
                                <div class="photo-item {{ $photo->is_primary ? 'is-primary' : '' }}" id="photo-{{ $photo->id }}" data-id="{{ $photo->id }}">
                                    <span class="photo-drag-handle" title="Arrastra para reordenar">&#9776;</span>
                                    <div class="photo-item-thumb">
                                        <img src="{{ asset('storage/' . $photo->path) }}" alt="{{ $photo->description }}" loading="lazy">
                                    </div>
                                    <div class="photo-item-info">
                                        <div class="photo-item-top">
                                            <span class="photo-item-order">{{ $loop->iteration }}</span>
                                            @if($photo->is_primary)
                                            <span class="photo-badge-primary">&#9733; Principal</span>
                                            @endif
                                            <div class="photo-item-actions">
                                                <button type="button" onclick="setPrimary({{ $photo->id }})" title="Marcar como principal">&#9733;</button>
                                                <button type="button" class="btn-del" onclick="deletePhoto({{ $photo->id }})" title="Eliminar">&#10005;</button>
                                            </div>
                                        </div>
                                        <input type="text" class="photo-desc-input" placeholder="Descripcion SEO (alt text)..." value="{{ $photo->description ?? '' }}" onchange="saveDescription({{ $photo->id }}, this.value)" data-photo-id="{{ $photo->id }}">
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            @if($photoCount === 0)
                            <div id="photoEmpty" style="text-align:center; padding:1.5rem 0.5rem; color:var(--text-muted);">
                                <div style="font-size:2rem; opacity:0.3; margin-bottom:0.3rem;">&#127976;</div>
                                <p style="font-size:0.82rem; margin:0;">Sin fotos aun</p>
                                <p style="font-size:0.72rem; margin:0.2rem 0 0; opacity:0.7;">Sube fotos para mostrar la propiedad</p>
                            </div>
                            @endif
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

{{-- Photo editor modal --}}
<div class="photo-editor-modal" id="photoEditorModal" aria-hidden="true">
    <div class="photo-editor-backdrop" onclick="cancelPhotoEditing()"></div>
    <div class="photo-editor-dialog" role="dialog" aria-modal="true" aria-labelledby="photoEditorTitle">
        <div class="photo-editor-grid">
            <div class="photo-editor-stage">
                <img id="photoEditorImage" alt="Editor de imagen" />
            </div>
            <div class="photo-editor-sidebar">
                <div class="photo-editor-header">
                    <div>
                        <div class="photo-editor-title" id="photoEditorTitle">Editor de imagen</div>
                        <div class="photo-editor-meta" id="photoEditorFileName">Ajusta la foto antes de subirla</div>
                    </div>
                    <button type="button" class="photo-editor-close" onclick="cancelPhotoEditing()">&#10005;</button>
                </div>

                <div class="photo-editor-progress" id="photoEditorProgress">Foto 1 de 1</div>

                <div class="photo-editor-panel-label">Recorte</div>
                <div class="photo-editor-fields">
                    <div class="photo-editor-field">
                        <label for="cropX">X</label>
                        <input type="number" id="cropX" value="0" step="1" onchange="applyCropDataFromInputs()">
                        <span>px</span>
                    </div>
                    <div class="photo-editor-field">
                        <label for="cropY">Y</label>
                        <input type="number" id="cropY" value="0" step="1" onchange="applyCropDataFromInputs()">
                        <span>px</span>
                    </div>
                    <div class="photo-editor-field">
                        <label for="cropWidth">Ancho</label>
                        <input type="number" id="cropWidth" value="0" step="1" onchange="applyCropDataFromInputs()">
                        <span>px</span>
                    </div>
                    <div class="photo-editor-field">
                        <label for="cropHeight">Altura</label>
                        <input type="number" id="cropHeight" value="0" step="1" onchange="applyCropDataFromInputs()">
                        <span>px</span>
                    </div>
                    <div class="photo-editor-field">
                        <label for="cropRotation">Rotacion</label>
                        <input type="number" id="cropRotation" value="0" step="1" onchange="setCropperRotation(this.value)">
                        <span>grados</span>
                    </div>
                </div>

                <div class="photo-editor-panel-label">Herramientas</div>
                <div class="photo-editor-tools">
                    <button type="button" class="photo-editor-tool is-active" id="photoToolMove" onclick="setCropperDragMode('move')" title="Mover">&#10021;</button>
                    <button type="button" class="photo-editor-tool" id="photoToolCrop" onclick="setCropperDragMode('crop')" title="Recortar">&#9635;</button>
                    <button type="button" class="photo-editor-tool" onclick="zoomCropper(0.1)" title="Zoom in">&#8853;</button>
                    <button type="button" class="photo-editor-tool" onclick="zoomCropper(-0.1)" title="Zoom out">&#8854;</button>
                    <button type="button" class="photo-editor-tool" onclick="fitCropperToFrame()" title="Ajustar">&#9974;</button>
                </div>
                <div class="photo-editor-tools">
                    <button type="button" class="photo-editor-tool" onclick="moveCropper(-20, 0)" title="Mover izquierda">&#8592;</button>
                    <button type="button" class="photo-editor-tool" onclick="moveCropper(20, 0)" title="Mover derecha">&#8594;</button>
                    <button type="button" class="photo-editor-tool" onclick="moveCropper(0, -20)" title="Mover arriba">&#8593;</button>
                    <button type="button" class="photo-editor-tool" onclick="moveCropper(0, 20)" title="Mover abajo">&#8595;</button>
                    <button type="button" class="photo-editor-tool" onclick="centerCropper()" title="Centrar">&#9675;</button>
                </div>
                <div class="photo-editor-tools">
                    <button type="button" class="photo-editor-tool" onclick="rotateCropper(-90)" title="Rotar izquierda">&#8630;</button>
                    <button type="button" class="photo-editor-tool" onclick="rotateCropper(90)" title="Rotar derecha">&#8631;</button>
                    <button type="button" class="photo-editor-tool" onclick="scaleCropper(-1, 1)" title="Espejo horizontal">&#8646;</button>
                    <button type="button" class="photo-editor-tool" onclick="scaleCropper(1, -1)" title="Espejo vertical">&#8645;</button>
                    <button type="button" class="photo-editor-tool" onclick="resetCropper()" title="Reiniciar">&#8634;</button>
                </div>

                <div class="photo-editor-hint">
                    Puedes recortar, mover, acercar y rotar antes de subir cada foto. Si seleccionas varias, se abriran una por una.
                </div>

                <div class="photo-editor-actions">
                    <button type="button" class="photo-editor-btn photo-editor-btn-secondary" onclick="cancelPhotoEditing()">Cancelar</button>
                    <button type="button" class="photo-editor-btn photo-editor-btn-primary" onclick="saveEditedPhoto()">Guardar y continuar</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
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
var photoEditorState = {
    queue: [],
    processed: [],
    index: 0,
    cropper: null,
    activeObjectUrl: null,
    activeFile: null,
    scaleX: 1,
    scaleY: 1,
};

function startPhotoEditing(fileList) {
    var files = Array.from(fileList || []).filter(function(file) {
        return file && /^image\//.test(file.type || '');
    });
    if (!files.length) return;

    if (typeof Cropper === 'undefined') {
        uploadPhotos(files);
        return;
    }

    photoEditorState.queue = files;
    photoEditorState.processed = [];
    photoEditorState.index = 0;
    openNextPhotoEditor();
}

function openNextPhotoEditor() {
    destroyPhotoEditorCropper();

    if (photoEditorState.index >= photoEditorState.queue.length) {
        closePhotoEditor();
        if (photoEditorState.processed.length) {
            uploadPhotos(photoEditorState.processed);
        }
        photoEditorState.queue = [];
        photoEditorState.processed = [];
        photoEditorState.index = 0;
        return;
    }

    var file = photoEditorState.queue[photoEditorState.index];
    photoEditorState.activeFile = file;

    var modal = document.getElementById('photoEditorModal');
    var image = document.getElementById('photoEditorImage');
    var fileName = document.getElementById('photoEditorFileName');
    var progress = document.getElementById('photoEditorProgress');

    if (photoEditorState.activeObjectUrl) {
        URL.revokeObjectURL(photoEditorState.activeObjectUrl);
    }

    photoEditorState.activeObjectUrl = URL.createObjectURL(file);
    image.src = photoEditorState.activeObjectUrl;
    fileName.textContent = file.name + ' · ' + formatBytes(file.size);
    progress.textContent = 'Foto ' + (photoEditorState.index + 1) + ' de ' + photoEditorState.queue.length;
    photoEditorState.scaleX = 1;
    photoEditorState.scaleY = 1;
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    setCropperDragMode('move');

    image.onload = function() {
        photoEditorState.cropper = new Cropper(image, {
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 1,
            background: false,
            responsive: true,
            rotatable: true,
            zoomable: true,
            scalable: false,
            crop: syncCropInputs,
            ready: function() {
                syncCropInputs();
            },
        });
    };
}

function closePhotoEditor() {
    destroyPhotoEditorCropper();
    var modal = document.getElementById('photoEditorModal');
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
}

function destroyPhotoEditorCropper() {
    if (photoEditorState.cropper) {
        photoEditorState.cropper.destroy();
        photoEditorState.cropper = null;
    }
    if (photoEditorState.activeObjectUrl) {
        URL.revokeObjectURL(photoEditorState.activeObjectUrl);
        photoEditorState.activeObjectUrl = null;
    }
}

function cancelPhotoEditing() {
    closePhotoEditor();
    photoEditorState.queue = [];
    photoEditorState.processed = [];
    photoEditorState.index = 0;
    photoEditorState.activeFile = null;
    document.getElementById('photoFiles').value = '';
}

function useOriginalPhoto() {
    if (photoEditorState.activeFile) {
        photoEditorState.processed.push(photoEditorState.activeFile);
    }
    photoEditorState.index += 1;
    openNextPhotoEditor();
}

function saveEditedPhoto() {
    if (!photoEditorState.cropper || !photoEditorState.activeFile) return;

    var originalType = photoEditorState.activeFile.type || 'image/jpeg';
    var exportType = /image\/(png|webp|jpeg)/.test(originalType) ? originalType : 'image/jpeg';
    var canvas = photoEditorState.cropper.getCroppedCanvas({
        maxWidth: 2400,
        maxHeight: 2400,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    });

    if (!canvas) {
        useOriginalPhoto();
        return;
    }

    canvas.toBlob(function(blob) {
        if (!blob) {
            useOriginalPhoto();
            return;
        }

        var editedFile = new File([blob], photoEditorState.activeFile.name, {
            type: exportType,
            lastModified: Date.now(),
        });

        photoEditorState.processed.push(editedFile);
        photoEditorState.index += 1;
        openNextPhotoEditor();
    }, exportType, 0.92);
}

function syncCropInputs() {
    if (!photoEditorState.cropper) return;
    var data = photoEditorState.cropper.getData(true);
    document.getElementById('cropX').value = Math.round(data.x || 0);
    document.getElementById('cropY').value = Math.round(data.y || 0);
    document.getElementById('cropWidth').value = Math.round(data.width || 0);
    document.getElementById('cropHeight').value = Math.round(data.height || 0);
    document.getElementById('cropRotation').value = Math.round(data.rotate || 0);
}

function applyCropDataFromInputs() {
    if (!photoEditorState.cropper) return;
    photoEditorState.cropper.setData({
        x: parseFloat(document.getElementById('cropX').value) || 0,
        y: parseFloat(document.getElementById('cropY').value) || 0,
        width: parseFloat(document.getElementById('cropWidth').value) || 0,
        height: parseFloat(document.getElementById('cropHeight').value) || 0,
    });
}

function setCropperRotation(value) {
    if (!photoEditorState.cropper) return;
    photoEditorState.cropper.rotateTo(parseFloat(value) || 0);
    syncCropInputs();
}

function setCropperDragMode(mode) {
    if (photoEditorState.cropper) {
        photoEditorState.cropper.setDragMode(mode);
    }

    var moveBtn = document.getElementById('photoToolMove');
    var cropBtn = document.getElementById('photoToolCrop');
    if (moveBtn) moveBtn.classList.toggle('is-active', mode === 'move');
    if (cropBtn) cropBtn.classList.toggle('is-active', mode === 'crop');
}

function moveCropper(x, y) {
    if (!photoEditorState.cropper) return;
    photoEditorState.cropper.move(x, y);
    syncCropInputs();
}

function zoomCropper(amount) {
    if (!photoEditorState.cropper) return;
    photoEditorState.cropper.zoom(amount);
}

function rotateCropper(amount) {
    if (!photoEditorState.cropper) return;
    photoEditorState.cropper.rotate(amount);
    syncCropInputs();
}

function scaleCropper(x, y) {
    if (!photoEditorState.cropper) return;

    if (x !== 1) {
        photoEditorState.scaleX = photoEditorState.scaleX * -1;
        photoEditorState.cropper.scaleX(photoEditorState.scaleX);
    }

    if (y !== 1) {
        photoEditorState.scaleY = photoEditorState.scaleY * -1;
        photoEditorState.cropper.scaleY(photoEditorState.scaleY);
    }
}

function centerCropper() {
    if (!photoEditorState.cropper) return;
    var container = photoEditorState.cropper.getContainerData();
    var canvas = photoEditorState.cropper.getCanvasData();
    photoEditorState.cropper.setCanvasData({
        left: (container.width - canvas.width) / 2,
        top: (container.height - canvas.height) / 2,
    });
    syncCropInputs();
}

function fitCropperToFrame() {
    if (!photoEditorState.cropper) return;
    photoEditorState.cropper.reset();
    photoEditorState.scaleX = 1;
    photoEditorState.scaleY = 1;
    setCropperDragMode('move');
    syncCropInputs();
}

function resetCropper() {
    if (!photoEditorState.cropper) return;
    photoEditorState.cropper.reset();
    photoEditorState.scaleX = 1;
    photoEditorState.scaleY = 1;
    setCropperDragMode('move');
    syncCropInputs();
}

function formatBytes(bytes) {
    if (!bytes) return '0 KB';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

function uploadPhotos(files) {
    if (!files || files.length === 0) return;
    var content = document.getElementById('photoDropContent');
    var loading = document.getElementById('photoDropLoading');
    var drop = document.getElementById('photoDrop');
    content.style.display = 'none';
    loading.style.display = '';
    drop.style.pointerEvents = 'none';

    var list = document.getElementById('photoList');
    var empty = document.getElementById('photoEmpty');
    if (empty) empty.style.display = 'none';

    // Placeholder
    var ph = document.createElement('div');
    ph.className = 'photo-item-uploading';
    ph.innerHTML = '<div class="photo-spinner"></div><span style="font-size:0.75rem; color:var(--primary);">Subiendo ' + files.length + ' foto(s)...</span>';
    list.appendChild(ph);

    var fd = new FormData();
    fd.append('_token', csrfToken);
    for (var j = 0; j < files.length; j++) fd.append('photos[]', files[j]);

    fetch(photoStoreUrl, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        ph.remove();
        if (data.photos) {
            data.photos.forEach(function(photo) { addPhotoItem(photo); });
            updatePhotoCount(data.total);
            renumberPhotos();
        }
    })
    .catch(function() { ph.remove(); alert('Error al subir fotos'); })
    .finally(function() {
        content.style.display = '';
        loading.style.display = 'none';
        drop.style.pointerEvents = '';
        document.getElementById('photoFiles').value = '';
    });
}

function addPhotoItem(photo) {
    var list = document.getElementById('photoList');
    var div = document.createElement('div');
    div.className = 'photo-item' + (photo.is_primary ? ' is-primary' : '');
    div.id = 'photo-' + photo.id;
    div.dataset.id = photo.id;
    div.innerHTML =
        '<span class="photo-drag-handle" title="Arrastra para reordenar">&#9776;</span>' +
        '<div class="photo-item-thumb"><img src="' + photo.url + '" alt="" loading="lazy"></div>' +
        '<div class="photo-item-info">' +
            '<div class="photo-item-top">' +
                '<span class="photo-item-order"></span>' +
                (photo.is_primary ? '<span class="photo-badge-primary">&#9733; Principal</span>' : '') +
                '<div class="photo-item-actions">' +
                    '<button type="button" onclick="setPrimary(' + photo.id + ')" title="Principal">&#9733;</button>' +
                    '<button type="button" class="btn-del" onclick="deletePhoto(' + photo.id + ')" title="Eliminar">&#10005;</button>' +
                '</div>' +
            '</div>' +
            '<input type="text" class="photo-desc-input" placeholder="Descripcion SEO (alt text)..." value="" onchange="saveDescription(' + photo.id + ', this.value)">' +
        '</div>';
    list.appendChild(div);
}

function setPrimary(photoId) {
    var url = '/properties/' + propertyId + '/photos/' + photoId + '/primary';
    fetch(url, { method: 'PATCH', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.success) return;
        document.querySelectorAll('#photoList .photo-item').forEach(function(t) {
            t.classList.remove('is-primary');
            var badge = t.querySelector('.photo-badge-primary');
            if (badge) badge.remove();
        });
        var el = document.getElementById('photo-' + photoId);
        if (el) {
            el.classList.add('is-primary');
            var top = el.querySelector('.photo-item-top');
            var order = top.querySelector('.photo-item-order');
            var badge = document.createElement('span');
            badge.className = 'photo-badge-primary';
            badge.innerHTML = '&#9733; Principal';
            order.after(badge);
            renumberPhotos();
        }
    });
}

function deletePhoto(photoId) {
    if (!confirm('Eliminar foto?')) return;
    var el = document.getElementById('photo-' + photoId);
    if (el) el.style.opacity = '0.4';
    var url = '/properties/' + propertyId + '/photos/' + photoId;
    fetch(url, { method: 'DELETE', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.success) return;
        if (el) el.remove();
        updatePhotoCount(data.total);
        renumberPhotos();
        if (data.total === 0) {
            var emptyDiv = document.createElement('div');
            emptyDiv.id = 'photoEmpty';
            emptyDiv.style.cssText = 'text-align:center;padding:1.5rem 0.5rem;color:var(--text-muted);';
            emptyDiv.innerHTML = '<div style="font-size:2rem;opacity:0.3;margin-bottom:0.3rem;">&#127976;</div><p style="font-size:0.82rem;margin:0;">Sin fotos</p>';
            document.getElementById('photoList').after(emptyDiv);
        }
    })
    .catch(function() { if (el) el.style.opacity = '1'; });
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
    document.querySelectorAll('#photoList .photo-item').forEach(function(t, i) {
        var num = t.querySelector('.photo-item-order');
        if (num) num.textContent = i + 1;
    });
}

function saveOrder() {
    var items = document.querySelectorAll('#photoList .photo-item');
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
    drop.addEventListener('drop', function(e) { startPhotoEditing(e.dataTransfer.files); });
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

document.addEventListener('keydown', function(e) {
    var modal = document.getElementById('photoEditorModal');
    if (!modal || !modal.classList.contains('is-open')) return;

    if (e.key === 'Escape') {
        cancelPhotoEditing();
    }

    if ((e.metaKey || e.ctrlKey) && e.key === 'Enter') {
        e.preventDefault();
        saveEditedPhoto();
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
<script>
// Init SortableJS on photo list
var photoList = document.getElementById('photoList');
if (photoList) {
    new Sortable(photoList, {
        handle: '.photo-drag-handle',
        animation: 150,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        onEnd: function() {
            renumberPhotos();
            saveOrder();
        }
    });
}

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
