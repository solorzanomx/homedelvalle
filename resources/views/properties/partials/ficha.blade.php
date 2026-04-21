@php
// ─── Defaults & Config ────────────────────────────────────────────────────────
$siteName      = $siteName    ?? 'Home del Valle';
$mode          = $mode        ?? 'pdf';
$siteSetting   = $siteSetting ?? null;
$includeBroker = $includeBroker ?? (bool) ($property->broker_id && $property->broker);
$broker        = $includeBroker ? $property->broker : null;

// Contact from SiteSetting or hardcoded fallback
$contactPhone  = $siteSetting?->contact_phone     ?? $siteSetting?->whatsapp_number ?? '+52 55 0000 0000';
$contactEmail  = $siteSetting?->contact_email     ?? 'info@homedelvalle.mx';
$contactAddr   = $siteSetting?->address           ?? 'Ciudad de México';
$siteUrl       = 'www.homedelvalle.mx';

// ─── Label Maps ───────────────────────────────────────────────────────────────
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
    'pool'            => 'Alberca',
    'gym'             => 'Gimnasio',
    'garden'          => 'Jardin',
    'terrace'         => 'Terraza',
    'security'        => 'Seguridad 24/7',
    'elevator'        => 'Elevador',
    'rooftop'         => 'Rooftop',
    'bbq'             => 'Area de BBQ',
    'playground'      => 'Area de juegos',
    'pet_friendly'    => 'Pet Friendly',
    'laundry'         => 'Lavanderia',
    'storage'         => 'Bodega',
    'concierge'       => 'Concierge',
    'business_center' => 'Business Center',
    'cinema'          => 'Sala de Cine',
    'spa'             => 'Spa',
    'jacuzzi'         => 'Jacuzzi',
    'sauna'           => 'Sauna',
    'tennis'          => 'Cancha de Tenis',
    'paddle'          => 'Cancha de Padel',
    'co_working'      => 'Co-working',
];

// ─── Image Helper ─────────────────────────────────────────────────────────────
// For PDF (DomPDF): embed images as base64 data URIs
// For email mode: use storage URLs
$imgSrc = function (?string $path) use ($mode): ?string {
    if (empty($path)) return null;
    if ($mode === 'email') return asset('storage/' . $path);
    $full = storage_path('app/public/' . $path);
    if (!file_exists($full)) return null;
    $mime = mime_content_type($full) ?: 'image/jpeg';
    return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($full));
};

// ─── Photos ───────────────────────────────────────────────────────────────────
$primaryPhoto  = $property->primaryPhoto();
$heroSrc       = $imgSrc($primaryPhoto?->path ?? $property->photo);

$galleryPhotos = $property->photos
    ->reject(fn($p) => $p->is_primary)
    ->take(9)
    ->values();
$gallerySrcs = $galleryPhotos
    ->map(fn($p) => $imgSrc($p->path))
    ->filter()
    ->values();

// ─── Logo ─────────────────────────────────────────────────────────────────────
$logoSrc = null;
if ($siteSetting?->logo_path) {
    $logoSrc = $imgSrc($siteSetting->logo_path);
}
if (!$logoSrc) {
    $localLogo = public_path('images/logo-homedelvalle.png');
    if (file_exists($localLogo)) {
        $mime = mime_content_type($localLogo) ?: 'image/png';
        $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($localLogo));
    }
}

// ─── QR Code ──────────────────────────────────────────────────────────────────
$qrSrc = null;
if ($property->qrCode?->qr_code_path) {
    $qrSrc = $imgSrc($property->qrCode->qr_code_path);
}

// ─── Broker Photo ─────────────────────────────────────────────────────────────
$brokerPhotoSrc = null;
if ($broker?->photo) {
    $brokerPhotoSrc = $imgSrc($broker->photo);
}

// ─── Derived Property Data ────────────────────────────────────────────────────
$propertyType  = $typeLabels[$property->property_type ?? '']  ?? ($property->property_type ?? '');
$operationType = $opLabels[$property->operation_type ?? '']   ?? ($property->operation_type ?? '');
$statusLabel   = $statusLabels[$property->status ?? '']       ?? ($property->status ?? '');

// Build location string
$locationParts = array_filter([
    $property->colony,
    $property->city,
    $property->zipcode ? 'C.P. ' . $property->zipcode : null,
]);
$locationStr = implode(', ', $locationParts);

