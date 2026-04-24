@extends('layouts.portal')
@section('title', 'Mi Portal')

@section('styles')
/* ── Tokens ─────────────────────────────────────────────────────── */
:root {
    --hdv-navy:   #0C1A2E;
    --hdv-blue:   #1D4ED8;
    --hdv-blue50: #EFF6FF;
}

/* ── Hero ────────────────────────────────────────────────────────── */
.hero-card {
    background: var(--hdv-navy);
    border-radius: 14px;
    padding: 2rem 2rem 1.75rem;
    margin-bottom: 1.5rem;
    position: relative;
    overflow: hidden;
}
.hero-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: var(--hdv-blue);
}
.hero-greeting {
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #93C5FD;
    margin-bottom: 0.4rem;
}
.hero-name {
    font-size: 1.55rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: 0.3rem;
    line-height: 1.2;
}
.hero-property { font-size: 0.85rem; color: #94A3B8; margin-bottom: 1.25rem; }
.hero-property strong { color: #CBD5E1; }
.hero-badge {
    display: inline-flex; align-items: center; gap: 0.4rem;
    font-size: 0.72rem; font-weight: 600;
    padding: 4px 12px; border-radius: 20px;
    background: rgba(29,78,216,0.25); color: #93C5FD;
    border: 1px solid rgba(29,78,216,0.4);
}
.hero-badge .dot {
    width: 6px; height: 6px; border-radius: 50%; background: #3B82F6;
    animation: pulse 2s infinite;
}
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }

/* ── Funnel stepper ──────────────────────────────────────────────── */
.funnel-wrap {
    background: var(--card); border: 1px solid var(--border);
    border-radius: 14px; padding: 1.5rem; margin-bottom: 1.5rem;
}
.funnel-title {
    font-size: 0.7rem; font-weight: 700; letter-spacing: 1px;
    text-transform: uppercase; color: var(--text-muted); margin-bottom: 1.25rem;
}
.funnel-steps {
    display: flex; align-items: flex-start; position: relative;
}
.funnel-steps::before {
    content: ''; position: absolute; top: 18px; left: 18px; right: 18px;
    height: 2px; background: var(--border); z-index: 0;
}
.funnel-progress-line {
    position: absolute; top: 18px; left: 18px;
    height: 2px; background: var(--hdv-blue); z-index: 0; transition: width .4s ease;
}
.funnel-step {
    flex: 1; display: flex; flex-direction: column;
    align-items: center; position: relative; z-index: 1;
}
.step-circle {
    width: 36px; height: 36px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.78rem; font-weight: 700;
    border: 2px solid var(--border); background: var(--card);
    color: var(--text-muted); margin-bottom: 0.5rem;
}
.step-circle.done  { background:#ECFDF5; border-color:#10B981; color:#10B981; }
.step-circle.active { background:var(--hdv-blue); border-color:var(--hdv-blue); color:#fff; box-shadow:0 0 0 4px rgba(29,78,216,.15); }
.step-label { font-size:0.7rem; font-weight:600; color:var(--text-muted); text-align:center; line-height:1.3; max-width:72px; }
.step-label.active { color:var(--hdv-blue); }
.step-label.done   { color:#10B981; }

/* ── Main grid ───────────────────────────────────────────────────── */
.portal-grid {
    display: grid; grid-template-columns: 1fr 300px; gap: 1.25rem; align-items: start;
}
@media (max-width:768px) { .portal-grid { grid-template-columns: 1fr; } }

/* ── Next action card ────────────────────────────────────────────── */
.action-card {
    background: var(--hdv-blue50); border: 1px solid #BFDBFE;
    border-left: 4px solid var(--hdv-blue);
    border-radius: 0 12px 12px 0; padding: 1.25rem 1.5rem; margin-bottom: 1.25rem;
}
.action-eyebrow {
    font-size: 0.68rem; font-weight: 700; letter-spacing: 1px;
    text-transform: uppercase; color: var(--hdv-blue); margin-bottom: 0.35rem;
}
.action-title  { font-size: 1rem; font-weight: 700; color: var(--hdv-navy); margin-bottom: 0.4rem; }
.action-desc   { font-size: 0.83rem; color: #475569; margin-bottom: 1rem; line-height: 1.5; }
.btn-hdv {
    display: inline-flex; align-items: center; gap: 0.4rem;
    background: var(--hdv-blue); color: #fff; font-size: 0.82rem; font-weight: 600;
    padding: 9px 18px; border-radius: 8px; border: none; cursor: pointer;
    text-decoration: none; transition: opacity .15s;
}
.btn-hdv:hover { opacity:.88; }

/* ── Section cards ───────────────────────────────────────────────── */
.section-card {
    background: var(--card); border: 1px solid var(--border);
    border-radius: 12px; margin-bottom: 1.25rem; overflow: hidden;
}
.section-hd {
    padding: 0.9rem 1.25rem; border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
}
.section-hd-title { font-size: 0.82rem; font-weight: 700; color: var(--text); }
.section-body { padding: 0 1.25rem; }

/* ── Activity feed ───────────────────────────────────────────────── */
.activity-item {
    display: flex; gap: 0.85rem; padding: 0.65rem 0;
    border-bottom: 1px solid var(--border); font-size: 0.82rem;
}
.activity-item:last-child { border-bottom: none; }
.activity-icon {
    width: 28px; height: 28px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.8rem; flex-shrink: 0; margin-top: 1px;
}
.activity-text { color: var(--text); line-height: 1.4; }
.activity-time { font-size: 0.72rem; color: var(--text-muted); margin-top: 2px; }

/* ── Sidebar ─────────────────────────────────────────────────────── */
.doc-mini-item {
    display: flex; align-items: center; gap: 0.6rem;
    padding: 0.55rem 0; border-bottom: 1px solid var(--border); font-size: 0.8rem;
}
.doc-mini-item:last-child { border-bottom: none; }
.mini-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.advisor-card {
    background: var(--card); border: 1px solid var(--border);
    border-radius: 12px; padding: 1.25rem; text-align: center;
}
.advisor-avatar {
    width: 52px; height: 52px; border-radius: 50%;
    background: var(--hdv-navy); color: #fff;
    font-size: 1.1rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 0.75rem;
}
.advisor-name  { font-size: 0.88rem; font-weight: 700; color: var(--text); }
.advisor-role  { font-size: 0.72rem; color: var(--text-muted); margin-bottom: 0.85rem; }
.btn-wa {
    display: inline-flex; align-items: center; gap: 0.4rem;
    background: #22C55E; color: #fff; font-size: 0.78rem; font-weight: 600;
    padding: 7px 14px; border-radius: 8px; text-decoration: none;
    width: 100%; justify-content: center;
}
.stat-card {
    background: var(--card); border: 1px solid var(--border); border-radius: 12px;
    padding: 1.25rem; display: flex; align-items: center; gap: 1rem;
}
.empty-hero { text-align:center; padding:4rem 2rem; color:var(--text-muted); font-size:.9rem; }
@endsection

@section('content')
@php
    $firstName = explode(' ', Auth::user()->name)[0];
    $etapa     = $captacion?->portal_etapa ?? 0;

    $etapaInfo = [
        1 => ['label' => 'Documentación',   'short' => 'Documentos'],
        2 => ['label' => 'Opinión de valor', 'short' => 'Valuación'],
        3 => ['label' => 'Precio de venta',  'short' => 'Precio'],
        4 => ['label' => 'Contrato firmado', 'short' => 'Exclusiva'],
    ];

    $propAddress = $captacion?->property_address ?? ($properties->first()?->address ?? null);
    $propCity    = $properties->first()?->city ?? 'Benito Juárez, CDMX';

    // ── Next action ───────────────────────────────────────────────
    $nextAction = null;
    if ($captacion) {
        $docLabels = [
            'identificacion'        => 'INE o Pasaporte',
            'curp'                  => 'CURP',
            'comprobante_domicilio' => 'Comprobante de domicilio',
        ];
        $pendingRequired = $captacion->getPendingRequiredDocs();

        if ($etapa === 1 && count($pendingRequired) > 0) {
            $count = count($pendingRequired);
            $nextAction = [
                'eyebrow' => 'Tu próximo paso',
                'title'   => $count === 1 ? 'Falta 1 documento para avanzar' : "Faltan {$count} documentos para avanzar",
                'desc'    => 'Sube tu ' . implode(', ', array_map(fn($k) => $docLabels[$k] ?? $k, $pendingRequired)) . '. Este es el único paso que depende de ti en este momento.',
                'cta'     => 'Subir documentos',
                'url'     => route('portal.captacion'),
            ];
        } elseif ($etapa === 1) {
            $nextAction = [
                'eyebrow' => 'En revisión',
                'title'   => 'Tu asesor está revisando tus documentos',
                'desc'    => 'Recibimos todo. Te avisamos en menos de 24 horas con el resultado.',
                'cta'     => 'Ver mis documentos',
                'url'     => route('portal.captacion'),
            ];
        } elseif ($etapa === 2) {
            if ($captacion->valuation) {
                $nextAction = [
                    'eyebrow' => 'Tu próximo paso',
                    'title'   => 'Tu opinión de valor está lista',
                    'desc'    => 'Analizamos tu inmueble con datos reales de mercado en Benito Juárez. Revisa el resultado.',
                    'cta'     => 'Ver mi valuación',
                    'url'     => route('portal.captacion'),
                ];
            } else {
                $nextAction = [
                    'eyebrow' => 'En proceso',
                    'title'   => 'Preparando tu opinión de valor',
                    'desc'    => 'Estamos analizando tu inmueble con nuestro sistema propio y datos reales de ' . ($propCity ?: 'Benito Juárez') . '. Listo en 1–2 días hábiles.',
                    'cta'     => 'Ver mi expediente',
                    'url'     => route('portal.captacion'),
                ];
            }
        } elseif ($etapa === 3) {
            if ($captacion->precio_acordado && !$captacion->etapa3_completed_at) {
                $nextAction = [
                    'eyebrow' => 'Tu próximo paso',
                    'title'   => 'Confirma el precio de salida al mercado',
                    'desc'    => 'Proponemos $' . number_format($captacion->precio_acordado, 0) . ' MXN como precio de venta. Cuando lo confirmes, arrancamos.',
                    'cta'     => 'Revisar y confirmar precio',
                    'url'     => route('portal.captacion'),
                ];
            } else {
                $nextAction = [
                    'eyebrow' => 'Precio confirmado',
                    'title'   => 'Generando tu contrato de exclusiva',
                    'desc'    => 'Ya acordamos el precio. Tu asesor está preparando el contrato. Lo recibirás por correo para firma digital.',
                    'cta'     => 'Ver mi proceso',
                    'url'     => route('portal.captacion'),
                ];
            }
        } elseif ($etapa === 4) {
            if (!$captacion->isEtapa4Complete()) {
                $nextAction = [
                    'eyebrow' => 'Último paso',
                    'title'   => 'Firma tu contrato de exclusiva',
                    'desc'    => 'Revisa tu correo — ahí encontrarás el enlace para firmarlo digitalmente. Una vez firmado, salimos oficialmente al mercado.',
                    'cta'     => 'Ver mi contrato',
                    'url'     => route('portal.captacion'),
                ];
            } else {
                $nextAction = [
                    'eyebrow' => '¡Exclusiva activa!',
                    'title'   => 'Tu inmueble ya está en el mercado',
                    'desc'    => 'Contrato firmado. Estamos trabajando activamente para encontrar al comprador ideal para ti.',
                    'cta'     => 'Ver actividad',
                    'url'     => route('portal.captacion'),
                ];
            }
        }
    }

    // ── Activity log ──────────────────────────────────────────────
    $activities = [];
    if ($captacion) {
        if ($captacion->etapa4_completed_at)
            $activities[] = ['icon' => '🤝', 'bg' => '#ECFDF5', 'text' => 'Contrato de exclusiva firmado — tu inmueble ya está en el mercado.', 'time' => $captacion->etapa4_completed_at->diffForHumans()];
        if ($captacion->etapa3_completed_at)
            $activities[] = ['icon' => '✓',  'bg' => '#ECFDF5', 'text' => 'Precio de venta confirmado: $' . number_format($captacion->precio_acordado, 0) . ' MXN.', 'time' => $captacion->etapa3_completed_at->diffForHumans()];
        if ($captacion->etapa2_completed_at)
            $activities[] = ['icon' => '📊', 'bg' => '#EFF6FF', 'text' => 'Opinión de valor completada y publicada en tu portal.', 'time' => $captacion->etapa2_completed_at->diffForHumans()];
        if ($captacion->etapa1_completed_at)
            $activities[] = ['icon' => '✓',  'bg' => '#ECFDF5', 'text' => 'Todos tus documentos fueron revisados y aprobados.', 'time' => $captacion->etapa1_completed_at->diffForHumans()];

        $lastDoc = $captacion->documents->sortByDesc('created_at')->first();
        if ($lastDoc)
            $activities[] = ['icon' => '📎', 'bg' => '#F5F3FF', 'text' => 'Documento recibido: ' . ($lastDoc->file_name ?? 'archivo'), 'time' => $lastDoc->created_at->diffForHumans()];

        $activities[] = ['icon' => '🏠', 'bg' => '#FFF7ED', 'text' => 'Tu expediente fue abierto. Bienvenido a Home del Valle.', 'time' => $captacion->created_at->diffForHumans()];
    }

    // ── Docs sidebar ──────────────────────────────────────────────
    $sidebarDocs = [];
    if ($captacion) {
        $docCatLabels    = ['identificacion' => 'INE / Pasaporte', 'curp' => 'CURP', 'comprobante_domicilio' => 'Comprobante de domicilio'];
        $docsByCategory  = $captacion->documents->groupBy('category');
        foreach ($docCatLabels as $key => $label) {
            $latest = ($docsByCategory[$key] ?? collect())->sortByDesc('created_at')->first();
            $sidebarDocs[] = ['label' => $label, 'status' => $latest?->captacion_status ?? 'none'];
        }
    }

    // Progress line widths
    $lineWidths = [0 => '0%', 1 => '0%', 2 => '33%', 3 => '66%', 4 => '100%'];
    $lineWidth  = $lineWidths[min($etapa, 4)] ?? '0%';
@endphp

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- ESTADO: Sin cliente vinculado --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
@if(!$client)
<div class="empty-hero">
    <div style="font-size:2.5rem; margin-bottom:1rem;">🏠</div>
    <p style="font-size:1rem; font-weight:600; color:var(--text); margin-bottom:0.5rem;">Tu cuenta está siendo configurada</p>
    <p>Tu asesor está preparando tu expediente. En cuanto esté listo verás toda la información aquí.<br>Esto toma menos de 24 horas.</p>
</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- ESTADO: Cliente de venta con captación activa --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
@elseif($isVenta && $captacion)

{{-- Hero --}}
<div class="hero-card">
    <div class="hero-greeting">Hola, {{ $firstName }}</div>
    <div class="hero-name">Tu inmueble está<br>en buenas manos.</div>
    @if($propAddress)
    <div class="hero-property">
        <strong>{{ $propAddress }}</strong>@if($propCity) &nbsp;·&nbsp; {{ $propCity }}@endif
    </div>
    @endif
    <span class="hero-badge">
        <span class="dot"></span>
        Etapa {{ $etapa }} &nbsp;·&nbsp; {{ $etapaInfo[$etapa]['label'] ?? 'En proceso' }}
    </span>
</div>

{{-- Funnel stepper --}}
<div class="funnel-wrap">
    <div class="funnel-title">Tu proceso de venta</div>
    <div class="funnel-steps">
        <div class="funnel-progress-line" style="width:{{ $lineWidth }};"></div>
        @foreach($etapaInfo as $n => $info)
        @php $sc = $etapa > $n ? 'done' : ($etapa === $n ? 'active' : ''); @endphp
        <div class="funnel-step">
            <div class="step-circle {{ $sc }}">{{ $etapa > $n ? '✓' : $n }}</div>
            <div class="step-label {{ $sc }}">{{ $info['short'] }}</div>
        </div>
        @endforeach
    </div>
</div>

{{-- 2-col grid --}}
<div class="portal-grid">

    {{-- Left --}}
    <div>
        @if($nextAction)
        <div class="action-card">
            <div class="action-eyebrow">{{ $nextAction['eyebrow'] }}</div>
            <div class="action-title">{{ $nextAction['title'] }}</div>
            <div class="action-desc">{{ $nextAction['desc'] }}</div>
            <a href="{{ $nextAction['url'] }}" class="btn-hdv">{{ $nextAction['cta'] }} →</a>
        </div>
        @endif

        <div class="section-card">
            <div class="section-hd">
                <span class="section-hd-title">Lo que ha pasado en tu proceso</span>
            </div>
            <div class="section-body">
                @forelse($activities as $act)
                <div class="activity-item">
                    <div class="activity-icon" style="background:{{ $act['bg'] }};">{{ $act['icon'] }}</div>
                    <div>
                        <div class="activity-text">{{ $act['text'] }}</div>
                        <div class="activity-time">{{ $act['time'] }}</div>
                    </div>
                </div>
                @empty
                <div style="padding:.75rem 0; font-size:.82rem; color:var(--text-muted);">
                    Tu actividad aparecerá aquí conforme avance tu proceso.
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Right sidebar --}}
    <div>
        {{-- Docs --}}
        @if(!empty($sidebarDocs))
        <div class="section-card" style="margin-bottom:1.25rem;">
            <div class="section-hd">
                <span class="section-hd-title">Mis documentos</span>
                <a href="{{ route('portal.captacion') }}" style="font-size:.75rem;color:var(--hdv-blue);">Ver todos →</a>
            </div>
            <div class="section-body">
                @foreach($sidebarDocs as $doc)
                @php
                    $dotColor = match($doc['status']) {
                        'aprobado'  => '#10B981',
                        'rechazado' => '#EF4444',
                        'pendiente' => '#F59E0B',
                        default     => '#D1D5DB',
                    };
                    $statusLabel = match($doc['status']) {
                        'aprobado'  => 'Aprobado',
                        'rechazado' => 'Rechazado',
                        'pendiente' => 'En revisión',
                        default     => 'Pendiente',
                    };
                @endphp
                <div class="doc-mini-item">
                    <span class="mini-dot" style="background:{{ $dotColor }};"></span>
                    <div style="flex:1;">
                        <div style="font-weight:500;color:var(--text);">{{ $doc['label'] }}</div>
                        <div style="font-size:.7rem;color:{{ $dotColor }};">{{ $statusLabel }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Advisor --}}
        <div class="advisor-card">
            <div class="advisor-avatar">A</div>
            <div class="advisor-name">Alex Maldonado</div>
            <div class="advisor-role">Tu asesor patrimonial · Home del Valle</div>
            <a href="https://wa.me/525500000000?text=Hola+Alex%2C+tengo+una+pregunta+sobre+mi+proceso"
               class="btn-wa" target="_blank" rel="noopener">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                Escribir a mi asesor
            </a>
        </div>
    </div>

</div>{{-- /portal-grid --}}

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- ESTADO: Cliente de renta --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
@elseif($isRental)
<div style="margin-bottom:1.5rem;">
    <h2 style="font-size:1.35rem;font-weight:700;">Hola, {{ $firstName }}</h2>
    <p style="color:var(--text-muted);font-size:.85rem;">Resumen de tus procesos de renta</p>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:1.5rem;">
    <div class="stat-card">
        <div style="width:44px;height:44px;border-radius:10px;background:rgba(29,78,216,.08);color:var(--hdv-blue);display:flex;align-items:center;justify-content:center;font-size:1.2rem;">🏠</div>
        <div>
            <div style="font-size:1.5rem;font-weight:700;">{{ $rentals->count() }}</div>
            <div style="font-size:.75rem;color:var(--text-muted);">Procesos de renta</div>
        </div>
    </div>
    <div class="stat-card">
        <div style="width:44px;height:44px;border-radius:10px;background:rgba(16,185,129,.08);color:#10B981;display:flex;align-items:center;justify-content:center;font-size:1.2rem;">📄</div>
        <div>
            <div style="font-size:1.5rem;font-weight:700;">{{ $documents->count() }}</div>
            <div style="font-size:.75rem;color:var(--text-muted);">Documentos</div>
        </div>
    </div>
</div>

@foreach($rentals->take(5) as $rental)
<div class="section-card" style="margin-bottom:1rem;">
    <div class="section-hd">
        <span class="section-hd-title">{{ Str::limit($rental->property->title ?? 'Sin propiedad', 40) }}</span>
        <a href="{{ route('portal.rentals.show', $rental->id) }}" class="btn-hdv" style="font-size:.75rem;padding:5px 12px;">Ver →</a>
    </div>
    <div class="section-body" style="padding:.75rem 1.25rem;font-size:.82rem;color:var(--text-muted);">
        @if($rental->owner_client_id === $client->id)
            <span style="color:var(--hdv-blue);font-weight:600;">Propietario</span>
        @else
            <span style="color:#8B5CF6;font-weight:600;">Inquilino</span>
        @endif
        &nbsp;·&nbsp; {{ $rental->stage_label ?? 'En proceso' }}
        @if($rental->monthly_rent)
            &nbsp;·&nbsp; ${{ number_format($rental->monthly_rent, 0) }}/mes
        @endif
    </div>
</div>
@endforeach

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- ESTADO: Sin tipo definido todavía --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
@else
<div class="empty-hero">
    <div style="font-size:2.5rem;margin-bottom:1rem;">⏳</div>
    <p style="font-size:1rem;font-weight:600;color:var(--text);margin-bottom:.5rem;">Tu asesor está configurando tu expediente</p>
    <p>En cuanto esté listo verás toda la información aquí. Esto toma menos de 24 horas.</p>
</div>
@endif

@endsection
