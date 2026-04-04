@extends('layouts.app-sidebar')
@section('title', 'Aceptaciones: ' . $document->title)

@section('content')
<div class="page-header">
    <div>
        <h2>Aceptaciones: {{ $document->title }}</h2>
        <p class="text-muted">{{ $acceptances->total() }} aceptaciones registradas</p>
    </div>
    <a href="{{ route('admin.legal.show', $document) }}" class="btn btn-outline">&#8592; Volver al documento</a>
</div>

{{-- Filter Bar --}}
<div class="card" style="margin-bottom:1.25rem;">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.legal.document.acceptances', $document) }}" style="display:flex; gap:0.75rem; align-items:flex-end; flex-wrap:wrap;">
            <div class="form-group" style="margin-bottom:0; flex:1; min-width:200px;">
                <label class="form-label">Buscar por email</label>
                <input type="text" name="email" value="{{ request('email') }}" class="form-input" placeholder="correo@ejemplo.com">
            </div>
            <div class="form-group" style="margin-bottom:0; min-width:160px;">
                <label class="form-label">Desde</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input">
            </div>
            <div class="form-group" style="margin-bottom:0; min-width:160px;">
                <label class="form-label">Hasta</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input">
            </div>
            <div style="display:flex; gap:0.5rem;">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                @if(request()->hasAny(['email', 'date_from', 'date_to']))
                    <a href="{{ route('admin.legal.document.acceptances', $document) }}" class="btn btn-outline">Limpiar</a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Acceptances Table --}}
<div class="card">
    <div class="card-header">
        <h3>Registro de Aceptaciones</h3>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>IP</th>
                        <th>User Agent</th>
                        <th>Version</th>
                        <th>Contexto</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($acceptances as $acceptance)
                    <tr>
                        <td style="font-weight:500;">{{ $acceptance->email }}</td>
                        <td class="text-muted" style="font-size:0.82rem;">{{ $acceptance->ip_address }}</td>
                        <td class="text-muted" style="font-size:0.78rem; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $acceptance->user_agent }}">
                            {{ Str::limit($acceptance->user_agent, 40) }}
                        </td>
                        <td class="text-muted">v{{ $acceptance->version?->version_number ?? '-' }}</td>
                        <td><span class="badge badge-blue">{{ $acceptance->context ?? 'web' }}</span></td>
                        <td class="text-muted" style="font-size:0.85rem;">{{ $acceptance->accepted_at?->format('d/m/Y H:i') ?? $acceptance->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted" style="padding:3rem;">
                            <div style="font-size:2rem; margin-bottom:0.5rem;">&#9745;</div>
                            <p>No hay aceptaciones registradas
                                @if(request()->hasAny(['email', 'date_from', 'date_to']))
                                    con los filtros seleccionados.
                                @else
                                    para este documento.
                                @endif
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($acceptances->hasPages())
<div style="margin-top:1rem; display:flex; justify-content:center;">
    {{ $acceptances->withQueryString()->links() }}
</div>
@endif
@endsection
