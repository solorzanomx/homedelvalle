@extends('layouts.app-sidebar')
@section('title', 'Renta #' . $rental->id)

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Renta #{{ $rental->id }}</h1>
        <p class="page-subtitle">{{ $rental->property?->address ?? 'Sin propiedad' }}</p>
    </div>
    <div class="page-header-right">
        <a href="{{ route('admin.rentas.gestion') }}" class="btn btn-secondary btn-sm">
            <x-icon name="arrow-left" class="w-4 h-4" /> Gestión Post-Cierre
        </a>
        <a href="{{ route('rentals.show', $rental->id) }}" class="btn btn-secondary btn-sm">
            Vista clásica
        </a>
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.25rem;">

    {{-- Columna izquierda --}}
    <div style="display:flex;flex-direction:column;gap:1.25rem;">

        {{-- Datos del contrato --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">Contrato</h3></div>
            <div class="card-body" style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem 1.5rem;">
                @foreach([
                    ['Etapa', $rental->stage_label ?? $rental->stage],
                    ['Renta mensual', $rental->monthly_rent ? '$'.number_format($rental->monthly_rent) : '—'],
                    ['Depósito', $rental->deposit_amount ? '$'.number_format($rental->deposit_amount) : '—'],
                    ['Garantía', $rental->guarantee_type_label ?? $rental->guarantee_type ?? '—'],
                    ['Inicio', $rental->lease_start_date?->format('d/m/Y') ?? '—'],
                    ['Fin', $rental->lease_end_date?->format('d/m/Y') ?? '—'],
                    ['Duración', $rental->lease_duration_months ? $rental->lease_duration_months.' meses' : '—'],
                    ['Día de pago', $rental->payment_day ? 'Día '.$rental->payment_day : '—'],
                ] as [$lbl, $val])
                <div>
                    <p style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;margin-bottom:.2rem;">{{ $lbl }}</p>
                    <p style="font-size:.85rem;font-weight:600;color:#0f172a;">{{ $val }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Timeline de etapas --}}
        @if($rental->stageLogs->count())
        <div class="card">
            <div class="card-header"><h3 class="card-title">Historial de etapas</h3></div>
            <div class="card-body" style="padding:0;">
                @foreach($rental->stageLogs->take(10) as $log)
                <div style="display:flex;align-items:center;gap:.75rem;padding:.65rem 1rem;border-bottom:1px solid #f1f5f9;">
                    <span style="width:8px;height:8px;border-radius:50%;background:#3B82C4;flex-shrink:0;"></span>
                    <div style="flex:1;min-width:0;">
                        <p style="font-size:.78rem;font-weight:600;color:#0f172a;">
                            {{ $log->from_stage }} → {{ $log->to_stage }}
                        </p>
                        @if($log->notes)
                        <p style="font-size:.7rem;color:#64748b;margin-top:.1rem;">{{ $log->notes }}</p>
                        @endif
                    </div>
                    <div style="text-align:right;flex-shrink:0;">
                        <p style="font-size:.68rem;color:#94a3b8;">{{ $log->user?->name ?? '—' }}</p>
                        <p style="font-size:.65rem;color:#cbd5e1;">{{ $log->created_at->format('d/m H:i') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Documentos --}}
        @if($rental->documents->count())
        <div class="card">
            <div class="card-header"><h3 class="card-title">Documentos ({{ $rental->documents->count() }})</h3></div>
            <div class="card-body" style="padding:0;">
                @foreach($rental->documents as $doc)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;">
                    <div>
                        <p style="font-size:.78rem;font-weight:600;color:#0f172a;">{{ $doc->label ?? $doc->file_name }}</p>
                        <p style="font-size:.68rem;color:#64748b;">{{ $doc->category }}</p>
                    </div>
                    <a href="{{ route('documents.download', $doc->id) }}" style="font-size:.75rem;color:#1D4ED8;font-weight:600;text-decoration:none;">Descargar</a>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Columna derecha --}}
    <div style="display:flex;flex-direction:column;gap:1.25rem;">

        {{-- Propietario --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">Propietario</h3></div>
            <div class="card-body">
                @if($rental->ownerClient)
                <p style="font-size:.85rem;font-weight:600;color:#0f172a;">{{ $rental->ownerClient->name }}</p>
                <p style="font-size:.75rem;color:#64748b;margin-top:.2rem;">{{ $rental->ownerClient->email }}</p>
                <p style="font-size:.75rem;color:#64748b;">{{ $rental->ownerClient->phone }}</p>
                <a href="{{ route('clients.show', $rental->owner_client_id) }}" style="display:inline-block;margin-top:.5rem;font-size:.72rem;color:#1D4ED8;font-weight:600;text-decoration:none;">Ver perfil →</a>
                @else
                <p style="font-size:.8rem;color:#94a3b8;">Sin propietario asignado</p>
                @endif
            </div>
        </div>

        {{-- Inquilino --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">Inquilino</h3></div>
            <div class="card-body">
                @if($rental->tenantClient)
                <p style="font-size:.85rem;font-weight:600;color:#0f172a;">{{ $rental->tenantClient->name }}</p>
                <p style="font-size:.75rem;color:#64748b;margin-top:.2rem;">{{ $rental->tenantClient->email }}</p>
                <p style="font-size:.75rem;color:#64748b;">{{ $rental->tenantClient->phone }}</p>
                <a href="{{ route('clients.show', $rental->tenant_client_id) }}" style="display:inline-block;margin-top:.5rem;font-size:.72rem;color:#1D4ED8;font-weight:600;text-decoration:none;">Ver perfil →</a>
                @else
                <p style="font-size:.8rem;color:#94a3b8;">Sin inquilino asignado</p>
                @endif
            </div>
        </div>

        {{-- Póliza jurídica --}}
        @if($rental->polizaJuridica)
        <div class="card">
            <div class="card-header"><h3 class="card-title">Póliza Jurídica</h3></div>
            <div class="card-body">
                <p style="font-size:.78rem;color:#0f172a;">{{ $rental->polizaJuridica->provider ?? 'Activa' }}</p>
                @if($rental->polizaJuridica->expiry_date)
                <p style="font-size:.72rem;color:#64748b;margin-top:.2rem;">Vence: {{ $rental->polizaJuridica->expiry_date->format('d/m/Y') }}</p>
                @endif
            </div>
        </div>
        @endif

        {{-- Inmueble --}}
        @if($rental->property)
        <div class="card">
            <div class="card-header"><h3 class="card-title">Inmueble</h3></div>
            <div class="card-body">
                <p style="font-size:.82rem;font-weight:600;color:#0f172a;">{{ $rental->property->address }}</p>
                @if($rental->property->colony)
                <p style="font-size:.72rem;color:#64748b;margin-top:.15rem;">{{ $rental->property->colony }}</p>
                @endif
                <a href="{{ route('admin.properties.show', $rental->property_id) }}" style="display:inline-block;margin-top:.5rem;font-size:.72rem;color:#1D4ED8;font-weight:600;text-decoration:none;">Ver ficha →</a>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
