@extends('layouts.app-sidebar')
@section('title', 'Nuevo Lead')

@section('content')
<div class="page-header">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;margin:0">Nuevo Lead</h1>
        <p style="color:var(--text-muted);font-size:0.85rem;margin-top:0.25rem">
            Alta manual — para leads que llegan por un canal sin integración automática (correo, llamada, etc.)
        </p>
    </div>
    <a href="{{ route('admin.form-submissions.index') }}" class="btn btn-outline">← Volver</a>
</div>

<form method="POST" action="{{ route('admin.form-submissions.store') }}">
    @csrf

    <div class="card">
        <div class="card-header"><h3>Lo esencial</h3></div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Origen del lead <span class="required">*</span></label>
                    <select name="form_type" class="form-select" required>
                        <option value="inmuebles24" selected>Inmuebles24 (correo, sin API)</option>
                        <option value="easybroker">EasyBroker</option>
                        <option value="vendedor">Vendedor / Valuación</option>
                        <option value="vendedor_predio">Predio → Desarrolladora</option>
                        <option value="comprador">Comprador / Búsqueda</option>
                        <option value="b2b">Desarrollador / Inversionista</option>
                        <option value="contacto">Contacto general</option>
                    </select>
                    @error('form_type') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Etiqueta (opcional)</label>
                    <input type="text" name="lead_tag" class="form-input" value="{{ old('lead_tag') }}" placeholder="Ej. LEAD_INMUEBLES24, LEAD_BROKER">
                    <p class="form-hint">Déjalo vacío si no aplica. Usa LEAD_BROKER si sospechas que es una agencia — así queda visible antes de convertir.</p>
                </div>
                <div class="form-group">
                    <label class="form-label">Nombre <span class="required">*</span></label>
                    <input type="text" name="full_name" class="form-input" value="{{ old('full_name') }}" required>
                    @error('full_name') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Email <span class="required">*</span></label>
                    <input type="email" name="email" class="form-input" value="{{ old('email') }}" required>
                    @error('email') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="phone" class="form-input" value="{{ old('phone') }}" placeholder="10 dígitos">
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo de interés</label>
                    <select name="client_type" class="form-select">
                        <option value="">Sin definir</option>
                        <option value="buyer">Comprador</option>
                        <option value="renter">Inquilino</option>
                        <option value="owner">Propietario</option>
                        <option value="investor">Inversionista</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Temperatura</label>
                    <select name="lead_temperature" class="form-select">
                        <option value="hot" selected>Caliente</option>
                        <option value="warm">Tibio</option>
                        <option value="cold">Frío</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Presupuesto mínimo</label>
                    <input type="number" name="budget_min" class="form-input" value="{{ old('budget_min') }}" step="1">
                </div>
                <div class="form-group">
                    <label class="form-label">Presupuesto máximo</label>
                    <input type="number" name="budget_max" class="form-input" value="{{ old('budget_max') }}" step="1">
                </div>
                <div class="form-group full-width">
                    <label class="form-label">Tipo de propiedad</label>
                    <input type="text" name="property_type" class="form-input" value="{{ old('property_type') }}" placeholder="Ej. Departamento en Venta">
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top:1.5rem;">
        <div class="card-header"><h3>Aviso que consultó <span style="font-weight:400;font-size:0.8rem;color:var(--text-muted)">(opcional — portales sin API, ej. Inmuebles24)</span></h3></div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label class="form-label">Título del aviso</label>
                    <input type="text" name="titulo_aviso" class="form-input" value="{{ old('titulo_aviso') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo de operación</label>
                    <input type="text" name="tipo_operacion" class="form-input" value="{{ old('tipo_operacion') }}" placeholder="Venta / Renta">
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo de propiedad del aviso</label>
                    <input type="text" name="tipo_propiedad" class="form-input" value="{{ old('tipo_propiedad') }}" placeholder="Departamento / Casa...">
                </div>
                <div class="form-group">
                    <label class="form-label">Precio del aviso</label>
                    <input type="text" name="precio" class="form-input" value="{{ old('precio') }}" placeholder="MN 4,890,000">
                </div>
                <div class="form-group">
                    <label class="form-label">Ubicación</label>
                    <input type="text" name="ubicacion" class="form-input" value="{{ old('ubicacion') }}" placeholder="Colonia, Alcaldía">
                </div>
                <div class="form-group">
                    <label class="form-label">Código de aviso</label>
                    <input type="text" name="codigo_aviso" class="form-input" value="{{ old('codigo_aviso') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Código de anunciante</label>
                    <input type="text" name="codigo_anunciante" class="form-input" value="{{ old('codigo_anunciante') }}">
                </div>
                <div class="form-group full-width">
                    <label class="form-label">REF del correo</label>
                    <input type="text" name="ref" class="form-input" value="{{ old('ref') }}">
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top:1.5rem;">
        <div class="card-header"><h3>Lo que busca <span style="font-weight:400;font-size:0.8rem;color:var(--text-muted)">(opcional)</span></h3></div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label class="form-label">Tipo que busca</label>
                    <input type="text" name="busca_tipo" class="form-input" value="{{ old('busca_tipo') }}" placeholder="Ej. Departamento en Venta">
                </div>
                <div class="form-group full-width">
                    <label class="form-label">Presupuesto que busca (texto libre)</label>
                    <input type="text" name="busca_presupuesto" class="form-input" value="{{ old('busca_presupuesto') }}" placeholder="MXN 4.89M - MXN 5.19M">
                </div>
                <div class="form-group full-width">
                    <label class="form-label">Zonas de interés</label>
                    <input type="text" name="busca_zonas" class="form-input" value="{{ old('busca_zonas') }}" placeholder="Separadas por coma: Narvarte Poniente, Del Valle Centro">
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top:1.5rem;">
        <div class="card-header"><h3>Notas</h3></div>
        <div class="card-body">
            <textarea name="notes" class="form-textarea" rows="5" placeholder="Contexto, observaciones, inconsistencias detectadas...">{{ old('notes') }}</textarea>
            @error('notes') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="form-actions" style="margin-top:1.5rem;">
        <a href="{{ route('admin.form-submissions.index') }}" class="btn btn-outline">Cancelar</a>
        <button type="submit" class="btn btn-primary">Crear Lead</button>
    </div>
</form>
@endsection
