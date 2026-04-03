@extends('layouts.app-sidebar')
@section('title', 'Editar: ' . $referrer->name)

@section('styles')
<style>
/* ===== Profile Header ===== */
.profile-header {
    background: var(--card); border: 1px solid var(--border); border-radius: 16px;
    padding: 0; margin-bottom: 1.25rem; overflow: hidden;
}
.profile-cover { height: 48px; }
.profile-head {
    display: flex; align-items: flex-end; gap: 1.25rem; padding: 0 2rem 1.5rem;
    margin-top: -48px; position: relative; z-index: 1;
}
.profile-avatar {
    width: 96px; height: 96px; border-radius: 50%; background: var(--card);
    border: 4px solid var(--card); display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 2rem; color: #fff; flex-shrink: 0;
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
}
.profile-meta { flex: 1; padding-bottom: 0.2rem; }
.profile-name { font-size: 1.25rem; font-weight: 700; line-height: 1.3; }
.profile-subtitle { font-size: 0.82rem; color: var(--text-muted); }
.profile-badges { display: flex; gap: 0.35rem; margin-top: 0.35rem; flex-wrap: wrap; }
.profile-actions { display: flex; gap: 0.5rem; align-items: flex-end; padding-bottom: 0.3rem; }

/* ===== Tabs ===== */
.p-tabs { display: flex; gap: 0; border-bottom: 1px solid var(--border); margin: 0 2rem; }
.p-tab {
    padding: 0.7rem 1.25rem; font-size: 0.82rem; font-weight: 500;
    border: none; background: none; color: var(--text-muted); cursor: pointer;
    position: relative; transition: color 0.15s;
}
.p-tab:hover { color: var(--text); }
.p-tab.active { color: var(--primary); font-weight: 600; }
.p-tab.active::after {
    content: ''; position: absolute; bottom: -1px; left: 0; right: 0;
    height: 2px; background: var(--primary); border-radius: 2px 2px 0 0;
}

/* ===== Panels ===== */
.p-panel { display: none; padding: 1.5rem 2rem; animation: panelIn 0.2s ease; }
.p-panel.active { display: block; }
@keyframes panelIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; } }

.p-section-title {
    font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em;
    color: var(--text-muted); margin: 1.5rem 0 0.75rem; padding-bottom: 0.4rem;
    border-bottom: 1px solid var(--border);
}
.p-section-title:first-child { margin-top: 0; }

/* ===== Save Bar ===== */
.p-save {
    display: flex; justify-content: space-between; align-items: center;
    padding: 1rem 2rem; border-top: 1px solid var(--border); background: var(--bg);
    border-radius: 0 0 16px 16px;
}
.p-save-meta { font-size: 0.72rem; color: var(--text-muted); }
.p-save-actions { display: flex; gap: 0.5rem; }

/* ===== Type cards ===== */
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

