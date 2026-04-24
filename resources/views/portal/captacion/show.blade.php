@extends('layouts.portal')
@section('title', 'Mi Proceso de Venta')

@section('styles')
/* ─── Doc status dot ─── */
.doc-status-dot { width:9px; height:9px; border-radius:50%; flex-shrink:0; }
.dot-pending  { background:#d1d5db; }
.dot-uploaded { background:#f59e0b; }
.dot-approved { background:#10b981; }
.dot-rejected { background:#ef4444; }

/* ─── Stage cards ─── */
.stage-card {
    background:#fff;
    border:1px solid var(--border);
    border-radius:12px;
    margin-bottom:1rem;
    overflow:hidden;
    transition:box-shadow .2s;
}
.stage-card.active {
    border-color:#1D4ED8;
    box-shadow:0 4px 24px rgba(29,78,216,.08);
}
.stage-card.done   { border-color:#d1fae5; }
.stage-card.locked { opacity:.5; pointer-events:none; }

/* ─── Stage card header ─── */
.sc-header {
    display:flex; align-items:center; gap:.85rem;
    padding:1rem 1.25rem;
    border-bottom:1px solid var(--border);
}
.stage-card.done .sc-header { border-bottom-color:#d1fae5; }
.stage-card.active .sc-header { border-bottom-color:#dbeafe; background:#f8faff; }
.stage-card.locked .sc-header { background:var(--bg); }

.sc-icon {
    width:38px; height:38px; border-radius:10px;
    display:flex; align-items:center; justify-content:center;
    font-size:1rem; flex-shrink:0; font-weight:700;
}
.sc-icon-done   { background:#dcfce7; color:#16a34a; }
.sc-icon-active { background:#dbeafe; color:#1D4ED8; }
.sc-icon-locked { background:#f1f5f9; color:#94a3b8; }

.sc-title { flex:1; }
.sc-title-main { font-size:.9rem; font-weight:700; color:var(--text); }
.sc-title-sub  { font-size:.72rem; color:var(--text-muted); margin-top:.1rem; }

/* ─── Stage card body ─── */
.sc-body { padding:1.25rem; }

/* ─── Doc rows ─── */
.doc-row {
    display:flex; align-items:center; gap:.75rem;
    padding:.7rem 0; border-bottom:1px solid var(--border);
    font-size:.85rem;
}
.doc-row:last-child { border-bottom:none; }
.doc-info { flex:1; min-width:0; }
.doc-name { font-weight:500; }
.doc-meta { font-size:.72rem; color:var(--text-muted); margin-top:.1rem; }
.doc-rejection { font-size:.72rem; color:#ef4444; margin-top:.1rem; }

/* ─── Section label ─── */
.section-label {
    font-size:.68rem; font-weight:700; text-transform:uppercase;
    letter-spacing:.6px; color:var(--text-muted); margin-bottom:.65rem;
}

/* ─── Progress bar ─── */
.prog-track { height:6px; background:#e2e8f0; border-radius:3px; overflow:hidden; }
.prog-fill  { height:100%; background:#1D4ED8; border-radius:3px; transition:width .4s; }

/* ─── Price display ─── */
.price-hero {
    background:linear-gradient(135deg,#0C1A2E,#1e3a5f);
    border-radius:10px; padding:1.25rem 1.5rem;
    display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap;
}
.price-hero-val {
    font-size:1.65rem; font-weight:800; color:#fff;
    letter-spacing:-1px; line-height:1;
}
.price-hero-label { font-size:.68rem; color:rgba(255,255,255,.5); text-transform:uppercase; letter-spacing:.5px; margin-bottom:.3rem; }

/* ─── Confirm price box ─── */
.confirm-box {
    background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px;
    padding:1rem 1.25rem; display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap;
}
.confirm-box p { font-size:.85rem; color:#1e40af; margin:0; }

/* ─── Done summary ─── */
.done-summary {
    display:flex; align-items:center; gap:1rem; padding:.1rem 0; flex-wrap:wrap;
}
.done-pill {
    display:inline-flex; align-items:center; gap:.35rem;
    padding:.3rem .7rem; background:#f0fdf4; border:1px solid #bbf7d0;
    border-radius:20px; font-size:.75rem; color:#15803d; font-weight:500;
}

/* ─── Signature status ─── */
.sig-status {
    display:flex; align-items:center; gap:.75rem;
    padding:.75rem 1rem; background:#f8fafc; border-radius:8px;
    border:1px solid var(--border); font-size:.85rem;
}
.sig-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; }
@endsection

@section('content')
@php
    $etapa      = $captacion->portal_etapa;
    $etapa4Done = $captacion->isEtapa4Complete();

    // Doc helpers
    $requiredApproved = collect($requiredCats)->filter(function($cat) use ($docsByCategory) {
        $latest = ($docsByCategory[$cat] ?? collect())->sortByDesc('created_at')->first();
        return $latest && $latest->captacion_status === 'aprobado';
    })->count();
    $requiredTotal = count($requiredCats);
    $progPct = $requiredTotal > 0 ? round($requiredApproved / $requiredTotal * 100) : 0;
@endphp

{{-- ── Page header ── --}}
<div class="page-header" style="margin-bottom:1.5rem;">
    <div>
        <h2 style="font-size:1.25rem;">Mi Proceso de Venta</h2>
        <p class="text-muted" style="font-size:.8rem; margin-top:.2rem;">
            {{ $captacion->property_address ?? 'Tu inmueble' }}
        </p>
    </div>
    @if($etapa4Done)
        <span class="badge badge-green" style="font-size:.78rem; padding:.35rem .85rem;">&#10003; Exclusiva firmada</span>
    @else
        <span class="badge badge-blue" style="font-size:.78rem; padding:.35rem .85rem;">Etapa {{ $etapa }} de 4</span>
    @endif
</div>

{{-- ════════════════════════════════════════
     ETAPA 1 — Documentación
════════════════════════════════════════ --}}
<div class="stage-card {{ $etapa === 1 ? 'active' : ($etapa > 1 ? 'done' : 'locked') }}">
    <div class="sc-header">
        <div class="sc-icon {{ $etapa > 1 ? 'sc-icon-done' : ($etapa === 1 ? 'sc-icon-active' : 'sc-icon-locked') }}">
            @if($etapa > 1) &#10003; @else 1 @endif
        </div>
        <div class="sc-title">
            <div class="sc-title-main">Documentación</div>
            <div class="sc-title-sub">Sube los documentos requeridos para iniciar</div>
        </div>
        @if($etapa === 1)
            <span class="badge badge-blue">En curso</span>
        @elseif($etapa > 1)
            <span class="badge badge-green">Completada</span>
        @endif
    </div>

    @if($etapa === 1)
    {{-- ACTIVE: show full document list --}}
    <div class="sc-body">

        {{-- Progress bar --}}
        <div style="margin-bottom:1.25rem;">
            <div style="display:flex; justify-content:space-between; margin-bottom:.4rem;">
                <span style="font-size:.75rem; color:var(--text-muted);">Documentos requeridos aprobados</span>
                <span style="font-size:.75rem; font-weight:700; color:{{ $requiredApproved === $requiredTotal ? 'var(--success)' : '#1D4ED8' }};">{{ $requiredApproved }}/{{ $requiredTotal }}</span>
            </div>
            <div class="prog-track">
                <div class="prog-fill" style="width:{{ $progPct }}%;"></div>
            </div>
        </div>

        {{-- Required docs --}}
        <div style="margin-bottom:1.25rem;">
            <div class="section-label">Documentos requeridos</div>
            @foreach($requiredCats as $cat)
                @php
                    $docs = $docsByCategory[$cat] ?? collect();
                    $latestDoc = $docs->sortByDesc('created_at')->first();
                    $docStatus = $latestDoc ? $latestDoc->captacion_status : null;
                @endphp
                <div class="doc-row">
                    <span class="doc-status-dot {{ $docStatus === 'aprobado' ? 'dot-approved' : ($docStatus === 'rechazado' ? 'dot-rejected' : ($latestDoc ? 'dot-uploaded' : 'dot-pending')) }}"></span>
                    <div class="doc-info">
                        <div class="doc-name">{{ $allCategories[$cat] ?? $cat }}</div>
                        @if($latestDoc)
                        <div class="doc-meta">
                            {{ $latestDoc->file_name }}
                            @if($docStatus === 'aprobado')
                                &middot; <span style="color:var(--success);">Aprobado</span>
                            @elseif($docStatus === 'rechazado')
                                &middot; <span style="color:var(--danger);">Rechazado</span>
                            @else
                                &middot; <span style="color:#f59e0b;">En revisión</span>
                            @endif
                        </div>
                        @if($docStatus === 'rechazado' && $latestDoc->rejection_reason)
                        <div class="doc-rejection">&#9888; {{ $latestDoc->rejection_reason }}</div>
                        @endif
                        @endif
                    </div>
                    @if($docStatus !== 'aprobado')
                    <button type="button" class="btn btn-sm btn-outline" onclick="openUpload('{{ $cat }}')">
                        {{ $latestDoc ? 'Resubir' : 'Subir' }}
                    </button>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Optional docs --}}
        <div>
            <div class="section-label">Documentos opcionales</div>
            @foreach($optionalCats as $cat)
                @php
                    $docs = $docsByCategory[$cat] ?? collect();
                    $latestDoc = $docs->sortByDesc('created_at')->first();
                    $docStatus = $latestDoc ? $latestDoc->captacion_status : null;
                @endphp
                <div class="doc-row">
                    <span class="doc-status-dot {{ $docStatus === 'aprobado' ? 'dot-approved' : ($docStatus === 'rechazado' ? 'dot-rejected' : ($latestDoc ? 'dot-uploaded' : 'dot-pending')) }}"></span>
                    <div class="doc-info">
                        <div class="doc-name">{{ $allCategories[$cat] ?? $cat }}</div>
                        @if($latestDoc)
                        <div class="doc-meta">
                            {{ $latestDoc->file_name }}
                            @if($docStatus === 'aprobado')
                                &middot; <span style="color:var(--success);">Aprobado</span>
                            @elseif($docStatus === 'rechazado')
                                &middot; <span style="color:var(--danger);">Rechazado</span>
                            @else
                                &middot; <span style="color:#f59e0b;">En revisión</span>
                            @endif
                        </div>
                        @endif
                    </div>
                    @if($docStatus !== 'aprobado')
                    <button type="button" class="btn btn-sm btn-outline" onclick="openUpload('{{ $cat }}')">
                        {{ $latestDoc ? 'Resubir' : 'Subir' }}
                    </button>
                    @endif
                </div>
            @endforeach
        </div>

        <p style="font-size:.75rem; color:var(--text-muted); margin-top:1.25rem; padding-top:1rem; border-top:1px solid var(--border);">
            &#8505; Tu asesor revisará cada documento y te notificará cuando estén aprobados.
        </p>
    </div>

    @elseif($etapa > 1)
    {{-- DONE: collapsed summary --}}
    <div class="sc-body" style="padding:.9rem 1.25rem;">
        <div class="done-summary">
            <span class="done-pill">&#10003; {{ $requiredApproved }} documentos aprobados</span>
            @php $optApproved = collect($optionalCats)->filter(fn($c) => ($docsByCategory[$c] ?? collect())->sortByDesc('created_at')->first()?->captacion_status === 'aprobado')->count(); @endphp
            @if($optApproved > 0)
            <span class="done-pill">&#10003; {{ $optApproved }} opcionales incluidos</span>
            @endif
        </div>
    </div>
    @endif
</div>


{{-- ════════════════════════════════════════
     ETAPA 2 — Valuación
════════════════════════════════════════ --}}
<div class="stage-card {{ $etapa === 2 ? 'active' : ($etapa > 2 ? 'done' : 'locked') }}">
    <div class="sc-header">
        <div class="sc-icon {{ $etapa > 2 ? 'sc-icon-done' : ($etapa === 2 ? 'sc-icon-active' : 'sc-icon-locked') }}">
            @if($etapa > 2) &#10003; @else 2 @endif
        </div>
        <div class="sc-title">
            <div class="sc-title-main">Valuación del Inmueble</div>
            <div class="sc-title-sub">Análisis de mercado y precio sugerido</div>
        </div>
        @if($etapa === 2)
            <span class="badge badge-blue">En curso</span>
        @elseif($etapa > 2)
            <span class="badge badge-green">Completada</span>
        @else
            <span class="badge badge-yellow">Pendiente</span>
        @endif
    </div>

    @if($etapa === 2)
    <div class="sc-body">
        @if($captacion->valuation)
        <div class="price-hero" style="margin-bottom:1rem;">
            <div>
                <div class="price-hero-label">Precio sugerido de lista</div>
                <div class="price-hero-val">${{ number_format($captacion->valuation->suggested_list_price ?? $captacion->valuation->total_value_mid ?? 0) }}</div>
                <div style="font-size:.72rem; color:rgba(255,255,255,.45); margin-top:.35rem;">
                    ${{ number_format($captacion->valuation->adjusted_price_m2 ?? 0) }}/m²
                    &middot; {{ $captacion->valuation->created_at?->format('d M Y') }}
                </div>
            </div>
            <a href="{{ route('portal.valuacion') }}"
               style="display:inline-flex; align-items:center; gap:.4rem; background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.2); color:#fff; font-size:.8rem; font-weight:600; padding:.6rem 1.1rem; border-radius:8px; white-space:nowrap;">
                Ver análisis completo →
            </a>
        </div>
        <p style="font-size:.78rem; color:var(--text-muted);">Revisa el análisis completo para entender cómo llegamos a este precio antes de pasar a la siguiente etapa.</p>
        @else
        <div style="display:flex; align-items:center; gap:1rem; padding:.5rem 0;">
            <div style="width:40px; height:40px; border-radius:10px; background:#eff6ff; display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0;">&#128202;</div>
            <div>
                <div style="font-weight:600; font-size:.88rem; margin-bottom:.2rem;">Valuación en preparación</div>
                <div style="font-size:.78rem; color:var(--text-muted);">Tu asesor está analizando el mercado. Te notificaremos cuando el análisis esté listo.</div>
            </div>
        </div>
        @endif
    </div>

    @elseif($etapa > 2)
    <div class="sc-body" style="padding:.9rem 1.25rem;">
        <div class="done-summary">
            @if($captacion->valuation)
            <span class="done-pill">&#10003; ${{ number_format($captacion->valuation->suggested_list_price ?? $captacion->valuation->total_value_mid ?? 0) }} MXN</span>
            <a href="{{ route('portal.valuacion') }}" style="font-size:.75rem; color:#1D4ED8; font-weight:500;">Ver análisis →</a>
            @else
            <span class="done-pill">&#10003; Valuación completada</span>
            @endif
        </div>
    </div>

    @else
    <div class="sc-body" style="padding:.9rem 1.25rem;">
        <p style="font-size:.82rem; color:var(--text-muted);">Se habilitará cuando tus documentos sean aprobados.</p>
    </div>
    @endif
</div>


{{-- ════════════════════════════════════════
     ETAPA 3 — Precio de Salida
════════════════════════════════════════ --}}
<div class="stage-card {{ $etapa === 3 ? 'active' : ($etapa > 3 ? 'done' : 'locked') }}">
    <div class="sc-header">
        <div class="sc-icon {{ $etapa > 3 ? 'sc-icon-done' : ($etapa === 3 ? 'sc-icon-active' : 'sc-icon-locked') }}">
            @if($etapa > 3) &#10003; @else 3 @endif
        </div>
        <div class="sc-title">
            <div class="sc-title-main">Precio de Salida</div>
            <div class="sc-title-sub">Acuerdo final del precio de venta</div>
        </div>
        @if($etapa === 3)
            <span class="badge badge-blue">En curso</span>
        @elseif($etapa > 3)
            <span class="badge badge-green">Confirmado</span>
        @else
            <span class="badge badge-yellow">Pendiente</span>
        @endif
    </div>

    @if($etapa === 3)
    <div class="sc-body">
        @if($captacion->precio_acordado)
            @if($captacion->etapa3_completed_at)
            {{-- Already confirmed --}}
            <div class="done-summary" style="margin-bottom:1rem;">
                <span class="done-pill">&#10003; Precio confirmado el {{ $captacion->etapa3_completed_at->format('d/m/Y') }}</span>
            </div>
            <div class="price-hero">
                <div>
                    <div class="price-hero-label">Precio acordado</div>
                    <div class="price-hero-val">${{ number_format($captacion->precio_acordado, 0) }} <span style="font-size:1rem; font-weight:400; opacity:.6;">MXN</span></div>
                </div>
            </div>
            @else
            {{-- Pending confirmation --}}
            <div class="price-hero" style="margin-bottom:1rem;">
                <div>
                    <div class="price-hero-label">Precio propuesto por tu asesor</div>
                    <div class="price-hero-val">${{ number_format($captacion->precio_acordado, 0) }} <span style="font-size:1rem; font-weight:400; opacity:.6;">MXN</span></div>
                </div>
            </div>
            <div class="confirm-box">
                <p>¿Estás de acuerdo con este precio de venta?</p>
                <form method="POST" action="{{ route('portal.captacion.confirm-price') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="background:#1D4ED8;">Acepto este precio</button>
                </form>
            </div>
            @endif
        @else
        <div style="display:flex; align-items:center; gap:1rem; padding:.5rem 0;">
            <div style="width:40px; height:40px; border-radius:10px; background:#eff6ff; display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0;">&#128176;</div>
            <div>
                <div style="font-weight:600; font-size:.88rem; margin-bottom:.2rem;">Precio en definición</div>
                <div style="font-size:.78rem; color:var(--text-muted);">Tu asesor establecerá el precio basado en la valuación y las condiciones del mercado.</div>
            </div>
        </div>
        @endif
    </div>

    @elseif($etapa > 3)
    <div class="sc-body" style="padding:.9rem 1.25rem;">
        <div class="done-summary">
            <span class="done-pill">&#10003; ${{ number_format($captacion->precio_acordado ?? 0, 0) }} MXN</span>
            @if($captacion->etapa3_completed_at)
            <span style="font-size:.72rem; color:var(--text-muted);">Confirmado {{ $captacion->etapa3_completed_at->format('d/m/Y') }}</span>
            @endif
        </div>
    </div>

    @else
    <div class="sc-body" style="padding:.9rem 1.25rem;">
        <p style="font-size:.82rem; color:var(--text-muted);">Se habilitará después de la valuación.</p>
    </div>
    @endif
</div>


{{-- ════════════════════════════════════════
     ETAPA 4 — Firma de Exclusiva
════════════════════════════════════════ --}}
<div class="stage-card {{ $etapa4Done ? 'done' : ($etapa === 4 ? 'active' : 'locked') }}">
    <div class="sc-header">
        <div class="sc-icon {{ $etapa4Done ? 'sc-icon-done' : ($etapa === 4 ? 'sc-icon-active' : 'sc-icon-locked') }}">
            @if($etapa4Done) &#10003; @else 4 @endif
        </div>
        <div class="sc-title">
            <div class="sc-title-main">Firma de Exclusiva</div>
            <div class="sc-title-sub">Contrato de representación exclusiva</div>
        </div>
        @if($etapa < 4)
            <span class="badge badge-yellow">Pendiente</span>
        @elseif($etapa4Done)
            <span class="badge badge-green">Firmado</span>
        @else
            <span class="badge badge-blue">En proceso</span>
        @endif
    </div>

    @if($etapa === 4 || $etapa4Done)
    <div class="sc-body">
        @if($etapa4Done)
        <div style="display:flex; align-items:center; gap:1rem; padding:.25rem 0;">
            <div style="width:40px; height:40px; border-radius:10px; background:#dcfce7; display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0;">&#9989;</div>
            <div>
                <div style="font-weight:600; font-size:.88rem; margin-bottom:.2rem; color:#15803d;">¡Contrato firmado!</div>
                <div style="font-size:.78rem; color:var(--text-muted);">Tu inmueble ya está en proceso de preparación para salir al mercado.</div>
            </div>
        </div>
        @elseif($captacion->signatureRequest)
        <div class="sig-status">
            <span class="sig-dot" style="background:#f59e0b;"></span>
            <div>
                <div style="font-weight:600; font-size:.85rem;">Contrato pendiente de firma</div>
                <div style="font-size:.75rem; color:var(--text-muted); margin-top:.15rem;">
                    Revisa tu correo electrónico — te enviamos el contrato para firma digital.
                    Estado: <strong>{{ ucfirst($captacion->signatureRequest->status) }}</strong>
                </div>
            </div>
        </div>
        @else
        <div style="display:flex; align-items:center; gap:1rem; padding:.5rem 0;">
            <div style="width:40px; height:40px; border-radius:10px; background:#eff6ff; display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0;">&#128221;</div>
            <div>
                <div style="font-weight:600; font-size:.88rem; margin-bottom:.2rem;">Contrato en preparación</div>
                <div style="font-size:.78rem; color:var(--text-muted);">Tu asesor generará el contrato de exclusiva. Recibirás un correo cuando esté listo para firmar.</div>
            </div>
        </div>
        @endif
    </div>

    @else
    <div class="sc-body" style="padding:.9rem 1.25rem;">
        <p style="font-size:.82rem; color:var(--text-muted);">Se habilitará cuando confirmes el precio de venta.</p>
    </div>
    @endif
</div>


{{-- ── Upload modal ── --}}
<div id="upload-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:9999; align-items:center; justify-content:center; padding:1rem;">
    <div style="background:#fff; border-radius:14px; max-width:460px; width:100%; box-shadow:0 20px 60px rgba(0,0,0,.2); overflow:hidden;">
        <div style="padding:1.1rem 1.5rem; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
            <span style="font-weight:700; font-size:.95rem;">Subir documento</span>
            <button type="button" onclick="closeUpload()" style="background:none; border:none; font-size:1.3rem; cursor:pointer; color:var(--text-muted); line-height:1;">&times;</button>
        </div>
        <form method="POST" action="{{ route('portal.captacion.upload') }}" enctype="multipart/form-data" style="padding:1.5rem;">
            @csrf
            <input type="hidden" name="category" id="upload-category">
            <div style="margin-bottom:1.25rem;">
                <label style="display:block; font-size:.78rem; font-weight:600; color:var(--text-muted); margin-bottom:.4rem;">Categoría seleccionada</label>
                <div id="upload-cat-name" style="font-size:.88rem; font-weight:600; padding:.55rem .75rem; background:var(--bg); border:1px solid var(--border); border-radius:var(--radius);"></div>
            </div>
            <div style="margin-bottom:1.25rem;">
                <label style="display:block; font-size:.78rem; font-weight:600; color:var(--text-muted); margin-bottom:.4rem;">Archivo (PDF, JPG o PNG — máx. 10 MB)</label>
                <input type="file" name="file" id="upload-file" class="form-input" accept=".pdf,.jpg,.jpeg,.png" required>
            </div>
            <div style="display:flex; gap:.75rem; justify-content:flex-end;">
                <button type="button" class="btn btn-outline" onclick="closeUpload()">Cancelar</button>
                <button type="submit" class="btn btn-primary" style="background:#1D4ED8;">Subir</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
var catNames = @json($allCategories);

function openUpload(category) {
    document.getElementById('upload-category').value = category;
    document.getElementById('upload-file').value = '';
    document.getElementById('upload-cat-name').textContent = catNames[category] || category;
    var modal = document.getElementById('upload-modal');
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
