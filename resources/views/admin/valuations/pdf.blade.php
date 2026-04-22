@php
// ─── Config ──────────────────────────────────────────────────────────────────
$siteName = 'Home del Valle';
$siteUrl  = 'www.homedelvalle.mx';
$folio    = 'OV-' . str_pad($valuation->id, 5, '0', STR_PAD_LEFT);
$today    = now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
$validity = now()->addDays(90)->locale('es')->isoFormat('D [de] MMMM [de] YYYY');

// ─── Logo ─────────────────────────────────────────────────────────────────────
// logoSrc      = white logo (for dark bg box in header)
// logoSrcLight = dark logo (for light bg) — uses logo_path_dark if uploaded
$siteSetting  = \App\Models\SiteSetting::first();
$logoSrc      = null;
$logoSrcLight = null;
$logoPath     = $siteSetting?->logo_path
    ? storage_path('app/public/' . $siteSetting->logo_path)
    : public_path('images/logo-homedelvalle.png');
if ($logoPath && file_exists($logoPath)) {
    $mime    = mime_content_type($logoPath) ?: 'image/png';
    $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
}
$logoDarkPath = $siteSetting?->logo_path_dark
    ? storage_path('app/public/' . $siteSetting->logo_path_dark)
    : null;
if ($logoDarkPath && file_exists($logoDarkPath)) {
    $mime2        = mime_content_type($logoDarkPath) ?: 'image/png';
    $logoSrcLight = 'data:' . $mime2 . ';base64,' . base64_encode(file_get_contents($logoDarkPath));
}

// ─── Fonts (embebidas como base64 para funcionar sin internet) ────────────────
$fontsDir  = resource_path('fonts');
$fontInter   = $fontsDir . '/inter-latin.woff2';
$fontPlay4   = $fontsDir . '/playfair-latin.woff2';
$fontPlay7   = $fontsDir . '/playfair-700-latin.woff2';
$b64Inter  = file_exists($fontInter) ? base64_encode(file_get_contents($fontInter)) : null;
$b64Play4  = file_exists($fontPlay4) ? base64_encode(file_get_contents($fontPlay4)) : null;
$b64Play7  = file_exists($fontPlay7) ? base64_encode(file_get_contents($fontPlay7)) : null;

// ─── Contact ──────────────────────────────────────────────────────────────────
$contactPhone = $siteSetting?->contact_phone ?? $siteSetting?->whatsapp_number ?? '';
$contactEmail = $siteSetting?->contact_email ?? '';

// ─── Valuation data ───────────────────────────────────────────────────────────
$colonia    = $valuation->colonia?->name ?? $valuation->input_colonia_raw ?? '—';
$zone       = $valuation->colonia?->zone?->name ?? 'Benito Juárez';
$typeLabel  = $valuation->type_label;
$ageLabel   = match($valuation->age_category) {
    'new'  => 'Nuevo  ·  0 – 10 años',
    'mid'  => 'Seminuevo  ·  10 – 30 años',
    'old'  => 'Antiguo  ·  más de 30 años',
    default => $valuation->age_category ?? '—',
};

$address = $valuation->property?->address
    ? ($valuation->property->address . ($valuation->property->city ? ', ' . $valuation->property->city : ''))
    : ($typeLabel . ' en ' . $colonia . ', ' . $zone . ', CDMX');

$diagLabel = $valuation->diagnosis_label;
[$diagBg, $diagColor, $diagBorder] = match($valuation->diagnosis) {
    'on_market'    => ['#DBEAFE', '#1E3A8A', '#93C5FD'],
    'above_market' => ['#FEF9C3', '#713F12', '#FDE047'],
    'opportunity'  => ['#DCFCE7', '#14532D', '#86EFAC'],
    default        => ['#F3F4F6', '#374151', '#D1D5DB'],
};

$n    = $valuation->ai_narrative ?? [];
$low  = (float)($valuation->total_value_low  ?? 0);
$mid  = (float)($valuation->total_value_mid  ?? 0);
$high = (float)($valuation->total_value_high ?? 0);
$sug  = (float)($valuation->suggested_list_price ?? 0);

// Range bar percentages (30–100 scale so low never looks empty)
$rangeMin = $low  * 0.97;
$rangeMax = $high * 1.03;
$rangeSpan = $rangeMax - $rangeMin;
$pLow  = $rangeSpan > 0 ? round(($low - $rangeMin) / $rangeSpan * 100) : 30;
$pMid  = $rangeSpan > 0 ? round(($mid - $rangeMin) / $rangeSpan * 100) : 60;
$pHigh = 100;
$pSug  = $rangeSpan > 0 ? min(100, round(($sug  - $rangeMin) / $rangeSpan * 100)) : 75;

$adjTotal = $valuation->adjustments->isNotEmpty()
    ? round((($valuation->adjusted_price_m2 - $valuation->base_price_m2) / $valuation->base_price_m2) * 100, 1)
    : 0;

