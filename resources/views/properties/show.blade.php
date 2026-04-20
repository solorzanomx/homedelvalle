@extends('layouts.app-sidebar')
@section('title', $property->title)

@section('styles')
<style>
.prop-layout { display: grid; grid-template-columns: 1fr 360px; gap: 1.25rem; align-items: start; }
@media (max-width: 1024px) { .prop-layout { grid-template-columns: 1fr; } }

/* Hero gallery */
.prop-hero {
    background: var(--card); border: 1px solid var(--border); border-radius: 12px;
    overflow: hidden; margin-bottom: 1.25rem;
}
.prop-gallery { display: grid; grid-template-columns: 1fr 1fr; grid-template-rows: 200px 200px; gap: 3px; position: relative; }
.prop-gallery.single { grid-template-columns: 1fr; grid-template-rows: 380px; }
.prop-gallery.two { grid-template-columns: 1fr 1fr; grid-template-rows: 380px; }
.prop-gallery.three-plus .gal-main { grid-row: 1 / 3; }
.prop-gallery-item { overflow: hidden; cursor: pointer; position: relative; }
.prop-gallery-item img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.3s; }
.prop-gallery-item:hover img { transform: scale(1.03); }
.gal-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea, #764ba2); color: rgba(255,255,255,0.4); font-size: 4rem; }
.gal-more {
    position: absolute; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center;
    justify-content: center; color: #fff; font-size: 1.2rem; font-weight: 600;
}

/* Lightbox */
.lb-overlay {
    display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.92); z-index: 1000;
    align-items: center; justify-content: center; flex-direction: column;
}
.lb-overlay.show { display: flex; }
.lb-img { max-width: 90vw; max-height: 80vh; object-fit: contain; border-radius: 8px; }
.lb-nav {
    position: absolute; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.15);
    border: none; color: #fff; font-size: 1.5rem; width: 44px; height: 44px; border-radius: 50%;
    cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.15s;
}
.lb-nav:hover { background: rgba(255,255,255,0.3); }
.lb-prev { left: 16px; }
.lb-next { right: 16px; }
.lb-close {
    position: absolute; top: 16px; right: 16px; background: rgba(255,255,255,0.15);
    border: none; color: #fff; font-size: 1.3rem; width: 40px; height: 40px; border-radius: 50%;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
}
.lb-counter { color: rgba(255,255,255,0.6); font-size: 0.82rem; margin-top: 0.75rem; }
.lb-desc { color: rgba(255,255,255,0.8); font-size: 0.78rem; margin-top: 0.4rem; max-width: 600px; text-align: center; }

/* Property header */
.prop-header { padding: 1.25rem; }
.prop-header-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; margin-bottom: 0.5rem; }
.prop-price { font-size: 1.5rem; font-weight: 700; color: var(--text); line-height: 1; }
.prop-currency { font-size: 0.85rem; font-weight: 500; color: var(--text-muted); margin-left: 0.25rem; }
.prop-title { font-size: 1.05rem; font-weight: 600; color: var(--text); margin-bottom: 0.2rem; }
.prop-loc { font-size: 0.82rem; color: var(--text-muted); }

/* Badges row */
.prop-badges-row { display: flex; gap: 0.4rem; flex-wrap: wrap; padding: 0 1.25rem 0.75rem; }
.badge-purple { background: #f3f0ff; color: #6d28d9; }

/* Quick specs bar */
.prop-specs-bar {
    display: flex; gap: 0; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);
}
.prop-spec-item {
    flex: 1; text-align: center; padding: 0.65rem 0.5rem; border-right: 1px solid var(--border);
    font-size: 0.78rem;
}
.prop-spec-item:last-child { border-right: none; }
.prop-spec-val { font-weight: 700; font-size: 1rem; color: var(--text); }
.prop-spec-lbl { font-size: 0.68rem; color: var(--text-muted); }

/* Action bar */
.prop-actions {
    display: flex; gap: 0.5rem; padding: 0.75rem 1.25rem;
}
.prop-actions .btn { flex: 1; justify-content: center; }

/* Detail grid */
.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem 1.25rem; }
.detail-item { padding: 0.4rem 0; }
.detail-label { font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 0.1rem; }
.detail-value { font-size: 0.88rem; font-weight: 500; }

/* Feature cards */
.feature-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem; }
.feature-card {
    background: var(--bg); border-radius: var(--radius); padding: 0.75rem; text-align: center;
}
.feature-card-val { font-size: 1.2rem; font-weight: 700; color: var(--text); }
.feature-card-lbl { font-size: 0.7rem; color: var(--text-muted); margin-top: 2px; }

/* Side card */
.side-card { background: var(--card); border: 1px solid var(--border); border-radius: 10px; margin-bottom: 1rem; overflow: hidden; }
.side-card-header {
    padding: 0.8rem 1rem; border-bottom: 1px solid var(--border); font-weight: 600; font-size: 0.85rem;
    display: flex; justify-content: space-between; align-items: center;
}
.side-card-body { padding: 1rem; }
.side-card-count {
    font-size: 0.7rem; background: var(--primary); color: #fff; padding: 0.1rem 0.5rem;
    border-radius: 10px; font-weight: 600;
}

/* Video */
.video-container { position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: var(--radius); }
.video-container iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; }

