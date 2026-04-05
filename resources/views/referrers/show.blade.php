@extends('layouts.app-sidebar')
@section('title', $referrer->name . ' - Comisionista')

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

/* ===== Info Rows ===== */
.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 2rem; }
.info-item { padding: 0.65rem 0; border-bottom: 1px solid var(--border); }
.info-label { font-size: 0.72rem; color: var(--text-muted); margin-bottom: 0.15rem; }
.info-value { font-size: 0.85rem; font-weight: 500; }
.info-full { grid-column: 1 / -1; }

/* ===== Quick Actions ===== */
.quick-actions {
    display: flex; gap: 0.5rem; padding: 1rem 2rem; border-top: 1px solid var(--border);
    background: var(--bg); border-radius: 0 0 16px 16px;
}

/* ===== Referral Form ===== */
.ref-form-card {
    background: var(--card); border: 1px solid var(--border); border-radius: 12px;
    margin-bottom: 1.25rem; overflow: hidden;
}
.ref-form-header {
    padding: 1rem 1.5rem; border-bottom: 1px solid var(--border);
    display: flex; justify-content: space-between; align-items: center;
}
.ref-form-header h3 { font-size: 0.95rem; font-weight: 600; margin: 0; }
.ref-form-body { padding: 1.25rem 1.5rem; }

.ref-type-selector { display: flex; gap: 0.5rem; margin-bottom: 1rem; }
.ref-type-btn {
    flex: 1; padding: 0.75rem; border-radius: var(--radius); border: 2px solid var(--border);
    text-align: center; cursor: pointer; transition: all 0.15s; position: relative;
}
.ref-type-btn:hover { border-color: var(--primary); }
.ref-type-btn.active { border-color: var(--primary); background: rgba(102,126,234,0.04); }
.ref-type-btn input { position: absolute; opacity: 0; pointer-events: none; }
.ref-type-pct { font-size: 1.25rem; font-weight: 700; color: var(--primary); }
.ref-type-label { font-size: 0.78rem; font-weight: 600; margin-top: 0.15rem; }
.ref-type-desc { font-size: 0.65rem; color: var(--text-muted); margin-top: 0.1rem; }

/* ===== Referral Card ===== */
.ref-card {
    background: var(--card); border: 1px solid var(--border); border-radius: 10px;
    padding: 1rem 1.25rem; margin-bottom: 0.75rem; transition: border-color 0.15s;
}
.ref-card:hover { border-color: rgba(102,126,234,0.3); }
.ref-card-top { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem; }
.ref-card-name { font-size: 0.9rem; font-weight: 600; flex: 1; }
.ref-card-meta { font-size: 0.72rem; color: var(--text-muted); display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.5rem; }
.ref-card-chips { display: flex; gap: 0.35rem; flex-wrap: wrap; margin-bottom: 0.5rem; }
.ref-chip {
    font-size: 0.65rem; padding: 0.1rem 0.45rem; border-radius: 4px;
    font-weight: 500; display: inline-flex; align-items: center; gap: 3px;
}
.ref-chip-linked { background: rgba(102,126,234,0.1); color: var(--primary); }
.ref-chip-unlinked { background: var(--bg); color: var(--text-muted); border: 1px dashed var(--border); }
.ref-card-footer { display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; }
.ref-card-amount { font-size: 0.9rem; font-weight: 700; }

