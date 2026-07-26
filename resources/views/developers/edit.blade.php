@extends('layouts.app-sidebar')
@section('title', 'Editar Constructora')

@section('styles')
<style>
.section-title {
    font-size: 0.9rem; font-weight: 600; color: var(--text);
    margin: 1.5rem 0 0.75rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--border);
}
.section-title:first-child { margin-top: 0; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div><h2>Editar Constructora</h2></div>
    <a href="{{ route('developers.show', $company) }}" class="btn btn-outline">Volver</a>
</div>

<div class="card" style="max-width:700px;">
    <div class="card-body">
        @if($errors->any())
            <div style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); border-radius:var(--radius); padding:0.75rem 1rem; margin-bottom:1.25rem;">
                @foreach($errors->all() as $error)
                    <p style="color:var(--danger); font-size:0.82rem; margin:0.15rem 0;">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('developers.update', $company) }}">
            @csrf
            @method('PUT')

            <div class="section-title" style="margin-top:0;">Datos de la Constructora</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nombre <span class="required">*</span></label>
                    <input type="text" name="name" class="form-input" value="{{ old('name', $company->name) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo <span class="required">*</span></label>
                    <select name="type" class="form-select" required>
                        @foreach(\App\Models\DeveloperCompany::TYPES as $val => $label)
                            <option value="{{ $val }}" {{ old('type', $company->type) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">RFC</label>
                    <input type="text" name="rfc" class="form-input" value="{{ old('rfc', $company->rfc) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Sitio web</label>
                    <input type="text" name="website" class="form-input" value="{{ old('website', $company->website) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-select">
                        <option value="active" {{ old('status', $company->status) === 'active' ? 'selected' : '' }}>Activo</option>
                        <option value="inactive" {{ old('status', $company->status) === 'inactive' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
            </div>

            <div class="section-title">Notas</div>
            <div class="form-group">
                <textarea name="notes" class="form-textarea" rows="3">{{ old('notes', $company->notes) }}</textarea>
            </div>

            <div class="form-actions">
                <a href="{{ route('developers.show', $company) }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </form>

        <div style="margin-top:1.5rem; padding-top:1.25rem; border-top:1px solid var(--border);">
            <form method="POST" action="{{ route('developers.destroy', $company) }}" onsubmit="return confirm('¿Eliminar esta constructora? Sus contactos vinculados se quedarán sin empresa, no se borran.')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Eliminar Constructora</button>
            </form>
        </div>
    </div>
</div>
@endsection
