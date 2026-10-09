@php include(resource_path('views/pdf/_brand_data.php')); @endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Recibo de Comisión — {{ $folio }}</title>
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
.inner      { flex: 1; padding: 30px 52px 14px; display: flex; flex-direction: column; }
.page-foot  {
    flex-shrink: 0; border-top: 1px solid #e2e8f0; padding: 8px 52px;
    display: flex; justify-content: space-between; align-items: center;
    font-size: 8.5px; color: #94a3b8;
}
.page-foot strong { color: var(--hdv-navy); font-weight: 600; }

.doc-title { font-size: 17px; font-weight: 800; color: var(--hdv-navy); text-align: center; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 4px; }
.doc-folio { font-size: 9px; color: #94a3b8; text-align: center; margin-bottom: 16px; letter-spacing: .5px; }

.bueno-por {
    background: #f8fafc; border: 1.5px solid var(--hdv-navy); border-radius: 10px;
    padding: 14px 20px; text-align: center; margin-bottom: 18px;
}
.bueno-por .label { font-size: 9px; font-weight: 800; letter-spacing: 1px; color: var(--hdv-navy); text-transform: uppercase; margin-bottom: 4px; }
.bueno-por .monto { font-size: 22px; font-weight: 800; color: var(--hdv-navy); }

p { color: #334155; font-size: 11px; line-height: 1.65; margin-bottom: 12px; text-align: justify; }
strong { color: #0f172a; }
.monto-letras { display: inline-block; font-size: 11.5px; }

.clauses { margin: 4px 0 18px; }
.clause { padding: 0 0 12px; font-size: 11px; line-height: 1.65; color: #334155; text-align: justify; }
.clause:last-child { padding-bottom: 0; }

.cierre { margin-top: 10px; margin-bottom: 26px; font-size: 11px; color: #334155; }

.sign-row { display: flex; justify-content: center; margin-top: 10px; }
.sign-col { width: 280px; text-align: center; }
.sign-role { font-size: 8px; font-weight: 800; text-transform: uppercase; letter-spacing: .5px; color: var(--hdv-navy); margin-bottom: 4px; }
.sign-line { border-top: 1px solid #0f172a; padding-top: 6px; margin-top: 30px; font-size: 9.5px; color: #475569; }
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

    <div class="doc-title">Recibo de Comisión</div>
    <div class="doc-folio">Folio {{ $folio }} · Ciudad de México, a {{ $fecha }}</div>

    <div class="bueno-por">
      <div class="label">Bueno por</div>
      <div class="monto">{{ $montoNumero }} M.N.</div>
    </div>

    <div class="clauses">
      @foreach($clauses as $c)
        <div class="clause" data-clause="{{ $c['key'] }}">{!! $c['body'] !!}</div>
      @endforeach
    </div>

    <p class="cierre">Ciudad de México, a {{ $fecha }}.</p>

    <div class="sign-row">
      <div class="sign-col">
        <div class="sign-role">Recibí a mi entera satisfacción</div>
        <div class="sign-line">
          <div class="sign-name">{{ $representanteNombre }}</div>
          Home del Valle Bienes Raíces · {{ $representanteCargo }}
        </div>
      </div>
    </div>

    <div class="privacy-note">Documento confidencial. Generado por el sistema de Home del Valle el {{ $fecha }}. Folio {{ $folio }}.</div>

  </div></div>
  <div class="page-foot"><strong>Home del Valle</strong><span>Pocos inmuebles. Más control. Mejores resultados.</span><span>Recibo de Comisión · {{ $folio }}</span></div>
</div>

</body>
</html>
