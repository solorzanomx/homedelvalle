@extends('layouts.app-sidebar')
@section('title', isset($transaction) ? 'Editar Transaccion' : 'Nueva Transaccion')

@section('styles')
<style>
.tx-form-card {
    background: var(--card); border: 1px solid var(--border); border-radius: 10px;
    max-width: 720px; overflow: hidden;
}
.tx-form-header {
    padding: 1rem 1.5rem; border-bottom: 1px solid var(--border);
    display: flex; justify-content: space-between; align-items: center;
}
.tx-form-header h3 { font-size: 1rem; font-weight: 600; }
.tx-form-body { padding: 1.5rem; }

.section-label {
    font-size: 0.78rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;
    letter-spacing: 0.5px; margin: 1.5rem 0 0.75rem; padding-bottom: 0.4rem;
    border-bottom: 1px solid var(--border);
}
.section-label:first-child { margin-top: 0; }

/* Type cards */
.type-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1.25rem; }
.type-card {
    padding: 1rem; border-radius: var(--radius); border: 2px solid var(--border);
    text-align: center; cursor: pointer; transition: all 0.15s; position: relative;
}
.type-card:hover { border-color: var(--primary); }
.type-card.active-income { border-color: #10b981; background: #ecfdf5; }
.type-card.active-expense { border-color: #ef4444; background: #fef2f2; }
.type-card input { position: absolute; opacity: 0; pointer-events: none; }
.type-card-icon { font-size: 1.5rem; margin-bottom: 0.25rem; }
.type-card-label { font-size: 0.85rem; font-weight: 600; }
.type-card-desc { font-size: 0.72rem; color: var(--text-muted); }
</style>
@endsection

@section('content')
<div style="margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
    <a href="{{ route('admin.finance.dashboard') }}" style="font-size:0.82rem; color:var(--text-muted);">Finanzas</a>
    <span style="color:var(--text-muted); font-size:0.75rem;">/</span>
    <a href="{{ route('admin.finance.transactions') }}" style="font-size:0.82rem; color:var(--text-muted);">Transacciones</a>
    <span style="color:var(--text-muted); font-size:0.75rem;">/</span>
    <span style="font-size:0.82rem; color:var(--text);">{{ isset($transaction) ? 'Editar' : 'Nueva' }}</span>
</div>

<div class="tx-form-card">
    <div class="tx-form-header">
        <h3>{{ isset($transaction) ? 'Editar Transaccion' : 'Nueva Transaccion' }}</h3>
    </div>
    <div class="tx-form-body">
        @if($errors->any())
            <div class="alert alert-error" style="margin-bottom:1rem;">
                <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
            </div>
        @endif

        <form action="{{ isset($transaction) ? route('admin.finance.transactions.update', $transaction->id) : route('admin.finance.transactions.store') }}" method="POST">
            @csrf
            @if(isset($transaction)) @method('PUT') @endif

            {{-- Type --}}
            <div class="section-label" style="margin-top:0;">Tipo de Transaccion</div>
            <div class="type-cards">
                <label class="type-card {{ old('type', $transaction->type ?? '') === 'income' ? 'active-income' : '' }}" id="card-income"
                       onclick="document.querySelectorAll('.type-card').forEach(c=>{c.className='type-card'}); this.classList.add('type-card','active-income');">
                    <input type="radio" name="type" value="income" {{ old('type', $transaction->type ?? '') === 'income' ? 'checked' : '' }}>
                    <div class="type-card-icon">&#9650;</div>
                    <div class="type-card-label" style="color:#065f46;">Ingreso</div>
                    <div class="type-card-desc">Dinero que entra</div>
                </label>
                <label class="type-card {{ old('type', $transaction->type ?? '') === 'expense' ? 'active-expense' : '' }}" id="card-expense"
                       onclick="document.querySelectorAll('.type-card').forEach(c=>{c.className='type-card'}); this.classList.add('type-card','active-expense');">
                    <input type="radio" name="type" value="expense" {{ old('type', $transaction->type ?? '') === 'expense' ? 'checked' : '' }}>
                    <div class="type-card-icon">&#9660;</div>
                    <div class="type-card-label" style="color:#991b1b;">Egreso</div>
                    <div class="type-card-desc">Dinero que sale</div>
                </label>
            </div>

            {{-- Details --}}
            <div class="section-label">Detalle</div>
            <div class="form-grid">
                <div class="form-group full-width">
                    <label class="form-label">Descripcion <span class="required">*</span></label>
                    <input type="text" name="description" class="form-input" value="{{ old('description', $transaction->description ?? '') }}" maxlength="255" required placeholder="Describe la transaccion...">
                </div>
                <div class="form-group">
                    <label class="form-label">Categoria <span class="required">*</span></label>
                    <select name="category" class="form-select">
                        <option value="">Seleccionar...</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" {{ old('category', $transaction->category ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Metodo de Pago <span class="required">*</span></label>
                    <select name="payment_method" class="form-select">
                        @php $pmethods = ['cash'=>'Efectivo','transfer'=>'Transferencia','check'=>'Cheque','card'=>'Tarjeta','other'=>'Otro']; @endphp
                        @foreach($pmethods as $key => $label)
                            <option value="{{ $key }}" {{ old('payment_method', $transaction->payment_method ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Monto <span class="required">*</span></label>
                    <input type="number" name="amount" class="form-input" step="0.01" min="0.01" value="{{ old('amount', $transaction->amount ?? '') }}" required placeholder="0.00">
                </div>
                <div class="form-group">
                    <label class="form-label">Moneda <span class="required">*</span></label>
                    <select name="currency" class="form-select">
                        <option value="MXN" {{ old('currency', $transaction->currency ?? 'MXN') === 'MXN' ? 'selected' : '' }}>MXN</option>
                        <option value="USD" {{ old('currency', $transaction->currency ?? 'MXN') === 'USD' ? 'selected' : '' }}>USD</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha <span class="required">*</span></label>
                    <input type="date" name="date" class="form-input" value="{{ old('date', isset($transaction) ? $transaction->date->format('Y-m-d') : date('Y-m-d')) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Referencia</label>
                    <input type="text" name="reference" class="form-input" value="{{ old('reference', $transaction->reference ?? '') }}" maxlength="100" placeholder="No. cheque, ref. bancaria...">
                </div>
            </div>

            {{-- Relations --}}
            <div class="section-label">Relaciones (opcional)</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Deal</label>
                    <select name="deal_id" class="form-select">
                        <option value="">Ninguno</option>
                        @foreach($deals as $deal)
                            <option value="{{ $deal->id }}" {{ old('deal_id', $transaction->deal_id ?? '') == $deal->id ? 'selected' : '' }}>
                                Deal #{{ $deal->id }} - {{ $deal->title ?? ($deal->property->title ?? '') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Propiedad</label>
                    <select name="property_id" class="form-select">
                        <option value="">Ninguna</option>
                        @foreach($properties as $property)
                            <option value="{{ $property->id }}" {{ old('property_id', $transaction->property_id ?? '') == $property->id ? 'selected' : '' }}>
                                {{ $property->title ?? 'Propiedad #' . $property->id }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Broker</label>
                    <select name="broker_id" class="form-select">
                        <option value="">Ninguno</option>
                        @foreach($brokers as $broker)
                            <option value="{{ $broker->id }}" {{ old('broker_id', $transaction->broker_id ?? '') == $broker->id ? 'selected' : '' }}>
                                {{ $broker->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Notes --}}
            <div class="section-label">Notas</div>
            <div class="form-group">
                <textarea name="notes" class="form-textarea" rows="3" placeholder="Notas adicionales...">{{ old('notes', $transaction->notes ?? '') }}</textarea>
            </div>

            <div class="form-actions">
                @if(isset($transaction))
                    <form method="POST" action="{{ route('admin.finance.transactions.destroy', $transaction->id) }}" onsubmit="return confirm('Eliminar esta transaccion?')" style="margin-right:auto;">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                @endif
                <a href="{{ route('admin.finance.transactions') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    {{ isset($transaction) ? 'Actualizar' : 'Registrar' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
