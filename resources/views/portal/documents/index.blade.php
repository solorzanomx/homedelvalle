@extends('layouts.portal')
@section('title', 'Mis Documentos')

@section('content')
<div class="page-header">
    <div>
        <h2>Mis Documentos</h2>
        <p class="text-muted">{{ $documents->count() }} documentos</p>
    </div>
</div>

@if(!$client || $documents->isEmpty())
    <div class="card">
        <div class="card-body empty-state">
            <div class="empty-state-icon">&#128196;</div>
            <p>No tienes documentos asociados.</p>
        </div>
    </div>
@else
    {{-- Stats --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(16,185,129,0.1); color:var(--success);">&#10003;</div>
            <div>
                <div class="stat-value">{{ $documents->where('status', 'verified')->count() }}</div>
                <div class="stat-label">Verificados</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(245,158,11,0.1); color:#f59e0b;">&#9679;</div>
            <div>
                <div class="stat-value">{{ $documents->whereIn('status', ['pending', 'received'])->count() }}</div>
                <div class="stat-label">En Revision</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(239,68,68,0.1); color:var(--danger);">&#10007;</div>
            <div>
                <div class="stat-value">{{ $documents->where('status', 'rejected')->count() }}</div>
                <div class="stat-label">Rechazados</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="padding:0;">
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Categoria</th>
                            <th>Proceso</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documents as $doc)
                        <tr>
                            <td>
                                <div style="font-weight:500;">{{ $doc->label }}</div>
                                <div style="font-size:0.72rem; color:var(--text-muted);">{{ $doc->file_name }} &middot; {{ $doc->file_size_formatted }}</div>
                            </td>
                            <td class="text-muted">{{ $doc->category_label }}</td>
                            <td class="text-muted">
                                @if($doc->rentalProcess)
                                    <a href="{{ route('portal.rentals.show', $doc->rentalProcess->id) }}" style="color:var(--primary);">
                                        {{ Str::limit($doc->rentalProcess->property->title ?? 'Renta #' . $doc->rentalProcess->id, 25) }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ match($doc->status) { 'verified' => 'green', 'rejected' => 'red', 'received' => 'blue', default => 'yellow' } }}">
                                    {{ $doc->status_label }}
                                </span>
                                @if($doc->status === 'rejected' && $doc->rejection_reason)
                                    <div style="font-size:0.72rem; color:var(--danger); margin-top:0.15rem;">{{ $doc->rejection_reason }}</div>
                                @endif
                            </td>
                            <td class="text-muted">{{ $doc->created_at->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('portal.documents.download', $doc->id) }}" class="btn btn-sm btn-outline">Descargar</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
@endsection
