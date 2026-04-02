@extends('layouts.app-sidebar')
@section('title', 'Editar Renta #' . $rental->id)

@section('content')
<div class="page-header">
    <div>
        <h2>Editar Renta #{{ $rental->id }}</h2>
        <p class="text-muted">{{ $rental->property->title ?? 'Sin propiedad' }} &mdash; {{ $rental->ownerClient->name ?? '' }}</p>
    </div>
    <div style="display:flex; gap:0.5rem;">
        <a href="{{ route('rentals.show', $rental) }}" class="btn btn-outline">Ver Detalle</a>
        <a href="{{ route('rentals.index') }}" class="btn btn-outline">&#8592; Rentas</a>
    </div>
</div>

<div style="max-width:700px;">
    <div class="card">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-error" style="margin-bottom:1.25rem;">
                    <div>
                        <strong>Errores en el formulario:</strong>
                        <ul style="margin:0.5rem 0 0 1.25rem; font-size:0.85rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ route('rentals.update', $rental) }}" method="POST">
                @csrf @method('PUT')

                {{-- Propiedad y Relaciones --}}
                <h4 style="font-size:0.88rem; font-weight:600; margin-bottom:0.75rem; padding-bottom:0.5rem; border-bottom:1px solid var(--border);">Propiedad y Relaciones</h4>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Propiedad <span class="required">*</span></label>
                        <select name="property_id" class="form-select" required>
                            <option value="">Seleccionar propiedad...</option>
                            @foreach($properties as $property)
                                <option value="{{ $property->id }}" {{ old('property_id', $rental->property_id) == $property->id ? 'selected' : '' }}>{{ $property->title ?? 'Propiedad #'.$property->id }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Propietario</label>
                        <select name="owner_client_id" class="form-select">
                            <option value="">Seleccionar cliente...</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('owner_client_id', $rental->owner_client_id) == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Inquilino</label>
                        <select name="tenant_client_id" class="form-select">
                            <option value="">Seleccionar inquilino...</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('tenant_client_id', $rental->tenant_client_id) == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Broker</label>
                        <select name="broker_id" class="form-select">
                            <option value="">Sin broker asignado</option>
                            @foreach($brokers as $broker)
                                <option value="{{ $broker->id }}" {{ old('broker_id', $rental->broker_id) == $broker->id ? 'selected' : '' }}>{{ $broker->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Condiciones de la Renta --}}
                <h4 style="font-size:0.88rem; font-weight:600; margin:1.25rem 0 0.75rem; padding-bottom:0.5rem; border-bottom:1px solid var(--border);">Condiciones de la Renta</h4>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Renta Mensual</label>
                        <input type="number" name="monthly_rent" value="{{ old('monthly_rent', $rental->monthly_rent) }}" class="form-input" step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Moneda</label>
                        <select name="currency" class="form-select">
                            <option value="MXN" {{ old('currency', $rental->currency) === 'MXN' ? 'selected' : '' }}>MXN</option>
                            <option value="USD" {{ old('currency', $rental->currency) === 'USD' ? 'selected' : '' }}>USD</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Deposito</label>
                        <input type="number" name="deposit_amount" value="{{ old('deposit_amount', $rental->deposit_amount) }}" class="form-input" step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tipo de Garantia</label>
                        <select name="guarantee_type" class="form-select">
                            <option value="">Seleccionar...</option>
                            <option value="deposito" {{ old('guarantee_type', $rental->guarantee_type) === 'deposito' ? 'selected' : '' }}>Deposito</option>
                            <option value="poliza_juridica" {{ old('guarantee_type', $rental->guarantee_type) === 'poliza_juridica' ? 'selected' : '' }}>Poliza Juridica</option>
                            <option value="fianza" {{ old('guarantee_type', $rental->guarantee_type) === 'fianza' ? 'selected' : '' }}>Fianza</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Comision ($)</label>
                        <input type="number" name="commission_amount" value="{{ old('commission_amount', $rental->commission_amount) }}" class="form-input" step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Comision (%)</label>
                        <input type="number" name="commission_percentage" value="{{ old('commission_percentage', $rental->commission_percentage) }}" class="form-input" step="0.01" min="0" max="100" placeholder="0.00">
                    </div>
                </div>

                {{-- Fechas del Contrato --}}
                <h4 style="font-size:0.88rem; font-weight:600; margin:1.25rem 0 0.75rem; padding-bottom:0.5rem; border-bottom:1px solid var(--border);">Fechas del Contrato</h4>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Inicio del Contrato</label>
                        <input type="date" name="lease_start_date" value="{{ old('lease_start_date', $rental->lease_start_date?->format('Y-m-d')) }}" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fin del Contrato</label>
                        <input type="date" name="lease_end_date" value="{{ old('lease_end_date', $rental->lease_end_date?->format('Y-m-d')) }}" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Duracion (meses)</label>
                        <input type="number" name="lease_duration_months" value="{{ old('lease_duration_months', $rental->lease_duration_months) }}" class="form-input" min="1" placeholder="12">
                    </div>
                </div>

                {{-- Notas --}}
                <h4 style="font-size:0.88rem; font-weight:600; margin:1.25rem 0 0.75rem; padding-bottom:0.5rem; border-bottom:1px solid var(--border);">Notas</h4>
                <div class="form-group">
                    <textarea name="notes" class="form-textarea" rows="4" placeholder="Notas adicionales...">{{ old('notes', $rental->notes) }}</textarea>
                </div>

                {{-- Timestamps --}}
                <div style="font-size:0.78rem; color:var(--text-muted); margin-top:1rem; padding-top:0.75rem; border-top:1px solid var(--border);">
                    <div>Creado: {{ $rental->created_at->format('d/m/Y H:i') }}</div>
                    <div>Actualizado: {{ $rental->updated_at->format('d/m/Y H:i') }}</div>
                    @if($rental->completed_at)
                        <div>Completado: {{ $rental->completed_at->format('d/m/Y H:i') }}</div>
                    @endif
                </div>

                <div class="form-actions">
                    <form method="POST" action="{{ route('rentals.destroy', $rental) }}" onsubmit="return confirm('Eliminar este proceso de renta?')" style="margin-right:auto;">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                    <a href="{{ route('rentals.show', $rental) }}" class="btn btn-outline">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
