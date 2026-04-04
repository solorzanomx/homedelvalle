@extends('layouts.app-sidebar')
@section('title', 'Nueva Operacion')

@section('styles')
<style>
.section-title {
    font-size: 0.9rem; font-weight: 600; color: var(--text);
    margin: 1.5rem 0 0.75rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--border);
}
.section-title:first-child { margin-top: 0; }
.type-cards { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 0.5rem; }
.type-card {
    position: relative; border: 2px solid var(--border); border-radius: var(--radius);
    padding: 1.25rem; text-align: center; cursor: pointer; transition: all 0.2s;
    background: var(--card);
}
.type-card:hover { border-color: var(--primary); background: rgba(59,130,196,0.03); }
.type-card.selected { border-color: var(--primary); background: rgba(59,130,196,0.06); box-shadow: 0 0 0 3px rgba(59,130,196,0.12); }
.type-card input[type="radio"] { position: absolute; opacity: 0; pointer-events: none; }
.type-card .type-icon { font-size: 1.5rem; margin-bottom: 0.4rem; }
.type-card .type-label { font-size: 0.9rem; font-weight: 600; }
.type-card .type-desc { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem; }
.conditional-field { display: none; }
.conditional-field.visible { display: block; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div><h2>Nueva Operacion</h2></div>
    <a href="{{ route('operations.index') }}" class="btn btn-outline">Volver</a>
</div>

<div class="card" style="max-width:800px;">
    <div class="card-body">
        @if($errors->any())
            <div style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); border-radius:var(--radius); padding:0.75rem 1rem; margin-bottom:1.25rem;">
                @foreach($errors->all() as $error)
                    <p style="color:var(--danger); font-size:0.82rem; margin:0.15rem 0;">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('operations.store') }}" id="operationForm">
            @csrf

            {{-- Tipo de Operacion --}}
            <div class="section-title" style="margin-top:0;">Tipo de Operacion</div>
            <div class="type-cards">
                <label class="type-card {{ old('type') === 'venta' ? 'selected' : '' }}" id="cardVenta">
                    <input type="radio" name="type" value="venta" {{ old('type') === 'venta' ? 'checked' : '' }} required onchange="onTypeChange(this.value)">
                    <div class="type-icon">&#9830;</div>
                    <div class="type-label">Venta</div>
                    <div class="type-desc">Compraventa de propiedad</div>
                </label>
                <label class="type-card {{ old('type') === 'renta' ? 'selected' : '' }}" id="cardRenta">
                    <input type="radio" name="type" value="renta" {{ old('type') === 'renta' ? 'checked' : '' }} required onchange="onTypeChange(this.value)">
                    <div class="type-icon">&#127968;</div>
                    <div class="type-label">Renta</div>
                    <div class="type-desc">Arrendamiento de propiedad</div>
                </label>
                <label class="type-card {{ old('type') === 'captacion' ? 'selected' : '' }}" id="cardCaptacion">
                    <input type="radio" name="type" value="captacion" {{ old('type') === 'captacion' ? 'checked' : '' }} required onchange="onTypeChange(this.value)">
                    <div class="type-icon">&#128204;</div>
                    <div class="type-label">Captacion</div>
                    <div class="type-desc">Alta de propiedad al portafolio</div>
                </label>
            </div>
            @error('type') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror

            {{-- Target Type (solo captacion) --}}
            <div class="conditional-field" id="fieldTargetType">
                <div class="form-group" style="margin-top:0.75rem;">
                    <label class="form-label">Destino de la captacion <span class="required">*</span></label>
                    <select name="target_type" class="form-select">
                        <option value="">-- Seleccionar --</option>
                        <option value="venta" {{ old('target_type') === 'venta' ? 'selected' : '' }}>Venta</option>
                        <option value="renta" {{ old('target_type') === 'renta' ? 'selected' : '' }}>Renta</option>
                    </select>
                    <p class="form-hint">Al completar la captacion, se creara automaticamente una operacion de este tipo.</p>
                    @error('target_type') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Informacion General --}}
            <div class="section-title">Informacion General</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Propiedad</label>
                    <select name="property_id" class="form-select">
                        <option value="">-- Seleccionar --</option>
                        @foreach($properties as $property)
                            <option value="{{ $property->id }}" {{ old('property_id') == $property->id ? 'selected' : '' }}>{{ $property->title }}</option>
                        @endforeach
                    </select>
                    @error('property_id') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Cliente Principal <span class="required">*</span></label>
                    <select name="client_id" class="form-select" required>
                        <option value="">-- Seleccionar --</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                        @endforeach
                    </select>
                    @error('client_id') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" id="secondaryClientLabel">Comprador</label>
                    <select name="secondary_client_id" class="form-select">
                        <option value="">-- Ninguno --</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('secondary_client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                        @endforeach
                    </select>
                    @error('secondary_client_id') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Broker Asignado</label>
                    <select name="broker_id" class="form-select">
                        <option value="">-- Sin asignar --</option>
                        @foreach($brokers as $broker)
                            <option value="{{ $broker->id }}" {{ old('broker_id') == $broker->id ? 'selected' : '' }}>{{ $broker->name }}</option>
                        @endforeach
                    </select>
                    @error('broker_id') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Datos Financieros --}}
            <div class="section-title">Datos Financieros</div>
            <div class="form-grid">
                {{-- Venta fields --}}
                <div class="form-group conditional-field" id="fieldAmount">
                    <label class="form-label" id="labelAmount">Monto de Venta</label>
                    <input type="number" name="amount" class="form-input" value="{{ old('amount') }}" min="0" step="0.01" placeholder="0.00">
                    @error('amount') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                </div>

                {{-- Renta fields --}}
                <div class="form-group conditional-field" id="fieldMonthlyRent">
                    <label class="form-label">Renta Mensual</label>
                    <input type="number" name="monthly_rent" class="form-input" value="{{ old('monthly_rent') }}" min="0" step="0.01" placeholder="0.00">
                    @error('monthly_rent') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                </div>
                <div class="form-group conditional-field" id="fieldDeposit">
                    <label class="form-label">Deposito</label>
                    <input type="number" name="deposit_amount" class="form-input" value="{{ old('deposit_amount') }}" min="0" step="0.01" placeholder="0.00">
                    @error('deposit_amount') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                </div>
                <div class="form-group conditional-field" id="fieldGuarantee">
                    <label class="form-label">Tipo de Garantia</label>
                    <select name="guarantee_type" class="form-select">
                        <option value="">-- Seleccionar --</option>
                        @foreach(\App\Models\Operation::GUARANTEE_TYPES as $val => $label)
                            <option value="{{ $val }}" {{ old('guarantee_type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('guarantee_type') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                </div>

                {{-- Common fields --}}
                <div class="form-group">
                    <label class="form-label">Comision ($)</label>
                    <input type="number" name="commission_amount" class="form-input" value="{{ old('commission_amount') }}" min="0" step="0.01" placeholder="0.00">
                    @error('commission_amount') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Comision (%)</label>
                    <input type="number" name="commission_percentage" class="form-input" value="{{ old('commission_percentage') }}" min="0" max="100" step="0.01" placeholder="0.00">
                    @error('commission_percentage') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Moneda</label>
                    <select name="currency" class="form-select">
                        <option value="MXN" {{ old('currency', 'MXN') === 'MXN' ? 'selected' : '' }}>MXN</option>
                        <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD</option>
                    </select>
                    @error('currency') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha Estimada de Cierre</label>
                    <input type="date" name="expected_close_date" class="form-input" value="{{ old('expected_close_date') }}">
                    @error('expected_close_date') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Notas --}}
            <div class="section-title">Notas</div>
            <div class="form-group">
                <textarea name="notes" class="form-textarea" rows="4" placeholder="Notas adicionales sobre la operacion...">{{ old('notes') }}</textarea>
                @error('notes') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
            </div>

            <div class="form-actions">
                <a href="{{ route('operations.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">Crear Operacion</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function onTypeChange(type) {
    // Update card selection
    document.getElementById('cardVenta').classList.toggle('selected', type === 'venta');
    document.getElementById('cardRenta').classList.toggle('selected', type === 'renta');
    document.getElementById('cardCaptacion').classList.toggle('selected', type === 'captacion');

    // Secondary client label
    var label = document.getElementById('secondaryClientLabel');
    label.textContent = type === 'renta' ? 'Inquilino' : (type === 'captacion' ? 'Copropietario' : 'Comprador');

    // Target type (captacion only)
    var fieldTarget = document.getElementById('fieldTargetType');
    if (fieldTarget) fieldTarget.classList.toggle('visible', type === 'captacion');

    // Venta-only fields
    var ventaFields = ['fieldAmount'];
    ventaFields.forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.classList.toggle('visible', type === 'venta' || type === 'captacion');
    });

    // Amount label
    var labelAmount = document.getElementById('labelAmount');
    if (labelAmount) labelAmount.textContent = type === 'captacion' ? 'Valor Estimado' : 'Monto de Venta';

    // Renta-only fields
    var rentaFields = ['fieldMonthlyRent', 'fieldDeposit', 'fieldGuarantee'];
    rentaFields.forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.classList.toggle('visible', type === 'renta');
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    var checked = document.querySelector('input[name="type"]:checked');
    if (checked) {
        onTypeChange(checked.value);
    }
});
</script>
@endsection
