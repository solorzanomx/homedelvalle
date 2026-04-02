@extends('layouts.app-sidebar')
@section('title', 'Leads / Contactos')

@section('styles')
<style>
    .filters-bar { display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; margin-bottom: 1.5rem; }
    .filters-bar .form-input, .filters-bar .form-select { max-width: 240px; }
    .status-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 0.4rem; }
    .status-dot.unread { background: var(--primary); }
    .status-dot.read { background: var(--border); }
    .utm-badge { display: inline-block; font-size: 0.7rem; padding: 0.15rem 0.5rem; border-radius: 10px; background: rgba(102,126,234,0.08); color: var(--primary); }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Leads / Contactos</h2>
        <p class="text-muted">{{ $unreadCount }} sin leer &middot; {{ $submissions->total() }} total</p>
    </div>
</div>

<div class="filters-bar">
    <form method="GET" action="{{ route('admin.submissions.index') }}" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
        <input type="text" name="search" class="form-input" placeholder="Buscar nombre, email, tel..." value="{{ request('search') }}">
        <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">Todos</option>
            <option value="unread" {{ request('status') === 'unread' ? 'selected' : '' }}>Sin leer</option>
            <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>Leidos</option>
        </select>
        <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem;">Filtrar</button>
        @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('admin.submissions.index') }}" class="btn btn-outline" style="padding: 0.5rem 1rem;">Limpiar</a>
        @endif
    </form>
</div>

<div class="card">
    <div class="card-body" style="padding: 0;">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 30px;"></th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Telefono</th>
                    <th>Propiedad</th>
                    <th>Fuente</th>
                    <th>Fecha</th>
                    <th style="width: 100px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($submissions as $sub)
                <tr style="{{ !$sub->is_read ? 'font-weight: 600;' : '' }}">
                    <td><span class="status-dot {{ $sub->is_read ? 'read' : 'unread' }}"></span></td>
                    <td>
                        <a href="{{ route('admin.submissions.show', $sub) }}" style="color: var(--text); text-decoration: none;">
                            {{ $sub->name }}
                        </a>
                    </td>
                    <td style="font-size: 0.82rem;">{{ $sub->email }}</td>
                    <td style="font-size: 0.82rem;">{{ $sub->phone ?? '-' }}</td>
                    <td style="font-size: 0.82rem;">
                        @if($sub->property)
                            {{ Str::limit($sub->property->title, 30) }}
                        @else
                            <span class="text-muted">General</span>
                        @endif
                    </td>
                    <td>
                        @if($sub->utm_source)
                            <span class="utm-badge">{{ $sub->utm_source }}</span>
                        @else
                            <span class="text-muted" style="font-size: 0.78rem;">-</span>
                        @endif
                    </td>
                    <td style="font-size: 0.78rem; color: var(--text-muted);">{{ $sub->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <div style="display: flex; gap: 0.5rem;">
                            <a href="{{ route('admin.submissions.show', $sub) }}" class="btn btn-outline" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;">Ver</a>
                            <form method="POST" action="{{ route('admin.submissions.destroy', $sub) }}" onsubmit="return confirm('Eliminar este lead?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn" style="padding: 0.3rem 0.6rem; font-size: 0.75rem; color: var(--danger);">&#10005;</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 2rem; color: var(--text-muted);">No hay leads registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($submissions->hasPages())
<div style="margin-top: 1rem; display: flex; justify-content: center;">
    {{ $submissions->links() }}
</div>
@endif
@endsection