// Build feature rows (only non-empty values)
$features = [];
if ($property->lot_area > 0)           $features[] = ['value' => number_format((float)$property->lot_area, 0), 'label' => 'm² Terreno'];
if ($property->construction_area > 0)  $features[] = ['value' => number_format((float)$property->construction_area, 0), 'label' => 'm² Construidos'];
if ($property->area > 0 && !$property->lot_area && !$property->construction_area)
                                        $features[] = ['value' => number_format((float)$property->area, 0), 'label' => 'm²'];
if ($property->bedrooms > 0)           $features[] = ['value' => $property->bedrooms, 'label' => 'Recamaras'];
if ($property->bathrooms > 0) {
    $bathVal = $property->bathrooms . ($property->half_bathrooms ? ' + ' . $property->half_bathrooms . ' m' : '');
    $features[] = ['value' => $bathVal, 'label' => 'Banos'];
}
if ($property->parking > 0)            $features[] = ['value' => $property->parking, 'label' => 'Cajones'];

// Specs table (page 2)
$specs = [];
if ($property->lot_area > 0)          $specs['Terreno']         = number_format((float)$property->lot_area, 0) . ' m²';
if ($property->construction_area > 0) $specs['Construccion']    = number_format((float)$property->construction_area, 0) . ' m²';
if ($property->area > 0)              $specs['Superficie total'] = number_format((float)$property->area, 0) . ' m²';
if ($property->bedrooms > 0)          $specs['Recamaras']       = $property->bedrooms;
if ($property->bathrooms > 0)         $specs['Banos completos'] = $property->bathrooms;
if ($property->half_bathrooms > 0)    $specs['Medios banos']    = $property->half_bathrooms;
if ($property->parking > 0)           $specs['Estacionamientos']= $property->parking;
if ($property->floors > 0)            $specs['Niveles']         = $property->floors;
if ($property->year_built > 0)        $specs['Ano de construccion'] = $property->year_built;
if ($property->maintenance_fee > 0)   $specs['Mantenimiento']   = '$' . number_format((float)$property->maintenance_fee, 0) . '/mes';
if ($property->furnished)             $specs['Amueblado']       = 'Si';
if ($property->status)                $specs['Estatus']         = $statusLabel;

// Amenities
$amenities = $property->amenities ?? [];

// Reference number
$ref = str_pad($property->id, 5, '0', STR_PAD_LEFT);
$today = now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
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
   PAGE SETUP  —  A4 portrait, 13 mm margins
════════════════════════════════════════════════════════════════════════════ */
@page {
    size: A4 portrait;
    margin: 13mm 15mm 13mm 15mm;
}

html, body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11px;
    color: #2C2C2C;
    background: #ffffff;
    line-height: 1.55;
    -webkit-font-smoothing: antialiased;
}

/* ════════════════════════════════════════════════════════════════════════════
   PAGE WRAPPER  —  each .page = one A4 sheet
════════════════════════════════════════════════════════════════════════════ */
.page {
    width: 100%;
    page-break-after: always;
}
.page:last-child { page-break-after: avoid; }

/* ════════════════════════════════════════════════════════════════════════════
   TYPOGRAPHY
════════════════════════════════════════════════════════════════════════════ */
.hdv-heading {
    font-family: Georgia, 'Times New Roman', Times, serif;
    color: #1A2F4E;
    line-height: 1.2;
}

/* ════════════════════════════════════════════════════════════════════════════
   COLORS  (no CSS variables — max DomPDF compat)
   Navy dark  : #1A2F4E
   Navy deep  : #0C1A2E   (footer bg)
   Blue accent: #2563A0
   Blue mid   : #3B82C4
   Bg tint    : #F0F7FF
   Bg neutral : #F8F9FA
   Text main  : #2C2C2C
   Text muted : #6B7280
   Hairline   : #D4D8DC
════════════════════════════════════════════════════════════════════════════ */

/* ════════════════════════════════════════════════════════════════════════════
   PAGE HEADER  —  used on pages 2, 3, 4
════════════════════════════════════════════════════════════════════════════ */
.page-header-minimal {
    padding-bottom: 10px;
    margin-bottom: 18px;
    border-bottom: 1px solid #D4D8DC;
}
.page-header-minimal table { width: 100%; border-collapse: collapse; }
.page-header-minimal td { vertical-align: middle; }
.page-header-minimal .logo-wrap { width: 80px; }
.page-header-minimal .logo-wrap img { height: 28px; width: auto; display: block; }
.page-header-minimal .logo-text {
    font-family: Georgia, 'Times New Roman', Times, serif;
    font-size: 13px;
    font-weight: 700;
    color: #1A2F4E;
    letter-spacing: 0.5px;
    line-height: 1.1;
}
.page-header-minimal .logo-sub {
    font-size: 7.5px;
    color: #6B7280;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-top: 1px;
}
.page-header-minimal .page-ref {
    text-align: right;
    font-size: 8px;
    color: #6B7280;
    text-transform: uppercase;
    letter-spacing: 0.6px;
}
.page-header-minimal .page-label {
    font-size: 9px;
    color: #2563A0;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-top: 2px;
}

/* ════════════════════════════════════════════════════════════════════════════
   PAGE 1 — COVER HEADER
════════════════════════════════════════════════════════════════════════════ */
.cover-header {
    padding-bottom: 14px;
    margin-bottom: 14px;
    border-bottom: 2px solid #1A2F4E;
}
.cover-header table { width: 100%; border-collapse: collapse; }
.cover-header td { vertical-align: middle; }
.cover-header .logo-cell { width: 140px; }
.cover-header .logo-cell img { height: 38px; width: auto; display: block; }
.cover-header .brand-text {
    font-family: Georgia, 'Times New Roman', Times, serif;
    font-size: 22px;
    font-weight: 700;
    color: #1A2F4E;
    letter-spacing: 0.3px;
    line-height: 1.1;
}
.cover-header .brand-sub {
    font-size: 8px;
    color: #6B7280;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-top: 3px;
}
.cover-header .ficha-label {
    text-align: right;
    font-size: 8px;
    color: #6B7280;
    text-transform: uppercase;
    letter-spacing: 1.5px;
}
.cover-header .ficha-ref {
    text-align: right;
    font-size: 9px;
    color: #2563A0;
    font-weight: 700;
    letter-spacing: 0.5px;
    margin-top: 3px;
}
.cover-header .ficha-date {
    text-align: right;
    font-size: 7.5px;
    color: #9CA3AF;
    margin-top: 2px;
}

