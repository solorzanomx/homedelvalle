@extends('layouts.app-sidebar')
@section('title', $contact ? 'Editar contacto' : 'Nuevo contacto')

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
    <div><h2>{{ $contact ? 'Editar contacto' : 'Nuevo contacto' }}</h2></div>
    <a href="{{ route('developer-contacts.index') }}" class="btn btn-outline">Volver</a>
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

        <form method="POST" action="{{ $contact ? route('developer-contacts.update', $contact) : route('developer-contacts.store') }}">
            @csrf
            @if($contact) @method('PUT') @endif

            <div class="section-title" style="margin-top:0;">Datos del contacto</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nombre <span class="required">*</span></label>
                    <input type="text" name="name" class="form-input" value="{{ old('name', $contact->name ?? '') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Empresa</label>
                    <select name="developer_company_id" class="form-select">
                        <option value="">Ninguna — inversionista independiente</option>
                        @foreach($companies as $c)
                            <option value="{{ $c->id }}" {{ (int) old('developer_company_id', $selectedCompanyId) === $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Puesto / Área</label>
                    <input type="text" name="role" class="form-input" value="{{ old('role', $contact->role ?? '') }}" placeholder="Ej. Adquisiciones, Dirección Técnica">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" value="{{ old('email', $contact->email ?? '') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono</label>
                    <input type="tel" name="phone" class="form-input" value="{{ old('phone', $contact->phone ?? '') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-select">
                        <option value="active" {{ old('status', $contact->status ?? 'active') === 'active' ? 'selected' : '' }}>Activo</option>
                        <option value="inactive" {{ old('status', $contact->status ?? '') === 'inactive' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
            </div>

            <div class="section-title">Qué está buscando</div>
            <div class="form-grid">
                <div class="form-group full-width">
                    <label class="form-label">Zonas de interés</label>
                    <input type="text" name="interest_zones" class="form-input"
                           value="{{ old('interest_zones', is_array($contact->interest_zones ?? null) ? implode(', ', $contact->interest_zones) : '') }}"
                           placeholder="Ej. Del Valle, Narvarte, Nápoles">
                    <p class="form-hint">Separadas por coma.</p>
                </div>
                <div class="form-group">
                    <label class="form-label">Presupuesto mínimo (MXN)</label>
                    <input type="number" name="budget_min" class="form-input" value="{{ old('budget_min', $contact->budget_min ?? '') }}" step="0.01" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Presupuesto máximo (MXN)</label>
                    <input type="number" name="budget_max" class="form-input" value="{{ old('budget_max', $contact->budget_max ?? '') }}" step="0.01" min="0">
                </div>
            </div>

            <div class="section-title">Notas</div>
            <div class="form-group">
                <textarea name="notes" class="form-textarea" rows="3">{{ old('notes', $contact->notes ?? '') }}</textarea>
            </div>

            <div class="form-actions">
                <a href="{{ route('developer-contacts.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">{{ $contact ? 'Guardar cambios' : 'Crear contacto' }}</button>
            </div>
        </form>

        @if($contact)
        <div style="margin-top:1.5rem; padding-top:1.25rem; border-top:1px solid var(--border);">
            <form method="POST" action="{{ route('developer-contacts.destroy', $contact) }}" onsubmit="return confirm('¿Eliminar este contacto?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Eliminar contacto</button>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection
