@php
// ─── Config ──────────────────────────────────────────────────────────────────
$siteName = 'Home del Valle';
$siteUrl  = 'www.homedelvalle.mx';
$folio    = 'OV-' . str_pad($valuation->id, 5, '0', STR_PAD_LEFT);
$today    = now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
$validity = now()->addDays(90)->locale('es')->isoFormat('D [de] MMMM [de] YYYY');

// Alias a los nombres ya usados en todo este archivo, para no tocar cada
// sitio donde se leen — la identidad de marca (logo/fuente) ahora vive en
// pdf/_brand_data.php, compartida con Presentación y Propuesta de Servicios.
include(resource_path('views/pdf/_brand_data.php'));
$logoSrc      = $brandLogoSrc;
$logoSrcLight = $brandLogoSrcLight;
$b64Inter     = $brandFontB64;

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

// Lo que se muestra grande en el header (siempre hay algo), primera letra mayúscula
$addressDisplay = ucfirst($address ?? ($typeLabel . ' en ' . $colonia . ', ' . $zone . ', CDMX'));
// Subtítulo solo cuando hay dirección real (para no duplicar info)
$addressSubline = $address ? ($typeLabel . '  ·  ' . $colonia . '  ·  ' . $zone . ', Benito Juárez, CDMX') : null;

// Nombre del propietario, si la valuación ya está ligada a un cliente —
// misma frase ("Preparada para...") que ya usa Presentación, para que el
// tono sea el mismo entre los 2 documentos que llegan al propietario.
$propietarioNombre = $valuation->property?->owner?->name ?? null;