/* CRM Section cards */
.crm-section { background: var(--card); border: 1px solid var(--border); border-radius: 10px; margin-bottom: 1.25rem; overflow: hidden; }
.crm-section-header {
    padding: 0.8rem 1.25rem; border-bottom: 1px solid var(--border);
    display: flex; justify-content: space-between; align-items: center;
}
.crm-section-header h4 { font-size: 0.88rem; font-weight: 600; margin: 0; }
.crm-section-body { padding: 1rem 1.25rem; }
.crm-empty { font-size: 0.82rem; color: var(--text-muted); text-align: center; padding: 1.5rem 0; }

/* Tab pills for CRM */
.crm-tabs { display: flex; gap: 0.35rem; padding: 0.75rem 1.25rem; border-bottom: 1px solid var(--border); overflow-x: auto; }
.crm-tab {
    padding: 0.35rem 0.75rem; border-radius: 16px; font-size: 0.75rem; font-weight: 500;
    border: 1px solid var(--border); background: var(--card); color: var(--text-muted);
    cursor: pointer; white-space: nowrap; transition: all 0.15s;
}
.crm-tab:hover { border-color: var(--primary); color: var(--text); }
.crm-tab.active { background: var(--primary); color: #fff; border-color: var(--primary); }

/* Client row */
.client-row {
    display: flex; align-items: center; gap: 0.75rem; padding: 0.6rem 0;
    border-bottom: 1px solid var(--border);
}
.client-row:last-child { border-bottom: none; }
.client-avatar {
    width: 36px; height: 36px; border-radius: 50%; background: var(--primary); color: #fff;
    display: flex; align-items: center; justify-content: center; font-weight: 600;
    font-size: 0.78rem; flex-shrink: 0;
}
.client-info { flex: 1; min-width: 0; }
.client-name { font-size: 0.85rem; font-weight: 500; }
.client-meta { font-size: 0.72rem; color: var(--text-muted); }
.client-tags { display: flex; gap: 0.3rem; flex-shrink: 0; }

/* Deal row */
.deal-row {
    display: flex; align-items: center; gap: 0.75rem; padding: 0.65rem 0;
    border-bottom: 1px solid var(--border);
}
.deal-row:last-child { border-bottom: none; }
.deal-stage {
    font-size: 0.7rem; font-weight: 600; padding: 0.2rem 0.55rem; border-radius: 12px;
    color: #fff; flex-shrink: 0;
}
.deal-info { flex: 1; min-width: 0; }
.deal-client { font-size: 0.85rem; font-weight: 500; }
.deal-meta { font-size: 0.72rem; color: var(--text-muted); }
.deal-amount { font-size: 0.85rem; font-weight: 600; color: var(--primary); flex-shrink: 0; }

/* Operation row */
.op-row {
    display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.65rem 0;
    border-bottom: 1px solid var(--border);
}
.op-row:last-child { border-bottom: none; }
.op-type-badge {
    font-size: 0.68rem; font-weight: 600; padding: 0.15rem 0.5rem; border-radius: 4px;
    text-transform: uppercase; letter-spacing: 0.3px; flex-shrink: 0;
}
.op-type-venta { background: #dbeafe; color: #1d4ed8; }
.op-type-renta { background: #fef3c7; color: #92400e; }
.op-type-captacion { background: #f3e8ff; color: #7c3aed; }

/* Timeline */
.timeline-item {
    display: flex; gap: 0.75rem; padding: 0.55rem 0; border-bottom: 1px solid var(--border);
}
.timeline-item:last-child { border-bottom: none; }
.timeline-icon {
    width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center;
    justify-content: center; font-size: 0.8rem; flex-shrink: 0;
}
.timeline-icon-call { background: #dbeafe; color: #2563eb; }
.timeline-icon-email { background: #fce7f3; color: #db2777; }
.timeline-icon-visit { background: #d1fae5; color: #059669; }
.timeline-icon-meeting { background: #fef3c7; color: #d97706; }
.timeline-icon-whatsapp { background: #dcfce7; color: #16a34a; }
.timeline-icon-note { background: #f1f5f9; color: #64748b; }
.timeline-body { flex: 1; min-width: 0; }
.timeline-text { font-size: 0.82rem; }
.timeline-meta { font-size: 0.7rem; color: var(--text-muted); margin-top: 0.15rem; }

/* Email row */
.email-row {
    display: flex; align-items: center; gap: 0.75rem; padding: 0.6rem 0;
    border-bottom: 1px solid var(--border);
}
.email-row:last-child { border-bottom: none; }
.email-status { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.email-status-sent { background: var(--success); }
.email-status-failed { background: var(--danger); }
.email-status-pending { background: #f59e0b; }
.email-info { flex: 1; min-width: 0; }
.email-subject { font-size: 0.82rem; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.email-to { font-size: 0.72rem; color: var(--text-muted); }
.email-stats { flex-shrink: 0; text-align: right; }
.email-opens { font-size: 0.7rem; color: var(--text-muted); }
.email-date { font-size: 0.7rem; color: var(--text-muted); }

@media (max-width: 768px) {
    .prop-gallery { grid-template-rows: 180px 180px; }
    .prop-gallery.single { grid-template-rows: 240px; }
    .prop-gallery.two { grid-template-rows: 240px; }
    .feature-cards { grid-template-columns: repeat(2, 1fr); }
    .deal-row, .op-row { flex-wrap: wrap; }
    .prop-header-top { flex-direction: column; }
    .prop-specs-bar { flex-wrap: wrap; }
    .prop-spec-item { flex: 1 1 auto; min-width: 25%; }
}
</style>
@endsection

@section('content')
@php
    $types = ['House'=>'Casa','Apartment'=>'Depto','Land'=>'Terreno','Office'=>'Oficina','Commercial'=>'Comercial','Warehouse'=>'Bodega','Building'=>'Edificio'];
    $opLabels = ['sale'=>'Venta','rental'=>'Renta','temporary_rental'=>'Renta Temporal'];
    $photos = $property->photos->sortBy(fn($p) => $p->is_primary ? 0 : 1)->values();
    $primary = $property->primaryPhoto();
    $mainSrc = $primary ? asset('storage/' . $primary->path) : ($property->photo ? asset('storage/' . $property->photo) : null);

    $dealStages = [
        'lead' => ['Lead', '#94a3b8'], 'contact' => ['Contacto', '#60a5fa'], 'visit' => ['Visita', '#818cf8'],
        'negotiation' => ['Negociacion', '#a78bfa'], 'offer' => ['Oferta', '#f59e0b'],
        'closing' => ['Cierre', '#f97316'], 'won' => ['Ganado', '#10b981'], 'lost' => ['Perdido', '#ef4444'],
    ];
    $interactionIcons = [
        'call' => ['&#128222;', 'timeline-icon-call'], 'email' => ['&#9993;', 'timeline-icon-email'],
        'visit' => ['&#127968;', 'timeline-icon-visit'], 'meeting' => ['&#128197;', 'timeline-icon-meeting'],
        'whatsapp' => ['&#128172;', 'timeline-icon-whatsapp'], 'note' => ['&#128221;', 'timeline-icon-note'],
    ];
@endphp

{{-- Breadcrumb --}}
<div style="margin-bottom:1rem;">
    <a href="{{ route('properties.index') }}" style="font-size:0.82rem; color:var(--text-muted);">&#8592; Propiedades</a>
</div>

{{-- Hero Card --}}
<div class="prop-hero">
    {{-- Gallery Grid --}}
    @php
        $photoCount = $photos->count();
        $galClass = $photoCount === 0 ? 'single' : ($photoCount === 1 ? 'single' : ($photoCount === 2 ? 'two' : 'three-plus'));
    @endphp
    <div class="prop-gallery {{ $galClass }}">
        @if($photoCount === 0)
            <div class="prop-gallery-item"><div class="gal-placeholder">&#8962;</div></div>
        @elseif($photoCount === 1)
            <div class="prop-gallery-item" onclick="openLightbox(0)">
                <img src="{{ asset('storage/' . $photos[0]->path) }}" alt="{{ $property->title }}">
            </div>
        @elseif($photoCount === 2)
            @foreach($photos->take(2) as $i => $photo)
            <div class="prop-gallery-item" onclick="openLightbox({{ $i }})">
                <img src="{{ asset('storage/' . $photo->path) }}" alt="">
            </div>
            @endforeach
        @else
            <div class="prop-gallery-item gal-main" onclick="openLightbox(0)">
                <img src="{{ asset('storage/' . $photos[0]->path) }}" alt="{{ $property->title }}">
            </div>
            @foreach($photos->slice(1, 2) as $i => $photo)
            <div class="prop-gallery-item" onclick="openLightbox({{ $loop->index + 1 }})" style="position:relative;">
                <img src="{{ asset('storage/' . $photo->path) }}" alt="">
                @if($loop->last && $photoCount > 3)
                <div class="gal-more">+{{ $photoCount - 3 }} mas</div>
                @endif
            </div>
            @endforeach
        @endif
    </div>

    {{-- Property Header --}}
    <div class="prop-header">
        <div class="prop-header-top">
            <div>
                <div class="prop-price">${{ number_format($property->price, 0) }}<span class="prop-currency">{{ $property->currency ?? 'MXN' }}</span></div>
            </div>
            <div style="display:flex; gap:0.35rem;">
                <button class="btn btn-sm btn-outline" onclick="shareWhatsApp()" title="WhatsApp" style="color:#25d366; border-color:#25d366;">&#128172;</button>
                <button class="btn btn-sm btn-outline" onclick="shareEmail()" title="Email">&#9993;</button>
                <button class="btn btn-sm btn-outline" onclick="copyPropertyLink(this)" title="Copiar enlace">&#128279;</button>
            </div>
        </div>
        <div class="prop-title">{{ $property->title }}</div>
        <div class="prop-loc">{{ implode(', ', array_filter([$property->address, $property->colony, $property->city])) ?: 'Sin ubicacion' }}@if($property->zipcode) &middot; C.P. {{ $property->zipcode }}@endif</div>
    </div>

    <div class="prop-badges-row">
        <span class="badge badge-blue">{{ $types[$property->property_type] ?? $property->property_type }}</span>
        @if($property->operation_type)<span class="badge badge-purple">{{ $opLabels[$property->operation_type] ?? $property->operation_type }}</span>@endif
        @if($property->status === 'sold')<span class="badge badge-red">Vendido</span>@elseif($property->status === 'rented')<span class="badge badge-yellow">Rentado</span>@else<span class="badge badge-green">Disponible</span>@endif
        @if($property->isPublishedToEasyBroker())<span class="badge" style="background:#ecfdf5; color:#065f46;">EasyBroker</span>@endif
    </div>

    {{-- Quick Specs --}}
    @if($property->bedrooms !== null || $property->bathrooms !== null || $property->area || $property->parking !== null)
    <div class="prop-specs-bar">
        @if($property->bedrooms !== null)
        <div class="prop-spec-item"><div class="prop-spec-val">{{ $property->bedrooms }}</div><div class="prop-spec-lbl">Recamaras</div></div>
        @endif
        @if($property->bathrooms !== null)
        <div class="prop-spec-item"><div class="prop-spec-val">{{ $property->bathrooms }}</div><div class="prop-spec-lbl">Banos</div></div>
        @endif
        @if($property->area)
        <div class="prop-spec-item"><div class="prop-spec-val">{{ number_format($property->area, 0) }}</div><div class="prop-spec-lbl">m&sup2;</div></div>
        @endif
        @if($property->parking !== null)
        <div class="prop-spec-item"><div class="prop-spec-val">{{ $property->parking }}</div><div class="prop-spec-lbl">Estacionam.</div></div>
        @endif
    </div>
    @endif

    <div class="prop-actions">
        <a href="{{ route('properties.edit', $property) }}" class="btn btn-primary">&#9998; Editar</a>
        <a href="{{ route('properties.pdf', $property) }}" target="_blank" class="btn btn-outline">&#128196; Ficha PDF</a>
        <button type="button" onclick="document.getElementById('fichaModal').style.display='flex'" class="btn btn-outline">&#9993; Enviar ficha</button>
        @if($property->broker && $property->broker->phone)
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $property->broker->phone) }}" target="_blank" class="btn btn-outline" style="color:#25d366; border-color:#25d366;">&#128172; Broker</a>
        @endif
        <a href="{{ route('properties.index') }}" class="btn btn-outline">Volver</a>
    </div>
</div>

{{-- Lightbox --}}
<div class="lb-overlay" id="lightbox" onclick="if(event.target===this) closeLightbox()">
    <button class="lb-close" onclick="closeLightbox()">&times;</button>
    <button class="lb-nav lb-prev" onclick="lbNav(-1)">&#8249;</button>
    <img class="lb-img" id="lbImg" src="" alt="">
    <button class="lb-nav lb-next" onclick="lbNav(1)">&#8250;</button>
    <div class="lb-counter" id="lbCounter"></div>
    <div class="lb-desc" id="lbDesc"></div>
</div>

<div class="prop-layout">
    {{-- LEFT: Details + CRM --}}
    <div>
        {{-- Features --}}
        @if($property->bedrooms !== null || $property->bathrooms !== null || $property->area || $property->parking !== null || $property->floors || $property->half_bathrooms)
        <div class="card" style="margin-bottom:1.25rem;">
            <div class="card-body">
                <div style="font-weight:600; font-size:0.9rem; margin-bottom:0.75rem;">Caracteristicas</div>
                <div class="feature-cards">
                    @if($property->bedrooms !== null)
                    <div class="feature-card"><div class="feature-card-val">{{ $property->bedrooms }}</div><div class="feature-card-lbl">Recamaras</div></div>
                    @endif
                    @if($property->bathrooms !== null)
                    <div class="feature-card"><div class="feature-card-val">{{ $property->bathrooms }}</div><div class="feature-card-lbl">Banos</div></div>
                    @endif
                    @if($property->half_bathrooms)
                    <div class="feature-card"><div class="feature-card-val">{{ $property->half_bathrooms }}</div><div class="feature-card-lbl">Medios banos</div></div>
                    @endif
                    @if($property->parking !== null)
                    <div class="feature-card"><div class="feature-card-val">{{ $property->parking }}</div><div class="feature-card-lbl">Estacionamiento</div></div>
                    @endif
                    @if($property->floors)
                    <div class="feature-card"><div class="feature-card-val">{{ $property->floors }}</div><div class="feature-card-lbl">Pisos</div></div>
                    @endif
                </div>
                @if($property->lot_area || $property->construction_area || $property->area)
                <div class="feature-cards" style="margin-top:0.5rem;">
                    @if($property->lot_area)
                    <div class="feature-card"><div class="feature-card-val">{{ number_format($property->lot_area, 0) }}</div><div class="feature-card-lbl">m&sup2; Terreno</div></div>
                    @endif
                    @if($property->construction_area)
                    <div class="feature-card"><div class="feature-card-val">{{ number_format($property->construction_area, 0) }}</div><div class="feature-card-lbl">m&sup2; Construccion</div></div>
                    @endif
                    @if($property->area)
                    <div class="feature-card"><div class="feature-card-val">{{ number_format($property->area, 0) }}</div><div class="feature-card-lbl">m&sup2; Total</div></div>
                    @endif
                </div>
                @endif
                @if($property->year_built || $property->maintenance_fee || $property->furnished)
                <div style="display:flex; gap:1.5rem; flex-wrap:wrap; margin-top:0.75rem; font-size:0.85rem; color:var(--text-muted);">
                    @if($property->year_built)
                    <span>Ano: <strong style="color:var(--text);">{{ $property->year_built }}</strong></span>
                    @endif
                    @if($property->maintenance_fee)
                    <span>Mantenimiento: <strong style="color:var(--text);">${{ number_format($property->maintenance_fee, 0) }}/mes</strong></span>
                    @endif
                    @if($property->furnished)
                    <span>{{ match($property->furnished) { 'amueblado' => 'Amueblado', 'semi_amueblado' => 'Semi amueblado', default => 'Sin amueblar' } }}</span>
                    @endif
                </div>
                @endif
                @if($property->amenities && count($property->amenities))
                <div style="margin-top:0.75rem;">
                    <div style="font-size:0.8rem; color:var(--text-muted); margin-bottom:0.35rem;">Amenidades</div>
                    <div style="display:flex; flex-wrap:wrap; gap:0.3rem;">
                        @foreach($property->amenities as $amenity)
                        <span class="badge badge-blue" style="font-size:0.72rem;">{{ str_replace('_', ' ', ucfirst($amenity)) }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Description --}}
        @if($property->description)
        <div class="card" style="margin-bottom:1.25rem;">
            <div class="card-body">
                <div style="font-weight:600; font-size:0.9rem; margin-bottom:0.5rem;">Descripcion</div>
                <div style="font-size:0.88rem; line-height:1.65; white-space:pre-line; color:var(--text);">{{ $property->description }}</div>
            </div>
        </div>
        @endif

        {{-- YouTube --}}
        @if($property->youtube_url)
        <div class="card" style="margin-bottom:1.25rem;">
            <div class="card-body">
                <div style="font-weight:600; font-size:0.9rem; margin-bottom:0.5rem;">Video</div>
                @php
                    $ytId = null;
                    if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([\w-]{11})/', $property->youtube_url, $m)) $ytId = $m[1];
                @endphp
                @if($ytId)
                    <div class="video-container"><iframe src="https://www.youtube.com/embed/{{ $ytId }}" allowfullscreen></iframe></div>
                @else
                    <a href="{{ $property->youtube_url }}" target="_blank" class="btn btn-outline btn-sm">Ver video &rarr;</a>
                @endif
            </div>
        </div>
        @endif

        {{-- ═══════════════════════════════════════════════════════ --}}
        {{-- CRM SECTION: Pipeline / Deals                         --}}
        {{-- ═══════════════════════════════════════════════════════ --}}
        <div class="crm-section">
            <div class="crm-section-header">
                <h4>&#128200; Pipeline (Deals)</h4>
                @if($deals->count())
                <span class="side-card-count">{{ $deals->count() }}</span>
                @endif
            </div>
            <div class="crm-section-body">
                @forelse($deals as $deal)
                <div class="deal-row">
                    @php $ds = $dealStages[$deal->stage] ?? [$deal->stage, '#94a3b8']; @endphp
                    <span class="deal-stage" style="background:{{ $ds[1] }}">{{ $ds[0] }}</span>
                    <div class="deal-info">
                        <div class="deal-client">
                            @if($deal->client)
                                <a href="{{ route('clients.show', $deal->client) }}" style="color:var(--text); text-decoration:none;">{{ $deal->client->name }}</a>
                            @else
                                Sin cliente
                            @endif
                        </div>
                        <div class="deal-meta">
                            {{ $deal->created_at->format('d/m/Y') }}
                            @if($deal->expected_close_date) &middot; Cierre est: {{ $deal->expected_close_date->format('d/m/Y') }} @endif
                            @if($deal->broker) &middot; {{ $deal->broker->name }} @endif
                        </div>
                    </div>
                    @if($deal->amount > 0)
                    <div class="deal-amount">${{ number_format($deal->amount, 0) }}</div>
                    @endif
                </div>
                @empty
                <div class="crm-empty">No hay deals asociados a esta propiedad</div>
                @endforelse
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════ --}}
        {{-- CRM SECTION: Operaciones                               --}}
        {{-- ═══════════════════════════════════════════════════════ --}}
        <div class="crm-section">
            <div class="crm-section-header">
                <h4>&#128204; Operaciones</h4>
                @if($operations->count())
                <span class="side-card-count">{{ $operations->count() }}</span>
                @endif
            </div>
            <div class="crm-section-body">
                @forelse($operations as $op)
                <div class="op-row">
                    <span class="op-type-badge op-type-{{ $op->type }}">{{ $op->type }}</span>
                    <div style="flex:1; min-width:0;">
                        <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                            <span style="font-size:0.85rem; font-weight:500;">
                                @if($op->client)
                                    <a href="{{ route('clients.show', $op->client) }}" style="color:var(--text); text-decoration:none;">{{ $op->client->name }}</a>
                                @endif
                            </span>
                            @if($op->secondaryClient)
                            <span style="font-size:0.72rem; color:var(--text-muted);">
                                + {{ $op->secondaryClient->name }}
                            </span>
                            @endif
                            <span class="badge" style="background:{{ \App\Models\Operation::STAGE_COLORS[$op->stage] ?? '#94a3b8' }}; color:#fff; font-size:0.65rem;">
                                {{ \App\Models\Operation::STAGES[$op->stage] ?? $op->stage }}
                            </span>
                        </div>
                        <div style="font-size:0.72rem; color:var(--text-muted); margin-top:0.2rem;">
                            {{ $op->created_at->format('d/m/Y') }}
                            @if($op->status === 'cancelled') &middot; <span style="color:var(--danger)">Cancelada</span> @endif
                            @if($op->status === 'completed') &middot; <span style="color:var(--success)">Completada</span> @endif
                            @if($op->amount > 0) &middot; ${{ number_format($op->amount, 0) }} @endif
                            @if($op->monthly_rent > 0) &middot; ${{ number_format($op->monthly_rent, 0) }}/mes @endif
                            @if($op->user) &middot; {{ $op->user->name }} @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="crm-empty">No hay operaciones asociadas</div>
                @endforelse
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════ --}}
        {{-- CRM SECTION: Correos enviados                          --}}
        {{-- ═══════════════════════════════════════════════════════ --}}
        <div class="crm-section">
            <div class="crm-section-header">
                <h4>&#9993; Correos Enviados</h4>
                @if($emails->count())
                <span class="side-card-count">{{ $emails->count() }}</span>
                @endif
            </div>
            <div class="crm-section-body">
                @forelse($emails as $email)
                <div class="email-row">
                    <span class="email-status email-status-{{ $email->status }}"></span>
                    <div class="email-info">
                        <div class="email-subject">{{ $email->subject }}</div>
                        <div class="email-to">
                            Para: {{ $email->client->name ?? '—' }} &lt;{{ $email->client->email ?? '' }}&gt;
                            &middot; Por: {{ $email->user->name ?? '—' }}
                        </div>
                    </div>
                    <div class="email-stats">
                        @if($email->open_count > 0)
                        <div class="email-opens" style="color:var(--success);">&#128065; {{ $email->open_count }}x abierto</div>
                        @else
                        <div class="email-opens">Sin abrir</div>
                        @endif
                        <div class="email-date">{{ $email->created_at->format('d/m/y H:i') }}</div>
                    </div>
                </div>
                @empty
                <div class="crm-empty">Esta propiedad no se ha enviado por correo</div>
                @endforelse
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════ --}}
        {{-- CRM SECTION: Historial de Interacciones                --}}
        {{-- ═══════════════════════════════════════════════════════ --}}
        <div class="crm-section">
            <div class="crm-section-header">
                <h4>&#128337; Historial de Actividad</h4>
                @if($interactions->count())
                <span class="side-card-count">{{ $interactions->count() }}</span>
                @endif
            </div>
            @if($interactions->count())
            <div class="crm-tabs">
                <button class="crm-tab active" onclick="filterTimeline('all', this)">Todos</button>
                <button class="crm-tab" onclick="filterTimeline('call', this)">Llamadas</button>
                <button class="crm-tab" onclick="filterTimeline('email', this)">Correos</button>
                <button class="crm-tab" onclick="filterTimeline('visit', this)">Visitas</button>
                <button class="crm-tab" onclick="filterTimeline('whatsapp', this)">WhatsApp</button>
                <button class="crm-tab" onclick="filterTimeline('note', this)">Notas</button>
            </div>
            @endif
            <div class="crm-section-body" id="timelineBody">
                @forelse($interactions as $int)
                @php $ic = $interactionIcons[$int->type] ?? ['&#128221;', 'timeline-icon-note']; @endphp
                <div class="timeline-item" data-type="{{ $int->type }}">
                    <div class="timeline-icon {{ $ic[1] }}">{!! $ic[0] !!}</div>
                    <div class="timeline-body">
                        <div class="timeline-text">{{ $int->description }}</div>
                        <div class="timeline-meta">
                            @if($int->client)
                                <a href="{{ route('clients.show', $int->client) }}" style="color:var(--primary); text-decoration:none;">{{ $int->client->name }}</a> &middot;
                            @endif
                            {{ $int->user->name ?? '' }} &middot;
                            {{ $int->completed_at ? $int->completed_at->format('d/m/y H:i') : $int->created_at->format('d/m/y H:i') }}
                        </div>
                    </div>
                </div>
                @empty
                <div class="crm-empty">No hay interacciones registradas para esta propiedad</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- RIGHT: Sidebar --}}
    <div>
        {{-- Owner (Propietario) --}}
        <div class="side-card">
            <div class="side-card-header">Propietario</div>
            <div class="side-card-body">
                @if($property->owner)
                <div style="display:flex; align-items:center; gap:0.75rem;">
                    <div class="client-avatar">{{ strtoupper(substr($property->owner->name, 0, 1)) }}{{ strtoupper(substr($property->owner->last_name ?? '', 0, 1)) }}</div>
                    <div style="flex:1; min-width:0;">
                        <div style="font-weight:500; font-size:0.88rem;">
                            <a href="{{ route('clients.show', $property->owner) }}" style="color:var(--text); text-decoration:none;">{{ $property->owner->name }}</a>
                        </div>
                        @if($property->owner->email)<div style="font-size:0.78rem; color:var(--text-muted);">{{ $property->owner->email }}</div>@endif
                        @if($property->owner->phone)<div style="font-size:0.78rem; color:var(--text-muted);">{{ $property->owner->phone }}</div>@endif
                    </div>
                </div>
                @if($property->owner->phone)
                <div style="display:flex; gap:0.4rem; margin-top:0.75rem;">
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $property->owner->phone) }}" target="_blank" class="btn btn-sm btn-outline" style="flex:1; justify-content:center; color:#25d366; border-color:#25d366;">WhatsApp</a>
                    <a href="tel:{{ $property->owner->phone }}" class="btn btn-sm btn-outline" style="flex:1; justify-content:center;">Llamar</a>
                </div>
                @endif
                @else
                <div class="crm-empty" style="padding:0.5rem 0;">Sin propietario asignado</div>
                @endif
            </div>
        </div>

        {{-- Interested Clients --}}
        <div class="side-card">
            <div class="side-card-header">
                <span>Clientes Vinculados</span>
                @if($interestedClients->count())
                <span class="side-card-count">{{ $interestedClients->count() }}</span>
                @endif
            </div>
            <div class="side-card-body">
                @forelse($interestedClients as $client)
                <div class="client-row">
                    <div class="client-avatar">{{ strtoupper(substr($client->name, 0, 1)) }}{{ strtoupper(substr($client->last_name ?? '', 0, 1)) }}</div>
                    <div class="client-info">
                        <div class="client-name">
                            <a href="{{ route('clients.show', $client) }}" style="color:var(--text); text-decoration:none;">{{ $client->name }}</a>
                        </div>
                        <div class="client-meta">
                            {{ $client->email ?? '' }}
                            @if($client->phone) &middot; {{ $client->phone }} @endif
                        </div>
                    </div>
                    <div class="client-tags">
                        @php
                            $clientDeals = $deals->where('client_id', $client->id);
                            $clientOps = $operations->where('client_id', $client->id);
                            $clientEmails = $emails->where('client_id', $client->id);
                        @endphp
                        @if($clientDeals->count())
                            <span class="badge badge-blue" style="font-size:0.6rem;">Deal</span>
                        @endif
                        @if($clientOps->count())
                            <span class="badge badge-purple" style="font-size:0.6rem;">Op</span>
                        @endif
                        @if($clientEmails->count())
                            <span class="badge" style="background:#fce7f3; color:#db2777; font-size:0.6rem;">Email</span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="crm-empty" style="padding:1rem 0;">Sin clientes vinculados</div>
                @endforelse
            </div>
        </div>

        {{-- Broker --}}
        @if($property->broker)
        <div class="side-card">
            <div class="side-card-header">Broker Asignado</div>
            <div class="side-card-body">
                <div style="display:flex; align-items:center; gap:0.75rem;">
                    <div style="width:40px; height:40px; border-radius:50%; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:600; font-size:0.9rem;">
                        {{ strtoupper(substr($property->broker->name, 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-weight:500; font-size:0.88rem;">{{ $property->broker->name }}</div>
                        @if($property->broker->phone)<div style="font-size:0.78rem; color:var(--text-muted);">{{ $property->broker->phone }}</div>@endif
                    </div>
                </div>
                @if($property->broker->phone)
                <div style="display:flex; gap:0.4rem; margin-top:0.75rem;">
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $property->broker->phone) }}" target="_blank" class="btn btn-sm btn-outline" style="flex:1; justify-content:center; color:#25d366; border-color:#25d366;">WhatsApp</a>
                    <a href="tel:{{ $property->broker->phone }}" class="btn btn-sm btn-outline" style="flex:1; justify-content:center;">Llamar</a>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Ubicacion --}}
        <div class="side-card">
            <div class="side-card-header">Ubicacion</div>
            <div class="side-card-body">
                <div class="detail-grid">
                    @if($property->address)<div class="detail-item"><div class="detail-label">Direccion</div><div class="detail-value">{{ $property->address }}</div></div>@endif
                    @if($property->colony)<div class="detail-item"><div class="detail-label">Colonia</div><div class="detail-value">{{ $property->colony }}</div></div>@endif
                    @if($property->city)<div class="detail-item"><div class="detail-label">Ciudad</div><div class="detail-value">{{ $property->city }}</div></div>@endif
                    @if($property->zipcode)<div class="detail-item"><div class="detail-label">C.P.</div><div class="detail-value">{{ $property->zipcode }}</div></div>@endif
                </div>
            </div>
        </div>

        {{-- Resumen rapido --}}
        <div class="side-card">
            <div class="side-card-header">Resumen</div>
            <div class="side-card-body">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.5rem;">
                    <div style="text-align:center; padding:0.5rem; background:var(--bg); border-radius:var(--radius);">
                        <div style="font-size:1.1rem; font-weight:700; color:var(--primary);">{{ $deals->count() }}</div>
                        <div style="font-size:0.68rem; color:var(--text-muted);">Deals</div>
                    </div>
                    <div style="text-align:center; padding:0.5rem; background:var(--bg); border-radius:var(--radius);">
                        <div style="font-size:1.1rem; font-weight:700; color:var(--primary);">{{ $operations->count() }}</div>
                        <div style="font-size:0.68rem; color:var(--text-muted);">Operaciones</div>
                    </div>
                    <div style="text-align:center; padding:0.5rem; background:var(--bg); border-radius:var(--radius);">
                        <div style="font-size:1.1rem; font-weight:700; color:var(--primary);">{{ $emails->count() }}</div>
                        <div style="font-size:0.68rem; color:var(--text-muted);">Correos</div>
                    </div>
                    <div style="text-align:center; padding:0.5rem; background:var(--bg); border-radius:var(--radius);">
                        <div style="font-size:1.1rem; font-weight:700; color:var(--primary);">{{ $interactions->count() }}</div>
                        <div style="font-size:0.68rem; color:var(--text-muted);">Actividades</div>
                    </div>
                </div>
                @php
                    $activeDeals = $deals->whereNotIn('stage', ['won', 'lost'])->count();
                    $wonDeals = $deals->where('stage', 'won')->count();
                @endphp
                @if($activeDeals > 0 || $wonDeals > 0)
                <div style="margin-top:0.75rem; padding-top:0.75rem; border-top:1px solid var(--border); font-size:0.78rem;">
                    @if($activeDeals > 0)<div style="margin-bottom:0.25rem;">&#9989; {{ $activeDeals }} deal{{ $activeDeals > 1 ? 's' : '' }} activo{{ $activeDeals > 1 ? 's' : '' }}</div>@endif
                    @if($wonDeals > 0)<div style="color:var(--success);">&#127942; {{ $wonDeals }} deal{{ $wonDeals > 1 ? 's' : '' }} ganado{{ $wonDeals > 1 ? 's' : '' }}</div>@endif
                </div>
                @endif
            </div>
        </div>

        @if($property->isPublishedToEasyBroker())
        <div class="side-card">
            <div class="side-card-body">
                <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.5rem;">
                    <span style="width:8px; height:8px; border-radius:50%; background:var(--success); display:inline-block;"></span>
                    <span style="font-size:0.85rem; font-weight:500; color:var(--success);">EasyBroker</span>
                </div>
                @if($property->easybroker_public_url)
                    <a href="{{ $property->easybroker_public_url }}" target="_blank" style="font-size:0.82rem; color:var(--primary);">Ver publicacion &rarr;</a>
                @endif
            </div>
        </div>
        @endif

        {{-- Danger zone --}}
        <div class="side-card" style="border-color: rgba(239,68,68,0.2);">
            <div class="side-card-body" style="text-align:center;">
                <form method="POST" action="{{ route('properties.destroy', $property) }}" onsubmit="return confirm('Eliminar esta propiedad permanentemente?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" style="width:100%;">Eliminar propiedad</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Send Ficha Modal --}}
