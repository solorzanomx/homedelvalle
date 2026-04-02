@extends('layouts.app-sidebar')
@section('title', 'Nuevo Item de Checklist')

@section('styles')
<style>
.section-title {
    font-size: 0.9rem; font-weight: 600; color: var(--text);
    margin: 1.5rem 0 0.75rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--border);
}
.section-title:first-child { margin-top: 0; }
.checkbox-group {
    display: flex; align-items: center; gap: 0.5rem; padding: 0.4rem 0;
}
.checkbox-group input[type="checkbox"] {
    width: 16px; height: 16px; accent-color: var(--primary); cursor: pointer;
}
.checkbox-group label { font-size: 0.88rem; cursor: pointer; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div><h2>Nuevo Item de Checklist</h2></div>
    <a href="{{ route('admin.checklists.index') }}" class="btn btn-outline">Volver</a>
</div>

<div class="card" style="max-width:650px;">
    <div class="card-body">
        @if($errors->any())
            <div style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); border-radius:var(--radius); padding:0.75rem 1rem; margin-bottom:1.25rem;">
                @foreach($errors->all() as $error)
                    <p style="color:var(--danger); font-size:0.82rem; margin:0.15rem 0;">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.checklists.store') }}">
            @csrf

            <div class="section-title" style="margin-top:0;">Configuracion</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Etapa <span class="required">*</span></label>
                    <select name="stage" class="form-select" required>
                        <option value="">-- Seleccionar etapa --</option>
                        @foreach(\App\Models\Operation::STAGES as $key => $label)
                            <option value="{{ $key }}" {{ old('stage') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('stage') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo de Operacion <span class="required">*</span></label>
                    <select name="operation_type" class="form-select" required>
                        <option value="both" {{ old('operation_type', 'both') === 'both' ? 'selected' : '' }}>Ambos (Venta/Renta)</option>
                        <option value="venta" {{ old('operation_type') === 'venta' ? 'selected' : '' }}>Venta</option>
                        <option value="renta" {{ old('operation_type') === 'renta' ? 'selected' : '' }}>Renta</option>
                        <option value="captacion" {{ old('operation_type') === 'captacion' ? 'selected' : '' }}>Captacion</option>
                    </select>
                    @error('operation_type') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="section-title">Detalle</div>
            <div class="form-group">
                <label class="form-label">Titulo <span class="required">*</span></label>
                <input type="text" name="title" class="form-input" value="{{ old('title') }}" required placeholder="Ej: Firmar contrato de exclusiva">
                @error('title') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label class="form-label">Descripcion</label>
                <textarea name="description" class="form-textarea" rows="3" placeholder="Descripcion opcional del paso...">{{ old('description') }}</textarea>
                @error('description') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Orden</label>
                    <input type="number" name="sort_order" class="form-input" value="{{ old('sort_order', 0) }}" min="0" step="1">
                    <p class="form-hint">Menor numero = aparece primero</p>
                    @error('sort_order') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="section-title">Opciones</div>
            <div class="form-group">
                <div class="checkbox-group">
                    <input type="hidden" name="is_required" value="0">
                    <input type="checkbox" name="is_required" id="isRequired" value="1" {{ old('is_required', '1') == '1' ? 'checked' : '' }}>
                    <label for="isRequired">Requerido para avanzar de etapa</label>
                </div>
            </div>
            <div class="form-group">
                <div class="checkbox-group">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="isActive" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                    <label for="isActive">Activo</label>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('admin.checklists.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">Crear Item</button>
            </div>
        </form>
    </div>
</div>
@endsection
