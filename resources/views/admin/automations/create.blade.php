@extends('layouts.app-sidebar')
@section('title', 'Nueva Automatizacion')

@section('content')
<div class="page-header">
    <div>
        <h2>Nueva Automatizacion</h2>
        <p class="text-muted">Define una regla que se ejecute automaticamente ante un evento</p>
    </div>
    <a href="{{ route('admin.automations.index') }}" class="btn btn-outline">&#8592; Volver</a>
</div>

<div style="max-width:700px;">
    <form action="{{ route('admin.automations.store') }}" method="POST">
        @csrf

        {{-- Regla --}}
        <div class="card">
            <div class="card-header"><h3>Regla</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Nombre <span class="required">*</span></label>
                    <input type="text" name="name" class="form-input" value="{{ old('name') }}" required placeholder="Ej: Seguimiento a nuevo cliente">
                    @error('name') <p class="form-hint" style="color:var(--danger);">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" style="display:flex; align-items:center; gap:0.5rem;">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} style="width:16px; height:16px;">
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
                            <option value="{{ $value }}" {{ old('trigger') === $value ? 'selected' : '' }}>{{ $label }}</option>
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
                    <textarea name="conditions" class="form-textarea" rows="4" placeholder='{"stage": "lead", "property_type": "casa"}'>{{ old('conditions') }}</textarea>
                    <p class="form-hint">Opcional. Formato JSON con las condiciones que deben cumplirse para ejecutar la accion. Dejar vacio para ejecutar siempre que ocurra el trigger.</p>
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
                            <option value="{{ $value }}" {{ old('action') === $value ? 'selected' : '' }}>{{ $label }}</option>
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
                    <textarea name="action_config" class="form-textarea" rows="5" placeholder='{"task_title": "Seguimiento", "priority": "high", "due_days": 3}'>{{ old('action_config') }}</textarea>
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
            <button type="submit" class="btn btn-primary">Crear Regla</button>
        </div>
    </form>
</div>
@endsection
