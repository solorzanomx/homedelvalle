@extends('layouts.app-sidebar')
@section('title', 'Dashboard')

@section('styles')
<style>
/* ===== GREETING ===== */
.greeting-bar {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 1.5rem;
}
.greeting h2 {
    font-size: 1.35rem;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 0.15rem;
}
.greeting p {
    font-size: 0.85rem;
    color: var(--text-muted);
}
.greeting-date {
    font-size: 0.82rem;
    color: var(--text-muted);
    text-align: right;
}

/* ===== ALERT BANNER ===== */
.urgent-banner {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.85rem 1.15rem;
    background: linear-gradient(135deg, rgba(239,68,68,0.08), rgba(239,68,68,0.04));
    border: 1px solid rgba(239,68,68,0.2);
    border-radius: var(--radius);
    margin-bottom: 1.25rem;
    cursor: pointer;
    transition: all 0.15s;
}
.urgent-banner:hover { background: rgba(239,68,68,0.1); }
.urgent-dot {
    width: 10px; height: 10px;
    border-radius: 50%;
    background: var(--danger);
    flex-shrink: 0;
    animation: urgentPulse 2s infinite;
}
@keyframes urgentPulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(239,68,68,0.4); }
    50% { box-shadow: 0 0 0 6px rgba(239,68,68,0); }
}
.urgent-text { flex: 1; font-size: 0.88rem; font-weight: 600; color: #991b1b; }
.urgent-action {
    font-size: 0.78rem; font-weight: 600; color: var(--danger);
    white-space: nowrap;
}

/* ===== QUICK STATS (compact) ===== */
.quick-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}
@media (max-width: 768px) { .quick-stats { grid-template-columns: repeat(2, 1fr); } }
.qs-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 0.85rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.65rem;
    transition: border-color 0.15s;
}
.qs-card:hover { border-color: var(--primary); }
.qs-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    flex-shrink: 0;
}
.qs-value { font-size: 1.25rem; font-weight: 700; color: var(--text); line-height: 1.1; }
.qs-label { font-size: 0.72rem; color: var(--text-muted); margin-top: 0.05rem; }

/* ===== MAIN GRID ===== */
.dash-grid {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 1.25rem;
    align-items: start;
}
@media (max-width: 1024px) { .dash-grid { grid-template-columns: 1fr; } }

/* ===== TASK LIST ===== */
.task-list { display: flex; flex-direction: column; gap: 0; }
.task-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.85rem 1rem;
    border-bottom: 1px solid var(--border);
    transition: background 0.1s;
}
.task-item:last-child { border-bottom: none; }
.task-item:hover { background: var(--bg); }
.task-item.overdue { background: rgba(239,68,68,0.03); }

.task-check {
    width: 22px; height: 22px;
    border-radius: 50%;
    border: 2px solid var(--border);
    flex-shrink: 0;
    cursor: pointer;
    transition: all 0.15s;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0;
    color: #fff;
}
.task-check:hover {
    border-color: var(--success);
    background: rgba(16,185,129,0.1);
}
.task-check.checked {
    border-color: var(--success);
    background: var(--success);
    font-size: 0.6rem;
}

.task-body { flex: 1; min-width: 0; }
.task-title {
    font-size: 0.85rem; font-weight: 500; color: var(--text);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.task-meta {
    font-size: 0.72rem; color: var(--text-muted); margin-top: 0.1rem;
    display: flex; gap: 0.5rem; align-items: center;
}
.task-meta .overdue-tag {
    color: var(--danger); font-weight: 600;
}

.task-actions {
    display: flex; gap: 0.25rem; flex-shrink: 0;
}
.task-action-btn {
    width: 32px; height: 32px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--card);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    transition: all 0.15s;
    text-decoration: none;
    color: var(--text-muted);
}
.task-action-btn:hover { border-color: var(--primary); color: var(--primary); }
.task-action-btn.whatsapp:hover { border-color: #25d366; color: #25d366; }
.task-action-btn.phone:hover { border-color: #3b82f6; color: #3b82f6; }

/* ===== PIPELINE MINI ===== */
.pipeline-mini { display: flex; flex-direction: column; gap: 0.35rem; }
.pipeline-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0;
}
.pipeline-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}
.pipeline-stage {
    font-size: 0.82rem; color: var(--text);
    flex: 1;
}
.pipeline-count {
    font-size: 0.82rem; font-weight: 700; color: var(--text);
    background: var(--bg);
    padding: 0.1rem 0.5rem;
    border-radius: 10px;
    min-width: 28px;
    text-align: center;
}