/* ════════════════════════════════════════════════════════════════════════════
   HERO IMAGE
════════════════════════════════════════════════════════════════════════════ */
.hero-container {
    width: 100%;
    height: 195px;
    overflow: hidden;
    margin-bottom: 0;
    background: #E9ECEF;
}
.hero-container img {
    width: 100%;
    height: 195px;
    display: block;
}
.hero-placeholder {
    width: 100%;
    height: 195px;
    background: linear-gradient(135deg, #EEF2F7 0%, #DDE4EF 100%);
    display: table;
}
.hero-placeholder-inner {
    display: table-cell;
    vertical-align: middle;
    text-align: center;
    font-size: 11px;
    color: #9CA3AF;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* ════════════════════════════════════════════════════════════════════════════
   PRICE BAR  —  deep navy block
════════════════════════════════════════════════════════════════════════════ */
.price-bar {
    background: #1A2F4E;
    padding: 13px 18px;
    margin-bottom: 0;
}
.price-bar table { width: 100%; border-collapse: collapse; }
.price-bar td { vertical-align: middle; }
.price-bar .price-label {
    font-size: 7.5px;
    color: rgba(255,255,255,0.6);
    text-transform: uppercase;
    letter-spacing: 1.5px;
    font-weight: 600;
    margin-bottom: 3px;
}
.price-bar .price-amount {
    font-family: Georgia, 'Times New Roman', Times, serif;
    font-size: 28px;
    font-weight: 700;
    color: #ffffff;
    letter-spacing: -0.5px;
    line-height: 1;
}
.price-bar .price-currency {
    font-size: 12px;
    font-weight: 400;
    color: rgba(255,255,255,0.75);
    vertical-align: top;
    margin-right: 3px;
    line-height: 28px;
}
.price-bar .op-badge {
    display: inline-block;
    padding: 5px 14px;
    border: 1.5px solid rgba(255,255,255,0.4);
    border-radius: 2px;
    font-size: 9px;
    font-weight: 700;
    color: #ffffff;
    text-transform: uppercase;
    letter-spacing: 1.5px;
}

/* ════════════════════════════════════════════════════════════════════════════
   PROPERTY IDENTITY BLOCK
════════════════════════════════════════════════════════════════════════════ */
.identity-block {
    padding: 16px 0 12px;
    border-bottom: 1px solid #D4D8DC;
    margin-bottom: 14px;
}
.property-title {
    font-family: Georgia, 'Times New Roman', Times, serif;
    font-size: 20px;
    font-weight: 700;
    color: #1A2F4E;
    line-height: 1.25;
    margin-bottom: 6px;
    letter-spacing: -0.3px;
}
.property-location {
    font-size: 10px;
    color: #6B7280;
    margin-bottom: 10px;
    letter-spacing: 0.1px;
}
.property-location .loc-icon {
    color: #2563A0;
    font-weight: 700;
    margin-right: 3px;
}
.badge-row { margin-bottom: 0; }
.badge {
    display: inline-block;
    padding: 3px 9px;
    border-radius: 2px;
    font-size: 8.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-right: 5px;
}
.badge-type     { background: #F0F7FF; color: #1A2F4E; }
.badge-op       { background: #2563A0; color: #ffffff; }
.badge-status   { background: #DCFCE7; color: #166534; }

/* ════════════════════════════════════════════════════════════════════════════
   FEATURE PILLS  —  key numeric specs
════════════════════════════════════════════════════════════════════════════ */
.features-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 6px 0;
}
.feature-cell {
    background: #F8F9FA;
    padding: 10px 6px 8px;
    text-align: center;
    border-top: 3px solid #2563A0;
}
.feature-value {
    font-family: Georgia, 'Times New Roman', Times, serif;
    font-size: 17px;
    font-weight: 700;
    color: #1A2F4E;
    display: block;
    line-height: 1.1;
}
.feature-label {
    font-size: 7px;
    color: #6B7280;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    margin-top: 3px;
    display: block;
    font-weight: 600;
}

/* ════════════════════════════════════════════════════════════════════════════
   SECTION HEADER  (pages 2, 3, 4)
════════════════════════════════════════════════════════════════════════════ */
.section-head {
    font-family: Georgia, 'Times New Roman', Times, serif;
    font-size: 10px;
    font-weight: 700;
    color: #1A2F4E;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    padding-bottom: 7px;
    border-bottom: 2px solid #2563A0;
    margin-bottom: 12px;
}

/* ════════════════════════════════════════════════════════════════════════════
   DESCRIPTION
════════════════════════════════════════════════════════════════════════════ */
.description-text {
    font-size: 10px;
    color: #374151;
    line-height: 1.75;
    text-align: justify;
    white-space: pre-line;
}

/* ════════════════════════════════════════════════════════════════════════════
   SPECS + AMENITIES  —  two-column table
════════════════════════════════════════════════════════════════════════════ */
.specs-amenities-table {
    width: 100%;
    border-collapse: collapse;
}
.specs-amenities-table td {
    vertical-align: top;
    padding: 0;
}
.specs-col { width: 52%; padding-right: 20px; }
.amenities-col { width: 48%; padding-left: 18px; border-left: 1px solid #E5E7EB; }

/* SPECS  */
.spec-row {
    display: block;
    padding: 7px 0;
    border-bottom: 1px solid #F3F4F6;
}
.spec-row table { width: 100%; border-collapse: collapse; }
.spec-row td { vertical-align: middle; }
.spec-lbl {
    font-size: 8.5px;
    color: #6B7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}
.spec-val {
    text-align: right;
    font-size: 10px;
    color: #1A2F4E;
    font-weight: 700;
}

/* AMENITIES  */
.amenity-item {
    display: block;
    font-size: 9.5px;
    color: #374151;
    padding: 5px 0;
    border-bottom: 1px solid #F3F4F6;
}
.amenity-item:last-child { border-bottom: none; }
.amenity-check {
    color: #2563A0;
    font-weight: 700;
    margin-right: 5px;
}

/* ════════════════════════════════════════════════════════════════════════════
   OBSERVATIONS BLOCK
════════════════════════════════════════════════════════════════════════════ */
.observations-block {
    margin-top: 20px;
    padding: 12px 14px;
    background: #F8F9FA;
    border-left: 3px solid #3B82C4;
}
.observations-text {
    font-size: 9.5px;
    color: #4B5563;
    line-height: 1.7;
}

/* ════════════════════════════════════════════════════════════════════════════
   GALLERY  —  page 3
════════════════════════════════════════════════════════════════════════════ */
.gallery-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 7px;
}
.gallery-cell {
    width: 33.33%;
    height: 120px;
    overflow: hidden;
    background: #E9ECEF;
    vertical-align: top;
    padding: 0;
}
.gallery-cell img {
    width: 100%;
    height: 120px;
    display: block;
}
.gallery-placeholder {
    width: 100%;
    height: 120px;
    background: #F3F4F6;
    display: table;
}
.gallery-placeholder-inner {
    display: table-cell;
    vertical-align: middle;
    text-align: center;
    font-size: 9px;
    color: #9CA3AF;
}

/* ════════════════════════════════════════════════════════════════════════════
   BROKER BLOCK  —  page 4 (conditional)
════════════════════════════════════════════════════════════════════════════ */
.broker-wrapper {
    border: 1px solid #E5E7EB;
    padding: 16px 18px;
    margin-bottom: 20px;
    background: #ffffff;
}
.broker-eyebrow {
    font-size: 7.5px;
    color: #9CA3AF;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    font-weight: 700;
    margin-bottom: 12px;
}
.broker-inner-table { width: 100%; border-collapse: collapse; }
.broker-inner-table td { vertical-align: top; }
.broker-photo-cell { width: 64px; padding-right: 16px; }
.broker-photo-wrap {
    width: 64px;
    height: 64px;
    overflow: hidden;
    background: #EEF2F7;
}
.broker-photo-wrap img {
    width: 64px;
    height: 64px;
    display: block;
}
.broker-photo-initials {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, #1A2F4E 0%, #2563A0 100%);
    display: table;
}
.broker-initials-inner {
    display: table-cell;
    vertical-align: middle;
    text-align: center;
    font-family: Georgia, 'Times New Roman', Times, serif;
    font-size: 20px;
    font-weight: 700;
    color: #ffffff;
    letter-spacing: 1px;
}
.broker-name {
    font-family: Georgia, 'Times New Roman', Times, serif;
    font-size: 14px;
    font-weight: 700;
    color: #1A2F4E;
    line-height: 1.2;
    margin-bottom: 2px;
}
.broker-position {
    font-size: 8px;
    color: #2563A0;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 700;
    margin-bottom: 8px;
}
.broker-contact-item {
    font-size: 9.5px;
    color: #4B5563;
    margin-bottom: 3px;
}
.broker-contact-label {
    color: #9CA3AF;
    font-size: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
    margin-right: 4px;
}

/* ════════════════════════════════════════════════════════════════════════════
   CONTACT + QR BLOCK  —  page 4
════════════════════════════════════════════════════════════════════════════ */
.contact-qr-table { width: 100%; border-collapse: collapse; }
.contact-qr-table td { vertical-align: top; }
.contact-col {
    padding-right: 24px;
    border-right: 1px solid #E5E7EB;
}
.qr-col {
    width: 120px;
    padding-left: 24px;
    text-align: center;
}
.contact-brand-name {
    font-family: Georgia, 'Times New Roman', Times, serif;
    font-size: 16px;
    font-weight: 700;
    color: #1A2F4E;
    margin-bottom: 2px;
    letter-spacing: 0.2px;
}
.contact-tagline {
    font-size: 8px;
    color: #9CA3AF;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 14px;
}
.contact-item {
    font-size: 9.5px;
    color: #374151;
    margin-bottom: 5px;
    line-height: 1.4;
}
.contact-item-label {
    font-size: 7.5px;
    color: #9CA3AF;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    font-weight: 700;
    display: block;
    margin-bottom: 1px;
}
.qr-image-wrap {
    width: 90px;
    height: 90px;
    margin: 0 auto 8px;
    background: #F8F9FA;
    padding: 5px;
    border: 1px solid #E5E7EB;
}
.qr-image-wrap img {
    width: 80px;
    height: 80px;
    display: block;
}
.qr-label {
    font-size: 7.5px;
    color: #6B7280;
    text-align: center;
    line-height: 1.4;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.qr-cta {
    font-size: 7px;
    color: #2563A0;
    font-weight: 700;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    margin-top: 3px;
}

/* ════════════════════════════════════════════════════════════════════════════
   FOOTER LEGAL  —  dark navy bar
════════════════════════════════════════════════════════════════════════════ */
.footer-legal {
    background: #0C1A2E;
    padding: 12px 18px;
    margin-top: 22px;
    border-top: 3px solid #2563A0;
}
.footer-legal-brand {
    font-size: 9px;
    color: rgba(255,255,255,0.9);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 5px;
}
.footer-legal-text {
    font-size: 7px;
    color: rgba(255,255,255,0.5);
    line-height: 1.6;
    text-align: justify;
}
.footer-legal-copy {
    font-size: 7px;
    color: rgba(255,255,255,0.35);
    margin-top: 6px;
    text-align: center;
}

/* ════════════════════════════════════════════════════════════════════════════
   ACCENT DIVIDER
════════════════════════════════════════════════════════════════════════════ */
.rule-accent {
    border: none;
    border-top: 2px solid #2563A0;
    margin: 16px 0;
    width: 48px;
}
.rule-hairline {
    border: none;
    border-top: 1px solid #E5E7EB;
    margin: 16px 0;
}

/* ════════════════════════════════════════════════════════════════════════════
   PRINT FIDELITY
════════════════════════════════════════════════════════════════════════════ */
@media print {
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>
</head>
<body>


{{-- ═══════════════════════════════════════════════════════════════════════════
     PÁGINA 1  —  PORTADA + RESUMEN
     ═══════════════════════════════════════════════════════════════════════════ --}}
<div class="page">

    {{-- Cover Header --}}
    <div class="cover-header">
        <table>
            <tr>
                <td class="logo-cell">
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}" alt="{{ $siteName }}">
                    @else
                        <div class="brand-text">HOME<br>DEL VALLE</div>
                    @endif
                </td>
                <td>
                    @if($logoSrc)
                        <div class="brand-text">HOME DEL VALLE</div>
                        <div class="brand-sub">Inmobiliaria Boutique</div>
                    @endif
                </td>
                <td style="text-align:right;">
                    <div class="ficha-label">Ficha Tecnica</div>
                    <div class="ficha-ref">REF. #{{ $ref }}</div>
                    <div class="ficha-date">{{ $today }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Hero Image --}}
    @if($heroSrc)
        <div class="hero-container">
            <img src="{{ $heroSrc }}" alt="{{ $property->title }}">
        </div>
    @else
        <div class="hero-placeholder">
            <div class="hero-placeholder-inner">Sin imagen disponible</div>
        </div>
    @endif

    {{-- Price Bar --}}
    <div class="price-bar">
        <table>
            <tr>
                <td>
                    <div class="price-label">Precio</div>
                    <div class="price-amount">
                        <span class="price-currency">{{ $property->currency ?? 'MXN' }}</span>{{ $property->formatted_price }}
                    </div>
                </td>
                <td style="text-align:right;">
                    @if($operationType)
                        <span class="op-badge">{{ strtoupper($operationType) }}</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- Property Identity --}}
    <div class="identity-block">
        <div class="property-title">{{ $property->title }}</div>

        @if($locationStr || $property->address)
            <div class="property-location">
                <span class="loc-icon">—</span>
                @if($locationStr){{ $locationStr }}@endif
                @if($property->address)<br>{{ $property->address }}@endif
            </div>
        @endif

        <div class="badge-row">
            @if($propertyType)
                <span class="badge badge-type">{{ $propertyType }}</span>
            @endif
            @if($operationType)
                <span class="badge badge-op">{{ $operationType }}</span>
            @endif
            @if($statusLabel)
                <span class="badge badge-status">{{ $statusLabel }}</span>
            @endif
        </div>
    </div>

    {{-- Feature Pills --}}
    @if(count($features) > 0)
        <table class="features-table">
            <tr>
                @foreach($features as $f)
                    <td class="feature-cell">
                        <span class="feature-value">{{ $f['value'] }}</span>
                        <span class="feature-label">{{ $f['label'] }}</span>
                    </td>
                @endforeach
            </tr>
        </table>
    @endif

</div>{{-- /page 1 --}}


{{-- ═══════════════════════════════════════════════════════════════════════════
     PÁGINA 2  —  DESCRIPCIÓN + ESPECIFICACIONES + AMENIDADES
     ═══════════════════════════════════════════════════════════════════════════ --}}
<div class="page">

    {{-- Mini Header --}}
    <div class="page-header-minimal">
        <table>
            <tr>
                <td class="logo-wrap">
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}" alt="{{ $siteName }}">
                    @else
                        <div class="logo-text">HOME DEL VALLE</div>
                        <div class="logo-sub">Inmobiliaria Boutique</div>
                    @endif
                </td>
                <td>
                    @if($logoSrc)
                        <div class="logo-text">HOME DEL VALLE</div>
                        <div class="logo-sub">Inmobiliaria Boutique</div>
                    @endif
                </td>
                <td>
                    <div class="page-ref">REF. #{{ $ref }}</div>
                    <div class="page-label">Especificaciones</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Description --}}
    @if($property->description)
        <div class="section-head">Descripcion Comercial</div>
        <p class="description-text">{{ \Illuminate\Support\Str::limit(strip_tags($property->description), 900) }}</p>
        <hr class="rule-hairline">
    @endif

    {{-- Specs + Amenities (two columns) --}}
    @if(count($specs) > 0 || count($amenities) > 0)
        <table class="specs-amenities-table">
            <tr>
                {{-- SPECS --}}
                @if(count($specs) > 0)
                    <td class="specs-col">
                        <div class="section-head">Especificaciones Tecnicas</div>
                        @foreach($specs as $lbl => $val)
                            <div class="spec-row">
                                <table>
                                    <tr>
                                        <td><span class="spec-lbl">{{ $lbl }}</span></td>
                                        <td><span class="spec-val">{{ $val }}</span></td>
                                    </tr>
                                </table>
                            </div>
                        @endforeach
                    </td>
                @endif

                {{-- AMENITIES --}}
                @if(count($amenities) > 0)
                    <td class="amenities-col">
                        <div class="section-head">Amenidades</div>
                        @foreach($amenities as $a)
                            @php $aLabel = $amenityLabels[$a] ?? ucfirst(str_replace('_', ' ', $a)); @endphp
                            <div class="amenity-item">
                                <span class="amenity-check">&#10003;</span>{{ $aLabel }}
                            </div>
                        @endforeach
                    </td>
                @endif
            </tr>
        </table>
    @endif

    {{-- Observations --}}
    @if(!empty($property->observations))
        <div class="observations-block" style="margin-top:18px;">
            <div class="section-head" style="margin-bottom:8px;">Observaciones</div>
            <p class="observations-text">{{ $property->observations }}</p>
        </div>
    @endif

