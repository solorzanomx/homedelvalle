@extends('layouts.app-sidebar')
@section('title', $client->name)

@section('styles')
<style>
/* Layout */
.cli-layout { display: grid; grid-template-columns: 1fr 320px; gap: 1.25rem; align-items: start; }
@media (max-width: 1024px) { .cli-layout { grid-template-columns: 1fr; } }

/* Hero card */
.cli-hero {
    background: var(--card); border: 1px solid var(--border); border-radius: 10px;
    overflow: hidden; margin-bottom: 1.25rem;
}
.cli-hero-temp { height: 4px; }
.temp-hot { background: #ef4444; } .temp-warm { background: #f59e0b; } .temp-cold { background: #3b82f6; }

.cli-hero-body { padding: 1.25rem; display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; }
.cli-hero-avatar {
    width: 64px; height: 64px; border-radius: 50%; overflow: hidden; flex-shrink: 0;
    background: var(--primary); display: flex; align-items: center; justify-content: center;
    color: #fff; font-weight: 700; font-size: 1.5rem;
}
.cli-hero-avatar img { width: 100%; height: 100%; object-fit: cover; }
.cli-hero-info { flex: 1; min-width: 200px; }
.cli-hero-name { font-size: 1.2rem; font-weight: 700; margin-bottom: 0.15rem; }
.cli-hero-sub { font-size: 0.85rem; color: var(--text-muted); }
.cli-hero-badges { display: flex; gap: 0.4rem; flex-wrap: wrap; margin-top: 0.4rem; }

.cli-hero-actions {
    display: flex; gap: 0.4rem; padding: 0.75rem 1.25rem; border-top: 1px solid var(--border); flex-wrap: wrap;
}
.cli-hero-actions .btn { min-width: 100px; justify-content: center; }
.btn-wa { background: #25d366; color: #fff; border-color: #25d366; }
.btn-wa:hover { opacity: 0.9; }
.btn-call { background: #3b82f6; color: #fff; border-color: #3b82f6; }
.btn-call:hover { opacity: 0.9; }

/* Tabs */
.cli-tabs { display: flex; gap: 0; border-bottom: 2px solid var(--border); margin-bottom: 1.25rem; overflow-x: auto; }
.cli-tab {
    padding: 0.6rem 1rem; font-size: 0.82rem; font-weight: 500; color: var(--text-muted);
    border-bottom: 2px solid transparent; margin-bottom: -2px; cursor: pointer;
    white-space: nowrap; transition: all 0.15s; background: none; border-top: none; border-left: none; border-right: none;
}
.cli-tab:hover { color: var(--text); }
.cli-tab.active { color: var(--primary); border-bottom-color: var(--primary); }
.cli-tab-count { font-size: 0.68rem; background: var(--bg); padding: 1px 6px; border-radius: 10px; margin-left: 4px; }
.tab-panel { display: none; }
.tab-panel.active { display: block; }

/* Timeline */
.timeline { position: relative; }
.timeline-item { display: flex; gap: 0.75rem; padding: 0.75rem 0; position: relative; }
.timeline-dot {
    width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; margin-top: 4px;
    position: relative; z-index: 1;
}
.timeline-item:not(:last-child) .timeline-dot::after {
    content: ''; position: absolute; top: 12px; left: 4px; width: 2px; height: calc(100% + 14px);
    background: var(--border);
}
.timeline-content { flex: 1; min-width: 0; }
.timeline-type { font-size: 0.68rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.15rem; }
.timeline-body { font-size: 0.85rem; line-height: 1.5; word-wrap: break-word; }
.timeline-meta { font-size: 0.72rem; color: var(--text-muted); margin-top: 0.2rem; }

/* Quick note */
.quick-note {
    background: var(--card); border: 1px solid var(--border); border-radius: var(--radius);
    padding: 1rem; margin-bottom: 1.25rem;
}
.quick-note-row { display: flex; gap: 0.5rem; align-items: flex-start; }
.quick-note textarea { flex: 1; min-height: 50px; resize: vertical; }
.quick-note-type { display: flex; gap: 0.25rem; margin-bottom: 0.5rem; }
.note-type-btn {
    padding: 0.25rem 0.6rem; border-radius: 12px; font-size: 0.72rem; font-weight: 500;
    border: 1px solid var(--border); background: var(--card); color: var(--text-muted);
    cursor: pointer; transition: all 0.15s;
}
.note-type-btn.active { background: var(--primary); color: #fff; border-color: var(--primary); }

/* Side card */
.side-card { background: var(--card); border: 1px solid var(--border); border-radius: 10px; margin-bottom: 1rem; overflow: hidden; }
.side-card-header { padding: 0.8rem 1rem; border-bottom: 1px solid var(--border); font-weight: 600; font-size: 0.85rem; }
.side-card-body { padding: 1rem; }
.side-card-row { display: flex; justify-content: space-between; padding: 0.35rem 0; font-size: 0.82rem; }
.side-card-row .label { color: var(--text-muted); }
.side-card-row .value { font-weight: 500; text-align: right; }

/* Stats mini */
.stats-mini { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; }
.stat-mini { text-align: center; padding: 0.5rem; background: var(--bg); border-radius: var(--radius); }
.stat-mini-val { font-size: 1.1rem; font-weight: 700; }
.stat-mini-lbl { font-size: 0.68rem; color: var(--text-muted); }

/* Email list */
.email-item {
    display: flex; gap: 0.75rem; padding: 0.75rem 0; border-bottom: 1px solid var(--border); align-items: flex-start;
}
.email-item:last-child { border-bottom: none; }
.email-status { font-size: 0.7rem; font-weight: 500; padding: 1px 6px; border-radius: 8px; white-space: nowrap; }
.status-sent { background: #eef2ff; color: #3730a3; }
.status-opened { background: #ecfdf5; color: #065f46; }
.status-failed { background: #fef2f2; color: #991b1b; }

/* Portal section */
.portal-section { background: var(--bg); border-radius: var(--radius); padding: 0.75rem; margin-top: 0.75rem; }

/* Mention autocomplete */
.mention-dropdown {
    position: absolute; background: var(--card); border: 1px solid var(--border); border-radius: var(--radius);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1); z-index: 100; max-height: 160px; overflow-y: auto; display: none;
}
.mention-option {
    padding: 0.4rem 0.75rem; font-size: 0.82rem; cursor: pointer; transition: background 0.1s;
}
.mention-option:hover { background: var(--bg); }

/* Props tag */
.props-sent { display: flex; gap: 0.25rem; flex-wrap: wrap; margin-top: 0.25rem; }
.prop-tag { font-size: 0.7rem; background: #eef2ff; color: #3730a3; padding: 1px 6px; border-radius: 4px; }

/* Prop cards in client */
.prop-card-mini {
    display: flex; gap: 0.75rem; padding: 0.75rem; background: var(--bg); border-radius: var(--radius);
    margin-bottom: 0.5rem; align-items: center; transition: background 0.15s;
}
.prop-card-mini:hover { background: rgba(102,126,234,0.06); }
.prop-thumb {
    width: 56px; height: 56px; border-radius: 6px; overflow: hidden; flex-shrink: 0;
    background: var(--border); display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; color: var(--text-muted);
}
.prop-thumb img { width: 100%; height: 100%; object-fit: cover; }
.prop-mini-info { flex: 1; min-width: 0; }
.prop-mini-title { font-size: 0.85rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.prop-mini-sub { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.1rem; }
.prop-mini-price { font-size: 0.82rem; font-weight: 700; color: var(--primary); white-space: nowrap; }
.prop-section-label { font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); margin: 1rem 0 0.5rem; padding-bottom: 0.3rem; border-bottom: 1px solid var(--border); }
.prop-section-label:first-child { margin-top: 0; }

@media (max-width: 768px) {
    .cli-hero-body { flex-direction: column; text-align: center; }
    .cli-hero-badges { justify-content: center; }
    .cli-hero-actions { justify-content: center; }
}
</style>
@endsection

@section('content')
@php
    $tempColors = ['caliente' => 'hot', 'tibio' => 'warm', 'frio' => 'cold'];
    $tempLabels = ['caliente' => 'Caliente', 'tibio' => 'Tibio', 'frio' => 'Frio'];
    $prioLabels = ['alta' => 'Alta', 'media' => 'Media', 'baja' => 'Baja'];
    $prioBadges = ['alta' => 'badge-red', 'media' => 'badge-yellow', 'baja' => 'badge-blue'];
    $interestLabels = ['compra'=>'Compra','venta'=>'Venta','renta_propietario'=>'Renta (propietario)','renta_inquilino'=>'Renta (inquilino)'];
@endphp

<div style="margin-bottom:1rem;">
    <a href="{{ route('clients.index') }}" style="font-size:0.82rem; color:var(--text-muted);">&#8592; Clientes</a>
</div>

{{-- Hero --}}
<div class="cli-hero">
    <div class="cli-hero-temp temp-{{ $tempColors[$client->lead_temperature] ?? 'cold' }}"></div>
    <div class="cli-hero-body">
        <div class="cli-hero-avatar" style="background: {{ $client->lead_temperature === 'caliente' ? '#ef4444' : ($client->lead_temperature === 'tibio' ? '#f59e0b' : 'var(--primary)') }};">
            @if($client->photo)
                <img src="{{ asset('storage/' . $client->photo) }}" alt="">
            @else
                {{ strtoupper(substr($client->name, 0, 1)) }}
            @endif
        </div>
        <div class="cli-hero-info">
            <div class="cli-hero-name">{{ $client->name }}</div>
            <div class="cli-hero-sub">
                {{ $client->email ?? '—' }}@if($client->phone) &middot; {{ $client->phone }}@endif
            </div>
            <div class="cli-hero-badges">
                @if($client->lead_temperature)
                    <span class="badge {{ $client->lead_temperature === 'caliente' ? 'badge-red' : ($client->lead_temperature === 'tibio' ? 'badge-yellow' : 'badge-blue') }}">{{ $tempLabels[$client->lead_temperature] ?? '' }}</span>
                @endif
                @if($client->priority)
                    <span class="badge {{ $prioBadges[$client->priority] ?? 'badge-blue' }}">P: {{ $prioLabels[$client->priority] ?? '' }}</span>
                @endif
                @if(is_array($client->interest_types) && count($client->interest_types))
                    @foreach($client->interest_types as $it)
                        <span class="badge" style="background:#f3f0ff; color:#6d28d9;">{{ $interestLabels[$it] ?? $it }}</span>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
    <div class="cli-hero-actions">
        @if($client->whatsapp ?? $client->phone)
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $client->whatsapp ?? $client->phone) }}" target="_blank" class="btn btn-wa">&#128172; WhatsApp</a>
        @endif
        @if($client->phone)
            <a href="tel:{{ $client->phone }}" class="btn btn-call">&#128222; Llamar</a>
        @endif
        @if($client->email && Route::has('clients.email.compose'))
            <a href="{{ route('clients.email.compose', $client) }}" class="btn btn-outline">&#9993; Email</a>
        @endif
        <a href="{{ route('clients.edit', $client) }}" class="btn btn-outline">&#9998; Editar</a>
    </div>
</div>

<div class="cli-layout">
    {{-- LEFT: Tabs content --}}
    <div>
        {{-- Quick Note --}}
        <form action="{{ route('clients.interaction.store', $client) }}" method="POST" class="quick-note">
            @csrf
            <div class="quick-note-type">
                @foreach(['note'=>'Nota','call'=>'Llamada','visit'=>'Visita','meeting'=>'Reunion','whatsapp'=>'WhatsApp'] as $type => $label)
                <label class="note-type-btn {{ $type === 'note' ? 'active' : '' }}">
                    <input type="radio" name="type" value="{{ $type }}" {{ $type === 'note' ? 'checked' : '' }} style="display:none;" onchange="this.closest('.quick-note-type').querySelectorAll('.note-type-btn').forEach(b => b.classList.remove('active')); this.parentElement.classList.add('active');">
                    {{ $label }}
                </label>
                @endforeach
            </div>
            <div class="quick-note-row" style="position:relative;">
                <textarea name="description" class="form-textarea" rows="2" placeholder="Agregar nota... usa @ para mencionar" required id="noteInput"></textarea>
                <button type="submit" class="btn btn-primary" style="align-self:flex-end;">Agregar</button>
                <div class="mention-dropdown" id="mentionDropdown"></div>
            </div>
        </form>

        {{-- Tabs --}}
        <div class="cli-tabs">
            <button class="cli-tab active" onclick="switchTab('timeline')">Actividad <span class="cli-tab-count">{{ $timeline->count() }}</span></button>
            <button class="cli-tab" onclick="switchTab('properties')">Propiedades <span class="cli-tab-count">{{ $ownedProperties->count() + $dealProperties->count() }}</span></button>
            <button class="cli-tab" onclick="switchTab('emails')">Emails <span class="cli-tab-count">{{ $emails->count() }}</span></button>
            @php $totalDocCount = $clientDocs->count() + ($captacion ? $captacion->documents->count() : 0); @endphp
            <button class="cli-tab" onclick="switchTab('documents')">Documentos <span class="cli-tab-count">{{ $totalDocCount }}</span></button>
        </div>

        {{-- Timeline Tab --}}
        <div class="tab-panel active" id="tab-timeline">
            @if($timeline->count())
            <div class="timeline">
                @foreach($timeline as $item)
                <div class="timeline-item">
                    <div class="timeline-dot" style="background: {{ $item['color'] }};"></div>
                    <div class="timeline-content">
                        <div class="timeline-type" style="color: {{ $item['color'] }};">{{ $item['type_label'] }} &middot; {{ $item['date']->diffForHumans() }}</div>
                        <div class="timeline-body">{!! $item['body'] !!}</div>
                        @if(!empty($item['meta']))<div class="timeline-meta">{!! $item['meta'] !!}</div>@endif
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div style="text-align:center; padding:3rem; color:var(--text-muted);">
                <div style="font-size:2rem; margin-bottom:0.5rem; opacity:0.4;">&#128221;</div>
                Sin actividad aun. Agrega una nota arriba.
            </div>
            @endif
        </div>

        {{-- Properties Tab --}}
        <div class="tab-panel" id="tab-properties">
            @if($ownedProperties->count() || $dealProperties->count())
                @if($ownedProperties->count())
                <div class="prop-section-label">&#127968; Propietario ({{ $ownedProperties->count() }})</div>
                @foreach($ownedProperties as $prop)
                <a href="{{ route('properties.show', $prop) }}" class="prop-card-mini" style="text-decoration:none; color:inherit;">
                    <div class="prop-thumb">
                        @if($prop->photo)
                            <img src="{{ asset('storage/' . $prop->photo) }}" alt="">
                        @else
                            &#127968;
                        @endif
                    </div>
                    <div class="prop-mini-info">
                        <div class="prop-mini-title">{{ $prop->title }}</div>
                        <div class="prop-mini-sub">
                            {{ $prop->property_type_label }} &middot; {{ $prop->operation_label }}
                            @if($prop->colony || $prop->city) &middot; {{ implode(', ', array_filter([$prop->colony, $prop->city])) }}@endif
                        </div>
                    </div>
                    <div class="prop-mini-price">{{ $prop->formatted_price }}</div>
                </a>
                @endforeach
                @endif

                @if($dealProperties->count())
                <div class="prop-section-label">&#128200; Interesado via Deals ({{ $dealProperties->count() }})</div>
                @foreach($dealProperties as $prop)
                @php $deal = $prop->deals->where('client_id', $client->id)->first(); @endphp
                <a href="{{ route('properties.show', $prop) }}" class="prop-card-mini" style="text-decoration:none; color:inherit;">
                    <div class="prop-thumb">
                        @if($prop->photo)
                            <img src="{{ asset('storage/' . $prop->photo) }}" alt="">
                        @else
                            &#127968;
                        @endif
                    </div>
                    <div class="prop-mini-info">
                        <div class="prop-mini-title">{{ $prop->title }}</div>
                        <div class="prop-mini-sub">
                            {{ $prop->property_type_label }} &middot; {{ $prop->operation_label }}
                            @if($deal) &middot; <span class="badge" style="font-size:0.65rem;">{{ ucfirst($deal->stage) }}</span>@endif
                        </div>
                    </div>
                    <div class="prop-mini-price">{{ $prop->formatted_price }}</div>
                </a>
                @endforeach
                @endif

                @if($emailProperties->count())
                <div class="prop-section-label">&#9993; Enviadas por correo ({{ $emailProperties->count() }})</div>
                @foreach($emailProperties as $prop)
                <a href="{{ route('properties.show', $prop) }}" class="prop-card-mini" style="text-decoration:none; color:inherit;">
                    <div class="prop-thumb">
                        @if($prop->photo)
                            <img src="{{ asset('storage/' . $prop->photo) }}" alt="">
                        @else
                            &#127968;
                        @endif
                    </div>
                    <div class="prop-mini-info">
                        <div class="prop-mini-title">{{ $prop->title }}</div>
                        <div class="prop-mini-sub">{{ $prop->property_type_label }} &middot; {{ $prop->operation_label }}</div>
                    </div>
                    <div class="prop-mini-price">{{ $prop->formatted_price }}</div>
                </a>
                @endforeach
                @endif
            @else
            <div style="text-align:center; padding:3rem; color:var(--text-muted);">
                <div style="font-size:2rem; margin-bottom:0.5rem; opacity:0.4;">&#127968;</div>
                Sin propiedades vinculadas a este cliente.
            </div>
            @endif
        </div>

        {{-- Emails Tab --}}
        <div class="tab-panel" id="tab-emails">

            @if($emails->count())
                @foreach($emails as $email)
                <div class="email-item">
                    <div style="flex:1; min-width:0;">
                        <div style="font-weight:500; font-size:0.88rem;">{{ $email->subject }}</div>
                        <div style="font-size:0.78rem; color:var(--text-muted); margin-top:0.15rem;">
                            Por {{ $email->user->name ?? 'Sistema' }} &middot; {{ $email->created_at->diffForHumans() }}
                        </div>
                        @if($email->property_ids && count($email->property_ids) > 0)
                            <div class="props-sent" style="margin-top:0.3rem;">
                                @foreach($email->properties() as $p)
                                    <span class="prop-tag">{{ $p->title }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div>
                        @if($email->status === 'failed')
                            <span class="email-status status-failed">Fallido</span>
                        @elseif($email->is_opened)
                            <span class="email-status status-opened">Abierto {{ $email->open_count }}x</span>
                        @else
                            <span class="email-status status-sent">Enviado</span>
                        @endif
                    </div>
                </div>
                @endforeach
            @else
            <div style="text-align:center; padding:3rem; color:var(--text-muted);">
                Sin correos enviados.
                @if(Route::has('clients.email.compose'))
                    <br><a href="{{ route('clients.email.compose', $client) }}" style="color:var(--primary);">Enviar primer correo</a>
                @endif
            </div>
            @endif
        </div>

        {{-- Documents Tab --}}
        <div class="tab-panel" id="tab-documents">
        @php
            $captacionDocs = $captacion ? $captacion->documents->sortBy('category') : collect();
        @endphp

        @if($captacionDocs->isEmpty() && $clientDocs->isEmpty())
        <div style="text-align:center;padding:3rem;color:var(--text-muted);">
            <div style="font-size:2rem;opacity:.4;margin-bottom:.5rem;">&#128196;</div>
            Sin documentos cargados aún.
        </div>
        @else

        {{-- Captacion docs --}}
        @if($captacionDocs->isNotEmpty())
        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--text-muted);margin-bottom:.5rem;">Evaluación de Propiedad</div>
        <div style="display:flex;flex-direction:column;gap:.4rem;margin-bottom:1rem;">
            @foreach($captacionDocs as $doc)
            @php
                $sc = match($doc->captacion_status ?? 'pendiente') { 'aprobado' => '#10b981', 'rechazado' => '#ef4444', default => '#f59e0b' };
                $sl = match($doc->captacion_status ?? 'pendiente') { 'aprobado' => 'Aprobado', 'rechazado' => 'Rechazado', default => 'Pendiente' };
            @endphp
            <div style="display:flex;align-items:center;gap:.6rem;padding:.55rem .85rem;background:var(--card);border:1px solid var(--border);border-radius:var(--radius);">
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:600;font-size:.85rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $doc->label ?? $doc->file_name }}</div>
                    <div style="font-size:.72rem;color:var(--text-muted);">{{ $allDocCategories[$doc->category] ?? $doc->category }} &middot; {{ $doc->created_at->format('d/m/Y') }}@if($doc->uploader) &middot; {{ $doc->uploader->name }}@endif</div>
                    @if($doc->captacion_status === 'rechazado' && $doc->rejection_reason)
                    <div style="font-size:.72rem;color:#ef4444;">&#9888; {{ $doc->rejection_reason }}</div>
                    @endif
                </div>
                <span class="badge" style="background:{{ $sc }}20;color:{{ $sc }};flex-shrink:0;">{{ $sl }}</span>
                <a href="{{ route('documents.download', $doc->id) }}" class="btn btn-sm btn-outline" style="flex-shrink:0;">&#8615;</a>
            </div>
            @endforeach
        </div>
        @endif

        {{-- General docs --}}
        @if($clientDocs->isNotEmpty())
        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--text-muted);margin-bottom:.5rem;">Otros documentos</div>
        <div style="display:flex;flex-direction:column;gap:.4rem;">
            @foreach($clientDocs as $doc)
            @php
                $sc = match($doc->status ?? 'pending') { 'verified' => '#10b981', 'rejected' => '#ef4444', 'received' => '#3b82f6', default => '#f59e0b' };
                $sl = match($doc->status ?? 'pending') { 'verified' => 'Verificado', 'rejected' => 'Rechazado', 'received' => 'Recibido', default => 'Pendiente' };
            @endphp
            <div style="display:flex;align-items:center;gap:.6rem;padding:.55rem .85rem;background:var(--card);border:1px solid var(--border);border-radius:var(--radius);">
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:600;font-size:.85rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $doc->label ?? $doc->file_name }}</div>
                    <div style="font-size:.72rem;color:var(--text-muted);">{{ $allDocCategories[$doc->category] ?? $doc->category }} &middot; {{ $doc->created_at->format('d/m/Y') }}@if($doc->uploader) &middot; {{ $doc->uploader->name }}@endif</div>
                </div>
                <span class="badge" style="background:{{ $sc }}20;color:{{ $sc }};flex-shrink:0;">{{ $sl }}</span>
                <a href="{{ route('documents.download', $doc->id) }}" class="btn btn-sm btn-outline" style="flex-shrink:0;">&#8615;</a>
            </div>
            @endforeach
        </div>
        @endif

        @endif
    </div>
    </div>

    {{-- RIGHT: Sidebar --}}
    <div>
        {{-- Stats --}}
        <div class="side-card">
            <div class="side-card-body">
                <div class="stats-mini">
                    <div class="stat-mini"><div class="stat-mini-val">{{ $interactions->count() }}</div><div class="stat-mini-lbl">Interacciones</div></div>
                    <div class="stat-mini"><div class="stat-mini-val">{{ $emailsSent }}</div><div class="stat-mini-lbl">Emails</div></div>
                    <div class="stat-mini"><div class="stat-mini-val">{{ $emailsOpened }}</div><div class="stat-mini-lbl">Abiertos</div></div>
                </div>
            </div>
        </div>

        {{-- Details --}}
        <div class="side-card">
            <div class="side-card-header">Informacion</div>
            <div class="side-card-body" style="padding:0.5rem 1rem;">
                <div class="side-card-row"><span class="label">Telefono</span><span class="value">{{ $client->phone ?? '—' }}</span></div>
                <div class="side-card-row"><span class="label">WhatsApp</span><span class="value">{{ $client->whatsapp ?? $client->phone ?? '—' }}</span></div>
                <div class="side-card-row"><span class="label">Ciudad</span><span class="value">{{ $client->city ?? '—' }}</span></div>
                <div class="side-card-row"><span class="label">Interes</span><span class="value">{{ $client->property_type ?? '—' }}</span></div>
                @if($client->budget_min || $client->budget_max)
                <div class="side-card-row"><span class="label">Budget</span><span class="value">${{ number_format($client->budget_min ?? 0, 0) }} - ${{ number_format($client->budget_max ?? 0, 0) }}</span></div>
                @endif
                <div class="side-card-row"><span class="label">Broker</span><span class="value">{{ $client->broker->name ?? '—' }}</span></div>
                @if($client->marketingChannel)
                <div class="side-card-row"><span class="label">Canal</span><span class="value">{{ $client->marketingChannel->name }}</span></div>
                @endif
                @if($client->marketingCampaign)
                <div class="side-card-row"><span class="label">Campana</span><span class="value">{{ $client->marketingCampaign->name }}</span></div>
                @endif
                <div class="side-card-row"><span class="label">Registro</span><span class="value">{{ $client->created_at->format('d/m/Y') }}</span></div>
            </div>
        </div>

        {{-- Portal Access (admin) --}}
        @if(auth()->user() && auth()->user()->role === 'admin')
        <div class="side-card">
            <div class="side-card-header">Portal del Cliente</div>
            <div class="side-card-body">
                @if($client->user_id)
                    @php $portalUser = \App\Models\User::find($client->user_id); @endphp
                    <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.5rem;">
                        <span style="width:8px; height:8px; border-radius:50%; background:{{ $portalUser && $portalUser->is_active ? 'var(--success)' : 'var(--danger)' }};"></span>
                        <span style="font-size:0.82rem; font-weight:500;">{{ $portalUser && $portalUser->is_active ? 'Acceso activo' : 'Acceso desactivado' }}</span>
                    </div>
                    <div style="display:flex; gap:0.4rem; flex-wrap:wrap;">
                        <form method="POST" action="{{ route('clients.toggle-portal', $client) }}">@csrf
                            <button class="btn btn-sm btn-outline">{{ $portalUser && $portalUser->is_active ? 'Desactivar' : 'Activar' }}</button>
                        </form>
                        <form method="POST" action="{{ route('clients.delete-portal', $client) }}" onsubmit="return confirm('Eliminar acceso al portal?')">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">Eliminar</button>
                        </form>
                    </div>
                    <div class="portal-section" style="margin-top:0.75rem;">
                        <div style="font-size:0.78rem; font-weight:500; margin-bottom:0.35rem;">Resetear contrasena</div>
                        <form method="POST" action="{{ route('clients.reset-portal-password', $client) }}" style="display:flex; gap:0.35rem;">
                            @csrf
                            <input type="password" name="new_password" class="form-input" placeholder="Nueva contrasena" required style="flex:1; font-size:0.82rem; padding:0.35rem 0.6rem;">
                            <button class="btn btn-sm btn-outline">Reset</button>
                        </form>
                    </div>
                @else
                    <div style="font-size:0.82rem; color:var(--text-muted); margin-bottom:0.5rem;">Sin acceso al portal</div>

                    {{-- Contrato de confidencialidad --}}
                    @if($confidencialidadRequest && $confidencialidadRequest->status === 'draft')
                        <div style="font-size:0.78rem; color:var(--text-muted); margin-bottom:0.4rem;">
                            Contrato generado —
                            <a href="https://docs.google.com/document/d/{{ $confidencialidadRequest->file_id }}/edit" target="_blank" style="color:var(--primary);">Revisar en Drive</a>
                        </div>
                        <form method="POST" action="{{ route('admin.contrato.enviar', $confidencialidadRequest) }}" style="margin-bottom:0.5rem;">
                            @csrf
                            <button class="btn btn-sm btn-outline" style="width:100%;">Marcar como enviado al cliente</button>
                        </form>

                    @elseif($confidencialidadRequest && $confidencialidadRequest->status === 'pending')
                        <div style="display:flex; align-items:center; gap:0.4rem; margin-bottom:0.5rem;">
                            <span style="width:8px; height:8px; border-radius:50%; background:var(--warning);"></span>
                            <span style="font-size:0.78rem; color:var(--text-muted);">
                                Confidencialidad: Pendiente de firma —
                                <a href="https://docs.google.com/document/d/{{ $confidencialidadRequest->file_id }}/edit" target="_blank" style="color:var(--primary);">Ver doc</a>
                            </span>
                        </div>
                        <form method="POST" action="{{ route('admin.contrato.confirmar', $confidencialidadRequest) }}" onsubmit="return confirm('¿Confirmar que el cliente ya firmó el contrato? Esto creará su acceso al portal.')">
                            @csrf
                            <button class="btn btn-sm btn-primary" style="width:100%;">Confirmar firma recibida</button>
                        </form>

                    @elseif($confidencialidadRequest && in_array($confidencialidadRequest->status, ['completed', 'declined']))
                        @php
                            $badgeColor = $confidencialidadRequest->status === 'completed' ? 'var(--success)' : 'var(--danger)';
                            $badgeLabel = $confidencialidadRequest->status === 'completed' ? 'Firmado' : 'Rechazado';
                        @endphp
                        <div style="display:flex; align-items:center; gap:0.4rem; margin-bottom:0.5rem;">
                            <span style="width:8px; height:8px; border-radius:50%; background:{{ $badgeColor }};"></span>
                            <span style="font-size:0.78rem; color:var(--text-muted);">Confidencialidad: {{ $badgeLabel }}</span>
                        </div>

                    @else
                        <form method="POST" action="{{ route('admin.clients.contrato-generar', $client) }}" style="margin-bottom:0.5rem;">
                            @csrf
                            <button class="btn btn-sm btn-outline" style="width:100%;">Generar Contrato de Confidencialidad</button>
                        </form>
                    @endif

                    <form method="POST" action="{{ route('clients.create-portal', $client) }}" style="display:flex; gap:0.4rem;">
                        @csrf
                        <input type="password" name="password" class="form-input" placeholder="Contrasena (opc)" style="flex:1; font-size:0.82rem; padding:0.35rem 0.6rem;">
                        <button class="btn btn-sm btn-primary">Crear acceso</button>
                    </form>
                @endif
            </div>
        </div>
        @endif

        {{-- Danger zone --}}
        <div class="side-card" style="border-color:rgba(239,68,68,0.2);">
            <div class="side-card-body" style="text-align:center;">
                <form method="POST" action="{{ route('clients.destroy', $client) }}" onsubmit="return confirm('Eliminar este cliente permanentemente?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" style="width:100%;">Eliminar cliente</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function switchTab(tab) {
    document.querySelectorAll('.cli-tab').forEach(function(t) { t.classList.remove('active'); });
    document.querySelectorAll('.tab-panel').forEach(function(p) { p.classList.remove('active'); });
    document.getElementById('tab-' + tab).classList.add('active');
    event.currentTarget.classList.add('active');
}

