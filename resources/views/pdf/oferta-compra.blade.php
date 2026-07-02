@php include(resource_path('views/pdf/_brand_data.php')); @endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Carta Oferta de Compraventa — {{ $folio }}</title>
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
    font-size: 11.5px;
    line-height: 1.65;
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
.inner      { flex: 1; padding: 34px 52px 16px; display: flex; flex-direction: column; overflow: hidden; }
.page-foot  {
    flex-shrink: 0; border-top: 1px solid #e2e8f0; padding: 8px 52px;
    display: flex; justify-content: space-between; align-items: center;
    font-size: 8.5px; color: #94a3b8;
}
.page-foot strong { color: var(--hdv-navy); font-weight: 600; }

.doc-title { font-size: 18px; font-weight: 800; color: var(--hdv-navy); text-align: center; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 4px; }
.doc-folio { font-size: 9px; color: #94a3b8; text-align: center; margin-bottom: 22px; letter-spacing: .5px; }

.meta-line { font-size: 11.5px; color: #334155; margin-bottom: 4px; }
.meta-line strong { color: #0f172a; }

p { color: #334155; font-size: 11.5px; line-height: 1.7; margin-bottom: 10px; text-align: justify; }
strong { color: #0f172a; }

.buyer-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; margin: 12px 0 16px; }
.buyer-box .row { display: flex; gap: 6px; font-size: 10.5px; margin-bottom: 3px; }
.buyer-box .row:last-child { margin-bottom: 0; }
.buyer-box .lbl { color: #94a3b8; min-width: 130px; text-transform: uppercase; font-size: 8.5px; font-weight: 700; letter-spacing: .5px; padding-top: 1px; }
.buyer-box .val { color: #0f172a; font-weight: 600; }

.offer-table { width: 100%; border-collapse: collapse; margin: 4px 0 16px; font-size: 11px; }
.offer-table td { padding: 8px 12px; border-bottom: 1px solid #f1f5f9; }
.offer-table td:first-child { color: #64748b; width: 42%; }
.offer-table td:last-child { color: var(--hdv-navy); font-weight: 700; text-align: right; }
.offer-table tr:last-child td { border-bottom: none; }
.offer-table .letras { font-size: 8.5px; color: #94a3b8; font-weight: 400; text-align: right; text-transform: uppercase; }

.clauses { counter-reset: clause; margin: 6px 0 14px; }
.clause { counter-increment: clause; padding: 8px 0 8px 26px; position: relative; border-bottom: 1px solid #f8fafc; font-size: 11px; line-height: 1.65; color: #334155; text-align: justify; }
.clause:last-child { border-bottom: none; }
.clause::before { content: counter(clause) "."; position: absolute; left: 0; top: 8px; color: var(--hdv-navy); font-weight: 800; font-size: 11px; }
.clause strong { color: #0f172a; }

.sign-row { display: flex; gap: 40px; margin-top: 28px; }
.sign-col { flex: 1; text-align: center; }
.sign-line { border-top: 1px solid #0f172a; padding-top: 6px; margin-top: 40px; font-size: 9.5px; color: #475569; }
.sign-name { font-size: 11px; font-weight: 700; color: #0f172a; }

.privacy-note { font-size: 8.5px; color: #94a3b8; line-height: 1.6; margin-top: 18px; border-top: 1px solid #f1f5f9; padding-top: 10px; }
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

    <div class="doc-title">Carta Oferta de Compraventa</div>
    <div class="doc-folio">Folio {{ $folio }} · Ciudad de México, a {{ $fecha }}</div>

    <p class="meta-line"><strong>HOME DEL VALLE BIENES RAÍCES</strong><br>P R E S E N T E. —</p>

    <p>Por medio de la presente, el/la suscrito(a) manifiesta su interés en adquirir el inmueble ubicado en <strong>{{ $propertyAddress }}</strong>{{ $propertyExtra ? ', ' . $propertyExtra : '' }}, por lo que formula la siguiente oferta formal de compraventa, sujeta a los términos y condiciones establecidos en este documento:</p>

    <div class="buyer-box">
      <div class="row"><span class="lbl">Oferente</span><span class="val">{{ $buyerName }}</span></div>
      @if($buyerId)<div class="row"><span class="lbl">Identificación</span><span class="val">{{ $buyerId }}</span></div>@endif
      @if($buyerCurpRfc)<div class="row"><span class="lbl">CURP / RFC</span><span class="val">{{ $buyerCurpRfc }}</span></div>@endif
      @if($buyerAddress)<div class="row"><span class="lbl">Domicilio</span><span class="val">{{ $buyerAddress }}</span></div>@endif
      @if($propertyFull)<div class="row"><span class="lbl">Inmueble</span><span class="val">{{ $propertyFull }}</span></div>@endif
    </div>

    <table class="offer-table">
      <tr>
        <td>Precio ofertado</td>
        <td>
          ${{ number_format($offer->precio_ofertado, 2) }} MXN
          <div class="letras">({{ $precioLetras }})</div>
        </td>
      </tr>
      @if($offer->monto_apartado)
      <tr><td>Apartado, previa revisión de documentos</td><td>${{ number_format($offer->monto_apartado, 2) }} MXN</td></tr>
      @endif
      @if($offer->pago_firma_contrato)
      <tr><td>Pago a la firma del contrato de compraventa</td><td>${{ number_format($offer->pago_firma_contrato, 2) }} MXN</td></tr>
      @endif
      @if($offer->pago_firma_escritura)
      <tr><td>Pago a la firma de la escritura</td><td>${{ number_format($offer->pago_firma_escritura, 2) }} MXN</td></tr>
      @endif
      @if($offer->forma_pago)
      <tr><td>Forma de pago</td><td>{{ $offer->forma_pago }}</td></tr>
      @endif
    </table>

    <div class="clauses">
      <div class="clause">{!! \App\Services\PurchaseOfferGeneratorService::clause('vigencia', ['vigencia_dias' => $offer->vigencia_dias, 'vigencia_hasta' => $vigenciaHasta]) !!}</div>

      <div class="clause">{!! \App\Services\PurchaseOfferGeneratorService::clause('condicion_suspensiva') !!}</div>

      <div class="clause">{!! \App\Services\PurchaseOfferGeneratorService::clause('apartado') !!}</div>

      <div class="clause">{!! \App\Services\PurchaseOfferGeneratorService::clause('naturaleza_juridica') !!}</div>

      <div class="clause">{!! \App\Services\PurchaseOfferGeneratorService::clause('privacidad') !!}</div>
    </div>

    @if($offer->comentarios)
    <p><strong>Comentarios adicionales:</strong> {{ $offer->comentarios }}</p>
    @endif

    <div class="sign-row">
      <div class="sign-col">
        <div class="sign-line">
          <div class="sign-name">{{ $buyerName }}</div>
          Nombre y firma del oferente
        </div>
      </div>
    </div>

    <div class="privacy-note">Documento confidencial. Generado por el sistema de Home del Valle el {{ $fecha }}. Folio {{ $folio }}.</div>

  </div></div>
  <div class="page-foot"><strong>Home del Valle</strong><span>Pocos inmuebles. Más control. Mejores resultados.</span><span>Carta Oferta · {{ $folio }}</span></div>
</div>

</body>
</html>
