@php include(resource_path('views/pdf/_brand_data.php')); @endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Ficha Técnica — {{ $folio }}</title>
<style>
{!! $brandCssVars ?? '' !!}
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
@page { size: 215.9mm 279.4mm; }

body {
    font-family: 'Inter', Arial, sans-serif;
    background: #fff;
    color: #1e293b;
    font-size: 10.5px;
    line-height: 1.55;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}

.doc-title { font-size: 19px; font-weight: 800; color: var(--hdv-navy); text-align: center; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 4px; }
.doc-folio { font-size: 9.5px; color: #64748b; text-align: center; margin-bottom: 18px; }

.summary-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; margin-bottom: 16px; break-inside: avoid; }
.summary-title { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .5px; color: var(--hdv-navy); margin-bottom: 8px; }
.terms-table { width: 100%; border-collapse: collapse; font-size: 10px; }
.terms-table td { padding: 4px 8px; border-bottom: 1px solid #eef2f7; vertical-align: top; }
.terms-table td:first-child { color: #64748b; width: 38%; font-weight: 600; }
.terms-table td:last-child { color: #0f172a; font-weight: 700; }
.terms-table tr:last-child td { border-bottom: none; }

.section-h { font-size: 12px; font-weight: 800; color: var(--hdv-navy); text-transform: uppercase; letter-spacing: 1px; margin: 18px 0 10px; padding-bottom: 4px; border-bottom: 2px solid var(--hdv-accent); break-after: avoid; }

.photo-grid { display: flex; gap: 8px; margin-bottom: 6px; break-inside: avoid; }
.photo-grid img { width: 100%; height: 130px; object-fit: cover; border-radius: 6px; }

.verdict-badge { display: inline-block; padding: 5px 14px; border-radius: 20px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 10px; }
.verdict-compra_directa, .verdict-viable { background: #dcfce7; color: #166534; }
.verdict-negocia { background: #fef3c7; color: #92400e; }
.verdict-descarta { background: #fee2e2; color: #991b1b; }

.metric-row { display: flex; gap: 10px; margin-bottom: 10px; break-inside: avoid; }
.metric-box { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px; text-align: center; }
.metric-val { font-size: 15px; font-weight: 800; color: var(--hdv-navy); }
.metric-lbl { font-size: 8px; color: #64748b; text-transform: uppercase; letter-spacing: .4px; margin-top: 2px; }

p { color: #334155; font-size: 10.5px; line-height: 1.6; margin-bottom: 6px; text-align: justify; }
strong { color: #0f172a; }

.disclaimer { margin-top: 20px; padding: 10px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 8.5px; color: #64748b; break-inside: avoid; }
</style>
</head>
<body>

<div class="doc-title">Ficha Técnica de Predio</div>
<div class="doc-folio">Folio {{ $folio }} · Ciudad de México, a {{ $fecha }}</div>

<div class="summary-box">
    <div class="summary-title">{{ $property->title }}</div>
    <table class="terms-table">
        <tr><td>Dirección</td><td>{{ $property->address }}{{ $property->colony ? ', Col. ' . $property->colony : '' }}</td></tr>
        <tr><td>Tipo</td><td>{{ \App\Models\Property::PROPERTY_TYPES[$property->property_type] ?? $property->property_type }}</td></tr>
        <tr><td>Precio</td><td>${{ number_format((float) $property->price, 0) }} {{ $property->currency ?? 'MXN' }}</td></tr>
        <tr><td>Superficie de terreno</td><td>{{ $property->lot_area ? number_format((float) $property->lot_area, 1) . ' m²' : '—' }}</td></tr>
        <tr><td>Superficie de construcción</td><td>{{ $property->construction_area ? number_format((float) $property->construction_area, 1) . ' m²' : '—' }}</td></tr>
    </table>
</div>

@if($property->photos->count())
<div class="photo-grid">
    @foreach($property->photos->take(3) as $photo)
        <img src="{{ storage_path('app/public/' . $photo->path) }}" alt="">
    @endforeach
</div>
@endif

<div class="section-h">Dimensiones y zonificación</div>
<table class="terms-table">
    <tr><td>Frente</td><td>{{ $profile?->frente ? number_format((float) $profile->frente, 2) . ' m' : 'Sin dato' }}</td></tr>
    <tr><td>Fondo</td><td>{{ $profile?->fondo ? number_format((float) $profile->fondo, 2) . ' m' : 'Sin dato' }}</td></tr>
    <tr><td>Forma del terreno</td><td>{{ $profile?->forma_label ?? 'Sin dato' }}</td></tr>
    <tr><td>Uso de suelo</td><td>{{ $profile?->uso_suelo ?: 'Sin dato' }}</td></tr>
    <tr><td>Zonificación</td><td>{{ $profile?->zonificacion_label ?? 'Sin dato' }}</td></tr>
    <tr><td>COS / CUS</td><td>{{ $profile?->cos ?? '—' }} / {{ $profile?->cus ?? '—' }}</td></tr>
    <tr><td>Niveles permitidos</td><td>{{ $profile?->niveles_permitidos ?? 'Sin dato' }}</td></tr>
</table>

@if($vrc)
<div class="section-h">Análisis de potencial constructivo</div>
<span class="verdict-badge verdict-{{ $vrc['verdict'] }}">
    @switch($vrc['verdict'])
        @case('compra_directa') Compra directa — excelente retorno @break
        @case('viable') Viable — procede @break
        @case('negocia') Negocia — marginal, ajustar precio @break
        @default Descarta — inviable al precio actual
    @endswitch
</span>

<div class="metric-row">
    <div class="metric-box"><div class="metric-val">{{ $vrc['roi'] }}%</div><div class="metric-lbl">ROI estimado</div></div>
    <div class="metric-box"><div class="metric-val">{{ $vrc['deptos_estimados'] }}</div><div class="metric-lbl">Deptos. estimados</div></div>
    <div class="metric-box"><div class="metric-val">${{ number_format($vrc['incidencia_m2']) }}</div><div class="metric-lbl">Incidencia $/m² vendible</div></div>
    <div class="metric-box"><div class="metric-val">{{ $vrc['incidencia_pct'] }}%</div><div class="metric-lbl">Incidencia sobre venta</div></div>
</div>

<table class="terms-table">
    <tr><td>m² construibles (huella × CUS)</td><td>{{ number_format($vrc['m2_brutos'], 0) }} m²</td></tr>
    <tr><td>m² vendibles estimados</td><td>{{ number_format($vrc['m2_vendibles'], 0) }} m²</td></tr>
    <tr><td>Precio de venta usado ($/m²)</td><td>${{ number_format($vrc['precio_venta_m2']) }} ({{ $vrc['precio_venta_fuente'] === 'observatorio' ? 'Observatorio de Precios HDV' : 'manual' }})</td></tr>
    <tr><td>Ventas totales estimadas</td><td>${{ number_format($vrc['ventas']) }}</td></tr>
    <tr><td>Costo total estimado</td><td>${{ number_format($vrc['costo_total']) }}</td></tr>
    <tr><td>Utilidad neta estimada</td><td>${{ number_format($vrc['utilidad_neta']) }}</td></tr>
    <tr><td>Valor residual del terreno</td><td>${{ number_format($vrc['valor_residual']) }} (${{ number_format($vrc['valor_residual_m2']) }}/m²)</td></tr>
    <tr><td>Precio de oferta sugerido</td><td>${{ number_format($vrc['precio_oferta']) }} (${{ number_format($vrc['precio_oferta_m2']) }}/m²)</td></tr>
</table>
@endif

<div class="section-h">Situación legal y física</div>
<table class="terms-table">
    <tr><td>Libre de gravamen</td><td>{{ $profile?->libre_gravamen === null ? 'Sin verificar' : ($profile->libre_gravamen ? 'Sí' : 'No') }}</td></tr>
</table>
@if($profile?->restricciones)
<p><strong>Restricciones:</strong> {{ $profile->restricciones }}</p>
@endif
@if($profile?->colindancias)
<p><strong>Colindancias:</strong> {{ $profile->colindancias }}</p>
@endif
@if($profile?->servicios)
<p><strong>Servicios:</strong> {{ $profile->servicios }}</p>
@endif
@if($profile?->situacion_legal)
<p><strong>Situación legal:</strong> {{ $profile->situacion_legal }}</p>
@endif

<div class="disclaimer">
    Esta ficha es una herramienta de análisis preliminar generada por Home del Valle a partir de datos del predio y de referencias de mercado. Las cifras de potencial constructivo y viabilidad son estimaciones — no sustituyen un estudio de mercado, avalúo formal, ni la verificación directa de uso de suelo, COS/CUS y restricciones ante la autoridad correspondiente (SEDUVI). Documento confidencial, generado el {{ $fecha }}. Folio {{ $folio }}.
</div>

</body>
</html>
