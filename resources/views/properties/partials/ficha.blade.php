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
    'pool'            => 'Alberca',       'gym'             => 'Gimnasio',
    'garden'          => 'Jardin',        'terrace'         => 'Terraza',
    'security'        => 'Seguridad 24/7','elevator'        => 'Elevador',
    'rooftop'         => 'Rooftop',       'bbq'             => 'Area de BBQ',
    'playground'      => 'Area de juegos','pet_friendly'    => 'Pet Friendly',
    'laundry'         => 'Lavanderia',    'storage'         => 'Bodega',
    'concierge'       => 'Concierge',     'business_center' => 'Business Center',
    'cinema'          => 'Sala de Cine',  'spa'             => 'Spa',
    'jacuzzi'         => 'Jacuzzi',       'sauna'           => 'Sauna',
    'tennis'          => 'Cancha de Tenis','paddle'         => 'Cancha de Padel',
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

$primaryPhoto = $property->primaryPhoto();
$heroSrc      = $imgSrc($primaryPhoto?->path ?? $property->photo);

$galleryPhotos = $property->photos->reject(fn($p) => $p->is_primary)->take(9)->values();
$gallerySrcs   = $galleryPhotos->map(fn($p) => $imgSrc($p->path))->filter()->values();

$logoSrc = null;
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

$qrSrc = null;
if ($property->qrCode?->qr_code_path) {
    $qrSrc = $imgSrc($property->qrCode->qr_code_path);
}

$brokerPhotoSrc = null;
if ($broker?->photo) {
    $brokerPhotoSrc = $imgSrc($broker->photo);
}

$propertyType  = $typeLabels[$property->property_type ?? ''] ?? ($property->property_type ?? '');
$operationType = $opLabels[$property->operation_type ?? '']  ?? ($property->operation_type ?? '');
$statusLabel   = $statusLabels[$property->status ?? '']      ?? ($property->status ?? '');

$locationParts = array_filter([
    $property->colony,
    $property->city,
    $property->zipcode ? 'C.P. ' . $property->zipcode : null,
]);
$locationStr = implode(', ', $locationParts);

$features = [];
if ($property->lot_area > 0)          $features[] = ['v' => number_format((float)$property->lot_area, 0),          'l' => 'm² Terreno'];
if ($property->construction_area > 0) $features[] = ['v' => number_format((float)$property->construction_area, 0), 'l' => 'm² Construidos'];
if ($property->area > 0 && !$property->lot_area && !$property->construction_area)
                                       $features[] = ['v' => number_format((float)$property->area, 0),              'l' => 'm²'];
if ($property->bedrooms > 0)          $features[] = ['v' => $property->bedrooms, 'l' => 'Recamaras'];
if ($property->bathrooms > 0) {
    $bv = $property->bathrooms . ($property->half_bathrooms ? ' + ' . $property->half_bathrooms . ' m' : '');
    $features[] = ['v' => $bv, 'l' => 'Banos'];
}
if ($property->parking > 0)           $features[] = ['v' => $property->parking, 'l' => 'Cajones'];

$specs = [];
if ($property->lot_area > 0)          $specs['Terreno']          = number_format((float)$property->lot_area, 0) . ' m²';
if ($property->construction_area > 0) $specs['Construccion']     = number_format((float)$property->construction_area, 0) . ' m²';
if ($property->area > 0)              $specs['Superficie Total']  = number_format((float)$property->area, 0) . ' m²';
if ($property->bedrooms > 0)          $specs['Recamaras']        = $property->bedrooms;
if ($property->bathrooms > 0)         $specs['Banos Completos']  = $property->bathrooms;
if ($property->half_bathrooms > 0)    $specs['Medios Banos']     = $property->half_bathrooms;
if ($property->parking > 0)           $specs['Estacionamientos'] = $property->parking;
if ($property->floors > 0)            $specs['Niveles']          = $property->floors;
if ($property->year_built > 0)        $specs['Ano Construccion'] = $property->year_built;
if ($property->maintenance_fee > 0)   $specs['Mantenimiento']    = '$' . number_format((float)$property->maintenance_fee, 0) . '/mes';
if ($property->furnished)             $specs['Amueblado']        = 'Si';
if ($property->status)                $specs['Estatus']          = $statusLabel;

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
/* ════════════════════════════════════════════════════════════════════════════
   RESET
════════════════════════════════════════════════════════════════════════════ */
* { margin: 0; padding: 0; box-sizing: border-box; }

