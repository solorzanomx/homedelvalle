@extends('layouts.portal')
@section('title', 'Mis Rentas')

@section('content')
<div class="page-header">
    <div>
        <h2>Mis Procesos de Renta</h2>
        <p class="text-muted">{{ $rentals->count() }} procesos</p>
    </div>
</div>

@if(!$client || $rentals->isEmpty())
    <div class="card">
        <div class="card-body empty-state">
            <div class="empty-state-icon">&#127968;</div>
            <p>No tienes procesos de renta asociados.</p>
        </div>
    </div>
@else
    @foreach($rentals as $rental)
    <div class="card" style="margin-bottom:1rem;">
        <div class="card-body" style="display:flex; align-items:flex-start; gap:1rem;">
            <div style="font-size:2rem; flex-shrink:0;">&#127968;</div>
            <div style="flex:1;">
                <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.3rem;">
                    <span style="font-size:1rem; font-weight:600;">{{ $rental->property->title ?? 'Sin propiedad' }}</span>
                    @if($rental->owner_client_id === $client->id)
                        <span class="badge badge-blue">Propietario</span>
                    @else
                        <span class="badge badge-purple">Inquilino</span>
                    @endif
                </div>

                {{-- Stage Progress --}}
                @php
                    $stageKeys = array_keys(\App\Models\RentalProcess::STAGES);
                    $currentIdx = array_search($rental->stage, $stageKeys);
                @endphp
                <div class="stage-bar" style="margin:0.5rem 0;">
                    @foreach($stageKeys as $i => $sk)
                        <div class="stage-seg {{ $i < $currentIdx ? 'done' : ($i === $currentIdx ? 'now' : '') }}"></div>
                    @endforeach
                </div>
                <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.5rem;">
                    <span style="width:10px; height:10px; border-radius:50%; background:{{ $rental->stage_color }};"></span>
                    <span style="font-size:0.85rem; font-weight:500;">{{ $rental->stage_label }}</span>
                </div>

                <div style="display:flex; gap:1.5rem; font-size:0.82rem; color:var(--text-muted);">
                    @if($rental->monthly_rent)
                    <span>Renta: {{ $rental->currency ?? 'MXN' }} ${{ number_format($rental->monthly_rent, 0) }}</span>
                    @endif
                    @if($rental->lease_start_date)
                    <span>Inicio: {{ $rental->lease_start_date->format('d/m/Y') }}</span>
                    @endif
                    @if($rental->lease_end_date)
                    <span>Fin: {{ $rental->lease_end_date->format('d/m/Y') }}
                        @if($rental->is_expired)
                            <span class="badge badge-red" style="margin-left:3px;">Vencido</span>
                        @elseif($rental->days_until_expiration !== null && $rental->days_until_expiration <= 30)
                            <span class="badge badge-yellow" style="margin-left:3px;">{{ $rental->days_until_expiration }}d</span>
                        @endif
                    </span>
                    @endif
                </div>

                @if($rental->broker)
                <div style="font-size:0.78rem; color:var(--text-muted); margin-top:0.35rem;">
                    Asesor: {{ $rental->broker->name }}
                </div>
                @endif
            </div>
            <a href="{{ route('portal.rentals.show', $rental->id) }}" class="btn btn-outline">Ver Detalle</a>
        </div>
    </div>
    @endforeach
@endif
@endsection
