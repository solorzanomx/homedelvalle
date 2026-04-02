@extends('layouts.app-sidebar')
@section('title', 'Automatizaciones')

@section('content')
@php
    $triggerLabels = [
        'new_client' => 'Nuevo Cliente',
        'new_property' => 'Nueva Propiedad',
        'deal_stage_change' => 'Cambio Etapa Deal',
        'property_days_listed' => 'Dias en Listado',
        'client_inactive' => 'Cliente Inactivo',
        'task_overdue' => 'Tarea Vencida',
    ];
    $actionLabels = [
        'send_email' => 'Enviar Email',
        'create_task' => 'Crear Tarea',
        'notify_user' => 'Notificar Usuario',
        'change_status' => 'Cambiar Estado',
    ];
@endphp

<div class="page-header">
    <div>
        <h2>Automatizaciones</h2>
        <p class="text-muted">Reglas que ejecutan acciones automaticamente ante eventos del CRM</p>
    </div>
    <div style="display:flex; gap:0.5rem;">
        <a href="{{ route('admin.automations.logs') }}" class="btn btn-outline">&#128196; Ver Logs</a>
        <a href="{{ route('admin.automations.create') }}" class="btn btn-primary">+ Nueva Regla</a>
    </div>
</div>

{{-- Stats --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-blue">&#9881;</div>
        <div>
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-label">Total Reglas</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-green">&#9889;</div>
        <div>
            <div class="stat-value">{{ $stats['active'] }}</div>
            <div class="stat-label">Activas</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-purple">&#9654;</div>
        <div>
            <div class="stat-value">{{ number_format($stats['executions']) }}</div>
            <div class="stat-label">Ejecuciones Totales</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#ef4444;">&#9888;</div>
        <div>
            <div class="stat-value">{{ $stats['failed'] }}</div>
            <div class="stat-label">Fallidas</div>
        </div>
    </div>
</div>

{{-- Rules Table --}}
<div class="card">
    <div class="card-header">
        <h3>Reglas de Automatizacion</h3>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Trigger</th>
                    <th>Accion</th>
                    <th>Estado</th>
                    <th>Ejecuciones</th>
                    <th>Ultima Ejecucion</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rules as $rule)
                <tr>
                    <td style="font-weight:500;">{{ $rule->name }}</td>
                    <td><span class="badge badge-blue">{{ $triggerLabels[$rule->trigger] ?? $rule->trigger }}</span></td>
                    <td><span class="badge badge-yellow">{{ $actionLabels[$rule->action] ?? $rule->action }}</span></td>
                    <td>
                        <form action="{{ route('admin.automations.toggle', $rule) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="badge {{ $rule->is_active ? 'badge-green' : 'badge-red' }}" style="cursor:pointer; border:none; font-family:inherit;">
                                {{ $rule->is_active ? 'Activa' : 'Inactiva' }}
                            </button>
                        </form>
                    </td>
                    <td class="text-center">{{ number_format($rule->trigger_count) }}</td>
                    <td>
                        @if($rule->last_triggered_at)
                            <span title="{{ $rule->last_triggered_at->format('d/m/Y H:i') }}">{{ $rule->last_triggered_at->diffForHumans() }}</span>
                        @else
                            <span class="text-muted">&mdash;</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-btns">
                            <a href="{{ route('admin.automations.edit', $rule) }}" class="btn btn-sm btn-outline">Editar</a>
                            <a href="{{ route('admin.automations.logs', ['rule_id' => $rule->id]) }}" class="btn btn-sm btn-outline">Logs</a>
                            <form action="{{ route('admin.automations.destroy', $rule) }}" method="POST" onsubmit="return confirm('Eliminar esta regla y todos sus logs?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted" style="padding:2rem;">
                        No hay reglas de automatizacion. <a href="{{ route('admin.automations.create') }}" style="color:var(--primary);">Crear la primera</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
