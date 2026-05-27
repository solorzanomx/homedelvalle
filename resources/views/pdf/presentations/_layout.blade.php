<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
@import url('https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap');

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
.blist { list-style: none; margin: 10px 0 16px; }
.blist li { padding: 8px 0 8px 22px; position: relative; border-bottom: 1px solid #f1f5f9; color: #475569; font-size: 12.5px; line-height: 1.5; }
.blist li:last-child { border-bottom: none; }
.blist li::before { content: ''; position: absolute; left: 0; top: 15px; width: 6px; height: 6px; border-radius: 50%; background: #10b981; }

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
      <img src="{{ $photoUrl }}" class="cover-photo" alt="Inmueble">
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
  <div class="page-body">
    <div class="inner">
      <div class="section-tag">Contexto de mercado</div>
      <h2 class="section-h2">El mercado en tu zona</h2>
      <div class="accent-bar"></div>

      @php
        // Variables de intent disponibles en todas las páginas desde aquí
        $isRenta = in_array($captacion->intent ?? 'general', ['renta_residencial', 'renta_comercial']);
        $isConstructor = ($captacion->intent ?? '') === 'venta_constructor';
      @endphp

      @if($isRenta)
        <p>La <strong>demanda de rentas en Benito Juárez</strong> supera la oferta disponible desde 2022. Las rentas en Del Valle y colonias aledañas han crecido un <strong>11% anual</strong> en los últimos tres años, y la tasa de vacancia es de apenas el 4%.</p>

        <div class="chart-title">Renta promedio mensual por colonia (Benito Juárez, 2025)</div>
        <div class="bar-chart">
          <div class="bar-row">
            <div class="bar-lbl hl">Del Valle</div>
            <div class="bar-track"><div class="bar-fill hdv" style="width:100%;">$28,000 – $45,000</div></div>
          </div>
          <div class="bar-row">
            <div class="bar-lbl">Narvarte Ote.</div>
            <div class="bar-track"><div class="bar-fill avg" style="width:87%;">$24,000 – $38,000</div></div>
          </div>
          <div class="bar-row">
            <div class="bar-lbl">Nápoles</div>
            <div class="bar-track"><div class="bar-fill avg" style="width:80%;">$22,000 – $35,000</div></div>
          </div>
          <div class="bar-row">
            <div class="bar-lbl">Insurgentes Sur</div>
            <div class="bar-track"><div class="bar-fill avg" style="width:70%;">$19,000 – $30,000</div></div>
          </div>
          <div class="bar-row">
            <div class="bar-lbl">Col. del Valle</div>
            <div class="bar-track"><div class="bar-fill hi" style="width:95%;">Roma / Condesa comparado</div></div>
          </div>
        </div>
        <p class="chart-note">* Rango para departamentos de 2 recámaras (80–120 m²). Fuente: Observatorio de Precios HDV 2025.</p>

        <div class="insight-box">
          <p><strong>Momento ideal:</strong> Con vacancia de 4% y lista de espera activa, colocar tu inmueble en renta hoy significa inquilino calificado en 15–30 días. El precio de renta tiene margen al alza si el inmueble se presenta correctamente.</p>
        </div>

        <div class="chart-title" style="margin-top:16px;margin-bottom:8px;">Tendencia del mercado de renta en BJ (2023 → 2025)</div>
        <div class="bar-chart">
          <div class="bar-row"><div class="bar-lbl">Precio renta</div><div class="bar-track"><div class="bar-fill hi" style="width:60%;">+11% anual promedio</div></div></div>
          <div class="bar-row"><div class="bar-lbl">Demanda activa</div><div class="bar-track"><div class="bar-fill hdv" style="width:80%;">+22% más candidatos</div></div></div>
          <div class="bar-row"><div class="bar-lbl">Oferta disponible</div><div class="bar-track"><div class="bar-fill avg" style="width:38%;">-28% menos inventario</div></div></div>
        </div>

      @elseif($isConstructor)
        <p>Los predios en Benito Juárez con zonificación <strong>H5 y H6</strong> se han revalorizado <strong>22% en 3 años</strong>. La presión de desarrolladores sigue creciendo ante la escasez de predios disponibles en la zona.</p>

        <div class="chart-title">Valor de predio por m² de terreno — Colonias clave BJ (2025)</div>
        <div class="bar-chart">
          <div class="bar-row">
            <div class="bar-lbl hl">Del Valle</div>
            <div class="bar-track"><div class="bar-fill hdv" style="width:100%;">$45,000 – $75,000 / m²</div></div>
          </div>
          <div class="bar-row">
            <div class="bar-lbl">Narvarte</div>
            <div class="bar-track"><div class="bar-fill avg" style="width:85%;">$40,000 – $65,000 / m²</div></div>
          </div>
          <div class="bar-row">
            <div class="bar-lbl">Nápoles</div>
            <div class="bar-track"><div class="bar-fill avg" style="width:75%;">$35,000 – $55,000 / m²</div></div>
          </div>
          <div class="bar-row">
            <div class="bar-lbl">Insurgentes Sur</div>
            <div class="bar-track"><div class="bar-fill avg" style="width:65%;">$30,000 – $48,000 / m²</div></div>
          </div>
        </div>
        <p class="chart-note">* Valores para predios con H5/H6 sin construcción relevante. Fuente: Observatorio HDV 2025.</p>

        <div class="insight-box">
          <p><strong>Por qué ahora:</strong> Hay 30+ desarrolladores activos buscando predios en BJ con poco inventario disponible. Múltiples ofertas simultáneas elevan el precio final. La escasez de predios con ZP favorable no va a durar.</p>
        </div>

        <div class="chart-title" style="margin-top:16px;margin-bottom:8px;">Tendencia del valor de predios en BJ (2023 → 2025)</div>
        <div class="bar-chart">
          <div class="bar-row"><div class="bar-lbl">Valor predio</div><div class="bar-track"><div class="bar-fill hi" style="width:65%;">+22% en 3 años</div></div></div>
          <div class="bar-row"><div class="bar-lbl">Desarrolladores</div><div class="bar-track"><div class="bar-fill hdv" style="width:85%;">30+ activos buscando hoy</div></div></div>
          <div class="bar-row"><div class="bar-lbl">Predios H5/H6</div><div class="bar-track"><div class="bar-fill avg" style="width:32%;">-45% disponibles vs 2022</div></div></div>
        </div>

      @else
        <p>Los inmuebles residenciales en <strong>Benito Juárez</strong> tienen la demanda más alta de la CDMX. Del Valle mantiene precios de venta estables con tendencia alcista, impulsados por la escasez de oferta nueva.</p>

        <div class="chart-title">Precio de venta promedio por m² — Colonias de referencia BJ (2025)</div>
        <div class="bar-chart">
          <div class="bar-row">
            <div class="bar-lbl hl">Del Valle</div>
            <div class="bar-track"><div class="bar-fill hdv" style="width:90%;">$65,000 – $85,000 / m²</div></div>
          </div>
          <div class="bar-row">
            <div class="bar-lbl">Narvarte Ote.</div>
            <div class="bar-track"><div class="bar-fill avg" style="width:80%;">$58,000 – $75,000 / m²</div></div>
          </div>
          <div class="bar-row">
            <div class="bar-lbl">Nápoles</div>
            <div class="bar-track"><div class="bar-fill avg" style="width:72%;">$52,000 – $68,000 / m²</div></div>
          </div>
          <div class="bar-row">
            <div class="bar-lbl">Insurgentes Sur</div>
            <div class="bar-track"><div class="bar-fill avg" style="width:63%;">$45,000 – $60,000 / m²</div></div>
          </div>
          <div class="bar-row">
            <div class="bar-lbl hi">Roma / Condesa</div>
            <div class="bar-track"><div class="bar-fill hi" style="width:100%;">$75,000 – $105,000 / m²</div></div>
          </div>
        </div>
        <p class="chart-note">* Casas y departamentos usados en buen estado. Fuente: Observatorio de Precios HDV 2025.</p>

        <div class="insight-box">
          <p><strong>Momento favorable:</strong> El mercado de BJ tiene <strong>35% menos oferta disponible</strong> que hace 2 años. Con la estrategia correcta de precio y presentación, las propiedades bien posicionadas reciben 3–6 propuestas serias en los primeros 30 días.</p>
        </div>

        <div class="chart-title" style="margin-top:16px;margin-bottom:8px;">Tendencia del mercado residencial BJ (2023 → 2025)</div>
        <div class="bar-chart">
          <div class="bar-row"><div class="bar-lbl">Precio m²</div><div class="bar-track"><div class="bar-fill hi" style="width:55%;">+8.5% anual promedio</div></div></div>
          <div class="bar-row"><div class="bar-lbl">Demanda activa</div><div class="bar-track"><div class="bar-fill hdv" style="width:78%;">+15% más compradores</div></div></div>
          <div class="bar-row"><div class="bar-lbl">Oferta disponible</div><div class="bar-track"><div class="bar-fill avg" style="width:35%;">-35% menos inmuebles</div></div></div>
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
  <div class="page-body">
    <div class="inner">
      <div class="section-tag">Sobre nosotros</div>
      <h2 class="section-h2">¿Por qué Home del Valle?</h2>
      <div class="accent-bar"></div>

      <p>Llevamos <strong>más de 30 años</strong> trabajando en Benito Juárez. No somos una agencia de volumen: seleccionamos cada caso para trabajarlo con el nivel de atención que merece.</p>

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

      {{-- Testimonial --}}
      @if($isRenta)
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
  <div class="page-body">
    <div class="inner">
      <div class="section-tag">Sin costo adicional</div>
      <h2 class="section-h2">Todo incluido en<br>nuestra comisión</h2>
      <div class="accent-bar"></div>

      <p>Una sola comisión. Sin anticipos, sin cobros adicionales, sin sorpresas. Todo lo que necesitas para cerrar bien.</p>

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
  <div class="page-body">
    <div class="inner">
      <div class="section-tag">Propuesta económica</div>
      <h2 class="section-h2">Comisión y<br>próximos pasos</h2>
      <div class="accent-bar"></div>

      <div class="com-hero">
        <div class="com-pct">{{ $comisionPct }}%</div>
        <div class="com-desc">Comisión sobre precio de cierre · Sin anticipos · Sin cargos extras</div>
      </div>

      <ul class="steps">
        @foreach([
          'Agendamos la visita técnica al inmueble esta semana',
          'Presentamos nuestra valuación y confirmamos el precio de salida',
          'Firmamos el contrato de exclusiva o de comercialización',
          'Iniciamos la estrategia de marketing de inmediato',
        ] as $i => $paso)
        <li><span class="step-n">{{ $i + 1 }}</span>{{ $paso }}</li>
        @endforeach
      </ul>

      <div class="cta-box">
        <div class="cta-text">
          <h4>¿Listo para empezar?</h4>
          <p>Agenda la visita técnica esta semana y en 3 días tendrás tu valuación.</p>
        </div>
        <div class="cta-badge">Agendar visita →</div>
      </div>

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
