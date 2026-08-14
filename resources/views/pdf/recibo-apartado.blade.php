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
body { font-family: 'Inter', Arial, sans-serif; color:#1e293b; font-size:11.3px; line-height:1.55; }

p { color:#334155; font-size:10.8px; line-height:1.55; margin-bottom:7px; text-align:justify; }
strong { color:#0f172a; }

.doc-title { font-size:18px; font-weight:800; color:var(--hdv-navy); text-align:center; text-transform:uppercase; letter-spacing:.5px; margin-bottom:5px; }
.doc-folio { font-size:10px; color:#94a3b8; text-align:center; margin-bottom:6px; letter-spacing:.5px; }
.bueno-por { text-align:center; font-size:11px; color:#334155; margin-bottom: 22px; }
.bueno-por strong { color: var(--hdv-navy); }

.info-table { width:100%; border-collapse:collapse; margin:0 0 22px; border:1px solid #e2e8f0; border-radius:8px; overflow:hidden; }
.info-table td { padding:12px 15px; border-bottom:1px solid #f1f5f9; vertical-align:top; font-size:11px; }
.info-table tr:last-child td { border-bottom:none; }
.info-table td:first-child { color:#64748b; font-weight:700; width:34%; background:#f8fafc; }
.info-table td:last-child { color:var(--hdv-navy); font-weight:700; }

.section-title { font-size:12.5px; font-weight:800; color:var(--hdv-navy); text-align:center; text-transform:uppercase; margin:14px 0 8px; letter-spacing:.5px; page-break-after:avoid; }

.clause-block { margin-bottom:9px; page-break-inside:avoid; text-align:justify; }
.clause-block .ctitle { font-weight:800; color:var(--hdv-navy); text-transform:uppercase; display:block; margin-bottom:3px; font-size:11px; }

.sign-row { display:flex; justify-content:center; margin-top:50px; page-break-inside:avoid; }
.sign-col { width:280px; text-align:center; }
.sign-line { border-top:1px solid #0f172a; padding-top:6px; margin-top:44px; font-size:9.3px; color:#475569; }
.sign-name { font-size:11px; font-weight:700; color:#0f172a; }
</style>
</head>
<body>

<div class="doc-title">Recibo de Dep&oacute;sito en Garant&iacute;a y Reserva de Inmueble para Arrendamiento</div>
<div class="doc-folio">Folio {{ $folio }} &middot; Ciudad de M&eacute;xico, a {{ $fecha }}</div>
<div class="bueno-por">Bueno por: <strong>${{ $montoNumero }} M.N.</strong></div>

<p>Recib&iacute; de {{ $arrendataria }} la cantidad de <strong>{{ $montoTexto }}</strong>, por concepto de <strong>DEP&Oacute;SITO EN GARANT&Iacute;A Y RESERVA</strong> respecto de la futura celebraci&oacute;n del contrato de arrendamiento del inmueble ubicado en <strong>{{ $inmueble }}</strong>, propiedad de {{ $arrendador }}, sujeto a los t&eacute;rminos y condiciones que se establecen a continuaci&oacute;n:</p>

<table class="info-table">
    <tr><td>Promitente Arrendataria</td><td>{{ $arrendataria }}</td></tr>
    <tr><td>Promitente Arrendador</td><td>{{ $arrendador }}</td></tr>
    <tr><td>Inmueble</td><td>{{ $inmueble }}</td></tr>
    <tr><td>Renta mensual pactada</td><td>{{ $rentaTexto }}</td></tr>
    <tr><td>Fecha l&iacute;mite para formalizar</td><td>{{ $fechaLimite }}</td></tr>
</table>

<div class="section-title">Cl&aacute;usulas</div>
@foreach($clauses as $c)
<div class="clause-block"><span class="ctitle">{{ $c['title'] }}</span><p>{!! $c['body'] !!}</p></div>
@endforeach

<div class="sign-row">
    <div class="sign-col">
        <p style="font-size:10px; margin-bottom:0;">Le&iacute;do el presente instrumento y enteradas las partes de su contenido y alcance, se expide para constancia en la Ciudad de M&eacute;xico, a {{ $fecha }}.</p>
        <div style="font-size:10px; margin: 14px 0 0;">RECIB&Iacute; DE CONFORMIDAD</div>
        <div class="sign-line">
            <div class="sign-name">{{ $recibeName }}</div>
            {{ $recibeTitle }}<br>
            Home del Valle Bienes Ra&iacute;ces<br>
            Por cuenta y orden del propietario, {{ $arrendador }}
            @if($recibePhone)<br>Tel. {{ $recibePhone }}@endif
            @if($recibeEmail)<br>{{ $recibeEmail }}@endif
        </div>
    </div>
</div>

</body>
</html>
