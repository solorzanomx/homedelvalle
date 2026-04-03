@extends('layouts.app-sidebar')
@section('title', 'Editar Comisionista')

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
    <div><h2>Editar Comisionista</h2></div>
    <a href="{{ route('referrers.index') }}" class="btn btn-outline">Volver</a>
</div>

<div class="card" style="max-width:600px;">
    <div class="card-body">
        @if($errors->any())
            <div style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); border-radius:var(--radius); padding:0.75rem 1rem; margin-bottom:1.25rem;">
                @foreach($errors->all() as $error)
                    <p style="color:var(--danger); font-size:0.82rem; margin:0.15rem 0;">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('referrers.update', $referrer) }}">
            @csrf @method('PUT')

            <div class="section-title" style="margin-top:0;">Datos del Comisionista</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nombre <span class="required">*</span></label>
                    <input type="text" name="name" class="form-input" value="{{ old('name', $referrer->name) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Telefono</label>
                    <input type="tel" name="phone" class="form-input" value="{{ old('phone', $referrer->phone) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" value="{{ old('email', $referrer->email) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo <span class="required">*</span></label>
                    <select name="type" class="form-select" required>
                        @foreach(\App\Models\Referrer::TYPES as $val => $label)
                            <option value="{{ $val }}" {{ old('type', $referrer->type) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Direccion</label>
                    <input type="text" name="address" class="form-input" value="{{ old('address', $referrer->address) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-select">
                        <option value="active" {{ old('status', $referrer->status) === 'active' ? 'selected' : '' }}>Activo</option>
                        <option value="inactive" {{ old('status', $referrer->status) === 'inactive' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
            </div>

            <div class="section-title">Notas</div>
            <div class="form-group">
                <textarea name="notes" class="form-textarea" rows="3" placeholder="Notas...">{{ old('notes', $referrer->notes) }}</textarea>
            </div>

            <div class="form-actions">
                <a href="{{ route('referrers.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </form>

        <div style="margin-top:1rem; padding-top:0.75rem; border-top:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:0.72rem; color:var(--text-muted);">Creado {{ $referrer->created_at->format('d/m/Y') }}</span>
            <form method="POST" action="{{ route('referrers.destroy', $referrer) }}" onsubmit="return confirm('Eliminar este comisionista y todos sus referidos?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
            </form>
        </div>
    </div>
</div>
@endsection
