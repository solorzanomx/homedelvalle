@php include(resource_path('views/pdf/_brand_data.php')); @endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Recibo de Apartado — {{ $folio }}</title>
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
body { font-family: 'Inter', Arial, sans-serif; color:#1e293b; font-size:12px; line-height:1.6; }

.header { background: var(--hdv-navy); border-bottom: 4px solid var(--hdv-accent); padding: 16px 44px; display:flex; align-items:center; justify-content:space-between; }
.header img { height: 20px; max-width: 150px; object-fit: contain; }
.header .tag { font-size: 9px; letter-spacing: 1px; text-transform: uppercase; color: rgba(199,210,254,.7); }

.body { padding: 40px 56px; }
.doc-title { font-size: 20px; font-weight: 800; color: var(--hdv-navy); text-align: center; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 6px; }
.doc-folio { font-size: 11px; color: #94a3b8; text-align: center; margin-bottom: 34px; letter-spacing: .5px; }

.statement { font-size: 13px; line-height: 1.85; text-align: justify; margin-bottom: 30px; }
.statement strong { color: var(--hdv-navy); }

.info-table { width: 100%; border-collapse: collapse; margin-bottom: 40px; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; }
.info-table td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; vertical-align: top; font-size: 12px; }
.info-table tr:last-child td { border-bottom: none; }
.info-table td:first-child { color: #64748b; font-weight: 700; width: 34%; background: #f8fafc; }
.info-table td:last-child { color: var(--hdv-navy); font-weight: 700; }

.notes { font-size: 11px; color: #64748b; margin-bottom: 40px; }

.sign-row { display:flex; justify-content:center; margin-top: 60px; }
.sign-col { width: 280px; text-align: center; }
.sign-line { border-top: 1px solid #0f172a; padding-top: 6px; margin-top: 50px; font-size: 10px; color: #475569; }
.sign-name { font-size: 12px; font-weight: 700; color: #0f172a; }

.footer { position: fixed; bottom: 0; left: 0; right: 0; border-top: 1px solid #e2e8f0; padding: 10px 44px; display:flex; justify-content:space-between; font-size: 9px; color:#94a3b8; }
</style>
</head>
<body>

<div class="header">
    @if(!empty($brandLogoSrc))<img src="{{ $brandLogoSrc }}" alt="Home del Valle">@else<strong style="color:#fff;">Home del Valle</strong>@endif
    <span class="tag">Documento Legal &middot; Confidencial</span>
</div>

<div class="body">
    <div class="doc-title">Recibo de Apartado</div>
    <div class="doc-folio">Folio {{ $folio }} &middot; {{ $fecha }}</div>

    <div class="statement">
        Recibí de forma cierta y verdadera de <strong>{{ $tenantName }}</strong> la cantidad de <strong>${{ $montoNumero }} MXN</strong> ({{ $montoLetras }}), por concepto de <strong>apartado</strong> para reservar el inmueble ubicado en <strong>{{ $propertyFull }}</strong>, con el fin de dar inicio al proceso de investigación del arrendatario y al trámite de la póliza jurídica correspondiente.
    </div>

    <table class="info-table">
        <tr><td>Arrendatario(a)</td><td>{{ $tenantName }}</td></tr>
        <tr><td>Inmueble</td><td>{{ $propertyFull }}</td></tr>
        <tr><td>Monto recibido</td><td>${{ $montoNumero }} MXN</td></tr>
        <tr><td>Forma de pago</td><td>{{ $metodoLabel ?: '—' }}</td></tr>
        <tr><td>Fecha</td><td>{{ $fecha }}</td></tr>
    </table>

    @if(!empty($rental->apartado_notes))
    <div class="notes"><strong>Notas:</strong> {{ $rental->apartado_notes }}</div>
    @endif

    <div class="sign-row">
        <div class="sign-col">
            <div class="sign-line">
                <div class="sign-name">{{ $recibeName }}</div>
                Home del Valle Bienes Raíces
            </div>
        </div>
    </div>
</div>

<div class="footer">
    <span>Home del Valle</span>
    <span>Recibo de Apartado &middot; {{ $folio }}</span>
</div>

</body>
</html>