$condLabel   = $valuation->condition_label;
$confidLabel = ['high'=>'Alta','medium'=>'Media','low'=>'Baja'][$valuation->confidence] ?? '—';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>{{ $folio }} — Opinión de Valor — {{ $colonia }}</title>
<style>
/* ═══ FONTS ═══════════════════════════════════════════════════════════════════
   Inter      — sans-serif moderna (TODO: cuerpo, tablas, labels y números)
   Playfair Display — cargada pero no usada (reservada para uso futuro)
   Embebidas como base64 para funcionar sin conexión a internet
══════════════════════════════════════════════════════════════════════════════ */
@if($b64Inter)
@font-face {
    font-family: 'Inter';
    font-style: normal;
    font-weight: 100 900;
    font-display: swap;
    src: url('data:font/woff2;base64,{{ $b64Inter }}') format('woff2');
}
@endif

/* ═══ RESET ══════════════════════════════════════════════════════════════════ */
* { margin:0; padding:0; box-sizing:border-box; }

/* ═══ PAGE ═══════════════════════════════════════════════════════════════════ */
@page { size: A4 portrait; margin: 0; }
html, body { font-family: 'Inter', Arial, Helvetica, sans-serif; font-size: 14px; color: #1A1A1A; background: #fff; }

/* ═══ PALETA ══════════════════════════════════════════════════════════════════
   #0C1A2E  navy (header bg, footer bg, price bar)
   #1A3560  medium navy (identity bg)
   #2563A0  brand blue (accents, bars, section headings)
   #F4F6F8  cell bg      #E8ECF2  dividers      #5A6A7A  muted
══════════════════════════════════════════════════════════════════════════════ */

/* ═══ PAGE 1 ══════════════════════════════════════════════════════════════════ */
.pg1 { width:100%; height:297mm; position:relative; overflow:hidden; page-break-after:always; }

/* ── CABECERA ──────────────────────────────────────────────────────────────── */
.header {
    background: #fff;
    padding: 8mm 14mm 6mm;
    border-bottom: 3px solid #2563A0;
    position: relative;
}
.header-inner { display:flex; justify-content:space-between; align-items:center; gap:8mm; }

/* Logo side — dark box contains the white logo */
.header-brand  { flex-shrink:0; }
.logo-box {
    background: #0C1A2E;
    display: inline-flex; align-items: center; justify-content: center;
    padding: 7px 10px; border-radius: 3px;
}
.header-logo      { height:30px; width:auto; display:block; }
.header-tagline {
    font-size:8.5px; color:#5A6A7A; text-transform:uppercase;
    letter-spacing:1.2px; line-height:1.7; margin-top:6px; font-weight:500;
}

/* Center — document identity */
.header-center  { flex:1; text-align:center; }
.header-doc-type { font-size:9px; text-transform:uppercase; letter-spacing:3.5px; color:#9CA3AF; margin-bottom:4px; font-weight:500; }
.header-title    { font-size:21px; font-weight:700; color:#0C1A2E; letter-spacing:-0.3px; line-height:1.2; }
.header-subtitle { font-size:11px; color:#6B7280; margin-top:5px; line-height:1.5; }

/* Right — folio */
.header-meta         { text-align:right; flex-shrink:0; }
.header-folio-label  { font-size:8.5px; color:#9CA3AF; text-transform:uppercase; letter-spacing:1.5px; }
.header-folio        { font-size:15px; font-weight:700; color:#0C1A2E; margin-top:2px; }
.header-date         { font-size:10px; color:#6B7280; margin-top:3px; }

/* ── HERO PRECIO ───────────────────────────────────────────────────────────── */
.price-hero {
    background: linear-gradient(135deg, #0F2341 0%, #1A3560 60%, #1E4080 100%);
    padding: 9mm 14mm 8mm;
    display:flex; align-items:center; justify-content:space-between; gap:8mm;
    border-top: 1px solid rgba(255,255,255,.06);
}
.price-eyebrow { font-size:9.5px; color:rgba(255,255,255,.45); text-transform:uppercase; letter-spacing:2.5px; margin-bottom:8px; font-weight:500; }
.price-amount  {
    font-family: 'Inter', Arial, sans-serif;
    font-size:38px; font-weight:500; color:#fff;
    line-height:1; letter-spacing:-1px;
}
.price-currency { font-size:17px; font-weight:400; color:rgba(255,255,255,.5); vertical-align:top; line-height:38px; margin-right:1px; }
.price-sub { font-size:12px; color:rgba(255,255,255,.45); margin-top:8px; font-weight:400; letter-spacing:0.1px; }
.price-right { text-align:right; flex-shrink:0; }
.diag-badge {
    display:inline-block; padding:5px 16px; border-radius:2px;
    font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:1.5px;
    background:{{ $diagBg }}; color:{{ $diagColor }}; margin-bottom:14px;
}
.price-m2-val { font-size:21px; font-weight:600; color:#fff; font-family:'Inter',Arial,sans-serif; letter-spacing:-0.3px; }
.price-m2-lbl { font-size:9.5px; color:rgba(255,255,255,.4); text-transform:uppercase; letter-spacing:1px; margin-top:4px; }

/* ── STATS BAR ────────────────────────────────────────────────────────────── */
.stats-bar { display:flex; background:#fff; border-bottom:1px solid #E8ECF2; }
.stat-cell {
    flex:1; text-align:center; padding:10px 6px 9px;
    border-right:1px solid #E8ECF2; border-top:3px solid #2563A0;
}
.stat-cell:last-child { border-right:none; }
.stat-v {
    display:block;
    font-family:'Inter', Arial, sans-serif;
    font-size:17px; font-weight:600; color:#0C1A2E; line-height:1; letter-spacing:-0.3px;
}
.stat-v-sm { font-size:13px; }
.stat-l { display:block; font-size:8.5px; color:#9CA3AF; text-transform:uppercase; letter-spacing:.8px; margin-top:5px; font-weight:600; }

/* ── RANGO DE VALOR ────────────────────────────────────────────────────────── */
.range-section { padding:7mm 14mm 6mm; background:#fff; border-bottom:1px solid #E8ECF2; }
.range-title { font-size:11px; font-weight:700; color:#2563A0; text-transform:uppercase; letter-spacing:2px; margin-bottom:8px; padding-bottom:5px; border-bottom:1px solid #E8ECF2; }
.range-row   { display:flex; align-items:center; gap:10px; margin-bottom:7px; }
.range-row:last-child { margin-bottom:0; }
.range-lbl   { font-size:11px; color:#5A6A7A; font-weight:600; width:90px; flex-shrink:0; }
.range-track { flex:1; height:7px; background:#EFF3F8; border-radius:1px; }
.range-fill  { height:7px; border-radius:1px; background:#2563A0; }
.range-fill-dark { background:#0C1A2E; }
.range-val   { font-size:12px; font-weight:600; color:#0C1A2E; width:95px; text-align:right; flex-shrink:0; }
.range-val-accent { color:#2563A0; font-size:13px; font-weight:700; }
.range-divider { border:none; border-top:1px dashed #CBD5E1; margin:6px 0; }

/* ── DOS COLUMNAS: INMUEBLE + AJUSTES ──────────────────────────────────────── */
.two-col { display:flex; background:#fff; }
.col-left  { flex:1; padding:7mm 7mm 7mm 14mm; border-right:1px solid #E8ECF2; }
.col-right { width:90mm; padding:7mm 14mm 7mm 7mm; }

.sec-label { font-size:11px; font-weight:700; color:#2563A0; text-transform:uppercase; letter-spacing:2px; padding-bottom:5px; border-bottom:1px solid #E8ECF2; margin-bottom:9px; }

/* Specs grid */
.spec-grid { width:100%; border-collapse:collapse; }
.spec-grid td { padding:4px 0; border-bottom:1px solid #F4F6F8; vertical-align:middle; font-size:12px; }
.spec-grid td:first-child { color:#5A6A7A; font-size:9.5px; text-transform:uppercase; letter-spacing:.4px; font-weight:700; width:50%; }
.spec-grid td:last-child  { font-weight:700; color:#0C1A2E; text-align:right; }

/* Compact waterfall */
.wf-compact { width:100%; border-collapse:collapse; }
.wf-compact td { padding:4px 0; border-bottom:1px solid #F4F6F8; vertical-align:middle; font-size:12px; }
.wf-compact td:first-child { color:#5A6A7A; font-size:11px; width:55%; }
.wf-compact td:last-child  { font-weight:700; color:#0C1A2E; text-align:right; }
.wf-base-row td { font-weight:700; background:#F4F6F8; padding:4px 5px; }
.wf-total-row td { font-weight:700; background:#EFF6FF; padding:5px 5px; color:#1E3A8A; border-top:1.5px solid #BFDBFE; border-bottom:none; }
.pct-pos { color:#15803D; }
.pct-neg { color:#DC2626; }

/* ── FOOTER STRIP PG1 ──────────────────────────────────────────────────────── */
.pg1-footer {
    position:absolute; bottom:0; left:0; right:0;
    background:#0C1A2E; padding:5px 14mm;
    display:flex; justify-content:space-between; align-items:center;
}
.pg1-footer-left  { font-size:9px; color:rgba(255,255,255,.35); text-transform:uppercase; letter-spacing:2px; }
.pg1-footer-right { font-size:9px; color:rgba(255,255,255,.35); }

/* ═══ PAGE 2 ══════════════════════════════════════════════════════════════════ */
.pg2 { width:100%; height:297mm; position:relative; overflow:hidden; }
.pg2-body { padding:10mm 14mm 0; }

/* ── MINI HEADER PG2 ───────────────────────────────────────────────────────── */
.mini-header {
    display:flex; justify-content:space-between; align-items:center;
    padding-bottom:7px; border-bottom:2px solid #2563A0; margin-bottom:10px;
}
.mini-logo-box { background:#0C1A2E; padding:4px 7px; border-radius:2px; display:inline-flex; align-items:center; }
.mini-logo { height:18px; width:auto; display:block; }
.mini-brand { display:flex; align-items:center; gap:8px; }
.mini-brand-name { font-size:11px; font-weight:700; color:#0C1A2E; }
.mini-brand-sub  { font-size:8.5px; color:#9CA3AF; text-transform:uppercase; letter-spacing:1px; }
.mini-right { text-align:right; }
.mini-folio   { font-size:9px; color:#9CA3AF; text-transform:uppercase; letter-spacing:.8px; }
.mini-section { font-size:10px; color:#2563A0; font-weight:700; text-transform:uppercase; letter-spacing:1px; margin-top:2px; }

/* ── WATERFALL DETALLADO ───────────────────────────────────────────────────── */
.wf-table { width:100%; border-collapse:collapse; font-size:12px; }
.wf-table th {
    background:#F4F6F8; padding:5px 8px;
    font-size:9px; text-transform:uppercase; letter-spacing:1px; color:#9CA3AF;
    border-bottom:1px solid #E8ECF2; font-weight:700; text-align:left;
}
.wf-table th:nth-child(3),.wf-table th:nth-child(4),.wf-table th:nth-child(5) { text-align:right; }
.wf-table td { padding:5px 8px; border-bottom:1px solid #F4F6F8; vertical-align:middle; }
.wf-table td:nth-child(3),.wf-table td:nth-child(4),.wf-table td:nth-child(5) { text-align:right; font-weight:700; }
.wf-table .row-base  td { background:#F8FAFC; font-weight:700; }
.wf-table .row-total td { background:#EFF6FF; font-weight:700; border-top:1.5px solid #BFDBFE; }
.wf-bar-wrap { width:70px; }
.wf-bar { height:5px; border-radius:2px; }
.wf-bar-pos  { background:#10B981; }
.wf-bar-neg  { background:#EF4444; }
.wf-bar-zero { background:#E5E7EB; width:100%!important; }
.wf-sub { font-size:10px; color:#9CA3AF; margin-top:2px; }

/* ── AI NARRATIVE ──────────────────────────────────────────────────────────── */
.narr-body { font-size:13px; color:#374151; line-height:1.75; }
.narr-two  { display:flex; gap:8px; margin-top:9px; }
.narr-box  { flex:1; padding:10px 12px; font-size:12px; line-height:1.7; }
.narr-box-green { background:#F0FDF4; border:1px solid #BBF7D0; color:#14532D; }
.narr-box-red   { background:#FEF2F2; border:1px solid #FECACA; color:#7F1D1D; }
.narr-box-blue  { background:#EFF6FF; border:1px solid #BFDBFE; color:#1E3A8A; font-size:12.5px; margin-top:9px; padding:11px 13px; }
.narr-eyebrow { font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:1px; margin-bottom:5px; }
.narr-eyebrow-green { color:#15803D; }
.narr-eyebrow-red   { color:#DC2626; }
.narr-eyebrow-blue  { color:#2563A0; }

/* ── FACTORES CLAVE ────────────────────────────────────────────────────────── */
.pill { display:inline-block; background:#EFF6FF; border:1px solid #BFDBFE; padding:3px 11px; font-size:10px; color:#1E3A8A; font-weight:600; margin:2px 3px 2px 0; border-radius:2px; }

/* ── CARACTERÍSTICAS ───────────────────────────────────────────────────────── */
.char-row { padding:4.5px 0; border-bottom:1px solid #F4F6F8; display:flex; justify-content:space-between; align-items:center; font-size:12px; }
.char-row:last-child { border-bottom:none; }
.char-k { color:#9CA3AF; font-size:10px; text-transform:uppercase; letter-spacing:.4px; font-weight:700; }
.char-v { font-weight:700; color:#0C1A2E; }

/* ── CONTACTO ──────────────────────────────────────────────────────────────── */
.contact-row { display:flex; gap:8mm; margin-top:10px; padding-top:10px; border-top:1px solid #E8ECF2; }
.contact-item { display:flex; flex-direction:column; gap:2px; }
.contact-k { font-size:9px; color:#9CA3AF; text-transform:uppercase; letter-spacing:.8px; font-weight:700; }
.contact-v { font-size:12px; color:#0C1A2E; font-weight:600; }

/* ── FOOTER PG2 ────────────────────────────────────────────────────────────── */
.pg2-footer {
    position:absolute; bottom:0; left:0; right:0;
    background:#0C1A2E; border-top:3px solid #2563A0;
    padding:7px 14mm;
}
.footer-brand { font-size:9.5px; color:rgba(255,255,255,.75); font-weight:700; text-transform:uppercase; letter-spacing:2px; margin-bottom:3px; }
.footer-text  { font-size:8.5px; color:rgba(255,255,255,.35); line-height:1.65; }
.footer-copy  { font-size:8px; color:rgba(255,255,255,.22); margin-top:4px; text-align:center; }

/* ── RULE ───────────────────────────────────────────────────────────────────── */
.rule { border:none; border-top:1px solid #E8ECF2; margin:9px 0; }

@media print {
    * { -webkit-print-color-adjust:exact!important; print-color-adjust:exact!important; }
}
</style>
</head>
<body>

{{-- ═══════════════════════════════════════════════════════════════════════════
     PÁGINA 1  ·  PORTADA
     ═══════════════════════════════════════════════════════════════════════════ --}}
<div class="pg1">

    {{-- ── CABECERA ─────────────────────────────────────────────────────────── --}}
    <div class="header">
        <div class="header-inner">
            <div class="header-brand">
                @if($logoSrcLight)
                    {{-- Dark logo available — show directly on white bg --}}
                    <img src="{{ $logoSrcLight }}" class="header-logo" style="height:32px;width:auto;display:block;" alt="{{ $siteName }}">
                @elseif($logoSrc)
                    {{-- White logo — wrap in navy box so it's visible --}}
                    <div class="logo-box">
                        <img src="{{ $logoSrc }}" class="header-logo" alt="{{ $siteName }}">
                    </div>
                @else
                    <div class="logo-box" style="padding:8px 12px;">
                        <span style="font-size:16px;font-weight:800;color:#fff;letter-spacing:0.5px;">HOME DEL VALLE</span>
                    </div>
                @endif
                <div class="header-tagline">Inmobiliaria Boutique<br>Benito Juárez, CDMX</div>
            </div>
            <div class="header-center">
                <div class="header-doc-type">Documento Técnico</div>
                <div class="header-title">Opinión de Valor Inmobiliario</div>
                <div class="header-subtitle">{{ $address }}</div>
            </div>
            <div class="header-meta">
                <div class="header-folio-label">Folio</div>
                <div class="header-folio">{{ $folio }}</div>
                <div class="header-date">{{ $today }}</div>
            </div>
        </div>
    </div>

    {{-- ── HERO PRECIO ───────────────────────────────────────────────────────── --}}
    @if($sug)
    <div class="price-hero">
        <div class="price-left">
            <div class="price-eyebrow">Precio sugerido de salida</div>
            <div class="price-amount">
                <span class="price-currency">$</span>{{ number_format($sug) }}
                <span style="font-size:10px;color:rgba(255,255,255,.3);font-family:'Inter',sans-serif;font-weight:400;letter-spacing:1px;margin-left:4px;vertical-align:middle;"> MXN</span>
            </div>
            <div class="price-sub">
                ${{ number_format($valuation->adjusted_price_m2) }}/m²&nbsp;·&nbsp;{{ number_format($valuation->effective_m2, 0) }} m² efectivos
                &nbsp;·&nbsp; {{ $colonia }}, {{ $zone }}
            </div>
        </div>
        <div class="price-right">
            <div class="diag-badge">{{ $diagLabel }}</div>
            <div class="price-m2-box">
                <div class="price-m2-val">${{ number_format($valuation->adjusted_price_m2) }}/m²</div>
                <div class="price-m2-lbl">Precio ajustado por m²</div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── STATS BAR ─────────────────────────────────────────────────────────── --}}
    <div class="stats-bar">
        <div class="stat-cell">
            <span class="stat-v stat-v-sm">${{ number_format($valuation->base_price_m2) }}</span>
            <span class="stat-l">Base m² · {{ $colonia }}</span>
        </div>
        <div class="stat-cell">
            <span class="stat-v stat-v-sm">
                {{ ($adjTotal >= 0 ? '+' : '') . $adjTotal }}%
            </span>
            <span class="stat-l">Ajuste total aplicado</span>
        </div>
        <div class="stat-cell">
            <span class="stat-v">{{ number_format($valuation->effective_m2, 0) }}</span>
            <span class="stat-l">m² efectivos</span>
        </div>
        <div class="stat-cell">
            <span class="stat-v stat-v-sm">{{ $typeLabel }}</span>
            <span class="stat-l">Tipo de inmueble</span>
        </div>
        <div class="stat-cell">
            <span class="stat-v stat-v-sm">{{ $confidLabel }}</span>
            <span class="stat-l">Confianza del modelo</span>
        </div>
    </div>

    {{-- ── RANGO DE VALOR ────────────────────────────────────────────────────── --}}
    @if($low && $high)
    <div class="range-section">
        <div class="range-title">Rango de Valor Estimado</div>
        <div class="range-row">
            <div class="range-lbl">Valor mínimo</div>
            <div class="range-track">
                <div class="range-fill" style="width:{{ $pLow }}%;opacity:.5;"></div>
            </div>
            <div class="range-val">${{ number_format($low) }}</div>
        </div>
        <div class="range-row">
            <div class="range-lbl">Valor medio</div>
            <div class="range-track">
                <div class="range-fill" style="width:{{ $pMid }}%;opacity:.75;"></div>
            </div>
            <div class="range-val">${{ number_format($mid) }}</div>
        </div>
        <div class="range-row">
            <div class="range-lbl">Valor máximo</div>
            <div class="range-track">
                <div class="range-fill" style="width:{{ $pHigh }}%;"></div>
            </div>
            <div class="range-val">${{ number_format($high) }}</div>
        </div>
        <hr class="range-divider">
        <div class="range-row">
            <div class="range-lbl" style="color:#0C1A2E;font-weight:700;">Precio sugerido</div>
            <div class="range-track">
                <div class="range-fill range-fill-dark" style="width:{{ $pSug }}%;"></div>
            </div>
            <div class="range-val range-val-accent">${{ number_format($sug) }}</div>
        </div>
    </div>
    @endif

    {{-- ── DOS COLUMNAS ─────────────────────────────────────────────────────── --}}
    <div class="two-col">

        {{-- Columna izquierda: características --}}
        <div class="col-left">
            <div class="sec-label">Características del Inmueble</div>
            <table class="spec-grid">
                <tr><td>Colonia</td><td>{{ $colonia }}</td></tr>
                <tr><td>Zona estratégica</td><td>{{ $zone }}, Benito Juárez</td></tr>
                <tr><td>Tipo</td><td>{{ $typeLabel }}</td></tr>
                <tr><td>Antigüedad</td><td>{{ $valuation->input_age_years }} años &nbsp;·&nbsp; {{ $ageLabel }}</td></tr>
                <tr><td>Estado conservación</td><td>{{ $condLabel }}</td></tr>
                @if($valuation->input_m2_total)
                <tr><td>m² totales</td><td>{{ number_format($valuation->input_m2_total, 1) }} m²</td></tr>
                @endif
                @if($valuation->input_m2_const)
                <tr><td>m² construcción</td><td>{{ number_format($valuation->input_m2_const, 1) }} m²</td></tr>
                @endif
                <tr><td>Recámaras</td><td>{{ $valuation->input_bedrooms ?? '—' }}</td></tr>
                <tr><td>Baños</td><td>{{ $valuation->input_bathrooms ?? '—' }}</td></tr>
                <tr><td>Estacionamientos</td><td>{{ $valuation->input_parking ?? 0 }} cajón(es)</td></tr>
                @if($valuation->input_floor)
                <tr><td>Piso</td><td>Piso {{ $valuation->input_floor }}</td></tr>
                @endif
                <tr><td>Elevador</td><td>{{ $valuation->input_has_elevator ? 'Sí' : 'No' }}</td></tr>
                @php
                $amenList = implode(', ', array_filter([
                    $valuation->input_has_rooftop      ? 'Rooftop'         : null,
                    $valuation->input_has_balcony       ? 'Balcón'          : null,
                    $valuation->input_has_service_room  ? 'Cuarto servicio' : null,
                    $valuation->input_has_storage       ? 'Bodega'          : null,
                ]));
                @endphp
                @if($amenList)
                <tr><td>Amenidades</td><td>{{ $amenList }}</td></tr>
                @endif
            </table>
        </div>

        {{-- Columna derecha: resumen de ajustes --}}
        <div class="col-right">
            <div class="sec-label">Resumen de Ajustes</div>
            <table class="wf-compact">
                <tr class="wf-base-row">
                    <td>Precio base de zona</td>
                    <td>${{ number_format($valuation->base_price_m2) }}/m²</td>
                </tr>
                @foreach($valuation->adjustments as $adj)
                @php $v = (float)$adj->adjustment_value; @endphp
                <tr>
                    <td style="color:#374151;">{{ $adj->factor_label }}</td>
                    <td class="{{ $v > 0 ? 'pct-pos' : ($v < 0 ? 'pct-neg' : '') }}">
                        {{ $adj->formatted_value }}
                    </td>
                </tr>
                @endforeach
                <tr class="wf-total-row">
                    <td>Precio ajustado m²</td>
                    <td>${{ number_format($valuation->adjusted_price_m2) }}/m²</td>
                </tr>
            </table>

            @if($sug)
            <div style="background:#F0F4F8;border:1px solid #CBD5E1;padding:8px 10px;margin-top:10px;">
                <div style="font-size:9px;color:#5A6A7A;text-transform:uppercase;letter-spacing:1px;font-weight:700;margin-bottom:4px;">Valor total estimado</div>
                <div style="font-family:'Playfair Display',Georgia,serif;font-size:20px;font-weight:700;color:#0C1A2E;">${{ number_format($sug) }}</div>
                <div style="font-size:10px;color:#5A6A7A;margin-top:2px;">
                    Rango: ${{ number_format($low) }} — ${{ number_format($high) }}
                </div>
            </div>
            @endif

            <div style="margin-top:10px;padding:7px 9px;background:#FFFBEB;border:1px solid #FDE68A;font-size:9px;color:#78350F;line-height:1.6;">
                <strong>Validez:</strong> 90 días · Vence {{ $validity }}<br>
                <strong>Nota:</strong> No constituye avalúo formal con efectos fiscales o notariales (INDAABIN/AMPI).
            </div>
        </div>
    </div>

    {{-- ── FOOTER STRIP ─────────────────────────────────────────────────────── --}}
    <div class="pg1-footer">
        <div class="pg1-footer-left">{{ $siteUrl }}</div>
        <div class="pg1-footer-right">{{ $folio }} · {{ $today }}</div>
    </div>

</div>{{-- /pg1 --}}


{{-- ═══════════════════════════════════════════════════════════════════════════
     PÁGINA 2  ·  ANÁLISIS DETALLADO
     ═══════════════════════════════════════════════════════════════════════════ --}}
<div class="pg2">
<div class="pg2-body">

    {{-- ── MINI HEADER ──────────────────────────────────────────────────────── --}}
    <div class="mini-header">
        <div class="mini-brand">
            @if($logoSrcLight)
                <img src="{{ $logoSrcLight }}" class="mini-logo" style="height:18px;width:auto;" alt="{{ $siteName }}">
            @elseif($logoSrc)
                <div class="mini-logo-box">
                    <img src="{{ $logoSrc }}" class="mini-logo" alt="{{ $siteName }}">
                </div>
            @endif
            <div>
                <div class="mini-brand-name">HOME DEL VALLE</div>
                <div class="mini-brand-sub">Opinión de Valor Inmobiliario</div>
            </div>
        </div>
        <div class="mini-right">
            <div class="mini-folio">{{ $folio }}</div>
            <div class="mini-section">Análisis Detallado de Ajustes</div>
        </div>
    </div>

    {{-- ── WATERFALL DETALLADO ──────────────────────────────────────────────── --}}
    @if($valuation->adjustments->isNotEmpty())
    <div class="sec-label">Factores de Ajuste Aplicados · Metodología Waterfall</div>
    <table class="wf-table">
        <thead>
            <tr>
                <th style="width:35%;">Factor</th>
                <th>Impacto visual</th>
                <th style="width:60px;">Ajuste</th>
                <th style="width:85px;">Antes</th>
                <th style="width:85px;">Después</th>
            </tr>
        </thead>
        <tbody>
            <tr class="row-base">
                <td>
                    Precio base de zona · {{ $colonia }}
                    @if($valuation->snapshot)
                    <div class="wf-sub">{{ $valuation->snapshot->age_label }} · Confianza {{ $confidLabel }}</div>
                    @endif
                </td>
                <td><div class="wf-bar wf-bar-zero"></div></td>
                <td class="pct-zero" style="color:#9CA3AF;">—</td>
                <td style="color:#9CA3AF;">—</td>
                <td>${{ number_format($valuation->base_price_m2) }}/m²</td>
            </tr>
            @php $maxAbs = $valuation->adjustments->max(fn($a) => abs($a->adjustment_value)) ?: 1; @endphp
            @foreach($valuation->adjustments as $adj)
            @php
                $v      = (float)$adj->adjustment_value;
                $isNeu  = $adj->is_neutral;
                $isPos  = $adj->is_positive;
                $bw     = $isNeu ? 100 : min(100, round(abs($v) / $maxAbs * 100));
                $bCls   = $isNeu ? 'wf-bar-zero' : ($isPos ? 'wf-bar-pos' : 'wf-bar-neg');
                $tCls   = $isNeu ? 'pct-zero' : ($isPos ? 'pct-pos' : 'pct-neg');
            @endphp
            <tr>
                <td>
                    {{ $adj->factor_label }}
                    @if($adj->explanation)
                    <div class="wf-sub">{{ $adj->explanation }}</div>
                    @endif
                </td>
                <td class="wf-bar-wrap">
                    <div class="wf-bar {{ $bCls }}" style="width:{{ $bw }}%;"></div>
                </td>
                <td class="{{ $tCls }}">{{ $adj->formatted_value }}</td>
                <td style="color:#9CA3AF;font-size:10px;">${{ number_format($adj->price_before) }}/m²</td>
                <td>${{ number_format($adj->price_after) }}/m²</td>
            </tr>
            @endforeach
            @php $tPct = round((($valuation->adjusted_price_m2 - $valuation->base_price_m2) / $valuation->base_price_m2) * 100, 1); @endphp
            <tr class="row-total">
                <td style="font-weight:700;color:#1E3A8A;">Precio m² ajustado final</td>
                <td></td>
                <td class="{{ $tPct >= 0 ? 'pct-pos' : 'pct-neg' }}" style="font-size:12px;">{{ ($tPct >= 0 ? '+' : '') . $tPct }}%</td>
                <td></td>
                <td style="font-size:13px;color:#1E3A8A;">${{ number_format($valuation->adjusted_price_m2) }}/m²</td>
            </tr>
        </tbody>
    </table>
    <hr class="rule">
    @endif

    {{-- ── ANÁLISIS DE MERCADO IA ────────────────────────────────────────────── --}}
    @if(!empty($n['market_context']) || !empty($n['recommendation']))
    <div class="sec-label">Análisis Profesional de Mercado · Generado por IA</div>
    @if(!empty($n['market_context']))
    <p class="narr-body">{{ $n['market_context'] }}</p>
    @endif

    @if(!empty($n['property_strengths']) || !empty($n['property_risks']))
    <div class="narr-two">
        @if(!empty($n['property_strengths']))
        <div class="narr-box narr-box-green">
            <div class="narr-eyebrow narr-eyebrow-green">Fortalezas del inmueble</div>
            {{ $n['property_strengths'] }}
        </div>
        @endif
        @if(!empty($n['property_risks']))
        <div class="narr-box narr-box-red">
            <div class="narr-eyebrow narr-eyebrow-red">Riesgo principal</div>
            {{ $n['property_risks'] }}
        </div>
        @endif
    </div>
    @endif

    @if(!empty($n['recommendation']))
    <div class="narr-box narr-box-blue">
        <div class="narr-eyebrow narr-eyebrow-blue">Recomendación Comercial</div>
        {{ $n['recommendation'] }}
    </div>
    @endif

    @if(!empty($n['key_factors']) && is_array($n['key_factors']))
    <div style="margin-top:8px;">
        <div style="font-size:8px;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:#9CA3AF;margin-bottom:5px;">Factores clave identificados</div>
        @foreach($n['key_factors'] as $f)
            <span class="pill">{{ $f }}</span>
        @endforeach
    </div>
    @endif
    <hr class="rule">
    @endif

    {{-- ── RECOMENDACIÓN FALLBACK ────────────────────────────────────────────── --}}
    @if(empty($n['recommendation']) && $sug)
    <div class="sec-label">Recomendación Comercial</div>
    <div class="narr-box narr-box-blue">
        <div class="narr-eyebrow narr-eyebrow-blue">Estrategia de salida al mercado</div>
        @switch($valuation->diagnosis)
        @case('on_market')
            El inmueble está en línea con el mercado de {{ $colonia }}. El precio sugerido de ${{ number_format($sug) }} maximiza el balance entre rapidez de colocación y valor obtenido. Se recomienda iniciar con este precio y evaluar respuesta del mercado en las primeras tres semanas.
            @break
        @case('opportunity')
            El inmueble presenta características de oportunidad frente al mercado. Con un precio de salida de ${{ number_format($sug) }} se puede capturar el diferencial de mercado manteniendo alta probabilidad de cierre en el corto plazo.
            @break
        @case('above_market')
            El inmueble se posiciona por encima del promedio de mercado. Se recomienda iniciar en ${{ number_format($sug) }} con margen de negociación del 3–5% y ajustar estrategia a partir de la respuesta recibida en las primeras tres semanas.
            @break
        @default
            Se recomienda complementar este análisis con visitas comparativas en {{ $colonia }} para afinar el precio de salida al mercado.
        @endswitch
    </div>
    <hr class="rule">
    @endif

    {{-- ── CONTACTO ──────────────────────────────────────────────────────────── --}}
    <div class="contact-row">
        <div class="contact-item">
            <span class="contact-k">Inmobiliaria</span>
            <span class="contact-v">{{ $siteName }}</span>
        </div>
        @if($contactPhone)
        <div class="contact-item">
            <span class="contact-k">Teléfono</span>
            <span class="contact-v">{{ $contactPhone }}</span>
        </div>
        @endif
        @if($contactEmail)
        <div class="contact-item">
            <span class="contact-k">Correo</span>
            <span class="contact-v">{{ $contactEmail }}</span>
        </div>
        @endif
        <div class="contact-item">
            <span class="contact-k">Web</span>
            <span class="contact-v">{{ $siteUrl }}</span>
        </div>
        <div class="contact-item">
            <span class="contact-k">Validez</span>
            <span class="contact-v">90 días · Vence {{ $validity }}</span>
        </div>
    </div>

</div>{{-- /pg2-body --}}

{{-- ── FOOTER LEGAL ──────────────────────────────────────────────────────────── --}}
<div class="pg2-footer">
    <div class="footer-brand">{{ $siteName }} &mdash; {{ $siteUrl }}</div>
    <div class="footer-text">
        Esta Opinión de Valor es elaborada por {{ $siteName }} con base en datos de oferta publicada en portales inmobiliarios, referencias
        de mercado y los ajustes estadísticos descritos en este documento. <strong style="color:rgba(255,255,255,.5);">No constituye un avalúo
        formal</strong> con efectos fiscales, notariales o de crédito hipotecario; para dichos efectos se requiere la intervención de un
        valuador certificado (INDAABIN / SHF / AMPI). El valor real de cierre depende de las condiciones específicas de cada negociación.
    </div>
    <div class="footer-copy">&copy; {{ now()->year }} {{ $siteName }} · Todos los derechos reservados &nbsp;|&nbsp; {{ $siteUrl }} &nbsp;|&nbsp; {{ $folio }}</div>
</div>

</div>{{-- /pg2 --}}

</body>
</html>
