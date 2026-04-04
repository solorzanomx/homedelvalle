@extends('layouts.app-sidebar')
@section('title', $operation->client->name ?? 'Operacion #' . $operation->id)

@section('styles')
<style>
/* ===== HERO CARD ===== */
.hero-card {
    background: var(--card); border: 1px solid var(--border); border-radius: var(--radius);
    overflow: hidden; margin-bottom: 1.25rem;
}
.hero-top {
    display: flex; align-items: center; gap: 1rem;
    padding: 1rem 1.25rem; position: relative;
}
.hero-temp-bar { position: absolute; top: 0; left: 0; right: 0; height: 3px; }
.hero-temp-bar.hot { background: #ef4444; }
.hero-temp-bar.warm { background: #f59e0b; }
.hero-temp-bar.cold { background: #3b82f6; }
.hero-avatar {
    width: 52px; height: 52px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; font-weight: 700; color: #fff; flex-shrink: 0;
}
.hero-info { flex: 1; min-width: 0; }
.hero-name { font-size: 1.15rem; font-weight: 700; color: var(--text); }
.hero-sub {
    font-size: 0.82rem; color: var(--text-muted); margin-top: 0.1rem;
    display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;
}
.hero-badges { display: flex; gap: 0.3rem; flex-shrink: 0; }

/* Action Buttons */
.hero-actions {
    display: flex; gap: 0.35rem; padding: 0 1.25rem 1rem;
}
.action-btn {
    flex: 1; display: flex; align-items: center; justify-content: center; gap: 0.4rem;
    padding: 0.6rem 0.5rem; border-radius: var(--radius);
    font-size: 0.82rem; font-weight: 600; cursor: pointer;
    border: 1.5px solid var(--border); background: var(--card); color: var(--text);
    transition: all 0.15s; text-decoration: none;
}
.action-btn:hover { border-color: var(--primary); color: var(--primary); }
.action-btn.wa { color: #25d366; border-color: #25d366; background: rgba(37,211,102,0.04); }
.action-btn.wa:hover { background: #25d366; color: #fff; }
.action-btn.phone { color: #3b82f6; border-color: #3b82f6; background: rgba(59,130,246,0.04); }
.action-btn.phone:hover { background: #3b82f6; color: #fff; }
.action-btn-icon { font-size: 1.1rem; }

/* ===== STEPPER ===== */
.stepper {
    display: flex; align-items: center; gap: 0;
    padding: 0 1.25rem 1rem; overflow-x: auto;
}
.step {
    display: flex; align-items: center; gap: 0.35rem;
    padding: 0.3rem 0.5rem; border-radius: 20px; font-size: 0.7rem; font-weight: 600;
    white-space: nowrap; transition: all 0.15s;
    color: var(--text-muted); background: transparent;
}
.step.completed { color: var(--success); }
.step.current {
    background: var(--primary); color: #fff;
    padding: 0.3rem 0.7rem;
    box-shadow: 0 2px 8px rgba(59,130,196,0.3);
}
.step.future { opacity: 0.4; }
.step-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.step.completed .step-dot { background: var(--success); }
.step.current .step-dot { background: #fff; }
.step.future .step-dot { background: var(--border); }
.step-arrow { color: var(--border); font-size: 0.65rem; margin: 0 0.1rem; flex-shrink: 0; }

/* ===== ADVANCE BAR ===== */
.advance-bar {
    display: flex; align-items: center; gap: 0.5rem;
    padding: 0.65rem 1.25rem; border-top: 1px solid var(--border);
    background: var(--bg);
}
.advance-bar .progress-text {
    font-size: 0.75rem; color: var(--text-muted); white-space: nowrap;
}
.advance-bar .progress-fill {
    flex: 1; height: 5px; background: var(--border); border-radius: 3px; overflow: hidden;
}
.advance-bar .progress-fill-inner { height: 100%; border-radius: 3px; transition: width 0.3s; }
.advance-btn {
    padding: 0.4rem 1rem; border-radius: var(--radius);
    font-size: 0.8rem; font-weight: 700; color: #fff;
    background: var(--primary); border: none; cursor: pointer;
    transition: all 0.15s; white-space: nowrap;
}
.advance-btn:hover { opacity: 0.9; box-shadow: 0 2px 8px rgba(59,130,196,0.3); }

/* ===== LAYOUT ===== */
.op-layout { display: grid; grid-template-columns: 1fr 320px; gap: 1.25rem; align-items: start; }
@media (max-width: 1024px) { .op-layout { grid-template-columns: 1fr; } }

/* ===== QUICK NOTE ===== */
.quick-note {
    background: var(--card); border: 1px solid var(--border); border-radius: var(--radius);
    padding: 0.85rem 1rem; margin-bottom: 1.25rem;
}
.quick-note-form { display: flex; gap: 0.5rem; align-items: flex-end; }
.quick-note-form textarea {
    flex: 1; resize: none; border: 1px solid var(--border); border-radius: var(--radius);
    padding: 0.5rem 0.65rem; font-size: 0.85rem; font-family: inherit;
    background: var(--bg); color: var(--text); min-height: 38px; max-height: 100px;
    transition: border-color 0.15s;
}
.quick-note-form textarea:focus { outline: none; border-color: var(--primary); }

/* ===== TABS ===== */
.tab-bar {
    display: flex; gap: 0; border-bottom: 2px solid var(--border); margin-bottom: 1.25rem;
    overflow-x: auto;
}
.tab-btn {
    padding: 0.55rem 0.85rem; font-size: 0.82rem; font-weight: 500;
    color: var(--text-muted); background: none; border: none; cursor: pointer;
    border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all 0.15s;
    white-space: nowrap; display: flex; align-items: center; gap: 0.3rem;
}
.tab-btn:hover { color: var(--text); }
.tab-btn.active { color: var(--primary); border-bottom-color: var(--primary); }
.tab-count {
    font-size: 0.68rem; font-weight: 700; background: var(--bg);
    padding: 0.05rem 0.35rem; border-radius: 8px; color: var(--text-muted);
}
.tab-btn.active .tab-count { background: rgba(59,130,196,0.1); color: var(--primary); }
.tab-content { display: none; }
.tab-content.active { display: block; }

/* ===== TIMELINE ===== */
.timeline { position: relative; padding-left: 24px; }
.timeline::before { content: ''; position: absolute; left: 8px; top: 0; bottom: 0; width: 2px; background: var(--border); }
.timeline-item { position: relative; margin-bottom: 1rem; }
.timeline-dot {
    position: absolute; left: -20px; top: 4px; width: 14px; height: 14px; border-radius: 50%;
    border: 2px solid var(--card); z-index: 1;
}
.timeline-content {
    background: var(--card); border: 1px solid var(--border); border-radius: var(--radius);
    padding: 0.65rem 0.85rem;
}
.timeline-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.2rem; }
.timeline-type { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
.timeline-date { font-size: 0.7rem; color: var(--text-muted); }
.timeline-body { font-size: 0.85rem; line-height: 1.5; }
.timeline-meta { font-size: 0.7rem; color: var(--text-muted); margin-top: 0.25rem; }

/* ===== SIDEBAR ===== */
.info-panel { position: sticky; top: 1rem; }
.info-section {
    padding: 0.75rem 0; border-bottom: 1px solid var(--border);
}
.info-section:last-child { border-bottom: none; }
.info-label {
    font-size: 0.68rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.04em; color: var(--text-muted); margin-bottom: 0.5rem;
}
.info-row { display: flex; justify-content: space-between; padding: 0.25rem 0; font-size: 0.82rem; }
.info-row .lbl { color: var(--text-muted); }
.info-row .val { font-weight: 500; text-align: right; max-width: 60%; }
.info-row .val a { color: var(--primary); }
.info-stat-grid {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.4rem; text-align: center;
}
.info-stat-val { font-size: 1.05rem; font-weight: 700; color: var(--text); }
.info-stat-lbl { font-size: 0.65rem; color: var(--text-muted); }

/* ===== CHECKLIST ===== */
.checklist-item { display: flex; align-items: flex-start; gap: 0.5rem; padding: 0.3rem 0; font-size: 0.82rem; }
.checklist-item label { cursor: pointer; flex: 1; line-height: 1.4; }
.checklist-item input[type="checkbox"] { margin-top: 3px; cursor: pointer; accent-color: var(--primary); }
.checklist-item.completed label { text-decoration: line-through; opacity: 0.5; }
.checklist-meta { font-size: 0.65rem; color: var(--text-muted); margin-left: 1.25rem; }
.stage-checklist-group { margin-bottom: 1rem; }
.stage-checklist-header {
    display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.65rem;
    background: var(--bg); border-radius: var(--radius); margin-bottom: 0.35rem;
}
.stage-checklist-header .stage-label { font-size: 0.82rem; font-weight: 600; flex: 1; display: flex; align-items: center; gap: 0.35rem; }
.stage-checklist-header .stage-count { font-size: 0.7rem; color: var(--text-muted); }
.stage-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
.checklist-item.locked { opacity: 0.35; pointer-events: none; }
.checklist-item.past { opacity: 0.5; }

/* ===== DOCUMENTS ===== */
.doc-item {
    display: flex; align-items: center; gap: 0.65rem; padding: 0.65rem 0.85rem;
    border: 1px solid var(--border); border-radius: var(--radius); margin-bottom: 0.4rem; background: var(--card);
}
.doc-icon { font-size: 1.3rem; flex-shrink: 0; }
.doc-info { flex: 1; overflow: hidden; }
.doc-name { font-size: 0.82rem; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.doc-meta { font-size: 0.7rem; color: var(--text-muted); }
.doc-actions { display: flex; gap: 0.2rem; flex-shrink: 0; }

/* ===== MENTION ===== */
.mention-dropdown {
    position: absolute; left: 0; right: 0; top: 100%; background: var(--card); border: 1px solid var(--border);
    border-radius: var(--radius); box-shadow: 0 6px 20px rgba(0,0,0,0.1); z-index: 100; max-height: 200px; overflow-y: auto;
}
.mention-item { display: flex; align-items: center; gap: 0.5rem; padding: 0.45rem 0.65rem; cursor: pointer; font-size: 0.82rem; transition: background 0.1s; }
.mention-item:hover, .mention-item.selected { background: var(--bg); }
.mention-item .m-avatar {
    width: 26px; height: 26px; border-radius: 50%; background: var(--primary); color: #fff;
    display: flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 600; flex-shrink: 0; overflow: hidden;
}
.mention-item .m-avatar img { width: 100%; height: 100%; object-fit: cover; }
.mention-item .m-name { font-weight: 500; }
.mention-item .m-title { font-size: 0.7rem; color: var(--text-muted); }

/* ===== STAGE CHANGE ===== */
.stage-change-row {
    display: flex; gap: 0.4rem; align-items: center;
}

@media (max-width: 768px) {
    .hero-actions { flex-wrap: wrap; }
    .action-btn { flex: 0 0 calc(50% - 0.175rem); }
    .stepper { padding: 0 0.75rem 0.75rem; }
    .tab-bar { gap: 0; }
    .tab-btn { padding: 0.45rem 0.6rem; font-size: 0.78rem; }
}
</style>
@endsection

@section('content')
@php
    $client = $operation->client;
    $temp = $client->lead_temperature ?? '';
    $phone = $client->whatsapp ?: ($client->phone ?? '');
    $waLink = $phone ? 'https://wa.me/52' . preg_replace('/\D/', '', $phone) : '';
    $telLink = $phone ? 'tel:' . $phone : '';
    $initials = collect(explode(' ', $client->name ?? '?'))->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->join('');
    $avatarBg = match($temp) { 'hot' => '#ef4444', 'warm' => '#f59e0b', 'cold' => '#3b82f6', default => 'var(--primary)' };
    $availableStages = $operation->getAvailableStages();
    $stageKeys = array_keys($availableStages);
    $currentIdx = array_search($operation->stage, $stageKeys);
    $nextStage = $operation->getNextStage();
    $currentItems = $operation->checklistItems->where('stage', $operation->stage)->sortBy('id');
    $clCompleted = $currentItems->where('is_completed', true)->count();
    $clTotal = $currentItems->count();
    $clPct = $clTotal > 0 ? round(($clCompleted / $clTotal) * 100) : 0;
@endphp

<div class="page-header">
    <div></div>
    <div style="display:flex; gap:0.5rem;">
        <a href="{{ route('operations.edit', $operation) }}" class="btn btn-outline btn-sm">&#9998; Editar</a>
        <a href="{{ route('operations.index') }}" class="btn btn-outline btn-sm">&#8592; Pipeline</a>
    </div>
</div>

{{-- ===== HERO CARD ===== --}}
<div class="hero-card">
    <div class="hero-top">
        <div class="hero-temp-bar {{ $temp ?: 'none' }}"></div>
        <div class="hero-avatar" style="background:{{ $avatarBg }}">
            @if($client->photo)
                <img src="{{ asset('storage/' . $client->photo) }}" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
            @else
                {{ $initials }}
            @endif
        </div>
        <div class="hero-info">
            <div class="hero-name">{{ $client->name ?? 'Sin cliente' }}</div>
            <div class="hero-sub">
                @if($operation->property)
                    {{ Str::limit($operation->property->title, 35) }}
                @endif
                @if($phone)
                    <span style="color:var(--text-muted);">&middot; {{ $phone }}</span>
                @endif
            </div>
        </div>
        <div class="hero-badges">
            @if($operation->type === 'captacion')
                <span class="badge" style="background:rgba(20,184,166,0.1); color:#14b8a6;">{{ $operation->type_label }}</span>
            @elseif($operation->type === 'venta')
                <span class="badge badge-blue">{{ $operation->type_label }}</span>
            @else
                <span class="badge" style="background:#EBF5FF; color:#2563A0;">{{ $operation->type_label }}</span>
            @endif
            @if($operation->amount)
                <span class="badge badge-green" style="font-weight:700;">${{ number_format($operation->amount, 0) }}</span>
            @elseif($operation->monthly_rent)
                <span class="badge badge-green" style="font-weight:700;">${{ number_format($operation->monthly_rent, 0) }}/mes</span>
            @endif
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="hero-actions">
        @if($waLink)
        <a href="{{ $waLink }}" target="_blank" class="action-btn wa">
            <span class="action-btn-icon">&#128172;</span> WhatsApp
        </a>
        @endif
        @if($telLink)
        <a href="{{ $telLink }}" class="action-btn phone">
            <span class="action-btn-icon">&#128222;</span> Llamar
        </a>
        @endif
        @if($client)
        <a href="{{ route('clients.show', $client->id) }}" class="action-btn">
            <span class="action-btn-icon">&#128100;</span> Perfil
        </a>
        @endif
        @if($operation->property)
        <a href="{{ route('properties.show', $operation->property_id) }}" class="action-btn">
            <span class="action-btn-icon">&#127968;</span> Propiedad
        </a>
        @endif
    </div>

    {{-- Stage Stepper --}}
    <div class="stepper">
        @foreach($stageKeys as $i => $sk)
            @if($i > 0) <span class="step-arrow">&#9656;</span> @endif
            <div class="step {{ $i < $currentIdx ? 'completed' : ($i === $currentIdx ? 'current' : 'future') }}">
                <span class="step-dot"></span>
                {{ $availableStages[$sk] }}
            </div>
        @endforeach
    </div>

    {{-- Advance Bar --}}
    <div class="advance-bar">
        <span class="progress-text">{{ $clCompleted }}/{{ $clTotal }} checklist</span>
        <div class="progress-fill">
            <div class="progress-fill-inner" style="width:{{ $clPct }}%; background:{{ $operation->stage_color }};"></div>
        </div>
        @if($nextStage)
        <form method="POST" action="{{ route('operations.update-stage', $operation->id) }}" style="display:inline;">
            @csrf @method('PATCH')
            <input type="hidden" name="stage" value="{{ $nextStage }}">
            <button type="submit" class="advance-btn">{{ \App\Models\Operation::STAGES[$nextStage] ?? $nextStage }} &#8594;</button>
        </form>
        @else
        <span style="font-size:0.78rem; color:var(--success); font-weight:600;">Ultima etapa</span>
        @endif
    </div>
</div>

<div class="op-layout">
    {{-- ===== LEFT: Tabs ===== --}}
    <div>
        {{-- Quick Note --}}
        <div class="quick-note">
            <form method="POST" action="{{ route('operations.comments.store', $operation->id) }}" class="quick-note-form">
                @csrf
                <div style="flex:1; position:relative;">
                    <textarea name="body" class="mention-input" rows="1" required placeholder="Nota rapida... @ para mencionar"
                        oninput="this.style.height=''; this.style.height=Math.min(this.scrollHeight,100)+'px'"></textarea>
                    <div class="mention-dropdown" style="display:none;"></div>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Enviar</button>
            </form>
        </div>

        {{-- Tabs --}}
        <div class="tab-bar">
            <button class="tab-btn active" onclick="switchTab('timeline', this)">Timeline</button>
            <button class="tab-btn" onclick="switchTab('checklist', this)">Checklist <span class="tab-count">{{ $progress['completed'] ?? 0 }}/{{ $progress['total'] ?? 0 }}</span></button>
            <button class="tab-btn" onclick="switchTab('documents', this)">Docs <span class="tab-count">{{ $operation->documents->count() }}</span></button>
            <button class="tab-btn" onclick="switchTab('contracts', this)">Contratos <span class="tab-count">{{ $operation->contracts->count() }}</span></button>
            @if($operation->type === 'renta')
            <button class="tab-btn" onclick="switchTab('poliza', this)">Poliza</button>
            @endif
            <button class="tab-btn" onclick="switchTab('tasks', this)">Tareas <span class="tab-count">{{ $operation->tasks->count() }}</span></button>
            <button class="tab-btn" onclick="switchTab('notes', this)">Notas <span class="tab-count">{{ $operation->comments->count() }}</span></button>
        </div>

        {{-- TAB: Timeline --}}
        <div class="tab-content active" id="tab-timeline">
            @if($timeline->isEmpty())
                <div style="text-align:center; padding:2rem; color:var(--text-muted);">
                    <p style="font-size:2rem; margin-bottom:0.5rem;">&#128221;</p>
                    <p>Sin actividad registrada.</p>
                </div>
            @else
                <div class="timeline">
                    @foreach($timeline as $event)
                    <div class="timeline-item">
                        <div class="timeline-dot" style="background: {{ $event['color'] }};"></div>
                        <div class="timeline-content">
                            <div class="timeline-header">
                                <span class="timeline-type" style="color: {{ $event['color'] }};">{{ $event['type_label'] }}</span>
                                <span class="timeline-date">{{ $event['date']->format('d/m H:i') }}</span>
                            </div>
                            <div class="timeline-body">{!! $event['body'] !!}</div>
                            @if(!empty($event['meta']))
                            <div class="timeline-meta">{!! $event['meta'] !!}</div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- TAB: Checklist --}}
        <div class="tab-content" id="tab-checklist">
            @php
                $itemsByStage = $operation->checklistItems->groupBy('stage');
            @endphp

            @foreach($availableStages as $stageKey => $stageLabel)
                @php
                    $stageIdx = array_search($stageKey, $stageKeys);
                    $stageItems = $itemsByStage->get($stageKey, collect())->sortBy('id');
                    $stageCompleted = $stageItems->where('is_completed', true)->count();
                    $stageTotal = $stageItems->count();
                    $stageColor = \App\Models\Operation::STAGE_COLORS[$stageKey] ?? '#94a3b8';
                    $isPast = $stageIdx < $currentIdx;
                    $isCurrent = $stageIdx === $currentIdx;
                    $isFuture = $stageIdx > $currentIdx;
                @endphp
                <div class="stage-checklist-group">
                    <div class="stage-checklist-header">
                        <div class="stage-label">
                            <span class="stage-dot" style="background: {{ $stageColor }};"></span>
                            {{ $stageLabel }}
                            @if($isPast) <span style="font-size:0.7rem; color:var(--success);">&#10003;</span> @endif
                        </div>
                        <span class="stage-count">
                            @if($stageTotal > 0) {{ $stageCompleted }}/{{ $stageTotal }} @else — @endif
                        </span>
                    </div>
                    @if($stageTotal > 0)
                    <div style="padding-left:0.5rem;">
                        @foreach($stageItems as $item)
                        <div class="checklist-item {{ $item->is_completed ? 'completed' : '' }} {{ $isPast ? 'past' : '' }} {{ $isFuture ? 'locked' : '' }}">
                            @if($isCurrent)
                                <form method="POST" action="{{ route('operations.checklist.toggle', [$operation->id, $item->id]) }}">
                                    @csrf @method('PATCH')
                                    <input type="checkbox" onchange="this.form.submit()" {{ $item->is_completed ? 'checked' : '' }}>
                                </form>
                            @else
                                <input type="checkbox" {{ $item->is_completed || $isPast ? 'checked' : '' }} disabled>
                            @endif
                            <label>{{ $item->template->title ?? $item->notes ?? 'Item' }}</label>
                        </div>
                        @if($item->is_completed && $item->completedByUser)
                        <div class="checklist-meta">{{ $item->completedByUser->name }} &middot; {{ $item->completed_at->format('d/m H:i') }}</div>
                        @endif
                        @endforeach
                    </div>
                    @endif
                </div>
            @endforeach

            @if($operation->checklistItems->isEmpty())
                <div style="text-align:center; padding:2rem; color:var(--text-muted);">Sin checklist configurado.</div>
            @endif
        </div>

        {{-- TAB: Documents --}}
        <div class="tab-content" id="tab-documents">
            <div class="card" style="margin-bottom:1rem;">
                <div class="card-body" style="padding:0.85rem;">
                    <form method="POST" action="{{ route('operations.documents.store', $operation->id) }}" enctype="multipart/form-data">
                        @csrf
                        <div style="display:flex; gap:0.5rem; align-items:flex-end; flex-wrap:wrap;">
                            <div class="form-group" style="flex:1; min-width:120px; margin:0;">
                                <label class="form-label" style="font-size:0.72rem;">Categoria</label>
                                <select name="category" class="form-select" required>
                                    @foreach($documentCategories as $ck => $cl) <option value="{{ $ck }}">{{ $cl }}</option> @endforeach
                                </select>
                            </div>
                            <div class="form-group" style="flex:1; min-width:140px; margin:0;">
                                <label class="form-label" style="font-size:0.72rem;">Etiqueta</label>
                                <input type="text" name="label" class="form-input" required placeholder="Nombre">
                            </div>
                            <div class="form-group" style="flex:1; min-width:140px; margin:0;">
                                <label class="form-label" style="font-size:0.72rem;">Archivo</label>
                                <input type="file" name="file" class="form-input" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm" style="height:38px;">Subir</button>
                        </div>
                    </form>
                </div>
            </div>

            @php $docsByCategory = $operation->documents->groupBy('category'); @endphp
            @foreach($documentCategories as $catKey => $catLabel)
                @php $catDocs = $docsByCategory->get($catKey, collect()); @endphp
                <div style="margin-bottom:0.65rem;">
                    <div style="display:flex; align-items:center; gap:0.4rem; margin-bottom:0.3rem;">
                        @if($catDocs->where('status', 'verified')->count() > 0) <span style="color:var(--success);">&#10003;</span>
                        @elseif($catDocs->count() > 0) <span style="color:#f59e0b;">&#9679;</span>
                        @else <span style="color:var(--border);">&#9675;</span> @endif
                        <span style="font-size:0.8rem; font-weight:600;">{{ $catLabel }}</span>
                        <span style="font-size:0.7rem; color:var(--text-muted);">({{ $catDocs->count() }})</span>
                    </div>
                    @foreach($catDocs as $doc)
                    <div class="doc-item">
                        <div class="doc-icon">&#128196;</div>
                        <div class="doc-info">
                            <div class="doc-name">{{ $doc->label }}</div>
                            <div class="doc-meta">{{ $doc->uploader->name ?? '' }} &middot; {{ $doc->created_at->format('d/m/Y') }}</div>
                        </div>
                        <span class="badge badge-{{ match($doc->status) { 'verified' => 'green', 'rejected' => 'red', 'received' => 'blue', default => 'yellow' } }}">{{ $doc->status_label }}</span>
                        <div class="doc-actions">
                            <a href="{{ route('documents.download', $doc->id) }}" class="btn btn-sm btn-outline" title="Descargar">&#8615;</a>
                            @if($doc->status !== 'verified')
                            <form method="POST" action="{{ route('documents.update-status', $doc->id) }}" style="display:inline;">@csrf @method('PATCH')<input type="hidden" name="status" value="verified"><button type="submit" class="btn btn-sm btn-outline" style="color:var(--success);">&#10003;</button></form>
                            @endif
                            @if($doc->status !== 'rejected')
                            <form method="POST" action="{{ route('documents.update-status', $doc->id) }}" style="display:inline;">@csrf @method('PATCH')<input type="hidden" name="status" value="rejected"><button type="submit" class="btn btn-sm btn-outline" style="color:var(--danger);">&#10007;</button></form>
                            @endif
                            <form method="POST" action="{{ route('documents.destroy', $doc->id) }}" style="display:inline;" onsubmit="return confirm('Eliminar?')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-danger">&#128465;</button></form>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        {{-- TAB: Contracts --}}
        <div class="tab-content" id="tab-contracts">
            <div class="card" style="margin-bottom:1rem;"><div class="card-body" style="padding:0.85rem;">
                @if($contractTemplates->isEmpty())
                    <p style="font-size:0.82rem; color:var(--text-muted);">Sin plantillas. <a href="{{ route('admin.contract-templates.create') }}" style="color:var(--primary);">Crear</a></p>
                @else
                <form method="POST" action="{{ route('operations.contracts.generate', $operation->id) }}">@csrf
                    <div style="display:flex; gap:0.5rem; align-items:flex-end; flex-wrap:wrap;">
                        <div class="form-group" style="flex:1; min-width:140px; margin:0;">
                            <label class="form-label" style="font-size:0.72rem;">Plantilla</label>
                            <select name="contract_template_id" class="form-select" required>
                                @foreach($contractTemplates as $tpl) <option value="{{ $tpl->id }}">{{ $tpl->name }}</option> @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="flex:1; min-width:140px; margin:0;">
                            <label class="form-label" style="font-size:0.72rem;">Titulo</label>
                            <input type="text" name="title" class="form-input" required placeholder="Ej: Contrato {{ $operation->property->title ?? '' }}">
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm" style="height:38px;">Generar</button>
                    </div>
                </form>
                @endif
            </div></div>

            @if($operation->contracts->isEmpty())
                <div style="text-align:center; padding:2rem; color:var(--text-muted);">Sin contratos.</div>
            @else
                @foreach($operation->contracts->sortByDesc('created_at') as $contract)
                <div class="card" style="margin-bottom:0.65rem;">
                    <div class="card-body" style="padding:0.85rem;">
                        <div style="display:flex; align-items:flex-start; gap:0.65rem;">
                            <div style="font-size:1.3rem; flex-shrink:0;">
                                @if($contract->is_signed) &#9989; @elseif($contract->signature_status === 'pending_signature') &#9997; @else &#128196; @endif
                            </div>
                            <div style="flex:1; overflow:hidden;">
                                <div style="font-weight:600; font-size:0.88rem;">{{ $contract->title }}</div>
                                <div style="font-size:0.72rem; color:var(--text-muted); margin-top:0.1rem;">
                                    {{ \App\Models\ContractTemplate::TYPES[$contract->type] ?? ucfirst($contract->type) }} &middot; {{ $contract->created_at->format('d/m/Y') }}
                                </div>
                                @if($contract->is_signed)
                                <div style="font-size:0.72rem; margin-top:0.25rem; padding:0.2rem 0.4rem; background:rgba(16,185,129,0.08); border-radius:4px; display:inline-block;">
                                    Firmado: {{ $contract->signature_data['signer_name'] ?? '' }} &middot; {{ $contract->signed_at->format('d/m/Y H:i') }}
                                </div>
                                @endif
                            </div>
                            <span class="badge badge-{{ match($contract->signature_status) { 'signed' => 'green', 'pending_signature' => 'yellow', default => 'blue' } }}">{{ $contract->signature_status_label }}</span>
                        </div>
                        <div style="display:flex; gap:0.35rem; margin-top:0.6rem; padding-top:0.6rem; border-top:1px solid var(--border); flex-wrap:wrap;">
                            @if($contract->generated_html) <a href="{{ route('contracts.preview', $contract->id) }}" class="btn btn-sm btn-outline" target="_blank">Vista Previa</a> @endif
                            @if($contract->pdf_path) <a href="{{ route('contracts.download', $contract->id) }}" class="btn btn-sm btn-outline">&#8615; PDF</a> @endif
                            @if(!$contract->is_signed)
                                @if($contract->signature_status !== 'pending_signature')
                                <form method="POST" action="{{ route('contracts.send-signature', $contract->id) }}" style="display:inline;">@csrf <button type="submit" class="btn btn-sm btn-outline">Enviar a Firma</button></form>
                                @endif
                                <button type="button" class="btn btn-sm btn-primary" onclick="this.nextElementSibling.style.display=this.nextElementSibling.style.display==='none'?'flex':'none'">Firmar Ahora</button>
                                <div style="display:none; gap:0.4rem; align-items:flex-end; width:100%; margin-top:0.5rem;">
                                    <form method="POST" action="{{ route('contracts.sign', $contract->id) }}" style="display:flex; gap:0.4rem; width:100%; align-items:flex-end;">@csrf
                                        <div class="form-group" style="flex:1; margin:0;"><input type="text" name="signer_name" class="form-input" required placeholder="Nombre firmante"></div>
                                        <div class="form-group" style="flex:1; margin:0;"><input type="email" name="signer_email" class="form-input" required placeholder="Email firmante"></div>
                                        <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Registrar firma digital?')">Confirmar</button>
                                    </form>
                                </div>
                            @endif
                            <form method="POST" action="{{ route('contracts.destroy', $contract->id) }}" style="display:inline; margin-left:auto;" onsubmit="return confirm('Eliminar?')">@csrf @method('DELETE') <button type="submit" class="btn btn-sm btn-danger">Eliminar</button></form>
                        </div>
                    </div>
                </div>
                @endforeach
            @endif
        </div>

        {{-- TAB: Poliza --}}
        @if($operation->type === 'renta')
        <div class="tab-content" id="tab-poliza">
            @php $poliza = $operation->poliza; @endphp
            @if(!$poliza)
                <div class="card"><div class="card-body" style="text-align:center; padding:2rem;">
                    <p style="font-size:1.5rem; margin-bottom:0.5rem;">&#128203;</p>
                    <p style="color:var(--text-muted); margin-bottom:1rem;">Sin poliza juridica.</p>
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('polizaForm').style.display='block'; this.style.display='none';">+ Crear Poliza</button>
                    <div id="polizaForm" style="display:none; text-align:left; margin-top:1rem;">
                        <form method="POST" action="{{ route('operations.poliza.store', $operation->id) }}">@csrf
                            <div class="form-grid">
                                <div class="form-group"><label class="form-label">Compania</label><input type="text" name="insurance_company" class="form-input" placeholder="Ej: Juridica Integral"></div>
                                <div class="form-group"><label class="form-label">No. Poliza</label><input type="text" name="policy_number" class="form-input"></div>
                                <div class="form-group"><label class="form-label">Costo</label><input type="number" name="cost" class="form-input" step="0.01" min="0"></div>
                                <div class="form-group"><label class="form-label">Moneda</label><select name="currency" class="form-select"><option value="MXN">MXN</option><option value="USD">USD</option></select></div>
                                <div class="form-group full-width"><label class="form-label">Notas</label><textarea name="notes" class="form-textarea" rows="2"></textarea></div>
                            </div>
                            <div style="display:flex; gap:0.5rem; justify-content:flex-end; margin-top:0.5rem;">
                                <button type="button" class="btn btn-outline" onclick="document.getElementById('polizaForm').style.display='none';">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Crear</button>
                            </div>
                        </form>
                    </div>
                </div></div>
            @else
                <div class="card" style="margin-bottom:1rem;">
                    <div class="card-header"><h3>Poliza Juridica</h3><span class="badge" style="background:{{ $poliza->status_color }}20; color:{{ $poliza->status_color }};">{{ $poliza->status_label }}</span></div>
                    <div class="card-body">
                        @php $polizaStatuses = array_keys(\App\Models\PolizaJuridica::STATUSES); $cpIdx = array_search($poliza->status, $polizaStatuses); @endphp
                        <div style="display:flex; gap:3px; margin-bottom:1rem;">
                            @foreach($polizaStatuses as $pi => $ps)
                                @if(!in_array($ps, ['rejected','expired']))
                                <div style="flex:1; height:5px; border-radius:3px; background:{{ $pi <= $cpIdx && !in_array($poliza->status, ['rejected','expired']) ? $poliza->status_color : 'var(--border)' }};"></div>
                                @endif
                            @endforeach
                        </div>
                        <div class="form-grid" style="font-size:0.82rem;">
                            @if($poliza->insurance_company) <div class="info-row"><span class="lbl">Compania</span><span class="val">{{ $poliza->insurance_company }}</span></div> @endif
                            @if($poliza->policy_number) <div class="info-row"><span class="lbl">No. Poliza</span><span class="val">{{ $poliza->policy_number }}</span></div> @endif
                            @if($poliza->cost) <div class="info-row"><span class="lbl">Costo</span><span class="val">{{ $poliza->currency }} ${{ number_format($poliza->cost, 0) }}</span></div> @endif
                            @if($poliza->coverage_start) <div class="info-row"><span class="lbl">Cobertura</span><span class="val">{{ $poliza->coverage_start->format('d/m/Y') }} — {{ $poliza->coverage_end?->format('d/m/Y') ?? '—' }}</span></div> @endif
                        </div>
                        <div style="display:flex; gap:0.4rem; flex-wrap:wrap; margin-top:0.75rem; padding-top:0.65rem; border-top:1px solid var(--border);">
                            @php $transitions = match($poliza->status) { 'pending' => ['documents_submitted'], 'documents_submitted' => ['in_review','rejected'], 'in_review' => ['approved','rejected'], 'rejected' => ['pending'], 'approved' => ['expired'], default => [] }; @endphp
                            @foreach($transitions as $ns)
                            <form method="POST" action="{{ route('polizas.update-status', $poliza->id) }}" style="display:inline;">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $ns }}">
                                @if($ns === 'rejected') <input type="hidden" name="rejection_reason" value="" id="rr-{{ $ns }}"> @endif
                                <button type="submit" class="btn btn-sm {{ $ns === 'approved' ? 'btn-primary' : ($ns === 'rejected' ? 'btn-danger' : 'btn-outline') }}"
                                    @if($ns === 'rejected') onclick="var r=prompt('Razon?'); if(!r){event.preventDefault();return;} document.getElementById('rr-{{ $ns }}').value=r;" @endif>{{ \App\Models\PolizaJuridica::STATUSES[$ns] }}</button>
                            </form>
                            @endforeach
                        </div>
                    </div>
                </div>
                {{-- Poliza Events --}}
                <div class="card"><div class="card-header"><h3>Historial</h3></div><div class="card-body">
                    <form method="POST" action="{{ route('polizas.events.store', $poliza->id) }}" style="display:flex; gap:0.4rem; margin-bottom:1rem;">@csrf
                        <input type="text" name="description" class="form-input" placeholder="Nota..." required style="flex:1;">
                        <button type="submit" class="btn btn-primary btn-sm">Agregar</button>
                    </form>
                    @if($poliza->events->isEmpty())
                        <p style="text-align:center; color:var(--text-muted); font-size:0.82rem; padding:1rem;">Sin eventos.</p>
                    @else
                        <div class="timeline">
                            @foreach($poliza->events as $evt)
                            <div class="timeline-item">
                                <div class="timeline-dot" style="background:{{ match($evt->event_type) { 'status_change' => '#8b5cf6', 'note' => '#3b82f6', default => '#10b981' } }};"></div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <span class="timeline-type" style="color:{{ match($evt->event_type) { 'status_change' => '#8b5cf6', 'note' => '#3b82f6', default => '#10b981' } }};">{{ match($evt->event_type) { 'status_change' => 'Cambio', 'note' => 'Nota', default => ucfirst($evt->event_type) } }}</span>
                                        <span class="timeline-date">{{ $evt->created_at->format('d/m H:i') }}</span>
                                    </div>
                                    <div class="timeline-body">{{ $evt->description }}</div>
                                    <div class="timeline-meta">{{ $evt->user->name ?? '' }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div></div>
            @endif
        </div>
        @endif

        {{-- TAB: Tasks --}}
        <div class="tab-content" id="tab-tasks">
            <div class="card" style="margin-bottom:1rem;"><div class="card-body" style="padding:0.85rem;">
                <form method="POST" action="{{ route('tasks.store') }}">@csrf
                    <input type="hidden" name="operation_id" value="{{ $operation->id }}">
                    <input type="hidden" name="status" value="pending">
                    <div style="display:flex; gap:0.4rem; align-items:flex-end; flex-wrap:wrap;">
                        <div class="form-group" style="flex:2; min-width:180px; margin:0;">
                            <label class="form-label" style="font-size:0.72rem;">Tarea</label>
                            <input type="text" name="title" class="form-input" required placeholder="Nombre de la tarea">
                        </div>
                        <div class="form-group" style="flex:0; min-width:100px; margin:0;">
                            <label class="form-label" style="font-size:0.72rem;">Prioridad</label>
                            <select name="priority" class="form-select"><option value="low">Baja</option><option value="medium" selected>Media</option><option value="high">Alta</option><option value="urgent">Urgente</option></select>
                        </div>
                        <div class="form-group" style="flex:0; min-width:130px; margin:0;">
                            <label class="form-label" style="font-size:0.72rem;">Vence</label>
                            <input type="date" name="due_date" class="form-input">
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm" style="height:38px;">Agregar</button>
                    </div>
                </form>
            </div></div>

            @if($operation->tasks->isEmpty())
                <div style="text-align:center; padding:2rem; color:var(--text-muted);">Sin tareas.</div>
            @else
                @foreach($operation->tasks->sortByDesc('created_at') as $task)
                <div class="card" style="margin-bottom:0.4rem;">
                    <div class="card-body" style="padding:0.6rem 0.85rem; display:flex; align-items:center; gap:0.6rem;">
                        <form method="POST" action="{{ route('tasks.toggleComplete', $task->id) }}" style="display:flex;">@csrf @method('PATCH')
                            <input type="checkbox" onchange="this.form.submit()" {{ $task->status === 'completed' ? 'checked' : '' }} style="width:16px; height:16px; accent-color:var(--primary); cursor:pointer;">
                        </form>
                        <div style="flex:1;">
                            <div style="font-size:0.85rem; font-weight:500; {{ $task->status === 'completed' ? 'text-decoration:line-through; opacity:0.5;' : '' }}">{{ $task->title }}</div>
                            <div style="font-size:0.68rem; color:var(--text-muted);">
                                @if($task->due_date)
                                    Vence: {{ $task->due_date->format('d/m/Y') }}
                                    @if($task->status !== 'completed' && $task->due_date->isPast()) <span class="badge badge-red" style="font-size:0.6rem;">Vencida</span> @endif
                                @endif
                            </div>
                        </div>
                        <span class="badge badge-{{ match($task->priority) { 'urgent' => 'red', 'high' => 'yellow', 'low' => 'green', default => 'blue' } }}" style="font-size:0.62rem;">{{ match($task->priority) { 'urgent' => 'Urgente', 'high' => 'Alta', 'low' => 'Baja', default => 'Media' } }}</span>
                    </div>
                </div>
                @endforeach
            @endif
        </div>

        {{-- TAB: Notes --}}
        <div class="tab-content" id="tab-notes">
            <div class="card" style="margin-bottom:1rem;"><div class="card-body" style="padding:0.85rem;">
                <form method="POST" action="{{ route('operations.comments.store', $operation->id) }}">@csrf
                    <div class="form-group" style="margin-bottom:0.4rem; position:relative;">
                        <textarea name="body" class="form-textarea mention-input" rows="3" required placeholder="Escribe una nota... @ para mencionar"></textarea>
                        <div class="mention-dropdown" style="display:none;"></div>
                    </div>
                    <div style="display:flex; justify-content:flex-end;">
                        <button type="submit" class="btn btn-primary btn-sm">Agregar Nota</button>
                    </div>
                </form>
            </div></div>

            @if($operation->comments->isEmpty())
                <div style="text-align:center; padding:2rem; color:var(--text-muted);">Sin notas.</div>
            @else
                @foreach($operation->comments as $comment)
                <div class="card" style="margin-bottom:0.4rem;">
                    <div class="card-body" style="padding:0.6rem 0.85rem;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.2rem;">
                            <span style="font-size:0.8rem; font-weight:600;">{{ $comment->user->name ?? 'Usuario' }}</span>
                            <span style="font-size:0.68rem; color:var(--text-muted);">{{ $comment->created_at->format('d/m H:i') }}</span>
                        </div>
                        <div style="font-size:0.85rem; line-height:1.5; white-space:pre-line;">{!! \App\Helpers\MentionHelper::render($comment->body) !!}</div>
                    </div>
                </div>
                @endforeach
            @endif
        </div>
    </div>

    {{-- ===== RIGHT: Info Panel ===== --}}
    <div class="info-panel">
        {{-- Current Stage Checklist --}}
        @if($clTotal > 0)
        <div class="card" style="margin-bottom:0.75rem;">
            <div class="card-body" style="padding:0.85rem;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.4rem;">
                    <span style="font-size:0.75rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em;">Checklist: {{ $operation->stage_label }}</span>
                    <span style="font-size:0.72rem; color:var(--text-muted);">{{ $clCompleted }}/{{ $clTotal }}</span>
                </div>
                @foreach($currentItems as $item)
                <div class="checklist-item {{ $item->is_completed ? 'completed' : '' }}">
                    <form method="POST" action="{{ route('operations.checklist.toggle', [$operation->id, $item->id]) }}">@csrf @method('PATCH')
                        <input type="checkbox" onchange="this.form.submit()" {{ $item->is_completed ? 'checked' : '' }}>
                    </form>
                    <label>{{ $item->template->title ?? $item->notes ?? 'Item' }}</label>
                </div>
                @endforeach
                <div style="height:4px; background:var(--border); border-radius:2px; margin-top:0.5rem; overflow:hidden;">
                    <div style="height:100%; width:{{ $clPct }}%; background:var(--success); border-radius:2px;"></div>
                </div>
            </div>
        </div>
        @endif

        {{-- Stage Change --}}
        <div class="card" style="margin-bottom:0.75rem;">
            <div class="card-body" style="padding:0.85rem;">
                <div class="info-label">Cambiar Etapa</div>
                <form method="POST" action="{{ route('operations.update-stage', $operation->id) }}" class="stage-change-row">
                    @csrf @method('PATCH')
                    <select name="stage" class="form-select" style="font-size:0.8rem; flex:1;">
                        @foreach($availableStages as $sk => $sl) <option value="{{ $sk }}" {{ $operation->stage === $sk ? 'selected' : '' }}>{{ $sl }}</option> @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary">Ir</button>
                </form>
            </div>
        </div>

        {{-- Operation Details --}}
        <div class="card" style="margin-bottom:0.75rem;">
            <div class="card-body" style="padding:0.85rem;">
                <div class="info-section" style="padding-top:0;">
                    <div class="info-label">Detalles</div>
                    @if($operation->type === 'captacion' && $operation->target_type)
                    <div class="info-row"><span class="lbl">Destino</span><span class="val"><span class="badge badge-{{ $operation->target_type === 'venta' ? 'blue' : '' }}" style="{{ $operation->target_type === 'renta' ? 'background:#EBF5FF;color:#2563A0;' : '' }}">{{ ucfirst($operation->target_type) }}</span></span></div>
                    @endif
                    @if($operation->sourceOperation)
                    <div class="info-row"><span class="lbl">Origen</span><span class="val"><a href="{{ route('operations.show', $operation->source_operation_id) }}">Captacion #{{ $operation->source_operation_id }}</a></span></div>
                    @endif
                    @php $spawned = $operation->spawnedOperations ?? collect(); @endphp
                    @if($spawned->count() > 0)
                    <div class="info-row"><span class="lbl">Generada</span><span class="val">@foreach($spawned as $sp)<a href="{{ route('operations.show', $sp->id) }}">{{ ucfirst($sp->type) }} #{{ $sp->id }}</a>{{ !$loop->last ? ', ' : '' }}@endforeach</span></div>
                    @endif
                    @if($operation->broker)
                    <div class="info-row"><span class="lbl">Broker</span><span class="val">{{ $operation->broker->name }}</span></div>
                    @endif
                    @if($operation->user)
                    <div class="info-row"><span class="lbl">Asignado</span><span class="val">{{ $operation->user->name }}</span></div>
                    @endif
                    @if($operation->amount)
                    <div class="info-row"><span class="lbl">Monto</span><span class="val" style="font-weight:700;">{{ $operation->currency ?? 'MXN' }} ${{ number_format($operation->amount, 0) }}</span></div>
                    @endif
                    @if($operation->monthly_rent)
                    <div class="info-row"><span class="lbl">Renta</span><span class="val" style="font-weight:700;">{{ $operation->currency ?? 'MXN' }} ${{ number_format($operation->monthly_rent, 0) }}/mes</span></div>
                    @endif
                    @if($operation->deposit_amount)
                    <div class="info-row"><span class="lbl">Deposito</span><span class="val">${{ number_format($operation->deposit_amount, 0) }}</span></div>
                    @endif
                    @if($operation->commission_percentage || $operation->commission_amount)
                    <div class="info-row"><span class="lbl">Comision</span><span class="val">@if($operation->commission_percentage){{ $operation->commission_percentage }}%@endif @if($operation->commission_amount) ${{ number_format($operation->commission_amount, 0) }}@endif</span></div>
                    @endif
                    @if($operation->guarantee_type)
                    <div class="info-row"><span class="lbl">Garantia</span><span class="val">{{ $operation->guarantee_type_label }}</span></div>
                    @endif
                    @if($operation->expected_close_date)
                    <div class="info-row"><span class="lbl">Cierre</span><span class="val">{{ $operation->expected_close_date->format('d/m/Y') }}</span></div>
                    @endif
                    @if($operation->lease_end_date)
                    <div class="info-row"><span class="lbl">Fin Contrato</span><span class="val">{{ $operation->lease_end_date->format('d/m/Y') }}
                        @if($operation->is_expired) <span class="badge badge-red" style="font-size:0.6rem;">Vencido</span>
                        @elseif($operation->days_until_expiration !== null && $operation->days_until_expiration <= 30) <span class="badge badge-yellow" style="font-size:0.6rem;">{{ $operation->days_until_expiration }}d</span>
                        @endif</span></div>
                    @endif
                    <div class="info-row"><span class="lbl">Creado</span><span class="val">{{ $operation->created_at->format('d/m/Y') }}</span></div>
                </div>

                @if($operation->notes)
                <div class="info-section">
                    <div class="info-label">Notas</div>
                    <div style="font-size:0.82rem; line-height:1.5;">{!! \App\Helpers\MentionHelper::render($operation->notes) !!}</div>
                </div>
                @endif

                <div class="info-section">
                    <div class="info-stat-grid">
                        <div><div class="info-stat-val">{{ $operation->documents->count() }}</div><div class="info-stat-lbl">Docs</div></div>
                        <div><div class="info-stat-val">{{ $operation->tasks->count() }}</div><div class="info-stat-lbl">Tareas</div></div>
                        <div><div class="info-stat-val">{{ $operation->contracts->count() }}</div><div class="info-stat-lbl">Contratos</div></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Delete --}}
        <div class="card">
            <div class="card-body" style="padding:0.85rem;">
                <form method="POST" action="{{ route('operations.destroy', $operation) }}" onsubmit="return confirm('Eliminar esta operacion?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" style="width:100%; justify-content:center;">Eliminar Operacion</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function switchTab(name, btn) {
    document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
    document.querySelectorAll('.tab-content').forEach(function(c) { c.classList.remove('active'); });
    document.getElementById('tab-' + name).classList.add('active');
    btn.classList.add('active');
}

// ===== @MENTION AUTOCOMPLETE =====
(function() {
    var searchUrl = '{{ route("api.users.search") }}';
    var debounceTimer = null;

    document.querySelectorAll('.mention-input').forEach(function(textarea) {
        var dropdown = textarea.parentElement.querySelector('.mention-dropdown');
        if (!dropdown) return;
        var selectedIndex = -1;
        var items = [];

        textarea.addEventListener('input', function() {
            var mention = getMentionQuery(textarea);
            if (mention !== null && mention.length >= 1) {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function() { searchUsers(mention, dropdown, textarea); }, 200);
            } else { hideDropdown(dropdown); }
        });

        textarea.addEventListener('keydown', function(e) {
            if (dropdown.style.display === 'none') return;
            if (e.key === 'ArrowDown') { e.preventDefault(); selectedIndex = Math.min(selectedIndex + 1, items.length - 1); highlightItem(dropdown, selectedIndex); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); selectedIndex = Math.max(selectedIndex - 1, 0); highlightItem(dropdown, selectedIndex); }
            else if (e.key === 'Enter' && selectedIndex >= 0) { e.preventDefault(); insertMention(textarea, items[selectedIndex], dropdown); }
            else if (e.key === 'Escape') { hideDropdown(dropdown); }
        });

        textarea.addEventListener('blur', function() { setTimeout(function() { hideDropdown(dropdown); }, 200); });

        function searchUsers(query, dd, ta) {
            fetch(searchUrl + '?q=' + encodeURIComponent(query), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.json(); })
            .then(function(users) {
                items = users; selectedIndex = -1;
                if (!users.length) { hideDropdown(dd); return; }
                var html = '';
                users.forEach(function(u, i) {
                    var av = u.avatar ? '<img src="'+u.avatar+'">' : u.initial;
                    html += '<div class="mention-item" data-index="'+i+'" onmousedown="event.preventDefault();">'
                        + '<div class="m-avatar">'+av+'</div>'
                        + '<div><div class="m-name">'+escHtml(u.name)+'</div>'
                        + (u.title ? '<div class="m-title">'+escHtml(u.title)+'</div>' : '')
                        + '</div></div>';
                });
                dd.innerHTML = html; dd.style.display = 'block';
                dd.querySelectorAll('.mention-item').forEach(function(el) {
                    el.addEventListener('mousedown', function(ev) { ev.preventDefault(); insertMention(ta, items[parseInt(el.dataset.index)], dd); });
                });
            });
        }
    });

    function getMentionQuery(ta) {
        var before = ta.value.substring(0, ta.selectionStart);
        var m = before.match(/@([A-Za-z\u00C0-\u00FF\s]*)$/);
        return m ? m[1].trim() : null;
    }
    function insertMention(ta, user, dd) {
        var before = ta.value.substring(0, ta.selectionStart);
        var after = ta.value.substring(ta.selectionStart);
        var at = before.lastIndexOf('@');
        ta.value = before.substring(0, at) + '@' + user.name + ' ' + after;
        var np = at + user.name.length + 2;
        ta.setSelectionRange(np, np); ta.focus(); hideDropdown(dd);
    }
    function hideDropdown(dd) { dd.style.display = 'none'; }
    function highlightItem(dd, idx) { dd.querySelectorAll('.mention-item').forEach(function(el, i) { el.classList.toggle('selected', i === idx); }); }
    function escHtml(t) { var d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
})();
</script>
@endsection
