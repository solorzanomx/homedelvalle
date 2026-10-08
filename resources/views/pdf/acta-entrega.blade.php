@php include(resource_path('views/pdf/_brand_data.php')); @endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Acta de Entrega — {{ $folio }}</title>
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

.parties-row { display: flex; gap: 12px; margin: 8px 0 14px; }
.party-box { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; text-align: center; }
.party-box .role { font-size: 8px; font-weight: 800; text-transform: uppercase; letter-spacing: .5px; color: var(--hdv-navy); margin-bottom: 4px; }
.party-box .name { color: #0f172a; font-weight: 700; font-size: 11px; }

.clauses { counter-reset: clause; margin: 8px 0 14px; }
.clause { counter-increment: clause; padding: 10px 0 10px 26px; position: relative; border-bottom: 1px solid #f8fafc; font-size: 10.5px; line-height: 1.6; color: #334155; text-align: justify; }
.clause:last-child { border-bottom: none; }
.clause::before { content: counter(clause) "."; position: absolute; left: 0; top: 10px; color: var(--hdv-navy); font-weight: 800; font-size: 10.5px; }
.clause strong { color: #0f172a; }

.sign-row { display: flex; justify-content: center; gap: 40px; margin-top: 20px; }
.sign-col { width: 220px; text-align: center; }
.sign-role { font-size: 8px; font-weight: 800; text-transform: uppercase; letter-spacing: .5px; color: var(--hdv-navy); margin-bottom: 4px; }
.sign-line { border-top: 1px solid #0f172a; padding-top: 6px; margin-top: 18px; font-size: 9.5px; color: #475569; }
.sign-name { font-size: 11px; font-weight: 700; color: #0f172a; }

.privacy-note { font-size: 8.5px; color: #94a3b8; line-height: 1.6; margin-top: 16px; border-top: 1px solid #f1f5f9; padding-top: 8px; }
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

    <div class="doc-title">Acta de Entrega y Recepción de Inmueble</div>
    <div class="doc-folio">Folio {{ $folio }} · Ciudad de México, a {{ $fecha }}</div>

    <p>En la Ciudad de México, a {{ $fecha }}, comparecen por una parte el/la señor(a) <strong>{{ $sellerName }}</strong>, en su carácter de propietario y parte vendedora, a quien en lo sucesivo se le denominará &ldquo;EL VENDEDOR&rdquo;; y por la otra, el/la señor(a) <strong>{{ $buyerName }}</strong>, en su carácter de parte compradora, a quien en lo sucesivo se le denominará &ldquo;{{ $compradoraRolLabel }}&rdquo;, quienes hacen constar lo siguiente respecto del inmueble ubicado en <strong>{{ $propertyFull }}</strong>, objeto de la operación de compraventa celebrada entre las partes:</p>

    <div class="parties-row">
      <div class="party-box">
        <div class="role">Entrega</div>
        <div class="name">{{ $sellerName }}</div>
      </div>
      <div class="party-box">
        <div class="role">{{ $compradoraRolLabel }}</div>
        <div class="name">{{ $buyerName }}</div>
      </div>
    </div>

    <div class="clauses">
      @foreach($clauses as $c)
        <div class="clause" data-clause="{{ $c['key'] }}">{!! $c['body'] !!}</div>
      @endforeach
    </div>

    <p>Leída la presente Acta y enteradas las partes de su contenido y alcance, la firman de conformidad por duplicado en la Ciudad de México, en la fecha señalada.</p>

    <div class="sign-row">
      <div class="sign-col">
        <div class="sign-role">Entrega</div>
        <div class="sign-line">
          <div class="sign-name">{{ $sellerName }}</div>
          El vendedor
        </div>
      </div>
      <div class="sign-col">
        <div class="sign-role">Recibe de conformidad</div>
        <div class="sign-line">
          <div class="sign-name">{{ $buyerName }}</div>
          {{ $compradoraRolLabel }}
        </div>
      </div>
    </div>

    <div class="privacy-note">Documento confidencial. Generado por el sistema de Home del Valle el {{ $fecha }}. Folio {{ $folio }}.</div>

  </div></div>
  <div class="page-foot"><strong>Home del Valle</strong><span>Pocos inmuebles. Más control. Mejores resultados.</span><span>Acta de Entrega · {{ $folio }}</span></div>
</div>

</body>
</html>
