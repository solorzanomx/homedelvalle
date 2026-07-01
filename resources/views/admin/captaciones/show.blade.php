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
.action-btn.suggested { border-width:2px; }

/* ===== STEPPER ===== */
.stepper { display:flex; align-items:center; padding:0 1.25rem 1rem; overflow-x:auto; gap:0; }
.step {
    display:flex; align-items:center; gap:.35rem; padding:.3rem .5rem; border-radius:20px;
    font-size:.7rem; font-weight:600; white-space:nowrap; color:var(--text-muted);
}
.step.completed { color:var(--success); }
.step.current   { background:var(--primary); color:#fff; padding:.3rem .75rem; box-shadow:0 2px 8px rgba(102,126,234,.3); }
.step.future    { opacity:.65; }
.step-dot { width:6px; height:6px; border-radius:50%; flex-shrink:0; }
.step.completed .step-dot { background:var(--success); }
.step.current   .step-dot { background:#fff; }
.step.future    .step-dot { background:var(--border); }
.step-arrow { color:var(--border); font-size:.65rem; margin:0 .1rem; flex-shrink:0; }
.stepper-progress { height:4px; background:var(--border); border-radius:99px; margin:-.4rem 1.25rem .9rem; overflow:hidden; }
.stepper-progress-fill { height:100%; background:var(--primary); border-radius:99px; transition:width .2s; }

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
    transition:border-color .15s, background .15s;
}
.doc-row.drag-over { border:2px dashed var(--primary); background:rgba(102,126,234,.05); }
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

/* ===== CHECKLIST (9 etapas del pipeline) ===== */
.checklist-item { display:flex; align-items:flex-start; gap:.5rem; padding:.3rem 0; font-size:.82rem; }
.checklist-item label { cursor:pointer; flex:1; line-height:1.4; }
.checklist-item input[type="checkbox"] { margin-top:3px; cursor:pointer; accent-color:var(--primary); }
.checklist-item.completed label { text-decoration:line-through; opacity:.5; }
.checklist-meta { font-size:.65rem; color:var(--text-muted); margin-left:1.25rem; }
.stage-checklist-group { margin-bottom:1rem; }
.stage-checklist-header {
    display:flex; align-items:center; gap:.5rem; padding:.5rem .65rem;
    background:var(--bg); border-radius:var(--radius); margin-bottom:.35rem;
}
.stage-checklist-header .stage-label { font-size:.82rem; font-weight:600; flex:1; display:flex; align-items:center; gap:.35rem; }
.stage-checklist-header .stage-count { font-size:.7rem; color:var(--text-muted); }
.stage-dot { width:8px; height:8px; border-radius:50%; display:inline-block; }
.checklist-item.locked { opacity:.35; pointer-events:none; }
.checklist-item.past { opacity:.5; }

/* Reject modal */
#reject-modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:9999; align-items:center; justify-content:center; }
#reject-modal.open { display:flex; }

/* ===== Cabina de etapa (cockpit) ===== */
.cockpit-header {
    background:var(--bg); border-radius:var(--radius); padding:.75rem .9rem; margin-bottom:.75rem;
}
.cockpit-header .cockpit-goal { font-size:.8rem; font-weight:600; margin-bottom:.35rem; }
.cockpit-header .cockpit-progress-bar { height:6px; background:var(--border); border-radius:99px; overflow:hidden; margin-top:.4rem; }
.cockpit-header .cockpit-progress-fill { height:100%; border-radius:99px; }
.cockpit-item {
    border:1px solid var(--border); border-radius:var(--radius); padding:.85rem 1rem; margin-bottom:.6rem;
}
.cockpit-item.done { padding:.5rem .9rem; background:var(--bg); }
.cockpit-item .cockpit-title { font-size:.85rem; font-weight:600; display:flex; align-items:center; gap:.4rem; }
.cockpit-item.done .cockpit-title { font-weight:500; }
.cockpit-item .cockpit-summary { font-size:.75rem; color:var(--text-muted); margin-top:.15rem; margin-left:1.4rem; }
.cockpit-item .cockpit-body { margin-top:.65rem; }
.cockpit-item .cockpit-field { margin-bottom:.55rem; }
.cockpit-item .cockpit-field label { display:block; font-size:.72rem; color:var(--text-muted); margin-bottom:.2rem; }
.cockpit-item .cockpit-actions { display:flex; gap:.5rem; flex-wrap:wrap; margin-bottom:.6rem; }
.cockpit-edit-link { font-size:.72rem; color:var(--primary); cursor:pointer; background:none; border:none; padding:0; }
.cockpit-motivation { font-size:.78rem; color:var(--text-muted); font-style:italic; margin-top:.3rem; }
.cockpit-item .cockpit-description { font-size:.76rem; color:var(--text-muted); margin:.15rem 0 0 1.4rem; line-height:1.4; }
.split-columns { display:flex; gap:1.25rem; flex-wrap:wrap; margin-top:.5rem; }
.split-col { flex:1; min-width:240px; }
.split-col-title { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:var(--text-muted); margin-bottom:.5rem; }
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
    $approved  = $captacion->documents->where('captacion_status','aprobado')->count();
    $total     = $captacion->documents->count();
    $pct       = $total > 0 ? round($approved / $total * 100) : 0;

    // Stepper unificado: refleja Operation.stage (lo que gobierna el kanban),
    // no portal_etapa — antes mostraban dos progresos distintos sin relación
    // visible entre sí. Ver docs/07-FLUJO-CAPTACION-Y-MEJORAS.md.
    $opStages      = \App\Models\Operation::CAPTACION_STAGES;
    $opStage       = $captacion->operation?->stage;
    $opCurrentIdx  = $opStage ? array_search($opStage, $opStages) : false;
    $opColor       = $opStage ? (\App\Models\Operation::STAGE_COLORS[$opStage] ?? '#94a3b8') : $etapaColor;
    $opStageLabel  = $opStage ? (\App\Models\Operation::STAGES[$opStage] ?? $opStage) : $etapaLabels[$etapa];
@endphp

<div class="page-header">
    <div></div>
    <div style="display:flex;gap:.5rem;">
        <a href="{{ route('clients.show', $client->id) }}" class="btn btn-outline btn-sm">&#128100; Cliente</a>
        <a href="{{ route('admin.captaciones.pipeline') }}" class="btn btn-outline btn-sm">&#8592; Pipeline</a>
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
        <div class="hero-top-bar" style="background:{{ $opColor }};"></div>
        <div class="hero-avatar" style="background:{{ $opColor }};">{{ $initials }}</div>
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
            <span class="badge" style="background:{{ $opColor }}1a;color:{{ $opColor }};">{{ $opStageLabel }}</span>
            @if($captacion->precio_acordado)
            <span class="badge badge-green" style="font-weight:700;">${{ number_format($captacion->precio_acordado,0) }}</span>
            @endif
            @php
                $statusColor = match($captacion->status) {
                    'completado','convertido' => 'green',
                    'cancelado','declinado'   => 'red',
                    default                   => 'yellow',
                };
            @endphp
            <span class="badge badge-{{ $statusColor }}">
                {{ ucfirst($captacion->status) }}
            </span>
        </div>
    </div>

    @php
        // Orden de los botones de contacto según lo que el manual del broker
        // recomienda hacer primero en la etapa actual (no cambia el estilo,
        // ya consistente entre los 4 — solo el orden y un acento sutil).
        $primaryAction = \App\Livewire\Admin\CaptacionStageCockpit::STAGE_PRIMARY_ACTION[$opStage] ?? 'llamar';
    @endphp
    <div class="hero-actions">
        @php $waHtml = 'wa' . ($primaryAction === 'whatsapp' ? ' suggested' : ''); $phoneHtml = 'phone' . ($primaryAction === 'llamar' ? ' suggested' : ''); @endphp
        @if($primaryAction === 'whatsapp')
            @if($waLink)<a href="{{ $waLink }}" target="_blank" class="action-btn {{ $waHtml }}">&#128172; WhatsApp</a>@endif
            @if($telLink)<a href="{{ $telLink }}" class="action-btn {{ $phoneHtml }}">&#128222; Llamar</a>@endif
        @else
            @if($telLink)<a href="{{ $telLink }}" class="action-btn {{ $phoneHtml }}">&#128222; Llamar</a>@endif
            @if($waLink)<a href="{{ $waLink }}" target="_blank" class="action-btn {{ $waHtml }}">&#128172; WhatsApp</a>@endif
        @endif
        <a href="{{ route('clients.show', $client->id) }}" class="action-btn">&#128100; Perfil</a>
        @if($captacion->status !== 'declinado' && $captacion->status !== 'completado')
        <button type="button" onclick="openDecline()" class="action-btn" style="border-color:#ef4444;color:#ef4444;background:rgba(239,68,68,.04);">&#10005; Declinar</button>
        @endif
    </div>

    {{-- Stage Stepper — las 6 etapas reales de Operation.stage --}}
    @if($opCurrentIdx !== false)
    <div class="stepper">
        @foreach($opStages as $idx => $stageKey)
        @if($idx > 0)
        <span class="step-arrow">&#9656;</span>
        @endif
        <div class="step {{ $idx < $opCurrentIdx ? 'completed' : ($idx === $opCurrentIdx ? 'current' : 'future') }}">
            <span class="step-dot"></span>
            {{ \App\Models\Operation::STAGES[$stageKey] ?? $stageKey }}
            @if($idx < $opCurrentIdx)
            &#10003;
            @endif
        </div>
        @endforeach
    </div>
    <div class="stepper-progress">
        <div class="stepper-progress-fill" style="width:{{ round(($opCurrentIdx / max(count($opStages) - 1, 1)) * 100) }}%;"></div>
    </div>
    @endif

    {{-- Docs progress --}}
    <div class="advance-bar">
        <span class="progress-text">{{ $approved }}/{{ $total }} documentos aprobados</span>
        <div class="progress-fill">
            <div class="progress-fill-inner" style="width:{{ $pct }}%;background:{{ $etapaColor }};"></div>
        </div>
        @if($etapa >= 4)
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
                <input type="hidden" name="property_id" value="{{ $captacion->property_id }}">
                <textarea name="description" rows="1" placeholder="Nota rapida sobre este proceso..."
                    oninput="this.style.height='';this.style.height=Math.min(this.scrollHeight,100)+'px'"></textarea>
                <button type="submit" class="btn btn-primary btn-sm">Enviar</button>
            </form>
        </div>

        {{-- Proceso de la etapa — siempre visible al entrar, no vive detrás
             de una pestaña. Ver docs/07-FLUJO-CAPTACION-Y-MEJORAS.md. --}}
        @if($captacion->operation)
        <livewire:admin.captacion-stage-cockpit
            :operation="$captacion->operation"
            :captacion="$captacion"
            wire:key="cockpit-{{ $captacion->operation->id }}"
        />
        @endif

        <div class="tab-bar">
            <button class="tab-btn active" onclick="switchTab('docs',this)">
                Documentos <span class="tab-count">{{ $total }}</span>
            </button>
            <button class="tab-btn" onclick="switchTab('timeline',this)">
                Actividad <span class="tab-count">{{ $interactions->count() }}</span>
            </button>
            <button class="tab-btn" onclick="switchTab('quickquote',this)">
                📊 Valor de Mercado
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
            <div class="doc-row" ondragover="event.preventDefault(); this.classList.add('drag-over');" ondragleave="this.classList.remove('drag-over');" ondrop="handleDocDrop(event, this)">
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
                            <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" capture="environment" style="display:none;" onchange="handleDocFileChange(this)">
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
            <div class="doc-row" ondragover="event.preventDefault(); this.classList.add('drag-over');" ondragleave="this.classList.remove('drag-over');" ondrop="handleDocDrop(event, this)">
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
                            <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" capture="environment" style="display:none;" onchange="handleDocFileChange(this)">
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

        {{-- TAB: Valor de Mercado (Quick Quote) --}}
        <div class="tab-content" id="tab-quickquote">
            @php
                $qProperty = $captacion->property ?? $clientProperty ?? null;
                $qColoniaId = $qProperty?->market_colonia_id ?? null;
                $qType = match($qProperty?->property_type ?? '') {
                    'Apartment' => 'apartment',
                    'House'     => 'house',
                    'Office'    => 'office',
                    default     => 'apartment',
                };
                $qM2 = $qProperty?->construction_area ?? $qProperty?->area ?? null;
                $qLandM2 = $qProperty?->lot_area ?? 0;
                $qYearBuilt = $qProperty?->year_built ?? null;
            @endphp
            <livewire:admin.quick-quote
                :coloniaId="$qColoniaId"
                :propertyType="$qType"
                :m2Construction="$qM2"
                :m2Land="$qLandM2"
                :yearBuilt="$qYearBuilt"
                :bedrooms="$qProperty?->bedrooms ?? 0"
                :bathrooms="$qProperty?->bathrooms ?? 0"
                :parking="$qProperty?->parking ?? -1"
                :widgetMode="true"
                wire:key="quick-quote-{{ $captacion->id }}"
            />
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

        {{-- Documentos para la firma — para que el propietario los vaya
             consiguiendo desde antes de llegar a Revisión de Documentos.
             Ver docs/07-FLUJO-CAPTACION-Y-MEJORAS.md. --}}
        @php
            $pendingDocs = $captacion->getPendingRequiredDocs();
            $pendingDocLabels = array_map(fn($cat) => \App\Models\Document::CATEGORIES[$cat] ?? $cat, $pendingDocs);
        @endphp
        <div class="side-card">
            <div class="side-card-header">
                <span class="side-card-title">&#128193; Documentos para la firma</span>
            </div>
            <div class="side-card-body">
                @if(empty($pendingDocLabels))
                <p style="font-size:.8rem;color:var(--success);font-weight:600;">&#10003; Todo listo para la firma</p>
                @else
                <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:.5rem;">El propietario los puede ir subiendo desde su Portal desde ahora — no hace falta esperar a Revisión de Documentos.</p>
                <ul style="font-size:.8rem;margin:0 0 .6rem 1.1rem;padding:0;">
                    @foreach($pendingDocLabels as $label)
                    <li>{{ $label }}</li>
                    @endforeach
                </ul>
                @if($waLink)
                @php
                    $docsMsg = "Hola {$client->name}, para ir avanzando con tu propiedad necesitamos que nos compartas: " . implode(', ', $pendingDocLabels) . ". Los puedes subir directo desde tu Portal cuando puedas, antes de llegar a la firma de exclusiva.";
                @endphp
                <a href="{{ $waLink }}?text={{ urlencode($docsMsg) }}" target="_blank" class="action-btn wa" style="width:100%;">&#128172; Recordar por WhatsApp</a>
                @endif
                @endif
            </div>
        </div>

        {{-- Presentación — antes solo tenía botón en la columna "contacto" del
             kanban y desaparecía al avanzar de etapa. Ver docs/07-FLUJO-
             CAPTACION-Y-MEJORAS.md. --}}
        <div class="side-card">
            <div class="side-card-header">
                <span class="side-card-title">&#128196; Presentación</span>
            </div>
            <div class="side-card-body">
                <a href="{{ route('admin.captaciones.presentation', $captacion) }}" target="_blank" class="btn btn-primary btn-sm" style="width:100%;display:block;text-align:center;margin-bottom:.6rem;">
                    Ver Presentación
                </a>
                <form method="POST" action="{{ route('admin.captaciones.presentation.send.email', $captacion) }}" style="display:flex;gap:.4rem;margin-bottom:.4rem;">
                    @csrf
                    <input type="email" name="email" placeholder="correo@ejemplo.com" value="{{ $captacion->client->email ?? '' }}" class="form-control" style="font-size:.8rem;" required>
                    <button type="submit" class="btn btn-sm btn-outline" style="white-space:nowrap;">Enviar</button>
                </form>
                <form method="POST" action="{{ route('admin.captaciones.presentation.send.whatsapp', $captacion) }}" style="display:flex;gap:.4rem;">
                    @csrf
                    <input type="text" name="phone" placeholder="55 1234 5678" value="{{ $captacion->client->whatsapp ?? $captacion->client->phone ?? '' }}" class="form-control" style="font-size:.8rem;" required>
                    <button type="submit" class="btn btn-sm btn-outline" style="white-space:nowrap;">WhatsApp</button>
                </form>
            </div>
        </div>

        {{-- Brief pre-visita — llegar preparado sin reconstruir de memoria --}}
        @if($marketSnapshot || $captacion->notes_from_call)
        <div class="side-card">
            <div class="side-card-header">
                <span class="side-card-title">&#128203; Brief pre-visita</span>
            </div>
            <div class="side-card-body">
                @if($marketSnapshot)
                <div class="info-row">
                    <span class="lbl">Precio de referencia</span>
                    <span class="val" style="font-weight:700;">
                        ${{ number_format($marketSnapshot->price_m2_low, 0) }}–${{ number_format($marketSnapshot->price_m2_high, 0) }} /m²{{ $marketSnapshot->operation_type === 'rent' ? '/mes' : '' }}
                    </span>
                </div>
                <div style="font-size:.72rem;color:var(--text-muted);margin-bottom:.6rem;">Confianza: {{ $marketSnapshot->confidence_label }} · Observatorio HDV</div>
                @endif
                @if($captacion->notes_from_call)
                <div class="info-row" style="flex-direction:column;align-items:flex-start;">
                    <span class="lbl">Notas de la llamada</span>
                    <span class="val" style="font-size:.8rem;font-weight:400;white-space:pre-line;">{{ $captacion->notes_from_call }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Propuesta de Servicios — para presentar en vivo durante la visita --}}
        <div class="side-card">
            <div class="side-card-header">
                <span class="side-card-title">&#128188; Propuesta de Servicios</span>
            </div>
            <div class="side-card-body">
                <a href="{{ route('admin.captaciones.servicios.live', $captacion) }}" target="_blank" class="btn btn-primary btn-sm" style="width:100%;display:block;text-align:center;margin-bottom:.4rem;">
                    Ver en vivo (para mostrar en la visita)
                </a>
                <a href="{{ route('admin.captaciones.servicios.pdf', $captacion) }}" target="_blank" class="btn btn-sm btn-outline" style="width:100%;display:block;text-align:center;margin-bottom:.6rem;">
                    Ver PDF
                </a>
                <form method="POST" action="{{ route('admin.captaciones.servicios.send.email', $captacion) }}" style="display:flex;gap:.4rem;margin-bottom:.4rem;">
                    @csrf
                    <input type="email" name="email" placeholder="correo@ejemplo.com" value="{{ $captacion->client->email ?? '' }}" class="form-control" style="font-size:.8rem;" required>
                    <button type="submit" class="btn btn-sm btn-outline" style="white-space:nowrap;">Enviar</button>
                </form>
                <form method="POST" action="{{ route('admin.captaciones.servicios.send.whatsapp', $captacion) }}" style="display:flex;gap:.4rem;">
                    @csrf
                    <input type="text" name="phone" placeholder="55 1234 5678" value="{{ $captacion->client->whatsapp ?? $captacion->client->phone ?? '' }}" class="form-control" style="font-size:.8rem;" required>
                    <button type="submit" class="btn btn-sm btn-outline" style="white-space:nowrap;">WhatsApp</button>
                </form>
            </div>
        </div>

        {{-- Agendar visita — atajo de un clic, sin ir al perfil del cliente --}}
        <div class="side-card">
            <div class="side-card-header">
                <span class="side-card-title">&#128197; Agendar visita</span>
            </div>
            <div class="side-card-body">
                <form method="POST" action="{{ route('admin.captaciones.schedule-visit', $captacion) }}">
                    @csrf
                    <div style="display:flex;gap:.4rem;margin-bottom:.5rem;">
                        <input type="date" name="scheduled_at_date" class="form-control" style="font-size:.82rem;" required>
                        <input type="time" name="scheduled_at_time" class="form-control" style="font-size:.82rem;max-width:110px;" value="10:00">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm" style="width:100%;">Confirmar visita</button>
                </form>
                <p style="font-size:.72rem;color:var(--text-muted);margin-top:.4rem;">Se envía confirmación automática al propietario con link para confirmar/reagendar.</p>
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
                <div class="info-row"><span class="lbl">Valor estimado</span><span class="val" style="color:var(--text);font-weight:700;">${{ number_format($captacion->valuation->total_value_mid ?? 0, 0) }}</span></div>
                <div class="info-row"><span class="lbl">Fecha</span><span class="val">{{ $captacion->valuation->created_at->format('d/m/Y') }}</span></div>
                <div class="info-row"><span class="lbl">Colonia</span><span class="val" style="font-size:.78rem;">{{ $captacion->valuation->colonia?->name ?? $captacion->valuation->input_colonia_raw ?? '—' }}</span></div>
                <a href="{{ route('admin.valuations.show', $captacion->valuation) }}" class="btn btn-sm btn-outline" style="width:100%;text-align:center;display:block;margin-top:.5rem;">Ver valuación</a>
                <form method="POST" action="{{ route('admin.captaciones.unlink-valuation', $captacion) }}" style="margin-top:.4rem;" onsubmit="return confirm('¿Desvincular esta valuación?')">
                    @csrf
                    <button type="submit" class="btn btn-sm" style="width:100%;background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;">Desvincular</button>
                </form>
                @else
                @if($etapa < 2)
                <p style="font-size:.8rem;color:var(--text-muted);">Disponible cuando se completen los documentos.</p>
                @else
                <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:.75rem;">Vincula una opinión de valor existente o crea una nueva.</p>
                @if($valuations->isNotEmpty())
                <form method="POST" action="{{ route('admin.captaciones.link-valuation', $captacion) }}" style="margin-bottom:.5rem;">
                    @csrf
                    <select name="valuation_id" class="form-select" style="margin-bottom:.5rem;font-size:.82rem;">
                        @foreach($valuations as $val)
                        <option value="{{ $val->id }}">#{{ $val->id }} — ${{ number_format($val->total_value_mid ?? 0,0) }} ({{ $val->created_at->format('d/m/Y') }}){{ $val->colonia ? ' · '.$val->colonia->name : '' }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm" style="width:100%;">Vincular valuación</button>
                </form>
                @endif
                <a href="{{ route('admin.valuations.create', array_filter(['property' => $clientProperty?->id, 'client_id' => $captacion->client_id])) }}" class="btn btn-sm btn-outline" style="width:100%;text-align:center;display:block;">+ Crear Opinión de Valor</a>
                @if($clientProperty)
                <div style="font-size:.72rem;color:var(--text-muted);margin-top:.4rem;">Propiedad: {{ $clientProperty->title }}{{ $clientProperty->marketColonia ? ' — '.$clientProperty->marketColonia->name : '' }}</div>
                @else
                <div style="font-size:.72rem;color:#f59e0b;margin-top:.4rem;">Sin propiedad vinculada al cliente. <a href="{{ route('properties.create') }}" style="color:var(--primary);">Crear propiedad</a></div>
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

