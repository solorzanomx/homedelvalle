@extends('layouts.app-sidebar')
@section('title', 'Captación — ' . ($captacion->client->name ?? ''))

@section('content')
<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem; flex-wrap:wrap;">
    <a href="{{ route('admin.captaciones.index') }}" style="color:var(--text-muted); font-size:.85rem;">← Captaciones</a>
    <h1 style="font-size:1.3rem; font-weight:700; margin:0;">{{ $captacion->client->name ?? 'Cliente' }}</h1>
    <span class="badge badge-{{ $captacion->portal_etapa >= 4 ? 'green' : 'blue' }}">Etapa {{ $captacion->portal_etapa }}</span>
    <span class="badge badge-{{ $captacion->status === 'completado' ? 'green' : ($captacion->status === 'cancelado' ? 'red' : 'yellow') }}">
        {{ ucfirst($captacion->status) }}
    </span>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-error">{{ session('error') }}</div>
@endif

<div style="display:grid; grid-template-columns:1fr 320px; gap:1.25rem; align-items:start;">

{{-- ── Columna principal: Documentos ── --}}
<div>
    <div class="card" style="margin-bottom:1.25rem;">
        <div class="card-header"><h3>Documentos</h3></div>
        <div class="card-body" style="padding:0;">
            @if($captacion->documents->isEmpty())
            <div style="padding:1.5rem; text-align:center; color:var(--text-muted);">El cliente aún no ha subido documentos.</div>
            @else
            <table class="data-table">
                <thead>
                    <tr><th>Categoría</th><th>Archivo</th><th>Estado</th><th>Acción</th></tr>
                </thead>
                <tbody>
                    @foreach($captacion->documents->sortBy('category') as $doc)
                    <tr>
                        <td style="font-weight:500;">{{ $allCategories[$doc->category] ?? $doc->category }}</td>
                        <td style="font-size:.8rem;">
                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" style="color:var(--primary);">
                                {{ $doc->file_name }}
                            </a>
                            <div style="color:var(--text-muted);">{{ $doc->file_size_formatted }}</div>
                        </td>
                        <td>
                            <span class="badge badge-{{ $doc->captacion_status === 'aprobado' ? 'green' : ($doc->captacion_status === 'rechazado' ? 'red' : 'yellow') }}">
                                {{ ucfirst($doc->captacion_status) }}
                            </span>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.captaciones.doc-status', [$captacion, $doc]) }}" style="display:flex; gap:.4rem; align-items:center; flex-wrap:wrap;">
                                @csrf
                                @if($doc->captacion_status !== 'aprobado')
                                <button type="submit" name="captacion_status" value="aprobado" class="btn btn-sm" style="background:#dcfce7; color:#166534;">Aprobar</button>
                                @endif
                                @if($doc->captacion_status !== 'rechazado')
                                <button type="submit" name="captacion_status" value="rechazado" class="btn btn-sm" style="background:#fee2e2; color:#991b1b;"
                                    onclick="return prompt('Razón de rechazo (opcional):') !== null ? (this.form.querySelector('[name=rejection_reason]').value = prompt('Razón:') ?? '', true) : false">
                                    Rechazar
                                </button>
                                <input type="hidden" name="rejection_reason" value="">
                                @endif
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
</div>

