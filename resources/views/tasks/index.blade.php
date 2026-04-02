@extends('layouts.app-sidebar')
@section('title', 'Tareas')

@section('styles')
<style>
/* ===== STATUS PILLS ===== */
.status-pills { display: flex; gap: 0.5rem; margin-bottom: 1.25rem; overflow-x: auto; padding-bottom: 2px; }
.status-pill {
    display: flex; align-items: center; gap: 0.4rem; padding: 0.45rem 0.9rem; border-radius: 20px;
    font-size: 0.78rem; font-weight: 500; border: 1px solid var(--border); background: var(--card);
    color: var(--text-muted); text-decoration: none; white-space: nowrap; transition: all 0.15s;
}
.status-pill:hover { border-color: var(--primary); color: var(--text); }
.status-pill.active { background: var(--primary); color: #fff; border-color: var(--primary); }
.pill-count { font-size: 0.7rem; background: rgba(0,0,0,0.08); padding: 1px 6px; border-radius: 10px; }
.status-pill.active .pill-count { background: rgba(255,255,255,0.25); }

/* ===== TASK LIST ===== */
.task-list { background: var(--card); border: 1px solid var(--border); border-radius: 10px; overflow: hidden; }
.task-list-header {
    padding: 0.8rem 1.25rem; border-bottom: 1px solid var(--border);
    font-weight: 600; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem;
}
.task-item {
    display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.7rem 1.25rem;
    border-bottom: 1px solid var(--border); transition: background 0.1s;
}
.task-item:last-child { border-bottom: none; }
.task-item:hover { background: rgba(248,250,252,0.8); }

/* Checkbox */
.task-check {
    flex-shrink: 0; margin-top: 2px;
}
.task-check button {
    width: 22px; height: 22px; border-radius: 6px; border: 2px solid var(--border);
    background: none; cursor: pointer; display: flex; align-items: center; justify-content: center;
    font-size: 12px; color: var(--success); transition: all 0.15s; padding: 0;
}
.task-check button:hover { border-color: var(--success); background: #ecfdf5; }
.task-check button.is-done { background: var(--success); border-color: var(--success); color: #fff; }

/* Task content */
.task-content { flex: 1; min-width: 0; }
.task-title { font-size: 0.88rem; font-weight: 500; color: var(--text); }
.task-title.done { text-decoration: line-through; color: var(--text-muted); }
.task-title a { color: inherit; }
.task-sub { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem; display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center; }

/* Task right */
.task-right { display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0; }
.task-due {
    font-size: 0.75rem; font-weight: 500; padding: 0.2rem 0.5rem; border-radius: 4px; white-space: nowrap;
}
.due-overdue { background: #fef2f2; color: #991b1b; }
.due-today { background: #fffbeb; color: #92400e; }
.due-upcoming { background: #eef2ff; color: #3730a3; }
.due-none { color: var(--text-muted); }

/* Priority dot */
.prio-dot {
    width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
}
.prio-urgent { background: #ef4444; }
.prio-high { background: #f59e0b; }
.prio-medium { background: #3b82f6; }
.prio-low { background: #94a3b8; }

/* Badge */
.badge-orange { background: #fff7ed; color: #c2410c; }

/* Quick add */
.quick-add {
    display: flex; gap: 0.5rem; padding: 0.75rem 1.25rem; background: var(--card);
    border: 1px solid var(--border); border-radius: var(--radius); margin-bottom: 1.25rem;
}
.quick-add input { flex: 1; }

/* Empty */
.task-empty { text-align: center; padding: 3rem; color: var(--text-muted); font-size: 0.88rem; }

/* FAB */
.task-fab {
    display: none; position: fixed; bottom: 80px; right: 16px; z-index: 91;
    width: 52px; height: 52px; border-radius: 50%; border: none;
    background: var(--primary); color: #fff; font-size: 26px; font-weight: 300;
    box-shadow: 0 4px 14px rgba(102,126,234,0.4);
    align-items: center; justify-content: center; cursor: pointer; text-decoration: none;
}
@media (max-width: 768px) { .task-fab { display: flex; } }
</style>
@endsection

@section('content')
@php
    $prioColors = ['urgent'=>'prio-urgent','high'=>'prio-high','medium'=>'prio-medium','low'=>'prio-low'];
    $prioLabels = ['urgent'=>'Urgente','high'=>'Alta','medium'=>'Media','low'=>'Baja'];
    $statusLabels = ['pending'=>'Pendiente','in_progress'=>'En progreso','completed'=>'Completada','cancelled'=>'Cancelada'];
@endphp

{{-- Stats --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-blue">&#9776;</div>
        <div><div class="stat-value">{{ $stats['total'] }}</div><div class="stat-label">Total</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-orange">&#9203;</div>
        <div><div class="stat-value">{{ $stats['pending'] }}</div><div class="stat-label">Pendientes</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#ef4444;">&#9888;</div>
        <div><div class="stat-value">{{ $stats['overdue'] }}</div><div class="stat-label">Vencidas</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-green">&#10003;</div>
        <div><div class="stat-value">{{ $stats['completed_this_week'] }}</div><div class="stat-label">Completadas</div></div>
    </div>
</div>

{{-- Header --}}
<div class="page-header">
    <div>
        <h2>Tareas</h2>
        <p class="text-muted">{{ $tasks->total() }} tarea{{ $tasks->total() !== 1 ? 's' : '' }}</p>
    </div>
    <a href="{{ route('tasks.create') }}" class="btn btn-primary" style="white-space:nowrap;">+ Nueva</a>
</div>

{{-- Quick Add --}}
<form action="{{ route('tasks.store') }}" method="POST" class="quick-add">
    @csrf
    <input type="hidden" name="priority" value="medium">
    <input type="hidden" name="status" value="pending">
    <input type="text" name="title" class="form-input" placeholder="Agregar tarea rapida..." required>
    <input type="date" name="due_date" class="form-input" style="width:auto;" value="{{ date('Y-m-d') }}">
    <button type="submit" class="btn btn-primary">Agregar</button>
</form>

{{-- Status pills --}}
<div class="status-pills">
    <a href="{{ route('tasks.index') }}" class="status-pill {{ !request('status') && !request('priority') ? 'active' : '' }}">Todas</a>
    <a href="{{ route('tasks.index', ['status' => 'pending']) }}" class="status-pill {{ request('status') === 'pending' ? 'active' : '' }}">Pendientes <span class="pill-count">{{ $stats['pending'] }}</span></a>
    <a href="{{ route('tasks.index', ['status' => 'in_progress']) }}" class="status-pill {{ request('status') === 'in_progress' ? 'active' : '' }}">En progreso</a>
    <a href="{{ route('tasks.index', ['status' => 'completed']) }}" class="status-pill {{ request('status') === 'completed' ? 'active' : '' }}">Completadas</a>
    <a href="{{ route('tasks.index', ['priority' => 'urgent']) }}" class="status-pill {{ request('priority') === 'urgent' ? 'active' : '' }}" style="{{ request('priority') === 'urgent' ? '' : 'border-color:#fca5a5; color:#ef4444;' }}">Urgentes</a>
    <a href="{{ route('tasks.index', ['priority' => 'high']) }}" class="status-pill {{ request('priority') === 'high' ? 'active' : '' }}" style="{{ request('priority') === 'high' ? '' : 'border-color:#fde68a; color:#f59e0b;' }}">Alta prioridad</a>
</div>

{{-- Task List --}}
<div class="task-list">
    @forelse($tasks as $task)
    @php
        $isOverdue = $task->due_date && $task->due_date->isPast() && !in_array($task->status, ['completed','cancelled']);
        $isToday = $task->due_date && $task->due_date->isToday();
    @endphp
    <div class="task-item">
        <div class="task-check">
            <form method="POST" action="{{ route('tasks.toggleComplete', $task) }}">
                @csrf @method('PATCH')
                <button type="submit" class="{{ $task->status === 'completed' ? 'is-done' : '' }}" title="{{ $task->status === 'completed' ? 'Reabrir' : 'Completar' }}">
                    {{ $task->status === 'completed' ? '&#10003;' : '' }}
                </button>
            </form>
        </div>
        <div class="task-content">
            <div class="task-title {{ $task->status === 'completed' ? 'done' : '' }}">
                <a href="{{ route('tasks.edit', $task) }}">{{ $task->title }}</a>
            </div>
            <div class="task-sub">
                <span class="prio-dot {{ $prioColors[$task->priority] ?? 'prio-low' }}"></span>
                <span>{{ $prioLabels[$task->priority] ?? $task->priority }}</span>
                @if($task->client)<span>&middot; {{ $task->client->name }}</span>@endif
                @if($task->operation)<span>&middot; Op #{{ $task->operation->id }}</span>@endif
                @if($task->property)<span>&middot; {{ Str::limit($task->property->title, 20) }}</span>@endif
                @if($task->user)<span>&middot; {{ $task->user->name }}</span>@endif
            </div>
        </div>
        <div class="task-right">
            @if($task->due_date)
                <span class="task-due {{ $isOverdue ? 'due-overdue' : ($isToday ? 'due-today' : 'due-upcoming') }}">
                    {{ $isOverdue ? 'Vencida ' : '' }}{{ $task->due_date->format('d/m') }}
                </span>
            @endif
            <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-outline" style="padding:0.2rem 0.5rem; font-size:0.72rem;">&#9998;</a>
        </div>
    </div>
    @empty
    <div class="task-empty">
        No hay tareas {{ request('status') ? 'con ese estado' : '' }}. <br>
        <a href="{{ route('tasks.create') }}" style="color:var(--primary); font-weight:500;">+ Crear primera tarea</a>
    </div>
    @endforelse
</div>

@if($tasks->hasPages())
<div style="margin-top:1rem; text-align:center;">{{ $tasks->links() }}</div>
@endif

<a href="{{ route('tasks.create') }}" class="task-fab">+</a>
@endsection
