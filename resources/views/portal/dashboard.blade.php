@extends('layouts.portal')
@section('title', 'Mi Portal')

@section('content')
<div class="page-header">
    <div>
        <h2>Bienvenido, {{ Auth::user()->name }}</h2>
        <p class="text-muted">Resumen de tu actividad</p>
    </div>
</div>

@if(!$client)
    <div class="card">
        <div class="card-body empty-state">
            <div class="empty-state-icon">&#128100;</div>
            <p>Tu cuenta aun no esta vinculada a un perfil de cliente. Contacta a tu asesor para mas informacion.</p>
        </div>
    </div>
@else
    {{-- Stats --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(59,130,196,0.1); color:var(--primary);">&#127968;</div>
            <div>
                <div class="stat-value">{{ $rentals->count() }}</div>
                <div class="stat-label">Procesos de Renta</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(16,185,129,0.1); color:var(--success);">&#128196;</div>
            <div>
                <div class="stat-value">{{ $documents->count() }}</div>
                <div class="stat-label">Documentos</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(139,92,246,0.1); color:#8b5cf6;">&#128221;</div>
            <div>
                <div class="stat-value">{{ $contracts->count() }}</div>
                <div class="stat-label">Contratos</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(245,158,11,0.1); color:#f59e0b;">&#128276;</div>
            <div>
                <div class="stat-value">{{ $rentals->where('stage', 'activo')->count() }}</div>
                <div class="stat-label">Rentas Activas</div>
            </div>
        </div>
    </div>

    {{-- Recent Rentals --}}
    <div class="card" style="margin-bottom:1.25rem;">
        <div class="card-header">
            <h3>Mis Procesos de Renta</h3>
            <a href="{{ route('portal.rentals.index') }}" class="btn btn-sm btn-outline">Ver todos</a>
        </div>
        <div class="card-body" style="padding:0;">
            @if($rentals->isEmpty())
                <div class="empty-state" style="padding:2rem;">
                    <p>No tienes procesos de renta activos.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Propiedad</th>
                                <th>Rol</th>
                                <th>Etapa</th>
                                <th>Renta</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rentals->take(5) as $rental)
                            <tr>
                                <td style="font-weight:500;">{{ Str::limit($rental->property->title ?? 'Sin propiedad', 35) }}</td>
                                <td>
                                    @if($rental->owner_client_id === $client->id)
                                        <span class="badge badge-blue">Propietario</span>
                                    @else
                                        <span class="badge badge-purple">Inquilino</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge" style="background: {{ $rental->stage_color . '20' }}; color: {{ $rental->stage_color }};">
                                        {{ $rental->stage_label }}
                                    </span>
                                </td>
                                <td>{{ $rental->monthly_rent ? '$' . number_format($rental->monthly_rent, 0) : '—' }}</td>
                                <td><a href="{{ route('portal.rentals.show', $rental->id) }}" class="btn btn-sm btn-outline">Ver</a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Recent Documents Pending --}}
    @php $pendingDocs = $documents->whereIn('status', ['pending', 'rejected']); @endphp
    @if($pendingDocs->isNotEmpty())
    <div class="card">
        <div class="card-header">
            <h3>Documentos que Requieren Atencion</h3>
        </div>
        <div class="card-body" style="padding:0;">
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Documento</th><th>Categoria</th><th>Estado</th><th>Fecha</th></tr></thead>
                    <tbody>
                        @foreach($pendingDocs->take(5) as $doc)
                        <tr>
                            <td style="font-weight:500;">{{ $doc->label }}</td>
                            <td class="text-muted">{{ $doc->category_label }}</td>
                            <td>
                                <span class="badge badge-{{ $doc->status === 'rejected' ? 'red' : 'yellow' }}">
                                    {{ $doc->status_label }}
                                </span>
                            </td>
                            <td class="text-muted">{{ $doc->created_at->format('d/m/Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
@endif
@endsection
