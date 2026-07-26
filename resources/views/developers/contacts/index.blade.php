@extends('layouts.app-sidebar')
@section('title', 'Contactos de Constructoras')

@section('styles')
<style>
.filter-bar {
    background: var(--card); border: 1px solid var(--border); border-radius: var(--radius);
    padding: 1rem 1.25rem; margin-bottom: 1.25rem;
}
.filter-bar .filter-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 0.75rem; align-items: end;
}
.filter-bar .filter-actions { display: flex; gap: 0.5rem; align-items: end; margin-top: 0.75rem; }
.zone-tag { display: inline-block; font-size: 0.68rem; padding: 0.1rem 0.45rem; border-radius: 10px; background: rgba(102,126,234,0.1); color: var(--primary); margin: 0.1rem 0.2rem 0 0; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Contactos de Constructoras</h2>
        <p class="text-muted">A quién le mandamos las fichas técnicas de predios</p>
    </div>
    <div class="action-btns">
        <a href="{{ route('developers.index') }}" class="btn btn-outline">Ver constructoras</a>
        <a href="{{ route('developer-contacts.create') }}" class="btn btn-primary">+ Nuevo contacto</a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>
@endif

<form method="GET" action="{{ route('developer-contacts.index') }}" class="filter-bar">
    <div class="filter-grid">
        <div class="form-group" style="margin:0;">
            <label class="form-label">Buscar</label>
            <input type="text" name="search" class="form-input" value="{{ request('search') }}" placeholder="Nombre, correo, empresa...">
        </div>
        <div class="form-group" style="margin:0;">
            <label class="form-label">Zona de interés</label>
            <input type="text" name="zone" class="form-input" value="{{ request('zone') }}" placeholder="Ej. Del Valle">
        </div>
        <div class="form-group" style="margin:0;">
            <label class="form-label">Estado</label>
            <select name="status" class="form-select">
                <option value="">Todos</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activo</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactivo</option>
            </select>
        </div>
    </div>
    <div class="filter-actions">
        <button type="submit" class="btn btn-primary btn-sm">Buscar</button>
        <a href="{{ route('developer-contacts.index') }}" class="btn btn-outline btn-sm">Limpiar</a>
    </div>
</form>

<div class="card">
    <div class="card-body" style="padding:0;">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Empresa</th>
                        <th>Puesto</th>
                        <th>Contacto</th>
                        <th>Zonas de interés</th>
                        <th>Presupuesto</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contacts as $contact)
                    <tr>
                        <td style="font-weight:500;">{{ $contact->name }}</td>
                        <td style="font-size:0.85rem;">
                            @if($contact->developerCompany)
                                <a href="{{ route('developers.show', $contact->developerCompany) }}">{{ $contact->developerCompany->name }}</a>
                            @else
                                <span class="text-muted">Inversionista independiente</span>
                            @endif
                        </td>
                        <td class="text-muted" style="font-size:0.85rem;">{{ $contact->role ?: '—' }}</td>
                        <td class="text-muted" style="font-size:0.82rem;">
                            {{ $contact->email ?: '—' }}
                            @if($contact->phone)<br>{{ $contact->phone }}@endif
                        </td>
                        <td>
                            @forelse($contact->interest_zones ?? [] as $zone)
                                <span class="zone-tag">{{ $zone }}</span>
                            @empty
                                <span class="text-muted" style="font-size:0.8rem;">—</span>
                            @endforelse
                        </td>
                        <td class="text-muted" style="font-size:0.8rem;">
                            @if($contact->budget_min || $contact->budget_max)
                                ${{ number_format($contact->budget_min ?? 0) }} – ${{ number_format($contact->budget_max ?? 0) }}
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @if($contact->status === 'active')
                                <span class="badge badge-green">Activo</span>
                            @else
                                <span class="badge badge-red">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('developer-contacts.edit', $contact) }}" class="btn btn-sm btn-outline">Editar</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted" style="padding:2rem;">No hay contactos registrados todavía.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($contacts->hasPages())
        <div style="padding:1rem 1.5rem; border-top:1px solid var(--border);">{{ $contacts->links() }}</div>
        @endif
    </div>
</div>
@endsection
