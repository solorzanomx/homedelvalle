@extends('layouts.app-sidebar')
@section('title', 'Editar Tarea')

@section('styles')
<style>
.task-form-card {
    background: var(--card); border: 1px solid var(--border); border-radius: 10px;
    max-width: 720px; overflow: hidden;
}
.task-form-header {
    padding: 1rem 1.5rem; border-bottom: 1px solid var(--border);
    display: flex; justify-content: space-between; align-items: center;
}
.task-form-header h3 { font-size: 1rem; font-weight: 600; }
.task-form-body { padding: 1.5rem; }

.section-label {
    font-size: 0.78rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;
    letter-spacing: 0.5px; margin: 1.5rem 0 0.75rem; padding-bottom: 0.4rem;
    border-bottom: 1px solid var(--border);
}
.section-label:first-child { margin-top: 0; }

/* Priority cards */
.prio-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem; }
.prio-card {
    padding: 0.5rem; border-radius: var(--radius); border: 2px solid var(--border);
    text-align: center; cursor: pointer; transition: all 0.15s; position: relative;
}
.prio-card:hover { border-color: var(--primary); }
.prio-card.active { border-color: var(--primary); background: rgba(102,126,234,0.04); }
.prio-card input { position: absolute; opacity: 0; pointer-events: none; }
.prio-dot-lg { width: 10px; height: 10px; border-radius: 50%; margin: 0 auto 0.25rem; }
.prio-card-label { font-size: 0.78rem; font-weight: 500; }

/* Meta bar */
.task-meta-bar {
    background: var(--bg); border-radius: var(--radius); padding: 0.75rem 1rem;
    display: flex; flex-wrap: wrap; gap: 1.25rem; font-size: 0.78rem; color: var(--text-muted);
    margin-bottom: 1.25rem;
}
.task-meta-bar strong { color: var(--text); }

@media (max-width: 640px) { .prio-cards { grid-template-columns: repeat(2, 1fr); } }
</style>
@endsection

@section('content')
<div style="margin-bottom:1rem;">
    <a href="{{ route('tasks.index') }}" style="font-size:0.82rem; color:var(--text-muted);">&#8592; Tareas</a>
</div>

<div class="task-form-card">
    <div class="task-form-header">
        <h3>Editar Tarea</h3>
        <div style="display:flex; gap:0.4rem; align-items:center;">
            @php
                $statusBadges = ['pending'=>'badge-yellow','in_progress'=>'badge-blue','completed'=>'badge-green','cancelled'=>'badge-red'];
                $statusLabels = ['pending'=>'Pendiente','in_progress'=>'En progreso','completed'=>'Completada','cancelled'=>'Cancelada'];
            @endphp
            <span class="badge {{ $statusBadges[$task->status] ?? 'badge-blue' }}">{{ $statusLabels[$task->status] ?? $task->status }}</span>
            <form method="POST" action="{{ route('tasks.toggleComplete', $task) }}" style="display:inline;">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-sm {{ $task->status === 'completed' ? 'btn-outline' : 'btn-primary' }}" style="font-size:0.75rem;">
                    {{ $task->status === 'completed' ? 'Reabrir' : '&#10003; Completar' }}
                </button>
            </form>
        </div>
    </div>
    <div class="task-form-body">
        {{-- Meta bar --}}
        <div class="task-meta-bar">
            <div><strong>Asignado:</strong> {{ $task->user->name ?? 'Sin asignar' }}</div>
            <div><strong>Creada:</strong> {{ $task->created_at->format('d/m/Y H:i') }}</div>
            @if($task->completed_at)<div><strong>Completada:</strong> {{ $task->completed_at->format('d/m/Y H:i') }}</div>@endif
        </div>

        @if($errors->any())
            <div class="alert alert-error" style="margin-bottom:1rem;">
                <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
            </div>
        @endif

        <form action="{{ route('tasks.update', $task) }}" method="POST">
            @csrf @method('PUT')

            <div class="form-group">
                <label class="form-label">Titulo <span class="required">*</span></label>
                <input type="text" name="title" value="{{ old('title', $task->title) }}" class="form-input" required>
            </div>

            <div class="form-group">
                <label class="form-label">Descripcion</label>
                <textarea name="description" class="form-textarea" rows="3">{{ old('description', $task->description) }}</textarea>
            </div>

            <div class="section-label">Prioridad</div>
            <div class="prio-cards" style="margin-bottom:1rem;">
                @foreach(['low'=>['Baja','#94a3b8'], 'medium'=>['Media','#3b82f6'], 'high'=>['Alta','#f59e0b'], 'urgent'=>['Urgente','#ef4444']] as $val => [$label, $color])
                <label class="prio-card {{ old('priority', $task->priority) === $val ? 'active' : '' }}" onclick="this.closest('.prio-cards').querySelectorAll('.prio-card').forEach(c=>c.classList.remove('active')); this.classList.add('active');">
                    <input type="radio" name="priority" value="{{ $val }}" {{ old('priority', $task->priority) === $val ? 'checked' : '' }}>
                    <div class="prio-dot-lg" style="background:{{ $color }};"></div>
                    <div class="prio-card-label">{{ $label }}</div>
                </label>
                @endforeach
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-select">
                        <option value="pending" {{ old('status', $task->status) === 'pending' ? 'selected' : '' }}>Pendiente</option>
                        <option value="in_progress" {{ old('status', $task->status) === 'in_progress' ? 'selected' : '' }}>En progreso</option>
                        <option value="completed" {{ old('status', $task->status) === 'completed' ? 'selected' : '' }}>Completada</option>
                        <option value="cancelled" {{ old('status', $task->status) === 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha de vencimiento</label>
                    <input type="date" name="due_date" value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}" class="form-input">
                </div>
            </div>

            <div class="section-label">Relaciones</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Operacion</label>
                    <select name="operation_id" class="form-select">
                        <option value="">Sin operacion</option>
                        @foreach($operations as $op)
                            <option value="{{ $op->id }}" {{ old('operation_id', $task->operation_id) == $op->id ? 'selected' : '' }}>
                                #{{ $op->id }} - {{ $op->type_label }} - {{ $op->property->title ?? $op->client->name ?? '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Cliente</label>
                    <select name="client_id" class="form-select">
                        <option value="">Sin cliente</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id', $task->client_id) == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Propiedad</label>
                    <select name="property_id" class="form-select">
                        <option value="">Sin propiedad</option>
                        @foreach($properties as $property)
                            <option value="{{ $property->id }}" {{ old('property_id', $task->property_id) == $property->id ? 'selected' : '' }}>{{ $property->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Negocio</label>
                    <select name="deal_id" class="form-select">
                        <option value="">Sin negocio</option>
                        @foreach($deals as $deal)
                            <option value="{{ $deal->id }}" {{ old('deal_id', $task->deal_id) == $deal->id ? 'selected' : '' }}>#{{ $deal->id }} - ${{ number_format($deal->amount, 0) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Eliminar esta tarea?')" style="margin-right:auto;">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
                <a href="{{ route('tasks.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection
