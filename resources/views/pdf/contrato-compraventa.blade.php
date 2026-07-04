@php include(resource_path('views/pdf/_brand_data.php')); @endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Contrato de Compraventa — {{ $folio }}</title>
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
    font-size: 11px;
    line-height: 1.6;
    width: 215.9mm;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}

.page {
    width: 215.9mm;
    height: 279.4mm;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    break-after: page;
    page-break-after: always;
}
.page:last-child { break-after: auto; page-break-after: auto; }
.page-header-inner {
    flex-shrink: 0; background: var(--hdv-navy); border-bottom: 4px solid var(--hdv-accent);
    padding: 10px 52px; display: flex; align-items: center; justify-content: space-between;
}
.page-header-inner img { height: 18px; max-width: 140px; object-fit: contain; display: block; }
.page-header-inner span.phi-text { font-size: 12px; font-weight: 700; color: #fff; }
.page-header-inner .phi-tag { font-size: 8.5px; letter-spacing: 1px; text-transform: uppercase; color: rgba(199,210,254,.7); }
.page-body  { flex: 1; overflow: hidden; display: flex; flex-direction: column; }
.inner      { flex: 1; padding: 30px 52px 14px; display: flex; flex-direction: column; overflow: hidden; }
.page-foot  {
    flex-shrink: 0; border-top: 1px solid #e2e8f0; padding: 8px 52px;
    display: flex; justify-content: space-between; align-items: center;
    font-size: 8.5px; color: #94a3b8;
}
.page-foot strong { color: var(--hdv-navy); font-weight: 600; }

.doc-title { font-size: 17px; font-weight: 800; color: var(--hdv-navy); text-align: center; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 4px; }
.doc-folio { font-size: 9px; color: #94a3b8; text-align: center; margin-bottom: 14px; letter-spacing: .5px; }

p { color: #334155; font-size: 11px; line-height: 1.55; margin-bottom: 7px; text-align: justify; }
strong { color: #0f172a; }

.parties-row { display: flex; gap: 12px; margin: 8px 0 12px; }
.party-box { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; }
.party-box .role { font-size: 8px; font-weight: 800; text-transform: uppercase; letter-spacing: .5px; color: var(--hdv-navy); margin-bottom: 4px; }
.party-box .row { display: flex; gap: 6px; font-size: 10px; margin-bottom: 2px; }
.party-box .lbl { color: #94a3b8; min-width: 60px; text-transform: uppercase; font-size: 8px; font-weight: 700; letter-spacing: .5px; padding-top: 1px; }
.party-box .val { color: #0f172a; font-weight: 600; }

.terms-table { width: 100%; border-collapse: collapse; margin: 4px 0 10px; font-size: 11px; }
.terms-table td { padding: 6px 12px; border-bottom: 1px solid #f1f5f9; }
.terms-table td:first-child { color: #64748b; width: 42%; }
.terms-table td:last-child { color: var(--hdv-navy); font-weight: 700; text-align: right; }
.terms-table tr:last-child td { border-bottom: none; }

.clauses { counter-reset: clause; margin: 4px 0 10px; }
.clause { counter-increment: clause; padding: 5px 0 5px 26px; position: relative; border-bottom: 1px solid #f8fafc; font-size: 10.5px; line-height: 1.5; color: #334155; text-align: justify; }
.clause:last-child { border-bottom: none; }
.clause::before { content: counter(clause) "."; position: absolute; left: 0; top: 5px; color: var(--hdv-navy); font-weight: 800; font-size: 10.5px; }
.clause strong { color: #0f172a; }

.sign-row { display: flex; justify-content: center; gap: 40px; margin-top: 8px; }
.sign-col { width: 220px; text-align: center; }
.sign-line { border-top: 1px solid #0f172a; padding-top: 6px; margin-top: 18px; font-size: 9.5px; color: #475569; }
.sign-name { font-size: 11px; font-weight: 700; color: #0f172a; }

.privacy-note { font-size: 8.5px; color: #94a3b8; line-height: 1.6; margin-top: 12px; border-top: 1px solid #f1f5f9; padding-top: 8px; }
</style>
</head>
<body>

<div class="page">
  <div class="page-header-inner">
    @if(!empty($brandLogoSrcLight))<img src="{{ $brandLogoSrcLight }}" alt="Home del Valle">
    @elseif(!empty($brandLogoSrc))<img src="{{ $brandLogoSrc }}" alt="Home del Valle">
    @else<span class="phi-text">Home del Valle</span>@endif
    <span class="phi-tag">Documento Legal · Confidencial</span>
  </div>
  <div class="page-body"><div class="inner">

    <div class="doc-title">Contrato de Compraventa</div>
    <div class="doc-folio">Folio {{ $folio }} · Ciudad de México, a {{ $fecha }}</div>

    <p>Contrato de compraventa que celebran, por una parte, el VENDEDOR, y por la otra, el COMPRADOR, respecto del inmueble ubicado en <strong>{{ $propertyFull }}</strong>, sujeto a las siguientes cláusulas:</p>

    <div class="parties-row">
      <div class="party-box">
        <div class="role">Vendedor</div>
        <div class="row"><span class="lbl">Nombre</span><span class="val">{{ $sellerName }}</span></div>
        @if($sellerId)<div class="row"><span class="lbl">Identificación</span><span class="val">{{ $sellerId }}</span></div>@endif
        @if($sellerCurpRfc)<div class="row"><span class="lbl">CURP/RFC</span><span class="val">{{ $sellerCurpRfc }}</span></div>@endif
      </div>
      <div class="party-box">
        <div class="role">Comprador</div>
        <div class="row"><span class="lbl">Nombre</span><span class="val">{{ $buyerName }}</span></div>
        @if($buyerId)<div class="row"><span class="lbl">Identificación</span><span class="val">{{ $buyerId }}</span></div>@endif
        @if($buyerCurpRfc)<div class="row"><span class="lbl">CURP/RFC</span><span class="val">{{ $buyerCurpRfc }}</span></div>@endif
      </div>
    </div>

    <table class="terms-table">
      <tr><td>Inmueble</td><td>{{ $propertyFull }}</td></tr>
      <tr><td>Precio de compraventa</td><td>{{ $precio }}</td></tr>
      <tr><td>Forma de pago</td><td>{{ $formaPago }}</td></tr>
    </table>

    <div class="clauses">
      <div class="clause">{!! \App\Services\ContratoCompraventaGeneratorService::clause('objeto') !!}</div>

      <div class="clause">{!! \App\Services\ContratoCompraventaGeneratorService::clause('precio', ['precio' => $precio, 'forma_pago' => $formaPago]) !!}</div>

      <div class="clause">{!! \App\Services\ContratoCompraventaGeneratorService::clause('condicion_suspensiva') !!}</div>

      <div class="clause">{!! \App\Services\ContratoCompraventaGeneratorService::clause('entrega_gastos') !!}</div>

      <div class="clause">{!! \App\Services\ContratoCompraventaGeneratorService::clause('penalizacion') !!}</div>

      <div class="clause">{!! \App\Services\ContratoCompraventaGeneratorService::clause('privacidad') !!}</div>
    </div>

    <div class="sign-row">
      <div class="sign-col">
        <div class="sign-line">
          <div class="sign-name">{{ $sellerName }}</div>
          Nombre y firma del vendedor
        </div>
      </div>
      <div class="sign-col">
        <div class="sign-line">
          <div class="sign-name">{{ $buyerName }}</div>
          Nombre y firma del comprador
        </div>
      </div>
    </div>

    <div class="privacy-note">Documento confidencial. Generado por el sistema de Home del Valle el {{ $fecha }}. Folio {{ $folio }}.</div>

  </div></div>
  <div class="page-foot"><strong>Home del Valle</strong><span>Pocos inmuebles. Más control. Mejores resultados.</span><span>Contrato de Compraventa · {{ $folio }}</span></div>
</div>

</body>
</html>
