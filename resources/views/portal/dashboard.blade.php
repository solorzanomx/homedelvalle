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
            <p>Tu cuenta aún no está vinculada a un perfil de cliente. Contacta a tu asesor para más información.</p>
        </div>
    </div>
@else

    {{-- ── CLIENTE DE VENTA / CAPTACIÓN ─────────────────── --}}
    @if($isVenta)
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(102,126,234,0.1); color:var(--primary);">&#127968;</div>
            <div>
                <div class="stat-value">{{ $properties->count() }}</div>
                <div class="stat-label">{{ $properties->count() === 1 ? 'Inmueble' : 'Inmuebles' }} en gestión</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(16,185,129,0.1); color:var(--success);">&#128196;</div>
            <div>
                <div class="stat-value">{{ $documents->count() }}</div>
                <div class="stat-label">Documentos</div>
            </div>
        </div>
    </div>

    {{-- Propiedades del cliente --}}
    <div class="card" style="margin-bottom:1.25rem;">
        <div class="card-header">
            <h3>Mi Inmueble</h3>
        </div>
        @if($properties->isEmpty())
            <div class="card-body empty-state" style="padding:2rem;">
                <div class="empty-state-icon">&#127968;</div>
                <p>Aún no tenemos registrado tu inmueble. Tu asesor lo añadirá pronto.</p>
            </div>
        @else
            <div class="card-body" style="padding:0;">
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr><th>Inmueble</th><th>Tipo</th><th>Precio</th><th>Estado</th></tr>
                        </thead>
                        <tbody>
                            @foreach($properties as $property)
                            <tr>
                                <td style="font-weight:500;">{{ \Illuminate\Support\Str::limit($property->title ?? $property->address, 40) }}</td>
                                <td class="text-muted">{{ ucfirst($property->property_type ?? '—') }}</td>
                                <td>{{ $property->price ? '$' . number_format($property->price, 0) : '—' }}</td>
                                <td>
                                    @php
                                        $statusMap = [
                                            'available'  => ['label' => 'Disponible',   'class' => 'badge-green'],
                                            'sold'       => ['label' => 'Vendido',       'class' => 'badge-blue'],
                                            'rented'     => ['label' => 'Rentado',       'class' => 'badge-purple'],
                                            'inactive'   => ['label' => 'Inactivo',      'class' => 'badge-yellow'],
                                        ];
                                        $s = $statusMap[$property->status] ?? ['label' => ucfirst($property->status ?? '—'), 'class' => 'badge-yellow'];
                                    @endphp
                                    <span class="badge {{ $s['class'] }}">{{ $s['label'] }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    {{-- Próximos pasos captación --}}
    @php $captacion = \App\Models\Captacion::where('client_id', $client->id)->where('status', 'activo')->latest()->first(); @endphp
    <div class="card" style="margin-bottom:1.25rem;">
        <div class="card-header">
            <h3>Proceso de captación</h3>
            <a href="{{ route('portal.captacion') }}" class="btn btn-sm btn-primary">Ver mi proceso</a>
        </div>
        <div class="card-body">
            @if($captacion)
            @php $etapa = $captacion->portal_etapa; @endphp
            <div class="stage-bar" style="height:8px; margin-bottom:1rem;">
                @for($i = 1; $i <= 4; $i++)
                <div class="stage-seg {{ $i < $etapa ? 'done' : ($i === $etapa ? 'now' : '') }}"></div>
                @endfor
            </div>
            <div style="display:flex; justify-content:space-between; font-size:.75rem; color:var(--text-muted); margin-bottom:1rem;">
                <span>Documentación</span><span>Valuación</span><span>Precio</span><span>Exclusiva</span>
            </div>
            <p style="font-size:.85rem;">
                @if($etapa === 1) Sube tus documentos para avanzar al siguiente paso.
                @elseif($etapa === 2) Documentos aprobados. Aguardando valuación de tu inmueble.
                @elseif($etapa === 3) Valuación lista. Revisa y confirma el precio de venta.
                @elseif($etapa === 4) Precio acordado. Pendiente firma de contrato de exclusiva.
                @endif
            </p>
            @else
            <p style="font-size:.85rem; color:var(--text-muted);">Tu proceso de captación aún no ha iniciado. Contacta a tu asesor.</p>
            @endif
        </div>
    </div>
    @endif

    {{-- ── CLIENTE DE RENTA ──────────────────────────────── --}}
    @if($isRental)
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(102,126,234,0.1); color:var(--primary);">&#127968;</div>
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
                            <tr><th>Propiedad</th><th>Rol</th><th>Etapa</th><th>Renta</th><th></th></tr>
                        </thead>
                        <tbody>
                            @foreach($rentals->take(5) as $rental)
                            <tr>
                                <td style="font-weight:500;">{{ \Illuminate\Support\Str::limit($rental->property->title ?? 'Sin propiedad', 35) }}</td>
                                <td>
                                    @if($rental->owner_client_id === $client->id)
                                        <span class="badge badge-blue">Propietario</span>
                                    @else
                                        <span class="badge badge-purple">Inquilino</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge" style="background:{{ $rental->stage_color . '20' }}; color:{{ $rental->stage_color }};">
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
    @endif

    {{-- ── Sin tipo definido ─────────────────────────────── --}}
    @if(!$isVenta && !$isRental)
    <div class="card">
        <div class="card-body empty-state">
            <div class="empty-state-icon">&#128100;</div>
            <p>Tu asesor está configurando tu expediente. Pronto verás tu información aquí.</p>
        </div>
    </div>
    @endif

    {{-- ── Documentos pendientes (para todos) ───────────── --}}
    @php $pendingDocs = $documents->whereIn('status', ['pending', 'rejected']); @endphp
    @if($pendingDocs->isNotEmpty())
    <div class="card">
        <div class="card-header"><h3>Documentos que Requieren Atención</h3></div>
        <div class="card-body" style="padding:0;">
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Documento</th><th>Categoría</th><th>Estado</th><th>Fecha</th></tr></thead>
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