/* ===== ACTIVITY FEED ===== */
.activity-feed { display: flex; flex-direction: column; gap: 0; }
.activity-item {
    display: flex;
    gap: 0.6rem;
    padding: 0.65rem 0;
    border-bottom: 1px solid var(--border);
}
.activity-item:last-child { border-bottom: none; }
.activity-icon {
    width: 28px; height: 28px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.72rem; flex-shrink: 0;
    background: var(--bg); color: var(--text-muted);
}
.activity-text {
    font-size: 0.8rem; color: var(--text); line-height: 1.35;
}
.activity-text strong { font-weight: 600; }
.activity-time { font-size: 0.7rem; color: var(--text-muted); margin-top: 0.1rem; }

/* ===== SECTION HEADER ===== */
.section-head {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 0.75rem;
}
.section-head h3 { font-size: 0.92rem; font-weight: 600; color: var(--text); }
.section-head a { font-size: 0.78rem; color: var(--primary); text-decoration: none; font-weight: 500; }
.section-head a:hover { text-decoration: underline; }

/* ===== EMPTY STATE ===== */
.empty-state-sm {
    text-align: center;
    padding: 1.5rem 1rem;
    color: var(--text-muted);
}
.empty-state-sm .icon { font-size: 1.5rem; opacity: 0.3; margin-bottom: 0.25rem; }
.empty-state-sm p { font-size: 0.82rem; }