<div id="fichaModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; padding:24px; width:400px; max-width:90vw; box-shadow:0 20px 60px rgba(0,0,0,0.3);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h3 style="margin:0; font-size:1rem;">Enviar ficha por email</h3>
            <button type="button" onclick="document.getElementById('fichaModal').style.display='none'" style="background:none; border:none; font-size:1.2rem; cursor:pointer; color:#9ca3af;">&times;</button>
        </div>
        <form id="fichaForm" onsubmit="sendFicha(event)">
            <div style="margin-bottom:12px;">
                <label style="display:block; font-size:0.82rem; font-weight:600; margin-bottom:4px;">Nombre (opcional)</label>
                <input type="text" name="name" class="form-input" placeholder="Nombre del destinatario">
            </div>
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:0.82rem; font-weight:600; margin-bottom:4px;">Email *</label>
                <input type="email" name="email" class="form-input" placeholder="correo@ejemplo.com" required>
            </div>
            <div style="display:flex; gap:8px; justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('fichaModal').style.display='none'" class="btn btn-outline btn-sm">Cancelar</button>
                <button type="submit" class="btn btn-primary btn-sm" id="fichaSubmitBtn">Enviar</button>
            </div>
        </form>
        <p id="fichaMsg" style="margin:12px 0 0; font-size:0.82rem; display:none;"></p>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Gallery photos data
