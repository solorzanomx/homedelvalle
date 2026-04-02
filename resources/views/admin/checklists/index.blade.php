@extends('layouts.app-sidebar')
@section('title', 'Checklists por Etapa')

@section('styles')
<style>
.stage-section { margin-bottom: 1rem; }
.stage-header {
    display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1.25rem;
    background: var(--card); border: 1px solid var(--border); border-radius: var(--radius);
    cursor: pointer; user-select: none; transition: all 0.15s;
}
.stage-header:hover { background: var(--bg); }
.stage-header.open { border-bottom-left-radius: 0; border-bottom-right-radius: 0; border-bottom-color: transparent; }
.stage-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.stage-name { font-size: 0.9rem; font-weight: 600; flex: 1; }
.stage-count {
    font-size: 0.72rem; font-weight: 500; background: var(--bg); color: var(--text-muted);
    padding: 0.15rem 0.55rem; border-radius: 20px;
}
.stage-chevron { font-size: 0.65rem; color: var(--text-muted); transition: transform 0.2s; }
.stage-header.open .stage-chevron { transform: rotate(180deg); }
.stage-body {
    display: none; border: 1px solid var(--border); border-top: none;
    border-bottom-left-radius: var(--radius); border-bottom-right-radius: var(--radius);
    background: var(--card); overflow: hidden;
}
.stage-body.open { display: block; }
.empty-stage {
    padding: 1.5rem; text-align: center; color: var(--text-muted); font-size: 0.85rem;
}
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Checklists por Etapa</h2>
        <p class="text-muted">Plantillas de checklist para cada etapa de operacion</p>
    </div>
    <a href="{{ route('admin.checklists.create') }}" class="btn btn-primary">+ Nuevo Item</a>
</div>

@foreach($stages as $stageKey => $stageLabel)
    @php
        $items = $templates->get($stageKey, collect());
        $color = \App\Models\Operation::STAGE_COLORS[$stageKey] ?? '#94a3b8';
    @endphp
    <div class="stage-section">
        <div class="stage-header" onclick="toggleStage(this)">
            <span class="stage-dot" style="background: {{ $color }};"></span>
            <span class="stage-name">{{ $stageLabel }}</span>
            <span class="stage-count">{{ $items->count() }} {{ $items->count() === 1 ? 'item' : 'items' }}</span>
            <span class="stage-chevron">&#9660;</span>
        </div>
        <div class="stage-body">
            @if($items->isEmpty())
                <div class="empty-stage">Sin items configurados para esta etapa</div>
            @else
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Titulo</th>
                                <th>Tipo</th>
                                <th>Requerido</th>
                                <th>Orden</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                            <tr>
                                <td style="font-weight:500;">{{ $item->title }}</td>
                                <td>
                                    @if($item->operation_type === 'venta')
                                        <span class="badge badge-blue">Venta</span>
                                    @elseif($item->operation_type === 'renta')
                                        <span class="badge badge-yellow">Renta</span>
                                    @elseif($item->operation_type === 'captacion')
                                        <span class="badge" style="background:rgba(20,184,166,0.1); color:#14b8a6;">Captacion</span>
                                    @else
                                        <span class="badge badge-green">Ambos</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->is_required)
                                        <span class="badge badge-red">Si</span>
                                    @else
                                        <span class="badge" style="background:var(--bg); color:var(--text-muted);">No</span>
                                    @endif
                                </td>
                                <td>{{ $item->sort_order }}</td>
                                <td>
                                    @if($item->is_active)
                                        <span class="badge badge-green">Activa</span>
                                    @else
                                        <span class="badge" style="background:var(--bg); color:var(--text-muted);">Inactiva</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <a href="{{ route('admin.checklists.edit', $item) }}" class="btn btn-sm btn-outline">Editar</a>
                                        <form method="POST" action="{{ route('admin.checklists.destroy', $item) }}" style="display:inline" onsubmit="return confirm('Eliminar este item del checklist?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endforeach
@endsection

@section('scripts')
<script>
function toggleStage(header) {
    var body = header.nextElementSibling;
    var isOpen = header.classList.contains('open');
    header.classList.toggle('open', !isOpen);
    body.classList.toggle('open', !isOpen);
}
</script>
@endsection
