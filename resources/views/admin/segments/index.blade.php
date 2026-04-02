@extends('layouts.app-sidebar')
@section('title', 'Segmentos')

@section('styles')
<style>
.seg-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem; }
.seg-header h2 { font-size: 1.15rem; font-weight: 700; }
.seg-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1rem; }

.seg-card {
    background: var(--card); border: 1px solid var(--border); border-radius: 10px;
    padding: 1.25rem; transition: border-color 0.15s;
}
.seg-card:hover { border-color: var(--primary); }
.seg-card-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem; }
.seg-name { font-weight: 700; font-size: 0.95rem; }
.seg-badge {
    font-size: 0.68rem; font-weight: 600; padding: 2px 8px; border-radius: 10px;
}
.seg-active { background: #ecfdf5; color: #065f46; }
.seg-inactive { background: #fef2f2; color: #991b1b; }
.seg-system { background: #eef2ff; color: #3730a3; }
.seg-desc { font-size: 0.82rem; color: var(--text-muted); margin-bottom: 0.75rem; line-height: 1.4; }

.seg-stats { display: flex; gap: 1rem; margin-bottom: 0.75rem; }
.seg-stat { text-align: center; }
.seg-stat-val { font-size: 1.2rem; font-weight: 700; color: var(--primary); }
.seg-stat-lbl { font-size: 0.68rem; color: var(--text-muted); }

.seg-rules { margin-bottom: 0.75rem; }
.seg-rule {
    display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.72rem;
    background: var(--bg); padding: 2px 8px; border-radius: 4px; margin: 0 0.25rem 0.25rem 0;
}

.seg-actions { display: flex; gap: 0.4rem; }
</style>
@endsection

@section('content')
<div class="seg-header">
    <div>
        <h2>Segmentos</h2>
        <p style="font-size:0.82rem; color:var(--text-muted);">Agrupa clientes automaticamente por comportamiento y datos.</p>
    </div>
    <a href="{{ route('admin.segments.create') }}" class="btn btn-primary">+ Nuevo Segmento</a>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-error" style="margin-bottom:1rem;">{{ session('error') }}</div>
@endif

<div class="seg-grid">
    @foreach($segments as $segment)
    <div class="seg-card">
        <div class="seg-card-top">
            <div class="seg-name">{{ $segment->name }}</div>
            <div style="display:flex; gap:0.25rem;">
                @if($segment->is_system)<span class="seg-badge seg-system">Sistema</span>@endif
                <span class="seg-badge {{ $segment->is_active ? 'seg-active' : 'seg-inactive' }}">{{ $segment->is_active ? 'Activo' : 'Inactivo' }}</span>
            </div>
        </div>
        @if($segment->description)
        <div class="seg-desc">{{ $segment->description }}</div>
        @endif

        <div class="seg-stats">
            <div class="seg-stat">
                <div class="seg-stat-val">{{ $segment->clients_count }}</div>
                <div class="seg-stat-lbl">Clientes</div>
            </div>
            <div class="seg-stat">
                <div class="seg-stat-val">{{ count($segment->rules ?? []) }}</div>
                <div class="seg-stat-lbl">Reglas</div>
            </div>
            @if($segment->last_evaluated_at)
            <div class="seg-stat" style="text-align:left; flex:1;">
                <div style="font-size:0.75rem; color:var(--text-muted);">Evaluado</div>
                <div style="font-size:0.78rem;">{{ $segment->last_evaluated_at->diffForHumans() }}</div>
            </div>
            @endif
        </div>

        <div class="seg-rules">
            @foreach($segment->rules ?? [] as $rule)
            <span class="seg-rule">
                {{ \App\Models\Segment::FIELDS[$rule['field']] ?? $rule['field'] }}
                <strong>{{ $rule['operator'] }}</strong>
                {{ is_array($rule['value'] ?? null) ? implode(', ', $rule['value']) : ($rule['value'] ?? '') }}
            </span>
            @endforeach
        </div>

        <div class="seg-actions">
            <form method="POST" action="{{ route('admin.segments.evaluate', $segment) }}">
                @csrf
                <button class="btn btn-sm btn-outline" title="Evaluar ahora">&#x21bb; Evaluar</button>
            </form>
            <a href="{{ route('admin.segments.edit', $segment) }}" class="btn btn-sm btn-outline">Editar</a>
            @if(!$segment->is_system)
            <form method="POST" action="{{ route('admin.segments.destroy', $segment) }}" onsubmit="return confirm('Eliminar segmento?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-danger">Eliminar</button>
            </form>
            @endif
        </div>
    </div>
    @endforeach
</div>

@if($segments->isEmpty())
<div style="text-align:center; padding:4rem 1rem; color:var(--text-muted);">
    <div style="font-size:2.5rem; margin-bottom:0.5rem; opacity:0.3;">&#127919;</div>
    <p>No hay segmentos creados. Crea tu primer segmento para agrupar clientes automaticamente.</p>
</div>
@endif
@endsection