@php
    $lbPhotosData = $photos->map(function($p) { return ['src' => asset('storage/' . $p->path), 'desc' => $p->description ?? '']; })->values();
@endphp
var lbPhotos = @json($lbPhotosData);
var lbIndex = 0;

function openLightbox(i) {
    lbIndex = i;
    updateLightbox();
    document.getElementById('lightbox').classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    document.getElementById('lightbox').classList.remove('show');
    document.body.style.overflow = '';
}

function lbNav(dir) {
    lbIndex = (lbIndex + dir + lbPhotos.length) % lbPhotos.length;
    updateLightbox();
}

function updateLightbox() {
    if (!lbPhotos.length) return;
    document.getElementById('lbImg').src = lbPhotos[lbIndex].src;
    document.getElementById('lbCounter').textContent = (lbIndex + 1) + ' / ' + lbPhotos.length;
    document.getElementById('lbDesc').textContent = lbPhotos[lbIndex].desc || '';
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    if (!document.getElementById('lightbox').classList.contains('show')) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') lbNav(-1);
    if (e.key === 'ArrowRight') lbNav(1);
});

// Share functions
var propUrl = '{{ route("propiedades.show", [$property->id, $property->slug]) }}';
var propTitle = @json($property->title);
var propPrice = '${{ number_format($property->price, 0) }} {{ $property->currency ?? "MXN" }}';