// @mention autocomplete
var noteInput = document.getElementById('noteInput');
var mentionDropdown = document.getElementById('mentionDropdown');
var mentionSearch = '';
var mentionStart = -1;

if (noteInput) {
    noteInput.addEventListener('input', function(e) {
        var val = this.value;
        var pos = this.selectionStart;
        var before = val.substring(0, pos);
        var atIdx = before.lastIndexOf('@');

        if (atIdx >= 0 && (atIdx === 0 || before[atIdx - 1] === ' ' || before[atIdx - 1] === '\n')) {
            mentionSearch = before.substring(atIdx + 1);
            mentionStart = atIdx;
            if (mentionSearch.length >= 1) {
                fetchMentions(mentionSearch);
            } else {
                mentionDropdown.style.display = 'none';
            }
        } else {
            mentionDropdown.style.display = 'none';
        }
    });

    noteInput.addEventListener('keydown', function(e) {
        if (mentionDropdown.style.display === 'block') {
            var items = mentionDropdown.querySelectorAll('.mention-option');
            var active = mentionDropdown.querySelector('.mention-option.active');
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                if (!active && items.length) { items[0].classList.add('active'); return; }
                if (!active) return;
                active.classList.remove('active');
                var idx = Array.from(items).indexOf(active);
                var next = e.key === 'ArrowDown' ? (idx + 1) % items.length : (idx - 1 + items.length) % items.length;
                items[next].classList.add('active');
            } else if ((e.key === 'Enter' || e.key === 'Tab') && active) {
                e.preventDefault();
                insertMention(active.dataset.name);
            } else if (e.key === 'Escape') {
                mentionDropdown.style.display = 'none';
            }
        }
    });
}

function fetchMentions(query) {
    fetch('/api/users/search?q=' + encodeURIComponent(query), {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    })
    .then(function(r) { return r.json(); })
    .then(function(users) {
        if (!users.length) { mentionDropdown.style.display = 'none'; return; }
        var html = '';
        users.forEach(function(u) {
            html += '<div class="mention-option" data-name="' + u.name + (u.last_name ? ' ' + u.last_name : '') + '" onclick="insertMention(this.dataset.name)">' + u.name + (u.last_name ? ' ' + u.last_name : '') + ' <span style="color:var(--text-muted); font-size:0.72rem;">(' + u.role + ')</span></div>';
        });
        mentionDropdown.innerHTML = html;
        mentionDropdown.style.display = 'block';
    })
    .catch(function() { mentionDropdown.style.display = 'none'; });
}

function insertMention(name) {
    var val = noteInput.value;
    var before = val.substring(0, mentionStart);
    var after = val.substring(noteInput.selectionStart);
    noteInput.value = before + '@' + name + ' ' + after;
    noteInput.focus();
    var newPos = mentionStart + name.length + 2;
    noteInput.setSelectionRange(newPos, newPos);
    mentionDropdown.style.display = 'none';
}
</script>
@endsection
