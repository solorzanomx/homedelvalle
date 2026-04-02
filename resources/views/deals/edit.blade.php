@extends('layouts.app-sidebar')
@section('title', 'Editar Deal')

@section('content')
<div class="page-header">
    <div>
        <h2>Editar Deal #{{ $deal->id }}</h2>
        <p class="text-muted">{{ $deal->client->name ?? '' }} &mdash; {{ $deal->property->title ?? '' }}</p>
    </div>
    <a href="{{ route('deals.index') }}" class="btn btn-outline">&#8592; Volver a Deals</a>
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

            <form action="{{ route('deals.update', $deal->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Relaciones --}}
                <h4 style="font-size:0.88rem; font-weight:600; margin-bottom:0.75rem; padding-bottom:0.5rem; border-bottom:1px solid var(--border);">Relaciones</h4>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Propiedad <span class="required">*</span></label>
                        <select name="property_id" class="form-select" required>
                            <option value="">Seleccionar propiedad...</option>
                            @foreach($properties as $property)
                                <option value="{{ $property->id }}" {{ old('property_id', $deal->property_id) == $property->id ? 'selected' : '' }}>{{ $property->title ?? 'Propiedad #'.$property->id }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Cliente <span class="required">*</span></label>
                        <select name="client_id" class="form-select" required>
                            <option value="">Seleccionar cliente...</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id', $deal->client_id) == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label">Broker</label>
                        <select name="broker_id" class="form-select">
                            <option value="">Sin broker asignado</option>
                            @foreach($brokers as $broker)
                                <option value="{{ $broker->id }}" {{ old('broker_id', $deal->broker_id) == $broker->id ? 'selected' : '' }}>{{ $broker->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-hint">Opcional. Puedes asignar un broker despues.</div>
                    </div>
                </div>

                {{-- Detalle --}}
                <h4 style="font-size:0.88rem; font-weight:600; margin:1.25rem 0 0.75rem; padding-bottom:0.5rem; border-bottom:1px solid var(--border);">Detalle</h4>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Etapa <span class="required">*</span></label>
                        <select name="stage" class="form-select" required>
                            @foreach($stages as $key => $label)
                                <option value="{{ $key }}" {{ old('stage', $deal->stage) === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Monto <span class="required">*</span></label>
                        <input type="number" name="amount" value="{{ old('amount', $deal->amount) }}" class="form-input" step="0.01" min="0" required placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Comision</label>
                        <input type="number" name="commission_amount" value="{{ old('commission_amount', $deal->commission_amount) }}" class="form-input" step="0.01" min="0" placeholder="0.00">
                        <div class="form-hint">Monto de comision del deal</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fecha Esperada de Cierre</label>
                        <input type="date" name="expected_close_date" value="{{ old('expected_close_date', $deal->expected_close_date ? $deal->expected_close_date->format('Y-m-d') : '') }}" class="form-input">
                    </div>
                </div>

                {{-- Notas --}}
                <h4 style="font-size:0.88rem; font-weight:600; margin:1.25rem 0 0.75rem; padding-bottom:0.5rem; border-bottom:1px solid var(--border);">Notas</h4>
                <div class="form-group">
                    <textarea name="notes" class="form-textarea" rows="4" placeholder="Notas adicionales sobre este deal...">{{ old('notes', $deal->notes) }}</textarea>
                </div>

                {{-- Timestamps --}}
                <div style="background:var(--bg); border-radius:var(--radius); padding:0.75rem 1rem; margin-top:1rem; font-size:0.78rem; color:var(--text-muted);">
                    <div style="display:flex; gap:1.5rem; flex-wrap:wrap;">
                        <span>Creado: {{ $deal->created_at->format('d/m/Y H:i') }}</span>
                        <span>Actualizado: {{ $deal->updated_at->format('d/m/Y H:i') }}</span>
                        @if($deal->closed_at)
                            <span style="color: {{ in_array($deal->stage, ['won']) ? 'var(--success)' : 'var(--danger)' }}; font-weight:500;">
                                Cerrado: {{ $deal->closed_at->format('d/m/Y H:i') }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="form-actions">
                    <form method="POST" action="{{ route('deals.destroy', $deal->id) }}" onsubmit="return confirm('Eliminar este deal?')" style="margin-right:auto;">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                    <a href="{{ route('deals.index') }}" class="btn btn-outline">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
