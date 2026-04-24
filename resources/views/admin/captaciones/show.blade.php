@extends('layouts.app-sidebar')
@section('title', ($captacion->client->name ?? 'Captación') . ' — Evaluación de Propiedad')

@section('styles')
<style>
/* ===== HERO CARD ===== */
.hero-card { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); overflow:hidden; margin-bottom:1.25rem; }
.hero-top  { display:flex; align-items:center; gap:1rem; padding:1rem 1.25rem; position:relative; }
.hero-top-bar { position:absolute; top:0; left:0; right:0; height:3px; }
.hero-avatar { width:52px; height:52px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.1rem; font-weight:700; color:#fff; flex-shrink:0; }
.hero-info { flex:1; min-width:0; }
.hero-name { font-size:1.15rem; font-weight:700; color:var(--text); }
.hero-sub  { font-size:.82rem; color:var(--text-muted); margin-top:.1rem; display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; }
.hero-badges { display:flex; gap:.3rem; flex-shrink:0; flex-wrap:wrap; }

.hero-actions { display:flex; gap:.35rem; padding:0 1.25rem 1rem; flex-wrap:wrap; }
.action-btn {
    flex:1; min-width:80px; display:flex; align-items:center; justify-content:center; gap:.4rem;
    padding:.6rem .5rem; border-radius:var(--radius); font-size:.82rem; font-weight:600;
    border:1.5px solid var(--border); background:var(--card); color:var(--text);
    transition:all .15s; text-decoration:none; cursor:pointer;
}
.action-btn:hover { border-color:var(--primary); color:var(--primary); }
.action-btn.wa    { color:#25d366; border-color:#25d366; background:rgba(37,211,102,.04); }
.action-btn.wa:hover { background:#25d366; color:#fff; }
.action-btn.phone { color:#3b82f6; border-color:#3b82f6; background:rgba(59,130,246,.04); }
.action-btn.phone:hover { background:#3b82f6; color:#fff; }

/* ===== STEPPER ===== */
.stepper { display:flex; align-items:center; padding:0 1.25rem 1rem; overflow-x:auto; gap:0; }
.step {
    display:flex; align-items:center; gap:.35rem; padding:.3rem .5rem; border-radius:20px;
    font-size:.7rem; font-weight:600; white-space:nowrap; color:var(--text-muted);
}
.step.completed { color:var(--success); }
.step.current   { background:var(--primary); color:#fff; padding:.3rem .75rem; box-shadow:0 2px 8px rgba(102,126,234,.3); }
.step.future    { opacity:.4; }
.step-dot { width:6px; height:6px; border-radius:50%; flex-shrink:0; }
.step.completed .step-dot { background:var(--success); }
.step.current   .step-dot { background:#fff; }
.step.future    .step-dot { background:var(--border); }
.step-arrow { color:var(--border); font-size:.65rem; margin:0 .1rem; flex-shrink:0; }

/* ===== DOCS PROGRESS BAR ===== */
.advance-bar {
    display:flex; align-items:center; gap:.5rem; padding:.65rem 1.25rem;
    border-top:1px solid var(--border); background:var(--bg);
}
.advance-bar .progress-text { font-size:.75rem; color:var(--text-muted); white-space:nowrap; }
.progress-fill { flex:1; height:5px; background:var(--border); border-radius:3px; overflow:hidden; }
.progress-fill-inner { height:100%; border-radius:3px; transition:width .3s; }

/* ===== LAYOUT ===== */
.cap-layout { display:grid; grid-template-columns:1fr 300px; gap:1.25rem; align-items:start; }
@media (max-width:1024px) { .cap-layout { grid-template-columns:1fr; } }

/* ===== QUICK NOTE ===== */
.quick-note { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:.85rem 1rem; margin-bottom:1.25rem; }
.quick-note-form { display:flex; gap:.5rem; align-items:flex-end; }
.quick-note-form textarea {
    flex:1; resize:none; border:1px solid var(--border); border-radius:var(--radius);
    padding:.5rem .65rem; font-size:.85rem; font-family:inherit;
    background:var(--bg); color:var(--text); min-height:38px; max-height:100px;
}
.quick-note-form textarea:focus { outline:none; border-color:var(--primary); }

/* ===== TABS ===== */
.tab-bar { display:flex; gap:0; border-bottom:2px solid var(--border); margin-bottom:1.25rem; overflow-x:auto; }
.tab-btn {
    padding:.55rem .85rem; font-size:.82rem; font-weight:500; color:var(--text-muted);
    background:none; border:none; cursor:pointer; border-bottom:2px solid transparent;
    margin-bottom:-2px; transition:all .15s; white-space:nowrap;
    display:flex; align-items:center; gap:.3rem;
}
.tab-btn:hover { color:var(--text); }
.tab-btn.active { color:var(--primary); border-bottom-color:var(--primary); }
.tab-count { font-size:.68rem; font-weight:700; background:var(--bg); padding:.05rem .35rem; border-radius:8px; color:var(--text-muted); }
.tab-btn.active .tab-count { background:rgba(102,126,234,.1); color:var(--primary); }
.tab-content { display:none; }
.tab-content.active { display:block; }

/* ===== TIMELINE ===== */
.timeline { position:relative; padding-left:24px; }
.timeline::before { content:''; position:absolute; left:8px; top:0; bottom:0; width:2px; background:var(--border); }
.timeline-item  { position:relative; margin-bottom:1rem; }
.timeline-dot   { position:absolute; left:-20px; top:4px; width:14px; height:14px; border-radius:50%; border:2px solid var(--card); z-index:1; }
.timeline-content { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:.65rem .85rem; }
.timeline-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:.2rem; }
.timeline-type { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; }
.timeline-date { font-size:.7rem; color:var(--text-muted); }
.timeline-body { font-size:.85rem; line-height:1.5; }
.timeline-meta { font-size:.7rem; color:var(--text-muted); margin-top:.25rem; }

/* ===== DOCS ===== */
.doc-row {
    display:flex; align-items:center; gap:.65rem; padding:.65rem .85rem;
    border:1px solid var(--border); border-radius:var(--radius); margin-bottom:.4rem; background:var(--card);
}
.doc-icon { font-size:1.2rem; flex-shrink:0; }
.doc-info { flex:1; overflow:hidden; }
.doc-name { font-size:.82rem; font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.doc-meta { font-size:.7rem; color:var(--text-muted); }
.doc-actions { display:flex; gap:.3rem; flex-shrink:0; flex-wrap:wrap; }
.doc-section-label {
    font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px;
    color:var(--text-muted); margin:1rem 0 .5rem;
}

/* ===== SIDEBAR ===== */
.info-panel { position:sticky; top:1rem; }
.side-card  { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); margin-bottom:.85rem; overflow:hidden; }
.side-card-header {
    display:flex; align-items:center; justify-content:space-between;
    padding:.65rem .85rem; border-bottom:1px solid var(--border);
    background:var(--bg);
}
.side-card-title { font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:var(--text-muted); display:flex; align-items:center; gap:.4rem; }
.side-card-body { padding:.85rem; }
.info-row { display:flex; justify-content:space-between; padding:.2rem 0; font-size:.82rem; }
.info-row .lbl { color:var(--text-muted); }
.info-row .val { font-weight:500; }

/* Reject modal */
#reject-modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:9999; align-items:center; justify-content:center; }
#reject-modal.open { display:flex; }
</style>
@endsection

@section('content')
@php
    $client    = $captacion->client;
    $phone     = $client->whatsapp ?? $client->phone ?? '';
    $waLink    = $phone ? 'https://wa.me/52' . preg_replace('/\D/', '', $phone) : '';
    $telLink   = $phone ? 'tel:' . $phone : '';
    $initials  = collect(explode(' ', $client->name ?? '?'))->map(fn($w) => mb_strtoupper(mb_substr($w,0,1)))->take(2)->join('');
    $etapa     = $captacion->portal_etapa;
    $etapaColor = $etapaColors[$etapa] ?? '#94a3b8';
    $etapaList = [1,2,3,4];
    $approved  = $captacion->documents->where('captacion_status','aprobado')->count();
    $total     = $captacion->documents->count();
    $pct       = $total > 0 ? round($approved / $total * 100) : 0;
@endphp

<div class="page-header">
    <div></div>
    <div style="display:flex;gap:.5rem;">
        <a href="{{ route('clients.show', $client->id) }}" class="btn btn-outline btn-sm">&#128100; Cliente</a>
        <a href="{{ route('admin.captaciones.index') }}" class="btn btn-outline btn-sm">&#8592; Pipeline</a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-error" style="margin-bottom:1rem;">{{ session('error') }}</div>
@endif

{{-- ===== HERO CARD ===== --}}
<div class="hero-card">
    <div class="hero-top">
        <div class="hero-top-bar" style="background:{{ $etapaColor }};"></div>
        <div class="hero-avatar" style="background:{{ $etapaColor }};">{{ $initials }}</div>
        <div class="hero-info">
            <div class="hero-name">{{ $client->name ?? 'Cliente' }}</div>
            <div class="hero-sub">
                @if($client->email)
                    <span>{{ $client->email }}</span>
                @endif
                @if($phone)
                    <span>&middot; {{ $phone }}</span>
                @endif
            </div>
        </div>
        <div class="hero-badges">
            <span class="badge" style="background:{{ $etapaColor }}1a;color:{{ $etapaColor }};">{{ $etapaLabels[$etapa] }}</span>
            @if($captacion->precio_acordado)
            <span class="badge badge-green" style="font-weight:700;">${{ number_format($captacion->precio_acordado,0) }}</span>
            @endif
            <span class="badge badge-{{ $captacion->status === 'completado' ? 'green' : ($captacion->status === 'cancelado' ? 'red' : 'yellow') }}">
                {{ ucfirst($captacion->status) }}
            </span>
        </div>
    </div>

    <div class="hero-actions">
        @if($waLink)
        <a href="{{ $waLink }}" target="_blank" class="action-btn wa">&#128172; WhatsApp</a>
        @endif
        @if($telLink)
        <a href="{{ $telLink }}" class="action-btn phone">&#128222; Llamar</a>
        @endif
        <a href="{{ route('clients.show', $client->id) }}" class="action-btn">&#128100; Perfil</a>
    </div>

    {{-- Stage Stepper --}}
    <div class="stepper">
        @foreach($etapaList as $n)
        @if($n > 1)
        <span class="step-arrow">&#9656;</span>
        @endif
        <div class="step {{ $n < $etapa ? 'completed' : ($n === $etapa ? 'current' : 'future') }}">
            <span class="step-dot"></span>
            {{ $etapaLabels[$n] }}
            @if($n < $etapa)
            &#10003;
            @endif
        </div>
        @endforeach
    </div>

    {{-- Docs progress --}}
    <div class="advance-bar">
        <span class="progress-text">{{ $approved }}/{{ $total }} documentos aprobados</span>
        <div class="progress-fill">
            <div class="progress-fill-inner" style="width:{{ $pct }}%;background:{{ $etapaColor }};"></div>
        </div>
        @if($etapa < 4)
        <span style="font-size:.75rem;color:{{ $etapaColor }};font-weight:600;">Etapa {{ $etapa }}: {{ $etapaLabels[$etapa] }}</span>
        @else
        <span style="font-size:.75rem;color:var(--success);font-weight:600;">&#10003; Proceso completado</span>
        @endif
    </div>
</div>

<div class="cap-layout">

    {{-- ===== LEFT: Tabs ===== --}}
    <div>
        {{-- Quick note --}}
        <div class="quick-note">
            <form method="POST" action="{{ route('clients.interaction.store', $client->id) }}" class="quick-note-form">
                @csrf
                <input type="hidden" name="type" value="note">
                <textarea name="description" rows="1" placeholder="Nota rapida sobre este proceso..."
                    oninput="this.style.height='';this.style.height=Math.min(this.scrollHeight,100)+'px'"></textarea>
                <button type="submit" class="btn btn-primary btn-sm">Enviar</button>
            </form>
        </div>

        <div class="tab-bar">
            <button class="tab-btn active" onclick="switchTab('docs',this)">
                Documentos <span class="tab-count">{{ $total }}</span>
            </button>
            <button class="tab-btn" onclick="switchTab('timeline',this)">
                Actividad <span class="tab-count">{{ $interactions->count() }}</span>
            </button>
        </div>

        {{-- TAB: Documentos --}}
        <div class="tab-content active" id="tab-docs">
            <div class="doc-section-label">Documentos Requeridos</div>
            @foreach($requiredCats as $cat)
            @php
                $docs = $docsByCategory[$cat] ?? collect();
                $latest = $docs->sortByDesc('created_at')->first();
                $status = $latest ? $latest->captacion_status : null;
                $ext = $latest ? strtolower(pathinfo($latest->file_name, PATHINFO_EXTENSION)) : '';
                $isImg = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                $isPdf = $ext === 'pdf';
                $previewUrl = $latest ? asset('storage/' . $latest->file_path) : '';
                $docTitle = $allCategories[$cat] ?? $cat;
            @endphp
            <div class="doc-row">
                <span class="doc-icon">
                    @if($status === 'aprobado') &#9989;
                    @elseif($status === 'rechazado') &#10060;
                    @elseif($latest) &#128196;
                    @else &#9898;
                    @endif
                </span>
                <div class="doc-info">
                    <div class="doc-name">{{ $docTitle }}</div>
                    @if($latest)
                    <div class="doc-meta">
                        <span style="color:var(--text-muted);">{{ $latest->file_name }}</span>
                        @if($latest->file_size_formatted ?? '')
                        &middot; {{ $latest->file_size_formatted }}
                        @endif
                        @if($latest->rejection_reason)
                        &middot; <span style="color:#ef4444;">{{ $latest->rejection_reason }}</span>
                        @endif
                    </div>
                    @else
                    <div class="doc-meta" style="color:#ef4444;">Pendiente de carga</div>
                    @endif
                </div>
                <div class="doc-actions">
                    {{-- Preview / Ver --}}
                    @if($latest)
                        @if($isImg || $isPdf)
                        <button type="button" class="btn btn-sm btn-outline"
                            onclick="openPreview('{{ $previewUrl }}', '{{ $isImg ? 'img' : 'pdf' }}', '{{ addslashes($docTitle) }}')">Ver</button>
                        @else
                        <a href="{{ $previewUrl }}" target="_blank" class="btn btn-sm btn-outline">Ver</a>
                        @endif
                    @endif
                    {{-- Upload / Replace --}}
                    <form method="POST" action="{{ route('admin.captaciones.upload', $captacion) }}" enctype="multipart/form-data" style="display:inline;">
                        @csrf
                        <input type="hidden" name="category" value="{{ $cat }}">
                        <label class="btn btn-sm btn-outline" style="cursor:pointer;margin:0;">
                            {{ $latest ? 'Reemplazar' : 'Subir' }}
                            <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" style="display:none;" onchange="this.form.submit()">
                        </label>
                    </form>
                    @if($latest)
                    {{-- Delete --}}
                    <form method="POST" action="{{ route('admin.captaciones.document.delete', [$captacion, $latest]) }}" style="display:inline;" onsubmit="return confirm('¿Eliminar este documento?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm" style="background:#fee2e2;color:#991b1b;border-color:#fecaca;">Eliminar</button>
                    </form>
                    {{-- Approve / Reject --}}
                    @if($status !== 'aprobado')
                    <form method="POST" action="{{ route('admin.captaciones.doc-status', [$captacion, $latest]) }}">
                        @csrf
                        <input type="hidden" name="captacion_status" value="aprobado">
                        <button type="submit" class="btn btn-sm" style="background:#dcfce7;color:#166534;border-color:#bbf7d0;">✓ OK</button>
                    </form>
                    @else
                    <span class="badge badge-green">Aprobado</span>
                    @endif
                    @if($status !== 'rechazado')
                    <button type="button" class="btn btn-sm" style="background:#fee2e2;color:#991b1b;border-color:#fecaca;"
                        onclick="openReject('{{ route('admin.captaciones.doc-status', [$captacion, $latest]) }}')">
                        Rechazar
                    </button>
                    @endif
                    @endif
                </div>
            </div>
            @endforeach

            <div class="doc-section-label" style="margin-top:1.5rem;">Documentos Opcionales</div>
            @foreach($optionalCats as $cat)
            @php
                $docs = $docsByCategory[$cat] ?? collect();
                $latest = $docs->sortByDesc('created_at')->first();
                $status = $latest ? $latest->captacion_status : null;
                $ext = $latest ? strtolower(pathinfo($latest->file_name, PATHINFO_EXTENSION)) : '';
                $isImg = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                $isPdf = $ext === 'pdf';
                $previewUrl = $latest ? asset('storage/' . $latest->file_path) : '';
                $docTitle = $allCategories[$cat] ?? $cat;
            @endphp
            <div class="doc-row">
                <span class="doc-icon">
                    @if($status === 'aprobado') &#9989;
                    @elseif($status === 'rechazado') &#10060;
                    @elseif($latest) &#128196;
                    @else &#9898;
                    @endif
                </span>
                <div class="doc-info">
                    <div class="doc-name">{{ $docTitle }}</div>
                    @if($latest)
                    <div class="doc-meta">
                        <span style="color:var(--text-muted);">{{ $latest->file_name }}</span>
                    </div>
                    @else
                    <div class="doc-meta">No cargado</div>
                    @endif
                </div>
                <div class="doc-actions">
                    {{-- Preview / Ver --}}
                    @if($latest)
                        @if($isImg || $isPdf)
                        <button type="button" class="btn btn-sm btn-outline"
                            onclick="openPreview('{{ $previewUrl }}', '{{ $isImg ? 'img' : 'pdf' }}', '{{ addslashes($docTitle) }}')">Ver</button>
                        @else
                        <a href="{{ $previewUrl }}" target="_blank" class="btn btn-sm btn-outline">Ver</a>
                        @endif
                    @endif
                    {{-- Upload / Replace --}}
                    <form method="POST" action="{{ route('admin.captaciones.upload', $captacion) }}" enctype="multipart/form-data" style="display:inline;">
                        @csrf
                        <input type="hidden" name="category" value="{{ $cat }}">
                        <label class="btn btn-sm btn-outline" style="cursor:pointer;margin:0;">
                            {{ $latest ? 'Reemplazar' : 'Subir' }}
                            <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" style="display:none;" onchange="this.form.submit()">
                        </label>
                    </form>
                    @if($latest)
                    {{-- Delete --}}
                    <form method="POST" action="{{ route('admin.captaciones.document.delete', [$captacion, $latest]) }}" style="display:inline;" onsubmit="return confirm('¿Eliminar?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm" style="background:#fee2e2;color:#991b1b;border-color:#fecaca;">Eliminar</button>
                    </form>
                    {{-- Approve --}}
                    @if($status !== 'aprobado')
                    <form method="POST" action="{{ route('admin.captaciones.doc-status', [$captacion, $latest]) }}">
                        @csrf
                        <input type="hidden" name="captacion_status" value="aprobado">
                        <button type="submit" class="btn btn-sm" style="background:#dcfce7;color:#166534;border-color:#bbf7d0;">✓ OK</button>
                    </form>
                    @else
                    <span class="badge badge-green">Aprobado</span>
                    @endif
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- TAB: Actividad --}}
        <div class="tab-content" id="tab-timeline">
            @if($interactions->isEmpty())
            <div style="text-align:center;padding:2.5rem;color:var(--text-muted);">
                <p style="font-size:2rem;margin-bottom:.5rem;">&#128221;</p>
                <p>Sin actividad registrada.</p>
            </div>
            @else
            <div class="timeline">
                @foreach($interactions as $item)
                @php
                    $tColors = ['call'=>'#3b82f6','whatsapp'=>'#25d366','email'=>'#8b5cf6','visit'=>'#f59e0b','note'=>'#94a3b8'];
                    $tLabels = ['call'=>'Llamada','whatsapp'=>'WhatsApp','email'=>'Email','visit'=>'Visita','note'=>'Nota'];
                    $tc = $tColors[$item->type] ?? '#94a3b8';
                    $tl = $tLabels[$item->type] ?? ucfirst($item->type);
                @endphp
                <div class="timeline-item">
                    <div class="timeline-dot" style="background:{{ $tc }};"></div>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <span class="timeline-type" style="color:{{ $tc }};">{{ $tl }}</span>
                            <span class="timeline-date">{{ $item->created_at->format('d/m H:i') }}</span>
                        </div>
                        <div class="timeline-body">{{ $item->description }}</div>
                        @if($item->user)
                        <div class="timeline-meta">por {{ $item->user->name }}</div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- ===== RIGHT: Sidebar ===== --}}
    <div class="info-panel">

        {{-- Cliente --}}
        <div class="side-card">
            <div class="side-card-header">
                <span class="side-card-title">&#128100; Cliente</span>
                <a href="{{ route('clients.show', $client->id) }}" style="font-size:.75rem;color:var(--primary);">Ver perfil &rarr;</a>
            </div>
            <div class="side-card-body">
                <div class="info-row"><span class="lbl">Nombre</span><span class="val">{{ $client->name }}</span></div>
                @if($client->email)
                <div class="info-row"><span class="lbl">Email</span><span class="val" style="font-size:.78rem;">{{ $client->email }}</span></div>
                @endif
                @if($phone)
                <div class="info-row"><span class="lbl">Tel</span><span class="val">{{ $phone }}</span></div>
                @endif
                @if($captacion->property_address)
                <div class="info-row"><span class="lbl">Inmueble</span><span class="val" style="font-size:.78rem;">{{ $captacion->property_address }}</span></div>
                @endif
                <div class="info-row"><span class="lbl">Inicio</span><span class="val">{{ $captacion->created_at->format('d/m/Y') }}</span></div>
            </div>
        </div>

        {{-- Etapa 2: Valuación --}}
        <div class="side-card">
            <div class="side-card-header">
                <span class="side-card-title" style="color:#3b82f6;">&#9632; Etapa 2 &mdash; Valuación</span>
                @if($etapa > 2)<span style="color:var(--success);font-size:.72rem;">&#10003; Completa</span>@endif
            </div>
            <div class="side-card-body">
                @if($captacion->valuation)
                <div class="info-row"><span class="lbl">Valor estimado</span><span class="val" style="color:var(--text);font-weight:700;">${{ number_format($captacion->valuation->estimated_value ?? 0, 0) }}</span></div>
                <div class="info-row"><span class="lbl">Fecha</span><span class="val">{{ $captacion->valuation->created_at->format('d/m/Y') }}</span></div>
                @else
                @if($etapa < 2)
                <p style="font-size:.8rem;color:var(--text-muted);">Disponible cuando se completen los documentos.</p>
                @else
                <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:.75rem;">Vincula una opinión de valor existente.</p>
                @if($valuations->isNotEmpty())
                <form method="POST" action="{{ route('admin.captaciones.link-valuation', $captacion) }}">
                    @csrf
                    <select name="valuation_id" class="form-select" style="margin-bottom:.5rem;font-size:.82rem;">
                        @foreach($valuations as $val)
                        <option value="{{ $val->id }}">#{{ $val->id }} &mdash; ${{ number_format($val->estimated_value ?? 0,0) }} ({{ $val->created_at->format('d/m/Y') }})</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm" style="width:100%;">Vincular valuación</button>
                </form>
                @else
                <a href="{{ route('admin.valuations.create', ['client_id' => $captacion->client_id]) }}" class="btn btn-sm btn-outline" style="width:100%;text-align:center;display:block;">+ Crear Opinión de Valor</a>
                @endif
                @endif
                @endif
            </div>
        </div>

        {{-- Etapa 3: Precio --}}
        <div class="side-card">
            <div class="side-card-header">
                <span class="side-card-title" style="color:#8b5cf6;">&#9632; Etapa 3 &mdash; Precio</span>
                @if($captacion->etapa3_completed_at)<span style="color:var(--success);font-size:.72rem;">&#10003; Confirmado</span>@endif
            </div>
            <div class="side-card-body">
                @if($etapa < 3)
                <p style="font-size:.8rem;color:var(--text-muted);">Disponible tras vincular la valuación.</p>
                @elseif($captacion->precio_acordado)
                <div class="info-row"><span class="lbl">Precio</span><span class="val" style="font-size:1rem;font-weight:700;color:var(--text);">${{ number_format($captacion->precio_acordado,0) }} MXN</span></div>
                @if($captacion->etapa3_completed_at)
                <p style="font-size:.75rem;color:var(--success);margin-top:.5rem;">&#10003; Cliente confirmó el {{ $captacion->etapa3_completed_at->format('d/m/Y') }}</p>
                @else
                <p style="font-size:.75rem;color:var(--text-muted);margin-top:.5rem;">Pendiente de confirmación del cliente</p>
                @endif
                @else
                <form method="POST" action="{{ route('admin.captaciones.set-price', $captacion) }}">
                    @csrf
                    <div style="margin-bottom:.5rem;">
                        <label class="form-label" style="font-size:.75rem;">Precio de venta (MXN)</label>
                        <input type="number" name="precio" class="form-input" placeholder="Ej: 3500000" step="1000" min="0">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm" style="width:100%;">Establecer precio</button>
                </form>
                @endif
            </div>
        </div>

        {{-- Etapa 4: Contrato de Exclusiva --}}
        <div class="side-card">
            <div class="side-card-header">
                <span class="side-card-title" style="color:#10b981;">&#9632; Etapa 4 &mdash; Exclusiva</span>
                @if($captacion->isEtapa4Complete())<span style="color:var(--success);font-size:.72rem;">&#10003; Firmado</span>@endif
            </div>
            <div class="side-card-body">
                @if($etapa < 4)
                <p style="font-size:.8rem;color:var(--text-muted);">Disponible cuando el cliente confirme el precio.</p>
                @elseif($captacion->signatureRequest)
                <div class="info-row"><span class="lbl">Estado</span><span class="val">{{ ucfirst($captacion->signatureRequest->status) }}</span></div>
                @if($captacion->signatureRequest->file_id)
                <a href="https://docs.google.com/document/d/{{ $captacion->signatureRequest->file_id }}" target="_blank"
                   class="btn btn-sm btn-outline" style="width:100%;text-align:center;display:block;margin-top:.5rem;">
                    &#128196; Ver en Drive
                </a>
                @endif
                @if($captacion->signatureRequest->status !== 'completed')
                <form method="POST" action="{{ route('admin.captaciones.confirmar-exclusiva', $captacion) }}" style="margin-top:.5rem;">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-primary" style="width:100%;">Confirmar firma manual</button>
                </form>
                @else
                <p style="font-size:.75rem;color:var(--success);margin-top:.5rem;">&#10003; Contrato firmado</p>
                @endif
                @else
                <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:.75rem;">Genera el contrato de exclusiva para el cliente.</p>
                <form method="POST" action="{{ route('admin.captaciones.generar-exclusiva', $captacion) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm" style="width:100%;"
                        {{ !$captacion->etapa3_completed_at ? 'disabled' : '' }}>
                        Generar Contrato
                    </button>
                </form>
                @if(!$captacion->etapa3_completed_at)
                <p style="font-size:.72rem;color:var(--text-muted);margin-top:.35rem;text-align:center;">Espera la confirmación del precio</p>
                @endif
                @endif
            </div>
        </div>

    </div>
</div>

{{-- Preview Modal --}}
<div id="preview-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.8);z-index:10000;align-items:center;justify-content:center;flex-direction:column;">
    <div style="background:#fff;border-radius:12px;max-width:900px;width:95%;max-height:90vh;display:flex;flex-direction:column;overflow:hidden;">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:.75rem 1rem;border-bottom:1px solid #e5e7eb;">
            <span id="preview-title" style="font-weight:700;font-size:.95rem;"></span>
            <div style="display:flex;gap:.5rem;align-items:center;">
                <a id="preview-download" href="#" target="_blank" class="btn btn-sm btn-outline">Descargar</a>
                <button onclick="closePreview()" style="background:none;border:none;font-size:1.4rem;cursor:pointer;color:#6b7280;">&times;</button>
            </div>
        </div>
        <div id="preview-body" style="flex:1;overflow:auto;display:flex;align-items:center;justify-content:center;min-height:400px;background:#f9fafb;">
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div id="reject-modal">
    <div style="background:#fff;border-radius:12px;max-width:420px;width:90%;padding:1.5rem;box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <h3 style="font-size:1rem;margin-bottom:1rem;">Razón de rechazo</h3>
        <form id="reject-form" method="POST">
            @csrf
            <input type="hidden" name="captacion_status" value="rechazado">
            <div style="margin-bottom:1rem;">
                <textarea name="rejection_reason" class="form-input" rows="3" placeholder="Explica por qué se rechaza este documento..." style="resize:none;"></textarea>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;">
                <button type="button" class="btn btn-outline" onclick="closeReject()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Rechazar documento</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
function switchTab(id, btn) {
    document.querySelectorAll('.tab-content').forEach(function(el) { el.classList.remove('active'); });
    document.querySelectorAll('.tab-btn').forEach(function(el) { el.classList.remove('active'); });
    document.getElementById('tab-' + id).classList.add('active');
    btn.classList.add('active');
}
function openReject(action) {
    document.getElementById('reject-form').action = action;
    document.getElementById('reject-modal').classList.add('open');
}
function closeReject() {
    document.getElementById('reject-modal').classList.remove('open');
}
document.getElementById('reject-modal').addEventListener('click', function(e) {
    if (e.target === this) closeReject();
});
function openPreview(url, type, title) {
    document.getElementById('preview-title').textContent = title;
    document.getElementById('preview-download').href = url;
    var body = document.getElementById('preview-body');
    if (type === 'img') {
        body.innerHTML = '<img src="' + url + '" style="max-width:100%;max-height:80vh;object-fit:contain;">';
    } else {
        body.innerHTML = '<iframe src="' + url + '" style="width:100%;height:75vh;border:none;"></iframe>';
    }
    document.getElementById('preview-modal').style.display = 'flex';
}
function closePreview() {
    document.getElementById('preview-modal').style.display = 'none';
    document.getElementById('preview-body').innerHTML = '';
}
document.getElementById('preview-modal').addEventListener('click', function(e) {
    if (e.target === this) closePreview();
});
</script>
@endsection