/* Type badges */
.type-portero { background: rgba(59,130,246,0.1); color: #3b82f6; }
.type-vecino { background: rgba(34,197,94,0.1); color: #22c55e; }
.type-broker_hipotecario { background: rgba(168,85,247,0.1); color: #a855f7; }
.type-cliente_pasado { background: rgba(249,115,22,0.1); color: #f97316; }
.type-comisionista { background: rgba(234,179,8,0.1); color: #ca8a04; }
.type-otro { background: rgba(107,114,128,0.1); color: #6b7280; }

/* ===== Responsive ===== */
@media (max-width: 768px) {
    .profile-head { flex-direction: column; align-items: center; text-align: center; padding: 0 1rem 1.25rem; }
    .profile-actions { justify-content: center; }
    .p-tabs { margin: 0 1rem; overflow-x: auto; }
    .p-panel { padding: 1.25rem 1rem; }
    .p-save { padding: 0.75rem 1rem; flex-direction: column; gap: 0.5rem; }
    .type-cards { grid-template-columns: repeat(2, 1fr); }
}
</style>
@endsection

@section('content')
@php
    $avatarColors = ['#667eea','#764ba2','#f093fb','#4facfe','#43e97b','#fa709a'];
    $typeIcons = [
        'portero' => '&#127970;', 'vecino' => '&#127968;', 'broker_hipotecario' => '&#127974;',
        'cliente_pasado' => '&#128100;', 'comisionista' => '&#128176;', 'otro' => '&#128101;',
    ];
@endphp

<div style="margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
    <a href="{{ route('referrers.index') }}" style="font-size:0.82rem; color:var(--text-muted);">&#8592; Comisionistas</a>
    <span style="color:var(--text-muted); font-size:0.72rem;">/</span>
    <span style="font-size:0.82rem; color:var(--text);">{{ $referrer->name }}</span>
</div>

@if($errors->any())
    <div class="alert alert-error" style="margin-bottom:1rem;">
        @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
    </div>
@endif

<form method="POST" action="{{ route('referrers.update', $referrer) }}">
@csrf @method('PUT')

<div class="profile-header">
    <div class="profile-cover"></div>
    <div class="profile-head">
        <div class="profile-avatar" style="background:{{ $avatarColors[$referrer->id % count($avatarColors)] }};">
            {{ strtoupper(substr($referrer->name, 0, 1)) }}
        </div>
        <div class="profile-meta">
            <div class="profile-name">{{ $referrer->name }}</div>
            <div class="profile-subtitle">{{ $referrer->phone ?: $referrer->email ?: '—' }}</div>
            <div class="profile-badges">
                <span class="badge type-{{ $referrer->type }}" style="font-size:0.72rem; padding:0.15rem 0.5rem; border-radius:4px;">
                    {{ \App\Models\Referrer::TYPES[$referrer->type] ?? $referrer->type }}
                </span>
                @if($referrer->status === 'active')
                    <span class="badge badge-green" style="font-size:0.68rem;">Activo</span>
                @else
                    <span class="badge badge-red" style="font-size:0.68rem;">Inactivo</span>
                @endif
            </div>
        </div>
        <div class="profile-actions">
            <a href="{{ route('referrers.show', $referrer) }}" class="btn btn-sm btn-outline">Ver perfil</a>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="p-tabs">
        <button type="button" class="p-tab active" onclick="showTab('general', this)">General</button>
        <button type="button" class="p-tab" onclick="showTab('type', this)">Tipo</button>
    </div>

    {{-- Tab: General --}}
    <div class="p-panel active" id="panel-general">
        <div class="p-section-title">Informacion de contacto</div>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Nombre <span class="required">*</span></label>
                <input type="text" name="name" class="form-input" value="{{ old('name', $referrer->name) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Telefono</label>
                <input type="tel" name="phone" class="form-input" value="{{ old('phone', $referrer->phone) }}" placeholder="+52 55 1234 5678">
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input" value="{{ old('email', $referrer->email) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Direccion / Zona</label>
                <input type="text" name="address" class="form-input" value="{{ old('address', $referrer->address) }}" placeholder="Colonia, calle, edificio...">
            </div>
        </div>

        <div class="p-section-title">Estado</div>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Estado</label>
                <select name="status" class="form-select">
                    <option value="active" {{ old('status', $referrer->status) === 'active' ? 'selected' : '' }}>Activo</option>
                    <option value="inactive" {{ old('status', $referrer->status) === 'inactive' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
        </div>

        <div class="p-section-title">Notas</div>
        <div class="form-group">
            <textarea name="notes" class="form-textarea" rows="3" placeholder="Como lo conocimos, zona que cubre, acuerdos especiales...">{{ old('notes', $referrer->notes) }}</textarea>
        </div>
    </div>

    {{-- Tab: Type --}}
    <div class="p-panel" id="panel-type">
        <div class="p-section-title">Tipo de comisionista</div>
        <div class="type-cards" style="margin-bottom:1rem;">
            @foreach(\App\Models\Referrer::TYPES as $val => $label)
            <label class="type-card {{ old('type', $referrer->type) === $val ? 'active' : '' }}" onclick="this.closest('.type-cards').querySelectorAll('.type-card').forEach(c=>c.classList.remove('active')); this.classList.add('active');">
                <input type="radio" name="type" value="{{ $val }}" {{ old('type', $referrer->type) === $val ? 'checked' : '' }} required>
                <div class="type-icon">{!! $typeIcons[$val] ?? '&#128101;' !!}</div>
                <div class="type-card-label">{{ $label }}</div>
            </label>
            @endforeach
        </div>
    </div>

    {{-- Save Bar --}}
    <div class="p-save">
        <div class="p-save-meta">Creado {{ $referrer->created_at->format('d/m/Y') }} &middot; {{ $referrer->total_referrals }} referidos &middot; ${{ number_format($referrer->total_earned, 0) }} ganados</div>
        <div class="p-save-actions">
            <a href="{{ route('referrers.show', $referrer) }}" class="btn btn-outline btn-sm">Cancelar</a>
            <button type="submit" class="btn btn-primary btn-sm">Guardar cambios</button>
        </div>
    </div>
</div>
</form>

{{-- Danger Zone --}}
<div style="max-width: 480px; margin-top: 1.5rem;">
    <div style="background:var(--card); border:1px solid #fecaca; border-radius:12px; padding:1.25rem;">
        <div style="font-size:0.82rem; font-weight:600; color:#991b1b; margin-bottom:0.25rem;">Zona de peligro</div>
        <p style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.75rem;">Eliminar este comisionista y todos sus referidos.</p>
        <form method="POST" action="{{ route('referrers.destroy', $referrer) }}" onsubmit="return confirm('Seguro que deseas eliminar a {{ $referrer->name }}? Se eliminaran todos sus referidos.')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">Eliminar comisionista</button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function showTab(name, btn) {
    document.querySelectorAll('.p-panel').forEach(function(p) { p.classList.toggle('active', p.id === 'panel-' + name); });
    document.querySelectorAll('.p-tab').forEach(function(t) { t.classList.remove('active'); });
    btn.classList.add('active');
}
</script>
@endsection