/* ════════════════════════════════════════════════════════════════════════════
   PAGE  —  A4, margins 12mm top/bottom · 14mm left/right
   Content area: 182 × 273 mm
════════════════════════════════════════════════════════════════════════════ */
@page { size: A4 portrait; margin: 12mm 14mm 12mm 14mm; }

html, body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 10px;
    color: #1A1A1A;
    background: #FFFFFF;
    line-height: 1.5;
}

/* ════════════════════════════════════════════════════════════════════════════
   COLOR PALETTE  —  brand blues only (from app.css @theme)
   brand-950  #0C1A2E   price bar, bottom strip, legal footer bg
   brand-800  #1A2F4E   titles, brand name, body dark
   brand-600  #2563A0   accent line, section labels, markers, borders
   brand-500  #3B82C4   mid blue (available for hover/secondary use)
   brand-50   #F0F7FF   light tint backgrounds
   Neutral    #F8F9FA   stat cell backgrounds
   Divider    #E5E7EB
   Body text  #404040
   Muted      #6B7280 / #9CA3AF
════════════════════════════════════════════════════════════════════════════ */

/* ════════════════════════════════════════════════════════════════════════════
   PAGE CONTAINERS
   .pg1  — cover page, fixed height = content area height, forces page break
   .pg2  — content page, auto height
════════════════════════════════════════════════════════════════════════════ */
.pg1 {
    width: 100%;
    height: 273mm;
    page-break-after: always;
    position: relative;
    overflow: hidden;
}
.pg2 { width: 100%; }

/* ════════════════════════════════════════════════════════════════════════════
   PAGE 1 — COVER HEADER
   Three columns: logo | brand name + tagline | ficha ref + date
════════════════════════════════════════════════════════════════════════════ */
.hdr { padding-bottom: 9px; border-bottom: 1px solid #E0E4EA; }
.hdr-tbl { width: 100%; border-collapse: collapse; }
.hdr-tbl td { vertical-align: middle; padding: 0; }
.hdr-logo { width: 110px; }
.hdr-logo img { height: 30px; width: auto; display: block; }
.hdr-name {
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 17px; font-weight: 700; color: #0C1A2E; letter-spacing: 0.2px; line-height: 1;
}
.hdr-sub { font-size: 6.5px; color: #9CA3AF; text-transform: uppercase; letter-spacing: 2px; margin-top: 3px; }
.hdr-ref-col { text-align: right; }
.hdr-label { font-size: 6.5px; color: #9CA3AF; text-transform: uppercase; letter-spacing: 1.5px; }
.hdr-ref { font-size: 11px; font-weight: 700; color: #0C1A2E; letter-spacing: 0.2px; margin-top: 2px; }
.hdr-date { font-size: 7px; color: #9CA3AF; margin-top: 1px; }

.accent-rule { height: 2px; background: #2563A0; width: 36px; margin: 7px 0 0 0; }

/* ════════════════════════════════════════════════════════════════════════════
   PAGE 1 — HERO IMAGE
   183mm tall — dominant visual; fills ~67% of the page
════════════════════════════════════════════════════════════════════════════ */
.hero { width: 100%; height: 183mm; overflow: hidden; background: #1A2F4E; margin-top: 7px; }
.hero img { width: 100%; height: 183mm; display: block; }
.hero-empty {
    width: 100%; height: 183mm;
    background: linear-gradient(160deg, #1A2F4E 0%, #0C1A2E 100%);
    display: table;
}
.hero-empty-inner {
    display: table-cell; vertical-align: middle; text-align: center;
    font-size: 10px; color: rgba(255,255,255,0.25);
    text-transform: uppercase; letter-spacing: 3px;
}

/* ════════════════════════════════════════════════════════════════════════════
   PAGE 1 — PRICE BAR
   Full-width dark band immediately below hero
════════════════════════════════════════════════════════════════════════════ */
.price-bar { background: #0C1A2E; padding: 9px 14px; }
.price-bar-tbl { width: 100%; border-collapse: collapse; }
.price-bar-tbl td { vertical-align: middle; padding: 0; }
.price-lbl { font-size: 6px; color: rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 2px; margin-bottom: 2px; }
.price-amount {
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 26px; font-weight: 700; color: #FFFFFF; letter-spacing: -0.5px; line-height: 1;
}
.price-currency { font-size: 10px; color: rgba(255,255,255,0.55); vertical-align: top; margin-right: 2px; line-height: 26px; }
.op-badge {
    display: inline-block;
    border: 1.5px solid rgba(255,255,255,0.35); padding: 5px 13px;
    font-size: 7.5px; font-weight: 700; color: #FFFFFF;
    text-transform: uppercase; letter-spacing: 2px;
}

/* ════════════════════════════════════════════════════════════════════════════
   PAGE 1 — PROPERTY IDENTITY
   Title, location, badge tags
════════════════════════════════════════════════════════════════════════════ */
.identity { padding: 12px 0 9px; border-bottom: 1px solid #E0E4EA; }
.prop-title {
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 16px; font-weight: 700; color: #0C1A2E;
    line-height: 1.3; margin-bottom: 4px; letter-spacing: -0.2px;
}
.prop-loc { font-size: 8.5px; color: #707070; margin-bottom: 6px; line-height: 1.4; }
.prop-loc .dash { color: #2563A0; margin-right: 4px; }
.badge {
    display: inline-block; padding: 2px 8px; border-radius: 1px;
    font-size: 7px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; margin-right: 4px;
}
.b-type   { background: #F0F4F8; color: #1A2F4E; }
.b-op     { background: #2563A0; color: #FFFFFF; }
.b-status { background: #DCFCE7; color: #166534; }

/* ════════════════════════════════════════════════════════════════════════════
   PAGE 1 — STATS BAR
   4 equal cells, gold top border, serif numbers
════════════════════════════════════════════════════════════════════════════ */
.stats-tbl { width: 100%; border-collapse: separate; border-spacing: 5px 0; margin-top: 9px; }
.stat-cell { text-align: center; padding: 11px 4px 9px; border-top: 3px solid #2563A0; background: #F8F9FA; }
.stat-v {
    display: block;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 19px; font-weight: 700; color: #0C1A2E; line-height: 1;
}
.stat-l { display: block; font-size: 6.5px; color: #9CA3AF; text-transform: uppercase; letter-spacing: 1px; margin-top: 5px; font-weight: 700; }

/* ════════════════════════════════════════════════════════════════════════════
   PAGE 1 — BOTTOM STRIP
   Anchored to bottom via position:absolute (strictly necessary here)
════════════════════════════════════════════════════════════════════════════ */
.pg1-footer { position: absolute; bottom: 0; left: 0; right: 0; background: #0C1A2E; padding: 7px 0; text-align: center; }
.pg1-footer span { font-size: 6px; color: rgba(255,255,255,0.28); text-transform: uppercase; letter-spacing: 3.5px; }

/* ════════════════════════════════════════════════════════════════════════════
   PAGE 2 — MINI HEADER
════════════════════════════════════════════════════════════════════════════ */
.mini-hdr { padding-bottom: 7px; margin-bottom: 11px; border-bottom: 1px solid #E0E4EA; }
.mini-hdr-tbl { width: 100%; border-collapse: collapse; }
.mini-hdr-tbl td { vertical-align: middle; padding: 0; }
.mini-logo { width: 65px; }
.mini-logo img { height: 22px; width: auto; display: block; }
.mini-name { font-family: Georgia, 'Times New Roman', serif; font-size: 10.5px; font-weight: 700; color: #0C1A2E; }
.mini-sub  { font-size: 6px; color: #9CA3AF; text-transform: uppercase; letter-spacing: 1.5px; margin-top: 1px; }
.mini-right { text-align: right; }
.mini-ref    { font-size: 7px; color: #9CA3AF; text-transform: uppercase; letter-spacing: 0.8px; }
.mini-section { font-size: 7.5px; color: #2563A0; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-top: 2px; }

/* ════════════════════════════════════════════════════════════════════════════
   SECTION LABEL  —  small caps, blue, tight bottom rule
════════════════════════════════════════════════════════════════════════════ */
.sec-lbl {
    font-size: 7px; font-weight: 700; color: #2563A0;
    text-transform: uppercase; letter-spacing: 2px;
    padding-bottom: 5px; border-bottom: 1px solid #E0E4EA; margin-bottom: 9px;
}

/* ════════════════════════════════════════════════════════════════════════════
   DESCRIPTION
════════════════════════════════════════════════════════════════════════════ */
.desc { font-size: 9.5px; color: #404040; line-height: 1.7; }

/* ════════════════════════════════════════════════════════════════════════════
   SPECS + AMENITIES — TWO COLUMNS
════════════════════════════════════════════════════════════════════════════ */
.cols-tbl { width: 100%; border-collapse: collapse; }
.cols-tbl td { vertical-align: top; padding: 0; }
.col-specs { width: 52%; padding-right: 16px; }
.col-amen  { width: 48%; padding-left: 14px; border-left: 1px solid #E0E4EA; }

.spec-row { padding: 4px 0; border-bottom: 1px solid #F0F2F5; }
.spec-tbl  { width: 100%; border-collapse: collapse; }
.spec-tbl td { vertical-align: middle; padding: 0; }
.spec-k { font-size: 7.5px; color: #9CA3AF; text-transform: uppercase; letter-spacing: 0.8px; font-weight: 600; }
.spec-v { text-align: right; font-size: 9.5px; color: #0C1A2E; font-weight: 700; }

.amen-row { display: block; font-size: 8.5px; color: #404040; padding: 4px 0; border-bottom: 1px solid #F0F2F5; line-height: 1.3; }
.amen-row:last-child { border-bottom: none; }
.amen-dot { display: inline-block; width: 5px; height: 5px; background: #2563A0; margin-right: 7px; vertical-align: middle; }

/* ════════════════════════════════════════════════════════════════════════════
   GALLERY — 3 columns, max 9 images, 72px cells
════════════════════════════════════════════════════════════════════════════ */
.gal-tbl { width: 100%; border-collapse: separate; border-spacing: 4px; }
.gal-cell { width: 33.33%; height: 72px; overflow: hidden; background: #E8ECF0; vertical-align: top; padding: 0; }
.gal-cell img { width: 100%; height: 72px; display: block; }
.gal-empty { width: 100%; height: 72px; background: #F0F2F5; display: table; }
.gal-empty-in { display: table-cell; vertical-align: middle; text-align: center; font-size: 7.5px; color: #C8CDD5; }

/* ════════════════════════════════════════════════════════════════════════════
   BROKER BLOCK  —  conditional
════════════════════════════════════════════════════════════════════════════ */
.broker-wrap { border: 1px solid #E0E4EA; padding: 9px 12px; }
.broker-eyebrow { font-size: 6.5px; color: #9CA3AF; text-transform: uppercase; letter-spacing: 2px; font-weight: 700; margin-bottom: 8px; }
.broker-tbl { width: 100%; border-collapse: collapse; }
.broker-tbl td { vertical-align: top; padding: 0; }
.broker-img-cell { width: 48px; padding-right: 12px; }
.broker-img { width: 48px; height: 48px; overflow: hidden; background: #E8ECF0; }
.broker-img img { width: 48px; height: 48px; display: block; }
.broker-init-wrap { width: 48px; height: 48px; background: linear-gradient(135deg, #1A2F4E 0%, #2563A0 100%); display: table; }
.broker-init-in { display: table-cell; vertical-align: middle; text-align: center; font-family: Georgia, serif; font-size: 17px; font-weight: 700; color: #fff; }
.broker-name { font-family: Georgia, 'Times New Roman', serif; font-size: 11px; font-weight: 700; color: #0C1A2E; margin-bottom: 1px; }
.broker-role { font-size: 7px; color: #2563A0; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; margin-bottom: 5px; }
.broker-line { font-size: 8.5px; color: #404040; margin-bottom: 2px; }
.broker-k { font-size: 7px; color: #9CA3AF; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; margin-right: 4px; }

/* ════════════════════════════════════════════════════════════════════════════
   CONTACT + QR
════════════════════════════════════════════════════════════════════════════ */
.contact-tbl { width: 100%; border-collapse: collapse; }
.contact-tbl td { vertical-align: top; padding: 0; }
.contact-left { padding-right: 22px; border-right: 1px solid #E0E4EA; }
.contact-right { width: 96px; padding-left: 22px; text-align: center; }
.contact-brand { font-family: Georgia, 'Times New Roman', serif; font-size: 12px; font-weight: 700; color: #0C1A2E; margin-bottom: 1px; }
.contact-tag   { font-size: 6.5px; color: #9CA3AF; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 10px; }
.contact-row   { font-size: 9px; color: #404040; margin-bottom: 4px; line-height: 1.35; }
.contact-k     { font-size: 6.5px; color: #9CA3AF; text-transform: uppercase; letter-spacing: 0.8px; font-weight: 700; display: block; margin-bottom: 1px; }
.qr-box { width: 74px; height: 74px; margin: 0 auto 5px; border: 1px solid #E0E4EA; padding: 4px; background: #FAFAFA; }
.qr-box img { width: 66px; height: 66px; display: block; }
.qr-lbl { font-size: 6.5px; color: #9CA3AF; text-align: center; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.5; }
.qr-cta { font-size: 6px; color: #2563A0; font-weight: 700; text-align: center; text-transform: uppercase; letter-spacing: 0.8px; margin-top: 2px; }

/* ════════════════════════════════════════════════════════════════════════════
   LEGAL FOOTER  —  dark band, gold top border
════════════════════════════════════════════════════════════════════════════ */
.footer { background: #0C1A2E; padding: 9px 14px; margin-top: 13px; border-top: 2px solid #2563A0; }
.footer-brand { font-size: 7.5px; color: rgba(255,255,255,0.8); font-weight: 700; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 4px; }
.footer-text  { font-size: 6.5px; color: rgba(255,255,255,0.38); line-height: 1.6; }
.footer-copy  { font-size: 6.5px; color: rgba(255,255,255,0.22); margin-top: 5px; text-align: center; }

/* ════════════════════════════════════════════════════════════════════════════
   UTILITIES
════════════════════════════════════════════════════════════════════════════ */
.rule { border: none; border-top: 1px solid #E0E4EA; margin: 11px 0; }
.mt8  { margin-top: 8px; }
.mt11 { margin-top: 11px; }

@media print {
    * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
}
</style>
</head>
<body>


{{-- ═══════════════════════════════════════════════════════════════════════
     PÁGINA 1  ·  PORTADA
     Estructura vertical (de arriba a abajo):
       header  →  gold-rule  →  hero (183mm)  →  price-bar
       →  identity  →  stats  →  (space)  →  footer strip (absolute)
     ═══════════════════════════════════════════════════════════════════════ --}}
<div class="pg1">

    {{-- Header: logo · brand · ref/date --}}
    <div class="hdr">
        <table class="hdr-tbl">
            <tr>
                <td class="hdr-logo">
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}" alt="{{ $siteName }}">
                    @else
                        <div class="hdr-name" style="font-size:15px;">HOME DEL VALLE</div>
                    @endif
                </td>
                <td>
                    @if($logoSrc)
                        <div class="hdr-name">HOME DEL VALLE</div>
                        <div class="hdr-sub">Inmobiliaria Boutique</div>
                    @else
                        <div class="hdr-sub">Inmobiliaria Boutique</div>
                    @endif
                </td>
                <td class="hdr-ref-col">
                    <div class="hdr-label">Ficha Tecnica</div>
                    <div class="hdr-ref">REF. #{{ $ref }}</div>
                    <div class="hdr-date">{{ $today }}</div>
                </td>
            </tr>
        </table>
        <div class="accent-rule"></div>
    </div>

    {{-- Hero image --}}
    @if($heroSrc)
        <div class="hero"><img src="{{ $heroSrc }}" alt="{{ $property->title }}"></div>
    @else
        <div class="hero-empty"><div class="hero-empty-inner">Sin imagen disponible</div></div>
    @endif

    {{-- Price bar --}}
    <div class="price-bar">
        <table class="price-bar-tbl">
            <tr>
                <td>
                    <div class="price-lbl">Precio</div>
                    <div class="price-amount">
                        <span class="price-currency">{{ $property->currency ?? 'MXN' }}</span>{{ $property->formatted_price }}
                    </div>
                </td>
                <td style="text-align:right;">
                    @if($operationType)<span class="op-badge">{{ strtoupper($operationType) }}</span>@endif
                </td>
            </tr>
        </table>
    </div>

    {{-- Property identity --}}
    <div class="identity">
        <div class="prop-title">{{ $property->title }}</div>
        @if($locationStr || $property->address)
            <div class="prop-loc">
                <span class="dash">—</span>@if($locationStr){{ $locationStr }}@endif@if($property->address)<br>{{ $property->address }}@endif
            </div>
        @endif
        <div>
            @if($propertyType)<span class="badge b-type">{{ $propertyType }}</span>@endif
            @if($operationType)<span class="badge b-op">{{ $operationType }}</span>@endif
            @if($statusLabel)<span class="badge b-status">{{ $statusLabel }}</span>@endif
        </div>
    </div>

    {{-- Stats bar --}}
    @if(count($features) > 0)
        <table class="stats-tbl">
            <tr>
                @foreach($features as $f)
                    <td class="stat-cell">
                        <span class="stat-v">{{ $f['v'] }}</span>
                        <span class="stat-l">{{ $f['l'] }}</span>
                    </td>
                @endforeach
            </tr>
        </table>
    @endif

    {{-- Footer strip (anchored absolute to bottom) --}}
    <div class="pg1-footer"><span>{{ $siteUrl }}</span></div>

</div>{{-- /pg1 --}}


{{-- ═══════════════════════════════════════════════════════════════════════
     PÁGINA 2  ·  DESCRIPCIÓN · ESPECIFICACIONES · GALERÍA · CONTACTO
     ═══════════════════════════════════════════════════════════════════════ --}}
<div class="pg2">

    {{-- Mini header --}}
    <div class="mini-hdr">
        <table class="mini-hdr-tbl">
            <tr>
                <td class="mini-logo">
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}" alt="{{ $siteName }}">
                    @else
                        <div class="mini-name">HOME DEL VALLE</div>
                    @endif
                </td>
                <td>
                    @if($logoSrc)
                        <div class="mini-name">HOME DEL VALLE</div>
                        <div class="mini-sub">Inmobiliaria Boutique</div>
                    @endif
                </td>
                <td class="mini-right">
                    <div class="mini-ref">REF. #{{ $ref }}</div>
                    <div class="mini-section">Especificaciones</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── DESCRIPCIÓN ─────────────────────────────────────────────────── --}}
    @if($property->description)
        <div class="sec-lbl">Descripcion Comercial</div>
        <p class="desc">{{ \Illuminate\Support\Str::limit(strip_tags($property->description), 480) }}</p>
        <hr class="rule">
    @endif

    {{-- ── ESPECIFICACIONES + AMENIDADES ──────────────────────────────── --}}
    @if(count($specs) > 0 || count($amenities) > 0)
        <table class="cols-tbl">
            <tr>
                @if(count($specs) > 0)
                    <td class="col-specs">
                        <div class="sec-lbl">Especificaciones Tecnicas</div>
                        @foreach($specs as $k => $v)
                            <div class="spec-row">
                                <table class="spec-tbl"><tr>
                                    <td><span class="spec-k">{{ $k }}</span></td>
                                    <td><span class="spec-v">{{ $v }}</span></td>
                                </tr></table>
                            </div>
                        @endforeach
                    </td>
                @endif
                @if(count($amenities) > 0)
                    <td class="col-amen">
                        <div class="sec-lbl">Amenidades</div>
                        @foreach($amenities as $a)
                            @php $al = $amenityLabels[$a] ?? ucfirst(str_replace('_', ' ', $a)); @endphp
                            <div class="amen-row"><span class="amen-dot"></span>{{ $al }}</div>
                        @endforeach
                    </td>
                @endif
            </tr>
        </table>
    @endif

    {{-- ── GALERÍA (max 9, grid 3 columnas) ──────────────────────────── --}}
    @if($gallerySrcs->count() > 0)
        <hr class="rule">
        <div class="sec-lbl">Galeria de Imagenes</div>
        @php $chunks = $gallerySrcs->chunk(3); @endphp
        <table class="gal-tbl">
            @foreach($chunks as $row)
                <tr>
                    @foreach($row as $src)
                        <td class="gal-cell"><img src="{{ $src }}" alt="Propiedad"></td>
                    @endforeach
                    @for($i = $row->count(); $i < 3; $i++)
                        <td class="gal-cell"><div class="gal-empty"><div class="gal-empty-in">&nbsp;</div></div></td>
                    @endfor
                </tr>
            @endforeach
        </table>
    @endif

    {{-- ── BROKER (condicional) ────────────────────────────────────────── --}}
    @if($includeBroker && $broker)
        <hr class="rule">
        <div class="broker-wrap">
            <div class="broker-eyebrow">Asesor Inmobiliario</div>
            <table class="broker-tbl">
                <tr>
                    <td class="broker-img-cell">
                        @if($brokerPhotoSrc)
                            <div class="broker-img"><img src="{{ $brokerPhotoSrc }}" alt="{{ $broker->name }}"></div>
                        @else
                            @php $ini = strtoupper(mb_substr($broker->name ?? 'A', 0, 1)); @endphp
                            <div class="broker-init-wrap"><div class="broker-init-in">{{ $ini }}</div></div>
                        @endif
                    </td>
                    <td>
                        <div class="broker-name">{{ $broker->name }}</div>
                        <div class="broker-role">{{ $broker->specialty ?? $broker->type ?? 'Asesor Inmobiliario' }}</div>
                        @if($broker->phone)
                            <div class="broker-line"><span class="broker-k">Tel</span>{{ $broker->phone }}</div>
                        @endif
                        @if($broker->email)
                            <div class="broker-line"><span class="broker-k">Email</span>{{ $broker->email }}</div>
                        @endif
                        @if($broker->company_name)
                            <div class="broker-line"><span class="broker-k">Empresa</span>{{ $broker->company_name }}</div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    @endif

    {{-- ── CONTACTO + QR ────────────────────────────────────────────────── --}}
    <hr class="rule">
    <div class="sec-lbl">Informacion de Contacto</div>
    <table class="contact-tbl">
        <tr>
            <td class="contact-left">
                <div class="contact-brand">{{ $siteName }}</div>
                <div class="contact-tag">Inmobiliaria Boutique</div>
                @if($contactPhone)
                    <div class="contact-row"><span class="contact-k">Telefono</span>{{ $contactPhone }}</div>
                @endif
                @if($contactEmail)
                    <div class="contact-row"><span class="contact-k">Correo</span>{{ $contactEmail }}</div>
                @endif
                <div class="contact-row"><span class="contact-k">Web</span>{{ $siteUrl }}</div>
                @if($contactAddr)
                    <div class="contact-row"><span class="contact-k">Ubicacion</span>{{ $contactAddr }}</div>
                @endif
            </td>
            @if($qrSrc)
                <td class="contact-right">
                    <div class="qr-box"><img src="{{ $qrSrc }}" alt="QR"></div>
                    <div class="qr-lbl">Escanea para<br>mas informacion</div>
                    <div class="qr-cta">Agenda una visita</div>
                </td>
            @endif
        </tr>
    </table>

    {{-- ── FOOTER LEGAL ─────────────────────────────────────────────────── --}}
    <div class="footer">
        <div class="footer-brand">{{ $siteName }} &mdash; {{ $siteUrl }}</div>
        <div class="footer-text">
            La informacion contenida en esta ficha tecnica es proporcionada unicamente con fines informativos y no constituye una oferta
            vinculante. Los precios, disponibilidades, superficies y caracteristicas estan sujetos a cambios sin previo aviso y deben
            verificarse directamente con el asesor inmobiliario asignado. Las imagenes son ilustrativas. Para cualquier transaccion
            inmobiliaria se recomienda la asesoria de profesionales legales y financieros independientes.
        </div>
        <div class="footer-copy">&copy; {{ now()->year }} {{ $siteName }}. Todos los derechos reservados. &nbsp;|&nbsp; {{ $siteUrl }}</div>
    </div>

</div>{{-- /pg2 --}}


</body>
</html>
