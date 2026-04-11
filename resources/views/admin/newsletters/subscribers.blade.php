@extends('layouts.app-sidebar')
@section('title', 'Suscriptores Newsletter')

@section('styles')
<style>
.nl-stats { display: flex; gap: 1rem; margin-bottom: 1.25rem; }
.nl-stat { padding: 0.75rem 1.25rem; background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); }
.nl-stat-val { font-size: 1.2rem; font-weight: 700; }
.nl-stat-lbl { font-size: 0.72rem; color: var(--text-muted); }
.filters-bar { display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap; margin-bottom: 1.25rem; }
.source-badge { display: inline-block; font-size: 0.7rem; padding: 0.15rem 0.5rem; border-radius: 10px; background: rgba(102,126,234,0.08); color: var(--primary); }
.add-form { display: flex; gap: 0.5rem; align-items: end; padding: 1rem 1.25rem; background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); margin-bottom: 1.25rem; flex-wrap: wrap; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Suscriptores Newsletter</h2>
        <p class="text-muted">{{ $activeCount }} activos &middot; {{ $totalCount }} total</p>
    </div>
    <div style="display:flex; gap:0.5rem;">
        <a href="{{ route('admin.newsletters.subscribers.export', request()->query()) }}" class="btn btn-outline">Exportar CSV</a>
        <a href="{{ route('admin.newsletters.campaigns') }}" class="btn btn-primary">Campanas</a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>
@endif

<div class="nl-stats">
    <div class="nl-stat"><div class="nl-stat-val">{{ $totalCount }}</div><div class="nl-stat-lbl">Total</div></div>
    <div class="nl-stat"><div class="nl-stat-val">{{ $activeCount }}</div><div class="nl-stat-lbl">Activos</div></div>
    <div class="nl-stat"><div class="nl-stat-val">{{ $unsubscribedCount }}</div><div class="nl-stat-lbl">Desuscritos</div></div>
</div>

{{-- Add subscriber form --}}
<form method="POST" action="{{ route('admin.newsletters.subscribers.store') }}" class="add-form">
    @csrf
    <div class="form-group" style="margin:0;">
        <label class="form-label">Agregar suscriptor</label>
        <input type="email" name="email" class="form-input" placeholder="email@ejemplo.com" required style="min-width:240px;">
    </div>
    <div class="form-group" style="margin:0;">
        <label class="form-label">Fuente</label>
        <select name="source" class="form-select">
            <option value="manual">Manual</option>
            <option value="popup">Popup</option>
            <option value="landing">Landing</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary" style="height:38px;">Agregar</button>
</form>
@error('email') <div class="alert alert-error" style="margin-bottom:1rem;">{{ $message }}</div> @enderror

{{-- Filters --}}
<div class="filters-bar">
    <form method="GET" action="{{ route('admin.newsletters.subscribers') }}" style="display:flex; gap:0.75rem; flex-wrap:wrap; align-items:center;">
        <input type="text" name="search" class="form-input" placeholder="Buscar por email..." value="{{ request('search') }}" style="max-width:240px;">
        <select name="status" class="form-select" onchange="this.form.submit()" style="max-width:180px;">
            <option value="">Todos</option>
            <option value="subscribed" {{ request('status') === 'subscribed' ? 'selected' : '' }}>Activos</option>
            <option value="unsubscribed" {{ request('status') === 'unsubscribed' ? 'selected' : '' }}>Desuscritos</option>
        </select>
        <button type="submit" class="btn btn-primary" style="padding:0.5rem 1rem;">Filtrar</button>
        @if(request()->hasAny(['search', 'status']))
        <a href="{{ route('admin.newsletters.subscribers') }}" class="btn btn-outline" style="padding:0.5rem 1rem;">Limpiar</a>
        @endif
    </form>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-body" style="padding:0;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Fuente</th>
                    <th>Cliente</th>
                    <th>Suscrito</th>
                    <th>Estado</th>
                    <th style="width:80px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscribers as $sub)
                <tr>
                    <td style="font-size:0.85rem; font-weight:500;">{{ $sub->email }}</td>
                    <td><span class="source-badge">{{ $sub->source ?? '-' }}</span></td>
                    <td style="font-size:0.82rem;">
                        @if($sub->client)
                        <a href="{{ route('clients.show', $sub->client) }}" style="color:var(--primary);">{{ $sub->client->name }}</a>
                        @else
                        <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td style="font-size:0.78rem; color:var(--text-muted);">{{ $sub->subscribed_at?->format('d/m/Y H:i') ?? $sub->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        @if($sub->unsubscribed_at)
                        <span class="badge badge-red">Desuscrito</span>
                        @else
                        <span class="badge badge-green">Activo</span>
                        @endif
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.newsletters.subscribers.destroy', $sub) }}" onsubmit="return confirm('Eliminar suscriptor?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn" style="padding:0.3rem 0.6rem; font-size:0.75rem; color:var(--danger);">&#10005;</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:2rem; color:var(--text-muted);">No hay suscriptores.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($subscribers->hasPages())
<div style="margin-top:1rem; display:flex; justify-content:center;">{{ $subscribers->links() }}</div>
@endif
@endsection
