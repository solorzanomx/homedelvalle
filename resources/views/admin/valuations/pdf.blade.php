@php
// ─── Config ──────────────────────────────────────────────────────────────────
$siteName = 'Home del Valle';
$siteUrl  = 'www.homedelvalle.mx';
$folio    = 'OV-' . str_pad($valuation->id, 5, '0', STR_PAD_LEFT);
$today    = now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
$validity = now()->addDays(90)->locale('es')->isoFormat('D [de] MMMM [de] YYYY');

// ─── Logo ─────────────────────────────────────────────────────────────────────
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

// ─── Fonts ────────────────────────────────────────────────────────────────────
$fontsDir = resource_path('fonts');
$fontInter  = $fontsDir . '/inter-latin.woff2';
$b64Inter = file_exists($fontInter) ? base64_encode(file_get_contents($fontInter)) : null;

// ─── Contact ──────────────────────────────────────────────────────────────────
$contactPhone = $siteSetting?->contact_phone ?? $siteSetting?->whatsapp_number ?? '';
$contactEmail = $siteSetting?->contact_email ?? '';

// ─── Valuation data ───────────────────────────────────────────────────────────
$colonia    = $valuation->colonia?->name ?? $valuation->input_colonia_raw ?? '—';
$zone       = $valuation->colonia?->zone?->name ?? 'Benito Juárez';
$typeLabel  = $valuation->type_label;
$ageLabel   = match($valuation->age_category) {
    'new'  => 'Nuevo · 0–10 años',
    'mid'  => 'Seminuevo · 10–30 años',
    'old'  => 'Antiguo · +30 años',
    default => $valuation->age_category ?? '—',
};

// Dirección concreta: input_address > property->address > fallback descriptivo
$address = $valuation->input_address
    ?? ($valuation->property?->address
        ? ($valuation->property->address . ($valuation->property->city ? ', ' . $valuation->property->city : ''))
        : null);

// Lo que se muestra grande en el header (siempre hay algo)
$addressDisplay = $address ?? ($typeLabel . ' en ' . $colonia . ', ' . $zone . ', CDMX');
// Subtítulo solo cuando hay dirección real (para no duplicar info)
$addressSubline = $address ? ($typeLabel . '  ·  ' . $colonia . '  ·  ' . $zone . ', Benito Juárez, CDMX') : null;

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

$rangeMin  = $low  * 0.97;
$rangeMax  = $high * 1.03;
$rangeSpan = $rangeMax - $rangeMin;
$pLow  = $rangeSpan > 0 ? round(($low - $rangeMin) / $rangeSpan * 100) : 25;
$pMid  = $rangeSpan > 0 ? round(($mid - $rangeMin) / $rangeSpan * 100) : 55;
$pHigh = 96; // leave 4% margin so the last dot isn't clipped
$pSug  = $rangeSpan > 0 ? min(94, round(($sug - $rangeMin) / $rangeSpan * 100)) : 72;

$adjTotal  = $valuation->adjustments->isNotEmpty()
    ? round((($valuation->adjusted_price_m2 - $valuation->base_price_m2) / $valuation->base_price_m2) * 100, 1)
    : 0;
$condLabel   = $valuation->condition_label;
$confidLabel = ['high'=>'Alta','medium'=>'Media','low'=>'Baja'][$valuation->confidence] ?? '—';

// ─── Mapa estático ────────────────────────────────────────────────────────────
$mapKey    = config('services.google_maps.key');
// Mismo punto que se muestra en el header del PDF
$mapCenter = $address
    ? urlencode($address . ', Benito Juárez, Ciudad de México, Mexico')
    : urlencode($colonia . ', Benito Juárez, Ciudad de México, Mexico');
$mapZoom   = $address ? 16 : 15;
$mapUrl    = $mapKey
    ? "https://maps.googleapis.com/maps/api/staticmap?center={$mapCenter}&zoom={$mapZoom}&size=560x260&scale=2&maptype=roadmap"
      . "&style=feature:all|element:geometry|color:0xf2f2f2"
      . "&style=feature:road|element:geometry|color:0xffffff"
      . "&style=feature:road.arterial|element:geometry|color:0xe8e8e8"
      . "&style=feature:poi|visibility:off"
      . "&style=feature:transit|visibility:off"
      . "&style=feature:water|element:geometry|color:0xd0e4f7"
      . "&markers=color:0x2563A0|size:mid|{$mapCenter}"
      . "&key={$mapKey}"
    : null;

// ─── Consideraciones clave ────────────────────────────────────────────────────
$considerations = [];
$diagTexts = [
    'on_market'    => 'Precio alineado con el mercado de ' . $colonia . '. Alta competitividad desde el primer día de oferta.',
    'above_market' => 'El inmueble supera el promedio de zona. Considerar un margen de negociación del 3–5%.',
    'opportunity'  => 'Ventaja de precio frente al mercado activo. Alta probabilidad de cierre en el corto plazo.',
];
if (isset($diagTexts[$valuation->diagnosis])) $considerations[] = $diagTexts[$valuation->diagnosis];
if ($valuation->confidence === 'low')
    $considerations[] = 'Confianza estadística baja. Se recomienda validar con recorridos comparativos en la zona.';
elseif ($valuation->confidence === 'high')
    $considerations[] = 'Alta confianza en la muestra de mercado. Estimación sólida y respaldada.';
if ($adjTotal < -15)
    $considerations[] = 'Los ajustes negativos son significativos. El estado de conservación es el principal factor de descuento.';
elseif ($adjTotal > 10)
    $considerations[] = 'Atributos diferenciales elevan el valor por encima del precio base de la zona.';
$considerations[] = 'Vigencia 90 días — vence el ' . $validity . '. No sustituye avalúo formal (INDAABIN / SHF).';
$totalPages = $valuation->input_notes ? 4 : 3;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>{{ $folio }} — Opinión de Valor — {{ $colonia }}</title>
<style>
/* ── FONT ─────────────────────────────────────────────────────────────────── */
@if($b64Inter)
@font-face {
    font-family: 'Inter';
    font-style: normal;
    font-weight: 100 900;
    font-display: swap;
    src: url('data:font/woff2;base64,{{ $b64Inter }}') format('woff2');
}
@endif

/* ── RESET ────────────────────────────────────────────────────────────────── */
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
@page { size: A4 portrait; margin: 0; }
html, body {
    font-family: 'Inter', -apple-system, Arial, sans-serif;
    font-size: 13px;
    color: #111827;
    background: #fff;
    line-height: 1.55;
    -webkit-font-smoothing: antialiased;
}
@media print {
    * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
}