{{-- ── Columna lateral: Acciones por etapa ── --}}
<div>

    {{-- Etapa 2: Vincular valuación --}}
    <div class="card" style="margin-bottom:1rem;">
        <div class="card-header"><h3>Etapa 2 — Valuación</h3></div>
        <div class="card-body">
            @if($captacion->valuation)
            <p style="font-size:.85rem; margin-bottom:.5rem;">Valuación vinculada:</p>
            <div class="detail-row">
                <span class="label">Valor estimado</span>
                <span class="value">${{ number_format($captacion->valuation->estimated_value ?? 0, 0) }}</span>
            </div>
            @else
            <p style="font-size:.82rem; color:var(--text-muted); margin-bottom:.75rem;">Vincula una opinión de valor existente.</p>
            @if($valuations->isNotEmpty())
            <form method="POST" action="{{ route('admin.captaciones.link-valuation', $captacion) }}">
                @csrf
                <select name="valuation_id" class="form-select" style="margin-bottom:.5rem;">
                    @foreach($valuations as $val)
                    <option value="{{ $val->id }}">#{{ $val->id }} — ${{ number_format($val->estimated_value ?? 0, 0) }} ({{ $val->created_at->format('d/m/Y') }})</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary btn-sm" style="width:100%;">Vincular</button>
            </form>
            @else
            <p style="font-size:.8rem; color:var(--text-muted);">No hay valuaciones disponibles para este cliente.</p>
            <a href="{{ route('admin.valuations.create', ['client_id' => $captacion->client_id]) }}" class="btn btn-sm btn-outline" style="width:100%; margin-top:.5rem; text-align:center;">
                Crear Valuación
            </a>
            @endif
            @endif
        </div>
    </div>

    {{-- Etapa 3: Precio --}}
    <div class="card" style="margin-bottom:1rem;">
        <div class="card-header"><h3>Etapa 3 — Precio</h3></div>
        <div class="card-body">
            @if($captacion->precio_acordado)
            <div class="detail-row">
                <span class="label">Precio acordado</span>
                <span class="value">${{ number_format($captacion->precio_acordado, 0) }}</span>
            </div>
            @if($captacion->etapa3_completed_at)
            <p style="font-size:.78rem; color:var(--success); margin-top:.5rem;">✓ Confirmado por el cliente</p>
            @else
            <p style="font-size:.78rem; color:var(--text-muted); margin-top:.5rem;">Pendiente de confirmación por el cliente</p>
            @endif
            @else
            <form method="POST" action="{{ route('admin.captaciones.set-price', $captacion) }}">
                @csrf
                <div class="form-group" style="margin-bottom:.5rem;">
                    <label class="form-label">Precio de venta (MXN)</label>
                    <input type="number" name="precio" class="form-input" placeholder="Ej: 3500000" step="1000" min="0">
                </div>
                <button type="submit" class="btn btn-primary btn-sm" style="width:100%;">Establecer precio</button>
            </form>
            @endif
        </div>
    </div>

    {{-- Etapa 4: Contrato de exclusiva --}}
    <div class="card">
        <div class="card-header"><h3>Etapa 4 — Exclusiva</h3></div>
        <div class="card-body">
            @if($captacion->signatureRequest)
            <div class="detail-row">
                <span class="label">Estado</span>
                <span class="value">{{ ucfirst($captacion->signatureRequest->status) }}</span>
            </div>
            @if($captacion->signatureRequest->file_id)
            <a href="https://docs.google.com/document/d/{{ $captacion->signatureRequest->file_id }}" target="_blank"
               class="btn btn-sm btn-outline" style="width:100%; text-align:center; margin-top:.5rem;">
                Ver en Drive
            </a>
            @endif
            @if($captacion->signatureRequest->status !== 'completed')
            <form method="POST" action="{{ route('admin.captaciones.confirmar-exclusiva', $captacion) }}" style="margin-top:.5rem;">
                @csrf
                <button type="submit" class="btn btn-sm btn-primary" style="width:100%;">Confirmar firma manual</button>
            </form>
            @else
            <p style="font-size:.78rem; color:var(--success); margin-top:.5rem;">✓ Contrato firmado</p>
            @endif
            @else
            <p style="font-size:.82rem; color:var(--text-muted); margin-bottom:.75rem;">Genera el contrato de exclusiva para el cliente.</p>
            <form method="POST" action="{{ route('admin.captaciones.generar-exclusiva', $captacion) }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm" style="width:100%;"
                    {{ !$captacion->etapa3_completed_at ? 'disabled title=Espera la confirmación del precio' : '' }}>
                    Generar Contrato Exclusiva
                </button>
            </form>
            @endif
        </div>
    </div>

</div>
</div>
@endsection
