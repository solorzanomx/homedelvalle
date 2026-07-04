@extends('layouts.app-sidebar')
@section('title', 'Editar Proveedor')

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
    <div><h2>Editar Proveedor</h2></div>
    <a href="{{ route('providers.show', $company) }}" class="btn btn-outline">Volver</a>
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

        <form method="POST" action="{{ route('providers.update', $company) }}">
            @csrf
            @method('PUT')

            <div class="section-title" style="margin-top:0;">Datos del Proveedor</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nombre <span class="required">*</span></label>
                    <input type="text" name="name" class="form-input" value="{{ old('name', $company->name) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo <span class="required">*</span></label>
                    <select name="type" class="form-select" required>
                        <option value="">-- Seleccionar --</option>
                        @foreach(\App\Models\ProviderCompany::TYPES as $val => $label)
                            <option value="{{ $val }}" {{ old('type', $company->type) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Persona de Contacto</label>
                    <input type="text" name="contact_name" class="form-input" value="{{ old('contact_name', $company->contact_name) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" value="{{ old('email', $company->email) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Telefono</label>
                    <input type="tel" name="phone" class="form-input" value="{{ old('phone', $company->phone) }}">
                </div>
            </div>

            <div class="section-title">Ubicacion</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Direccion</label>
                    <input type="text" name="address" class="form-input" value="{{ old('address', $company->address) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Ciudad</label>
                    <input type="text" name="city" class="form-input" value="{{ old('city', $company->city) }}">
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
                <a href="{{ route('providers.show', $company) }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </form>

        <div style="margin-top:1.5rem; padding-top:1.25rem; border-top:1px solid var(--border);">
            <form method="POST" action="{{ route('providers.destroy', $company) }}" onsubmit="return confirm('¿Eliminar este proveedor? Sus contactos y registros de cobros vinculados también se eliminarán.')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Eliminar Proveedor</button>
            </form>
        </div>
    </div>
</div>
@endsection
