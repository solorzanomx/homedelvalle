@extends('layouts.app-sidebar')
@section('title', 'Todas las Aceptaciones')

@section('content')
<div class="page-header">
    <div>
        <h2>Todas las Aceptaciones</h2>
        <p class="text-muted">Registro global de aceptaciones legales</p>
    </div>
    <a href="{{ route('admin.legal.index') }}" class="btn btn-outline">&#8592; Documentos Legales</a>
</div>

{{-- Stats --}}
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
    <div class="stat-card">
        <div class="stat-icon bg-blue">&#9745;</div>
        <div>
            <div class="stat-value">{{ $totalAcceptances ?? 0 }}</div>
            <div class="stat-label">Total Aceptaciones</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-green">&#128197;</div>
        <div>
            <div class="stat-value">{{ $thisMonth ?? 0 }}</div>
            <div class="stat-label">Este Mes</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-purple">&#128196;</div>
        <div>
            <div class="stat-value">{{ $uniqueDocuments ?? 0 }}</div>
            <div class="stat-label">Documentos Unicos</div>
        </div>
    </div>
</div>

{{-- Filter Bar --}}
<div class="card" style="margin-bottom:1.25rem;">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.legal.acceptances') }}" style="display:flex; gap:0.75rem; align-items:flex-end; flex-wrap:wrap;">
            <div class="form-group" style="margin-bottom:0; min-width:200px;">
                <label class="form-label">Documento</label>
                <select name="document_id" class="form-select">
                    <option value="">Todos los documentos</option>
                    @foreach($documents as $doc)
                        <option value="{{ $doc->id }}" {{ request('document_id') == $doc->id ? 'selected' : '' }}>{{ $doc->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom:0; flex:1; min-width:180px;">
                <label class="form-label">Buscar por email</label>
                <input type="text" name="email" value="{{ request('email') }}" class="form-input" placeholder="correo@ejemplo.com">
            </div>
            <div class="form-group" style="margin-bottom:0; min-width:150px;">
                <label class="form-label">Desde</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input">
            </div>
            <div class="form-group" style="margin-bottom:0; min-width:150px;">
                <label class="form-label">Hasta</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input">
            </div>
            <div style="display:flex; gap:0.5rem;">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                @if(request()->hasAny(['document_id', 'email', 'date_from', 'date_to']))
                    <a href="{{ route('admin.legal.acceptances') }}" class="btn btn-outline">Limpiar</a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Acceptances Table --}}
<div class="card">
    <div class="card-header">
        <h3>Aceptaciones</h3>
        <span class="text-muted" style="font-size:0.82rem;">{{ $acceptances->total() }} resultados</span>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Documento</th>
                        <th>Email</th>
                        <th>IP</th>
                        <th>Version</th>
                        <th>Contexto</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($acceptances as $acceptance)
                    <tr>
                        <td>
                            <a href="{{ route('admin.legal.show', $acceptance->legal_document_id) }}" style="font-weight:500; color:var(--primary);">
                                {{ $acceptance->document?->title ?? 'Documento #' . $acceptance->legal_document_id }}
                            </a>
                        </td>
                        <td style="font-weight:500;">{{ $acceptance->email }}</td>
                        <td class="text-muted" style="font-size:0.82rem;">{{ $acceptance->ip_address }}</td>
                        <td class="text-muted">v{{ $acceptance->version?->version_number ?? '-' }}</td>
                        <td><span class="badge badge-blue">{{ $acceptance->context ?? 'web' }}</span></td>
                        <td class="text-muted" style="font-size:0.85rem;">{{ $acceptance->accepted_at?->format('d/m/Y H:i') ?? $acceptance->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted" style="padding:3rem;">
                            <div style="font-size:2rem; margin-bottom:0.5rem;">&#9745;</div>
                            <p>No hay aceptaciones registradas
                                @if(request()->hasAny(['document_id', 'email', 'date_from', 'date_to']))
                                    con los filtros seleccionados.
                                @else
                                    todavia.
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