.badge-registrado { background: rgba(234,179,8,0.1); color: #ca8a04; }
.badge-en_proceso { background: rgba(59,130,246,0.1); color: #3b82f6; }
.badge-por_pagar { background: rgba(249,115,22,0.1); color: #f97316; }
.badge-pagado { background: rgba(16,185,129,0.1); color: #10b981; }

/* ===== Link Form ===== */
.link-form {
    display: none; background: var(--bg); border-radius: var(--radius);
    padding: 0.75rem; margin-top: 0.5rem; border: 1px solid var(--border);
}
.link-form.open { display: block; }

/* Type badges */
.type-portero { background: rgba(59,130,246,0.1); color: #3b82f6; }
.type-vecino { background: rgba(34,197,94,0.1); color: #22c55e; }
.type-broker_hipotecario { background: rgba(168,85,247,0.1); color: #a855f7; }
.type-cliente_pasado { background: rgba(249,115,22,0.1); color: #f97316; }
.type-comisionista { background: rgba(234,179,8,0.1); color: #ca8a04; }
.type-otro { background: rgba(107,114,128,0.1); color: #6b7280; }

@media (max-width: 768px) {
    .profile-head { flex-direction: column; align-items: center; text-align: center; padding: 0 1rem 1.25rem; }
    .profile-actions { justify-content: center; }
    .p-tabs { margin: 0 1rem; overflow-x: auto; }
    .p-panel { padding: 1.25rem 1rem; }
    .info-grid { grid-template-columns: 1fr; }
    .ref-type-selector { flex-direction: column; }
    .ref-form-body { padding: 1rem; }
}
</style>
@endsection

@section('content')
@php
    $avatarColors = ['#667eea','#764ba2','#f093fb','#4facfe','#43e97b','#fa709a'];
    $pendingCount = $referrals->where('status', 'registrado')->count() + $referrals->where('status', 'en_proceso')->count();
    $porPagarCount = $referrals->where('status', 'por_pagar')->count();
@endphp

<div style="margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
    <a href="{{ route('referrers.index') }}" style="font-size:0.82rem; color:var(--text-muted);">&#8592; Comisionistas</a>
    <span style="color:var(--text-muted); font-size:0.72rem;">/</span>
    <span style="font-size:0.82rem; color:var(--text);">{{ $referrer->name }}</span>
</div>

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
            @if($referrer->phone)
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $referrer->phone) }}" target="_blank" class="btn btn-sm" style="background:#25d366; color:#fff; border:none;">WhatsApp</a>
            @endif
            <a href="{{ route('referrers.edit', $referrer) }}" class="btn btn-sm btn-primary">Editar</a>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="p-tabs">
        <button type="button" class="p-tab active" onclick="showTab('info', this)">Informacion</button>
        <button type="button" class="p-tab" onclick="showTab('referrals', this)">
            Referidos
            @if($porPagarCount > 0)
                <span style="font-size:0.62rem; background:rgba(249,115,22,0.15); color:#f97316; padding:1px 5px; border-radius:8px; margin-left:3px;">{{ $porPagarCount }}</span>
            @else
                <span style="font-size:0.68rem; color:var(--text-muted);">{{ $referrer->total_referrals }}</span>
            @endif
        </button>
    </div>

    {{-- Tab: Info --}}
    <div class="p-panel active" id="panel-info">
        <div class="p-section-title">Datos de contacto</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Nombre</div>
                <div class="info-value">{{ $referrer->name }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Tipo</div>
                <div class="info-value">{{ \App\Models\Referrer::TYPES[$referrer->type] ?? $referrer->type }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Telefono</div>
                <div class="info-value">{{ $referrer->phone ?: '—' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Email</div>
                <div class="info-value">{{ $referrer->email ?: '—' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Direccion / Zona</div>
                <div class="info-value">{{ $referrer->address ?: '—' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Registrado</div>
                <div class="info-value">{{ $referrer->created_at->translatedFormat('d M Y') }}</div>
            </div>
        </div>

        <div class="p-section-title">Estadisticas</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Total referidos</div>
                <div class="info-value">{{ $referrer->total_referrals }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Total ganado</div>
                <div class="info-value" style="color:var(--success);">${{ number_format($referrer->total_earned, 0) }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">En proceso</div>
                <div class="info-value">{{ $pendingCount }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Por pagar</div>
                <div class="info-value" style="color:#f97316;">{{ $porPagarCount }}</div>
            </div>
        </div>

        @if($referrer->notes)
        <div class="p-section-title">Notas</div>
        <div class="info-grid">
            <div class="info-item info-full">
                <div class="info-value" style="font-weight:400;">{{ $referrer->notes }}</div>
            </div>
        </div>
        @endif
    </div>

    {{-- Tab: Referidos --}}
    <div class="p-panel" id="panel-referrals">
        <div class="p-section-title">Historial de referidos</div>

        @forelse($referrals as $ref)
        <div class="ref-card">
            <div class="ref-card-top">
                @if($ref->referral_type === 'trajo_cliente')
                    <span class="badge" style="background:rgba(234,179,8,0.1); color:#ca8a04; font-size:0.68rem; padding:0.15rem 0.5rem;">10%</span>
                @else
                    <span class="badge" style="background:rgba(102,126,234,0.1); color:var(--primary); font-size:0.68rem; padding:0.15rem 0.5rem;">5%</span>
                @endif
                <div class="ref-card-name">
                    {{ $ref->referred_name ?: 'Sin nombre' }}
                    @if($ref->referred_phone)
                        <span style="font-size:0.72rem; color:var(--text-muted); font-weight:400; margin-left:0.3rem;">{{ $ref->referred_phone }}</span>
                    @endif
                </div>
                <span class="badge badge-{{ $ref->status }}" style="font-size:0.68rem; padding:0.15rem 0.5rem; border-radius:4px;">
                    {{ \App\Models\Referral::STATUSES[$ref->status] ?? $ref->status }}
                </span>
            </div>

            <div class="ref-card-meta">
                <span>{{ $ref->referral_type === 'trajo_cliente' ? 'Trajo cliente listo' : 'Trajo propietario' }}</span>
                <span>{{ $ref->created_at->format('d/m/Y') }}</span>
                @if($ref->referred_context)
                    <span>{{ Str::limit($ref->referred_context, 50) }}</span>
                @endif
                @if($ref->notes)
                    <span>{{ Str::limit($ref->notes, 40) }}</span>
                @endif
            </div>

            {{-- Vinculacion chips --}}
            <div class="ref-card-chips">
                @if($ref->property)
                    <a href="{{ route('properties.show', $ref->property) }}" class="ref-chip ref-chip-linked">&#127968; {{ Str::limit($ref->property->title, 25) }}</a>
                @else
                    <span class="ref-chip ref-chip-unlinked">&#127968; Sin propiedad</span>
                @endif
                @if($ref->operation)
                    <a href="{{ route('operations.show', $ref->operation) }}" class="ref-chip ref-chip-linked">&#128203; Op #{{ $ref->operation->id }}</a>
                @else
                    <span class="ref-chip ref-chip-unlinked">&#128203; Sin operacion</span>
                @endif
                @if($ref->client)
                    <a href="{{ route('clients.show', $ref->client) }}" class="ref-chip ref-chip-linked">&#128100; {{ $ref->client->name }}</a>
                @else
                    <span class="ref-chip ref-chip-unlinked">&#128100; Sin cliente</span>
                @endif
            </div>

            {{-- Actions per status --}}
            <div class="ref-card-footer">
                <div>
                    @if(in_array($ref->status, ['por_pagar', 'pagado']))
                        <span class="ref-card-amount" style="color:{{ $ref->status === 'pagado' ? 'var(--success)' : '#f97316' }};">
                            ${{ number_format($ref->commission_amount, 0) }}
                        </span>
                        @if($ref->status === 'pagado' && $ref->paid_at)
                            <span style="font-size:0.68rem; color:var(--text-muted); margin-left:0.5rem;">Pagado {{ $ref->paid_at->format('d/m/Y') }}</span>
                        @endif
                    @else
                        <span style="font-size:0.72rem; color:var(--text-muted);">{{ $ref->commission_percentage }}% de comision</span>
                    @endif
                </div>
                <div style="display:flex; gap:0.35rem; align-items:center;">
                    @if($ref->status === 'registrado')
                        <form method="POST" action="{{ route('referrals.update-status', $ref) }}" style="display:inline">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="en_proceso">
                            <button type="submit" class="btn btn-sm btn-outline" style="font-size:0.68rem; padding:0.2rem 0.5rem;">En proceso</button>
                        </form>
                    @elseif($ref->status === 'en_proceso')
                        <button type="button" class="btn btn-sm btn-outline" style="font-size:0.68rem; padding:0.2rem 0.5rem;" onclick="toggleLink({{ $ref->id }})">Vincular</button>
                        @if($ref->operation_id)
                        <form method="POST" action="{{ route('referrals.update-status', $ref) }}" style="display:inline">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="por_pagar">
                            <button type="submit" class="btn btn-sm btn-primary" style="font-size:0.68rem; padding:0.2rem 0.5rem;">Por pagar</button>
                        </form>
                        @endif
                    @elseif($ref->status === 'por_pagar')
                        <form method="POST" action="{{ route('referrals.update-status', $ref) }}" style="display:inline" onsubmit="return confirm('Confirmar pago de ${{ number_format($ref->commission_amount, 0) }} a {{ $referrer->name }}?')">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="pagado">
                            <button type="submit" class="btn btn-sm btn-primary" style="font-size:0.68rem; padding:0.2rem 0.5rem;">Marcar pagado</button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Link form (hidden by default) --}}
            @if($ref->status === 'en_proceso')
            <div class="link-form" id="link-form-{{ $ref->id }}">
                <form method="POST" action="{{ route('referrals.link', $ref) }}" style="display:grid; grid-template-columns: 1fr 1fr 1fr auto; gap:0.5rem; align-items:end;">
                    @csrf @method('PATCH')
                    <div>
                        <label class="form-label" style="font-size:0.68rem;">Propiedad</label>
                        <select name="property_id" class="form-select" style="font-size:0.78rem; padding:0.35rem;">
                            <option value="">—</option>
                            @foreach($properties as $prop)
                                <option value="{{ $prop->id }}" {{ $ref->property_id == $prop->id ? 'selected' : '' }}>{{ Str::limit($prop->title, 30) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label" style="font-size:0.68rem;">Operacion</label>
                        <select name="operation_id" class="form-select" style="font-size:0.78rem; padding:0.35rem;">
                            <option value="">—</option>
                            @foreach($operations as $op)
                                <option value="{{ $op->id }}" {{ $ref->operation_id == $op->id ? 'selected' : '' }}>Op #{{ $op->id }} - {{ ucfirst($op->type) }} ({{ $op->stage }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label" style="font-size:0.68rem;">Cliente/Propietario</label>
                        <select name="client_id" class="form-select" style="font-size:0.78rem; padding:0.35rem;">
                            <option value="">—</option>
                            @foreach($clients as $cli)
                                <option value="{{ $cli->id }}" {{ $ref->client_id == $cli->id ? 'selected' : '' }}>{{ $cli->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary" style="font-size:0.68rem;">Guardar</button>
                </form>
            </div>
            @endif
        </div>
        @empty
        <p style="text-align:center; padding:2rem; color:var(--text-muted); font-size:0.85rem;">Sin referidos registrados aun.</p>
        @endforelse

        @if($referrals->hasPages())
        <div style="padding-top:1rem; text-align:center;">{{ $referrals->links() }}</div>
        @endif
    </div>

    {{-- Footer --}}
    <div class="quick-actions">
        <a href="{{ route('referrers.edit', $referrer) }}" class="btn btn-primary btn-sm">Editar</a>
        @if($referrer->phone)
            <a href="tel:{{ $referrer->phone }}" class="btn btn-outline btn-sm">Llamar</a>
        @endif
        @if($referrer->email)
            <a href="mailto:{{ $referrer->email }}" class="btn btn-outline btn-sm">Email</a>
        @endif
    </div>
</div>

{{-- Registrar nuevo referido --}}
<div class="ref-form-card">
    <div class="ref-form-header">
        <h3>Registrar Referido</h3>
        <span style="font-size:0.72rem; color:var(--text-muted);">La comision se paga al cerrar la operacion</span>
    </div>
    <div class="ref-form-body">
        <form method="POST" action="{{ route('referrers.referrals.store', $referrer) }}">
            @csrf

            {{-- Tipo de referido --}}
            <div class="ref-type-selector">
                <label class="ref-type-btn active" onclick="selectRefType(this, 5)">
                    <input type="radio" name="referral_type" value="trajo_propietario" checked>
                    <div class="ref-type-pct">5%</div>
                    <div class="ref-type-label">Trajo propietario</div>
                    <div class="ref-type-desc">Nos refiere un dueno de casa para exclusiva</div>
                </label>
                <label class="ref-type-btn" onclick="selectRefType(this, 10)">
                    <input type="radio" name="referral_type" value="trajo_cliente">
                    <div class="ref-type-pct">10%</div>
                    <div class="ref-type-label">Trajo cliente listo</div>
                    <div class="ref-type-desc">Nos trae cliente listo para cerrar exclusiva</div>
                </label>
            </div>

            {{-- Datos del referido --}}
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:0.75rem; margin-bottom:0.75rem;">
                <div class="form-group" style="margin:0;">
                    <label class="form-label">A quien nos refieres? <span class="required">*</span></label>
                    <input type="text" name="referred_name" class="form-input" required placeholder="Nombre completo del propietario o cliente" value="{{ old('referred_name') }}">
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Telefono del referido</label>
                    <input type="tel" name="referred_phone" class="form-input" placeholder="+52 55 1234 5678" value="{{ old('referred_phone') }}">
                </div>
            </div>

            <div class="form-group" style="margin-bottom:0.75rem;">
                <label class="form-label">Contexto</label>
                <input type="text" name="referred_context" class="form-input" placeholder="Direccion del inmueble, situacion, que quiere hacer..." value="{{ old('referred_context') }}">
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:0.75rem;">
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Propiedad (si ya existe en el sistema)</label>
                    <select name="property_id" class="form-select">
                        <option value="">— Ninguna por ahora —</option>
                        @foreach($properties as $prop)
                            <option value="{{ $prop->id }}">{{ Str::limit($prop->title, 40) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Comision %</label>
                    <input type="number" id="commPct" name="commission_percentage" class="form-input" value="5" step="0.5" min="0" max="100" required>
                </div>
            </div>

            <div class="form-group" style="margin-top:0.75rem;">
                <label class="form-label">Notas adicionales</label>
                <input type="text" name="notes" class="form-input" placeholder="Acuerdos especiales, detalles relevantes..." value="{{ old('notes') }}">
            </div>

            <div style="margin-top:0.75rem;">
                <button type="submit" class="btn btn-primary btn-sm">Registrar Referido</button>
            </div>
        </form>
    </div>
</div>

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

function selectRefType(el, pct) {
    document.querySelectorAll('.ref-type-btn').forEach(function(b) { b.classList.remove('active'); });
    el.classList.add('active');
    document.getElementById('commPct').value = pct;
}

function toggleLink(id) {
    var form = document.getElementById('link-form-' + id);
    if (form) form.classList.toggle('open');
}
</script>
@endsection
