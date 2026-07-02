@php include(resource_path('views/pdf/_brand_data.php')); @endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
@if($brandFontB64)
@font-face {
    font-family: 'Inter';
    font-style: normal;
    font-weight: 100 900;
    font-display: swap;
    src: url('data:font/woff2;base64,{{ $brandFontB64 }}') format('woff2');
}
@endif

*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
@page { size: 215.9mm 279.4mm; margin: 0; }

body {
    font-family: 'Inter', Arial, sans-serif;
    background: #fff;
    color: #1e293b;
    font-size: 13px;
    line-height: 1.6;
    width: 215.9mm;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}

/* ── Páginas ────────────────────────────────────────────────────────── */
.page {
    width: 215.9mm;
    min-height: 279.4mm;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    break-after: page;
    page-break-after: always;
    background: #fff;
}
.page:last-child { break-after: auto; page-break-after: auto; }

/* ── Header nav ─────────────────────────────────────────────────────── */
.header {
    background: #1e1b4b;
    padding: 20px 48px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
}
.header-logo img {
    height: 38px;
    max-width: 180px;
    object-fit: contain;
    object-position: left center;
}
.header-logo-text {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 2.5px;
    text-transform: uppercase;
    color: rgba(148, 163, 184, 0.7);
}
.header-right {
    text-align: right;
}
.header-title {
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: rgba(148, 163, 184, 0.6);
    margin-bottom: 3px;
}
.header-doc {
    font-size: 17px;
    font-weight: 800;
    color: #fff;
    letter-spacing: -0.3px;
}
.header-date {
    font-size: 9px;
    color: rgba(148, 163, 184, 0.5);
    margin-top: 2px;
}

/* ── Accent stripe ──────────────────────────────────────────────────── */
.accent-stripe {
    height: 4px;
    background: linear-gradient(90deg, #10b981 0%, #34d399 50%, #1e1b4b 100%);
    flex-shrink: 0;
}

/* ── Hero / photo block ─────────────────────────────────────────────── */
.hero {
    position: relative;
    flex-shrink: 0;
}
.hero-photo {
    width: 100%;
    height: 190px;
    object-fit: cover;
    display: block;
}
.hero-placeholder {
    width: 100%;
    height: 110px;
    background: #f1f5f9;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.hero-placeholder-inner {
    text-align: center;
    color: #94a3b8;
    font-size: 10px;
    letter-spacing: 1px;
    text-transform: uppercase;
}
.hero-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(15,23,42,0.92) 0%, rgba(15,23,42,0.4) 60%, transparent 100%);
    padding: 20px 48px 18px;
}
.hero-tag {
    display: inline-block;
    background: rgba(37,99,235,0.85);
    color: #fff;
    font-size: 8px;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    padding: 3px 10px;
    border-radius: 20px;
    margin-bottom: 7px;
}
.hero-address {
    font-size: 18px;
    font-weight: 800;
    color: #fff;
    line-height: 1.2;
    letter-spacing: -0.2px;
}
.hero-sub {
    font-size: 11px;
    color: rgba(203, 213, 225, 0.85);
    margin-top: 3px;
}

/* Address block (no photo) */
.address-block {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 20px 48px;
    display: flex;
    align-items: center;
    gap: 14px;
    flex-shrink: 0;
}
.address-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: #1e1b4b;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.address-text-label {
    font-size: 8.5px;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #94a3b8;
    margin-bottom: 2px;
}
.address-text-value {
    font-size: 16px;
    font-weight: 700;
    color: #1e1b4b;
    line-height: 1.2;
}
.address-text-sub {
    font-size: 11px;
    color: #64748b;
    margin-top: 1px;
}

/* ── Body content ───────────────────────────────────────────────────── */
.body-content {
    flex: 1;
    padding: 28px 48px 0;
}

/* ── Intro ──────────────────────────────────────────────────────────── */
.intro-box {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-left: 4px solid #10b981;
    border-radius: 0 10px 10px 0;
    padding: 16px 20px;
    margin-bottom: 24px;
}
.intro-box p {
    font-size: 12.5px;
    color: #1e3a8a;
    line-height: 1.75;
    margin: 0;
}
.intro-box strong {
    color: #1e40af;
}

