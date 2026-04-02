@extends('layouts.app-sidebar')
@section('title', $automation->name)

@section('styles')
<style>
.show-layout { display: grid; grid-template-columns: 1fr 320px; gap: 1.25rem; align-items: start; }
@media (max-width: 1024px) { .show-layout { grid-template-columns: 1fr; } }

.show-card { background: var(--card); border: 1px solid var(--border); border-radius: 10px; margin-bottom: 1rem; overflow: hidden; }
.show-card-header { padding: 0.8rem 1.25rem; border-bottom: 1px solid var(--border); font-weight: 600; font-size: 0.88rem; display: flex; justify-content: space-between; align-items: center; }
.show-card-body { padding: 1.25rem; }

/* Flow visualization */
.flow-step { display: flex; gap: 0.75rem; margin-bottom: 0.25rem; }
.flow-line { width: 3px; background: var(--border); flex-shrink: 0; margin-left: 14px; min-height: 24px; }
.flow-dot { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; color: #fff; flex-shrink: 0; }
.flow-detail { flex: 1; min-width: 0; padding: 0.5rem 0; }
.flow-detail-type { font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; }
.flow-detail-desc { font-size: 0.85rem; margin-top: 0.1rem; }

.dot-delay { background: #f59e0b; }
.dot-email { background: #3b82f6; }
.dot-whatsapp { background: #25d366; }
.dot-condition { background: #ec4899; }
.dot-task { background: #6366f1; }
.dot-pipeline { background: #10b981; }
.dot-score { background: #8b5cf6; }
.dot-field { background: #6b7280; }

/* Enrollments list */
.enr-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0; border-bottom: 1px solid var(--border); }
.enr-item:last-child { border-bottom: none; }
.enr-avatar { width: 32px; height: 32px; border-radius: 50%; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.78rem; flex-shrink: 0; }
.enr-info { flex: 1; min-width: 0; }
.enr-name { font-size: 0.85rem; font-weight: 500; }
.enr-meta { font-size: 0.72rem; color: var(--text-muted); }
.enr-status { font-size: 0.68rem; padding: 2px 8px; border-radius: 10px; font-weight: 500; }
.enr-active { background: #ecfdf5; color: #065f46; }
.enr-completed { background: #eef2ff; color: #3730a3; }
.enr-paused { background: #fef3c7; color: #92400e; }

.stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; margin-bottom: 1rem; }
.stat-box { text-align: center; padding: 0.75rem; background: var(--bg); border-radius: var(--radius); }
.stat-box-val { font-size: 1.3rem; font-weight: 700; }
.stat-box-lbl { font-size: 0.72rem; color: var(--text-muted); }
</style>
@endsection

@section('content')
<div style="margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
    <a href="{{ route('admin.automations-engine.index') }}" style="font-size:0.82rem; color:var(--text-muted);">Automatizaciones</a>
    <span style="font-size:0.72rem; color:var(--text-muted);">/</span>
    <span style="font-size:0.82rem;">{{ $automation->name }}</span>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>
@endif

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem; flex-wrap:wrap; gap:0.5rem;">
    <div style="display:flex; align-items:center; gap:0.75rem;">
        <h2 style="font-size:1.15rem; font-weight:700;">{{ $automation->name }}</h2>
        <span class="{{ $automation->is_active ? 'badge-on' : 'badge-off' }}" style="font-size:0.72rem; padding:3px 10px; border-radius:10px;">{{ $automation->is_active ? 'Activa' : 'Pausada' }}</span>
    </div>
    <div style="display:flex; gap:0.4rem;">
        <form method="POST" action="{{ route('admin.automations-engine.toggle', $automation) }}">@csrf
            <button class="btn btn-sm btn-outline">{{ $automation->is_active ? 'Pausar' : 'Activar' }}</button>
        </form>
        <a href="{{ route('admin.automations-engine.edit', $automation) }}" class="btn btn-sm btn-primary">Editar</a>
    </div>
</div>

<div class="show-layout">
    <div>
        {{-- Stats --}}
        <div class="stats-row">
            <div class="stat-box"><div class="stat-box-val">{{ $automation->enrollment_count }}</div><div class="stat-box-lbl">Total inscritos</div></div>
            <div class="stat-box"><div class="stat-box-val">{{ $automation->enrollments->where('status', 'active')->count() }}</div><div class="stat-box-lbl">Activos</div></div>
            <div class="stat-box"><div class="stat-box-val">{{ $automation->enrollments->where('status', 'completed')->count() }}</div><div class="stat-box-lbl">Completados</div></div>
        </div>

        {{-- Flow --}}
        <div class="show-card">
            <div class="show-card-header">
                Flujo ({{ $automation->steps->count() }} pasos)
                <span style="font-size:0.75rem; color:var(--text-muted);">Trigger: {{ \App\Models\Automation::TRIGGERS[$automation->trigger_type] ?? $automation->trigger_type }}</span>
            </div>
            <div class="show-card-body">
                @foreach($automation->steps as $step)
                    @if(!$loop->first)<div class="flow-line"></div>@endif
                    @php
                        $dotClass = match($step->type) {
                            'delay' => 'dot-delay', 'send_email' => 'dot-email', 'send_whatsapp' => 'dot-whatsapp',
                            'condition' => 'dot-condition', 'create_task' => 'dot-task', 'move_pipeline' => 'dot-pipeline',
                            'add_score' => 'dot-score', default => 'dot-field',
                        };
                        $icon = match($step->type) {
                            'delay' => '⏱', 'send_email' => '✉', 'send_whatsapp' => '💬',
                            'condition' => '?', 'create_task' => '✓', 'move_pipeline' => '→',
                            'add_score' => '+', default => '•',
                        };
                    @endphp
                    <div class="flow-step">
                        <div class="flow-dot {{ $dotClass }}">{{ $icon }}</div>
                        <div class="flow-detail">
                            <div class="flow-detail-type" style="color: {{ match($step->type) { 'delay'=>'#f59e0b','send_email'=>'#3b82f6','send_whatsapp'=>'#25d366','condition'=>'#ec4899','create_task'=>'#6366f1','move_pipeline'=>'#10b981','add_score'=>'#8b5cf6',default=>'#6b7280' } }};">
                                {{ \App\Models\Automation::STEP_TYPES[$step->type] ?? $step->type }}
                            </div>
                            <div class="flow-detail-desc">
                                @switch($step->type)
                                    @case('delay') Esperar {{ $step->config['value'] ?? '?' }} {{ $step->config['unit'] ?? 'horas' }} @break
                                    @case('send_email') {{ $step->config['subject'] ?? 'Sin asunto' }} @break
                                    @case('send_whatsapp') {{ Str::limit($step->config['message'] ?? '', 60) }} @break
                                    @case('condition') Si {{ $step->config['field'] ?? '?' }} {{ $step->config['operator'] ?? '' }} {{ $step->config['value'] ?? '' }} @break
                                    @case('create_task') {{ $step->config['title'] ?? 'Tarea' }} ({{ $step->config['priority'] ?? 'media' }}) @break
                                    @case('move_pipeline') {{ ucfirst($step->config['operation_type'] ?? 'captacion') }} → {{ $step->config['stage'] ?? 'lead' }} @break
                                    @case('add_score') +{{ $step->config['points'] ?? 0 }} puntos @break
                                    @case('update_field') {{ $step->config['field'] ?? '' }} = {{ $step->config['value'] ?? '' }} @break
                                @endswitch
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Enrollments --}}
        <div class="show-card">
            <div class="show-card-header">Clientes inscritos ({{ $automation->enrollments->count() }})</div>
            <div class="show-card-body">
                @forelse($automation->enrollments->take(20) as $enr)
                <div class="enr-item">
                    <div class="enr-avatar">{{ strtoupper(substr($enr->client->name ?? '?', 0, 1)) }}</div>
                    <div class="enr-info">
                        <div class="enr-name">{{ $enr->client->name ?? 'Eliminado' }}</div>
                        <div class="enr-meta">Paso {{ $enr->current_step + 1 }} &middot; {{ $enr->created_at->diffForHumans() }}</div>
                    </div>
                    <span class="enr-status {{ $enr->status === 'active' ? 'enr-active' : ($enr->status === 'completed' ? 'enr-completed' : 'enr-paused') }}">
                        {{ ucfirst($enr->status) }}
                    </span>
                </div>
                @empty
                <p style="text-align:center; color:var(--text-muted); padding:1rem;">Sin clientes inscritos todavia.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div>
        <div class="show-card">
            <div class="show-card-header">Detalles</div>
            <div class="show-card-body" style="padding:0.5rem 1rem;">
                <div style="display:flex; justify-content:space-between; padding:0.35rem 0; font-size:0.82rem;"><span style="color:var(--text-muted);">Trigger</span><span style="font-weight:500;">{{ \App\Models\Automation::TRIGGERS[$automation->trigger_type] ?? '' }}</span></div>
                <div style="display:flex; justify-content:space-between; padding:0.35rem 0; font-size:0.82rem;"><span style="color:var(--text-muted);">Reentrada</span><span>{{ $automation->allow_reentry ? 'Si' : 'No' }}</span></div>
                <div style="display:flex; justify-content:space-between; padding:0.35rem 0; font-size:0.82rem;"><span style="color:var(--text-muted);">Creado por</span><span>{{ $automation->creator->name ?? 'Sistema' }}</span></div>
                <div style="display:flex; justify-content:space-between; padding:0.35rem 0; font-size:0.82rem;"><span style="color:var(--text-muted);">Creada</span><span>{{ $automation->created_at->format('d/m/Y') }}</span></div>
            </div>
        </div>

        @if($automation->description)
        <div class="show-card">
            <div class="show-card-header">Descripcion</div>
            <div class="show-card-body"><p style="font-size:0.85rem; line-height:1.5;">{{ $automation->description }}</p></div>
        </div>
        @endif
    </div>
</div>
@endsection