</div>{{-- /page 2 --}}


{{-- ═══════════════════════════════════════════════════════════════════════════
     PÁGINA 3  —  GALERÍA  (solo si hay fotos secundarias)
     ═══════════════════════════════════════════════════════════════════════════ --}}
@if($gallerySrcs->count() > 0)
<div class="page">

    {{-- Mini Header --}}
    <div class="page-header-minimal">
        <table>
            <tr>
                <td class="logo-wrap">
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}" alt="{{ $siteName }}">
                    @else
                        <div class="logo-text">HOME DEL VALLE</div>
                    @endif
                </td>
                <td>
                    @if($logoSrc)
                        <div class="logo-text">HOME DEL VALLE</div>
                        <div class="logo-sub">Inmobiliaria Boutique</div>
                    @endif
                </td>
                <td>
                    <div class="page-ref">REF. #{{ $ref }}</div>
                    <div class="page-label">Galeria Fotografica</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Gallery Title --}}
    <div class="section-head">Galeria de Imagenes</div>

    {{-- Gallery Grid — 3 columns --}}
    @php
        $galleryChunks = $gallerySrcs->chunk(3);
    @endphp
    <table class="gallery-table">
        @foreach($galleryChunks as $row)
            <tr>
                @foreach($row as $src)
                    <td class="gallery-cell">
                        <img src="{{ $src }}" alt="Propiedad">
                    </td>
                @endforeach
                {{-- Fill empty cells in last row --}}
                @for($i = $row->count(); $i < 3; $i++)
                    <td class="gallery-cell">
                        <div class="gallery-placeholder">
                            <div class="gallery-placeholder-inner"> </div>
                        </div>
                    </td>
                @endfor
            </tr>
        @endforeach
    </table>

