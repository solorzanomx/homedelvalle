@extends('layouts.app-sidebar')
@section('title', 'Documentos Legales')

@section('content')
<div class="page-header">
    <div>
        <h2>Documentos Legales</h2>
        <p class="text-muted">Avisos de privacidad, terminos y condiciones</p>
    </div>
    <a href="{{ route('admin.legal.create') }}" class="btn btn-primary">+ Nuevo Documento</a>
</div>

{{-- Stats --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-blue">&#128196;</div>
        <div>
            <div class="stat-value">{{ $totalDocuments ?? 0 }}</div>
            <div class="stat-label">Total Documentos</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-green">&#10003;</div>
        <div>
            <div class="stat-value">{{ $publishedCount ?? 0 }}</div>
            <div class="stat-label">Publicados</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-purple">&#128221;</div>
        <div>
            <div class="stat-value">{{ $totalVersions ?? 0 }}</div>
            <div class="stat-label">Versiones Totales</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-orange">&#9745;</div>
        <div>
            <div class="stat-value">{{ $totalAcceptances ?? 0 }}</div>
            <div class="stat-label">Total Aceptaciones</div>
        </div>
    </div>
</div>

{{-- Documents Table --}}
<div class="card">
    <div class="card-header">
        <h3>Todos los Documentos</h3>
        @if(Route::has('admin.legal.acceptances'))
            <a href="{{ route('admin.legal.acceptances') }}" class="btn btn-sm btn-outline">Ver Todas las Aceptaciones</a>
        @endif
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Titulo</th>
                        <th>Tipo</th>
                        <th>Version Actual</th>
                        <th>Estado</th>
                        <th>Aceptaciones</th>
                        <th>Actualizado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $document)
                    <tr>
                        <td style="font-weight:500;">{{ $document->title }}</td>
                        <td>
                            @php
                                $typeBadges = [
                                    'aviso_privacidad' => 'badge-blue',
                                    'terminos_condiciones' => 'badge-purple',
                                    'contrato' => 'badge-yellow',
                                    'otro' => 'badge-green',
                                ];
                                $badgeClass = $typeBadges[$document->type] ?? 'badge-blue';
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ \App\Models\LegalDocument::TYPES[$document->type] ?? $document->type }}</span>
                        </td>
                        <td class="text-muted">v{{ $document->currentVersion?->version_number ?? '0' }}</td>
                        <td>
                            @if($document->status === 'published')
                                <span class="badge badge-green">Publicado</span>
                            @else
                                <span class="badge badge-yellow">Borrador</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $document->acceptances_count ?? $document->acceptances()->count() }}</td>
                        <td class="text-muted" style="font-size:0.85rem;">{{ $document->updated_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="action-btns">
                                <a href="{{ route('admin.legal.show', $document) }}" class="btn btn-sm btn-outline">Ver</a>
                                <a href="{{ route('admin.legal.edit', $document) }}" class="btn btn-sm btn-outline">Editar</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted" style="padding:3rem;">
                            <div style="font-size:2rem; margin-bottom:0.5rem;">&#128196;</div>
                            <p style="margin-bottom:0.75rem;">No hay documentos legales.</p>
                            <a href="{{ route('admin.legal.create') }}" class="btn btn-primary btn-sm">+ Crear primer documento</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
