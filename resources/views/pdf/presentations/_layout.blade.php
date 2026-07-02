@php include(resource_path('views/pdf/_brand_data.php')); @endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
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

*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
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

/* ── Estructura de página ─────────────────────────────────────────────────── */
.page {
    width: 215.9mm;
    height: 279.4mm;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    break-after: page;
    page-break-after: always;
}
.page:last-child { break-after: auto; page-break-after: auto; }
.page-header-inner {
    flex-shrink: 0; background: #1e1b4b; border-bottom: 4px solid #10b981;
    padding: 10px 52px; display: flex; align-items: center; justify-content: space-between;
}
.page-header-inner img { height: 18px; max-width: 140px; object-fit: contain; display: block; }
.page-header-inner span.phi-text { font-size: 12px; font-weight: 700; color: #fff; }
.page-header-inner .phi-tag { font-size: 8.5px; letter-spacing: 1px; text-transform: uppercase; color: rgba(199,210,254,.7); }
.page-body  { flex: 1; overflow: hidden; display: flex; flex-direction: column; }
.inner      { flex: 1; padding: 40px 52px 16px; display: flex; flex-direction: column; overflow: hidden; }
.page-foot  {
    flex-shrink: 0;
    border-top: 1px solid #e2e8f0;
    padding: 8px 52px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 9.5px;
    color: #94a3b8;
}
.page-foot strong { color: #1e1b4b; font-weight: 600; }

/* ── Portada ─────────────────────────────────────────────────────────────── */
.cover-body { flex: 1; background: #1e1b4b; padding: 36px 52px 24px; display: flex; flex-direction: column; }
.cover-foot { flex-shrink: 0; background: #17154a; padding: 12px 52px; display: flex; justify-content: space-between; align-items: center; font-size: 10px; color: rgba(199,210,254,.55); border-top: 1px solid rgba(255,255,255,.08); }
.cover-foot strong { color: rgba(199,210,254,.85); }
.cover-logo { height: 44px; max-width: 200px; object-fit: contain; object-position: left; margin-bottom: 24px; }
.cover-logo-text { font-size: 12px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: rgba(199,210,254,.5); margin-bottom: 24px; }
.cover-photo { width: 100%; height: 266px; object-fit: cover; border-radius: 10px; margin-bottom: 22px; }
.cover-photo-ph {
    width: 100%; height: 220px; border-radius: 10px; margin-bottom: 22px;
    background: rgba(255,255,255,.04); border: 1px dashed rgba(199,210,254,.15);
    display: flex; align-items: center; justify-content: center;
    color: rgba(199,210,254,.2); font-size: 11px; letter-spacing: 1px;
}
.cover-tag { display: inline-block; background: rgba(16,185,129,.15); border: 1px solid rgba(16,185,129,.3); color: #6ee7b7; font-size: 9px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; padding: 3px 11px; border-radius: 20px; margin-bottom: 14px; }
.cover-title { font-size: 30px; font-weight: 800; color: #fff; line-height: 1.15; letter-spacing: -.4px; margin-bottom: 10px; }
.cover-sub   { font-size: 14px; font-weight: 300; color: rgba(199,210,254,.8); }
.cover-spacer { flex: 1; min-height: 10px; }
.cover-prop-box { border-top: 1px solid rgba(255,255,255,.1); padding-top: 18px; margin-top: 12px; display: flex; justify-content: space-between; align-items: flex-end; }
.cover-prop-left .label { font-size: 8px; letter-spacing: 2px; text-transform: uppercase; color: rgba(199,210,254,.35); margin-bottom: 4px; }
.cover-prop-left .value { font-size: 16px; font-weight: 600; color: #fff; }
.cover-prop-right { text-align: right; }
.cover-prop-right .intent-badge { display: inline-block; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.15); color: rgba(199,210,254,.7); font-size: 9px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; padding: 4px 10px; border-radius: 6px; }

/* ── Tipografía general ──────────────────────────────────────────────────── */
.section-tag { font-size: 8.5px; font-weight: 700; letter-spacing: 2.5px; text-transform: uppercase; color: #94a3b8; margin-bottom: 5px; }
.section-h2  { font-size: 22px; font-weight: 800; color: #1e1b4b; line-height: 1.2; letter-spacing: -.3px; margin-bottom: 7px; }
.accent-bar  { width: 28px; height: 3px; background: #10b981; border-radius: 2px; margin-bottom: 20px; }
p { color: #475569; font-size: 12.5px; line-height: 1.75; margin-bottom: 11px; }
strong { color: #1e293b; }

/* ── Stats boxes ─────────────────────────────────────────────────────────── */
.stats-row { display: flex; gap: 8px; margin: 16px 0; }
.stat-box { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 10px; text-align: center; }
.stat-num  { font-size: 24px; font-weight: 800; color: #1e1b4b; line-height: 1; }
.stat-num .accent { color: #10b981; }
.stat-lbl  { font-size: 9.5px; color: #64748b; margin-top: 3px; line-height: 1.3; }

/* ── Tabla comparativa ───────────────────────────────────────────────────── */
.cmp-table { width: 100%; border-collapse: collapse; font-size: 11.5px; margin-bottom: 0; }
.cmp-table th { padding: 7px 12px; text-align: left; font-size: 8.5px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; }
.cmp-table th:first-child { color: #94a3b8; background: #f8fafc; border-radius: 6px 0 0 0; }
.cmp-table th:last-child  { color: #fff; background: #1e1b4b; border-radius: 0 6px 0 0; }
.cmp-table td { padding: 7px 12px; border-bottom: 1px solid #f1f5f9; color: #475569; }
.cmp-table td:last-child  { color: #1e1b4b; font-weight: 600; }
.cmp-table tr:last-child td { border-bottom: none; }

/* ── Gráfica de barras (mercado) ─────────────────────────────────────────── */
.chart-title { font-size: 10px; font-weight: 700; color: #1e1b4b; letter-spacing: .5px; text-transform: uppercase; margin-bottom: 10px; }
.bar-chart   { margin: 0; }
.bar-row     { display: flex; align-items: center; gap: 8px; margin-bottom: 7px; }
.bar-lbl     { width: 100px; font-size: 11px; color: #64748b; text-align: right; flex-shrink: 0; }
.bar-lbl.hl  { color: #1e1b4b; font-weight: 700; }
.bar-track   { flex: 1; height: 22px; background: #f1f5f9; border-radius: 4px; overflow: hidden; }
.bar-fill    { height: 100%; display: flex; align-items: center; padding: 0 8px; font-size: 10px; font-weight: 600; white-space: nowrap; border-radius: 4px; }
.bar-fill.hdv { background: #1e1b4b; color: #fff; }
.bar-fill.avg { background: #94a3b8; color: #fff; }
.bar-fill.hi  { background: #10b981; color: #fff; }
.chart-note   { font-size: 9.5px; color: #94a3b8; margin-top: 6px; font-style: italic; }

/* ── Bullet list ─────────────────────────────────────────────────────────── */
.blist, .bullet-list { list-style: none; margin: 10px 0 16px; }
.blist li, .bullet-list li { padding: 8px 0 8px 22px; position: relative; border-bottom: 1px solid #f1f5f9; color: #475569; font-size: 12.5px; line-height: 1.5; }
.blist li:last-child, .bullet-list li:last-child { border-bottom: none; }
.blist li::before, .bullet-list li::before { content: ''; position: absolute; left: 0; top: 15px; width: 6px; height: 6px; border-radius: 50%; background: #10b981; }

/* ── Proceso timeline ────────────────────────────────────────────────────── */
.tl { display: flex; align-items: flex-start; margin: 20px 0 16px; position: relative; }
.tl::before { content: ''; position: absolute; top: 20px; left: 20px; right: 20px; height: 2px; background: #e2e8f0; z-index: 0; }
.tl-step { flex: 1; display: flex; flex-direction: column; align-items: center; position: relative; z-index: 1; }
.tl-circle { width: 40px; height: 40px; border-radius: 50%; background: #1e1b4b; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 800; margin-bottom: 8px; box-shadow: 0 0 0 4px #fff; }
.tl-circle.active { background: #10b981; }
.tl-name  { font-size: 10.5px; font-weight: 700; color: #1e1b4b; text-align: center; line-height: 1.3; }
.tl-days  { font-size: 9.5px; color: #94a3b8; text-align: center; margin-top: 2px; }

/* ── Marketing box ───────────────────────────────────────────────────────── */
.mkt-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin: 14px 0; }
.mkt-item { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; }
.mkt-item h4 { font-size: 11.5px; font-weight: 700; color: #1e1b4b; margin-bottom: 3px; }
.mkt-item p  { font-size: 11px; color: #64748b; margin: 0; line-height: 1.5; }
.mkt-box { background: #f8fafc; border-left: 3px solid #10b981; border-radius: 0 8px 8px 0; padding: 14px 18px; margin: 12px 0; }
.mkt-box p { white-space: pre-line; font-size: 12px; color: #334155; line-height: 1.8; margin: 0; }

/* ── Servicios ───────────────────────────────────────────────────────────── */
.svc-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 9px; margin-top: 14px; }
.svc-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 13px 14px; }
.svc-icon { font-size: 18px; margin-bottom: 6px; display: block; }
.svc-card h4 { font-size: 11.5px; font-weight: 700; color: #1e1b4b; margin-bottom: 4px; }
.svc-card p  { font-size: 10.5px; color: #64748b; margin: 0; line-height: 1.5; }
.svc-note    { font-size: 10.5px; color: #64748b; text-align: center; margin-top: 14px; padding: 10px; background: #f0fdf4; border-radius: 8px; border: 1px solid #bbf7d0; }

/* ── Comisión hero ───────────────────────────────────────────────────────── */
.com-hero { background: linear-gradient(135deg, #f0fdf4, #dcfce7); border: 1px solid #bbf7d0; border-radius: 12px; padding: 20px; margin: 16px 0; text-align: center; }
.com-pct  { font-size: 52px; font-weight: 800; color: #065f46; line-height: 1; letter-spacing: -2px; white-space: nowrap; }
.com-desc { font-size: 11px; color: #064e3b; margin-top: 5px; }

/* ── Steps ───────────────────────────────────────────────────────────────── */
.steps { list-style: none; margin: 12px 0; }
.steps li { padding: 9px 0 9px 38px; position: relative; border-bottom: 1px solid #f1f5f9; color: #475569; font-size: 12.5px; }
.steps li:last-child { border-bottom: none; }
.step-n { position: absolute; left: 0; top: 7px; width: 24px; height: 24px; border-radius: 50%; background: #1e1b4b; color: #fff; font-size: 10.5px; font-weight: 700; display: flex; align-items: center; justify-content: center; }

/* ── Agente card ─────────────────────────────────────────────────────────── */
.agent-card { background: #1e1b4b; border-radius: 10px; padding: 16px 20px; margin-top: 16px; display: flex; align-items: center; gap: 16px; }
.agent-av { width: 44px; height: 44px; border-radius: 50%; flex-shrink: 0; background: rgba(255,255,255,.12); border: 2px solid rgba(255,255,255,.2); color: #fff; font-size: 16px; font-weight: 700; display: flex; align-items: center; justify-content: center; }
.agent-info h3 { font-size: 14px; font-weight: 700; color: #fff; margin-bottom: 3px; }
.agent-info p  { font-size: 11px; color: rgba(199,210,254,.75); margin: 1px 0; line-height: 1.4; }

/* ── CTA ─────────────────────────────────────────────────────────────────── */
.cta-box { background: #1e1b4b; border-radius: 10px; padding: 14px 20px; margin-top: 14px; display: flex; align-items: center; justify-content: space-between; }
.cta-text h4 { font-size: 13px; font-weight: 700; color: #fff; margin-bottom: 2px; }
.cta-text p  { font-size: 11px; color: rgba(199,210,254,.7); margin: 0; }
.cta-badge   { background: #10b981; color: #fff; font-size: 10px; font-weight: 700; padding: 6px 14px; border-radius: 20px; white-space: nowrap; letter-spacing: .5px; }

/* ── Precio ──────────────────────────────────────────────────────────────── */
.price-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 14px 18px; margin: 14px 0; display: flex; align-items: center; gap: 12px; }
.price-val   { font-size: 22px; font-weight: 800; color: #1e3a8a; }
.price-label { font-size: 10px; color: #1e40af; margin-bottom: 2px; }

/* ── Disclaimer ──────────────────────────────────────────────────────────── */
.disclaimer { font-size: 9.5px; color: #94a3b8; line-height: 1.55; border-top: 1px solid #e2e8f0; padding-top: 10px; margin-top: 14px; }

/* ── Market insight box ──────────────────────────────────────────────────── */
.insight-box { background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 10px 14px; margin-top: 12px; }
.insight-box p { font-size: 11.5px; color: #78350f; margin: 0; line-height: 1.5; }
.insight-box strong { color: #92400e; }

.testimonial { background: #f8fafc; border-left: 3px solid #1e1b4b; border-radius: 0 8px 8px 0; padding: 14px 18px; margin-top: 14px; }
.quote       { font-size: 12.5px; color: #334155; line-height: 1.7; font-style: italic; margin-bottom: 8px; }
.quote-author  { font-size: 10.5px; color: #64748b; font-weight: 600; }

/* ── Observatorio HDV badge ──────────────────────────────────────────────── */
.obs-badge { display: flex; align-items: center; gap: 12px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 10px 16px; margin: 12px 0; }
.obs-label { font-size: 8.5px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #166534; margin-bottom: 2px; }
.obs-price { font-size: 20px; font-weight: 800; color: #065f46; line-height: 1; }
.obs-conf  { font-size: 8px; background: #dcfce7; border: 1px solid #86efac; border-radius: 10px; padding: 3px 9px; color: #166534; font-weight: 600; white-space: nowrap; }
.obs-conf.medium { background: #fef9c3; border-color: #fde047; color: #713f12; }
.obs-conf.low    { background: #f1f5f9; border-color: #cbd5e1; color: #64748b; }

/* ── KPI dark row ────────────────────────────────────────────────────────── */
.dark-kpi-row { display: flex; gap: 8px; margin: 12px 0; }
.dark-kpi     { flex: 1; background: #1e1b4b; border-radius: 10px; padding: 12px 8px; text-align: center; }
.dark-kpi-num { font-size: 22px; font-weight: 800; color: #fff; line-height: 1; }
.dark-kpi-num .green { color: #10b981; }
.dark-kpi-lbl { font-size: 8.5px; color: rgba(199,210,254,.55); margin-top: 3px; line-height: 1.3; text-transform: uppercase; letter-spacing: .5px; }

/* ── Mini cards (3 col) ──────────────────────────────────────────────────── */
.mini-grid-3  { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; margin: 12px 0; }
.mini-card    { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px; }
.mini-card-icon { font-size: 17px; margin-bottom: 4px; display: block; }
.mini-card h4 { font-size: 10.5px; font-weight: 700; color: #1e1b4b; margin-bottom: 3px; }
.mini-card p  { font-size: 9.5px; color: #64748b; margin: 0; line-height: 1.5; }

/* ── Profile chips (buyer/tenant types) ─────────────────────────────────── */
.profile-row  { display: flex; gap: 6px; margin: 12px 0; }
.profile-chip { flex: 1; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 9px 11px; }
.profile-chip h4 { font-size: 10.5px; font-weight: 700; color: #1e1b4b; margin-bottom: 2px; }
.profile-chip p  { font-size: 9.5px; color: #64748b; margin: 0; line-height: 1.45; }

/* ── Income simulator table ──────────────────────────────────────────────── */
.sim-row { display: flex; gap: 8px; margin: 12px 0; }
.sim-box { flex: 1; text-align: center; padding: 10px 8px; background: #fff; border-radius: 8px; border: 1px solid #bbf7d0; }
.sim-val  { font-size: 20px; font-weight: 800; color: #065f46; line-height: 1; }
.sim-lbl  { font-size: 8.5px; color: #64748b; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 3px; }
.sim-desc { font-size: 9px; color: #94a3b8; margin-top: 2px; }
</style>
</head>
<body>

{{-- ═════════════════════════════════════════════
     PÁGINA 1 — PORTADA
════════════════════════════════════════════════ --}}
<div class="page">
  <div class="cover-body">

    @if(!empty($logoDarkUrl))
      <img src="{{ $logoDarkUrl }}" class="cover-logo" alt="Home del Valle">
    @elseif(!empty($logoUrl))
      <img src="{{ $logoUrl }}" class="cover-logo" alt="Home del Valle">
    @else
      <div class="cover-logo-text">Home del Valle · Bienes Raíces</div>
    @endif

    @if(!empty($photoUrl))
      <div style="position:relative;margin-bottom:22px;">
        <img src="{{ $photoUrl }}" class="cover-photo" alt="Inmueble" style="margin-bottom:0;">
        @if(!empty($photoIsStreetView))
        <div style="position:absolute;bottom:8px;right:8px;background:rgba(0,0,0,.55);border-radius:4px;padding:2px 7px;font-size:7px;color:rgba(255,255,255,.8);letter-spacing:.5px;">
          📍 Vista de calle · reemplaza con foto real
        </div>
        @endif
      </div>
    @else
      <div class="cover-photo-ph">Fotografía del inmueble</div>
    @endif

    <div class="cover-tag">Presentación Inicial · Uso Confidencial</div>
    <h1 class="cover-title">Propuesta de<br>comercialización</h1>
    <p class="cover-sub">Preparada para <strong style="color:#fff;">{{ $nombrePropietario }}</strong></p>

    {{-- Slogan box que llena el espacio central --}}
    <div style="margin-top:22px;padding:18px 22px;background:rgba(255,255,255,.05);border-radius:10px;border:1px solid rgba(255,255,255,.1);">
      <div style="font-size:8.5px;letter-spacing:2px;text-transform:uppercase;color:rgba(199,210,254,.35);margin-bottom:8px;">Nuestra filosofía</div>
      <div style="font-size:18px;font-weight:800;color:#fff;line-height:1.35;letter-spacing:-.2px;">Pocos inmuebles.<br>Más control.<br>Mejores resultados.</div>
    </div>

    <div class="cover-spacer"></div>

    <div class="cover-prop-box">
      <div class="cover-prop-left">
        <div class="label">Inmueble</div>
        <div class="value">{{ $inmuebleTipo }}{{ $inmuebleColonia ? ' · ' . $inmuebleColonia : '' }}</div>
      </div>
      <div class="cover-prop-right">
        <span class="intent-badge">{{ $captacion->intent_label }}</span>
      </div>
    </div>

  </div>
  <div class="cover-foot">
    <span>{{ $fechaPresentacion }}</span>
    <span>Agente: <strong>{{ $nombreAgente }}</strong></span>
  </div>
</div>

{{-- ═════════════════════════════════════════════
     PÁGINA 2 — EL MERCADO EN TU ZONA
     Datos reales de Benito Juárez / Del Valle
════════════════════════════════════════════════ --}}
<div class="page">
  <div class="page-header-inner">
    @if(!empty($brandLogoSrcLight))
      <img src="{{ $brandLogoSrcLight }}" alt="Home del Valle">
    @elseif(!empty($brandLogoSrc))
      <img src="{{ $brandLogoSrc }}" alt="Home del Valle">
    @else
      <span class="phi-text">Home del Valle</span>
    @endif
    <span class="phi-tag">Presentación · Uso Confidencial</span>
  </div>
  <div class="page-body">
    <div class="inner">
      <div class="section-tag">Contexto de mercado</div>
      <h2 class="section-h2">El mercado en tu zona</h2>
      <div class="accent-bar"></div>

      @php
        // Variables de intent — disponibles en todas las páginas desde aquí
        $intentSlug       = $captacion->intent ?? 'general';
        $isRenta          = in_array($intentSlug, ['renta_residencial', 'renta_comercial']);
        $isRentaComercial = $intentSlug === 'renta_comercial';
        $isRentaResid     = $intentSlug === 'renta_residencial';
        $isConstructor    = $intentSlug === 'venta_constructor';
        $isVentaComercial = $intentSlug === 'venta_comercial';
        $confLabel = match($mercadoConfianza ?? null) {
            'high'   => 'Alta confianza',
            'medium' => 'Confianza media',
            'low'    => 'Confianza estimada',
            default  => null,
        };
        $confClass = match($mercadoConfianza ?? null) {
            'high'   => '',
            'medium' => 'medium',
            'low'    => 'low',
            default  => '',
        };
      @endphp

      {{-- ═══ RENTA COMERCIAL ═══════════════════════════════════════ --}}
      @if($isRentaComercial)
        <p>El <strong>arrendamiento comercial en Benito Juárez</strong> opera con lógica distinta al residencial: contratos más largos, arrendatarios más solventes y rentas más estables. La demanda de locales, oficinas y bodegas de calidad sigue superando la oferta.</p>

        <div class="chart-title">Renta comercial promedio por m²/mes — Benito Juárez (2025)</div>
        <div class="bar-chart">
          <div class="bar-row">
            <div class="bar-lbl hl">Local prime BJ</div>
            <div class="bar-track"><div class="bar-fill hdv" style="width:100%;">$350 – $600 /m²/mes</div></div>
          </div>
          <div class="bar-row">
            <div class="bar-lbl">Oficina clase A</div>
            <div class="bar-track"><div class="bar-fill avg" style="width:82%;">$280 – $480 /m²/mes</div></div>
          </div>
          <div class="bar-row">
            <div class="bar-lbl">Local secundario</div>
            <div class="bar-track"><div class="bar-fill avg" style="width:65%;">$200 – $350 /m²/mes</div></div>
          </div>
          <div class="bar-row">
            <div class="bar-lbl">Oficina clase B/C</div>
            <div class="bar-track"><div class="bar-fill avg" style="width:52%;">$160 – $280 /m²/mes</div></div>
          </div>
          <div class="bar-row">
            <div class="bar-lbl">Bodega / uso mixto</div>
            <div class="bar-track"><div class="bar-fill hi" style="width:40%;">$120 – $200 /m²/mes</div></div>
          </div>
        </div>
        <p class="chart-note">* Rangos para inmuebles en buenas condiciones en Benito Juárez. Fuente: Observatorio de Precios HDV 2025.</p>

        <div class="insight-box">
          <p><strong>Estabilidad del comercial:</strong> Un contrato comercial de 5 años con ajuste INPC + spread anual garantiza ingresos predecibles y crecientes. Un arrendatario corporativo con garantía afianzadora <strong>elimina virtualmente el riesgo de impago</strong>.</p>
        </div>

        <div class="chart-title" style="margin-top:14px;margin-bottom:8px;">Ventajas del arrendamiento comercial vs. residencial</div>
        <div class="bar-chart">
          <div class="bar-row"><div class="bar-lbl">Plazo contractual</div><div class="bar-track"><div class="bar-fill hdv" style="width:90%;">Comercial 3–10 años vs residencial 1–2 años</div></div></div>
          <div class="bar-row"><div class="bar-lbl">Tipo de garantía</div><div class="bar-track"><div class="bar-fill hi" style="width:80%;">Fianza corporativa de primer nivel</div></div></div>
          <div class="bar-row"><div class="bar-lbl">Ajuste anual</div><div class="bar-track"><div class="bar-fill avg" style="width:65%;">INPC + spread pactado en contrato</div></div></div>
        </div>

      {{-- ═══ RENTA RESIDENCIAL ══════════════════════════════════════ --}}
      @elseif($isRenta)
        <p>La <strong>demanda de renta residencial en Benito Juárez</strong> supera la oferta disponible. Las rentas en Del Valle y colonias aledañas han crecido <strong>11% anual</strong> en los últimos tres años, con una tasa de vacancia histórica de apenas ~4%.</p>

        <div class="chart-title">Renta mensual por tipo de inmueble — Benito Juárez (2025)</div>
        <div class="bar-chart">
          <div class="bar-row">
            <div class="bar-lbl hl">Casa 3 rec.</div>
            <div class="bar-track"><div class="bar-fill hdv" style="width:100%;">$30,000 – $65,000 /mes</div></div>
          </div>
          <div class="bar-row">
            <div class="bar-lbl">Depto 3 rec.</div>
            <div class="bar-track"><div class="bar-fill avg" style="width:87%;">$28,000 – $55,000 /mes</div></div>
          </div>
          <div class="bar-row">
            <div class="bar-lbl">Depto 2 rec.</div>
            <div class="bar-track"><div class="bar-fill avg" style="width:72%;">$18,000 – $38,000 /mes</div></div>
          </div>
          <div class="bar-row">
            <div class="bar-lbl">Estudio / 1 rec.</div>
            <div class="bar-track"><div class="bar-fill hi" style="width:50%;">$12,000 – $22,000 /mes</div></div>
          </div>
        </div>
        <p class="chart-note">* Rangos para inmuebles en buen estado en BJ. Fuente: Observatorio de Precios HDV 2025.</p>

        <div class="insight-box">
          <p><strong>Momento ideal:</strong> Con vacancia ~4% y candidatos registrados activos, los inmuebles bien presentados con precio competitivo se colocan en <strong>15–30 días</strong>. El precio de renta tiene margen al alza si el inmueble se prepara correctamente.</p>
        </div>

        <div class="chart-title" style="margin-top:14px;margin-bottom:8px;">Tendencia del mercado de renta BJ (2023 → 2025)</div>
        <div class="bar-chart">
          <div class="bar-row"><div class="bar-lbl">Precio de renta</div><div class="bar-track"><div class="bar-fill hi" style="width:60%;">+11% anual promedio</div></div></div>
          <div class="bar-row"><div class="bar-lbl">Demanda activa</div><div class="bar-track"><div class="bar-fill hdv" style="width:80%;">+22% más candidatos registrados</div></div></div>
          <div class="bar-row"><div class="bar-lbl">Oferta disponible</div><div class="bar-track"><div class="bar-fill avg" style="width:36%;">-28% menos inmuebles en renta</div></div></div>
        </div>

      {{-- ═══ VENTA A CONSTRUCTOR ════════════════════════════════════ --}}
      @elseif($isConstructor)
        <p>Los predios en Benito Juárez con zonificación <strong>H5 y H6</strong> se han revalorizado <strong>22% en 3 años</strong>. La presión de desarrolladores sigue creciendo ante la escasez de predios disponibles con ZP favorable.</p>

        <div class="chart-title">Valor de predio por m² de terreno — Colonias clave BJ (2025)</div>
        <div class="bar-chart">
          <div class="bar-row">
            <div class="bar-lbl hl">Del Valle Sur</div>
            <div class="bar-track"><div class="bar-fill hdv" style="width:100%;">$50,000 – $80,000 /m²</div></div>
          </div>
          <div class="bar-row">
            <div class="bar-lbl">Narvarte Pte.</div>
            <div class="bar-track"><div class="bar-fill avg" style="width:86%;">$44,000 – $68,000 /m²</div></div>
          </div>
          <div class="bar-row">
            <div class="bar-lbl">Nápoles</div>
            <div class="bar-track"><div class="bar-fill avg" style="width:73%;">$36,000 – $57,000 /m²</div></div>
          </div>
          <div class="bar-row">
            <div class="bar-lbl">Insurgentes Sur</div>
            <div class="bar-track"><div class="bar-fill hi" style="width:60%;">$30,000 – $48,000 /m²</div></div>
          </div>
        </div>
        <p class="chart-note">* Valores para predios con H5/H6 sin construcción relevante. Fuente: Observatorio HDV 2025.</p>

        <div class="insight-box">
          <p><strong>Por qué ahora:</strong> Hay <strong>30+ desarrolladores activos</strong> buscando predios en BJ con poco inventario disponible. Las múltiples ofertas simultáneas que generamos elevan el precio final. La escasez de predios con ZP favorable en BJ es <em>estructural</em> — no coyuntural.</p>
        </div>

        <div class="chart-title" style="margin-top:14px;margin-bottom:8px;">Tendencia del valor de predios H5/H6 en BJ (2023 → 2025)</div>
        <div class="bar-chart">
          <div class="bar-row"><div class="bar-lbl">Valor predio</div><div class="bar-track"><div class="bar-fill hi" style="width:65%;">+22% en 3 años</div></div></div>
          <div class="bar-row"><div class="bar-lbl">Desarrolladores activos</div><div class="bar-track"><div class="bar-fill hdv" style="width:85%;">30+ buscando en BJ hoy</div></div></div>
          <div class="bar-row"><div class="bar-lbl">Predios H5/H6 disponibles</div><div class="bar-track"><div class="bar-fill avg" style="width:30%;">-45% vs 2022</div></div></div>
        </div>

      {{-- ═══ VENTA COMERCIAL ════════════════════════════════════════ --}}
      @elseif($isVentaComercial)
        <p>Los <strong>inmuebles comerciales en Benito Juárez</strong> se valúan por rentabilidad: cap rate, NOI y flujo proyectado. El comprador es el inversionista que entiende estos números — y en BJ la demanda de activos comerciales de calidad es alta.</p>

        <div class="chart-title">Precio de venta comercial por m² — Benito Juárez (2025)</div>
        <div class="bar-chart">
          <div class="bar-row">
            <div class="bar-lbl hl">Local prime BJ</div>
            <div class="bar-track"><div class="bar-fill hdv" style="width:100%;">$85,000 – $150,000 /m²</div></div>
          </div>
          <div class="bar-row">
            <div class="bar-lbl">Oficina clase A</div>
            <div class="bar-track"><div class="bar-fill avg" style="width:78%;">$70,000 – $115,000 /m²</div></div>
          </div>
          <div class="bar-row">
            <div class="bar-lbl">Local secundario</div>
            <div class="bar-track"><div class="bar-fill avg" style="width:60%;">$50,000 – $82,000 /m²</div></div>
          </div>
          <div class="bar-row">
            <div class="bar-lbl">Bodega / uso mixto</div>
            <div class="bar-track"><div class="bar-fill hi" style="width:44%;">$36,000 – $60,000 /m²</div></div>
          </div>
        </div>
        <p class="chart-note">* Rangos para inmuebles en buenas condiciones, con contrato de arrendamiento vigente ideal. Fuente: Observatorio HDV 2025.</p>

        <div class="insight-box">
          <p><strong>Factor determinante:</strong> Un inmueble comercial con arrendatario solvente y contrato vigente puede alcanzar un <strong>cap rate de 6–8%</strong> atractivo para inversionistas. La presentación con datos de rentabilidad real es lo que define el precio de cierre.</p>
        </div>

        <div class="chart-title" style="margin-top:14px;margin-bottom:8px;">Métricas de inversión comercial en BJ (referencia 2025)</div>
        <div class="bar-chart">
          <div class="bar-row"><div class="bar-lbl">Cap rate objetivo</div><div class="bar-track"><div class="bar-fill hi" style="width:68%;">6–8% en inmuebles de calidad BJ</div></div></div>
          <div class="bar-row"><div class="bar-lbl">Inversionistas activos</div><div class="bar-track"><div class="bar-fill hdv" style="width:85%;">40+ en nuestra red hoy</div></div></div>
          <div class="bar-row"><div class="bar-lbl">Plusvalía comercial BJ</div><div class="bar-track"><div class="bar-fill avg" style="width:55%;">+8% anual 2022–2025</div></div></div>
        </div>

      {{-- ═══ VENTA RESIDENCIAL (default) ═══════════════════════════ --}}
      @else
        <p>Los inmuebles residenciales en <strong>Benito Juárez</strong> tienen la demanda activa más alta de la CDMX. Del Valle mantiene precios con tendencia alcista sólida, impulsados por la escasez de oferta nueva y la demanda sostenida de compradores calificados.</p>

        <div class="chart-title">Precio de venta promedio por m² — Colonias de referencia BJ (2025)</div>
        <div class="bar-chart">
          <div class="bar-row">
            <div class="bar-lbl hl">Del Valle</div>
            <div class="bar-track"><div class="bar-fill hdv" style="width:90%;">$65,000 – $90,000 /m²</div></div>
          </div>
          <div class="bar-row">
            <div class="bar-lbl">Narvarte Ote.</div>
            <div class="bar-track"><div class="bar-fill avg" style="width:80%;">$58,000 – $78,000 /m²</div></div>
          </div>
          <div class="bar-row">
            <div class="bar-lbl">Nápoles</div>
            <div class="bar-track"><div class="bar-fill avg" style="width:72%;">$52,000 – $70,000 /m²</div></div>
          </div>
          <div class="bar-row">
            <div class="bar-lbl">Insurgentes Sur</div>
            <div class="bar-track"><div class="bar-fill avg" style="width:63%;">$45,000 – $62,000 /m²</div></div>
          </div>
          <div class="bar-row">
            <div class="bar-lbl hi">Roma / Condesa</div>
            <div class="bar-track"><div class="bar-fill hi" style="width:100%;">$75,000 – $110,000 /m²</div></div>
          </div>
        </div>
        <p class="chart-note">* Casas y departamentos usados en buen estado. Fuente: Observatorio de Precios HDV 2025.</p>

        <div class="insight-box">
          <p><strong>Momento favorable:</strong> El mercado de BJ tiene <strong>35% menos oferta disponible</strong> que hace 2 años. Las propiedades bien posicionadas con precio correcto reciben <strong>3–6 propuestas serias en los primeros 30 días</strong>.</p>
        </div>

        <div class="chart-title" style="margin-top:14px;margin-bottom:8px;">Tendencia del mercado residencial BJ (2023 → 2025)</div>
        <div class="bar-chart">
          <div class="bar-row"><div class="bar-lbl">Precio m²</div><div class="bar-track"><div class="bar-fill hi" style="width:55%;">+8.5% anual promedio</div></div></div>
          <div class="bar-row"><div class="bar-lbl">Demanda activa</div><div class="bar-track"><div class="bar-fill hdv" style="width:78%;">+15% más compradores calificados</div></div></div>
          <div class="bar-row"><div class="bar-lbl">Oferta disponible</div><div class="bar-track"><div class="bar-fill avg" style="width:35%;">-35% menos inmuebles activos</div></div></div>
        </div>
      @endif

      {{-- Observatorio HDV — Precio real de tu colonia (si disponible) --}}
      @if(!empty($precioMercadoZona))
      <div class="obs-badge">
        <div style="flex:1;">
          <div class="obs-label">📊 Observatorio HDV · {{ $inmuebleColonia ?: 'Tu colonia' }}</div>
          <div class="obs-price">{{ $precioMercadoZona }}</div>
        </div>
        @if($confLabel)
        <div class="obs-conf {{ $confClass }}">{{ $confLabel }}</div>
        @endif
      </div>
      @endif

    </div>
  </div>
  <div class="page-foot">
    <strong>Home del Valle</strong>
    <span>{{ $sloganHDV }}</span>
    <span>{{ $fechaPresentacion }}</span>
  </div>
</div>

{{-- ═════════════════════════════════════════════
     PÁGINA 3 — ¿POR QUÉ HOME DEL VALLE?
════════════════════════════════════════════════ --}}
<div class="page">
  <div class="page-header-inner">
    @if(!empty($brandLogoSrcLight))
      <img src="{{ $brandLogoSrcLight }}" alt="Home del Valle">
    @elseif(!empty($brandLogoSrc))
      <img src="{{ $brandLogoSrc }}" alt="Home del Valle">
    @else
      <span class="phi-text">Home del Valle</span>
    @endif
    <span class="phi-tag">Presentación · Uso Confidencial</span>
  </div>
  <div class="page-body">
    <div class="inner">
      <div class="section-tag">Sobre nosotros</div>
      <h2 class="section-h2">¿Por qué Home del Valle?</h2>
      <div class="accent-bar"></div>

      <p>Estimado/a <strong>{{ $nombrePropietario }}</strong>, gracias por considerar a Home del Valle para su propiedad. Llevamos <strong>más de 30 años</strong> trabajando en Benito Juárez. No somos una agencia de volumen: seleccionamos cada caso para trabajarlo con el nivel de atención que merece.</p>

      <div class="stats-row">
        <div class="stat-box">
          <div class="stat-num">30<span class="accent">+</span></div>
          <div class="stat-lbl">Años en<br>el mercado BJ</div>
        </div>
        <div class="stat-box">
          <div class="stat-num"><span class="accent">&lt;</span>20</div>
          <div class="stat-lbl">Inmuebles activos<br>simultáneamente</div>
        </div>
        <div class="stat-box">
          <div class="stat-num">95<span class="accent">%</span></div>
          <div class="stat-lbl">Clientes que<br>nos recomiendan</div>
        </div>
        <div class="stat-box">
          <div class="stat-num"><span class="accent">$</span>0</div>
          <div class="stat-lbl">Cobros<br>anticipados</div>
        </div>
      </div>

      <table class="cmp-table">
        <thead>
          <tr>
            <th>Agencia de volumen</th>
            <th>Home del Valle</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>100+ inmuebles activos, poca atención por caso</td>
            <td>Máximo 20 activos — atención real a tu inmueble</td>
          </tr>
          <tr>
            <td>Asesor de nivel junior asignado</td>
            <td>Dirección General involucrada en cada caso</td>
          </tr>
          <tr>
            <td>Proceso estándar para todos los inmuebles</td>
            <td>Estrategia personalizada según tu objetivo y plazo</td>
          </tr>
          <tr>
            <td>Legal tercerizado o sin cobertura</td>
            <td>Área jurídica interna — blindaje desde el día 1</td>
          </tr>
          <tr>
            <td>Sin seguimiento post-cierre</td>
            <td>Portal del Propietario con visibilidad total</td>
          </tr>
        </tbody>
      </table>

      {{-- Testimonial — real de la tabla testimonials si hay uno aplicable a
           la zona/tipo de operación; si no, el texto genérico de respaldo
           (nunca se inventa un dato específico nuevo). Ver docs/07-FLUJO-
           CAPTACION-Y-MEJORAS.md sección 4. --}}
      @php
        $testimonioReal = null;
        if (!empty($inmuebleColonia)) {
          $testimonioReal = \App\Models\Testimonial::active()
            ->where('operation_type', $isRenta ? 'Renta' : 'Venta')
            ->where('location', 'like', '%' . trim(explode(',', $inmuebleColonia)[0]) . '%')
            ->inRandomOrder()
            ->first();
        }
      @endphp
      @if($testimonioReal)
      <div class="testimonial">
        <div class="quote">{{ $testimonioReal->content }}</div>
        <div class="quote-author">— {{ $testimonioReal->name }}{{ $testimonioReal->location ? ', ' . $testimonioReal->location : '' }}</div>
      </div>
      @elseif($isRenta)
      <div class="testimonial">
        <div class="quote">HDV nos encontró un inquilino excelente en 18 días. La póliza jurídica nos dio mucha tranquilidad y el proceso fue completamente transparente.</div>
        <div class="quote-author">— Propietaria de departamento, Colonia del Valle (2024)</div>
      </div>
      @elseif($isConstructor)
      <div class="testimonial">
        <div class="quote">Recibimos 4 ofertas en 3 semanas y cerramos 12% por encima del precio que teníamos en mente. La red de desarrolladores de HDV es real y activa.</div>
        <div class="quote-author">— Propietario de predio, Colonia Narvarte (2024)</div>
      </div>
      @else
      <div class="testimonial">
        <div class="quote">Vendimos en 42 días al precio que queríamos. El equipo de HDV estuvo con nosotros en cada paso — y sin sorpresas legales ni de último minuto.</div>
        <div class="quote-author">— Propietaria de casa, Colonia del Valle (2024)</div>
      </div>
      @endif

    </div>
  </div>
  <div class="page-foot">
    <strong>Home del Valle</strong>
    <span>{{ $sloganHDV }}</span>
    <span>{{ $fechaPresentacion }}</span>
  </div>
</div>

{{-- ═════════════════════════════════════════════
     PÁGINA 4 — LO QUE PROPONEMOS (intent-específico)
════════════════════════════════════════════════ --}}
<div class="page">
  <div class="page-header-inner">
    @if(!empty($brandLogoSrcLight))
      <img src="{{ $brandLogoSrcLight }}" alt="Home del Valle">
    @elseif(!empty($brandLogoSrc))
      <img src="{{ $brandLogoSrc }}" alt="Home del Valle">
    @else
      <span class="phi-text">Home del Valle</span>
    @endif
    <span class="phi-tag">Presentación · Uso Confidencial</span>
  </div>
  <div class="page-body">
    <div class="inner">
      <div class="section-tag">Nuestra propuesta</div>
      <h2 class="section-h2">Lo que proponemos<br>para tu inmueble</h2>
      <div class="accent-bar"></div>

      {!! $proposicion !!}

      @if(!empty($precioSugerido))
      <div class="price-box">
        <div>
          <div class="price-label">Precio de referencia sugerido · HDV Observatorio de Precios</div>
          <div class="price-val">{{ $precioSugerido }}</div>
        </div>
      </div>
      @endif

    </div>
  </div>
  <div class="page-foot">
    <strong>Home del Valle</strong>
    <span>{{ $sloganHDV }}</span>
    <span>{{ $fechaPresentacion }}</span>
  </div>
</div>

{{-- ═════════════════════════════════════════════
     PÁGINA 5 — NUESTRO PROCESO
     Timeline visual + plan de marketing
════════════════════════════════════════════════ --}}
<div class="page">
  <div class="page-header-inner">
    @if(!empty($brandLogoSrcLight))
      <img src="{{ $brandLogoSrcLight }}" alt="Home del Valle">
    @elseif(!empty($brandLogoSrc))
      <img src="{{ $brandLogoSrc }}" alt="Home del Valle">
    @else
      <span class="phi-text">Home del Valle</span>
    @endif
    <span class="phi-tag">Presentación · Uso Confidencial</span>
  </div>
  <div class="page-body">
    <div class="inner">
      <div class="section-tag">De la llamada al cierre</div>
      <h2 class="section-h2">Nuestro proceso<br>paso a paso</h2>
      <div class="accent-bar"></div>

      @php $isRentaP = $isRenta; @endphp

      {{-- Timeline visual --}}
      <div class="tl">
        <div class="tl-step">
          <div class="tl-circle active">1</div>
          <div class="tl-name">Valuación<br>técnica</div>
          <div class="tl-days">1–3 días</div>
        </div>
        <div class="tl-step">
          <div class="tl-circle">2</div>
          <div class="tl-name">Preparación<br>del inmueble</div>
          <div class="tl-days">1 semana</div>
        </div>
        <div class="tl-step">
          <div class="tl-circle">3</div>
          <div class="tl-name">{!! $isRentaP ? 'Búsqueda de<br>candidatos' : 'Publicación<br>y difusión' !!}</div>
          <div class="tl-days">1–2 semanas</div>
        </div>
        <div class="tl-step">
          <div class="tl-circle">4</div>
          <div class="tl-name">{!! $isRentaP ? 'Calificación<br>y selección' : 'Visitas y<br>negociación' !!}</div>
          <div class="tl-days">{{ $isRentaP ? '1 semana' : '2–4 semanas' }}</div>
        </div>
        <div class="tl-step">
          <div class="tl-circle">5</div>
          <div class="tl-name">{!! $isRentaP ? 'Firma de<br>contrato' : 'Escrituración<br>y cierre' !!}</div>
          <div class="tl-days">{{ $isRentaP ? '1–3 días' : '1–3 semanas' }}</div>
        </div>
      </div>

      <p style="font-size:11px;color:#64748b;text-align:center;margin-bottom:16px;">
        Tiempo total estimado: <strong style="color:#1e1b4b;">{{ $isRentaP ? '30–45 días' : '60–90 días' }}</strong> de la firma de exclusiva al cierre.
      </p>

      {{-- Plan de marketing visual --}}
      <div class="section-tag" style="margin-top:4px;">Plan de acción</div>
      <div class="mkt-grid">
        <div class="mkt-item">
          <h4>📸 Fotografía y video profesional</h4>
          <p>Sesión con fotógrafo especializado en inmuebles y video tour.</p>
        </div>
        <div class="mkt-item">
          <h4>📋 Ficha boutique</h4>
          <p>Descripción curada y materiales editoriales de alta calidad.</p>
        </div>
        <div class="mkt-item">
          <h4>🎯 Difusión segmentada</h4>
          <p>Nuestra base de {{ $isRentaP ? 'inquilinos' : 'compradores' }} calificados + portales clave.</p>
        </div>
        <div class="mkt-item">
          <h4>📊 Reporte semanal</h4>
          <p>Métricas reales: vistas, consultas e interés real.</p>
        </div>
      </div>

      @if(!empty($planMarketing))
      <div class="mkt-box" style="margin-top:10px;">
        <p>{{ $planMarketing }}</p>
      </div>
      @endif

    </div>
  </div>
  <div class="page-foot">
    <strong>Home del Valle</strong>
    <span>{{ $sloganHDV }}</span>
    <span>{{ $fechaPresentacion }}</span>
  </div>
</div>

{{-- ═════════════════════════════════════════════
     PÁGINA 6 — SERVICIOS INCLUIDOS
════════════════════════════════════════════════ --}}
<div class="page">
  <div class="page-header-inner">
    @if(!empty($brandLogoSrcLight))
      <img src="{{ $brandLogoSrcLight }}" alt="Home del Valle">
    @elseif(!empty($brandLogoSrc))
      <img src="{{ $brandLogoSrc }}" alt="Home del Valle">
    @else
      <span class="phi-text">Home del Valle</span>
    @endif
    <span class="phi-tag">Presentación · Uso Confidencial</span>
  </div>
  <div class="page-body">
    <div class="inner">
      <div class="section-tag">Sin costo adicional</div>
      <h2 class="section-h2">Todo incluido en<br>nuestra comisión</h2>
      <div class="accent-bar"></div>

      <p>Una sola comisión. Sin anticipos, sin cobros adicionales, sin sorpresas. Todo lo que necesitas para cerrar bien.</p>

      @if($esRenta)
      <div class="svc-grid">
        <div class="svc-card">
          <span class="svc-icon">📊</span>
          <h4>Valuación de renta</h4>
          <p>Precio óptimo de renta basado en datos reales del Observatorio HDV.</p>
        </div>
        <div class="svc-card">
          <span class="svc-icon">🔍</span>
          <h4>Calificación de candidatos</h4>
          <p>Buró de crédito, comprobante de ingresos 3× y referencias verificadas.</p>
        </div>
        <div class="svc-card">
          <span class="svc-icon">⚖️</span>
          <h4>Póliza jurídica</h4>
          <p>Cobertura hasta 18 meses de renta en caso de incumplimiento del inquilino.</p>
        </div>
        <div class="svc-card">
          <span class="svc-icon">📋</span>
          <h4>Contrato de arrendamiento</h4>
          <p>Con cláusulas de protección al propietario e inventario fotográfico.</p>
        </div>
        <div class="svc-card">
          <span class="svc-icon">💳</span>
          <h4>Administración de cobro</h4>
          <p>Gestión del cobro mensual y reporte de pagos puntual.</p>
        </div>
        <div class="svc-card">
          <span class="svc-icon">📱</span>
          <h4>Portal del Propietario</h4>
          <p>Seguimiento 24/7: pagos, historial del inquilino y documentos.</p>
        </div>
      </div>
      @else
      <div class="svc-grid">
        <div class="svc-card">
          <span class="svc-icon">📊</span>
          <h4>Valuación profesional</h4>
          <p>Precio óptimo basado en datos reales del Observatorio HDV.</p>
        </div>
        <div class="svc-card">
          <span class="svc-icon">⚖️</span>
          <h4>Blindaje legal</h4>
          <p>Contratos y revisión jurídica por nuestro equipo interno.</p>
        </div>
        <div class="svc-card">
          <span class="svc-icon">📁</span>
          <h4>Gestoría documental</h4>
          <p>Recopilamos y verificamos toda la documentación necesaria.</p>
        </div>
        <div class="svc-card">
          <span class="svc-icon">💰</span>
          <h4>Asesoría fiscal</h4>
          <p>Orientación sobre ISR, IVA y deducciones de tu operación.</p>
        </div>
        <div class="svc-card">
          <span class="svc-icon">🏛️</span>
          <h4>Acompañamiento notarial</h4>
          <p>Presencia el día de la firma con notario de tu confianza.</p>
        </div>
        <div class="svc-card">
          <span class="svc-icon">📱</span>
          <h4>Portal del Propietario</h4>
          <p>Seguimiento 24/7 desde tu celular: documentos, avances y pagos.</p>
        </div>
      </div>
      @endif

      <div class="svc-note">
        🔒 <strong>Confidencialidad garantizada.</strong> Tus datos y los del inmueble solo se comparten con candidatos calificados y verificados. Nunca en redes abiertas sin tu autorización.
      </div>

    </div>
  </div>
  <div class="page-foot">
    <strong>Home del Valle</strong>
    <span>{{ $sloganHDV }}</span>
    <span>{{ $fechaPresentacion }}</span>
  </div>
</div>

{{-- ═════════════════════════════════════════════
     PÁGINA 7 — COMISIÓN Y CIERRE
════════════════════════════════════════════════ --}}
<div class="page">
  <div class="page-header-inner">
    @if(!empty($brandLogoSrcLight))
      <img src="{{ $brandLogoSrcLight }}" alt="Home del Valle">
    @elseif(!empty($brandLogoSrc))
      <img src="{{ $brandLogoSrc }}" alt="Home del Valle">
    @else
      <span class="phi-text">Home del Valle</span>
    @endif
    <span class="phi-tag">Presentación · Uso Confidencial</span>
  </div>
  <div class="page-body">
    <div class="inner">
      <div class="section-tag">Propuesta económica</div>
      <h2 class="section-h2">Comisión y<br>próximos pasos</h2>
      <div class="accent-bar"></div>

      @if($esRenta)
      <div class="com-hero" style="background:linear-gradient(135deg,#eff6ff,#dbeafe);border-color:#bfdbfe;">
        <div style="font-size:9px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#1e40af;margin-bottom:8px;">COMISIÓN DE COLOCACIÓN</div>
        <div class="com-pct" style="font-size:40px;color:#1e40af;letter-spacing:-.5px;line-height:1.2;">{{ $comisionLabel }}</div>
        <div class="com-desc" style="color:#1e3a8a;">Comisión única de colocación · Sin cobros mensuales · Sin anticipos · Se carga al cierre del contrato</div>
      </div>
      @else
      <div class="com-hero">
        <div class="com-pct">{{ $comisionLabel }}</div>
        <div class="com-desc">Comisión sobre precio de cierre · Sin anticipos · Sin cargos extras</div>
      </div>
      @endif

      <ul class="steps">
        @if($esRenta)
        @foreach([
          'Agendamos la visita técnica al inmueble esta semana',
          'Tomamos fotografías y preparamos los materiales de difusión',
          'Firmamos el contrato de exclusiva de arrendamiento',
          'Iniciamos la búsqueda activa y calificación de candidatos',
        ] as $i => $paso)
        <li><span class="step-n">{{ $i + 1 }}</span>{{ $paso }}</li>
        @endforeach
        @else
        @foreach([
          'Agendamos la visita técnica al inmueble esta semana',
          'Presentamos nuestra valuación y confirmamos el precio de salida',
          'Firmamos el contrato de exclusiva o de comercialización',
          'Iniciamos la estrategia de marketing de inmediato',
        ] as $i => $paso)
        <li><span class="step-n">{{ $i + 1 }}</span>{{ $paso }}</li>
        @endforeach
        @endif
      </ul>

      @if($esRenta)
      <div class="cta-box">
        <div class="cta-text">
          <h4>¿Listo para colocar tu inmueble?</h4>
          <p>Inquilino calificado en 30–45 días con protección legal total.</p>
        </div>
        <div class="cta-badge">Agendar visita →</div>
      </div>
      @else
      <div class="cta-box">
        <div class="cta-text">
          <h4>¿Listo para empezar?</h4>
          <p>Agenda la visita técnica esta semana y en 3 días tendrás tu valuación.</p>
        </div>
        <div class="cta-badge">Agendar visita →</div>
      </div>
      @endif

      <div class="agent-card">
        <div class="agent-av">{{ strtoupper(substr($nombreAgente, 0, 1)) }}</div>
        <div class="agent-info">
          <h3>{{ $nombreAgente }}</h3>
          <p>Agente · Home del Valle Bienes Raíces</p>
          @if($telefonoAgente)<p>{{ $telefonoAgente }}</p>@endif
          @if($emailAgente)<p>{{ $emailAgente }}</p>@endif
        </div>
      </div>

      <div class="disclaimer">
        Este documento es informativo y no constituye oferta vinculante. Los términos comerciales se formalizan al firmar el contrato de exclusiva o de comercialización con Home del Valle Bienes Raíces. Los datos de mercado son referenciales y se actualizan periódicamente en el Observatorio de Precios HDV.
      </div>

    </div>
  </div>
  <div class="page-foot">
    <strong>Home del Valle</strong>
    <span>{{ $sloganHDV }}</span>
    <span>{{ $fechaPresentacion }}</span>
  </div>
</div>

</body>
</html>
