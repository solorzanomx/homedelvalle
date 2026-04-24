@extends('layouts.portal')
@section('title', 'Mi Proceso de Venta')

@section('styles')
<style>
    /* ── Stage bar ── */
    .etapa-bar { display:flex; gap:0; margin-bottom:2rem; border-radius:10px; overflow:hidden; border:1px solid var(--border); }
    .etapa-step {
        flex:1; padding:.85rem 1rem; text-align:center; font-size:.78rem; font-weight:600;
        background:var(--bg); color:var(--text-muted); position:relative; cursor:default;
        border-right:1px solid var(--border);
    }
    .etapa-step:last-child { border-right:none; }
    .etapa-step.done { background:rgba(16,185,129,.08); color:var(--success); }
    .etapa-step.active { background:rgba(102,126,234,.1); color:var(--primary); }
    .etapa-step .etapa-num { display:block; font-size:1rem; font-weight:700; margin-bottom:.15rem; }
    .etapa-step.done .etapa-num::after { content:' ✓'; }

    /* ── Doc list ── */
    .doc-item {
        display:flex; align-items:center; gap:.75rem; padding:.75rem 0;
        border-bottom:1px solid var(--border); font-size:.85rem;
    }
    .doc-item:last-child { border-bottom:none; }
    .doc-status-dot {
        width:10px; height:10px; border-radius:50%; flex-shrink:0;
    }
    .dot-pending  { background:#d1d5db; }
    .dot-uploaded { background:#f59e0b; }
    .dot-approved { background:#10b981; }
    .dot-rejected { background:#ef4444; }

    .upload-area {
        border:2px dashed var(--border); border-radius:var(--radius);
        padding:1.5rem; text-align:center; cursor:pointer;
        transition:border-color .15s; margin-top:.75rem;
    }
    .upload-area:hover { border-color:var(--primary); }
    .upload-area input[type=file] { display:none; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Mi Proceso de Venta</h2>
        <p class="text-muted">Seguimiento de la captación de tu inmueble</p>
    </div>
    <a href="{{ route('portal.dashboard') }}" class="btn btn-outline">← Volver</a>
</div>

{{-- ── Barra de etapas ── --}}
<div class="etapa-bar">
    @php
        $etapa = $captacion->portal_etapa;
        $etapaLabels = [
            1 => ['Documentación', 'Sube tus documentos'],
            2 => ['Valuación', 'Revisión del inmueble'],
            3 => ['Precio', 'Acuerdo de precio'],
            4 => ['Contrato', 'Firma de exclusiva'],
        ];
    @endphp
    @foreach($etapaLabels as $n => [$title, $sub])
    <div class="etapa-step {{ $etapa > $n ? 'done' : ($etapa === $n ? 'active' : '') }}">
        <span class="etapa-num">{{ $title }}</span>
        <span style="font-weight:400; opacity:.8;">{{ $sub }}</span>
    </div>
    @endforeach
</div>

{{-- ── Etapa 1: Documentación ── --}}
<div class="card" style="margin-bottom:1.25rem;">
    <div class="card-header">
        <h3>
            @if($etapa > 1) <span style="color:var(--success);">✓ </span> @endif
            Etapa 1 — Documentación
        </h3>
        @if($etapa === 1)
        <span class="badge badge-blue">En curso</span>
        @elseif($etapa > 1)
        <span class="badge badge-green">Completada</span>
        @endif
    </div>
    <div class="card-body">
        @if($etapa === 1 || true)
        <p style="font-size:.85rem; color:var(--text-muted); margin-bottom:1.25rem;">
            Sube los documentos necesarios para iniciar el proceso. Tu asesor los revisará y aprobará cada uno.
        </p>

        {{-- Documentos requeridos --}}
        <div style="margin-bottom:1.5rem;">
            <div style="font-size:.78rem; font-weight:600; text-transform:uppercase; letter-spacing:.5px; color:var(--text-muted); margin-bottom:.75rem;">Documentos Requeridos</div>
            @foreach($requiredCats as $cat)
            @php
                $docs = $docsByCategory[$cat] ?? collect();
                $latestDoc = $docs->sortByDesc('created_at')->first();
                $status = $latestDoc?->captacion_status ?? null;
            @endphp
            <div class="doc-item">
                <span class="doc-status-dot {{ $status === 'aprobado' ? 'dot-approved' : ($status === 'rechazado' ? 'dot-rejected' : ($latestDoc ? 'dot-uploaded' : 'dot-pending')) }}"></span>
                <div style="flex:1;">
                    <div style="font-weight:500;">{{ $allCategories[$cat] ?? $cat }}</div>
                    @if($latestDoc)
                    <div style="font-size:.75rem; color:var(--text-muted); margin-top:.1rem;">
                        {{ $latestDoc->file_name }} ·
                        @if($status === 'aprobado') <span style="color:var(--success);">Aprobado</span>
                        @elseif($status === 'rechazado') <span style="color:var(--danger);">Rechazado@if($latestDoc->rejection_reason): {{ $latestDoc->rejection_reason }}@endif</span>
                        @else <span style="color:#f59e0b;">En revisión</span>
                        @endif
                    </div>
                    @endif
                </div>
                @if($status !== 'aprobado')
                <button type="button" class="btn btn-sm btn-outline" onclick="openUpload('{{ $cat }}')">
                    {{ $latestDoc ? 'Resubir' : 'Subir' }}
                </button>
                @endif
            </div>
            @endforeach
        </div>

        {{-- Documentos opcionales --}}
        <div>
            <div style="font-size:.78rem; font-weight:600; text-transform:uppercase; letter-spacing:.5px; color:var(--text-muted); margin-bottom:.75rem;">Documentos Opcionales</div>
            @foreach($optionalCats as $cat)
            @php
                $docs = $docsByCategory[$cat] ?? collect();
                $latestDoc = $docs->sortByDesc('created_at')->first();
                $status = $latestDoc?->captacion_status ?? null;
            @endphp
            <div class="doc-item">
                <span class="doc-status-dot {{ $status === 'aprobado' ? 'dot-approved' : ($status === 'rechazado' ? 'dot-rejected' : ($latestDoc ? 'dot-uploaded' : 'dot-pending')) }}"></span>
                <div style="flex:1;">
                    <div style="font-weight:500;">{{ $allCategories[$cat] ?? $cat }}</div>
                    @if($latestDoc)
                    <div style="font-size:.75rem; color:var(--text-muted); margin-top:.1rem;">
                        {{ $latestDoc->file_name }} ·
                        @if($status === 'aprobado') <span style="color:var(--success);">Aprobado</span>
                        @elseif($status === 'rechazado') <span style="color:var(--danger);">Rechazado</span>
                        @else <span style="color:#f59e0b;">En revisión</span>
                        @endif
                    </div>
                    @endif
                </div>
                @if($status !== 'aprobado')
                <button type="button" class="btn btn-sm btn-outline" onclick="openUpload('{{ $cat }}')">
                    {{ $latestDoc ? 'Resubir' : 'Subir' }}
                </button>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- ── Etapa 2: Valuación ── --}}
<div class="card" style="margin-bottom:1.25rem; {{ $etapa < 2 ? 'opacity:.55;pointer-events:none;' : '' }}">
    <div class="card-header">
        <h3>
            @if($etapa > 2) <span style="color:var(--success);">✓ </span> @endif
            Etapa 2 — Valuación del Inmueble
        </h3>
        @if($etapa === 2) <span class="badge badge-blue">En curso</span>
        @elseif($etapa > 2) <span class="badge badge-green">Completada</span>
        @else <span class="badge badge-yellow">Pendiente</span>
        @endif
    </div>
    <div class="card-body" style="font-size:.88rem; color:var(--text-muted);">
        @if($etapa < 2)
        <p>Esta etapa se habilitará cuando tus documentos estén aprobados.</p>
        @elseif($captacion->valuation)
        <div class="detail-row"><span class="label">Opinión de valor</span><span class="value" style="color:var(--text);">${{ number_format($captacion->valuation->estimated_value ?? 0, 0) }}</span></div>
        <div class="detail-row"><span class="label">Fecha</span><span class="value">{{ $captacion->valuation->created_at?->format('d/m/Y') }}</span></div>
        @else
        <p>Tu asesor está preparando la valuación de tu inmueble. Te notificaremos cuando esté lista.</p>
        @endif
    </div>
</div>

{{-- ── Etapa 3: Precio ── --}}
<div class="card" style="margin-bottom:1.25rem; {{ $etapa < 3 ? 'opacity:.55;pointer-events:none;' : '' }}">
    <div class="card-header">
        <h3>
            @if($etapa > 3) <span style="color:var(--success);">✓ </span> @endif
            Etapa 3 — Acuerdo de Precio
        </h3>
        @if($etapa === 3) <span class="badge badge-blue">En curso</span>
        @elseif($etapa > 3) <span class="badge badge-green">Completada</span>
        @else <span class="badge badge-yellow">Pendiente</span>
        @endif
    </div>
    <div class="card-body">
        @if($etapa < 3)
        <p style="font-size:.88rem; color:var(--text-muted);">Esta etapa se habilitará después de la valuación.</p>
        @elseif($captacion->precio_acordado)
        <div class="detail-row">
            <span class="label">Precio acordado</span>
            <span class="value" style="font-size:1.1rem; color:var(--text);">${{ number_format($captacion->precio_acordado, 0) }} MXN</span>
        </div>
        @if($captacion->etapa3_completed_at)
        <p style="font-size:.8rem; color:var(--success); margin-top:.5rem;">✓ Confirmado el {{ $captacion->etapa3_completed_at->format('d/m/Y') }}</p>
        @else
        <form method="POST" action="{{ route('portal.captacion.confirm-price') }}" style="margin-top:1rem;">
            @csrf
            <button type="submit" class="btn btn-primary">Acepto este precio</button>
        </form>
        @endif
        @else
        <p style="font-size:.88rem; color:var(--text-muted);">Tu asesor establecerá el precio de venta recomendado basado en la valuación.</p>
        @endif
    </div>
</div>

{{-- ── Etapa 4: Contrato de Exclusiva ── --}}
<div class="card" style="{{ $etapa < 4 ? 'opacity:.55;pointer-events:none;' : '' }}">
    <div class="card-header">
        <h3>
            @if($captacion->isEtapa4Complete()) <span style="color:var(--success);">✓ </span> @endif
            Etapa 4 — Contrato de Exclusiva
        </h3>
        @if($etapa < 4) <span class="badge badge-yellow">Pendiente</span>
        @elseif($captacion->isEtapa4Complete()) <span class="badge badge-green">Firmado</span>
        @else <span class="badge badge-blue">En proceso</span>
        @endif
    </div>
    <div class="card-body" style="font-size:.88rem; color:var(--text-muted);">
        @if($etapa < 4)
        <p>Esta etapa se habilitará cuando confirmes el precio de venta.</p>
        @elseif($captacion->isEtapa4Complete())
        <p style="color:var(--success);">¡Contrato firmado! Ya estamos listos para poner tu inmueble en el mercado.</p>
        @elseif($captacion->signatureRequest)
        <p>Tu contrato de exclusiva está pendiente de firma. Revisa tu correo electrónico o contacta a tu asesor.</p>
        <div class="detail-row" style="margin-top:.5rem;"><span class="label">Estado</span><span class="value">{{ ucfirst($captacion->signatureRequest->status) }}</span></div>
        @else
        <p>Tu asesor generará el contrato de exclusiva. Recibirás un correo cuando esté listo.</p>
        @endif
    </div>
</div>

{{-- ── Upload modal ── --}}
<div id="upload-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:9999; align-items:center; justify-content:center; padding:1rem;">
    <div style="background:#fff; border-radius:12px; max-width:480px; width:100%; padding:1.5rem; box-shadow:0 10px 40px rgba(0,0,0,.2);">
        <h3 style="margin-bottom:1rem; font-size:1rem;">Subir Documento</h3>
        <form method="POST" action="{{ route('portal.captacion.upload') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="category" id="upload-category">
            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label">Archivo (PDF, JPG o PNG, máx. 10 MB)</label>
                <input type="file" name="file" id="upload-file" class="form-input" accept=".pdf,.jpg,.jpeg,.png" required>
            </div>
            <div style="display:flex; gap:.75rem; justify-content:flex-end;">
                <button type="button" class="btn btn-outline" onclick="closeUpload()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Subir</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openUpload(category) {
    document.getElementById('upload-category').value = category;
    document.getElementById('upload-file').value = '';
    const modal = document.getElementById('upload-modal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeUpload() {
    document.getElementById('upload-modal').style.display = 'none';
    document.body.style.overflow = '';
}
document.getElementById('upload-modal').addEventListener('click', function(e) {
    if (e.target === this) closeUpload();
});
</script>
@endsection