{{-- Confirmar antes de subir un documento — mismo patrón visual que
     #preview-modal. Ver docs/07-FLUJO-CAPTACION-Y-MEJORAS.md. --}}
<div id="doc-confirm-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.8);z-index:10000;align-items:center;justify-content:center;flex-direction:column;">
    <div style="background:#fff;border-radius:12px;max-width:500px;width:95%;max-height:90vh;display:flex;flex-direction:column;overflow:hidden;">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:.75rem 1rem;border-bottom:1px solid #e5e7eb;">
            <span style="font-weight:700;font-size:.95rem;">Confirmar documento</span>
            <button type="button" onclick="cancelDocUpload()" style="background:none;border:none;font-size:1.4rem;cursor:pointer;color:#6b7280;">&times;</button>
        </div>
        <div id="doc-confirm-body" style="flex:1;overflow:auto;display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:200px;background:#f9fafb;padding:1.5rem;gap:.5rem;">
        </div>
        <div style="display:flex;gap:.5rem;justify-content:flex-end;padding:.85rem 1rem;border-top:1px solid #e5e7eb;">
            <button type="button" class="btn btn-outline" onclick="cancelDocUpload()">Elegir otra</button>
            <button type="button" class="btn btn-primary" onclick="confirmDocUpload()">Confirmar y subir</button>
        </div>
    </div>
</div>

{{-- Decline Modal --}}
<div id="decline-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;max-width:460px;width:90%;padding:1.5rem;box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <h3 style="font-size:1rem;margin-bottom:.4rem;color:#dc2626;">&#10005; Declinar captación amistosamente</h3>
        <p style="font-size:.82rem;color:var(--text-muted);margin-bottom:1rem;">Se marcará el caso como declinado, se cancelará la operación y se enviará un email amistoso al propietario (si tiene email registrado).</p>
        <form method="POST" action="{{ route('admin.captaciones.declinar', $captacion) }}">
            @csrf
            <div style="margin-bottom:1rem;">
                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:.35rem;">Motivo del declive <span style="color:#dc2626;">*</span></label>
                <textarea name="reason" rows="4" required minlength="10" maxlength="1000"
                          style="width:100%;padding:.5rem .75rem;border:1px solid var(--border);border-radius:var(--radius);font-family:inherit;font-size:.85rem;resize:vertical;"
                          placeholder="Ej. Las condiciones económicas del propietario no se alinean con el mercado actual..."></textarea>
                <p style="font-size:.72rem;color:var(--text-muted);margin-top:.25rem;">Mínimo 10 caracteres. Este mensaje no se muestra al propietario.</p>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;">
                <button type="button" class="btn btn-outline" onclick="closeDecline()">Cancelar</button>
                <button type="submit" class="btn btn-sm" style="background:#dc2626;color:#fff;border-color:#dc2626;">Declinar caso</button>
            </div>
        </form>
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
function openDecline() {
    document.getElementById('decline-modal').style.display = 'flex';
}
function closeDecline() {
    document.getElementById('decline-modal').style.display = 'none';
}
document.getElementById('decline-modal').addEventListener('click', function(e) {
    if (e.target === this) closeDecline();
});
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

// ── Subida de documentos: preview + drag&drop ──────────────────────────────
var pendingUploadForm = null;

function handleDocFileChange(input) {
    if (!input.files || !input.files[0]) return;
    showDocConfirm(input.form, input.files[0]);
}

function handleDocDrop(event, rowEl) {
    event.preventDefault();
    rowEl.classList.remove('drag-over');
    var file = event.dataTransfer.files[0];
    if (!file) return;
    var input = rowEl.querySelector('input[type="file"]');
    var dt = new DataTransfer();
    dt.items.add(file);
    input.files = dt.files;
    showDocConfirm(input.form, file);
}

function showDocConfirm(form, file) {
    pendingUploadForm = form;
    var body = document.getElementById('doc-confirm-body');

    if (file.type.startsWith('image/')) {
        var reader = new FileReader();
        reader.onload = function(e) {
            body.innerHTML = '<img src="' + e.target.result + '" style="max-width:100%;max-height:50vh;object-fit:contain;border-radius:8px;">'
                + '<span style="font-size:.8rem;color:var(--text-muted);">' + file.name + '</span>';
        };
        reader.readAsDataURL(file);
    } else {
        body.innerHTML = '<span style="font-size:2.5rem;">&#128196;</span>'
            + '<span style="font-size:.85rem;font-weight:600;">' + file.name + '</span>'
            + '<span style="font-size:.75rem;color:var(--text-muted);">No se puede previsualizar este tipo de archivo, pero se subirá tal cual.</span>';
    }

    document.getElementById('doc-confirm-modal').style.display = 'flex';
}

function confirmDocUpload() {
    if (pendingUploadForm) pendingUploadForm.submit();
}

function cancelDocUpload() {
    if (pendingUploadForm) {
        var input = pendingUploadForm.querySelector('input[type="file"]');
        if (input) input.value = '';
    }
    pendingUploadForm = null;
    document.getElementById('doc-confirm-modal').style.display = 'none';
    document.getElementById('doc-confirm-body').innerHTML = '';
}
document.getElementById('doc-confirm-modal').addEventListener('click', function(e) {
    if (e.target === this) cancelDocUpload();
});
</script>
@endsection