/* ═══════════════════════════════════════════════════════════════════════════
   PALETTE
   #0C1A2E  navy       — header/footer bg, primary titles
   #1D4ED8  blue       — accents, CTAs, highlights
   #EFF6FF  blue-50    — hero section bg
   #DBEAFE  blue-100   — hero section bg gradient
   #BFDBFE  blue-200   — borders, fills
   #F9FAFB  gray-50    — section alt backgrounds
   #E5E7EB  gray-200   — dividers, borders
   #111827  gray-900   — primary text
   #374151  gray-700   — secondary text
   #6B7280  gray-500   — muted text
   #9CA3AF  gray-400   — labels, placeholders
   #15803D  green      — positive values
   #DC2626  red        — negative values
═══════════════════════════════════════════════════════════════════════════ */

/* ── PAGE LAYOUT ──────────────────────────────────────────────────────────── */
.page {
    width: 100%;
    height: 297mm;
    display: grid;
    grid-template-rows: auto 1fr auto;
    background: #fff;
    overflow: hidden;
}
.page-break { break-after: page; page-break-after: always; }

/* ══════════════════════════════════════════════════════════════════════════
   PAGE 1 — EXECUTIVE COVER
   ══════════════════════════════════════════════════════════════════════════ */

/* ── P1 DARK HEADER BAND ──────────────────────────────────────────────────── */
.p1-header {
    background: #0C1A2E;
    padding: 0 48px;
    height: 82px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    flex-shrink: 0;
}

.p1-hd-logo { flex-shrink: 0; display: flex; flex-direction: column; gap: 5px; align-items: flex-start; }
.p1-hd-logo img { height: 32px; width: auto; max-width: 180px; display: block; object-fit: contain; }
.p1-hd-logo-txt {
    font-size: 15px;
    font-weight: 800;
    color: #fff;
    letter-spacing: -0.3px;
}
.p1-hd-logo-sub {
    font-size: 7.5px;
    text-transform: uppercase;
    letter-spacing: 2.5px;
    color: rgba(255,255,255,0.35);
    font-weight: 600;
}

.p1-hd-center { flex: 1; text-align: center; }
.p1-hd-eyebrow {
    font-size: 7.5px;
    text-transform: uppercase;
    letter-spacing: 3px;
    color: rgba(255,255,255,0.4);
    font-weight: 600;
    margin-bottom: 5px;
}
.p1-hd-title {
    font-size: 14px;
    font-weight: 800;
    color: #fff;
    letter-spacing: 3px;
    text-transform: uppercase;
    line-height: 1.2;
}
.p1-hd-subtitle {
    font-size: 11px;
    color: rgba(255,255,255,0.82);
    margin-top: 5px;
    letter-spacing: 0.2px;
    font-weight: 600;
}

.p1-hd-right { text-align: right; flex-shrink: 0; }
.p1-hd-folio-lbl {
    font-size: 7px;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: rgba(255,255,255,0.35);
    font-weight: 600;
    margin-bottom: 3px;
}
.p1-hd-folio {
    font-size: 13px;
    font-weight: 800;
    color: #fff;
    font-family: 'Courier New', monospace;
    font-feature-settings: "tnum";
    letter-spacing: 0.5px;
}
.p1-hd-date {
    font-size: 9px;
    color: rgba(255,255,255,0.35);
    margin-top: 4px;
}

/* ── P1 BODY ─────────────────────────────────────────────────────────────── */
.p1-body { overflow: hidden; display: flex; flex-direction: column; }

/* ── PROPERTY BAND ────────────────────────────────────────────────────────── */
.prop-band {
    background: #fff;
    border-bottom: 1px solid #E5E7EB;
    padding: 14px 48px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    flex-shrink: 0;
}
.prop-band-left { min-width: 0; flex: 1; }
.prop-address {
    font-size: 17px;
    font-weight: 800;
    color: #0C1A2E;
    letter-spacing: -0.4px;
    margin-bottom: 4px;
    line-height: 1.2;
}
.prop-address-sub {
    font-size: 10px;
    color: #6B7280;
    font-weight: 500;
    letter-spacing: 0.2px;
    margin-bottom: 6px;
    line-height: 1.4;
}
.prop-chips { display: flex; gap: 5px; flex-wrap: wrap; align-items: center; }
.prop-chip {
    font-size: 9.5px;
    font-weight: 600;
    color: #374151;
    background: #F9FAFB;
    border: 1px solid #E5E7EB;
    border-radius: 3px;
    padding: 3px 9px;
    white-space: nowrap;
}
.prop-chip-muted {
    font-size: 9.5px;
    font-weight: 500;
    color: #9CA3AF;
    background: transparent;
    border: 1px solid #F3F4F6;
    border-radius: 3px;
    padding: 3px 9px;
    white-space: nowrap;
}
.diag-badge {
    display: inline-flex;
    align-items: center;
    padding: 6px 14px;
    font-size: 8.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    border: 1px solid {{ $diagBorder }};
    background: {{ $diagBg }};
    color: {{ $diagColor }};
    border-radius: 3px;
    white-space: nowrap;
    flex-shrink: 0;
}

