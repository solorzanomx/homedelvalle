@extends('layouts.app-sidebar')
@section('title', 'Contratos')

@section('content')
<div class="page-header">
    <div>
        <h2>Contratos</h2>
        <p class="text-muted">{{ $contracts->total() }} contrato{{ $contracts->total() !== 1 ? 's' : '' }}</p>
    </div>
    <a href="{{ route('contracts.create') }}" class="btn btn-primary" style="white-space:nowrap;">+ Nuevo Contrato</a>
</div>

<form method="GET" action="{{ route('contracts.index') }}" class="card" style="margin-bottom:1rem;">
    <div class="card-body" style="padding:0.85rem; display:flex; gap:0.5rem; flex-wrap:wrap; align-items:flex-end;">
        <div class="form-group" style="flex:1; min-width:180px; margin:0;">
            <label class="form-label" style="font-size:0.72rem;">Buscar</label>
            <input type="text" name="q" class="form-input" value="{{ request('q') }}" placeholder="Cliente, propiedad, folio, título...">
        </div>
        <div class="form-group" style="min-width:160px; margin:0;">
            <label class="form-label" style="font-size:0.72rem;">Tipo</label>
            <select name="type" class="form-select">
                <option value="">Todos</option>
                @foreach(\App\Models\ContractTemplate::TYPES as $key => $label)
                <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group" style="min-width:160px; margin:0;">
            <label class="form-label" style="font-size:0.72rem;">Estado de firma</label>
            <select name="estado" class="form-select">
                <option value="">Todos</option>
                @foreach(\App\Models\Contract::SIGNATURE_STATUSES as $key => $label)
                <option value="{{ $key }}" {{ request('estado') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-outline btn-sm" style="height:38px;">Filtrar</button>
        @if(request()->anyFilled(['q','type','estado']))
        <a href="{{ route('contracts.index') }}" class="btn btn-sm" style="height:38px; display:flex; align-items:center;">Limpiar</a>
        @endif
    </div>
</form>

@if($contracts->isEmpty())
    <div style="text-align:center; padding:3rem; color:var(--text-muted); font-size:0.88rem;">Sin contratos que coincidan.</div>
@else
    @foreach($contracts as $contract)
    @php
        $dealUrl = $contract->operation_id ? route('operations.show', $contract->operation_id) : route('rentals.show', $contract->rental_process_id);
        $dealLabel = $contract->operation
            ? ($contract->operation->client?->name ?? '') . ($contract->operation->secondaryClient ? ' / ' . $contract->operation->secondaryClient->name : '')
            : (($contract->rentalProcess?->ownerClient?->name ?? '') . ($contract->rentalProcess?->tenantClient ? ' / ' . $contract->rentalProcess->tenantClient->name : ''));
        $property = $contract->operation?->property ?? $contract->rentalProcess?->property;
        $propertyLabel = $property?->address ?? $property?->title;
        $statusKey = $contract->currentVersion->signature_status ?? $contract->signature_status;
        $statusLabel = $contract->signature_status_label;
    @endphp
    <div class="card" style="margin-bottom:0.65rem;">
        <div class="card-body" style="padding:0.85rem; display:flex; align-items:flex-start; gap:0.65rem;">
            <div style="font-size:1.3rem; flex-shrink:0;">
                @if($contract->is_signed) &#9989; @elseif($statusKey === 'pending_signature') &#9997; @else &#128196; @endif
            </div>
            <div style="flex:1; overflow:hidden;">
                <div style="font-weight:600; font-size:0.9rem;">{{ $contract->title }}</div>
                <div style="font-size:0.76rem; color:var(--text-muted); margin-top:0.15rem;">
                    {{ \App\Models\ContractTemplate::TYPES[$contract->type] ?? ucfirst($contract->type) }}
                    @if($contract->folio) &middot; Folio {{ $contract->folio }} @endif
                    &middot; <a href="{{ $dealUrl }}" style="color:var(--primary);">{{ $dealLabel ?: 'Ver trato' }}</a>
                    @if($propertyLabel) &middot; {{ $propertyLabel }} @endif
                </div>
                <div style="font-size:0.72rem; color:var(--text-muted); margin-top:0.15rem;">
                    {{ $contract->created_at->format('d/m/Y') }}
                    @if($contract->currentVersion) &middot; Versión {{ $contract->currentVersion->version_number }} @endif
                </div>
            </div>
            <span class="badge badge-{{ match($statusKey) { 'signed' => 'green', 'pending_signature' => 'yellow', default => 'blue' } }}">{{ $statusLabel }}</span>
            <a href="{{ $dealUrl }}" class="btn btn-sm btn-outline">Ver en el trato →</a>
        </div>
    </div>
    @endforeach

    <div style="margin-top:1rem;">{{ $contracts->links() }}</div>
@endif
@endsection
