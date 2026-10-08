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
    font-size: 9.8px;
    line-height: 1.48;
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
    padding: 10px 52px; display: flex; align-items: center; justify-content: space-between;
}
.page-header-inner img { height: 18px; max-width: 140px; object-fit: contain; display: block; }
.page-header-inner span.phi-text { font-size: 12px; font-weight: 700; color: #fff; }
.page-header-inner .phi-tag { font-size: 8.5px; letter-spacing: 1px; text-transform: uppercase; color: rgba(199,210,254,.7); }
.page-body  { flex: 1; display: flex; flex-direction: column; }
.inner      { flex: 1; padding: 20px 50px 12px; display: flex; flex-direction: column; }
.page-foot  {
    flex-shrink: 0; border-top: 1px solid #e2e8f0; padding: 8px 52px;
    display: flex; justify-content: space-between; align-items: center;
    font-size: 8.5px; color: #94a3b8;
}
.page-foot strong { color: var(--hdv-navy); font-weight: 600; }

.doc-title { font-size: 15px; font-weight: 800; color: var(--hdv-navy); text-align: center; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 3px; }
.doc-folio { font-size: 8.5px; color: #94a3b8; text-align: center; margin-bottom: 9px; letter-spacing: .5px; }

p { color: #334155; font-size: 9.8px; line-height: 1.48; margin-bottom: 5px; text-align: justify; }
strong { color: #0f172a; }

.parties-row { display: flex; gap: 10px; margin: 6px 0 9px; }
.party-box { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 7px 12px; text-align: center; }
.party-box .role { font-size: 7.2px; font-weight: 800; text-transform: uppercase; letter-spacing: .5px; color: var(--hdv-navy); margin-bottom: 2px; }
.party-box .name { color: #0f172a; font-weight: 700; font-size: 10px; }
.party-box .sub { color: #64748b; font-size: 8px; margin-top: 2px; }

.clauses { counter-reset: clause; margin: 4px 0 9px; }
.clause { padding: 5px 0; border-bottom: 1px solid #f8fafc; font-size: 9.4px; line-height: 1.48; color: #334155; text-align: justify; }
.clause:last-child { border-bottom: none; }
.clause strong { color: #0f172a; }

.sign-row { display: flex; justify-content: center; gap: 26px; margin-top: 12px; flex-wrap: wrap; }
.sign-col { width: 190px; text-align: center; }
.sign-role { font-size: 7.2px; font-weight: 800; text-transform: uppercase; letter-spacing: .5px; color: var(--hdv-navy); margin-bottom: 3px; }
.sign-line { border-top: 1px solid #0f172a; padding-top: 5px; margin-top: 12px; font-size: 8.5px; color: #475569; }
.sign-name { font-size: 10px; font-weight: 700; color: #0f172a; }

.privacy-note { font-size: 8px; color: #94a3b8; line-height: 1.5; margin-top: 10px; border-top: 1px solid #f1f5f9; padding-top: 6px; }
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

    <p>En la Ciudad de México, a {{ $fecha }}, se reúnen en el inmueble ubicado en <strong>{{ $propertyFull }}</strong>, {{ $representanteNombre }}, en representación de Home del Valle Bienes Raíces, quien interviene para efectuar la entrega material del inmueble por cuenta del vendedor, el/la señor(a) <strong>{{ $sellerName }}</strong>, y el/la señor(a) <strong>{{ $buyerName }}</strong>, en su carácter de {{ $compradoraRolUpper }} del inmueble, conforme a la escritura pública de compraventa correspondiente. Los comparecientes hacen constar lo siguiente:</p>

    <div class="parties-row">
      <div class="party-box">
        <div class="role">Entrega por cuenta del vendedor</div>
        <div class="name">{{ $representanteNombre }}</div>
        <div class="sub">Home del Valle · en representación de {{ $sellerName }}</div>
      </div>
      <div class="party-box">
        <div class="role">{{ $compradoraRolUpper }}</div>
        <div class="name">{{ $buyerName }}</div>
      </div>
    </div>

    <div class="clauses">
      @foreach($clauses as $c)
        <div class="clause" data-clause="{{ $c['key'] }}">{!! $c['body'] !!}</div>
      @endforeach
    </div>

    <p><strong>Sexto. Aceptación y firma.</strong> Los comparecientes manifiestan su conformidad con los términos de la presente acta, reconociendo que la entrega material del inmueble se efectúa en la fecha señalada, quedando {{ $compradoraRolUpper }} en posesión física del inmueble y con el control de sus accesos. Leída la presente acta y enterados los comparecientes de su contenido, valor y alcance legal, la firman de conformidad, quedando un ejemplar en poder de cada uno de los comparecientes.</p>

    <div class="sign-row">
      <div class="sign-col">
        <div class="sign-role">Entrega por cuenta del vendedor</div>
        <div class="sign-line">
          <div class="sign-name">{{ $representanteNombre }}</div>
          Home del Valle · en representación de {{ $sellerName }}
        </div>
      </div>
      <div class="sign-col">
        <div class="sign-role">{{ $compradoraRolUpper }}</div>
        <div class="sign-line">
          <div class="sign-name">{{ \Illuminate\Support\Str::of($buyerName)->before(' y ') }}</div>
          Recibe el inmueble
        </div>
      </div>
      @if($coCompradorNombre)
      <div class="sign-col">
        <div class="sign-role">Co-comprador(a)</div>
        <div class="sign-line">
          <div class="sign-name">{{ $coCompradorNombre }}</div>
          Recibe el inmueble
        </div>
      </div>
      @endif
    </div>

    <div class="privacy-note">Documento confidencial. Generado por el sistema de Home del Valle el {{ $fecha }}. Folio {{ $folio }}.</div>

  </div></div>
  <div class="page-foot"><strong>Home del Valle</strong><span>Pocos inmuebles. Más control. Mejores resultados.</span><span>Acta de Entrega · {{ $folio }}</span></div>
</div>

</body>
</html>