/* ── PRICE HERO ───────────────────────────────────────────────────────────── */
.price-hero {
    background: linear-gradient(135deg, #EFF6FF, #DBEAFE 60%, #EFF6FF);
    padding: 22px 48px 18px;
    border-bottom: 1px solid #BFDBFE;
    display: flex;
    align-items: stretch;
    justify-content: space-between;
    gap: 32px;
    flex-shrink: 0;
}
.price-hero-left { flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: center; }
.price-eyebrow {
    font-size: 8px;
    text-transform: uppercase;
    letter-spacing: 4px;
    color: #1D4ED8;
    font-weight: 700;
    margin-bottom: 8px;
}
.price-figure {
    display: flex;
    align-items: flex-start;
    gap: 0;
    line-height: 1;
    margin-bottom: 8px;
}
.price-dollar {
    font-size: 26px;
    font-weight: 400;
    color: #6B7280;
    margin-top: 6px;
    margin-right: 2px;
    font-feature-settings: "tnum";
}
.price-amount {
    font-size: 52px;
    font-weight: 900;
    color: #0C1A2E;
    letter-spacing: -3px;
    font-feature-settings: "tnum";
    line-height: 1;
}
.price-mxn-tag {
    font-size: 10px;
    font-weight: 700;
    color: #9CA3AF;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    margin-top: 8px;
    margin-left: 6px;
    align-self: flex-end;
    padding-bottom: 6px;
}
.price-meta {
    font-size: 11.5px;
    color: #6B7280;
    line-height: 1.5;
}
.price-meta strong { color: #0C1A2E; font-weight: 700; }

/* ── KPI 2×2 GRID ─────────────────────────────────────────────────────────── */
.kpi-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-template-rows: 1fr 1fr;
    border: 1px solid #BFDBFE;
    border-radius: 6px;
    overflow: hidden;
    flex-shrink: 0;
    width: 248px;
    background: rgba(255,255,255,0.7);
}
.kpi-cell {
    padding: 12px 14px;
    border-right: 1px solid #BFDBFE;
    border-bottom: 1px solid #BFDBFE;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.kpi-cell:nth-child(2n) { border-right: none; }
.kpi-cell:nth-child(3),
.kpi-cell:nth-child(4) { border-bottom: none; }
.kpi-cell:first-child { border-left: 3px solid #1D4ED8; }
.kpi-label {
    font-size: 7px;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    color: #6B7280;
    font-weight: 600;
    margin-bottom: 4px;
}
.kpi-value {
    font-size: 15px;
    font-weight: 800;
    color: #0C1A2E;
    letter-spacing: -0.5px;
    font-feature-settings: "tnum";
    line-height: 1.1;
}
.kpi-value-pos { color: #15803D; }
.kpi-value-neg { color: #DC2626; }

/* ── RANGE VISUALIZATION ──────────────────────────────────────────────────── */
.range-section {
    padding: 18px 48px 14px;
    border-bottom: 1px solid #E5E7EB;
    flex-shrink: 0;
    background: #fff;
}
.range-eyebrow {
    font-size: 8px;
    text-transform: uppercase;
    letter-spacing: 4px;
    color: #1D4ED8;
    font-weight: 700;
    margin-bottom: 18px;
}
.rv-outer {
    position: relative;
    margin: 0 10px;
}
.rv-track {
    position: relative;
    height: 8px;
    background: #E5E7EB;
    border-radius: 4px;
    margin: 30px 0 34px;
}
.rv-fill {
    position: absolute;
    top: 0;
    height: 8px;
    background: linear-gradient(90deg, #BFDBFE 0%, #1D4ED8 100%);
    border-radius: 4px;
    opacity: 0.45;
}
.rv-dot {
    position: absolute;
    top: 50%;
    transform: translate(-50%, -50%);
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #fff;
    border: 2.5px solid #9CA3AF;
    z-index: 2;
}
.rv-dot-accent {
    width: 17px;
    height: 17px;
    background: #1D4ED8;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px #1D4ED8;
    z-index: 3;
}
.rv-lbl {
    position: absolute;
    bottom: calc(100% + 10px);
    transform: translateX(-50%);
    font-size: 7.5px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #9CA3AF;
    font-weight: 600;
    white-space: nowrap;
}
.rv-lbl-accent {
    color: #1D4ED8;
    font-weight: 800;
    font-size: 8px;
    letter-spacing: 1.5px;
}
.rv-price {
    position: absolute;
    top: calc(100% + 11px);
    transform: translateX(-50%);
    font-size: 11px;
    font-weight: 700;
    color: #374151;
    white-space: nowrap;
    font-feature-settings: "tnum";
}
.rv-price-accent {
    font-size: 12.5px;
    font-weight: 800;
    color: #1D4ED8;
}

/* ── ZONE SPLIT ───────────────────────────────────────────────────────────── */
.zone-split {
    display: flex;
    flex: 1;
    min-height: 0;
    overflow: hidden;
}
.zone-map-col {
    flex: 0 0 58%;
    border-right: 1px solid #E5E7EB;
    overflow: hidden;
    position: relative;
    display: flex;
    align-items: stretch;
}
.zone-map-col img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.zone-placeholder {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: #F9FAFB;
    background-image:
        linear-gradient(rgba(29,78,216,0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(29,78,216,0.04) 1px, transparent 1px);
    background-size: 22px 22px;
    padding: 24px;
    text-align: center;
    gap: 0;
}
.zone-ph-pin {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: #1D4ED8;
    margin: 0 auto 12px;
    box-shadow: 0 0 0 6px rgba(29,78,216,0.1), 0 0 0 12px rgba(29,78,216,0.05);
}
.zone-ph-name {
    font-size: 16px;
    font-weight: 800;
    color: #0C1A2E;
    margin-bottom: 5px;
    letter-spacing: -0.3px;
}
.zone-ph-sub {
    font-size: 9px;
    color: #9CA3AF;
    text-transform: uppercase;
    letter-spacing: 1.5px;
}

.zone-notes-col {
    flex: 0 0 42%;
    padding: 20px 26px;
    background: #fff;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    overflow: hidden;
}
.zone-notes-header {
    font-size: 8px;
    text-transform: uppercase;
    letter-spacing: 3px;
    color: #1D4ED8;
    font-weight: 700;
    padding-bottom: 10px;
    border-bottom: 1px solid #E5E7EB;
    margin-bottom: 14px;
}
.zone-note-item {
    display: flex;
    gap: 11px;
    align-items: flex-start;
    margin-bottom: 11px;
}
.zone-note-item:last-child { margin-bottom: 0; }
.zone-note-num {
    flex-shrink: 0;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #EFF6FF;
    border: 1px solid #BFDBFE;
    color: #1D4ED8;
    font-size: 8.5px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 1px;
    flex: none;
}
.zone-note-text {
    font-size: 11px;
    color: #374151;
    line-height: 1.65;
}

/* ── PAGE 1 FOOTER ────────────────────────────────────────────────────────── */
.p1-footer {
    background: #0C1A2E;
    height: 32px;
    padding: 0 48px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-shrink: 0;
}
.p1-ft-l { font-size: 8px; color: rgba(255,255,255,0.28); text-transform: uppercase; letter-spacing: 2px; }
.p1-ft-c { font-size: 8px; color: rgba(255,255,255,0.5); font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; }
.p1-ft-r { font-size: 8px; color: rgba(255,255,255,0.28); text-align: right; }

/* ══════════════════════════════════════════════════════════════════════════
   PAGES 2 & 3 — SHARED ELEMENTS
   ══════════════════════════════════════════════════════════════════════════ */

/* ── MINI HEADER ──────────────────────────────────────────────────────────── */
.mhd {
    background: #fff;
    padding: 11px 48px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 2px solid #1D4ED8;
    flex-shrink: 0;
}
.mhd-logo { display: flex; align-items: center; }
.mhd-logo img { height: 22px; width: auto; max-width: 140px; display: block; object-fit: contain; }
.mhd-logo-txt { font-size: 12px; font-weight: 800; color: #0C1A2E; letter-spacing: -0.3px; }
.mhd-right { text-align: right; }
.mhd-folio {
    font-size: 8px;
    color: #9CA3AF;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 2px;
}
.mhd-section {
    font-size: 10px;
    font-weight: 700;
    color: #1D4ED8;
    text-transform: uppercase;
    letter-spacing: 1.5px;
}

/* ── SECTION LABEL ────────────────────────────────────────────────────────── */
.sec-lbl {
    font-size: 8px;
    text-transform: uppercase;
    letter-spacing: 3.5px;
    color: #1D4ED8;
    font-weight: 700;
    margin-bottom: 10px;
}

/* ── DARK FOOTER (pages 2 & 3) ───────────────────────────────────────────── */
.dark-footer {
    background: #0C1A2E;
    padding: 7px 48px;
    border-top: 2px solid #1D4ED8;
    flex-shrink: 0;
}
.dark-footer-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
}
.dark-footer-brand {
    font-size: 8px;
    color: rgba(255,255,255,0.5);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 2px;
}
.dark-footer-page {
    font-size: 8px;
    color: rgba(255,255,255,0.28);
}
.dark-footer-legal {
    font-size: 7px;
    color: rgba(255,255,255,0.2);
    line-height: 1.65;
}

/* ══════════════════════════════════════════════════════════════════════════
   PAGE 2 — TECHNICAL ANALYSIS
   ══════════════════════════════════════════════════════════════════════════ */
.p2-body { padding: 18px 48px 14px; overflow: hidden; }

/* ── CHARACTERISTICS DATA GRID ────────────────────────────────────────────── */
.chars-grid {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #E5E7EB;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 10px;
}
.chars-grid td {
    padding: 7px 12px;
    border-right: 1px solid #E5E7EB;
    border-bottom: 1px solid #E5E7EB;
    vertical-align: top;
    width: 25%;
}
.chars-grid tr:last-child td { border-bottom: none; }
.chars-grid td:last-child { border-right: none; }
.char-label {
    display: block;
    font-size: 7.5px;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    color: #9CA3AF;
    font-weight: 600;
    margin-bottom: 3px;
}
.char-value {
    display: block;
    font-size: 12.5px;
    font-weight: 700;
    color: #0C1A2E;
    line-height: 1.3;
}

/* ── TAGS ROW ─────────────────────────────────────────────────────────────── */
.tags-row {
    margin-bottom: 14px;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
}
.tags-group-lbl {
    font-size: 7.5px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #9CA3AF;
    font-weight: 700;
    margin-right: 2px;
}
.tag-pill {
    display: inline-block;
    background: #EFF6FF;
    border: 1px solid #BFDBFE;
    padding: 2px 9px;
    font-size: 9.5px;
    color: #1D4ED8;
    font-weight: 600;
    border-radius: 20px;
}

/* ── WATERFALL TABLE ──────────────────────────────────────────────────────── */
.wf {
    width: 100%;
    border-collapse: collapse;
    font-size: 11px;
}
.wf thead th {
    background: #0C1A2E;
    color: rgba(255,255,255,0.55);
    padding: 7px 10px;
    font-size: 7.5px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    font-weight: 700;
    text-align: left;
}
.wf thead th:nth-child(n+3) { text-align: right; }
.wf tbody td {
    padding: 5px 10px;
    border-bottom: 1px solid #F3F4F6;
    vertical-align: middle;
}
.wf tbody td:nth-child(n+3) { text-align: right; font-weight: 700; }
.wf .r-base td { background: #F9FAFB; }
.wf .r-base td:first-child { font-weight: 700; color: #374151; }
.wf .r-total td {
    background: #1D4ED8;
    color: #fff;
    font-weight: 800;
    border-top: none;
    border-bottom: none;
}
.wf .r-total td:first-child { color: #fff; }
.wf-factor-name { font-weight: 600; color: #111827; font-size: 11px; }
.wf-factor-sub { font-size: 8.5px; color: #9CA3AF; margin-top: 1px; line-height: 1.4; }
.wf-bar-wrap { width: 108px; }
.wf-bar-bg {
    background: #F3F4F6;
    height: 7px;
    border-radius: 3px;
    overflow: hidden;
}
.wf-bar-inner {
    height: 7px;
    border-radius: 3px;
    min-width: 3px;
}
.wf-bar-pos { background: linear-gradient(90deg, #86EFAC, #15803D); }
.wf-bar-neg { background: linear-gradient(90deg, #FCA5A5, #DC2626); }
.wf-bar-neu { background: #E5E7EB; }
.wf-sub { font-size: 9px; color: #9CA3AF; margin-top: 2px; }
.pct-pos { color: #15803D; }
.pct-neg { color: #DC2626; }
.price-muted { color: #9CA3AF; font-size: 10px; font-weight: 500; }

/* ══════════════════════════════════════════════════════════════════════════
   PAGE 3 — MARKET ANALYSIS
   ══════════════════════════════════════════════════════════════════════════ */
.p3-body { padding: 20px 48px 16px; overflow: hidden; }

.narr-lead {
    font-size: 13.5px;
    color: #374151;
    line-height: 1.8;
    margin-bottom: 16px;
}

.str-risk-row { display: flex; gap: 14px; margin-bottom: 16px; }
.sr-card {
    flex: 1;
    padding: 13px 16px;
    background: #F9FAFB;
    border-top: 3px solid;
    border-radius: 0 0 3px 3px;
}
.sr-card-pos { border-top-color: #1D4ED8; }
.sr-card-neg { border-top-color: #9CA3AF; }
.sr-eyebrow {
    font-size: 8px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 7px;
}
.sr-eyebrow-pos { color: #1D4ED8; }
.sr-eyebrow-neg { color: #6B7280; }
.sr-text { font-size: 12px; color: #374151; line-height: 1.75; }

.rec-box {
    padding: 16px 20px;
    background: #EFF6FF;
    border: 1px solid #BFDBFE;
    border-left: 4px solid #1D4ED8;
    margin-bottom: 16px;
    border-radius: 0 3px 3px 0;
}
.rec-eyebrow {
    font-size: 8px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: #1D4ED8;
    margin-bottom: 7px;
}
.rec-text {
    font-size: 13px;
    color: #1E3A8A;
    line-height: 1.8;
    font-weight: 500;
}

.key-factors-wrap { margin-bottom: 16px; }
.key-factors-lbl {
    font-size: 7.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: #9CA3AF;
    margin-bottom: 7px;
}
.kf-pill {
    display: inline-block;
    background: #EFF6FF;
    border: 1px solid #BFDBFE;
    padding: 3px 12px;
    font-size: 10px;
    color: #1D4ED8;
    font-weight: 600;
    margin: 0 4px 4px 0;
    border-radius: 20px;
}

.rule { border: none; border-top: 1px solid #E5E7EB; margin: 14px 0; }

/* ── CONTACT STRIP ────────────────────────────────────────────────────────── */
.contact-strip {
    display: flex;
    border: 1px solid #E5E7EB;
    border-radius: 5px;
    overflow: hidden;
    margin-top: 14px;
}
.contact-item {
    flex: 1;
    padding: 10px 14px;
    border-right: 1px solid #E5E7EB;
}
.contact-item:last-child { border-right: none; }
.contact-item:first-child { border-left: 3px solid #1D4ED8; }
.contact-lbl {
    font-size: 7px;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    color: #9CA3AF;
    font-weight: 700;
    margin-bottom: 4px;
}
.contact-val {
    font-size: 11.5px;
    font-weight: 700;
    color: #0C1A2E;
    line-height: 1.35;
}

/* ── NOTES ────────────────────────────────────────────────────────────────── */
.notes-box {
    margin-top: 12px;
    padding: 10px 14px;
    background: #FFFBEB;
    border-left: 3px solid #F59E0B;
    border-radius: 0 3px 3px 0;
}
.notes-lbl { font-size: 7.5px; text-transform: uppercase; letter-spacing: 1.5px; color: #92400E; font-weight: 700; margin-bottom: 4px; }
.notes-text { font-size: 11.5px; color: #78350F; line-height: 1.65; }
</style>
</head>
<body>

{{-- ═══════════════════════════════════════════════════════════════════════════
     PÁGINA 1 — Portada ejecutiva
     ═══════════════════════════════════════════════════════════════════════════ --}}
<div class="page page-break">

    {{-- DARK NAVY HEADER BAND --}}
    <div class="p1-header">
        <div class="p1-hd-logo">
            @if($logoSrcLight)
                <img src="{{ $logoSrcLight }}" alt="{{ $siteName }}">
            @elseif($logoSrc)
                <img src="{{ $logoSrc }}" alt="{{ $siteName }}">
            @else
                <div class="p1-hd-logo-txt">HOME DEL VALLE</div>
            @endif
            <div class="p1-hd-logo-sub">Inmobiliaria Boutique</div>
        </div>

        <div class="p1-hd-center">
            <div class="p1-hd-eyebrow">Documento Técnico Confidencial</div>
            <div class="p1-hd-title">Opinión de Valor Inmobiliario</div>
            <div class="p1-hd-subtitle">{{ $addressDisplay }}</div>
        </div>

        <div class="p1-hd-right">
            <div class="p1-hd-folio-lbl">Folio</div>
            <div class="p1-hd-folio">{{ $folio }}</div>
            <div class="p1-hd-date">{{ $today }}</div>
        </div>
    </div>

    {{-- BODY --}}
    <div class="p1-body">

        {{-- PROPERTY BAND --}}
        <div class="prop-band">
            <div class="prop-band-left">
                <div class="prop-address">{{ $addressDisplay }}</div>
                @if($addressSubline)
                <div class="prop-address-sub">{{ $addressSubline }}</div>
                @endif
                <div class="prop-chips" style="{{ $addressSubline ? 'margin-top:5px;' : '' }}">
                    <span class="prop-chip">{{ $typeLabel }}</span>
                    <span class="prop-chip">{{ $colonia }} · {{ $zone }}</span>
                    <span class="prop-chip">{{ $ageLabel }}</span>
                    <span class="prop-chip">Conservación: {{ $condLabel }}</span>
                    <span class="prop-chip-muted">Confianza {{ $confidLabel }}</span>
                </div>
            </div>
            <div class="diag-badge">{{ $diagLabel }}</div>
        </div>

        {{-- PRICE HERO --}}
        @if($sug)
        <div class="price-hero">
            <div class="price-hero-left">
                <div class="price-eyebrow">Precio de Salida Recomendado</div>
                <div class="price-figure">
                    <span class="price-dollar">$</span>
                    <span class="price-amount">{{ number_format($sug) }}</span>
                    <span class="price-mxn-tag">MXN</span>
                </div>
                <div class="price-meta">
                    <strong>${{ number_format($valuation->adjusted_price_m2) }}/m²</strong> ajustado
                    &nbsp;·&nbsp; <strong>{{ number_format($valuation->effective_m2, 0) }} m²</strong> efectivos
                    &nbsp;·&nbsp; {{ $colonia }}
                </div>
            </div>
            <div class="kpi-grid">
                <div class="kpi-cell">
                    <span class="kpi-label">Precio /m² ajustado</span>
                    <span class="kpi-value">${{ number_format($valuation->adjusted_price_m2) }}</span>
                </div>
                <div class="kpi-cell">
                    <span class="kpi-label">Base zona</span>
                    <span class="kpi-value">${{ number_format($valuation->base_price_m2) }}</span>
                </div>
                <div class="kpi-cell">
                    <span class="kpi-label">Ajuste total</span>
                    <span class="kpi-value {{ $adjTotal >= 0 ? 'kpi-value-pos' : 'kpi-value-neg' }}">{{ ($adjTotal >= 0 ? '+' : '') . $adjTotal }}%</span>
                </div>
                <div class="kpi-cell">
                    <span class="kpi-label">m² efectivos</span>
                    <span class="kpi-value">{{ number_format($valuation->effective_m2, 0) }} m²</span>
                </div>
            </div>
        </div>
        @endif

        {{-- RANGE VISUALIZATION --}}
        @if($low && $high)
        <div class="range-section">
            <div class="range-eyebrow">Rango de Valor Estimado</div>
            <div class="rv-outer">
                <div class="rv-track">
                    {{-- Gradient fill between low and high --}}
                    <div class="rv-fill" style="left:{{ $pLow }}%;width:{{ $pHigh - $pLow }}%;"></div>

                    {{-- Dot: Mínimo --}}
                    <div class="rv-dot" style="left:{{ $pLow }}%;">
                        <span class="rv-lbl">Mínimo</span>
                        <span class="rv-price">${{ number_format($low) }}</span>
                    </div>

                    {{-- Dot: Medio --}}
                    <div class="rv-dot" style="left:{{ $pMid }}%;">
                        <span class="rv-lbl">Medio</span>
                        <span class="rv-price">${{ number_format($mid) }}</span>
                    </div>

                    {{-- Dot: Máximo --}}
                    <div class="rv-dot" style="left:{{ $pHigh }}%;">
                        <span class="rv-lbl">Máximo</span>
                        <span class="rv-price">${{ number_format($high) }}</span>
                    </div>

                    {{-- Dot: Sugerido (accent) --}}
                    <div class="rv-dot rv-dot-accent" style="left:{{ $pSug }}%;">
                        <span class="rv-lbl rv-lbl-accent">▲ Sugerido</span>
                        <span class="rv-price rv-price-accent">${{ number_format($sug) }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ZONE SPLIT --}}
        <div class="zone-split">
            <div class="zone-map-col">
                @if($mapUrl)
                    <img src="{{ $mapUrl }}" alt="Zona {{ $colonia }}">
                @else
                    <div class="zone-placeholder">
                        <div class="zone-ph-pin"></div>
                        <div class="zone-ph-name">{{ $colonia }}</div>
                        <div class="zone-ph-sub">{{ $zone }} · Benito Juárez · CDMX</div>
                    </div>
                @endif
            </div>
            <div class="zone-notes-col">
                <div class="zone-notes-header">A Considerar</div>
                @foreach($considerations as $i => $note)
                <div class="zone-note-item">
                    <div class="zone-note-num">{{ $i + 1 }}</div>
                    <div class="zone-note-text">{{ $note }}</div>
                </div>
                @endforeach
            </div>
        </div>

    </div>{{-- /p1-body --}}

    <div class="p1-footer">
        <div class="p1-ft-l">{{ $siteUrl }}</div>
        <div class="p1-ft-c">Home del Valle · Opinión de Valor</div>
        <div class="p1-ft-r">{{ $folio }} · Página 1 de {{ $totalPages }} · Confidencial</div>
    </div>

</div>{{-- /page-1 --}}


{{-- ═══════════════════════════════════════════════════════════════════════════
     PÁGINA 2 — Análisis técnico: características + waterfall
     ═══════════════════════════════════════════════════════════════════════════ --}}
<div class="page page-break">

    <div class="mhd">
        <div class="mhd-logo">
            @if($logoSrc)
                <img src="{{ $logoSrc }}" alt="{{ $siteName }}">
            @elseif($logoSrcLight)
                <img src="{{ $logoSrcLight }}" alt="{{ $siteName }}">
            @else
                <div class="mhd-logo-txt">HOME DEL VALLE</div>
            @endif
        </div>
        <div class="mhd-right">
            <div class="mhd-folio">{{ $folio }}</div>
            <div class="mhd-section">Análisis Técnico</div>
        </div>
    </div>

    <div class="p2-body">

        {{-- CARACTERÍSTICAS DEL INMUEBLE — 4-col data grid --}}
        <div class="sec-lbl">Características del Inmueble</div>

        @php
        // Build rows of cells (4 per row) — only non-null values
        $charCells = [];
        if ($address) $charCells[] = ['Dirección', $address];
        $charCells[] = ['Colonia', $colonia];
        $charCells[] = ['Zona', $zone . ', Benito Juárez'];
        $charCells[] = ['Tipo', $typeLabel];
        $charCells[] = ['Antigüedad', ($valuation->input_age_years ? $valuation->input_age_years . ' años · ' : '') . $ageLabel];
        $charCells[] = ['Conservación', $condLabel];
        if ($valuation->input_m2_total) $charCells[] = ['m² totales', number_format($valuation->input_m2_total, 1) . ' m²'];
        if ($valuation->input_m2_const) $charCells[] = ['m² construcción', number_format($valuation->input_m2_const, 1) . ' m²'];
        $charCells[] = ['Recámaras', $valuation->input_bedrooms ?? '—'];
        $charCells[] = ['Baños', $valuation->bathrooms_display];
        $charCells[] = ['Estacionamientos', ($valuation->input_parking ?? 0) . ' cajón(es)'];
        if ($valuation->input_floor) $charCells[] = ['Piso', 'Piso ' . $valuation->input_floor];
        $charCells[] = ['Elevador', $valuation->input_has_elevator ? 'Sí' : 'No'];
        if ($valuation->input_type === 'apartment') {
            if ($valuation->input_unit_position) $charCells[] = ['Posición', $valuation->input_unit_position === 'exterior' ? 'Exterior' : 'Interior'];
            if ($valuation->input_orientation)  $charCells[] = ['Orientación', ucfirst($valuation->input_orientation)];
            if ($valuation->input_seismic_status && $valuation->input_seismic_status !== 'none')
                $charCells[] = ['Historial sísmico', match($valuation->input_seismic_status) {
                    'damaged_repaired'   => 'Daño reparado',
                    'damaged_reinforced' => 'Daño reforzado',
                    'unknown'            => 'Desconocido',
                    default              => '—',
                }];
        }
        if ($valuation->input_street_type)   $charCells[] = ['Entorno', $valuation->street_type_label];
        if ($valuation->input_views)         $charCells[] = ['Vistas', $valuation->views_label];
        if ($valuation->input_legal_status)  $charCells[] = ['Estado legal', $valuation->legal_status_label];
        if ($valuation->input_maintenance_fee) $charCells[] = ['Mantenimiento', '$' . number_format($valuation->input_maintenance_fee) . '/mes'];
        // Pad to multiple of 4
        while (count($charCells) % 4 !== 0) $charCells[] = null;
        $charRows = array_chunk($charCells, 4);

        // Tags
        $amenTags = array_filter([
            $valuation->input_has_rooftop      ? 'Rooftop'         : null,
            $valuation->input_has_balcony       ? 'Balcón'          : null,
            $valuation->input_has_service_room  ? 'Cuarto servicio' : null,
            $valuation->input_has_storage       ? 'Bodega'          : null,
            $valuation->input_has_gym           ? 'Gimnasio'        : null,
            $valuation->input_has_pool          ? 'Alberca'         : null,
            $valuation->input_has_lobby         ? 'Lobby'           : null,
        ]);
        $secTags = array_filter([
            $valuation->input_has_doorman           ? 'Guardia 24h'      : null,
            $valuation->input_has_security_cameras   ? 'Cámaras CCTV'    : null,
            $valuation->input_has_intercom           ? 'Intercomunicador' : null,
            $valuation->input_has_alarm              ? 'Alarma'           : null,
        ]);
        $infraTags = array_filter([
            $valuation->input_has_natural_gas ? 'Gas natural' : null,
            $valuation->input_has_cistern     ? 'Cisterna'    : null,
        ]);
        @endphp

        <table class="chars-grid">
            @foreach($charRows as $row)
            <tr>
                @foreach($row as $cell)
                    @if($cell)
                    <td>
                        <span class="char-label">{{ $cell[0] }}</span>
                        <span class="char-value">{{ $cell[1] }}</span>
                    </td>
                    @else
                    <td></td>
                    @endif
                @endforeach
            </tr>
            @endforeach
        </table>

        {{-- TAGS ROW --}}
        @if($amenTags || $secTags || $infraTags)
        <div class="tags-row">
            @if($amenTags)
                <span class="tags-group-lbl">Amenidades:</span>
                @foreach($amenTags as $tag)
                    <span class="tag-pill">{{ $tag }}</span>
                @endforeach
            @endif
            @if($secTags)
                <span class="tags-group-lbl" style="margin-left:6px;">Seguridad:</span>
                @foreach($secTags as $tag)
                    <span class="tag-pill">{{ $tag }}</span>
                @endforeach
            @endif
            @if($infraTags)
                <span class="tags-group-lbl" style="margin-left:6px;">Infraestructura:</span>
                @foreach($infraTags as $tag)
                    <span class="tag-pill">{{ $tag }}</span>
                @endforeach
            @endif
        </div>
        @endif

        {{-- WATERFALL DETALLADO --}}
        @if($valuation->adjustments->isNotEmpty())
        <div class="sec-lbl">Factores de Ajuste — Metodología Waterfall</div>
        @php $maxAbs = $valuation->adjustments->max(fn($a) => abs($a->adjustment_value)) ?: 1; @endphp
        <table class="wf">
            <thead>
                <tr>
                    <th style="width:34%;">Factor</th>
                    <th style="width:110px;">Impacto visual</th>
                    <th style="width:60px;">Ajuste</th>
                    <th style="width:96px;">Antes</th>
                    <th style="width:96px;">Después</th>
                </tr>
            </thead>
            <tbody>
                {{-- Base row --}}
                <tr class="r-base">
                    <td>
                        <div class="wf-factor-name">Precio base · {{ $colonia }}</div>
                        @if($valuation->snapshot)
                        <div class="wf-factor-sub">{{ $valuation->snapshot->age_label ?? '' }} · Confianza {{ $confidLabel }}</div>
                        @endif
                    </td>
                    <td>
                        <div class="wf-bar-bg">
                            <div class="wf-bar-inner wf-bar-neu" style="width:100%;"></div>
                        </div>
                    </td>
                    <td style="color:#9CA3AF;">—</td>
                    <td class="price-muted">—</td>
                    <td style="font-weight:700;color:#0C1A2E;">${{ number_format($valuation->base_price_m2) }}/m²</td>
                </tr>

                {{-- Adjustment rows --}}
                @foreach($valuation->adjustments as $adj)
                @php
                    $v    = (float)$adj->adjustment_value;
                    $isN  = $adj->is_neutral;
                    $isP  = $adj->is_positive;
                    $bw   = $isN ? 100 : max(4, min(100, round(abs($v) / $maxAbs * 100)));
                    $bCls = $isN ? 'wf-bar-neu' : ($isP ? 'wf-bar-pos' : 'wf-bar-neg');
                    $tCls = $isN ? '' : ($isP ? 'pct-pos' : 'pct-neg');
                @endphp
                <tr>
                    <td>
                        <div class="wf-factor-name">{{ $adj->factor_label }}</div>
                        @if($adj->explanation)
                        <div class="wf-factor-sub">{{ $adj->explanation }}</div>
                        @endif
                    </td>
                    <td class="wf-bar-wrap">
                        <div class="wf-bar-bg">
                            <div class="wf-bar-inner {{ $bCls }}" style="width:{{ $bw }}%;"></div>
                        </div>
                    </td>
                    <td class="{{ $tCls }}">{{ $adj->formatted_value }}</td>
                    <td class="price-muted">${{ number_format($adj->price_before) }}/m²</td>
                    <td style="font-weight:700;color:#0C1A2E;">${{ number_format($adj->price_after) }}/m²</td>
                </tr>
                @endforeach

                {{-- Total row --}}
                @php $tPct = round((($valuation->adjusted_price_m2 - $valuation->base_price_m2) / $valuation->base_price_m2) * 100, 1); @endphp
                <tr class="r-total">
                    <td>Precio ajustado final · {{ $colonia }}</td>
                    <td></td>
                    <td>{{ ($tPct >= 0 ? '+' : '') . $tPct }}%</td>
                    <td></td>
                    <td>${{ number_format($valuation->adjusted_price_m2) }}/m²</td>
                </tr>
            </tbody>
        </table>
        @endif

    </div>{{-- /p2-body --}}

    <div class="dark-footer">
        <div class="dark-footer-top">
            <div class="dark-footer-brand">{{ $siteName }} — {{ $siteUrl }}</div>
            <div class="dark-footer-page">{{ $folio }} &nbsp;|&nbsp; Página 2 de {{ $totalPages }} &nbsp;|&nbsp; Confidencial</div>
        </div>
    </div>

</div>{{-- /page-2 --}}


{{-- ═══════════════════════════════════════════════════════════════════════════
     PÁGINA 3 — Análisis de mercado
     ═══════════════════════════════════════════════════════════════════════════ --}}
<div class="page {{ $valuation->input_notes ? 'page-break' : '' }}">

    <div class="mhd">
        <div class="mhd-logo">
            @if($logoSrc)
                <img src="{{ $logoSrc }}" alt="{{ $siteName }}">
            @elseif($logoSrcLight)
                <img src="{{ $logoSrcLight }}" alt="{{ $siteName }}">
            @else
                <div class="mhd-logo-txt">HOME DEL VALLE</div>
            @endif
        </div>
        <div class="mhd-right">
            <div class="mhd-folio">{{ $folio }}</div>
            <div class="mhd-section">Análisis de Mercado</div>
        </div>
    </div>

    <div class="p3-body">

        @if(!empty($n['market_context']) || !empty($n['recommendation']))

        <div class="sec-lbl">Análisis Profesional de Mercado</div>

        @if(!empty($n['market_context']))
        <p class="narr-lead">{{ $n['market_context'] }}</p>
        @endif

        @if(!empty($n['property_strengths']) || !empty($n['property_risks']))
        <div class="str-risk-row">
            @if(!empty($n['property_strengths']))
            <div class="sr-card sr-card-pos">
                <div class="sr-eyebrow sr-eyebrow-pos">Fortalezas del inmueble</div>
                <div class="sr-text">{{ $n['property_strengths'] }}</div>
            </div>
            @endif
            @if(!empty($n['property_risks']))
            <div class="sr-card sr-card-neg">
                <div class="sr-eyebrow sr-eyebrow-neg">Riesgo principal</div>
                <div class="sr-text">{{ $n['property_risks'] }}</div>
            </div>
            @endif
        </div>
        @endif

        @if(!empty($n['recommendation']))
        <div class="rec-box">
            <div class="rec-eyebrow">Recomendación Comercial</div>
            <div class="rec-text">{{ $n['recommendation'] }}</div>
        </div>
        @endif

        @if(!empty($n['key_factors']) && is_array($n['key_factors']))
        <div class="key-factors-wrap">
            <div class="key-factors-lbl">Factores clave identificados</div>
            @foreach($n['key_factors'] as $f)
                <span class="kf-pill">{{ $f }}</span>
            @endforeach
        </div>
        @endif

        <hr class="rule">

        @else

        {{-- FALLBACK sin IA --}}
        <div class="sec-lbl">Recomendación Comercial</div>
        <div class="rec-box">
            <div class="rec-eyebrow">Estrategia de salida al mercado</div>
            <div class="rec-text">
                @switch($valuation->diagnosis)
                @case('on_market')
                    El inmueble está en línea con el mercado de {{ $colonia }}. El precio de salida de ${{ number_format($sug) }} maximiza el equilibrio entre rapidez de colocación y valor obtenido. Se recomienda iniciar con este precio y evaluar la respuesta del mercado en las primeras tres semanas.
                    @break
                @case('opportunity')
                    El inmueble presenta características de oportunidad frente al mercado activo. Con un precio de ${{ number_format($sug) }} se puede capturar el diferencial de mercado manteniendo alta probabilidad de cierre en el corto plazo.
                    @break
                @case('above_market')
                    El inmueble se posiciona por encima del promedio de zona. Se recomienda iniciar en ${{ number_format($sug) }} con un margen de negociación del 3–5% y ajustar estrategia a partir de la respuesta recibida en las primeras tres semanas.
                    @break
                @default
                    Se recomienda complementar este análisis con recorridos comparativos en {{ $colonia }} para afinar el precio de salida al mercado.
                @endswitch
            </div>
        </div>
        <hr class="rule">

        @endif

        {{-- CONTACTO --}}
        <div class="sec-lbl">Contacto</div>
        <div class="contact-strip">
            <div class="contact-item">
                <div class="contact-lbl">Inmobiliaria</div>
                <div class="contact-val">{{ $siteName }}</div>
            </div>
            @if($contactPhone)
            <div class="contact-item">
                <div class="contact-lbl">Teléfono / WhatsApp</div>
                <div class="contact-val">{{ $contactPhone }}</div>
            </div>
            @endif
            @if($contactEmail)
            <div class="contact-item">
                <div class="contact-lbl">Correo electrónico</div>
                <div class="contact-val">{{ $contactEmail }}</div>
            </div>
            @endif
            <div class="contact-item">
                <div class="contact-lbl">Sitio web</div>
                <div class="contact-val">{{ $siteUrl }}</div>
            </div>
            <div class="contact-item">
                <div class="contact-lbl">Vigencia</div>
                <div class="contact-val">Vence {{ $validity }}</div>
            </div>
        </div>

    </div>{{-- /p3-body --}}

    <div class="dark-footer">
        <div class="dark-footer-top">
            <div class="dark-footer-brand">{{ $siteName }} — {{ $siteUrl }}</div>
            <div class="dark-footer-page">{{ $folio }} &nbsp;|&nbsp; Página 3 de {{ $totalPages }} &nbsp;|&nbsp; Confidencial</div>
        </div>
        @if(!$valuation->input_notes)
        <div class="dark-footer-legal">
            Esta Opinión de Valor es elaborada por {{ $siteName }} con base en datos de oferta publicada en portales inmobiliarios y ajustes estadísticos descritos en este documento.
            <strong style="color:rgba(255,255,255,0.38);">No constituye un avalúo formal</strong> con efectos fiscales, notariales o de crédito hipotecario.
            Para dichos efectos se requiere valuador certificado (INDAABIN / SHF / AMPI). El valor de cierre depende de las condiciones de cada negociación.
            &nbsp;·&nbsp; &copy; {{ now()->year }} {{ $siteName }} · Todos los derechos reservados.
        </div>
        @endif
    </div>

</div>{{-- /page-3 --}}


{{-- ═══════════════════════════════════════════════════════════════════════════
     PÁGINA 4 — Notas del analista (solo si hay notas)
     ═══════════════════════════════════════════════════════════════════════════ --}}
@if($valuation->input_notes)
<div class="page">

    <div class="mhd">
        <div class="mhd-logo">
            @if($logoSrc)
                <img src="{{ $logoSrc }}" alt="{{ $siteName }}">
            @elseif($logoSrcLight)
                <img src="{{ $logoSrcLight }}" alt="{{ $siteName }}">
            @else
                <div class="mhd-logo-txt">HOME DEL VALLE</div>
            @endif
        </div>
        <div class="mhd-right">
            <div class="mhd-folio">{{ $folio }}</div>
            <div class="mhd-section">Notas del Analista</div>
        </div>
    </div>

    <div style="padding: 28px 48px 20px; overflow: auto;">
        <div class="notes-box" style="max-height: none; overflow: visible;">
            <div class="notes-lbl">Notas del analista</div>
            <div class="notes-text" style="white-space: pre-wrap;">{{ $valuation->input_notes }}</div>
        </div>
    </div>

    <div class="dark-footer">
        <div class="dark-footer-top">
            <div class="dark-footer-brand">{{ $siteName }} — {{ $siteUrl }}</div>
            <div class="dark-footer-page">{{ $folio }} &nbsp;|&nbsp; Página 4 de 4 &nbsp;|&nbsp; Confidencial</div>
        </div>
        <div class="dark-footer-legal">
            Esta Opinión de Valor es elaborada por {{ $siteName }} con base en datos de oferta publicada en portales inmobiliarios y ajustes estadísticos descritos en este documento.
            <strong style="color:rgba(255,255,255,0.38);">No constituye un avalúo formal</strong> con efectos fiscales, notariales o de crédito hipotecario.
            Para dichos efectos se requiere valuador certificado (INDAABIN / SHF / AMPI). El valor de cierre depende de las condiciones de cada negociación.
            &nbsp;·&nbsp; &copy; {{ now()->year }} {{ $siteName }} · Todos los derechos reservados.
        </div>
    </div>

</div>{{-- /page-4 --}}
@endif

</body>
</html>