/* ===== STALE CLIENT ROW ===== */
.stale-list { display: flex; flex-direction: column; gap: 0; }
.stale-item {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.6rem 0.85rem;
    border-bottom: 1px solid var(--border);
    transition: background 0.1s;
}
.stale-item:last-child { border-bottom: none; }
.stale-item:hover { background: var(--bg); }
.stale-avatar {
    width: 34px; height: 34px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark, #764ba2));
    color: #fff;
    font-size: 0.72rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.stale-info { flex: 1; min-width: 0; }
.stale-name { font-size: 0.85rem; font-weight: 600; color: var(--text); }
.stale-detail { font-size: 0.7rem; color: var(--text-muted); }
</style>
@endsection

@section('content')
{{-- ===== GREETING ===== --}}
<div class="greeting-bar">
    <div class="greeting">
        @php
            $hour = now()->hour;
            $saludo = $hour < 12 ? 'Buenos dias' : ($hour < 19 ? 'Buenas tardes' : 'Buenas noches');
        @endphp
        <h2>{{ $saludo }}, {{ $user->name }}</h2>
        @php
            $pendingCount = $todayTasks->count() + $staleClients->count();
        @endphp
        @if($pendingCount > 0)
            <p>Tienes {{ $pendingCount }} {{ $pendingCount === 1 ? 'pendiente' : 'pendientes' }} hoy</p>
        @else
            <p>Todo al dia — buen trabajo</p>
        @endif
    </div>
    <div class="greeting-date">{{ now()->translatedFormat('l, d M Y') }}</div>
</div>

{{-- ===== URGENT BANNER ===== --}}
@if($staleClients->count() > 0)
<a href="{{ route('clients.index') }}" style="text-decoration:none;">
    <div class="urgent-banner">
        <span class="urgent-dot"></span>
        <span class="urgent-text">{{ $staleClients->count() }} {{ $staleClients->count() === 1 ? 'lead sin contactar' : 'leads sin contactar' }} (mas de 24h)</span>
        <span class="urgent-action">Ver leads &rarr;</span>
    </div>
</a>
@endif

{{-- ===== QUICK STATS ===== --}}
<div class="quick-stats">
    <div class="qs-card">
        <div class="qs-icon" style="background:rgba(59,130,246,0.1); color:#3b82f6;">&#9823;</div>
        <div>
            <div class="qs-value">{{ $newClientsWeek }}</div>
            <div class="qs-label">Leads esta semana</div>
        </div>
    </div>
    <div class="qs-card">
        <div class="qs-icon" style="background:rgba(139,92,246,0.1); color:#8b5cf6;">&#9881;</div>
        <div>
            <div class="qs-value">{{ $activeOperations }}</div>
            <div class="qs-label">Operaciones activas</div>
        </div>
    </div>
    <div class="qs-card">
        <div class="qs-icon" style="background:rgba(20,184,166,0.1); color:#14b8a6;">&#9733;</div>
        <div>
            <div class="qs-value">{{ $captacionesActive }}</div>
            <div class="qs-label">Captaciones activas</div>
        </div>
    </div>
    <div class="qs-card">
        <div class="qs-icon" style="background:rgba(16,185,129,0.1); color:#10b981;">&#10003;</div>
        <div>
            <div class="qs-value">{{ $operationsClosedMonth }}</div>
            <div class="qs-label">Cerradas este mes</div>
        </div>
    </div>
</div>

{{-- ===== MAIN LAYOUT ===== --}}
<div class="dash-grid">
    {{-- LEFT COLUMN --}}
    <div>
        {{-- TODAY'S FOLLOW-UPS --}}
        <div class="card">
            <div class="card-body" style="padding:0;">
                <div class="section-head" style="padding:1rem 1rem 0;">
                    <h3>Seguimientos de hoy @if($overdueCount > 0)<span style="color:var(--danger); font-size:0.75rem; font-weight:500;"> &middot; {{ $overdueCount }} vencidos</span>@endif</h3>
                    <a href="{{ route('tasks.index') }}">Ver todos</a>
                </div>

                @if($todayTasks->count() > 0)
                <div class="task-list" style="padding:0.25rem 0;">
                    @foreach($todayTasks as $task)
                    @php
                        $isOverdue = $task->due_date && $task->due_date->lt(today());
                        $client = $task->client;
                        $phone = $client?->whatsapp ?: $client?->phone;
                    @endphp
                    <div class="task-item {{ $isOverdue ? 'overdue' : '' }}">
                        <form method="POST" action="{{ route('tasks.toggleComplete', $task) }}" style="display:flex;">
                            @csrf @method('PATCH')
                            <button type="submit" class="task-check" title="Completar">&#10003;</button>
                        </form>
                        <div class="task-body">
                            <div class="task-title">{{ $task->title }}</div>
                            <div class="task-meta">
                                @if($client)
                                    <span>{{ $client->name }}</span>
                                @endif
                                @if($task->due_date)
                                    @if($isOverdue)
                                        <span class="overdue-tag">Vencido {{ $task->due_date->diffForHumans() }}</span>
                                    @else
                                        <span>{{ $task->due_date->format('h:i A') }}</span>
                                    @endif
                                @endif
                                @if($task->priority === 'high' || $task->priority === 'urgent')
                                    <span style="color:var(--danger); font-weight:600;">&#9679; {{ ucfirst($task->priority) }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="task-actions">
                            @if($phone)
                                <a href="https://wa.me/52{{ preg_replace('/\D/', '', $phone) }}" target="_blank" class="task-action-btn whatsapp" title="WhatsApp">&#128172;</a>
                                <a href="tel:{{ $phone }}" class="task-action-btn phone" title="Llamar">&#128222;</a>
                            @endif
                            @if($client)
                                <a href="{{ route('clients.show', $client) }}" class="task-action-btn" title="Ver lead">&#8594;</a>
                            @elseif($task->operation)
                                <a href="{{ route('operations.show', $task->operation) }}" class="task-action-btn" title="Ver operacion">&#8594;</a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="empty-state-sm">
                    <div class="icon">&#10003;</div>
                    <p>Sin seguimientos pendientes hoy</p>
                </div>
                @endif
            </div>
        </div>

        {{-- UPCOMING (Next 7 days) --}}
        @if($upcomingTasks->count() > 0)
        <div class="card" style="margin-top:1rem;">
            <div class="card-body" style="padding:0;">
                <div class="section-head" style="padding:1rem 1rem 0;">
                    <h3>Proximos 7 dias</h3>
                </div>
                <div class="task-list" style="padding:0.25rem 0;">
                    @foreach($upcomingTasks as $task)
                    @php $client = $task->client; @endphp
                    <div class="task-item">
                        <div style="width:22px; text-align:center; flex-shrink:0;">
                            <span style="font-size:0.72rem; color:var(--text-muted);">{{ $task->due_date->format('d') }}</span>
                        </div>
                        <div class="task-body">
                            <div class="task-title">{{ $task->title }}</div>
                            <div class="task-meta">
                                @if($client) <span>{{ $client->name }}</span> @endif
                                <span>{{ $task->due_date->translatedFormat('D d M') }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- STALE LEADS (expanded) --}}
        @if($staleClients->count() > 0)
        <div class="card" style="margin-top:1rem;">
            <div class="card-body" style="padding:0;">
                <div class="section-head" style="padding:1rem 1rem 0;">
                    <h3 style="color:var(--danger);">Leads sin contactar</h3>
                    <a href="{{ route('clients.index') }}">Ver todos</a>
                </div>
                <div class="stale-list" style="padding:0.25rem 0;">
                    @foreach($staleClients as $client)
                    @php $phone = $client->whatsapp ?: $client->phone; @endphp
                    <div class="stale-item">
                        <div class="stale-avatar">{{ mb_substr($client->name, 0, 1) }}</div>
                        <div class="stale-info">
                            <div class="stale-name">{{ $client->name }}</div>
                            <div class="stale-detail">
                                {{ $client->city ?: 'Sin ciudad' }}
                                @if($client->interest_types && is_array($client->interest_types))
                                    &middot; {{ implode(', ', array_map('ucfirst', $client->interest_types)) }}
                                @endif
                                &middot; {{ $client->created_at->diffForHumans() }}
                            </div>
                        </div>
                        <div class="task-actions">
                            @if($phone)
                                <a href="https://wa.me/52{{ preg_replace('/\D/', '', $phone) }}" target="_blank" class="task-action-btn whatsapp" title="WhatsApp">&#128172;</a>
                                <a href="tel:{{ $phone }}" class="task-action-btn phone" title="Llamar">&#128222;</a>
                            @endif
                            <a href="{{ route('clients.show', $client) }}" class="task-action-btn" title="Ver lead">&#8594;</a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- RIGHT COLUMN --}}
    <div>
        {{-- PIPELINE SUMMARY --}}
        <div class="card">
            <div class="card-body">
                <div class="section-head" style="margin-bottom:0.5rem;">
                    <h3>Pipeline Activo</h3>
                    <a href="{{ route('operations.index') }}">Ver &rarr;</a>
                </div>

                @if($pipelineSummary->count() > 0)
                    @foreach($pipelineSummary as $type => $stages)
                    <div style="margin-bottom:0.75rem;">
                        <div style="font-size:0.72rem; font-weight:600; text-transform:uppercase; letter-spacing:0.04em; color:var(--text-muted); margin-bottom:0.3rem;">
                            {{ $type === 'venta' ? 'Ventas' : ($type === 'renta' ? 'Rentas' : 'Captaciones') }}
                        </div>
                        <div class="pipeline-mini">
                            @foreach($stages as $s)
                            @php
                                $stageColors = \App\Models\Operation::STAGE_COLORS;
                                $stageLabels = ['lead'=>'Lead','contacto'=>'Contacto','visita'=>'Visita','exclusiva'=>'Exclusiva','publicacion'=>'Publicacion','busqueda'=>'Busqueda','investigacion'=>'Investigacion','contrato'=>'Contrato','entrega'=>'Entrega','cierre'=>'Cierre','activo'=>'Activo','renovacion'=>'Renovacion','revision_docs'=>'Rev. Docs','avaluo'=>'Avaluo','mejoras'=>'Mejoras','fotos_video'=>'Fotos/Video','carpeta_lista'=>'Carpeta Lista'];
                            @endphp
                            <div class="pipeline-row">
                                <span class="pipeline-dot" style="background:{{ $stageColors[$s->stage] ?? '#94a3b8' }};"></span>
                                <span class="pipeline-stage">{{ $stageLabels[$s->stage] ?? ucfirst($s->stage) }}</span>
                                <span class="pipeline-count">{{ $s->total }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="empty-state-sm">
                        <div class="icon">&#9881;</div>
                        <p>Sin operaciones activas</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- RECENT ACTIVITY --}}
        <div class="card" style="margin-top:0.75rem;">
            <div class="card-body">
                <div class="section-head" style="margin-bottom:0.5rem;">
                    <h3>Actividad Reciente</h3>
                </div>

                @if($recentInteractions->count() > 0)
                <div class="activity-feed">
                    @foreach($recentInteractions as $interaction)
                    <div class="activity-item">
                        <div class="activity-icon" style="
                            @if($interaction->type === 'call') background:rgba(59,130,246,0.1); color:#3b82f6;
                            @elseif($interaction->type === 'whatsapp') background:rgba(37,211,102,0.1); color:#25d366;
                            @elseif($interaction->type === 'email') background:rgba(139,92,246,0.1); color:#8b5cf6;
                            @elseif($interaction->type === 'visit') background:rgba(245,158,11,0.1); color:#f59e0b;
                            @else background:var(--bg); color:var(--text-muted);
                            @endif
                        ">
                            @if($interaction->type === 'call') &#128222;
                            @elseif($interaction->type === 'whatsapp') &#128172;
                            @elseif($interaction->type === 'email') &#9993;
                            @elseif($interaction->type === 'visit') &#128205;
                            @else &#128196;
                            @endif
                        </div>
                        <div>
                            <div class="activity-text">
                                <strong>{{ $interaction->user?->name ?? 'Sistema' }}</strong>
                                @if($interaction->type === 'call') llamo
                                @elseif($interaction->type === 'whatsapp') envio WhatsApp
                                @elseif($interaction->type === 'email') envio email
                                @elseif($interaction->type === 'visit') registro visita
                                @else registro nota
                                @endif
                                @if($interaction->client)
                                    a <a href="{{ route('clients.show', $interaction->client) }}" style="color:var(--primary); font-weight:500; text-decoration:none;">{{ $interaction->client->name }}</a>
                                @endif
                            </div>
                            <div class="activity-time">{{ $interaction->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="empty-state-sm">
                    <div class="icon">&#128196;</div>
                    <p>Sin actividad reciente</p>
                </div>
                @endif
            </div>
        </div>

        {{-- QUICK ACTIONS --}}
        <div class="card" style="margin-top:0.75rem;">
            <div class="card-body">
                <div class="section-head" style="margin-bottom:0.5rem;">
                    <h3>Acciones Rapidas</h3>
                </div>
                <div style="display:flex; flex-direction:column; gap:0.4rem;">
                    <a href="{{ route('clients.create') }}" class="btn btn-primary" style="justify-content:center; width:100%;">+ Nuevo Lead</a>
                    <a href="{{ route('operations.create') }}" class="btn btn-outline" style="justify-content:center; width:100%;">+ Nueva Operacion</a>
                    <a href="{{ route('properties.create') }}" class="btn btn-outline" style="justify-content:center; width:100%;">+ Nueva Propiedad</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