/* ── Section headers ────────────────────────────────────────────────── */
.section-label {
    font-size: 8.5px;
    font-weight: 700;
    letter-spacing: 2.5px;
    text-transform: uppercase;
    color: #94a3b8;
    margin-bottom: 4px;
}
.section-title {
    font-size: 20px;
    font-weight: 800;
    color: #1e1b4b;
    line-height: 1.2;
    letter-spacing: -0.3px;
    margin-bottom: 6px;
}
.accent-bar {
    width: 28px;
    height: 3px;
    background: #10b981;
    border-radius: 2px;
    margin-bottom: 18px;
}

/* ── Por qué nosotros ───────────────────────────────────────────────── */
.differentiators {
    display: flex;
    gap: 10px;
    margin-bottom: 28px;
}
.diff-card {
    flex: 1;
    background: #1e1b4b;
    border-radius: 10px;
    padding: 16px 14px;
    text-align: center;
}
.diff-icon {
    font-size: 22px;
    margin-bottom: 8px;
    display: block;
}
.diff-title {
    font-size: 12px;
    font-weight: 800;
    color: #fff;
    line-height: 1.3;
    margin-bottom: 5px;
}
.diff-desc {
    font-size: 10px;
    color: rgba(148, 163, 184, 0.75);
    line-height: 1.5;
}

/* ── Servicios grid ─────────────────────────────────────────────────── */
.section-wrapper {
    margin-bottom: 26px;
}
.svc-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-top: 4px;
}
.svc-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 9px;
    padding: 13px 14px;
    display: flex;
    gap: 10px;
    align-items: flex-start;
}
.svc-icon-wrap {
    width: 32px;
    height: 32px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}
.svc-text h4 {
    font-size: 11.5px;
    font-weight: 700;
    color: #1e1b4b;
    margin-bottom: 3px;
    line-height: 1.3;
}
.svc-text p {
    font-size: 10.5px;
    color: #64748b;
    margin: 0;
    line-height: 1.5;
}

/* ── Proceso ────────────────────────────────────────────────────────── */
.proceso-list {
    list-style: none;
    margin: 4px 0 0;
    display: flex;
    flex-direction: column;
    gap: 0;
}
.proceso-step {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 11px 0;
    border-bottom: 1px solid #f1f5f9;
    position: relative;
}
.proceso-step:last-child {
    border-bottom: none;
}
.proceso-num {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #1e1b4b;
    color: #fff;
    font-size: 11px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 1px;
}
.proceso-num.blue {
    background: #10b981;
}
.proceso-body h4 {
    font-size: 12px;
    font-weight: 700;
    color: #1e1b4b;
    margin-bottom: 2px;
    line-height: 1.3;
}
.proceso-body p {
    font-size: 11px;
    color: #64748b;
    margin: 0;
    line-height: 1.5;
}

/* ── Condiciones ────────────────────────────────────────────────────── */
.condiciones-row {
    display: flex;
    gap: 10px;
    margin-top: 4px;
}
.cond-box {
    flex: 1;
    border-radius: 10px;
    padding: 16px 14px;
    text-align: center;
}
.cond-box.main {
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    border: 1px solid #bfdbfe;
}
.cond-box.secondary {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
}
.cond-label {
    font-size: 8.5px;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #64748b;
    margin-bottom: 6px;
}
.cond-value {
    font-size: 26px;
    font-weight: 800;
    color: #1e3a8a;
    line-height: 1;
    letter-spacing: -1px;
}
.cond-value.dark {
    color: #1e1b4b;
    font-size: 20px;
}
.cond-sub {
    font-size: 10px;
    color: #64748b;
    margin-top: 4px;
    line-height: 1.4;
}
.cond-check {
    display: inline-block;
    background: #dcfce7;
    border: 1px solid #86efac;
    color: #166534;
    font-size: 9px;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 20px;
    letter-spacing: 0.5px;
    margin-top: 6px;
}

/* ── No-cost disclaimer ─────────────────────────────────────────────── */
.no-cost-bar {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 8px;
    padding: 10px 16px;
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 10px;
}
.no-cost-bar p {
    font-size: 11px;
    color: #166534;
    margin: 0;
    line-height: 1.5;
}
.no-cost-bar strong {
    color: #14532d;
}

