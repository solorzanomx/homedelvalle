@extends('layouts.app-sidebar')
@section('title', 'Nueva Renta')

@section('content')
<div class="page-header">
    <div>
        <h2>Nueva Renta</h2>
        <p class="text-muted">Iniciar un nuevo proceso de arrendamiento</p>
    </div>
    <a href="{{ route('rentals.index') }}" class="btn btn-outline">&#8592; Volver a Rentas</a>
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

            <form action="{{ route('rentals.store') }}" method="POST">
                @csrf

                {{-- Propiedad y Relaciones --}}
                <h4 style="font-size:0.88rem; font-weight:600; margin-bottom:0.75rem; padding-bottom:0.5rem; border-bottom:1px solid var(--border);">Propiedad y Relaciones</h4>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Propiedad <span class="required">*</span></label>
                        <select name="property_id" class="form-select" required>
                            <option value="">Seleccionar propiedad...</option>
                            @foreach($properties as $property)
                                <option value="{{ $property->id }}" {{ old('property_id') == $property->id ? 'selected' : '' }}>{{ $property->title ?? 'Propiedad #'.$property->id }}</option>
                            @endforeach
                        </select>
                        <div class="form-hint">Solo propiedades tipo renta</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Propietario</label>
                        <select name="owner_client_id" class="form-select">
                            <option value="">Seleccionar cliente...</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('owner_client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label">Broker</label>
                        <select name="broker_id" class="form-select">
                            <option value="">Sin broker asignado</option>
                            @foreach($brokers as $broker)
                                <option value="{{ $broker->id }}" {{ old('broker_id') == $broker->id ? 'selected' : '' }}>{{ $broker->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Condiciones de la Renta --}}
                <h4 style="font-size:0.88rem; font-weight:600; margin:1.25rem 0 0.75rem; padding-bottom:0.5rem; border-bottom:1px solid var(--border);">Condiciones de la Renta</h4>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Renta Mensual</label>
                        <input type="number" name="monthly_rent" value="{{ old('monthly_rent') }}" class="form-input" step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Moneda</label>
                        <select name="currency" class="form-select">
                            <option value="MXN" {{ old('currency', 'MXN') === 'MXN' ? 'selected' : '' }}>MXN</option>
                            <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Deposito</label>
                        <input type="number" name="deposit_amount" value="{{ old('deposit_amount') }}" class="form-input" step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tipo de Garantia</label>
                        <select name="guarantee_type" class="form-select">
                            <option value="">Seleccionar...</option>
                            <option value="deposito" {{ old('guarantee_type') === 'deposito' ? 'selected' : '' }}>Deposito</option>
                            <option value="poliza_juridica" {{ old('guarantee_type') === 'poliza_juridica' ? 'selected' : '' }}>Poliza Juridica</option>
                            <option value="fianza" {{ old('guarantee_type') === 'fianza' ? 'selected' : '' }}>Fianza</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Comision ($)</label>
                        <input type="number" name="commission_amount" value="{{ old('commission_amount') }}" class="form-input" step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Comision (%)</label>
                        <input type="number" name="commission_percentage" value="{{ old('commission_percentage') }}" class="form-input" step="0.01" min="0" max="100" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Duracion (meses)</label>
                        <input type="number" name="lease_duration_months" value="{{ old('lease_duration_months', 12) }}" class="form-input" min="1" placeholder="12">
                    </div>
                </div>

                {{-- Notas --}}
                <h4 style="font-size:0.88rem; font-weight:600; margin:1.25rem 0 0.75rem; padding-bottom:0.5rem; border-bottom:1px solid var(--border);">Notas</h4>
                <div class="form-group">
                    <textarea name="notes" class="form-textarea" rows="4" placeholder="Notas adicionales sobre este proceso de renta...">{{ old('notes') }}</textarea>
                </div>

                <div class="form-actions">
                    <a href="{{ route('rentals.index') }}" class="btn btn-outline">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Crear Proceso de Renta</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
