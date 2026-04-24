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
$mapCenter = urlencode($colonia . ', Benito Juárez, Ciudad de México, Mexico');
$mapUrl    = $mapKey
    ? "https://maps.googleapis.com/maps/api/staticmap?center={$mapCenter}&zoom=15&size=560x260&scale=2&maptype=roadmap"
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

/* ── PALETA ──────────────────────────────────────────────────────────────────
   #0C1A2E  navy — footer bg, títulos primarios
   #1D4ED8  azul vibrante — acento primario, precio sugerido
   #2563A0  azul medio — labels, barras, borders
   #3B82F6  azul claro — fills secundarios
   #EFF6FF  azul 50 — fondos de sección acento
   #F9FAFB  gris 50 — fondos alternos
   #E5E7EB  gris 200 — divisores
   #6B7280  gris 500 — texto secundario
   #111827  gris 900 — texto principal
────────────────────────────────────────────────────────────────────────────── */

/* ── LAYOUT DE PÁGINA (CSS Grid) ─────────────────────────────────────────── */
.page {
    width: 100%;
    height: 297mm;
    display: grid;
    grid-template-rows: auto 1fr auto;
    background: #fff;
    overflow: hidden;
}
.page-break { break-after: page; page-break-after: always; }

/* ── HEADER PRINCIPAL ─────────────────────────────────────────────────────── */
.hd {
    padding: 18px 48px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    border-bottom: 2px solid #1D4ED8;
}
.hd-logo { flex-shrink: 0; }
.hd-logo img { height: 34px; width: auto; display: block; }
.hd-logo-txt { font-size: 14px; font-weight: 800; color: #0C1A2E; letter-spacing: -0.3px; }
.hd-logo-sub { font-size: 8px; text-transform: uppercase; letter-spacing: 2px; color: #9CA3AF; margin-top: 4px; }
.hd-center { flex: 1; text-align: center; }
.hd-eyebrow { font-size: 8px; text-transform: uppercase; letter-spacing: 4.5px; color: #9CA3AF; font-weight: 600; margin-bottom: 3px; }
.hd-title { font-size: 17px; font-weight: 800; color: #0C1A2E; letter-spacing: -0.4px; line-height: 1.2; }
.hd-right { text-align: right; flex-shrink: 0; }
.hd-folio-lbl { font-size: 8px; text-transform: uppercase; letter-spacing: 2px; color: #9CA3AF; }
.hd-folio { font-size: 14px; font-weight: 800; color: #0C1A2E; margin-top: 1px; font-feature-settings: "tnum"; }
.hd-date { font-size: 10px; color: #6B7280; margin-top: 3px; }

/* ── MINI HEADER (págs 2 y 3) ────────────────────────────────────────────── */
.mhd {
    padding: 12px 48px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 2px solid #1D4ED8;
    background: #F9FAFB;
}
.mhd-logo img { height: 22px; width: auto; display: block; }
.mhd-logo-txt { font-size: 12px; font-weight: 800; color: #0C1A2E; }
.mhd-right { text-align: right; }
.mhd-folio { font-size: 8px; color: #9CA3AF; text-transform: uppercase; letter-spacing: 1.5px; }
.mhd-section { font-size: 10px; font-weight: 700; color: #1D4ED8; text-transform: uppercase; letter-spacing: 1.5px; margin-top: 2px; }

/* ── BODY WRAPPER ─────────────────────────────────────────────────────────── */
.body { overflow: hidden; }

/* ── PROPERTY BAND ────────────────────────────────────────────────────────── */
.prop-band {
    background: #F9FAFB;
    border-bottom: 1px solid #E5E7EB;
    padding: 14px 48px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}
.prop-address {
    font-size: 16px;
    font-weight: 800;
    color: #0C1A2E;
    letter-spacing: -0.3px;
    margin-bottom: 7px;
    line-height: 1.25;
}
.prop-meta { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }
.prop-chip {
    font-size: 10px;
    font-weight: 600;
    color: #374151;
    background: #fff;
    border: 1px solid #E5E7EB;
    border-radius: 2px;
    padding: 2px 8px;
    white-space: nowrap;
}
.prop-chip-light { color: #6B7280; background: transparent; border-color: transparent; padding-left: 0; }
.diag-badge {
    display: inline-block;
    padding: 5px 14px;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    border: 1px solid {{ $diagBorder }};
    background: {{ $diagBg }};
    color: {{ $diagColor }};
    border-radius: 2px;
    white-space: nowrap;
    flex-shrink: 0;
}

/* ── PRICE HERO ───────────────────────────────────────────────────────────── */
.price-section {
    padding: 22px 48px 18px;
    border-bottom: 1px solid #E5E7EB;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 24px;
}
.price-left { flex: 1; min-width: 0; }
.price-eyebrow {
    font-size: 8.5px;
    text-transform: uppercase;
    letter-spacing: 3.5px;
    color: #1D4ED8;
    font-weight: 700;
    margin-bottom: 6px;
}
.price-num {
    font-size: 56px;
    font-weight: 900;
    color: #0C1A2E;
    letter-spacing: -3.5px;
    line-height: 1;
    font-feature-settings: "tnum";
    white-space: nowrap;
}
.price-cur {
    font-size: 22px;
    font-weight: 400;
    color: #9CA3AF;
    vertical-align: top;
    line-height: 56px;
    margin-right: 2px;
}
.price-mxn {
    font-size: 11px;
    font-weight: 500;
    color: #9CA3AF;
    letter-spacing: 1px;
    margin-left: 8px;
    vertical-align: middle;
}
.price-sub {
    margin-top: 8px;
    font-size: 12px;
    color: #6B7280;
    line-height: 1.4;
}

/* ── KPI CARDS ────────────────────────────────────────────────────────────── */
.kpi-row {
    display: flex;
    gap: 0;
    border: 1px solid #E5E7EB;
    border-radius: 4px;
    overflow: hidden;
    flex-shrink: 0;
}
.kpi {
    padding: 10px 16px;
    border-right: 1px solid #E5E7EB;
    text-align: center;
    min-width: 88px;
}
.kpi:last-child { border-right: none; }
.kpi:first-child { border-left: 3px solid #1D4ED8; }
.kpi-v {
    display: block;
    font-size: 16px;
    font-weight: 800;
    color: #0C1A2E;
    letter-spacing: -0.5px;
    font-feature-settings: "tnum";
    line-height: 1.1;
}
.kpi-v-pos { color: #15803D; }
.kpi-v-neg { color: #DC2626; }
.kpi-l {
    display: block;
    font-size: 7.5px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #9CA3AF;
    font-weight: 600;
    margin-top: 4px;
}

/* ── RANGE VISUALIZATION ──────────────────────────────────────────────────── */
.range-section {
    padding: 20px 48px 16px;
    border-bottom: 1px solid #E5E7EB;
}
.range-eyebrow {
    font-size: 8.5px;
    text-transform: uppercase;
    letter-spacing: 3.5px;
    color: #1D4ED8;
    font-weight: 700;
    margin-bottom: 16px;
}
.rv {
    position: relative;
    margin: 0 8px;
}
.rv-track {
    position: relative;
    height: 6px;
    background: #E5E7EB;
    border-radius: 3px;
    margin: 28px 0 32px;
}
.rv-fill {
    position: absolute;
    top: 0;
    height: 6px;
    background: linear-gradient(90deg, #BFDBFE, #1D4ED8);
    border-radius: 3px;
    opacity: 0.5;
}
/* dots on track */
.rv-dot {
    position: absolute;
    top: 50%;
    transform: translate(-50%, -50%);
    width: 11px;
    height: 11px;
    border-radius: 50%;
    background: #fff;
    border: 2px solid #9CA3AF;
    z-index: 2;
}
.rv-dot-accent {
    width: 16px;
    height: 16px;
    background: #1D4ED8;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px #1D4ED8;
}
/* labels above track */
.rv-lbl-top {
    position: absolute;
    bottom: calc(100% + 8px);
    transform: translateX(-50%);
    font-size: 7.5px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #9CA3AF;
    font-weight: 600;
    white-space: nowrap;
}
.rv-lbl-top-accent {
    color: #1D4ED8;
    font-weight: 700;
    font-size: 8px;
    letter-spacing: 1.5px;
}
/* prices below track */
.rv-price-bot {
    position: absolute;
    top: calc(100% + 10px);
    transform: translateX(-50%);
    font-size: 11px;
    font-weight: 700;
    color: #374151;
    white-space: nowrap;
    font-feature-settings: "tnum";
}
.rv-price-bot-accent {
    font-size: 13px;
    font-weight: 800;
    color: #1D4ED8;
}

/* ── ZONA / MAPA + CONSIDERACIONES ───────────────────────────────────────── */
.zone-split {
    display: flex;
    gap: 0;
    margin: 0;
    flex: 1;
    min-height: 0;
}
.zone-map-wrap {
    flex: 1;
    border-right: 1px solid #E5E7EB;
    overflow: hidden;
    display: flex;
    align-items: stretch;
}
.zone-map-wrap img {
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
        linear-gradient(rgba(29,78,216,.05) 1px, transparent 1px),
        linear-gradient(90deg, rgba(29,78,216,.05) 1px, transparent 1px);
    background-size: 20px 20px;
    padding: 20px;
    text-align: center;
}
.zone-placeholder-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #1D4ED8;
    margin: 0 auto 10px;
    box-shadow: 0 0 0 5px rgba(29,78,216,0.12);
}
.zone-placeholder-name { font-size: 15px; font-weight: 800; color: #0C1A2E; margin-bottom: 4px; }
.zone-placeholder-sub  { font-size: 9px; color: #9CA3AF; text-transform: uppercase; letter-spacing: 1.2px; }

.zone-notes {
    width: 42%;
    padding: 18px 24px;
    background: #fff;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.zone-notes-title {
    font-size: 8.5px;
    text-transform: uppercase;
    letter-spacing: 3px;
    color: #1D4ED8;
    font-weight: 700;
    padding-bottom: 10px;
    border-bottom: 1px solid #E5E7EB;
    margin-bottom: 12px;
}
.zone-note {
    display: flex;
    gap: 10px;
    align-items: flex-start;
    margin-bottom: 10px;
}
.zone-note:last-child { margin-bottom: 0; }
.zone-note-dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: #1D4ED8;
    flex-shrink: 0;
    margin-top: 5px;
}
.zone-note-txt { font-size: 11.5px; color: #374151; line-height: 1.6; }

/* ── FOOTER PÁGINA 1 ─────────────────────────────────────────────────────── */
.ft {
    background: #0C1A2E;
    padding: 8px 48px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 2px solid #1D4ED8;
}
.ft-l { font-size: 8.5px; color: rgba(255,255,255,.3); text-transform: uppercase; letter-spacing: 2px; }
.ft-c { font-size: 8.5px; color: rgba(255,255,255,.55); font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; }
.ft-r { font-size: 8.5px; color: rgba(255,255,255,.3); text-align: right; }

/* ── FOOTER PÁGS 2 y 3 ───────────────────────────────────────────────────── */
.ft2 {
    background: #0C1A2E;
    padding: 7px 48px;
    border-top: 2px solid #1D4ED8;
}
.ft2-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; }
.ft2-brand { font-size: 8.5px; color: rgba(255,255,255,.55); font-weight: 700; text-transform: uppercase; letter-spacing: 2px; }
.ft2-page  { font-size: 8px; color: rgba(255,255,255,.3); }
.ft2-legal { font-size: 7.5px; color: rgba(255,255,255,.22); line-height: 1.6; }

/* ══════════════════════════════════════════════════════════════════════════
   PÁGINA 2 — Análisis técnico
   ══════════════════════════════════════════════════════════════════════════ */

.p2-body { padding: 18px 48px 14px; overflow: hidden; }

/* sección label genérico */
.sec-lbl {
    font-size: 8px;
    text-transform: uppercase;
    letter-spacing: 3.5px;
    color: #1D4ED8;
    font-weight: 700;
    margin-bottom: 8px;
}

/* dos columnas */
.two-col { display: flex; gap: 28px; margin-bottom: 16px; }
.col-left  { flex: 1; min-width: 0; }
.col-right { width: 38%; flex-shrink: 0; }
.col-hd {
    font-size: 8px;
    text-transform: uppercase;
    letter-spacing: 3px;
    color: #1D4ED8;
    font-weight: 700;
    padding-bottom: 7px;
    border-bottom: 2px solid #1D4ED8;
    margin-bottom: 8px;
}

/* tabla de especificaciones */
.spec-tbl { width: 100%; border-collapse: collapse; }
.spec-tbl td {
    padding: 4px 0;
    border-bottom: 1px solid #F3F4F6;
    font-size: 11.5px;
    vertical-align: middle;
}
.spec-tbl td:first-child {
    color: #6B7280;
    font-size: 9px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    font-weight: 600;
    width: 46%;
}
.spec-tbl td:last-child { font-weight: 700; color: #0C1A2E; text-align: right; }

/* waterfall compacto (col derecha) */
.wfc { width: 100%; border-collapse: collapse; }
.wfc td {
    padding: 4px 0;
    border-bottom: 1px solid #F3F4F6;
    font-size: 11.5px;
    vertical-align: middle;
}
.wfc td:first-child { color: #6B7280; width: 56%; font-size: 10.5px; }
.wfc td:last-child  { font-weight: 700; color: #0C1A2E; text-align: right; }
.wfc .r-base td { background: #F9FAFB; font-weight: 700; padding: 4px 6px; }
.wfc .r-total td { background: #EFF6FF; font-weight: 800; padding: 5px 6px; color: #1D4ED8; border-top: 1.5px solid #BFDBFE; border-bottom: none; }

/* total box */
.total-box {
    margin-top: 10px;
    padding: 10px 12px;
    border: 1px solid #E5E7EB;
    border-left: 3px solid #1D4ED8;
    background: #F9FAFB;
    border-radius: 0 2px 2px 0;
}
.total-box-lbl  { font-size: 7.5px; color: #6B7280; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; margin-bottom: 4px; }
.total-box-amt  { font-size: 22px; font-weight: 900; color: #0C1A2E; letter-spacing: -1px; font-feature-settings: "tnum"; line-height: 1.1; }
.total-box-rng  { font-size: 10px; color: #6B7280; margin-top: 3px; }

/* nota validez */
.valid-note {
    margin-top: 10px;
    padding: 7px 10px;
    background: #FFFBEB;
    border-left: 2px solid #F59E0B;
    font-size: 9.5px;
    color: #78350F;
    line-height: 1.6;
}

/* waterfall detallado */
.wf { width: 100%; border-collapse: collapse; font-size: 11.5px; }
.wf th {
    background: #0C1A2E;
    color: rgba(255,255,255,.6);
    padding: 6px 8px;
    font-size: 7.5px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    font-weight: 700;
    text-align: left;
}
.wf th:nth-child(n+3) { text-align: right; }
.wf td {
    padding: 4px 8px;
    border-bottom: 1px solid #F3F4F6;
    vertical-align: middle;
}
.wf td:nth-child(n+3) { text-align: right; font-weight: 700; }
.wf .r-base td { background: #F9FAFB; font-weight: 700; }
.wf .r-total td { background: #EFF6FF; font-weight: 800; border-top: 1.5px solid #BFDBFE; color: #0C1A2E; }
.wf-bar-wrap { width: 66px; }
.wf-bar { height: 5px; border-radius: 2px; }
.wf-bar-pos { background: #15803D; }
.wf-bar-neg { background: #DC2626; }
.wf-bar-neu { background: #E5E7EB; width: 100% !important; }
.wf-sub { font-size: 9px; color: #9CA3AF; margin-top: 1px; }
.pct-pos { color: #15803D; }
.pct-neg { color: #DC2626; }

/* ══════════════════════════════════════════════════════════════════════════
   PÁGINA 3 — Análisis de mercado
   ══════════════════════════════════════════════════════════════════════════ */
.p3-body { padding: 18px 48px 16px; overflow: hidden; }

.narr-lead { font-size: 13px; color: #374151; line-height: 1.8; margin-bottom: 14px; }

.str-risk { display: flex; gap: 12px; margin-bottom: 14px; }
.sr-card {
    flex: 1;
    padding: 12px 14px;
    background: #F9FAFB;
    border-top: 3px solid;
    border-radius: 0 0 2px 2px;
}
.sr-card-pos { border-top-color: #1D4ED8; }
.sr-card-neg { border-top-color: #6B7280; }
.sr-eyebrow {
    font-size: 8px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 7px;
}
.sr-eyebrow-pos { color: #1D4ED8; }
.sr-eyebrow-neg { color: #6B7280; }
.sr-txt { font-size: 12px; color: #374151; line-height: 1.75; }

.rec-box {
    padding: 14px 18px;
    background: #EFF6FF;
    border: 1px solid #BFDBFE;
    border-left: 4px solid #1D4ED8;
    margin-bottom: 14px;
    border-radius: 0 2px 2px 0;
}
.rec-eyebrow { font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; color: #1D4ED8; margin-bottom: 6px; }
.rec-txt { font-size: 13px; color: #1E3A8A; line-height: 1.8; font-weight: 500; }

.pills-wrap { margin-bottom: 14px; }
.pills-lbl { font-size: 7.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; color: #9CA3AF; margin-bottom: 6px; }
.pill {
    display: inline-block;
    background: #EFF6FF;
    border: 1px solid #BFDBFE;
    padding: 3px 11px;
    font-size: 10px;
    color: #1D4ED8;
    font-weight: 600;
    margin: 0 4px 4px 0;
    border-radius: 20px;
}

/* contact strip */
.contact-strip {
    display: flex;
    gap: 0;
    border: 1px solid #E5E7EB;
    border-radius: 4px;
    overflow: hidden;
    margin-top: 14px;
}
.contact-item {
    flex: 1;
    padding: 10px 14px;
    border-right: 1px solid #E5E7EB;
}
.contact-item:last-child { border-right: none; }
.contact-lbl { font-size: 7.5px; text-transform: uppercase; letter-spacing: 1.2px; color: #9CA3AF; font-weight: 700; margin-bottom: 3px; }
.contact-val { font-size: 12px; font-weight: 700; color: #0C1A2E; }

.rule { border: none; border-top: 1px solid #E5E7EB; margin: 12px 0; }

@media print {
    * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
}
</style>
</head>
<body>

{{-- ═══════════════════════════════════════════════════════════════════════════
     PÁGINA 1 — Portada ejecutiva
     ═══════════════════════════════════════════════════════════════════════════ --}}
<div class="page page-break">

    {{-- HEADER --}}
    <div class="hd">
        <div class="hd-logo">
            @if($logoSrcLight)
                <img src="{{ $logoSrcLight }}" alt="{{ $siteName }}">
            @elseif($logoSrc)
                <img src="{{ $logoSrc }}" alt="{{ $siteName }}">
            @else
                <div class="hd-logo-txt">HOME DEL VALLE</div>
            @endif
            <div class="hd-logo-sub">Inmobiliaria Boutique</div>
        </div>
        <div class="hd-center">
            <div class="hd-eyebrow">Documento Técnico</div>
            <div class="hd-title">Opinión de Valor Inmobiliario</div>
        </div>
        <div class="hd-right">
            <div class="hd-folio-lbl">Folio</div>
            <div class="hd-folio">{{ $folio }}</div>
            <div class="hd-date">{{ $today }}</div>
        </div>
    </div>

    <div class="body">

        {{-- PROPERTY BAND --}}
        <div class="prop-band">
            <div>
                <div class="prop-address">{{ $address }}</div>
                <div class="prop-meta">
                    <span class="prop-chip">{{ $typeLabel }}</span>
                    <span class="prop-chip">{{ $colonia }} · {{ $zone }}</span>
                    <span class="prop-chip">{{ $ageLabel }}</span>
                    <span class="prop-chip">Conservación: {{ $condLabel }}</span>
                    <span class="prop-chip-light prop-chip">Confianza {{ $confidLabel }}</span>
                </div>
            </div>
            <div class="diag-badge">{{ $diagLabel }}</div>
        </div>

        {{-- PRICE HERO --}}
        @if($sug)
        <div class="price-section">
            <div class="price-left">
                <div class="price-eyebrow">Precio de Salida Recomendado</div>
                <div class="price-num">
                    <span class="price-cur">$</span>{{ number_format($sug) }}<span class="price-mxn">MXN</span>
                </div>
                <div class="price-sub">
                    ${{ number_format($valuation->adjusted_price_m2) }}/m² ajustado
                    &nbsp;·&nbsp; {{ number_format($valuation->effective_m2, 0) }} m² efectivos
                    &nbsp;·&nbsp; {{ $colonia }}
                </div>
            </div>
            <div class="kpi-row">
                <div class="kpi">
                    <span class="kpi-v">${{ number_format($valuation->adjusted_price_m2) }}</span>
                    <span class="kpi-l">Precio /m²</span>
                </div>
                <div class="kpi">
                    <span class="kpi-v">${{ number_format($valuation->base_price_m2) }}</span>
                    <span class="kpi-l">Base zona</span>
                </div>
                <div class="kpi">
                    <span class="kpi-v {{ $adjTotal >= 0 ? 'kpi-v-pos' : 'kpi-v-neg' }}">{{ ($adjTotal >= 0 ? '+' : '') . $adjTotal }}%</span>
                    <span class="kpi-l">Ajuste total</span>
                </div>
                <div class="kpi">
                    <span class="kpi-v">{{ number_format($valuation->effective_m2, 0) }} m²</span>
                    <span class="kpi-l">m² efectivos</span>
                </div>
            </div>
        </div>
        @endif

        {{-- RANGE VISUALIZATION --}}
        @if($low && $high)
        <div class="range-section">
            <div class="range-eyebrow">Rango de Valor Estimado</div>
            <div class="rv">
                <div class="rv-track">
                    {{-- fill between low and high --}}
                    <div class="rv-fill" style="left:{{ $pLow }}%;width:{{ $pHigh - $pLow }}%;"></div>

                    {{-- dot MIN --}}
                    <div class="rv-dot" style="left:{{ $pLow }}%;">
                        <span class="rv-lbl-top">Mínimo</span>
                        <span class="rv-price-bot">${{ number_format($low) }}</span>
                    </div>

                    {{-- dot MEDIO --}}
                    <div class="rv-dot" style="left:{{ $pMid }}%;">
                        <span class="rv-lbl-top">Medio</span>
                        <span class="rv-price-bot">${{ number_format($mid) }}</span>
                    </div>

                    {{-- dot MÁXIMO --}}
                    <div class="rv-dot" style="left:{{ $pHigh }}%;">
                        <span class="rv-lbl-top">Máximo</span>
                        <span class="rv-price-bot">${{ number_format($high) }}</span>
                    </div>

                    {{-- dot SUGERIDO (accent) --}}
                    <div class="rv-dot rv-dot-accent" style="left:{{ $pSug }}%;">
                        <span class="rv-lbl-top rv-lbl-top-accent">▲ Sugerido</span>
                        <span class="rv-price-bot rv-price-bot-accent">${{ number_format($sug) }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ZONA + CONSIDERACIONES --}}
        <div class="zone-split">
            <div class="zone-map-wrap">
                @if($mapUrl)
                    <img src="{{ $mapUrl }}" alt="Zona {{ $colonia }}">
                @else
                    <div class="zone-placeholder">
                        <div class="zone-placeholder-dot"></div>
                        <div class="zone-placeholder-name">{{ $colonia }}</div>
                        <div class="zone-placeholder-sub">{{ $zone }} · Benito Juárez · CDMX</div>
                    </div>
                @endif
            </div>
            <div class="zone-notes">
                <div class="zone-notes-title">A considerar en esta valuación</div>
                @foreach($considerations as $note)
                <div class="zone-note">
                    <div class="zone-note-dot"></div>
                    <div class="zone-note-txt">{{ $note }}</div>
                </div>
                @endforeach
            </div>
        </div>

    </div>{{-- /body --}}

    <div class="ft">
        <div class="ft-l">{{ $siteUrl }}</div>
        <div class="ft-c">Home del Valle · Opinión de Valor</div>
        <div class="ft-r">{{ $folio }} · Página 1 de 3 · Confidencial</div>
    </div>

</div>{{-- /page-1 --}}


{{-- ═══════════════════════════════════════════════════════════════════════════
     PÁGINA 2 — Análisis técnico: características + waterfall
     ═══════════════════════════════════════════════════════════════════════════ --}}
<div class="page page-break">

    <div class="mhd">
        <div class="mhd-logo">
            @if($logoSrcLight)
                <img src="{{ $logoSrcLight }}" alt="{{ $siteName }}">
            @elseif($logoSrc)
                <img src="{{ $logoSrc }}" alt="{{ $siteName }}">
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

        {{-- DOS COLUMNAS --}}
        <div class="two-col">
            <div class="col-left">
                <div class="col-hd">Características del Inmueble</div>
                <table class="spec-tbl">
                    <tr><td>Colonia</td><td>{{ $colonia }}</td></tr>
                    <tr><td>Zona</td><td>{{ $zone }}, Benito Juárez</td></tr>
                    <tr><td>Tipo</td><td>{{ $typeLabel }}</td></tr>
                    <tr><td>Antigüedad</td><td>{{ $valuation->input_age_years }} años · {{ $ageLabel }}</td></tr>
                    <tr><td>Conservación</td><td>{{ $condLabel }}</td></tr>
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
                        $valuation->input_has_rooftop     ? 'Rooftop'          : null,
                        $valuation->input_has_balcony      ? 'Balcón'           : null,
                        $valuation->input_has_service_room ? 'Cuarto servicio'  : null,
                        $valuation->input_has_storage      ? 'Bodega'           : null,
                    ]));
                    @endphp
                    @if($amenList)
                    <tr><td>Amenidades</td><td>{{ $amenList }}</td></tr>
                    @endif
                    @if($valuation->input_type === 'apartment')
                        @if($valuation->input_unit_position)
                        <tr><td>Posición</td><td>{{ $valuation->input_unit_position === 'exterior' ? 'Exterior' : 'Interior' }}</td></tr>
                        @endif
                        @if($valuation->input_orientation)
                        <tr><td>Orientación</td><td>{{ ucfirst($valuation->input_orientation) }}</td></tr>
                        @endif
                        @if($valuation->input_seismic_status && $valuation->input_seismic_status !== 'none')
                        <tr><td>Historial sísmico</td><td>{{ match($valuation->input_seismic_status) {
                            'damaged_repaired'   => 'Daño reparado',
                            'damaged_reinforced' => 'Daño reforzado',
                            'unknown'            => 'Desconocido',
                            default              => '—',
                        } }}</td></tr>
                        @endif
                    @endif
                </table>
            </div>

            <div class="col-right">
                <div class="col-hd">Resumen de Ajustes</div>
                <table class="wfc">
                    <tr class="r-base">
                        <td>Precio base de zona</td>
                        <td>${{ number_format($valuation->base_price_m2) }}/m²</td>
                    </tr>
                    @foreach($valuation->adjustments as $adj)
                    @php $v = (float)$adj->adjustment_value; @endphp
                    <tr>
                        <td>{{ $adj->factor_label }}</td>
                        <td class="{{ $v > 0 ? 'pct-pos' : ($v < 0 ? 'pct-neg' : '') }}">{{ $adj->formatted_value }}</td>
                    </tr>
                    @endforeach
                    <tr class="r-total">
                        <td>Precio ajustado /m²</td>
                        <td>${{ number_format($valuation->adjusted_price_m2) }}/m²</td>
                    </tr>
                </table>

                @if($sug)
                <div class="total-box">
                    <div class="total-box-lbl">Valor total estimado</div>
                    <div class="total-box-amt">${{ number_format($sug) }}</div>
                    <div class="total-box-rng">Rango: ${{ number_format($low) }} — ${{ number_format($high) }}</div>
                </div>
                @endif

                <div class="valid-note">
                    <strong>Vigencia:</strong> 90 días — vence {{ $validity }}<br>
                    No constituye avalúo formal (INDAABIN / SHF / AMPI).
                </div>
            </div>
        </div>

        {{-- WATERFALL DETALLADO --}}
        @if($valuation->adjustments->isNotEmpty())
        <div class="sec-lbl">Factores de Ajuste — Metodología Waterfall</div>
        <table class="wf">
            <thead>
                <tr>
                    <th style="width:33%;">Factor</th>
                    <th>Impacto</th>
                    <th style="width:58px;">Ajuste</th>
                    <th style="width:90px;">Antes</th>
                    <th style="width:90px;">Después</th>
                </tr>
            </thead>
            <tbody>
                <tr class="r-base">
                    <td>
                        Precio base · {{ $colonia }}
                        @if($valuation->snapshot)
                        <div class="wf-sub">{{ $valuation->snapshot->age_label }} · Confianza {{ $confidLabel }}</div>
                        @endif
                    </td>
                    <td><div class="wf-bar wf-bar-neu"></div></td>
                    <td style="color:#9CA3AF;">—</td>
                    <td style="color:#9CA3AF;">—</td>
                    <td>${{ number_format($valuation->base_price_m2) }}/m²</td>
                </tr>
                @php $maxAbs = $valuation->adjustments->max(fn($a) => abs($a->adjustment_value)) ?: 1; @endphp
                @foreach($valuation->adjustments as $adj)
                @php
                    $v    = (float)$adj->adjustment_value;
                    $isN  = $adj->is_neutral;
                    $isP  = $adj->is_positive;
                    $bw   = $isN ? 100 : min(100, round(abs($v) / $maxAbs * 100));
                    $bCls = $isN ? 'wf-bar-neu' : ($isP ? 'wf-bar-pos' : 'wf-bar-neg');
                    $tCls = $isN ? '' : ($isP ? 'pct-pos' : 'pct-neg');
                @endphp
                <tr>
                    <td>
                        {{ $adj->factor_label }}
                        @if($adj->explanation)
                        <div class="wf-sub">{{ $adj->explanation }}</div>
                        @endif
                    </td>
                    <td class="wf-bar-wrap"><div class="wf-bar {{ $bCls }}" style="width:{{ $bw }}%;"></div></td>
                    <td class="{{ $tCls }}">{{ $adj->formatted_value }}</td>
                    <td style="color:#9CA3AF;font-size:10px;">${{ number_format($adj->price_before) }}/m²</td>
                    <td>${{ number_format($adj->price_after) }}/m²</td>
                </tr>
                @endforeach
                @php $tPct = round((($valuation->adjusted_price_m2 - $valuation->base_price_m2) / $valuation->base_price_m2) * 100, 1); @endphp
                <tr class="r-total">
                    <td>Precio ajustado final</td>
                    <td></td>
                    <td class="{{ $tPct >= 0 ? 'pct-pos' : 'pct-neg' }}">{{ ($tPct >= 0 ? '+' : '') . $tPct }}%</td>
                    <td></td>
                    <td>${{ number_format($valuation->adjusted_price_m2) }}/m²</td>
                </tr>
            </tbody>
        </table>
        @endif

    </div>{{-- /p2-body --}}

    <div class="ft2">
        <div class="ft2-top">
            <div class="ft2-brand">{{ $siteName }} — {{ $siteUrl }}</div>
            <div class="ft2-page">{{ $folio }} &nbsp;|&nbsp; Página 2 de 3 &nbsp;|&nbsp; Confidencial</div>
        </div>
    </div>

</div>{{-- /page-2 --}}


{{-- ═══════════════════════════════════════════════════════════════════════════
     PÁGINA 3 — Análisis de mercado
     ═══════════════════════════════════════════════════════════════════════════ --}}
<div class="page">

    <div class="mhd">
        <div class="mhd-logo">
            @if($logoSrcLight)
                <img src="{{ $logoSrcLight }}" alt="{{ $siteName }}">
            @elseif($logoSrc)
                <img src="{{ $logoSrc }}" alt="{{ $siteName }}">
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

        <div class="sec-lbl">Análisis Profesional de Mercado · IA</div>

        @if(!empty($n['market_context']))
        <p class="narr-lead">{{ $n['market_context'] }}</p>
        @endif

        @if(!empty($n['property_strengths']) || !empty($n['property_risks']))
        <div class="str-risk">
            @if(!empty($n['property_strengths']))
            <div class="sr-card sr-card-pos">
                <div class="sr-eyebrow sr-eyebrow-pos">Fortalezas del inmueble</div>
                <div class="sr-txt">{{ $n['property_strengths'] }}</div>
            </div>
            @endif
            @if(!empty($n['property_risks']))
            <div class="sr-card sr-card-neg">
                <div class="sr-eyebrow sr-eyebrow-neg">Riesgo principal</div>
                <div class="sr-txt">{{ $n['property_risks'] }}</div>
            </div>
            @endif
        </div>
        @endif

        @if(!empty($n['recommendation']))
        <div class="rec-box">
            <div class="rec-eyebrow">Recomendación Comercial</div>
            <div class="rec-txt">{{ $n['recommendation'] }}</div>
        </div>
        @endif

        @if(!empty($n['key_factors']) && is_array($n['key_factors']))
        <div class="pills-wrap">
            <div class="pills-lbl">Factores clave identificados</div>
            @foreach($n['key_factors'] as $f)
                <span class="pill">{{ $f }}</span>
            @endforeach
        </div>
        @endif

        <hr class="rule">

        @else

        {{-- FALLBACK sin IA --}}
        <div class="sec-lbl">Recomendación Comercial</div>
        <div class="rec-box">
            <div class="rec-eyebrow">Estrategia de salida al mercado</div>
            <div class="rec-txt">
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

    <div class="ft2">
        <div class="ft2-top">
            <div class="ft2-brand">{{ $siteName }} — {{ $siteUrl }}</div>
            <div class="ft2-page">{{ $folio }} &nbsp;|&nbsp; Página 3 de 3 &nbsp;|&nbsp; Confidencial</div>
        </div>
        <div class="ft2-legal">
            Esta Opinión de Valor es elaborada por {{ $siteName }} con base en datos de oferta publicada en portales inmobiliarios y ajustes estadísticos descritos en este documento.
            <strong style="color:rgba(255,255,255,.4);">No constituye un avalúo formal</strong> con efectos fiscales, notariales o de crédito hipotecario.
            Para dichos efectos se requiere valuador certificado (INDAABIN / SHF / AMPI). El valor de cierre depende de las condiciones de cada negociación.
            &nbsp;·&nbsp; &copy; {{ now()->year }} {{ $siteName }} · Todos los derechos reservados.
        </div>
    </div>

</div>{{-- /page-3 --}}

</body>
</html>
