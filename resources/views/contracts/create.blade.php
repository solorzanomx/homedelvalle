@extends('layouts.app-sidebar')
@section('title', 'Nuevo Contrato')

@section('content')
<div class="page-header">
    <div>
        <h2>Nuevo Contrato</h2>
        <p class="text-muted">Elige el trato, la plantilla y genera el contrato</p>
    </div>
    <a href="{{ route('contracts.index') }}" class="btn btn-outline">&#8592; Volver</a>
</div>

@if ($errors->any())
    <div class="alert alert-error" style="margin-bottom:1.25rem;">
        <div>
            <strong>Errores:</strong>
            <ul style="margin:0.5rem 0 0 1.25rem; font-size:0.85rem;">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="card">
    <div class="card-body">
        @if($operations->isEmpty() && $rentals->isEmpty())
            <p style="font-size:0.85rem; color:var(--text-muted);">No hay Operaciones de venta ni Rentas activas para asociar un contrato.</p>
        @elseif($contractTemplates->isEmpty())
            <p style="font-size:0.85rem; color:var(--text-muted);">Sin plantillas. <a href="{{ route('admin.contract-templates.create') }}" style="color:var(--primary);">Crear una plantilla primero</a>.</p>
        @else
        <form method="POST" action="{{ route('contracts.store') }}">
            @csrf
            <div class="form-grid">
                <div class="form-group full-width">
                    <label class="form-label">Trato <span class="required">*</span></label>
                    <select name="deal" class="form-select" required>
                        <option value="">Selecciona un trato...</option>
                        @if($operations->isNotEmpty())
                        <optgroup label="Operaciones de venta">
                            @foreach($operations as $op)
                            <option value="operation:{{ $op->id }}" {{ old('deal') === 'operation:'.$op->id ? 'selected' : '' }}>
                                #{{ $op->id }} — {{ $op->client->name ?? 'Sin vendedor' }} — {{ $op->property->address ?? $op->property->title ?? 'Sin propiedad' }}
                            </option>
                            @endforeach
                        </optgroup>
                        @endif
                        @if($rentals->isNotEmpty())
                        <optgroup label="Rentas">
                            @foreach($rentals as $rental)
                            <option value="rental:{{ $rental->id }}" {{ old('deal') === 'rental:'.$rental->id ? 'selected' : '' }}>
                                #{{ $rental->id }} — {{ $rental->ownerClient->name ?? 'Sin propietario' }} — {{ $rental->property->address ?? $rental->property->title ?? 'Sin propiedad' }}
                            </option>
                            @endforeach
                        </optgroup>
                        @endif
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Plantilla <span class="required">*</span></label>
                    <select name="contract_template_id" class="form-select" required>
                        <option value="">Selecciona una plantilla...</option>
                        @foreach($contractTemplates as $tpl)
                        <option value="{{ $tpl->id }}" {{ old('contract_template_id') == $tpl->id ? 'selected' : '' }}>{{ $tpl->name }} ({{ $tpl->type_label }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Título <span class="required">*</span></label>
                    <input type="text" name="title" class="form-input" value="{{ old('title') }}" required placeholder="Ej: Contrato de Promesa de Compraventa">
                </div>
                <div class="form-group full-width">
                    <label class="form-label">Notas (opcional)</label>
                    <textarea name="notes" class="form-textarea" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>
            <div class="form-actions">
                <a href="{{ route('contracts.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">Generar Contrato</button>
            </div>
        </form>
        @endif
    </div>
</div>
@endsection