$diagLabel  = $valuation->diagnosis_label;
$diagBorder = match($valuation->diagnosis) {
    'on_market'    => '#93C5FD',
    'above_market' => '#FDE047',
    'opportunity'  => '#86EFAC',
    default        => '#D1D5DB',
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
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>{{ $folio }} — Opinión de Valor — {{ $colonia }}</title>
<style>
{!! $brandCssVars ?? '' !!}
@if($b64Inter)
@font-face {
    font-family: 'Inter';
    font-style: normal;
    font-weight: 100 900;
    font-display: swap;
    src: url('data:font/woff2;base64,{{ $b64Inter }}') format('woff2');
}
@endif

*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
@page { size: A4 portrait; margin: 0; }
html, body {
    font-family: 'Inter', -apple-system, Arial, sans-serif;
    font-size: 11.5px;
    color: #1e293b;
    background: #fff;
    line-height: 1.6;
    -webkit-font-smoothing: antialiased;
}
@media print {
    * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
}

/* ═══════════════════════════════════════════════════════════════════════════
   Lenguaje visual: el mismo de Carta Oferta / Contrato de Exclusiva / Recibo
   de Apartado — sobrio, casi sin color (navy solo para títulos/acentos),
   cajas con borde fino en vez de fondos degradados, sin pills ni barras de
   colores. Este documento sí necesita tablas/cifras (es un análisis, no un
   contrato), pero se presentan con la misma discreción que el resto de los
   documentos de marca.
   ═══════════════════════════════════════════════════════════════════════════ */

.page {
    width: 100%;
    height: 297mm;
    display: grid;
    grid-template-rows: auto 1fr auto;
    background: #fff;
    overflow: hidden;
}
.page-break { break-after: page; page-break-after: always; }

/* ── HEADER (compartido en las 3 páginas) ─────────────────────────────────── */
.doc-hd {
    background: var(--hdv-navy);
    padding: 0 48px;
    height: 52px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
    border-bottom: 4px solid var(--hdv-accent);
}
.doc-hd-logo img { height: 19px; width: auto; max-width: 150px; display: block; object-fit: contain; }
.doc-hd-logo-txt { font-size: 12px; font-weight: 800; color: #fff; }
.doc-hd-right { text-align: right; }
.doc-hd-tag {
    font-size: 8px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: rgba(199,210,254,.7);
    font-weight: 600;
}
.doc-hd-folio {
    font-size: 8px;
    color: rgba(255,255,255,.35);
    letter-spacing: .5px;
    margin-top: 2px;
}

/* ── FOOTER (compartido) ──────────────────────────────────────────────────── */
.doc-ft {
    border-top: 1px solid #E2E8F0;
    padding: 8px 48px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 8.5px;
    color: #94A3B8;
    flex-shrink: 0;
}
.doc-ft strong { color: var(--hdv-navy); font-weight: 600; }
.doc-ft-legal {
    font-size: 7px;
    color: #B0B8C4;
    line-height: 1.5;
    padding: 0 48px 8px;
}
.p3-footer { flex-shrink: 0; }

/* ── SECTION LABEL ────────────────────────────────────────────────────────── */
.sec-lbl {
    font-size: 8px;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: #94A3B8;
    font-weight: 700;
    margin-bottom: 10px;
}

/* ══════════════════════════════════════════════════════════════════════════
   PAGE 1 — PORTADA
   ══════════════════════════════════════════════════════════════════════════ */
.p1-inner { flex: 1; overflow: hidden; padding: 20px 48px 12px; display: flex; flex-direction: column; }

.prop-title { font-size: 18px; font-weight: 800; color: var(--hdv-navy); letter-spacing: -.3px; margin-bottom: 2px; line-height: 1.2; }
.prop-sub   { font-size: 10px; color: #64748B; margin-bottom: 12px; }

.prop-meta-row {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 16px;
    padding-bottom: 14px;
    border-bottom: 1px solid #E2E8F0;
}
.prop-chips { display: flex; gap: 5px; flex-wrap: wrap; align-items: center; }
.prop-chip {
    font-size: 9px;
    font-weight: 600;
    color: #475569;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 3px;
    padding: 3px 8px;
    white-space: nowrap;
}
.prop-chip-muted { color: #94A3B8; background: transparent; border-color: #F1F5F9; }

.diag-line { text-align: right; flex-shrink: 0; }
.diag-lbl { display: block; font-size: 7.5px; text-transform: uppercase; letter-spacing: 1.2px; color: #94A3B8; font-weight: 700; margin-bottom: 3px; }
.diag-val {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: var(--hdv-navy);
    border-bottom: 2px solid {{ $diagBorder }};
    padding-bottom: 2px;
    white-space: nowrap;
}

/* ── PRECIO — caja con borde, sin degradado ──────────────────────────────── */
.price-card {
    border: 1px solid #E2E8F0;
    border-left: 3px solid var(--hdv-navy);
    border-radius: 4px;
    padding: 16px 22px;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 28px;
    flex-shrink: 0;
}
.price-card-left { flex: 1; min-width: 0; }
.price-eyebrow { font-size: 8px; text-transform: uppercase; letter-spacing: 2px; color: #94A3B8; font-weight: 700; margin-bottom: 7px; }
.price-figure { display: flex; align-items: flex-start; line-height: 1; margin-bottom: 7px; }
.price-dollar { font-size: 22px; font-weight: 400; color: #94A3B8; margin-top: 5px; margin-right: 2px; }
.price-amount { font-size: 40px; font-weight: 800; color: var(--hdv-navy); letter-spacing: -1.5px; line-height: 1; }
.price-mxn-tag { font-size: 9px; font-weight: 700; color: #94A3B8; letter-spacing: 1px; text-transform: uppercase; margin-left: 6px; align-self: flex-end; padding-bottom: 5px; }
.price-meta { font-size: 10.5px; color: #64748B; }
.price-meta strong { color: var(--hdv-navy); font-weight: 700; }

.kpi-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-template-rows: 1fr 1fr;
    border: 1px solid #E2E8F0;
    border-radius: 4px;
    overflow: hidden;
    flex-shrink: 0;
    width: 230px;
}
.kpi-cell { padding: 10px 13px; border-right: 1px solid #E2E8F0; border-bottom: 1px solid #E2E8F0; }
.kpi-cell:nth-child(2n) { border-right: none; }
.kpi-cell:nth-child(3), .kpi-cell:nth-child(4) { border-bottom: none; }
.kpi-label { display: block; font-size: 7px; text-transform: uppercase; letter-spacing: 1px; color: #94A3B8; font-weight: 600; margin-bottom: 3px; }
.kpi-value { display: block; font-size: 14px; font-weight: 800; color: var(--hdv-navy); letter-spacing: -.3px; }

/* ── RANGO DE VALOR ───────────────────────────────────────────────────────── */
.range-card { border: 1px solid #E2E8F0; border-radius: 4px; padding: 16px 24px 20px; margin-bottom: 14px; flex-shrink: 0; }
.rv-outer { position: relative; margin: 0 8px; }
.rv-track { position: relative; height: 3px; background: #E2E8F0; border-radius: 2px; margin: 28px 0 32px; }
.rv-dot {
    position: absolute; top: 50%; transform: translate(-50%, -50%);
    width: 9px; height: 9px; border-radius: 50%;
    background: #fff; border: 2px solid #94A3B8; z-index: 2;
}
.rv-dot-accent { width: 13px; height: 13px; background: var(--hdv-navy); border: 2.5px solid #fff; box-shadow: 0 0 0 1.5px var(--hdv-navy); z-index: 3; }
.rv-lbl { position: absolute; bottom: calc(100% + 9px); transform: translateX(-50%); font-size: 7.5px; text-transform: uppercase; letter-spacing: 1px; color: #94A3B8; font-weight: 600; white-space: nowrap; }
.rv-lbl-accent { color: var(--hdv-navy); font-weight: 800; font-size: 7.5px; }
.rv-price { position: absolute; top: calc(100% + 10px); transform: translateX(-50%); font-size: 10.5px; font-weight: 700; color: #475569; white-space: nowrap; }
.rv-price-accent { font-size: 11.5px; font-weight: 800; color: var(--hdv-navy); }

/* ── ZONA + A CONSIDERAR ──────────────────────────────────────────────────── */
.zone-split { display: flex; flex: 1; min-height: 0; overflow: hidden; border: 1px solid #E2E8F0; border-radius: 4px; }
.zone-map-col { flex: 0 0 56%; border-right: 1px solid #E2E8F0; overflow: hidden; position: relative; display: flex; align-items: stretch; }
.zone-map-col img { width: 100%; height: 100%; object-fit: cover; display: block; }
.zone-placeholder { width: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #F8FAFC; padding: 24px; text-align: center; }
.zone-ph-pin { width: 12px; height: 12px; border-radius: 50%; background: var(--hdv-navy); margin: 0 auto 10px; }
.zone-ph-name { font-size: 14px; font-weight: 800; color: var(--hdv-navy); margin-bottom: 4px; }
.zone-ph-sub { font-size: 8.5px; color: #94A3B8; text-transform: uppercase; letter-spacing: 1px; }

.zone-notes-col { flex: 0 0 44%; padding: 18px 22px; background: #fff; display: flex; flex-direction: column; overflow: hidden; }
.zone-notes-header { font-size: 8px; text-transform: uppercase; letter-spacing: 1.5px; color: #94A3B8; font-weight: 700; padding-bottom: 9px; border-bottom: 1px solid #F1F5F9; margin-bottom: 12px; }
.zone-note-item { display: flex; gap: 10px; align-items: flex-start; margin-bottom: 10px; }
.zone-note-item:last-child { margin-bottom: 0; }
.zone-note-num {
    flex-shrink: 0; width: 16px; height: 16px; border-radius: 50%;
    background: #fff; border: 1px solid #CBD5E1; color: var(--hdv-navy);
    font-size: 8px; font-weight: 800; display: flex; align-items: center; justify-content: center;
    margin-top: 1px;
}
.zone-note-text { font-size: 10.5px; color: #475569; line-height: 1.6; }

/* ══════════════════════════════════════════════════════════════════════════
   PAGE 2 — ANÁLISIS TÉCNICO
   ══════════════════════════════════════════════════════════════════════════ */
.p2-inner { flex: 1; overflow: hidden; padding: 20px 48px 12px; }

.chars-grid { width: 100%; border-collapse: collapse; border: 1px solid #E2E8F0; border-radius: 4px; overflow: hidden; margin-bottom: 12px; }
.chars-grid td { padding: 7px 12px; border-right: 1px solid #E2E8F0; border-bottom: 1px solid #E2E8F0; vertical-align: top; width: 25%; }
.chars-grid tr:last-child td { border-bottom: none; }
.chars-grid td:last-child { border-right: none; }
.char-label { display: block; font-size: 7.5px; text-transform: uppercase; letter-spacing: 1px; color: #94A3B8; font-weight: 600; margin-bottom: 3px; }
.char-value { display: block; font-size: 11.5px; font-weight: 700; color: var(--hdv-navy); line-height: 1.3; }

.tags-line { margin-bottom: 14px; font-size: 10.5px; color: #475569; line-height: 1.8; }
.tags-lbl { font-size: 8.5px; text-transform: uppercase; letter-spacing: 1px; color: #94A3B8; font-weight: 700; margin-right: 5px; }

/* ── TABLA WATERFALL ──────────────────────────────────────────────────────── */
.wf { width: 100%; border-collapse: collapse; font-size: 10.5px; }
.wf thead th {
    border-bottom: 2px solid var(--hdv-navy);
    padding: 6px 10px;
    font-size: 7.5px;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 700;
    color: #64748B;
    text-align: left;
}
.wf thead th:nth-child(n+2) { text-align: right; }
.wf tbody td { padding: 6px 10px; border-bottom: 1px solid #F1F5F9; vertical-align: middle; }
.wf tbody td:nth-child(n+2) { text-align: right; font-weight: 700; font-feature-settings: "tnum"; }
.wf .r-base td { background: #F8FAFC; }
.wf .r-base td:first-child { font-weight: 700; color: #334155; }
.wf .r-total td { border-top: 2px solid var(--hdv-navy); border-bottom: none; font-weight: 800; color: var(--hdv-navy); }
.wf-factor-name { font-weight: 600; color: #1e293b; font-size: 10.5px; }
.wf-factor-sub { font-size: 8.5px; color: #94A3B8; margin-top: 1px; line-height: 1.4; }
.wf-adj { color: #334155; }
.price-muted { color: #94A3B8; font-size: 9.5px; font-weight: 500; }

/* ══════════════════════════════════════════════════════════════════════════
   PAGE 3 — ANÁLISIS DE MERCADO
   ══════════════════════════════════════════════════════════════════════════ */
.p3-inner { flex: 1; overflow: hidden; padding: 20px 48px 12px; }

.narr-lead { font-size: 11.5px; color: #334155; line-height: 1.75; margin-bottom: 16px; text-align: justify; }

.str-risk-row { display: flex; gap: 16px; margin-bottom: 16px; }
.sr-card { flex: 1; padding: 12px 0 0; border-top: 2px solid; }
.sr-card-pos { border-top-color: var(--hdv-navy); }
.sr-card-neg { border-top-color: #CBD5E1; }
.sr-eyebrow { font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 6px; color: #64748B; }
.sr-text { font-size: 10.5px; color: #475569; line-height: 1.7; text-align: justify; }

.rec-box { padding: 14px 20px; border: 1px solid #E2E8F0; border-left: 3px solid var(--hdv-navy); border-radius: 0 4px 4px 0; margin-bottom: 16px; }
.rec-eyebrow { font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: #64748B; margin-bottom: 6px; }
.rec-text { font-size: 11px; color: #1e293b; line-height: 1.75; }

.key-factors-wrap { margin-bottom: 16px; }
.key-factors-lbl { font-size: 7.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: #94A3B8; margin-bottom: 7px; }
.kf-list { margin: 0; padding-left: 16px; }
.kf-list li { font-size: 10px; color: #334155; line-height: 1.7; }

.rule { border: none; border-top: 1px solid #E2E8F0; margin: 14px 0; }

/* ── CONTACTO ─────────────────────────────────────────────────────────────── */
.contact-strip { display: flex; border: 1px solid #E2E8F0; border-radius: 4px; overflow: hidden; margin-top: 12px; }
.contact-item { flex: 1; padding: 10px 14px; border-right: 1px solid #E2E8F0; }
.contact-item:last-child { border-right: none; }
.contact-lbl { font-size: 7px; text-transform: uppercase; letter-spacing: 1px; color: #94A3B8; font-weight: 700; margin-bottom: 4px; }
.contact-val { font-size: 10.5px; font-weight: 700; color: var(--hdv-navy); line-height: 1.35; }

/* ── NOTAS (alerta funcional — se conserva en ámbar) ─────────────────────── */
.notes-box { margin-top: 12px; padding: 10px 14px; background: #FFFBEB; border-left: 3px solid #F59E0B; border-radius: 0 3px 3px 0; }
.notes-lbl { font-size: 7.5px; text-transform: uppercase; letter-spacing: 1.5px; color: #92400E; font-weight: 700; margin-bottom: 4px; }
.notes-text { font-size: 10.5px; color: #78350F; line-height: 1.6; }
</style>
</head>
<body>

{{-- ═══════════════════════════════════════════════════════════════════════════
     PÁGINA 1 — Portada
     ═══════════════════════════════════════════════════════════════════════════ --}}
<div class="page page-break">

    <div class="doc-hd">
        <div class="doc-hd-logo">
            @if($logoSrcLight)
                <img src="{{ $logoSrcLight }}" alt="{{ $siteName }}">
            @elseif($logoSrc)
                <img src="{{ $logoSrc }}" alt="{{ $siteName }}">
            @else
                <div class="doc-hd-logo-txt">Home del Valle</div>
            @endif
        </div>
        <div class="doc-hd-right">
            <div class="doc-hd-tag">Opinión de Valor · Confidencial</div>
            <div class="doc-hd-folio">{{ $folio }} · {{ $today }}</div>
        </div>
    </div>

    <div class="p1-inner">

        <div class="prop-title">{{ $addressDisplay }}</div>
        @if($addressSubline)
        <div class="prop-sub">{{ $addressSubline }}</div>
        @endif

        <div class="prop-meta-row">
            <div class="prop-chips">
                @if($propietarioNombre)
                <span class="prop-chip">Preparada para {{ $propietarioNombre }}</span>
                @endif
                <span class="prop-chip">{{ $typeLabel }}</span>
                <span class="prop-chip">{{ $colonia }} · {{ $zone }}</span>
                <span class="prop-chip">{{ $ageLabel }}</span>
                <span class="prop-chip">Conservación: {{ $condLabel }}</span>
                <span class="prop-chip prop-chip-muted">Confianza {{ $confidLabel }}</span>
            </div>
            <div class="diag-line">
                <span class="diag-lbl">Diagnóstico</span>
                <span class="diag-val">{{ $diagLabel }}</span>
            </div>
        </div>

        {{-- PRECIO --}}
        @if($sug)
        <div class="price-card">
            <div class="price-card-left">
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
                    <span class="kpi-value">{{ ($adjTotal >= 0 ? '+' : '') . $adjTotal }}%</span>
                </div>
                <div class="kpi-cell">
                    <span class="kpi-label">m² efectivos</span>
                    <span class="kpi-value">{{ number_format($valuation->effective_m2, 0) }} m²</span>
                </div>
            </div>
        </div>
        @endif

        {{-- RANGO DE VALOR --}}
        @if($low && $high)
        <div class="range-card">
            <div class="sec-lbl">Rango de Valor Estimado</div>
            <div class="rv-outer">
                <div class="rv-track">
                    <div class="rv-dot" style="left:{{ $pLow }}%;">
                        <span class="rv-lbl">Mínimo</span>
                        <span class="rv-price">${{ number_format($low) }}</span>
                    </div>
                    <div class="rv-dot" style="left:{{ $pMid }}%;">
                        <span class="rv-lbl">Medio</span>
                        <span class="rv-price">${{ number_format($mid) }}</span>
                    </div>
                    <div class="rv-dot" style="left:{{ $pHigh }}%;">
                        <span class="rv-lbl">Máximo</span>
                        <span class="rv-price">${{ number_format($high) }}</span>
                    </div>
                    <div class="rv-dot rv-dot-accent" style="left:{{ $pSug }}%;">
                        <span class="rv-lbl rv-lbl-accent">▲ Sugerido</span>
                        <span class="rv-price rv-price-accent">${{ number_format($sug) }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ZONA + A CONSIDERAR --}}
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

    </div>{{-- /p1-inner --}}

    <div class="doc-ft">
        <span>{{ $siteUrl }}</span>
        <span><strong>{{ $siteName }}</strong> · Opinión de Valor</span>
        <span>{{ $folio }} · Página 1 de 3 · Confidencial</span>
    </div>

</div>{{-- /page-1 --}}


{{-- ═══════════════════════════════════════════════════════════════════════════
     PÁGINA 2 — Análisis técnico: características + waterfall
     ═══════════════════════════════════════════════════════════════════════════ --}}
<div class="page page-break">

    <div class="doc-hd">
        <div class="doc-hd-logo">
            @if($logoSrcLight)
                <img src="{{ $logoSrcLight }}" alt="{{ $siteName }}">
            @elseif($logoSrc)
                <img src="{{ $logoSrc }}" alt="{{ $siteName }}">
            @else
                <div class="doc-hd-logo-txt">Home del Valle</div>
            @endif
        </div>
        <div class="doc-hd-right">
            <div class="doc-hd-tag">Análisis Técnico · Confidencial</div>
            <div class="doc-hd-folio">{{ $folio }}</div>
        </div>
    </div>

    <div class="p2-inner">

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
        if ($valuation->input_renovation_year) $charCells[] = ['Última remodelación', (string) $valuation->input_renovation_year];
        if ($valuation->input_street_type)   $charCells[] = ['Entorno', $valuation->street_type_label];
        if ($valuation->input_views)         $charCells[] = ['Vistas', $valuation->views_label];
        if ($valuation->input_legal_status)  $charCells[] = ['Estado legal', $valuation->legal_status_label];
        if ($valuation->input_maintenance_fee) $charCells[] = ['Mantenimiento', '$' . number_format($valuation->input_maintenance_fee) . '/mes'];
        // Pad to multiple of 4
        while (count($charCells) % 4 !== 0) $charCells[] = null;
        $charRows = array_chunk($charCells, 4);

        // Amenidades / seguridad / infraestructura como texto, no pills
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

        @if($amenTags || $secTags || $infraTags)
        <div class="tags-line">
            @if($amenTags)<span class="tags-lbl">Amenidades</span>{{ implode(', ', $amenTags) }}@endif
            @if($secTags)<br><span class="tags-lbl">Seguridad</span>{{ implode(', ', $secTags) }}@endif
            @if($infraTags)<br><span class="tags-lbl">Infraestructura</span>{{ implode(', ', $infraTags) }}@endif
        </div>
        @endif

        {{-- WATERFALL DETALLADO --}}
        @if($valuation->adjustments->isNotEmpty())
        <div class="sec-lbl">Factores de Ajuste — Metodología Waterfall</div>
        <table class="wf">
            <thead>
                <tr>
                    <th>Factor</th>
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
                    <td class="price-muted">—</td>
                    <td class="price-muted">—</td>
                    <td style="font-weight:700;color:var(--hdv-navy);">${{ number_format($valuation->base_price_m2) }}/m²</td>
                </tr>

                {{-- Adjustment rows --}}
                @foreach($valuation->adjustments as $adj)
                @php $v = (float)$adj->adjustment_value; @endphp
                <tr>
                    <td>
                        <div class="wf-factor-name">{{ $adj->factor_label }}</div>
                        @if($adj->explanation)
                        <div class="wf-factor-sub">{{ $adj->explanation }}</div>
                        @endif
                    </td>
                    <td class="wf-adj">{{ $adj->formatted_value }}</td>
                    <td class="price-muted">${{ number_format($adj->price_before) }}/m²</td>
                    <td style="font-weight:700;color:var(--hdv-navy);">${{ number_format($adj->price_after) }}/m²</td>
                </tr>
                @endforeach

                {{-- Total row --}}
                @php $tPct = round((($valuation->adjusted_price_m2 - $valuation->base_price_m2) / $valuation->base_price_m2) * 100, 1); @endphp
                <tr class="r-total">
                    <td>Precio ajustado final · {{ $colonia }}</td>
                    <td>{{ ($tPct >= 0 ? '+' : '') . $tPct }}%</td>
                    <td></td>
                    <td>${{ number_format($valuation->adjusted_price_m2) }}/m²</td>
                </tr>
            </tbody>
        </table>
        @endif

    </div>{{-- /p2-inner --}}

    <div class="doc-ft">
        <span><strong>{{ $siteName }}</strong> — {{ $siteUrl }}</span>
        <span>{{ $folio }} · Página 2 de 3 · Confidencial</span>
    </div>

</div>{{-- /page-2 --}}


{{-- ═══════════════════════════════════════════════════════════════════════════
     PÁGINA 3 — Análisis de mercado
     ═══════════════════════════════════════════════════════════════════════════ --}}
<div class="page">

    <div class="doc-hd">
        <div class="doc-hd-logo">
            @if($logoSrcLight)
                <img src="{{ $logoSrcLight }}" alt="{{ $siteName }}">
            @elseif($logoSrc)
                <img src="{{ $logoSrc }}" alt="{{ $siteName }}">
            @else
                <div class="doc-hd-logo-txt">Home del Valle</div>
            @endif
        </div>
        <div class="doc-hd-right">
            <div class="doc-hd-tag">Análisis de Mercado · Confidencial</div>
            <div class="doc-hd-folio">{{ $folio }}</div>
        </div>
    </div>

    <div class="p3-inner">

        @if(!empty($n['market_context']) || !empty($n['recommendation']))

        <div class="sec-lbl">Análisis Profesional de Mercado</div>

        @if(!empty($n['market_context']))
        <p class="narr-lead">{{ $n['market_context'] }}</p>
        @endif

        @if(!empty($n['property_strengths']) || !empty($n['property_risks']))
        <div class="str-risk-row">
            @if(!empty($n['property_strengths']))
            <div class="sr-card sr-card-pos">
                <div class="sr-eyebrow">Fortalezas del inmueble</div>
                <div class="sr-text">{{ $n['property_strengths'] }}</div>
            </div>
            @endif
            @if(!empty($n['property_risks']))
            <div class="sr-card sr-card-neg">
                <div class="sr-eyebrow">Riesgo principal</div>
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
            <ul class="kf-list">
                @foreach($n['key_factors'] as $f)
                <li>{{ $f }}</li>
                @endforeach
            </ul>
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

    </div>{{-- /p3-inner --}}

    <div class="p3-footer">
        <div class="doc-ft" style="border-top:1px solid #E2E8F0;">
            <span><strong>{{ $siteName }}</strong> — {{ $siteUrl }}</span>
            <span>{{ $folio }} · Página 3 de 3 · Confidencial</span>
        </div>
        <div class="doc-ft-legal">
            Esta Opinión de Valor es elaborada por {{ $siteName }} con base en datos de oferta publicada en portales inmobiliarios y ajustes estadísticos descritos en este documento.
            <strong>No constituye un avalúo formal</strong> con efectos fiscales, notariales o de crédito hipotecario.
            Para dichos efectos se requiere valuador certificado (INDAABIN / SHF / AMPI). El valor de cierre depende de las condiciones de cada negociación.
            &nbsp;·&nbsp; &copy; {{ now()->year }} {{ $siteName }} · Todos los derechos reservados.
        </div>
    </div>

</div>{{-- /page-3 --}}

</body>
</html>