/* ── Agent card ─────────────────────────────────────────────────────── */
.agent-card {
    background: #1e1b4b;
    border-radius: 12px;
    padding: 18px 22px;
    display: flex;
    align-items: center;
    gap: 16px;
    margin-top: 6px;
}
.agent-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
    border: 2px solid rgba(255,255,255,0.2);
    color: #fff;
    font-size: 18px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    letter-spacing: -1px;
}
.agent-info {
    flex: 1;
}
.agent-info h3 {
    font-size: 14px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 4px;
}
.agent-info p {
    font-size: 10.5px;
    color: rgba(148, 163, 184, 0.75);
    margin: 2px 0;
    line-height: 1.4;
}
.agent-cta {
    text-align: right;
    flex-shrink: 0;
}
.agent-cta-label {
    font-size: 9px;
    color: rgba(148, 163, 184, 0.5);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 5px;
}
.agent-cta-action {
    background: #10b981;
    color: #fff;
    font-size: 10.5px;
    font-weight: 700;
    padding: 7px 16px;
    border-radius: 20px;
    letter-spacing: 0.3px;
    white-space: nowrap;
}

/* ── Footer ─────────────────────────────────────────────────────────── */
.page-footer {
    background: #1e1b4b;
    padding: 10px 48px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
    margin-top: auto;
}
.footer-brand {
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: rgba(148, 163, 184, 0.4);
}
.footer-brand strong {
    color: rgba(148, 163, 184, 0.7);
}
.footer-disclaimer {
    font-size: 8.5px;
    color: rgba(148, 163, 184, 0.35);
    text-align: center;
    flex: 1;
    padding: 0 16px;
}
.footer-page {
    font-size: 9px;
    color: rgba(148, 163, 184, 0.4);
    text-align: right;
}

/* ── Page 2 specifics ───────────────────────────────────────────────── */
.page2-header {
    background: #1e1b4b;
    padding: 14px 48px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
}
.page2-header-logo {
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: rgba(148, 163, 184, 0.5);
}
.page2-header-title {
    font-size: 10px;
    font-weight: 700;
    color: rgba(148, 163, 184, 0.6);
    letter-spacing: 1px;
    text-transform: uppercase;
}
.page2-header-addressline {
    font-size: 9px;
    color: rgba(148, 163, 184, 0.4);
    text-align: right;
    margin-top: 1px;
}

/* ── Two-column layout for page 2 ──────────────────────────────────── */
.two-col {
    display: flex;
    gap: 24px;
}
.col-left { flex: 1.1; }
.col-right { flex: 0.9; }

/* ── Closing CTA ────────────────────────────────────────────────────── */
.closing-cta {
    background: linear-gradient(135deg, #10b981, #1e1b4b);
    border-radius: 12px;
    padding: 18px 20px;
    text-align: center;
    margin-top: 14px;
}
.closing-cta h3 {
    font-size: 15px;
    font-weight: 800;
    color: #fff;
    margin-bottom: 5px;
    line-height: 1.3;
}
.closing-cta p {
    font-size: 11px;
    color: rgba(191, 219, 254, 0.8);
    margin: 0 0 12px;
    line-height: 1.5;
}
.closing-next-steps {
    display: flex;
    gap: 8px;
    justify-content: center;
}
.next-step-pill {
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.2);
    color: #fff;
    font-size: 10px;
    font-weight: 600;
    padding: 5px 12px;
    border-radius: 20px;
    white-space: nowrap;
}
.next-step-pill.highlight {
    background: #fff;
    color: #1e1b4b;
    border-color: #fff;
}
</style>
</head>
<body>

@php
    $initiales = collect(explode(' ', $nombreAgente ?? 'H V'))
        ->filter()->take(2)->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
    $operacion = $esRenta ? 'renta' : 'venta';
    $operacionLabel = $esRenta ? 'Renta' : 'Venta';
    $haPhoto = !empty($photoUrl);
@endphp

