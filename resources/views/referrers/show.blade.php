@extends('layouts.app-sidebar')
@section('title', $referrer->name)

@section('styles')
<style>
.profile-header {
    background: var(--card); border: 1px solid var(--border); border-radius: var(--radius);
    padding: 1.5rem; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 1.25rem;
}
.profile-avatar {
    width: 64px; height: 64px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark, #764ba2));
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-weight: 700; font-size: 1.5rem;
}
.profile-name { font-size: 1.15rem; font-weight: 700; }
.profile-meta { font-size: 0.82rem; color: var(--text-muted); display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 0.25rem; }
.stat-row { display: flex; gap: 1rem; margin-bottom: 1.25rem; }
.stat-mini {
    flex: 1; background: var(--card); border: 1px solid var(--border);
    border-radius: var(--radius); padding: 1rem; text-align: center;
}
.stat-mini-value { font-size: 1.5rem; font-weight: 700; }
.stat-mini-label { font-size: 0.72rem; color: var(--text-muted); margin-top: 0.15rem; }
.type-portero { background: rgba(59,130,246,0.1); color: #3b82f6; }
.type-vecino { background: rgba(34,197,94,0.1); color: #22c55e; }
.type-broker_hipotecario { background: rgba(168,85,247,0.1); color: #a855f7; }
.type-comisionista { background: rgba(234,179,8,0.1); color: #ca8a04; }
.type-otro { background: rgba(107,114,128,0.1); color: #6b7280; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div><h2>Comisionista</h2></div>
    <div style="display:flex; gap:0.5rem;">
        <a href="{{ route('referrers.edit', $referrer) }}" class="btn btn-outline">Editar</a>
        <a href="{{ route('referrers.index') }}" class="btn btn-outline">Volver</a>
    </div>
</div>

{{-- Profile Header --}}
<div class="profile-header">
    <div class="profile-avatar">{{ strtoupper(substr($referrer->name, 0, 1)) }}</div>
    <div>
        <div class="profile-name">{{ $referrer->name }}</div>
        <div class="profile-meta">
            <span class="badge type-{{ $referrer->type }}" style="font-size:0.72rem; padding:0.15rem 0.5rem; border-radius:4px;">
                {{ \App\Models\Referrer::TYPES[$referrer->type] ?? $referrer->type }}
            </span>
            @if($referrer->phone)<span>&#128222; {{ $referrer->phone }}</span>@endif
            @if($referrer->email)<span>&#9993; {{ $referrer->email }}</span>@endif
            @if($referrer->status === 'active')
                <span class="badge badge-green">Activo</span>
            @else
                <span class="badge badge-red">Inactivo</span>
            @endif
        </div>
    </div>
</div>

{{-- Stats --}}
<div class="stat-row">
    <div class="stat-mini">
        <div class="stat-mini-value">{{ $referrer->total_referrals }}</div>
        <div class="stat-mini-label">Total Referidos</div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-value" style="color:var(--success);">${{ number_format($referrer->total_earned, 0) }}</div>
        <div class="stat-mini-label">Total Ganado</div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-value">{{ $referrals->where('status', 'pending')->count() }}</div>
        <div class="stat-mini-label">Pendientes</div>
    </div>
</div>

{{-- Registrar nuevo referido --}}
<div class="card" style="margin-bottom:1.25rem;">
    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
        <h3 style="margin:0; font-size:0.95rem;">Registrar Referido</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('referrers.referrals.store', $referrer) }}">
            @csrf
            <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:0.75rem;">
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Propiedad</label>
                    <select name="property_id" class="form-select">
                        <option value="">— Seleccionar —</option>
                        @foreach($properties as $prop)
                            <option value="{{ $prop->id }}">{{ Str::limit($prop->title, 40) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Comision %</label>
                    <input type="number" name="commission_percentage" class="form-input" value="5" step="0.5" min="0" max="100" required>
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Monto $</label>
                    <input type="number" name="commission_amount" class="form-input" value="0" step="0.01" min="0">
                </div>
            </div>
            <div class="form-group" style="margin-top:0.75rem;">
                <label class="form-label">Notas</label>
                <input type="text" name="notes" class="form-input" placeholder="Detalles del referido...">
            </div>
            <div style="margin-top:0.75rem;">
                <button type="submit" class="btn btn-primary btn-sm">Registrar Referido</button>
            </div>
        </form>
    </div>
</div>

{{-- Historial de referidos --}}
<div class="card">
    <div class="card-header"><h3 style="margin:0; font-size:0.95rem;">Historial de Referidos</h3></div>
    <div class="card-body" style="padding:0;">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Propiedad</th>
                        <th>Comision %</th>
                        <th>Monto</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($referrals as $ref)
                    <tr>
                        <td style="font-size:0.82rem;">{{ $ref->created_at->format('d/m/Y') }}</td>
                        <td style="font-size:0.85rem;">
                            @if($ref->property)
                                <a href="{{ route('properties.show', $ref->property) }}" style="color:var(--primary);">{{ Str::limit($ref->property->title, 30) }}</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td style="font-size:0.85rem; text-align:center;">{{ $ref->commission_percentage }}%</td>
                        <td style="font-size:0.85rem; font-weight:500;">${{ number_format($ref->commission_amount, 0) }}</td>
                        <td>
                            @if($ref->status === 'paid')
                                <span class="badge badge-green">Pagado</span>
                            @elseif($ref->status === 'approved')
                                <span class="badge badge-blue">Aprobado</span>
                            @else
                                <span class="badge badge-yellow">Pendiente</span>
                            @endif
                        </td>
                        <td>
                            @if($ref->status === 'pending')
                            <form method="POST" action="{{ route('referrals.update-status', $ref) }}" style="display:inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" class="btn btn-sm btn-outline" style="font-size:0.7rem;">Aprobar</button>
                            </form>
                            @elseif($ref->status === 'approved')
                            <form method="POST" action="{{ route('referrals.update-status', $ref) }}" style="display:inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="paid">
                                <button type="submit" class="btn btn-sm btn-primary" style="font-size:0.7rem;">Marcar Pagado</button>
                            </form>
                            @else
                                <span style="font-size:0.72rem; color:var(--text-muted);">{{ $ref->paid_at?->format('d/m/Y') }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted" style="padding:2rem;">Sin referidos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($referrals->hasPages())
        <div style="padding:1rem 1.5rem; border-top:1px solid var(--border);">{{ $referrals->links() }}</div>
        @endif
    </div>
</div>

@if($referrer->notes)
<div class="card" style="margin-top:1.25rem;">
    <div class="card-header"><h3 style="margin:0; font-size:0.95rem;">Notas</h3></div>
    <div class="card-body" style="font-size:0.85rem; color:var(--text-muted);">{{ $referrer->notes }}</div>
</div>
@endif
@endsection