</div>{{-- /page 3 --}}
@endif


{{-- ═══════════════════════════════════════════════════════════════════════════
     PÁGINA 4  —  CONTACTO + BROKER + QR + LEGAL
     ═══════════════════════════════════════════════════════════════════════════ --}}
<div class="page">

    {{-- Mini Header --}}
    <div class="page-header-minimal">
        <table>
            <tr>
                <td class="logo-wrap">
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}" alt="{{ $siteName }}">
                    @else
                        <div class="logo-text">HOME DEL VALLE</div>
                    @endif
                </td>
                <td>
                    @if($logoSrc)
                        <div class="logo-text">HOME DEL VALLE</div>
                        <div class="logo-sub">Inmobiliaria Boutique</div>
                    @endif
                </td>
                <td>
                    <div class="page-ref">REF. #{{ $ref }}</div>
                    <div class="page-label">Informacion de Contacto</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── BLOQUE BROKER (condicional) ──────────────────────────────────────── --}}
    @if($includeBroker && $broker)
        <div class="broker-wrapper">
            <div class="broker-eyebrow">Asesor Inmobiliario</div>

            <table class="broker-inner-table">
                <tr>
                    {{-- Foto o iniciales --}}
                    <td class="broker-photo-cell">
                        @if($brokerPhotoSrc)
                            <div class="broker-photo-wrap">
                                <img src="{{ $brokerPhotoSrc }}" alt="{{ $broker->name }}">
                            </div>
                        @else
                            @php
                                $initials = strtoupper(mb_substr($broker->name ?? 'A', 0, 1));
                            @endphp
                            <div class="broker-photo-initials">
                                <div class="broker-initials-inner">{{ $initials }}</div>
                            </div>
                        @endif
                    </td>

                    {{-- Info del broker --}}
                    <td>
                        <div class="broker-name">{{ $broker->name }}</div>

                        @if($broker->specialty || $broker->type)
                            <div class="broker-position">
                                {{ $broker->specialty ?? $broker->type ?? 'Asesor Inmobiliario' }}
                            </div>
                        @else
                            <div class="broker-position">Asesor Inmobiliario</div>
                        @endif

                        @if($broker->phone)
                            <div class="broker-contact-item">
                                <span class="broker-contact-label">Tel</span>{{ $broker->phone }}
                            </div>
                        @endif
                        @if($broker->email)
                            <div class="broker-contact-item">
                                <span class="broker-contact-label">Email</span>{{ $broker->email }}
                            </div>
                        @endif
                        @if($broker->company_name)
                            <div class="broker-contact-item">
                                <span class="broker-contact-label">Empresa</span>{{ $broker->company_name }}
                            </div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    @endif

    {{-- ── CONTACTO CORPORATIVO + QR ────────────────────────────────────────── --}}
    <div class="section-head">Informacion de Contacto</div>
    <table class="contact-qr-table">
        <tr>
            {{-- Contact info --}}
            <td class="contact-col">
                <div class="contact-brand-name">{{ $siteName }}</div>
                <div class="contact-tagline">Inmobiliaria Boutique</div>

                @if($contactPhone)
                    <div class="contact-item">
                        <span class="contact-item-label">Telefono</span>
                        {{ $contactPhone }}
                    </div>
                @endif

                @if($contactEmail)
                    <div class="contact-item">
                        <span class="contact-item-label">Correo</span>
                        {{ $contactEmail }}
                    </div>
                @endif

                <div class="contact-item">
                    <span class="contact-item-label">Web</span>
                    {{ $siteUrl }}
                </div>

                @if($contactAddr)
                    <div class="contact-item">
                        <span class="contact-item-label">Ubicacion</span>
                        {{ $contactAddr }}
                    </div>
                @endif
            </td>

            {{-- QR Code --}}
            @if($qrSrc)
                <td class="qr-col">
                    <div class="qr-image-wrap">
                        <img src="{{ $qrSrc }}" alt="QR Propiedad">
                    </div>
                    <div class="qr-label">Escanea para<br>mas informacion</div>
                    <div class="qr-cta">Agenda una visita</div>
                </td>
            @endif
        </tr>
    </table>

    {{-- ── FOOTER LEGAL ─────────────────────────────────────────────────────── --}}
    <div class="footer-legal">
        <div class="footer-legal-brand">{{ $siteName }} &mdash; {{ $siteUrl }}</div>
        <div class="footer-legal-text">
            La informacion contenida en esta ficha tecnica es proporcionada unicamente con fines informativos y no constituye una oferta vinculante.
            Los precios, disponibilidades, superficies y caracteristicas estan sujetos a cambios sin previo aviso y deben verificarse directamente
            con el asesor inmobiliario asignado. Las imagenes son ilustrativas. Para cualquier transaccion inmobiliaria se recomienda
            la asesoria de profesionales legales y financieros independientes.
        </div>
        <div class="footer-legal-copy">
            &copy; {{ now()->year }} {{ $siteName }}. Todos los derechos reservados. &nbsp;|&nbsp; {{ $siteUrl }}
        </div>
    </div>

</div>{{-- /page 4 --}}


</body>
</html>
