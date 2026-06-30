@extends('layouts.app-sidebar')
@section('title', 'Editar Renta #' . $rental->id)

@section('styles')
<style>
.rform-section { margin-bottom: 1.5rem; }
.rform-section h4 {
    font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;
    color: var(--text-muted); margin-bottom: 0.75rem; padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--border);
}
.calc-hint {
    font-size: 0.75rem; color: var(--text-muted); margin-top: 0.3rem;
    display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;
}
.calc-hint button {
    font-size: 0.72rem; padding: 1px 8px; border-radius: 999px;
    border: 1px solid var(--border); background: var(--bg); cursor: pointer;
    color: var(--primary); font-weight: 500; transition: background 0.15s;
}
.calc-hint button:hover { background: var(--primary); color: #fff; border-color: var(--primary); }
.calc-display {
    background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px;
    padding: 0.6rem 1rem; font-size: 0.82rem; color: #166534;
    display: flex; align-items: center; justify-content: space-between; gap: 1rem;
    margin-top: 0.5rem;
}
.calc-display.warn { background: #fffbeb; border-color: #fde68a; color: #92400e; }
.freq-badge {
    display: inline-flex; align-items: center; gap: 0.4rem;
    background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 999px;
    padding: 0.2rem 0.75rem; font-size: 0.75rem; color: #1d4ed8; font-weight: 600;
}
</style>
@endsection

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

<div style="max-width:760px;"
     x-data="{
        monthlyRent: {{ old('monthly_rent', $rental->monthly_rent) ?: 0 }},
        frequency: '{{ old('payment_frequency', $rental->payment_frequency ?? 'mensual') }}',
        leaseStart: '{{ old('lease_start_date', $rental->lease_start_date?->format('Y-m-d') ?? '') }}',
        leaseDuration: {{ old('lease_duration_months', $rental->lease_duration_months ?? 12) }},
        commissionAgency: {{ old('commission_amount', $rental->commission_amount) ?: 0 }},
        brokerCommission: {{ old('broker_commission_amount', $rental->broker_commission_amount) ?: 0 }},
        hasBroker: {{ $rental->broker_id ? 'true' : 'false' }},
        increaseType: '{{ old('annual_increase_type', $rental->annual_increase_type ?? 'inpc') }}',
        depositAmount: {{ old('deposit_amount', $rental->deposit_amount) ?: 0 }},

        get totalPeriod() {
            if (this.frequency === 'anual') return this.monthlyRent * 12;
            if (this.frequency === 'semestral') return this.monthlyRent * 6;
            if (this.frequency === 'trimestral') return this.monthlyRent * 3;
            return this.monthlyRent;
        },
        get commissionIva() { return this.commissionAgency * 1.16; },
        get commissionPct() {
            return this.monthlyRent > 0 ? ((this.commissionAgency / this.monthlyRent) * 100).toFixed(1) : 0;
        },
        get totalCommission() { return this.commissionAgency + this.brokerCommission; },
        get totalCommissionIva() { return this.totalCommission * 1.16; },
        get leaseEnd() {
            if (!this.leaseStart || !this.leaseDuration) return '';
            const d = new Date(this.leaseStart);
            d.setMonth(d.getMonth() + parseInt(this.leaseDuration));
            d.setDate(d.getDate() - 1);
            return d.toISOString().split('T')[0];
        },
        get leaseEndDisplay() {
            if (!this.leaseEnd) return '';
            const d = new Date(this.leaseEnd + 'T00:00:00');
            return d.toLocaleDateString('es-MX', {day:'numeric', month:'long', year:'numeric'});
        },
        get leaseStartDisplay() {
            if (!this.leaseStart) return '';
            const d = new Date(this.leaseStart + 'T00:00:00');
            return d.toLocaleDateString('es-MX', {day:'numeric', month:'long', year:'numeric'});
        },
        get frequencyLabel() {
            const labels = {mensual:'Mensual',trimestral:'Trimestral',semestral:'Semestral',anual:'Anual por adelantado'};
            return labels[this.frequency] || this.frequency;
        },
        fmt(n) {
            return new Intl.NumberFormat('es-MX', {style:'currency', currency:'MXN', minimumFractionDigits:0, maximumFractionDigits:0}).format(n || 0);
        },
        setCommission() { this.commissionAgency = this.monthlyRent; },
        setDeposit(months) { this.depositAmount = this.monthlyRent * months; },
     }">

    <div class="card">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-error" style="margin-bottom:1.25rem;">
                    <strong>Errores en el formulario:</strong>
                    <ul style="margin:0.5rem 0 0 1.25rem; font-size:0.85rem;">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('rentals.update', $rental) }}" method="POST">
                @csrf @method('PUT')

                {{-- ① Partes --}}
                <div class="rform-section">
                    <h4>Partes</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Propiedad <span class="required">*</span></label>
                            <select name="property_id" class="form-select" required>
                                <option value="">Seleccionar propiedad...</option>
                                @foreach($properties as $property)
                                    <option value="{{ $property->id }}" {{ old('property_id', $rental->property_id) == $property->id ? 'selected' : '' }}>
                                        {{ $property->title ?? 'Propiedad #'.$property->id }}
                                    </option>
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
                            <label class="form-label">Arrendatario</label>
                            <select name="tenant_client_id" class="form-select">
                                <option value="">Seleccionar cliente...</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ old('tenant_client_id', $rental->tenant_client_id) == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Broker externo</label>
                            <select name="broker_id" class="form-select"
                                @change="hasBroker = $event.target.value !== ''">
                                <option value="">Sin broker externo</option>
                                @foreach($brokers as $broker)
                                    <option value="{{ $broker->id }}" {{ old('broker_id', $rental->broker_id) == $broker->id ? 'selected' : '' }}>{{ $broker->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- ② Condiciones del contrato --}}
                <div class="rform-section">
                    <h4>Condiciones del contrato</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Renta mensual</label>
                            <input type="number" name="monthly_rent" x-model.number="monthlyRent"
                                   class="form-input" step="0.01" min="0" placeholder="0.00">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Moneda</label>
                            <select name="currency" class="form-select">
                                <option value="MXN" {{ old('currency', $rental->currency) === 'MXN' ? 'selected' : '' }}>MXN</option>
                                <option value="USD" {{ old('currency', $rental->currency) === 'USD' ? 'selected' : '' }}>USD</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Frecuencia de pago</label>
                            <select name="payment_frequency" class="form-select" x-model="frequency">
                                <option value="mensual" {{ old('payment_frequency', $rental->payment_frequency) === 'mensual' ? 'selected' : '' }}>Mensual</option>
                                <option value="trimestral" {{ old('payment_frequency', $rental->payment_frequency) === 'trimestral' ? 'selected' : '' }}>Trimestral</option>
                                <option value="semestral" {{ old('payment_frequency', $rental->payment_frequency) === 'semestral' ? 'selected' : '' }}>Semestral</option>
                                <option value="anual" {{ old('payment_frequency', $rental->payment_frequency) === 'anual' ? 'selected' : '' }}>Anual por adelantado</option>
                            </select>
                        </div>
                        <div class="form-group" x-show="frequency !== 'anual'" x-cloak>
                            <label class="form-label">Día de pago</label>
                            <select name="payment_day" class="form-select">
                                <option value="">-- Seleccionar --</option>
                                @foreach(range(1, 28) as $d)
                                    <option value="{{ $d }}" {{ old('payment_day', $rental->payment_day) == $d ? 'selected' : '' }}>Día {{ $d }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <template x-if="frequency !== 'mensual' && monthlyRent > 0">
                        <div class="calc-display">
                            <span>
                                <span x-text="frequencyLabel"></span> a pagar:
                                <strong x-text="fmt(totalPeriod)"></strong>
                            </span>
                            <span x-show="frequency === 'anual'" class="freq-badge">
                                &#9650; Pago único anual
                            </span>
                        </div>
                    </template>

                    <div class="form-grid" style="margin-top:0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Duración (meses)</label>
                            <input type="number" name="lease_duration_months" x-model.number="leaseDuration"
                                   class="form-input" min="1" placeholder="12">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fecha de inicio</label>
                            <input type="date" name="lease_start_date" x-model="leaseStart" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fecha de fin</label>
                            <input type="date" name="lease_end_date"
                                   :value="leaseEnd || '{{ old('lease_end_date', $rental->lease_end_date?->format('Y-m-d') ?? '') }}'"
                                   class="form-input">
                            <div class="calc-hint" x-show="leaseStart && leaseDuration">
                                Calculada: <span x-text="leaseEndDisplay" style="font-weight:500;"></span>
                            </div>
                        </div>
                    </div>

                    <template x-if="leaseStart && leaseDuration">
                        <div class="calc-display">
                            <span>Vigencia: <strong x-text="leaseStartDisplay"></strong> &rarr; <strong x-text="leaseEndDisplay"></strong></span>
                            <span x-text="leaseDuration + ' meses'" style="font-weight:600;"></span>
                        </div>
                    </template>
                </div>

                {{-- ③ Garantía y depósito --}}
                <div class="rform-section">
                    <h4>Garantía y depósito</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Tipo de garantía</label>
                            <select name="guarantee_type" class="form-select">
                                <option value="">Seleccionar...</option>
                                <option value="deposito" {{ old('guarantee_type', $rental->guarantee_type) === 'deposito' ? 'selected' : '' }}>Depósito en efectivo</option>
                                <option value="poliza_juridica" {{ old('guarantee_type', $rental->guarantee_type) === 'poliza_juridica' ? 'selected' : '' }}>Póliza jurídica</option>
                                <option value="fianza" {{ old('guarantee_type', $rental->guarantee_type) === 'fianza' ? 'selected' : '' }}>Fianza</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Monto de depósito</label>
                            <input type="number" name="deposit_amount" x-model.number="depositAmount"
                                   class="form-input" step="0.01" min="0" placeholder="0.00">
                            <div class="calc-hint">
                                <span>Calcular:</span>
                                <button type="button" @click="setDeposit(1)">= 1 mes</button>
                                <button type="button" @click="setDeposit(2)">= 2 meses</button>
                                <template x-if="frequency === 'anual'">
                                    <button type="button" @click="depositAmount = 0" style="color:#ef4444;border-color:#ef4444;">Sin depósito</button>
                                </template>
                            </div>
                            <template x-if="frequency === 'anual' && depositAmount == 0">
                                <div class="calc-display warn" style="margin-top:0.4rem; font-size:0.75rem;">
                                    El pago anual por adelantado funciona como garantía
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- ④ Comisión --}}
                <div class="rform-section">
                    <h4>Comisión</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Comisión agencia ($)</label>
                            <input type="number" name="commission_amount" x-model.number="commissionAgency"
                                   class="form-input" step="0.01" min="0" placeholder="0.00">
                            <div class="calc-hint">
                                <span>Calcular:</span>
                                <button type="button" @click="setCommission()">= 1 mes de renta</button>
                                <template x-if="commissionAgency > 0 && monthlyRent > 0">
                                    <span x-text="'(' + commissionPct + '% de la renta)'"></span>
                                </template>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Comisión (%)</label>
                            <input type="number" name="commission_percentage"
                                   value="{{ old('commission_percentage', $rental->commission_percentage) }}"
                                   class="form-input" step="0.01" min="0" max="100" placeholder="0.00">
                        </div>
                    </div>

                    <template x-if="commissionAgency > 0">
                        <div class="calc-display" style="margin-bottom:0.75rem;">
                            <span>Comisión agencia + IVA 16%: <strong x-text="fmt(commissionIva)"></strong></span>
                            <span style="font-size:0.75rem; color:#166534;">Para facturación</span>
                        </div>
                    </template>

                    <div x-show="hasBroker" x-cloak>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Comisión broker externo ($)</label>
                                <input type="number" name="broker_commission_amount" x-model.number="brokerCommission"
                                       class="form-input" step="0.01" min="0" placeholder="0.00">
                            </div>
                        </div>
                        <template x-if="brokerCommission > 0">
                            <div class="calc-display">
                                <span>
                                    Agencia: <strong x-text="fmt(commissionAgency)"></strong> &nbsp;|&nbsp;
                                    Broker: <strong x-text="fmt(brokerCommission)"></strong> &nbsp;|&nbsp;
                                    Total + IVA: <strong x-text="fmt(totalCommissionIva)"></strong>
                                </span>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- ⑤ Incremento anual --}}
                <div class="rform-section">
                    <h4>Incremento anual</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Tipo de incremento</label>
                            <select name="annual_increase_type" class="form-select" x-model="increaseType">
                                <option value="inpc" {{ old('annual_increase_type', $rental->annual_increase_type) === 'inpc' ? 'selected' : '' }}>INPC (inflación)</option>
                                <option value="fixed" {{ old('annual_increase_type', $rental->annual_increase_type) === 'fixed' ? 'selected' : '' }}>Porcentaje fijo</option>
                                <option value="none" {{ old('annual_increase_type', $rental->annual_increase_type) === 'none' ? 'selected' : '' }}>Sin incremento</option>
                            </select>
                        </div>
                        <div class="form-group" x-show="increaseType === 'fixed'" x-cloak>
                            <label class="form-label">Porcentaje fijo (%)</label>
                            <input type="number" name="annual_increase_percentage"
                                   value="{{ old('annual_increase_percentage', $rental->annual_increase_percentage) }}"
                                   class="form-input" step="0.1" min="0" max="100" placeholder="5.0">
                        </div>
                    </div>
                    <div class="calc-hint" x-show="increaseType === 'inpc'" x-cloak>
                        El incremento se aplicará conforme al INPC publicado por el INEGI cada año
                    </div>
                </div>

                {{-- ⑥ Notas --}}
                <div class="rform-section">
                    <h4>Notas</h4>
                    <div class="form-group">
                        <textarea name="notes" class="form-textarea" rows="3"
                                  placeholder="Condiciones especiales, acuerdos verbales, observaciones...">{{ old('notes', $rental->notes) }}</textarea>
                    </div>
                </div>

                <div style="font-size:0.78rem; color:var(--text-muted); margin-bottom:1rem; padding-bottom:0.75rem; border-bottom:1px solid var(--border);">
                    Creado: {{ $rental->created_at->format('d/m/Y H:i') }} &nbsp;·&nbsp;
                    Actualizado: {{ $rental->updated_at->format('d/m/Y H:i') }}
                    @if($rental->completed_at) &nbsp;·&nbsp; Completado: {{ $rental->completed_at->format('d/m/Y H:i') }} @endif
                </div>

                <div class="form-actions">
                    <form method="POST" action="{{ route('rentals.destroy', $rental) }}"
                          onsubmit="return confirm('¿Eliminar este proceso de renta?')" style="margin-right:auto;">
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
