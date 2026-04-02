@extends('layouts.portal')
@section('title', $rental->property->title ?? 'Renta #' . $rental->id)

@section('content')
<div class="page-header">
    <div>
        <h2>{{ $rental->property->title ?? 'Renta #' . $rental->id }}</h2>
        <p class="text-muted">
            Eres: <strong>{{ $role === 'propietario' ? 'Propietario' : 'Inquilino' }}</strong>
        </p>
    </div>
    <a href="{{ route('portal.rentals.index') }}" class="btn btn-outline">&#8592; Mis Rentas</a>
</div>

{{-- Stage Progress --}}
@php
    $stageKeys = array_keys(\App\Models\RentalProcess::STAGES);
    $currentIdx = array_search($rental->stage, $stageKeys);
@endphp
<div class="card" style="margin-bottom:1.25rem;">
    <div class="card-body">
        <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.75rem;">
            <span style="width:12px; height:12px; border-radius:50%; background:{{ $rental->stage_color }};"></span>
            <span style="font-size:0.95rem; font-weight:600;">{{ $rental->stage_label }}</span>
        </div>
        <div class="stage-bar">
            @foreach($stageKeys as $i => $sk)
                <div class="stage-seg {{ $i < $currentIdx ? 'done' : ($i === $currentIdx ? 'now' : '') }}" title="{{ \App\Models\RentalProcess::STAGES[$sk] }}"></div>
            @endforeach
        </div>
        <div style="display:flex; gap:0.5rem; margin-top:0.5rem; flex-wrap:wrap;">
            @foreach($stageKeys as $i => $sk)
                <span style="font-size:0.65rem; flex:1; text-align:center; {{ $i === $currentIdx ? 'font-weight:700; color:var(--primary);' : 'color:var(--text-muted);' }}">
                    {{ \App\Models\RentalProcess::STAGES[$sk] }}
                </span>
            @endforeach
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.25rem;">
    {{-- Property Info --}}
    <div class="card">
        <div class="card-header"><h3>Propiedad</h3></div>
        <div class="card-body">
            @if($rental->property)
            <div class="detail-row"><span class="label">Titulo</span><span class="value">{{ $rental->property->title }}</span></div>
            <div class="detail-row"><span class="label">Direccion</span><span class="value">{{ $rental->property->address ?? '—' }}</span></div>
            @endif
            <div class="detail-row"><span class="label">Renta Mensual</span><span class="value">{{ $rental->currency ?? 'MXN' }} ${{ number_format($rental->monthly_rent ?? 0, 0) }}</span></div>
            @if($rental->deposit_amount)
            <div class="detail-row"><span class="label">Deposito</span><span class="value">${{ number_format($rental->deposit_amount, 0) }}</span></div>
            @endif
            @if($rental->guarantee_type)
            <div class="detail-row"><span class="label">Garantia</span><span class="value">{{ $rental->guarantee_type_label }}</span></div>
            @endif
        </div>
    </div>

    {{-- Contract Info --}}
    <div class="card">
        <div class="card-header"><h3>Contrato</h3></div>
        <div class="card-body">
            @if($rental->lease_start_date)
            <div class="detail-row"><span class="label">Inicio</span><span class="value">{{ $rental->lease_start_date->format('d/m/Y') }}</span></div>
            @endif
            @if($rental->lease_end_date)
            <div class="detail-row">
                <span class="label">Vencimiento</span>
                <span class="value">
                    {{ $rental->lease_end_date->format('d/m/Y') }}
                    @if($rental->is_expired)
                        <span class="badge badge-red">Vencido</span>
                    @elseif($rental->days_until_expiration !== null && $rental->days_until_expiration <= 60)
                        <span class="badge badge-yellow">{{ $rental->days_until_expiration }} dias</span>
                    @endif
                </span>
            </div>
            @endif
            @if($rental->lease_duration_months)
            <div class="detail-row"><span class="label">Duracion</span><span class="value">{{ $rental->lease_duration_months }} meses</span></div>
            @endif
            @if($rental->broker)
            <div class="detail-row"><span class="label">Asesor</span><span class="value">{{ $rental->broker->name }}</span></div>
            @endif
        </div>
    </div>
</div>

