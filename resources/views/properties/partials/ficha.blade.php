@php
// ─── Defaults & Config ────────────────────────────────────────────────────────
$siteName      = $siteName    ?? 'Home del Valle';
$mode          = $mode        ?? 'pdf';
$siteSetting   = $siteSetting ?? null;
$includeBroker = $includeBroker ?? (bool) ($property->broker_id && $property->broker);
$broker        = $includeBroker ? $property->broker : null;

$contactPhone  = $siteSetting?->contact_phone     ?? $siteSetting?->whatsapp_number ?? '+52 55 0000 0000';
$contactEmail  = $siteSetting?->contact_email     ?? 'info@homedelvalle.mx';
$contactAddr   = $siteSetting?->address           ?? 'Ciudad de México';
$siteUrl       = 'www.homedelvalle.mx';

$typeLabels = [
    'House' => 'Casa', 'Apartment' => 'Departamento', 'Land' => 'Terreno',
    'Office' => 'Oficina', 'Commercial' => 'Comercial',
    'Warehouse' => 'Bodega', 'Building' => 'Edificio',
];
$opLabels = [
    'sale' => 'Venta', 'rental' => 'Renta', 'temporary_rental' => 'Renta Temporal',
];
$statusLabels = [
    'available' => 'Disponible', 'sold' => 'Vendido',
    'rented' => 'Rentado', 'reserved' => 'Reservado',
];
$amenityLabels = [
    'pool'            => 'Alberca',        'gym'             => 'Gimnasio',
    'garden'          => 'Jardín',         'terrace'         => 'Terraza',
    'security'        => 'Seguridad 24/7', 'elevator'        => 'Elevador',
    'rooftop'         => 'Rooftop',        'bbq'             => 'Área de BBQ',
    'playground'      => 'Área de juegos', 'pet_friendly'    => 'Pet Friendly',
    'laundry'         => 'Lavandería',     'storage'         => 'Bodega',
    'concierge'       => 'Concierge',      'business_center' => 'Business Center',
    'cinema'          => 'Sala de Cine',   'spa'             => 'Spa',
    'jacuzzi'         => 'Jacuzzi',        'sauna'           => 'Sauna',
    'tennis'          => 'Cancha de Tenis','paddle'          => 'Cancha de Pádel',
    'co_working'      => 'Co-working',
];

$imgSrc = function (?string $path) use ($mode): ?string {
    if (empty($path)) return null;
    if ($mode === 'email') return asset('storage/' . $path);
    $full = storage_path('app/public/' . $path);
    if (!file_exists($full)) return null;
    $mime = mime_content_type($full) ?: 'image/jpeg';
    return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($full));
};

// ─── Logo ─────────────────────────────────────────────────────────────────────
$logoSrc      = null;
$logoSrcLight = null;
if ($siteSetting?->logo_path) {
    $logoSrc = $imgSrc($siteSetting->logo_path);
}
if (!$logoSrc) {
    $localLogo = public_path('images/logo-homedelvalle.png');
    if (file_exists($localLogo)) {
        $mime    = mime_content_type($localLogo) ?: 'image/png';
        $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($localLogo));
    }
}
if ($siteSetting?->logo_path_dark) {
    $lp2 = storage_path('app/public/' . $siteSetting->logo_path_dark);
    if (file_exists($lp2)) {
        $m2 = mime_content_type($lp2) ?: 'image/png';
        $logoSrcLight = 'data:' . $m2 . ';base64,' . base64_encode(file_get_contents($lp2));
    }
}

// ─── Inter font ───────────────────────────────────────────────────────────────
$fontInterPath = resource_path('fonts/inter-latin.woff2');
$b64Inter = ($mode === 'pdf' && file_exists($fontInterPath))
    ? base64_encode(file_get_contents($fontInterPath))
    : null;

