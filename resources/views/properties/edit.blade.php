@extends('layouts.app-sidebar')
@section('title', 'Editar Propiedad')

@section('styles')
<style>
/* Layout */
.edit-layout { display: grid; grid-template-columns: 1fr 320px; gap: 1.25rem; align-items: start; }
@media (max-width: 1024px) { .edit-layout { grid-template-columns: 1fr; } }

/* Tab nav — pill style matching index */
.tab-pills {
    display: flex; gap: 4px; margin-bottom: 1.25rem; overflow-x: auto; padding-bottom: 2px;
}
.tab-pill {
    padding: 0.45rem 0.9rem; border-radius: 20px; font-size: 0.78rem; font-weight: 500;
    border: 1px solid var(--border); background: var(--card); color: var(--text-muted);
    cursor: pointer; white-space: nowrap; transition: all 0.15s;
}
.tab-pill:hover { border-color: var(--primary); color: var(--text); }
.tab-pill.active { background: var(--primary); color: #fff; border-color: var(--primary); }

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
    padding: 1rem; text-align: center; cursor: pointer; transition: all 0.2s; margin-bottom: 0.75rem;
}
.photo-drop:hover, .photo-drop.dragover { border-color: var(--primary); background: rgba(102,126,234,0.03); }
.photo-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.4rem; }
.photo-thumb {
    position: relative; border-radius: 6px; overflow: hidden;
    aspect-ratio: 1; border: 2px solid transparent; transition: border-color 0.15s;
}
.photo-thumb.is-primary { border-color: var(--primary); }
.photo-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.photo-thumb-overlay {
    position: absolute; inset: 0; background: rgba(0,0,0,0.55);
    display: none; align-items: center; justify-content: center; gap: 0.2rem;
}
.photo-thumb:hover .photo-thumb-overlay { display: flex; }
.photo-thumb-overlay .btn { padding: 0.15rem 0.3rem; font-size: 0.6rem; }
.photo-primary-badge {
    position: absolute; top: 3px; left: 3px; font-size: 0.55rem;
    background: var(--primary); color: #fff; padding: 0.08rem 0.3rem;
    border-radius: 3px; font-weight: 600;
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
.yt-preview { border-radius: var(--radius); overflow: hidden; position: relative; padding-bottom: 56.25%; height: 0; background: #000; }
.yt-preview iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; }

/* Save bar */
.save-bar {
    background: var(--card); border: 1px solid var(--border); border-radius: 10px;
    padding: 0.75rem 1.25rem; margin-top: 1rem;
    display: flex; justify-content: space-between; align-items: center;
}
.save-bar-meta { font-size: 0.72rem; color: var(--text-muted); }

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
<div class="tab-pills" id="tabPills">
    <button class="tab-pill active" onclick="switchTab('general', this)">General</button>
    <button class="tab-pill" onclick="switchTab('location', this)">Ubicacion</button>
    <button class="tab-pill" onclick="switchTab('features', this)">Caracteristicas</button>
    <button class="tab-pill" onclick="switchTab('media', this)">Media</button>
    <button class="tab-pill" onclick="switchTab('integrations', this)">Integraciones</button>
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
                                    <option value="available" {{ old('status', $property->status) === 'available' ? 'selected' : '' }}>Disponible</option>
                                    <option value="sold" {{ old('status', $property->status) === 'sold' ? 'selected' : '' }}>Vendido</option>
                                    <option value="rented" {{ old('status', $property->status) === 'rented' ? 'selected' : '' }}>Rentado</option>
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
                    </div>
                </div>
            </div>

            {{-- TAB: Location --}}
            <div class="tab-panel" data-tab="location">
                <div class="card">
                    <div class="card-body">
                        <div class="section-label">Direccion de la propiedad</div>
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
                        <div class="section-label">Caracteristicas de la propiedad</div>
                        <div class="features-grid">
                            <div class="feature-card">
                                <div class="feature-icon">&#128716;</div>
                                <label>Recamaras</label>
                                <input type="number" name="bedrooms" value="{{ old('bedrooms', $property->bedrooms) }}" min="0" placeholder="0">
                            </div>
                            <div class="feature-card">
                                <div class="feature-icon">&#128705;</div>
                                <label>Banos</label>
                                <input type="number" name="bathrooms" value="{{ old('bathrooms', $property->bathrooms) }}" min="0" step="0.5" placeholder="0">
                            </div>
                            <div class="feature-card">
                                <div class="feature-icon">&#128207;</div>
                                <label>Area m&sup2;</label>
                                <input type="number" name="area" value="{{ old('area', $property->area) }}" min="0" step="0.01" placeholder="0">
                            </div>
                            <div class="feature-card">
                                <div class="feature-icon">&#128663;</div>
                                <label>Estacionamiento</label>
                                <input type="number" name="parking" value="{{ old('parking', $property->parking) }}" min="0" placeholder="0">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB: Media --}}
            <div class="tab-panel" data-tab="media">
                <div class="card">
                    <div class="card-body">
                        <div class="section-label">Descripcion del anuncio</div>
                        <div class="form-group">
                            <textarea name="description" class="form-textarea" rows="6" placeholder="Describe la propiedad con detalle...">{{ old('description', $property->description) }}</textarea>
                        </div>

                        <div class="section-label" style="margin-top:1.25rem;">Video de YouTube</div>
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
                    </div>
                </div>
            </div>

            {{-- TAB: Integrations --}}
            <div class="tab-panel" data-tab="integrations">
                <div class="card">
                    <div class="card-body">
                        <div class="section-label">EasyBroker</div>
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
                                    <button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('ebPublishForm').submit()">Actualizar</button>
                                    <button type="button" class="btn btn-sm btn-outline" style="color:#a16207; border-color:#a16207;" onclick="document.getElementById('ebUnpublishForm').submit()">Despublicar</button>
                                @else
                                    <button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('ebPublishForm').submit()">Publicar en EasyBroker</button>
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

    {{-- RIGHT: Photo panel + info --}}
    <div class="photos-panel">
        {{-- Photos --}}
        <div class="side-card">
            <div class="side-card-header" style="display:flex; justify-content:space-between; align-items:center;">
                Fotografias
                @php $photoCount = $property->photos->count(); @endphp
                <span style="font-size:0.68rem; color:var(--text-muted); background:var(--bg); padding:0.1rem 0.45rem; border-radius:10px;">{{ $photoCount }}/20</span>
            </div>
            <div class="side-card-body">
                @if($photoCount < 20)
                <form method="POST" action="{{ route('properties.photos.store', $property) }}" enctype="multipart/form-data" id="photoForm">
                    @csrf
                    <div class="photo-drop" id="photoDrop" onclick="document.getElementById('photoFiles').click()">
                        <input type="file" id="photoFiles" name="photos[]" accept="image/*" multiple style="display:none" onchange="document.getElementById('photoForm').submit()">
                        <div style="font-size:1.5rem; opacity:0.4; margin-bottom:0.15rem;">&#128247;</div>
                        <p style="margin:0; font-size:0.78rem; color:var(--text-muted);">Arrastra o clic para subir</p>
                    </div>
                </form>
                @endif

                @if($photoCount > 0)
                <div class="photo-grid">
                    @foreach($property->photos as $photo)
                    <div class="photo-thumb {{ $photo->is_primary ? 'is-primary' : '' }}">
                        <img src="{{ asset('storage/' . $photo->path) }}" alt="">
                        @if($photo->is_primary)<span class="photo-primary-badge">Principal</span>@endif
                        <div class="photo-thumb-overlay">
                            @if(!$photo->is_primary)
                            <form method="POST" action="{{ route('properties.photos.primary', [$property, $photo]) }}">@csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm" style="background:#fff; color:var(--text);" title="Principal">&#9733;</button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('properties.photos.destroy', [$property, $photo]) }}" onsubmit="return confirm('Eliminar foto?')">@csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">&#10005;</button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div style="text-align:center; padding:1rem 0.5rem; color:var(--text-muted); font-size:0.82rem;">Sin fotos aun</div>
                @endif
            </div>
        </div>

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
function previewYoutube() {
    var url = document.getElementById('youtubeInput').value;
    var container = document.getElementById('ytPreviewContainer');
    var match = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([\w-]{11})/);
    container.innerHTML = match ? '<div class="yt-preview" style="margin-top:0.75rem;"><iframe src="https://www.youtube.com/embed/' + match[1] + '" allowfullscreen></iframe></div>' : '';
}
// Drag & drop
(function() {
    var drop = document.getElementById('photoDrop');
    if (!drop) return;
    ['dragenter','dragover'].forEach(function(ev) { drop.addEventListener(ev, function(e) { e.preventDefault(); drop.classList.add('dragover'); }); });
    ['dragleave','drop'].forEach(function(ev) { drop.addEventListener(ev, function(e) { e.preventDefault(); drop.classList.remove('dragover'); }); });
    drop.addEventListener('drop', function(e) {
        document.getElementById('photoFiles').files = e.dataTransfer.files;
        document.getElementById('photoForm').submit();
    });
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
@endsection
