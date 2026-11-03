@php include(resource_path('views/pdf/_brand_data.php')); @endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Pagarés — {{ $folio }}</title>
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
@page { size: 215.9mm 139.7mm; margin: 0; }

body {
    font-family: 'Inter', Arial, sans-serif;
    background: #fff;
    color: #1e293b;
    font-size: 8.5px;
    line-height: 1.45;
    width: 215.9mm;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}

.page {
    width: 215.9mm;
    height: 139.7mm;
    display: flex;
    flex-direction: column;
    break-after: page;
    page-break-after: always;
    overflow: hidden;
}
.page:last-child { break-after: auto; page-break-after: auto; }

.page-header-inner {
    flex-shrink: 0; background: var(--hdv-navy); border-bottom: 3px solid var(--hdv-accent);
    padding: 5px 16mm; display: flex; align-items: center; justify-content: space-between;
}
.page-header-inner img { height: 12px; max-width: 100px; object-fit: contain; display: block; }
.page-header-inner span.phi-text { font-size: 9px; font-weight: 700; color: #fff; }
.page-header-inner .phi-tag { font-size: 6.5px; letter-spacing: .8px; text-transform: uppercase; color: rgba(199,210,254,.7); }

.inner { flex: 1; padding: 8px 16mm 0; display: flex; flex-direction: column; min-height: 0; }

.doc-title { font-size: 11px; font-weight: 800; color: var(--hdv-navy); text-align: center; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 2px; }
.doc-folio { font-size: 6.5px; color: #94a3b8; text-align: center; margin-bottom: 6px; letter-spacing: .4px; }

.bueno-por {
    background: #f8fafc; border: 1px solid var(--hdv-navy); border-radius: 6px;
    padding: 4px 10px; text-align: center; margin-bottom: 6px;
}
.bueno-por .label { font-size: 6.5px; font-weight: 800; letter-spacing: .7px; color: var(--hdv-navy); text-transform: uppercase; }
.bueno-por .monto { font-size: 13px; font-weight: 800; color: var(--hdv-navy); }
.bueno-por .letras { font-size: 7.5px; color: #475569; }

p { color: #1e293b; font-size: 8.3px; line-height: 1.42; margin-bottom: 5px; text-align: justify; font-weight: 600; }
.clauses p { font-weight: 400; color: #475569; font-size: 7.3px; margin-bottom: 4px; }
strong { color: #0f172a; }

.fecha-firma { font-size: 7.8px; text-align: center; margin: 4px 0 6px; color: #334155; }

.sign-row { display: flex; justify-content: center; gap: 24px; margin-top: auto; padding-bottom: 8px; }
.sign-col { width: 260px; text-align: center; }
.sign-line { border-top: 1px solid #0f172a; padding-top: 3px; margin-top: 14px; font-size: 6.8px; color: #475569; }
.sign-name { font-size: 8px; font-weight: 700; color: #0f172a; }
.sign-addr { font-size: 6.6px; color: #64748b; margin-top: 1px; }
</style>
</head>
<body>

@foreach($pagares as $p)
<div class="page">
  <div class="page-header-inner">
    @if(!empty($brandLogoSrcLight))<img src="{{ $brandLogoSrcLight }}" alt="Home del Valle">
    @elseif(!empty($brandLogoSrc))<img src="{{ $brandLogoSrc }}" alt="Home del Valle">
    @else<span class="phi-text">Home del Valle</span>@endif
    <span class="phi-tag">Documento Legal · Confidencial</span>
  </div>
  <div class="inner">

    <div class="doc-title">Pagaré {{ $p['numero'] }} de {{ $total }}</div>
    <div class="doc-folio">Folio {{ $folio }}-{{ str_pad($p['numero'], 2, '0', STR_PAD_LEFT) }}</div>

    <div class="bueno-por">
      <div class="label">Total a pagar</div>
      <div class="monto">{{ $montoNumero }} M.N.</div>
      <div class="letras">({{ $montoLetras }})</div>
    </div>

    @if($deudorEsPlural)
    <p>DEBEMOS Y PAGAREMOS INCONDICIONAL Y MANCOMUNADAMENTE, EN UNA SOLA EXHIBICIÓN EL DÍA {{ mb_strtoupper($p['fechaVencimiento']) }}, A {{ mb_strtoupper($acreedorNombre) }}, EN SU DOMICILIO UBICADO EN {{ mb_strtoupper($acreedorDomicilio) }}, LA CANTIDAD DE {{ $montoNumero }} M.N. ({{ mb_strtoupper($montoLetras) }}), CORRESPONDIENTES A LA RENTA DEL INMUEBLE UBICADO EN {{ mb_strtoupper($inmuebleDireccion) }}.</p>
    @else
    <p>DEBO Y PAGARÉ INCONDICIONALMENTE, EN UNA SOLA EXHIBICIÓN EL DÍA {{ mb_strtoupper($p['fechaVencimiento']) }}, A {{ mb_strtoupper($acreedorNombre) }}, EN SU DOMICILIO UBICADO EN {{ mb_strtoupper($acreedorDomicilio) }}, LA CANTIDAD DE {{ $montoNumero }} M.N. ({{ mb_strtoupper($montoLetras) }}), CORRESPONDIENTES A LA RENTA DEL INMUEBLE UBICADO EN {{ mb_strtoupper($inmuebleDireccion) }}.</p>
    @endif

    <div class="clauses">
      @foreach($clauses as $c)
        <p data-clause="{{ $c['key'] }}">{!! $c['body'] !!}</p>
      @endforeach
    </div>

    <div class="fecha-firma">Ciudad de México, a {{ $fechaFirma }}.</div>

    <div class="sign-row">
      @foreach($firmantes as $f)
      <div class="sign-col">
        <div class="sign-line">
          <div class="sign-name">{{ $f['nombre'] }}</div>
          SUSCRIPTOR(A)
          <div class="sign-addr">{{ $inmuebleDireccion }}</div>
        </div>
      </div>
      @endforeach
    </div>

  </div>
</div>
@endforeach

</body>
</html>