{{-- Documents --}}
<div class="card" style="margin-bottom:1.25rem;">
    <div class="card-header">
        <h3>Documentos ({{ $rental->documents->count() }})</h3>
    </div>
    <div class="card-body" style="padding:0;">
        @if($rental->documents->isEmpty())
            <div class="empty-state" style="padding:2rem;">
                <p>Sin documentos subidos.</p>
            </div>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Documento</th><th>Categoria</th><th>Estado</th><th>Fecha</th><th></th></tr></thead>
                    <tbody>
                        @foreach($rental->documents->sortByDesc('created_at') as $doc)
                        <tr>
                            <td style="font-weight:500;">{{ $doc->label }}</td>
                            <td class="text-muted">{{ $doc->category_label }}</td>
                            <td>
                                <span class="badge badge-{{ match($doc->status) { 'verified' => 'green', 'rejected' => 'red', 'received' => 'blue', default => 'yellow' } }}">
                                    {{ $doc->status_label }}
                                </span>
                                @if($doc->status === 'rejected' && $doc->rejection_reason)
                                    <div style="font-size:0.72rem; color:var(--danger); margin-top:0.15rem;">{{ $doc->rejection_reason }}</div>
                                @endif
                            </td>
                            <td class="text-muted">{{ $doc->created_at->format('d/m/Y') }}</td>
                            <td><a href="{{ route('portal.documents.download', $doc->id) }}" class="btn btn-sm btn-outline">Descargar</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- Upload Document --}}
<div class="card" style="margin-bottom:1.25rem;">
    <div class="card-header"><h3>Subir Documento</h3></div>
    <div class="card-body">
        <form method="POST" action="{{ route('portal.documents.upload') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="rental_process_id" value="{{ $rental->id }}">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Categoria</label>
                    <select name="category" class="form-select" required>
                        @foreach(\App\Models\Document::CATEGORIES as $ck => $cl)
                            <option value="{{ $ck }}">{{ $cl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Etiqueta</label>
                    <input type="text" name="label" class="form-input" required placeholder="Nombre del documento">
                </div>
                <div class="form-group">
                    <label class="form-label">Archivo</label>
                    <input type="file" name="file" class="form-input" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                </div>
                <div class="form-group" style="display:flex; align-items:flex-end;">
                    <button type="submit" class="btn btn-primary" style="width:100%;">Subir</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Contracts --}}
@if($rental->contracts->isNotEmpty())
<div class="card">
    <div class="card-header"><h3>Contratos ({{ $rental->contracts->count() }})</h3></div>
    <div class="card-body" style="padding:0;">
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Contrato</th><th>Tipo</th><th>Firma</th><th>Fecha</th><th></th></tr></thead>
                <tbody>
                    @foreach($rental->contracts->sortByDesc('created_at') as $contract)
                    <tr>
                        <td style="font-weight:500;">{{ $contract->title }}</td>
                        <td class="text-muted">{{ \App\Models\ContractTemplate::TYPES[$contract->type] ?? ucfirst($contract->type) }}</td>
                        <td>
                            <span class="badge badge-{{ match($contract->signature_status) { 'signed' => 'green', 'pending_signature' => 'yellow', default => 'blue' } }}">
                                {{ $contract->signature_status_label }}
                            </span>
                        </td>
                        <td class="text-muted">{{ $contract->created_at->format('d/m/Y') }}</td>
                        <td>
                            @if($contract->pdf_path)
                                <a href="{{ route('contracts.download', $contract->id) }}" class="btn btn-sm btn-outline">Descargar</a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- Stage History --}}
@if($rental->stageLogs->isNotEmpty())
<div class="card" style="margin-top:1.25rem;">
    <div class="card-header"><h3>Historial del Proceso</h3></div>
    <div class="card-body" style="padding:0;">
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>De</th><th>A</th><th>Notas</th><th>Fecha</th></tr></thead>
                <tbody>
                    @foreach($rental->stageLogs->sortByDesc('created_at') as $log)
                    <tr>
                        <td class="text-muted">{{ \App\Models\RentalProcess::STAGES[$log->from_stage] ?? $log->from_stage }}</td>
                        <td style="font-weight:500;">{{ \App\Models\RentalProcess::STAGES[$log->to_stage] ?? $log->to_stage }}</td>
                        <td class="text-muted">{{ $log->notes ?? '—' }}</td>
                        <td class="text-muted">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
