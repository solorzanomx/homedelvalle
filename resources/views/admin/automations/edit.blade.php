@extends('layouts.app-sidebar')
@section('title', 'Editar Automatizacion')

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
    $statusBadge = ['success' => 'badge-green', 'failed' => 'badge-red', 'skipped' => 'badge-yellow'];
    $statusLabel = ['success' => 'Exitoso', 'failed' => 'Fallido', 'skipped' => 'Omitido'];
@endphp

<div class="page-header">
    <div>
        <h2>Editar Automatizacion</h2>
        <p class="text-muted">{{ $automation->name }}</p>
    </div>
    <a href="{{ route('admin.automations.index') }}" class="btn btn-outline">&#8592; Volver</a>
</div>

<div style="max-width:700px;">
    <form action="{{ route('admin.automations.update', $automation) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Regla --}}
        <div class="card">
            <div class="card-header"><h3>Regla</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Nombre <span class="required">*</span></label>
                    <input type="text" name="name" class="form-input" value="{{ old('name', $automation->name) }}" required>
                    @error('name') <p class="form-hint" style="color:var(--danger);">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" style="display:flex; align-items:center; gap:0.5rem;">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $automation->is_active) ? 'checked' : '' }} style="width:16px; height:16px;">
                        Regla activa
                    </label>
                </div>
            </div>
        </div>

        {{-- Trigger --}}
        <div class="card">
            <div class="card-header"><h3>Trigger</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Evento disparador <span class="required">*</span></label>
                    <select name="trigger" class="form-select" required>
                        <option value="">-- Seleccionar --</option>
                        @foreach($triggers as $value => $label)
                            <option value="{{ $value }}" {{ old('trigger', $automation->trigger) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('trigger') <p class="form-hint" style="color:var(--danger);">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Condiciones --}}
        <div class="card">
            <div class="card-header"><h3>Condiciones</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Condiciones (JSON)</label>
                    <textarea name="conditions" class="form-textarea" rows="4" placeholder='{"stage": "lead", "property_type": "casa"}'>{{ old('conditions', $automation->conditions ? json_encode($automation->conditions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                    <p class="form-hint">Opcional. Formato JSON con las condiciones que deben cumplirse para ejecutar la accion.</p>
                    @error('conditions') <p class="form-hint" style="color:var(--danger);">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Accion --}}
        <div class="card">
            <div class="card-header"><h3>Accion</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Accion a ejecutar <span class="required">*</span></label>
                    <select name="action" class="form-select" required>
                        <option value="">-- Seleccionar --</option>
                        @foreach($actions as $value => $label)
                            <option value="{{ $value }}" {{ old('action', $automation->action) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('action') <p class="form-hint" style="color:var(--danger);">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Configuracion de Accion --}}
        <div class="card">
            <div class="card-header"><h3>Configuracion de Accion</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Parametros de la accion (JSON)</label>
                    <textarea name="action_config" class="form-textarea" rows="5" placeholder='{"task_title": "Seguimiento", "priority": "high", "due_days": 3}'>{{ old('action_config', $automation->action_config ? json_encode($automation->action_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                    <p class="form-hint">Formato JSON con la configuracion especifica de la accion seleccionada.</p>
                    <div style="margin-top:0.5rem; font-size:0.78rem; color:var(--text-muted); line-height:1.6;">
                        <strong>Ejemplos segun accion:</strong><br>
                        <code>Crear Tarea:</code> {"task_title": "...", "task_description": "...", "priority": "high|medium|low", "due_days": 3, "assign_to": 1}<br>
                        <code>Enviar Email:</code> {"template_id": 1, "to": "client"}<br>
                        <code>Notificar Usuario:</code> {"user_id": 1, "message": "..."}<br>
                        <code>Cambiar Estado:</code> {"new_status": "activo"}
                    </div>
                    @error('action_config') <p class="form-hint" style="color:var(--danger);">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="form-actions" style="border:none; padding:0; margin-bottom:2rem;">
            <a href="{{ route('admin.automations.index') }}" class="btn btn-outline">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </div>
    </form>

    {{-- Ultimos Logs --}}
    @if($recentLogs->count())
    <div class="card">
        <div class="card-header">
            <h3>Ultimos Logs</h3>
            <a href="{{ route('admin.automations.logs', ['rule_id' => $automation->id]) }}" class="btn btn-sm btn-outline">Ver todos</a>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Resultado</th>
                        <th>Error</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentLogs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td><span class="badge {{ $statusBadge[$log->status] ?? 'badge-blue' }}">{{ $statusLabel[$log->status] ?? $log->status }}</span></td>
                        <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ json_encode($log->action_result) }}">
                            {{ \Illuminate\Support\Str::limit(json_encode($log->action_result), 50) }}
                        </td>
                        <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            @if($log->error_message)
                                <span style="color:var(--danger);">{{ \Illuminate\Support\Str::limit($log->error_message, 60) }}</span>
                            @else
                                <span class="text-muted">&mdash;</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