{{-- ═══════════════════════════════════════════════════════════
     PÁGINA 1
════════════════════════════════════════════════════════════ --}}
<div class="page">

    {{-- Header --}}
    <div class="header">
        <div class="header-logo">
            @if(!empty($logoUrl))
                <img src="{{ $logoUrl }}" alt="Home del Valle">
            @else
                <div class="header-logo-text">Home del Valle · Bienes Raíces</div>
            @endif
        </div>
        <div class="header-right">
            <div class="header-title">Documento confidencial</div>
            <div class="header-doc">PROPUESTA DE SERVICIOS</div>
            <div class="header-date">{{ $fechaDocumento }}</div>
        </div>
    </div>
    <div class="accent-stripe"></div>

    {{-- Hero: photo or address block --}}
    @if($haPhoto)
    <div class="hero">
        <img src="{{ $photoUrl }}" class="hero-photo" alt="Inmueble">
        <div class="hero-overlay">
            <div class="hero-tag">{{ $operacionLabel }} · Home del Valle</div>
            <div class="hero-address">{{ $direccionInmueble ?: 'Inmueble en ' . ($coloniaInmueble ?: 'Benito Juárez, CDMX') }}</div>
            @if($coloniaInmueble || $tipoInmueble)
            <div class="hero-sub">
                {{ $tipoInmueble }}{{ ($tipoInmueble && $coloniaInmueble) ? ' · ' : '' }}{{ $coloniaInmueble }}{{ $m2Total ? ' · ' . $m2Total . ' m²' : '' }}
            </div>
            @endif
        </div>
    </div>
    @else
    <div class="address-block">
        <div class="address-icon">📍</div>
        <div>
            <div class="address-text-label">Inmueble para {{ $operacion }}</div>
            <div class="address-text-value">{{ $direccionInmueble ?: 'Inmueble en ' . ($coloniaInmueble ?: 'Benito Juárez, CDMX') }}</div>
            @if($coloniaInmueble || $tipoInmueble)
            <div class="address-text-sub">{{ $tipoInmueble }}{{ ($tipoInmueble && $coloniaInmueble) ? ' · ' : '' }}{{ $coloniaInmueble }}{{ $m2Total ? ' · ' . $m2Total . ' m²' : '' }}</div>
            @endif
        </div>
    </div>
    @endif

    {{-- Body content --}}
    <div class="body-content">

        {{-- Intro --}}
        <div class="intro-box">
            <p>
                Estimado/a <strong>{{ $nombrePropietario }}</strong>, es un placer presentarle nuestra propuesta para la <strong>{{ $operacion }}</strong> de su inmueble
                @if(!empty($direccionInmueble)) ubicado en <strong>{{ $direccionInmueble }}</strong>@endif.
                En Home del Valle trabajamos con una cartera selecta de propiedades para garantizar la dedicación y el resultado que su inmueble merece.
            </p>
        </div>

        {{-- Por qué nosotros --}}
        <div class="section-wrapper">
            <div class="section-label">Nuestra diferencia</div>
            <div class="section-title">¿Por qué Home del Valle?</div>
            <div class="accent-bar"></div>

            <div class="differentiators">
                <div class="diff-card">
                    <span class="diff-icon">🎯</span>
                    <div class="diff-title">Pocos inmuebles.<br>Más control.</div>
                    <div class="diff-desc">No somos una franquicia masiva. Elegimos trabajar con propiedades seleccionadas para dar atención real a cada una.</div>
                </div>
                <div class="diff-card">
                    <span class="diff-icon">👤</span>
                    <div class="diff-title">Asesor<br>dedicado.</div>
                    <div class="diff-desc">Tiene un solo interlocutor que conoce su inmueble de principio a fin y responde en el momento que lo necesita.</div>
                </div>
                <div class="diff-card">
                    <span class="diff-icon">📊</span>
                    <div class="diff-title">Transparencia<br>total.</div>
                    <div class="diff-desc">Reportes de actividad, visitas y retroalimentación de mercado. Usted sabe en todo momento qué está pasando.</div>
                </div>
            </div>
        </div>

        {{-- Nuestros servicios --}}
        <div class="section-wrapper">
            <div class="section-label">Lo que incluye</div>
            <div class="section-title">Nuestros Servicios</div>
            <div class="accent-bar"></div>

            <div class="svc-grid">
                @foreach($servicios as $svc)
                <div class="svc-card">
                    <div class="svc-icon-wrap">{{ $svc['icono'] }}</div>
                    <div class="svc-text">
                        <h4>{{ $svc['titulo'] }}</h4>
                        <p>{{ $svc['desc'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>{{-- /body-content --}}

    {{-- Footer page 1 --}}
    <div class="page-footer">
        <div class="footer-brand"><strong>Home del Valle</strong> · Bienes Raíces</div>
        <div class="footer-disclaimer">Documento confidencial · Uso exclusivo del destinatario</div>
        <div class="footer-page">1 / 2</div>
    </div>

</div>{{-- /page 1 --}}


{{-- ═══════════════════════════════════════════════════════════
     PÁGINA 2
════════════════════════════════════════════════════════════ --}}
<div class="page">

    {{-- Page 2 mini header --}}
    <div class="page2-header">
        <div class="page2-header-logo">Home del Valle · Bienes Raíces</div>
        <div style="text-align:right;">
            <div class="page2-header-title">Propuesta de Servicios</div>
            @if(!empty($direccionInmueble))
            <div class="page2-header-addressline">{{ $direccionInmueble }}</div>
            @endif
        </div>
    </div>
    <div class="accent-stripe"></div>

    <div class="body-content" style="padding-top:22px;">

        <div class="two-col">

            {{-- Left column: El proceso --}}
            <div class="col-left">
                <div class="section-wrapper">
                    <div class="section-label">Cómo trabajamos</div>
                    <div class="section-title">El Proceso</div>
                    <div class="accent-bar"></div>

                    <ul class="proceso-list">
                        @foreach($proceso as $i => $paso)
                        <li class="proceso-step">
                            <div class="proceso-num {{ $i === 0 ? 'blue' : '' }}">{{ $paso['num'] }}</div>
                            <div class="proceso-body">
                                <h4>{{ $paso['titulo'] }}</h4>
                                <p>{{ $paso['desc'] }}</p>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- Right column: Condiciones + Agent --}}
            <div class="col-right">

                <div class="section-wrapper">
                    <div class="section-label">Términos del acuerdo</div>
                    <div class="section-title">Condiciones</div>
                    <div class="accent-bar"></div>

                    <div class="condiciones-row">
                        <div class="cond-box main">
                            <div class="cond-label">Comisión</div>
                            <div class="cond-value">{{ $comisionLabel }}</div>
                            <div class="cond-sub">Sobre el precio de<br>{{ $esRenta ? 'primera mensualidad' : 'la operación' }}</div>
                        </div>
                        <div class="cond-box secondary">
                            <div class="cond-label">Vigencia Exclusiva</div>
                            <div class="cond-value dark">{{ $vigenciaExclusiva }}</div>
                            <div class="cond-sub">Renovable de mutuo acuerdo</div>
                            <div class="cond-check">✓ Renovable</div>
                        </div>
                    </div>

                    <div class="no-cost-bar" style="margin-top:8px;">
                        <span style="font-size:18px;">✅</span>
                        <p><strong>Sin costos ocultos ni anticipos.</strong> La comisión se liquida únicamente al concretar la operación. Fotografía, publicaciones y marketing incluidos sin cargo adicional.</p>
                    </div>
                </div>

                {{-- Agent card --}}
                <div class="section-wrapper" style="margin-top:18px;">
                    <div class="section-label">Su asesor</div>
                    <div class="section-title" style="font-size:16px;">{{ $nombreAgente }}</div>
                    <div class="accent-bar"></div>

                    <div class="agent-card">
                        <div class="agent-avatar">{{ $initiales }}</div>
                        <div class="agent-info">
                            <h3>{{ $nombreAgente }}</h3>
                            @if(!empty($telefonoAgente))
                            <p>📱 {{ $telefonoAgente }}</p>
                            @endif
                            @if(!empty($emailAgente))
                            <p>✉️ {{ $emailAgente }}</p>
                            @endif
                            <p style="margin-top:4px;color:rgba(148,163,184,0.55);font-size:9.5px;">Home del Valle · Bienes Raíces · Benito Juárez, CDMX</p>
                        </div>
                        <div class="agent-cta">
                            <div class="agent-cta-label">Siguiente paso</div>
                            <div class="agent-cta-action">Firmar Exclusiva</div>
                        </div>
                    </div>
                </div>

            </div>{{-- /col-right --}}

        </div>{{-- /two-col --}}

        {{-- Closing CTA --}}
        <div class="closing-cta">
            <h3>¿Listo para comenzar la comercialización?</h3>
            <p>El siguiente paso es formalizar el acuerdo de exclusiva para activar de inmediato la campaña de marketing de su inmueble.</p>
            <div class="closing-next-steps">
                <div class="next-step-pill">1. Revisar esta propuesta</div>
                <div class="next-step-pill highlight">2. Firmar contrato de exclusiva</div>
                <div class="next-step-pill">3. Sesión fotográfica en 48 hrs</div>
                <div class="next-step-pill">4. Publicación activa</div>
            </div>
        </div>

    </div>{{-- /body-content --}}

    {{-- Footer page 2 --}}
    <div class="page-footer" style="margin-top:auto;">
        <div class="footer-brand"><strong>Home del Valle</strong> · Bienes Raíces</div>
        <div class="footer-disclaimer">Propuesta válida por 30 días · {{ $fechaDocumento }}</div>
        <div class="footer-page">2 / 2</div>
    </div>

</div>{{-- /page 2 --}}

</body>
</html>
