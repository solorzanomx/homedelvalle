@extends('layouts.app-sidebar')
@section('title', 'Nuevo Comisionista')

@section('styles')
<style>
.user-form-card {
    background: var(--card); border: 1px solid var(--border); border-radius: 10px;
    max-width: 720px; overflow: hidden;
}
.user-form-header {
    padding: 1rem 1.5rem; border-bottom: 1px solid var(--border);
    display: flex; justify-content: space-between; align-items: center;
}
.user-form-header h3 { font-size: 1rem; font-weight: 600; }
.user-form-body { padding: 1.5rem; }

.section-label {
    font-size: 0.78rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;
    letter-spacing: 0.5px; margin: 1.5rem 0 0.75rem; padding-bottom: 0.4rem;
    border-bottom: 1px solid var(--border);
}
.section-label:first-child { margin-top: 0; }

/* Type cards */
.type-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; }
.type-card {
    padding: 0.75rem 0.5rem; border-radius: var(--radius); border: 2px solid var(--border);
    text-align: center; cursor: pointer; transition: all 0.15s; position: relative;
}
.type-card:hover { border-color: var(--primary); }
.type-card.active { border-color: var(--primary); background: rgba(102,126,234,0.04); }
.type-card input { position: absolute; opacity: 0; pointer-events: none; }
.type-icon { font-size: 1.2rem; margin-bottom: 0.15rem; }
.type-card-label { font-size: 0.78rem; font-weight: 600; }
.type-card-desc { font-size: 0.62rem; color: var(--text-muted); margin-top: 0.1rem; }

.commission-hint {
    background: rgba(102,126,234,0.06); border: 1px solid rgba(102,126,234,0.15);
    border-radius: var(--radius); padding: 0.75rem 1rem; margin-bottom: 1rem;
    font-size: 0.78rem; color: var(--text-muted);
}
.commission-hint strong { color: var(--primary); }

@media (max-width: 640px) { .type-cards { grid-template-columns: repeat(2, 1fr); } }
</style>
@endsection

@section('content')
<div style="margin-bottom:1rem;">
    <a href="{{ route('referrers.index') }}" style="font-size:0.82rem; color:var(--text-muted);">&#8592; Comisionistas</a>
</div>

<div class="user-form-card">
    <div class="user-form-header">
        <h3>Nuevo Comisionista</h3>
    </div>
    <div class="user-form-body">
        @if($errors->any())
            <div class="alert alert-error" style="margin-bottom:1rem;">
                <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
            </div>
        @endif

        <div class="commission-hint">
            Ofrecemos <strong>5% de nuestra comision</strong> a quien nos traiga un propietario listo para darnos su casa en exclusiva, y <strong>10%</strong> si nos traen un cliente listo para cerrar. La comision se paga al cerrar la operacion.
        </div>

        <form method="POST" action="{{ route('referrers.store') }}">
            @csrf

            <div class="section-label" style="margin-top:0;">Tipo de comisionista</div>
            @php
                $typeIcons = [
                    'portero' => '&#127970;',
                    'vecino' => '&#127968;',
                    'broker_hipotecario' => '&#127974;',
                    'cliente_pasado' => '&#128100;',
                    'comisionista' => '&#128176;',
                    'otro' => '&#128101;',
                ];
                $typeDescs = [
                    'portero' => 'Portero de edificio',
                    'vecino' => 'Vecino de la zona',
                    'broker_hipotecario' => 'Broker de hipotecas',
                    'cliente_pasado' => 'Cliente anterior',
                    'comisionista' => 'Comisionista general',
                    'otro' => 'Otro contacto',
                ];
            @endphp
            <div class="type-cards" style="margin-bottom:1rem;">
                @foreach(\App\Models\Referrer::TYPES as $val => $label)
                <label class="type-card {{ old('type', 'comisionista') === $val ? 'active' : '' }}" onclick="this.closest('.type-cards').querySelectorAll('.type-card').forEach(c=>c.classList.remove('active')); this.classList.add('active');">
                    <input type="radio" name="type" value="{{ $val }}" {{ old('type', 'comisionista') === $val ? 'checked' : '' }} required>
                    <div class="type-icon">{!! $typeIcons[$val] ?? '&#128101;' !!}</div>
                    <div class="type-card-label">{{ $label }}</div>
                    <div class="type-card-desc">{{ $typeDescs[$val] ?? '' }}</div>
                </label>
                @endforeach
            </div>

            <div class="section-label">Informacion de contacto</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nombre <span class="required">*</span></label>
                    <input type="text" name="name" class="form-input" value="{{ old('name') }}" required autofocus placeholder="Nombre completo">
                </div>
                <div class="form-group">
                    <label class="form-label">Telefono</label>
                    <input type="tel" name="phone" class="form-input" value="{{ old('phone') }}" placeholder="+52 55 1234 5678">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" value="{{ old('email') }}" placeholder="correo@ejemplo.com">
                </div>
                <div class="form-group">
                    <label class="form-label">Direccion / Zona</label>
                    <input type="text" name="address" class="form-input" value="{{ old('address') }}" placeholder="Colonia, calle, edificio...">
                </div>
            </div>

            <div class="section-label">Notas</div>
            <div class="form-group">
                <textarea name="notes" class="form-textarea" rows="3" placeholder="Como lo conocimos, zona que cubre, acuerdos especiales...">{{ old('notes') }}</textarea>
            </div>

            <input type="hidden" name="status" value="active">

            <div class="form-actions">
                <a href="{{ route('referrers.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">Crear Comisionista</button>
            </div>
        </form>
    </div>
</div>
@endsection