// ─── Images ───────────────────────────────────────────────────────────────────
$primaryPhoto  = $property->primaryPhoto();
$heroSrc       = $imgSrc($primaryPhoto?->path ?? $property->photo);

$galleryPhotos = $property->photos
    ->reject(fn($p) => $p->is_primary)
    ->filter(function($p) use ($mode) {
        if ($mode === 'email') return true;
        $full = storage_path('app/public/' . $p->path);
        if (!file_exists($full)) return false;
        [$w, $h] = @getimagesize($full) ?: [0, 0];
        return $w > 0 && $w >= $h;
    })
    ->take(9)->values();
$gallerySrcs = $galleryPhotos->map(fn($p) => $imgSrc($p->path))->filter()->values();

$qrSrc = null;
if ($property->qrCode?->qr_code_path) {
    $qrSrc = $imgSrc($property->qrCode->qr_code_path);
}
$brokerPhotoSrc = null;
if ($broker?->photo) {
    $brokerPhotoSrc = $imgSrc($broker->photo);
}

// ─── Property data ────────────────────────────────────────────────────────────
$propertyType  = $typeLabels[$property->property_type ?? ''] ?? ($property->property_type ?? '');
$operationType = $opLabels[$property->operation_type ?? '']  ?? ($property->operation_type ?? '');
$statusLabel   = $statusLabels[$property->status ?? '']      ?? ($property->status ?? '');

$locationParts = array_filter([$property->colony, $property->city,
    $property->zipcode ? 'C.P. ' . $property->zipcode : null]);
$locationStr = implode(', ', $locationParts);

$features = [];
if ($property->lot_area > 0)          $features[] = ['v' => number_format((float)$property->lot_area, 0),          'l' => 'm² Terreno'];
if ($property->construction_area > 0) $features[] = ['v' => number_format((float)$property->construction_area, 0), 'l' => 'm² Construidos'];
if ($property->area > 0 && !$property->lot_area && !$property->construction_area)
                                       $features[] = ['v' => number_format((float)$property->area, 0),              'l' => 'm²'];
if ($property->bedrooms > 0)          $features[] = ['v' => $property->bedrooms, 'l' => 'Recámaras'];
if ($property->bathrooms > 0) {
    $bv = $property->bathrooms . ($property->half_bathrooms ? '+' . $property->half_bathrooms . 'm' : '');
    $features[] = ['v' => $bv, 'l' => 'Baños'];
}
if ($property->parking > 0)           $features[] = ['v' => $property->parking, 'l' => 'Cajones'];

$specs = [];
if ($property->lot_area > 0)          $specs['Terreno']           = number_format((float)$property->lot_area, 0) . ' m²';
if ($property->construction_area > 0) $specs['Construcción']      = number_format((float)$property->construction_area, 0) . ' m²';
if ($property->area > 0)              $specs['Superficie total']  = number_format((float)$property->area, 0) . ' m²';
if ($property->bedrooms > 0)          $specs['Recámaras']         = $property->bedrooms;
if ($property->bathrooms > 0)         $specs['Baños completos']   = $property->bathrooms;
if ($property->half_bathrooms > 0)    $specs['Medios baños']      = $property->half_bathrooms;
if ($property->parking > 0)           $specs['Estacionamientos']  = $property->parking;
if ($property->floors > 0)            $specs['Niveles']           = $property->floors;
if ($property->year_built > 0)        $specs['Año construcción']  = $property->year_built;
if ($property->maintenance_fee > 0)   $specs['Mantenimiento']     = '$' . number_format((float)$property->maintenance_fee, 0) . '/mes';
if ($property->furnished)             $specs['Amueblado']         = 'Sí';
if ($property->status)                $specs['Estatus']           = $statusLabel;

$amenities = $property->amenities ?? [];
$ref        = str_pad($property->id, 5, '0', STR_PAD_LEFT);
$today      = now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>{{ $property->title }} — {{ $siteName }}</title>
<style>
/* ── FONT ──────────────────────────────────────────────────────────────────── */
@if($b64Inter)
@font-face {
    font-family: 'Inter';
    font-style: normal;
    font-weight: 100 900;
    font-display: swap;
    src: url('data:font/woff2;base64,{{ $b64Inter }}') format('woff2');
}
@endif

