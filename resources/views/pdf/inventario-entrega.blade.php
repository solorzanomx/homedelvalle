@php include(resource_path('views/pdf/_brand_data.php')); @endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Inventario de Entrega — {{ $folio }}</title>
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
@page { size: 215.9mm 279.4mm; margin: 0; }

body {
    font-family: 'Inter', Arial, sans-serif;
    background: #fff;
    color: #1e293b;
    font-size: 10px;
    line-height: 1.5;
    width: 215.9mm;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}

.page {
    width: 215.9mm;
    min-height: 279.4mm;
    display: flex;
    flex-direction: column;
    break-after: page;
    page-break-after: always;
}
.page:last-child { break-after: auto; page-break-after: auto; }
.page-header-inner {
    flex-shrink: 0; background: var(--hdv-navy); border-bottom: 4px solid var(--hdv-accent);
    padding: 10px 50px; display: flex; align-items: center; justify-content: space-between;
}
.page-header-inner img { height: 18px; max-width: 140px; object-fit: contain; display: block; }
.page-header-inner span.phi-text { font-size: 12px; font-weight: 700; color: #fff; }
.page-header-inner .phi-tag { font-size: 8.5px; letter-spacing: 1px; text-transform: uppercase; color: rgba(199,210,254,.7); }
.page-body  { flex: 1; display: flex; flex-direction: column; }
.inner      { flex: 1; padding: 16px 50px 10px; display: flex; flex-direction: column; }
.page-foot  {
    flex-shrink: 0; border-top: 1px solid #e2e8f0; padding: 8px 50px;
    display: flex; justify-content: space-between; align-items: center;
    font-size: 8.5px; color: #94a3b8;
}
.page-foot strong { color: var(--hdv-navy); font-weight: 600; }

.doc-title { font-size: 14px; font-weight: 800; color: var(--hdv-navy); text-align: center; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 2px; }
.doc-folio { font-size: 8px; color: #94a3b8; text-align: center; margin-bottom: 7px; letter-spacing: .5px; }

.parties-row { display: flex; gap: 8px; margin: 6px 0 8px; }
.party-box { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 7px; padding: 6px 10px; }
.party-box .role { font-size: 6.8px; font-weight: 800; text-transform: uppercase; letter-spacing: .5px; color: var(--hdv-navy); margin-bottom: 1px; }
.party-box .name { color: #0f172a; font-weight: 700; font-size: 9.5px; }
.inmueble-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 7px; padding: 6px 10px; margin-bottom: 8px; font-size: 9px; }
.inmueble-box .role { font-size: 6.8px; font-weight: 800; text-transform: uppercase; letter-spacing: .5px; color: var(--hdv-navy); margin-bottom: 1px; }

.section-label { font-size: 8.8px; font-weight: 800; color: var(--hdv-navy); text-transform: uppercase; letter-spacing: .4px; margin: 6px 0 3px; border-bottom: 1.5px solid #e2e8f0; padding-bottom: 2px; }

.items-list { margin: 0 0 4px; padding-left: 4px; columns: 2; column-gap: 20px; }
.item-row { padding: 2px 0 2px 15px; position: relative; font-size: 8.6px; line-height: 1.38; color: #334155; break-inside: avoid; }
.item-row::before { content: ""; position: absolute; left: 0; top: 2.5px; width: 8px; height: 8px; border: 1px solid #334155; border-radius: 2px; background: #fff; }

.grid-3 { display: flex; gap: 8px; margin-bottom: 6px; }
.grid-4 { display: flex; gap: 8px; margin-bottom: 6px; }
.field-box { flex: 1; border: 1px solid #e2e8f0; border-radius: 6px; padding: 4px 7px; }
.field-box .label { font-size: 6.5px; color: #94a3b8; text-transform: uppercase; letter-spacing: .3px; }
.field-box .value { font-size: 9px; font-weight: 700; color: #0f172a; margin-top: 1px; }
.field-box .value-blank { border-bottom: 1px solid #cbd5e1; height: 12px; margin-top: 3px; }

.obs-box { border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 9px; min-height: 26px; font-size: 9px; color: #334155; margin-bottom: 4px; white-space: pre-wrap; }
.obs-lines { margin-bottom: 6px; }
.obs-line { border-bottom: 1px solid #cbd5e1; height: 15px; }

.sign-row { display: flex; justify-content: center; gap: 36px; margin-top: 10px; }
.sign-col { width: 210px; text-align: center; }
.sign-role { font-size: 7px; font-weight: 800; text-transform: uppercase; letter-spacing: .5px; color: var(--hdv-navy); margin-bottom: 3px; }
.sign-line { border-top: 1px solid #0f172a; padding-top: 5px; margin-top: 18px; font-size: 8.5px; color: #475569; }
.sign-name { font-size: 10px; font-weight: 700; color: #0f172a; }

.privacy-note { font-size: 7.5px; color: #94a3b8; line-height: 1.4; margin-top: 8px; border-top: 1px solid #f1f5f9; padding-top: 5px; }
</style>
</head>
<body>

<div class="page">
  <div class="page-header-inner">
    @if(!empty($brandLogoSrcLight))<img src="{{ $brandLogoSrcLight }}" alt="Home del Valle">
    @elseif(!empty($brandLogoSrc))<img src="{{ $brandLogoSrc }}" alt="Home del Valle">
    @else<span class="phi-text">Home del Valle</span>@endif
    <span class="phi-tag">Documento · Confidencial</span>
  </div>
  <div class="page-body"><div class="inner">

    <div class="doc-title">Inventario y Estado del Inmueble al Momento de la Entrega</div>
    <div class="doc-folio">Folio {{ $folio }} · Ciudad de México, a {{ $fecha }}</div>

    <div class="inmueble-box">
      <div class="role">Inmueble</div>
      {{ $inmueble }}
    </div>

    <div class="parties-row">
      <div class="party-box">
        <div class="role">Arrendador</div>
        <div class="name">{{ $arrendador }}</div>
      </div>
      <div class="party-box">
        <div class="role">Arrendatario</div>
        <div class="name">{{ $arrendatario }}</div>
      </div>
    </div>

    <div class="section-label">Estado del inmueble</div>
    <div class="items-list">
      @foreach($items as $item)
        <div class="item-row">{{ $item }}</div>
      @endforeach
    </div>

    <div class="section-label">Llaves y accesos entregados</div>
    <div class="grid-4">
      <div class="field-box"><div class="label">Llaves de recámaras</div>@if($llavesRecamaras)<div class="value">{{ $llavesRecamaras }}</div>@else<div class="value-blank"></div>@endif</div>
      <div class="field-box"><div class="label">Llaves de entrada principal</div>@if($llavesEntrada)<div class="value">{{ $llavesEntrada }}</div>@else<div class="value-blank"></div>@endif</div>
      <div class="field-box"><div class="label">Chips / tarjetas de acceso</div>@if($chipsAcceso)<div class="value">{{ $chipsAcceso }}</div>@else<div class="value-blank"></div>@endif</div>
      <div class="field-box"><div class="label">Controles de estacionamiento</div>@if($controlesEstacionamiento)<div class="value">{{ $controlesEstacionamiento }}</div>@else<div class="value-blank"></div>@endif</div>
    </div>

    <div class="section-label">Observaciones</div>
    @if($observaciones)
    <div class="obs-box">{{ $observaciones }}</div>
    @endif
    <div class="obs-lines">
      <div class="obs-line"></div>
      <div class="obs-line"></div>
      <div class="obs-line"></div>
      <div class="obs-line"></div>
    </div>

    <div class="sign-row">
      <div class="sign-col">
        <div class="sign-role">Entrega</div>
        <div class="sign-line">
          <div class="sign-name">{{ $arrendador }}</div>
          Arrendador
        </div>
      </div>
      <div class="sign-col">
        <div class="sign-role">Recibe a su entera satisfacción</div>
        <div class="sign-line">
          <div class="sign-name">{{ $arrendatario }}</div>
          Arrendatario
        </div>
      </div>
    </div>

    <div class="privacy-note">Documento generado por el sistema de Home del Valle el {{ $fecha }}. Folio {{ $folio }}.</div>

  </div></div>
  <div class="page-foot"><strong>Home del Valle</strong><span>Pocos inmuebles. Más control. Mejores resultados.</span><span>Inventario de Entrega · {{ $folio }}</span></div>
</div>

</body>
</html>