function shareWhatsApp() {
    var msg = propTitle + '\n' + propPrice + '\n' + propUrl;
    window.open('https://wa.me/?text=' + encodeURIComponent(msg), '_blank');
}

function shareEmail() {
    var subject = encodeURIComponent(propTitle + ' - ' + propPrice);
    var body = encodeURIComponent(propTitle + '\nPrecio: ' + propPrice + '\n\nVer propiedad: ' + propUrl);
    window.location.href = 'mailto:?subject=' + subject + '&body=' + body;
}

function copyPropertyLink(btn) {
    navigator.clipboard.writeText(propUrl).then(function() {
        var orig = btn.innerHTML;
        btn.innerHTML = '&#10003;';
        setTimeout(function() { btn.innerHTML = orig; }, 2000);
    });
}

function filterTimeline(type, btn) {
    document.querySelectorAll('.crm-tab').forEach(function(t) { t.classList.remove('active'); });
    btn.classList.add('active');
    document.querySelectorAll('#timelineBody .timeline-item').forEach(function(item) {
        item.style.display = (type === 'all' || item.dataset.type === type) ? '' : 'none';
    });
}

function sendFicha(e) {
    e.preventDefault();
    var form = document.getElementById('fichaForm');
    var btn = document.getElementById('fichaSubmitBtn');
    var msg = document.getElementById('fichaMsg');
    btn.disabled = true;
    btn.textContent = 'Enviando...';
    msg.style.display = 'none';

    fetch('{{ route("properties.send-ficha", $property) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            email: form.email.value,
            name: form.name.value
        })
    }).then(function(r) { return r.json(); })
      .then(function(data) {
          msg.style.display = 'block';
          msg.style.color = data.success ? '#16a34a' : '#dc2626';
          msg.textContent = data.message;
          if (data.success) {
              setTimeout(function() { document.getElementById('fichaModal').style.display = 'none'; msg.style.display = 'none'; form.reset(); }, 2000);
          }
      }).catch(function() {
          msg.style.display = 'block';
          msg.style.color = '#dc2626';
          msg.textContent = 'Error de conexion.';
      }).finally(function() {
          btn.disabled = false;
          btn.textContent = 'Enviar';
      });
}
</script>
@endsection