/* ── RESET ──────────────────────────────────────────────────────────────────── */
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
@page { size: A4 portrait; margin: 0; }
html, body {
    font-family: 'Inter', -apple-system, Arial, sans-serif;
    font-size: 12px;
    color: #111827;
    background: #fff;
    line-height: 1.55;
    -webkit-font-smoothing: antialiased;
}

/* ── DESIGN TOKENS ───────────────────────────────────────────────────────────
   #0C1A2E  navy        footer bg, títulos primarios
   #1D4ED8  azul        acento primario (mismo que valuación)
   #EFF6FF  azul-50     fondos de acento
   #F9FAFB  gris-50     fondos alternos
   #E5E7EB  gris-200    divisores
   #6B7280  gris-500    texto secundario
   #111827  gris-900    texto principal
───────────────────────────────────────────────────────────────────────────── */

/* ── PAGE LAYOUT (CSS Grid) ─────────────────────────────────────────────────── */
.page {
    width: 100%;
    height: 297mm;
    display: grid;
    grid-template-rows: auto 1fr auto;
    background: #fff;
    overflow: hidden;
}
.page-break { break-after: page; page-break-after: always; }

/* ── HEADER ─────────────────────────────────────────────────────────────────── */
.hd {
    padding: 16px 48px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    border-bottom: 2px solid #1D4ED8;
}
.hd-logo { flex-shrink: 0; }
.hd-logo img { height: 32px; width: auto; display: block; }
.hd-logo-txt { font-size: 14px; font-weight: 800; color: #0C1A2E; }
.hd-logo-sub { font-size: 8px; text-transform: uppercase; letter-spacing: 2px; color: #9CA3AF; margin-top: 4px; }
.hd-center { flex: 1; text-align: center; }
.hd-eyebrow { font-size: 8px; text-transform: uppercase; letter-spacing: 4px; color: #9CA3AF; font-weight: 600; margin-bottom: 3px; }
.hd-title { font-size: 14px; font-weight: 800; color: #0C1A2E; letter-spacing: -0.2px; line-height: 1.3; }
.hd-right { text-align: right; flex-shrink: 0; }
.hd-ref-lbl { font-size: 8px; text-transform: uppercase; letter-spacing: 2px; color: #9CA3AF; }
.hd-ref { font-size: 13px; font-weight: 800; color: #0C1A2E; margin-top: 1px; font-feature-settings: "tnum"; }
.hd-date { font-size: 10px; color: #6B7280; margin-top: 3px; }

/* ── MINI HEADER (pág 2) ────────────────────────────────────────────────────── */
.mhd {
    padding: 11px 48px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 2px solid #1D4ED8;
    background: #F9FAFB;
}
.mhd-logo img { height: 20px; width: auto; display: block; }
.mhd-logo-txt { font-size: 12px; font-weight: 800; color: #0C1A2E; }
.mhd-right { text-align: right; }
.mhd-ref { font-size: 8px; color: #9CA3AF; text-transform: uppercase; letter-spacing: 1.5px; }
.mhd-section { font-size: 10px; font-weight: 700; color: #1D4ED8; text-transform: uppercase; letter-spacing: 1.5px; margin-top: 2px; }

/* ── HERO (pág 1 — ocupa toda la fila 1fr) ──────────────────────────────────── */
.hero-wrap {
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.hero {
    width: 100%;
    height: 76mm;
    flex-shrink: 0;
    overflow: hidden;
    background: #1A2F4E;
}
.hero img { width: 100%; height: 100%; object-fit: cover; object-position: center; display: block; }
.hero-empty {
    width: 100%; height: 76mm;
    background: linear-gradient(160deg, #1A2F4E 0%, #0C1A2E 100%);
    display: flex; align-items: center; justify-content: center;
    font-size: 10px; color: rgba(255,255,255,0.2);
    text-transform: uppercase; letter-spacing: 4px;
    flex-shrink: 0;
}

/* ── PRICE SECTION (debajo del hero) ────────────────────────────────────────── */
.price-section {
    padding: 16px 48px 14px;
    border-bottom: 1px solid #E5E7EB;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 20px;
    flex-shrink: 0;
}
.price-eyebrow {
    font-size: 8px;
    text-transform: uppercase;
    letter-spacing: 3.5px;
    color: #1D4ED8;
    font-weight: 700;
    margin-bottom: 5px;
}
.price-num {
    font-size: 46px;
    font-weight: 900;
    color: #0C1A2E;
    letter-spacing: -3px;
    line-height: 1;
    font-feature-settings: "tnum";
    white-space: nowrap;
}
.price-cur {
    font-size: 20px;
    font-weight: 400;
    color: #9CA3AF;
    vertical-align: top;
    line-height: 46px;
    margin-right: 2px;
}
.price-currency-lbl {
    font-size: 11px;
    color: #9CA3AF;
    font-weight: 500;
    letter-spacing: 1px;
    margin-left: 8px;
    vertical-align: middle;
}
.price-sub { margin-top: 6px; font-size: 11px; color: #6B7280; }
.price-right { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; flex-shrink: 0; }
.op-badge {
    display: inline-block;
    padding: 5px 16px;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 2px;
    background: #1D4ED8;
    color: #fff;
    border-radius: 2px;
}
.status-badge {
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #15803D;
    background: #DCFCE7;
    border: 1px solid #86EFAC;
    padding: 3px 10px;
    border-radius: 2px;
}

/* ── PROPERTY IDENTITY ──────────────────────────────────────────────────────── */
.identity {
    padding: 12px 48px 10px;
    border-bottom: 1px solid #E5E7EB;
    flex-shrink: 0;
}
.prop-title {
    font-size: 16px;
    font-weight: 800;
    color: #0C1A2E;
    letter-spacing: -0.3px;
    line-height: 1.25;
    margin-bottom: 6px;
}
.prop-location { font-size: 11px; color: #6B7280; margin-bottom: 8px; line-height: 1.5; }
.prop-chips { display: flex; gap: 6px; flex-wrap: wrap; }
.prop-chip {
    font-size: 9px;
    font-weight: 600;
    color: #374151;
    background: #F9FAFB;
    border: 1px solid #E5E7EB;
    border-radius: 2px;
    padding: 2px 9px;
}

/* ── STATS BAR ──────────────────────────────────────────────────────────────── */
.stats-row {
    display: flex;
    gap: 0;
    padding: 0 48px;
    flex-shrink: 0;
}
.kpi {
    flex: 1;
    padding: 12px 16px;
    border-top: 3px solid #1D4ED8;
    background: #F9FAFB;
    border-right: 1px solid #E5E7EB;
    text-align: center;
}
.kpi:last-child { border-right: none; }
.kpi-v {
    display: block;
    font-size: 22px;
    font-weight: 900;
    color: #0C1A2E;
    letter-spacing: -0.5px;
    line-height: 1;
    font-feature-settings: "tnum";
}
.kpi-l {
    display: block;
    font-size: 7.5px;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    color: #9CA3AF;
    font-weight: 600;
    margin-top: 5px;
}

/* ── FOOTER PÁG 1 ───────────────────────────────────────────────────────────── */
.ft1 {
    background: #0C1A2E;
    padding: 7px 48px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 2px solid #1D4ED8;
}
.ft1-l { font-size: 8.5px; color: rgba(255,255,255,.3); text-transform: uppercase; letter-spacing: 2px; }
.ft1-c { font-size: 8.5px; color: rgba(255,255,255,.55); font-weight: 700; text-transform: uppercase; letter-spacing: 2px; }
.ft1-r { font-size: 8.5px; color: rgba(255,255,255,.3); }

/* ── PÁG 2 BODY ──────────────────────────────────────────────────────────────── */
.p2-body {
    padding: 18px 48px 14px;
    overflow: hidden;
}

/* ── SECTION LABEL ──────────────────────────────────────────────────────────── */
.sec-lbl {
    font-size: 8px;
    font-weight: 700;
    color: #1D4ED8;
    text-transform: uppercase;
    letter-spacing: 3.5px;
    padding-bottom: 7px;
    border-bottom: 2px solid #1D4ED8;
    margin-bottom: 10px;
}

/* ── DESCRIPTION ────────────────────────────────────────────────────────────── */
.desc { font-size: 12px; color: #374151; line-height: 1.8; text-align: justify; margin-bottom: 14px; }

/* ── SPECS + AMENITIES ──────────────────────────────────────────────────────── */
.two-col { display: flex; gap: 28px; margin-bottom: 14px; }
.col-specs { flex: 1; min-width: 0; }
.col-amen  { width: 42%; flex-shrink: 0; }
.col-divider { border-left: 1px solid #E5E7EB; }

.spec-tbl { width: 100%; border-collapse: collapse; }
.spec-tbl tr td {
    padding: 4px 0;
    border-bottom: 1px solid #F3F4F6;
    font-size: 11.5px;
    vertical-align: middle;
}
.spec-tbl tr td:first-child {
    font-size: 9px;
    color: #6B7280;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    font-weight: 600;
    width: 52%;
}
.spec-tbl tr td:last-child { font-weight: 700; color: #0C1A2E; text-align: right; }

.amen-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 4px 0;
    border-bottom: 1px solid #F3F4F6;
    font-size: 11.5px;
    color: #374151;
}
.amen-item:last-child { border-bottom: none; }
.amen-dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: #1D4ED8;
    flex-shrink: 0;
}

/* ── GALLERY ─────────────────────────────────────────────────────────────────── */
.gal-grid {
    display: flex;
    flex-direction: column;
    gap: 4px;
    margin-bottom: 14px;
}
.gal-row { display: flex; gap: 4px; }
.gal-cell {
    flex: 1;
    height: 70px;
    overflow: hidden;
    background: #E8ECF0;
}
.gal-cell img { width: 100%; height: 100%; object-fit: cover; object-position: center; display: block; }
.gal-empty { width: 100%; height: 100%; background: #F3F4F6; }

/* ── BROKER CARD ─────────────────────────────────────────────────────────────── */
.broker-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 12px 16px;
    border: 1px solid #E5E7EB;
    border-left: 3px solid #1D4ED8;
    background: #F9FAFB;
    border-radius: 0 2px 2px 0;
    margin-bottom: 14px;
}
.broker-avatar {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    background: linear-gradient(135deg, #1A2F4E 0%, #1D4ED8 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}
.broker-avatar img { width: 52px; height: 52px; object-fit: cover; display: block; border-radius: 50%; }
.broker-init { font-size: 20px; font-weight: 800; color: #fff; }
.broker-info { flex: 1; }
.broker-eyebrow { font-size: 7.5px; color: #1D4ED8; text-transform: uppercase; letter-spacing: 2px; font-weight: 700; margin-bottom: 2px; }
.broker-name { font-size: 14px; font-weight: 800; color: #0C1A2E; line-height: 1.2; margin-bottom: 2px; }
.broker-role { font-size: 9px; color: #6B7280; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; }
.broker-contacts { display: flex; gap: 16px; }
.broker-contact-item { font-size: 10.5px; color: #374151; }
.broker-contact-lbl { font-size: 7.5px; color: #9CA3AF; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; display: block; margin-bottom: 1px; }

/* ── CONTACT STRIP ───────────────────────────────────────────────────────────── */
.contact-strip {
    display: flex;
    gap: 0;
    border: 1px solid #E5E7EB;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 14px;
}
.contact-item {
    flex: 1;
    padding: 10px 14px;
    border-right: 1px solid #E5E7EB;
}
.contact-item:last-child { border-right: none; }
.contact-lbl { font-size: 7.5px; text-transform: uppercase; letter-spacing: 1.2px; color: #9CA3AF; font-weight: 700; margin-bottom: 3px; }
.contact-val { font-size: 11.5px; font-weight: 700; color: #0C1A2E; }

/* QR en la franja de contacto */
.contact-qr-wrap { display: flex; gap: 0; border: 1px solid #E5E7EB; border-radius: 4px; overflow: hidden; margin-bottom: 14px; }
.contact-qr-info { flex: 1; display: flex; flex-wrap: wrap; }
.contact-qr-info .contact-item { border-bottom: none; }
.contact-qr-box {
    width: 110px;
    flex-shrink: 0;
    padding: 10px 14px;
    border-left: 1px solid #E5E7EB;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: #F9FAFB;
}
.qr-img { width: 76px; height: 76px; border: 1px solid #E5E7EB; padding: 3px; background: #fff; }
.qr-img img { width: 70px; height: 70px; display: block; }
.qr-cta { font-size: 7px; color: #1D4ED8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; margin-top: 5px; text-align: center; }

/* ── FOOTER PÁG 2 ───────────────────────────────────────────────────────────── */
.ft2 {
    background: #0C1A2E;
    padding: 7px 48px;
    border-top: 2px solid #1D4ED8;
}
.ft2-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; }
.ft2-brand { font-size: 8.5px; color: rgba(255,255,255,.55); font-weight: 700; text-transform: uppercase; letter-spacing: 2px; }
.ft2-ref { font-size: 8px; color: rgba(255,255,255,.3); }
.ft2-legal { font-size: 7.5px; color: rgba(255,255,255,.22); line-height: 1.65; }

.rule { border: none; border-top: 1px solid #E5E7EB; margin: 12px 0; }

@media print {
    * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
}
</style>
</head>
<body>

{{-- ═══════════════════════════════════════════════════════════════════════════
     PÁGINA 1 — Portada visual
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
            <div class="hd-eyebrow">Ficha Técnica</div>
            <div class="hd-title">{{ $propertyType ?: 'Propiedad' }}@if($operationType) · {{ $operationType }}@endif</div>
        </div>
        <div class="hd-right">
            <div class="hd-ref-lbl">Referencia</div>
            <div class="hd-ref">REF. #{{ $ref }}</div>
            <div class="hd-date">{{ $today }}</div>
        </div>
    </div>

    {{-- HERO + BODY --}}
    <div class="hero-wrap">

        {{-- Hero image --}}
        @if($heroSrc)
            <div class="hero"><img src="{{ $heroSrc }}" alt="{{ $property->title }}"></div>
        @else
            <div class="hero-empty">Sin imagen disponible</div>
        @endif

        {{-- Price section --}}
        <div class="price-section">
            <div>
                <div class="price-eyebrow">Precio de {{ $operationType ?: 'Venta' }}</div>
                <div class="price-num">
                    <span class="price-cur">{{ $property->currency ?? '$' }}</span>{{ $property->formatted_price }}<span class="price-currency-lbl">{{ $property->currency === 'USD' ? 'USD' : 'MXN' }}</span>
                </div>
                @if($property->maintenance_fee > 0)
                <div class="price-sub">Mantenimiento: ${{ number_format((float)$property->maintenance_fee, 0) }}/mes</div>
                @endif
            </div>
            <div class="price-right">
                @if($operationType)
                <span class="op-badge">{{ strtoupper($operationType) }}</span>
                @endif
                @if($statusLabel && $property->status !== 'available')
                <span class="status-badge">{{ $statusLabel }}</span>
                @endif
            </div>
        </div>

        {{-- Property identity --}}
        <div class="identity">
            <div class="prop-title">{{ $property->title }}</div>
            @if($locationStr || $property->address)
            <div class="prop-location">
                {{ implode(' · ', array_filter([$property->address, $locationStr])) }}
            </div>
            @endif
            <div class="prop-chips">
                @if($propertyType)<span class="prop-chip">{{ $propertyType }}</span>@endif
                @if($property->colony)<span class="prop-chip">{{ $property->colony }}</span>@endif
                @if($property->city)<span class="prop-chip">{{ $property->city }}</span>@endif
                @if($property->year_built > 0)<span class="prop-chip">Año {{ $property->year_built }}</span>@endif
                @if($property->furnished)<span class="prop-chip">Amueblado</span>@endif
            </div>
        </div>

        {{-- Stats KPIs --}}
        @if(count($features) > 0)
        <div class="stats-row">
            @foreach($features as $f)
            <div class="kpi">
                <span class="kpi-v">{{ $f['v'] }}</span>
                <span class="kpi-l">{{ $f['l'] }}</span>
            </div>
            @endforeach
        </div>
        @endif

    </div>{{-- /hero-wrap --}}

    {{-- FOOTER --}}
    <div class="ft1">
        <div class="ft1-l">{{ $siteUrl }}</div>
        <div class="ft1-c">Home del Valle · Ficha Técnica</div>
        <div class="ft1-r">REF. #{{ $ref }} · Pág. 1 de 2 · Confidencial</div>
    </div>

</div>{{-- /page-1 --}}


{{-- ═══════════════════════════════════════════════════════════════════════════
     PÁGINA 2 — Descripción · Especificaciones · Galería · Contacto
     ═══════════════════════════════════════════════════════════════════════════ --}}
<div class="page">

    {{-- MINI HEADER --}}
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
            <div class="mhd-ref">REF. #{{ $ref }}</div>
            <div class="mhd-section">Especificaciones</div>
        </div>
    </div>

    <div class="p2-body">

        {{-- DESCRIPCIÓN --}}
        @if($property->description)
        <div class="sec-lbl">Descripción Comercial</div>
        <p class="desc">{{ \Illuminate\Support\Str::limit(strip_tags($property->description), 520) }}</p>
        @endif

        {{-- ESPECIFICACIONES + AMENIDADES --}}
        @if(count($specs) > 0 || count($amenities) > 0)
        <div class="two-col">
            @if(count($specs) > 0)
            <div class="col-specs">
                <div class="sec-lbl">Especificaciones Técnicas</div>
                <table class="spec-tbl">
                    @foreach($specs as $k => $v)
                    <tr><td>{{ $k }}</td><td>{{ $v }}</td></tr>
                    @endforeach
                </table>
            </div>
            @endif
            @if(count($amenities) > 0)
            <div class="col-amen col-divider" style="padding-left: 28px;">
                <div class="sec-lbl">Amenidades</div>
                @foreach($amenities as $a)
                @php $al = $amenityLabels[$a] ?? ucfirst(str_replace('_', ' ', $a)); @endphp
                <div class="amen-item">
                    <div class="amen-dot"></div>
                    {{ $al }}
                </div>
                @endforeach
            </div>
            @endif
        </div>
        @endif

        {{-- GALERÍA --}}
        @if($gallerySrcs->count() > 0)
        <div class="sec-lbl">Galería de Imágenes</div>
        <div class="gal-grid">
            @foreach($gallerySrcs->chunk(3) as $row)
            <div class="gal-row">
                @foreach($row as $src)
                <div class="gal-cell"><img src="{{ $src }}" alt="Propiedad"></div>
                @endforeach
                @for($i = $row->count(); $i < 3; $i++)
                <div class="gal-cell"><div class="gal-empty"></div></div>
                @endfor
            </div>
            @endforeach
        </div>
        @endif

        {{-- BROKER --}}
        @if($includeBroker && $broker)
        <div class="broker-card">
            <div class="broker-avatar">
                @if($brokerPhotoSrc)
                    <img src="{{ $brokerPhotoSrc }}" alt="{{ $broker->name }}">
                @else
                    @php $ini = strtoupper(mb_substr($broker->name ?? 'A', 0, 1)); @endphp
                    <div class="broker-init">{{ $ini }}</div>
                @endif
            </div>
            <div class="broker-info">
                <div class="broker-eyebrow">Asesor Inmobiliario</div>
                <div class="broker-name">{{ $broker->name }}</div>
                <div class="broker-role">{{ $broker->specialty ?? $broker->type ?? 'Asesor Inmobiliario' }}</div>
                <div class="broker-contacts">
                    @if($broker->phone)
                    <div class="broker-contact-item">
                        <span class="broker-contact-lbl">Teléfono</span>
                        {{ $broker->phone }}
                    </div>
                    @endif
                    @if($broker->email)
                    <div class="broker-contact-item">
                        <span class="broker-contact-lbl">Correo</span>
                        {{ $broker->email }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- CONTACTO + QR --}}
        <div class="sec-lbl">Información de Contacto</div>
        @if($qrSrc)
        <div class="contact-qr-wrap">
            <div class="contact-qr-info">
                <div class="contact-item">
                    <div class="contact-lbl">Inmobiliaria</div>
                    <div class="contact-val">{{ $siteName }}</div>
                </div>
                @if($contactPhone)
                <div class="contact-item">
                    <div class="contact-lbl">Teléfono</div>
                    <div class="contact-val">{{ $contactPhone }}</div>
                </div>
                @endif
                @if($contactEmail)
                <div class="contact-item">
                    <div class="contact-lbl">Correo</div>
                    <div class="contact-val">{{ $contactEmail }}</div>
                </div>
                @endif
                <div class="contact-item">
                    <div class="contact-lbl">Web</div>
                    <div class="contact-val">{{ $siteUrl }}</div>
                </div>
            </div>
            <div class="contact-qr-box">
                <div class="qr-img"><img src="{{ $qrSrc }}" alt="QR"></div>
                <div class="qr-cta">Ver propiedad online</div>
            </div>
        </div>
        @else
        <div class="contact-strip">
            <div class="contact-item">
                <div class="contact-lbl">Inmobiliaria</div>
                <div class="contact-val">{{ $siteName }}</div>
            </div>
            @if($contactPhone)
            <div class="contact-item">
                <div class="contact-lbl">Teléfono</div>
                <div class="contact-val">{{ $contactPhone }}</div>
            </div>
            @endif
            @if($contactEmail)
            <div class="contact-item">
                <div class="contact-lbl">Correo</div>
                <div class="contact-val">{{ $contactEmail }}</div>
            </div>
            @endif
            <div class="contact-item">
                <div class="contact-lbl">Web</div>
                <div class="contact-val">{{ $siteUrl }}</div>
            </div>
        </div>
        @endif

    </div>{{-- /p2-body --}}

    {{-- FOOTER --}}
    <div class="ft2">
        <div class="ft2-top">
            <div class="ft2-brand">{{ $siteName }} — {{ $siteUrl }}</div>
            <div class="ft2-ref">REF. #{{ $ref }} &nbsp;|&nbsp; Pág. 2 de 2 &nbsp;|&nbsp; Confidencial</div>
        </div>
        <div class="ft2-legal">
            La información contenida en esta ficha técnica es proporcionada únicamente con fines informativos y no constituye una oferta vinculante.
            Los precios, disponibilidades, superficies y características están sujetos a cambios sin previo aviso y deben verificarse con el asesor asignado.
            &nbsp;·&nbsp; &copy; {{ now()->year }} {{ $siteName }}. Todos los derechos reservados.
        </div>
    </div>

</div>{{-- /page-2 --}}

</body>
</html>
