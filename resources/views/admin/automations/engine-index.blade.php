@extends('layouts.app-sidebar')
@section('title', 'Automatizaciones')

@section('styles')
<style>
.auto-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem; }
.auto-header h2 { font-size: 1.15rem; font-weight: 700; }

.auto-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 1rem; }
.auto-card { background: var(--card); border: 1px solid var(--border); border-radius: 10px; overflow: hidden; transition: border-color 0.15s; }
.auto-card:hover { border-color: var(--primary); }
.auto-bar { height: 4px; }
.auto-bar.on { background: var(--success); }
.auto-bar.off { background: var(--border); }
.auto-body { padding: 1.25rem; }
.auto-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.4rem; }
.auto-name { font-weight: 700; font-size: 0.95rem; }
.auto-desc { font-size: 0.82rem; color: var(--text-muted); margin-bottom: 0.75rem; }
.auto-trigger { display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.75rem; background: #eef2ff; color: #3730a3; padding: 3px 8px; border-radius: 4px; margin-bottom: 0.75rem; }
.auto-stats { display: flex; gap: 1.25rem; margin-bottom: 0.75rem; }
.auto-stat-val { font-size: 1.1rem; font-weight: 700; }
.auto-stat-lbl { font-size: 0.68rem; color: var(--text-muted); }
.auto-pills { display: flex; gap: 0.25rem; flex-wrap: wrap; margin-bottom: 0.75rem; }
.auto-pill { font-size: 0.68rem; padding: 2px 6px; border-radius: 3px; background: var(--bg); color: var(--text-muted); }
.auto-pill.delay { background: #fef3c7; color: #92400e; }
.auto-pill.email { background: #dbeafe; color: #1e40af; }
.auto-pill.whatsapp { background: #dcfce7; color: #166534; }
.auto-pill.condition { background: #fce7f3; color: #9d174d; }
.auto-pill.task { background: #e0e7ff; color: #3730a3; }
.auto-pill.pipeline { background: #ecfdf5; color: #065f46; }
.badge-on { background: #ecfdf5; color: #065f46; font-size: 0.68rem; padding: 2px 8px; border-radius: 10px; }
.badge-off { background: #f3f4f6; color: #6b7280; font-size: 0.68rem; padding: 2px 8px; border-radius: 10px; }
.auto-actions { display: flex; gap: 0.4rem; flex-wrap: wrap; }
</style>
@endsection

@section('content')
<div class="auto-header">
    <div>
        <h2>&#9889; Automatizaciones</h2>
        <p style="font-size:0.82rem; color:var(--text-muted);">Flujos automaticos para nutrir leads y moverlos al pipeline.</p>
    </div>
    <a href="{{ route('admin.automations-engine.create') }}" class="btn btn-primary">+ Nueva Automatizacion</a>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>
@endif

<div class="auto-grid">
    @foreach($automations as $auto)
    <div class="auto-card">
        <div class="auto-bar {{ $auto->is_active ? 'on' : 'off' }}"></div>
        <div class="auto-body">
            <div class="auto-top">
                <div class="auto-name">{{ $auto->name }}</div>
                <span class="{{ $auto->is_active ? 'badge-on' : 'badge-off' }}">{{ $auto->is_active ? 'Activa' : 'Pausada' }}</span>
            </div>
            @if($auto->description)<div class="auto-desc">{{ Str::limit($auto->description, 100) }}</div>@endif
            <div class="auto-trigger">&#9889; {{ \App\Models\Automation::TRIGGERS[$auto->trigger_type] ?? $auto->trigger_type }}</div>
            @if($auto->trigger_type === 'form_submitted' && ($auto->trigger_config['source'] ?? 'all') !== 'all')
            @php $srcLabels = ['contact' => 'Contacto', 'landing' => 'Landing', 'form' => 'Dinámicos']; @endphp
            <div style="font-size:0.68rem; color:var(--text-muted); margin-top:-0.5rem; margin-bottom:0.75rem;">Origen: {{ $srcLabels[$auto->trigger_config['source']] ?? $auto->trigger_config['source'] }}</div>
            @endif
            <div class="auto-stats">
                <div><div class="auto-stat-val">{{ $auto->steps_count }}</div><div class="auto-stat-lbl">Pasos</div></div>
                <div><div class="auto-stat-val">{{ $auto->enrollments_count }}</div><div class="auto-stat-lbl">Inscritos</div></div>
            </div>
            <div class="auto-actions">
                <form method="POST" action="{{ route('admin.automations-engine.toggle', $auto) }}">@csrf
                    <button class="btn btn-sm btn-outline">{{ $auto->is_active ? '&#10074;&#10074; Pausar' : '&#9654; Activar' }}</button>
                </form>
                <a href="{{ route('admin.automations-engine.show', $auto) }}" class="btn btn-sm btn-outline">Ver</a>
                <a href="{{ route('admin.automations-engine.edit', $auto) }}" class="btn btn-sm btn-outline">Editar</a>
                <form method="POST" action="{{ route('admin.automations-engine.destroy', $auto) }}" onsubmit="return confirm('Eliminar?')">@csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>

@if($automations->isEmpty())
<div style="text-align:center; padding:4rem; color:var(--text-muted);">
    <div style="font-size:2.5rem; margin-bottom:0.5rem; opacity:0.3;">&#9889;</div>
    <p>No hay automatizaciones. Crea tu primera para nutrir leads automaticamente.</p>
</div>
@endif
@endsection
