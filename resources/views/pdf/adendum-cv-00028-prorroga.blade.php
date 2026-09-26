@php include(resource_path('views/pdf/_brand_data.php')); @endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Adéndum — Folio CV-00028</title>
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
    line-height: 1.55;
    width: 215.9mm;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}

.page {
    width: 215.9mm;
    min-height: 279.4mm;
    display: flex;
    flex-direction: column;
}
.page-header-inner {
    flex-shrink: 0; background: var(--hdv-navy); border-bottom: 4px solid var(--hdv-accent);
    padding: 10px 52px; display: flex; align-items: center; justify-content: space-between;
}
.page-header-inner img { height: 18px; max-width: 140px; object-fit: contain; display: block; }
.page-header-inner span.phi-text { font-size: 12px; font-weight: 700; color: #fff; }
.page-header-inner .phi-tag { font-size: 8.5px; letter-spacing: 1px; text-transform: uppercase; color: rgba(199,210,254,.7); }
.page-body  { flex: 1; display: flex; flex-direction: column; }
.inner      { flex: 1; padding: 30px 52px 36px; display: flex; flex-direction: column; }
.page-foot  {
    position: fixed; bottom: 0; left: 0; right: 0; background: #fff;
    border-top: 1px solid #e2e8f0; padding: 8px 52px;
    display: flex; justify-content: space-between; align-items: center;
    font-size: 8.5px; color: #94a3b8;
}
.page-foot strong { color: var(--hdv-navy); font-weight: 600; }

.doc-title { font-size: 16px; font-weight: 800; color: var(--hdv-navy); text-align: center; text-transform: uppercase; letter-spacing: .5px; }
.doc-sub   { font-size: 9px; color: #94a3b8; text-align: center; margin-bottom: 14px; letter-spacing: .5px; }

p { color: #334155; font-size: 10.5px; line-height: 1.6; margin-bottom: 9px; text-align: justify; }
strong { color: #0f172a; }

.sec-title { font-size: 11px; font-weight: 800; color: var(--hdv-navy); text-align: center; letter-spacing: 1px; margin: 10px 0 8px; }

.sign-row { display: flex; justify-content: center; gap: 40px; margin-top: 18px; }
.sign-col { width: 260px; text-align: center; }
.sign-line { border-top: 1px solid #0f172a; padding-top: 6px; margin-top: 26px; font-size: 9.5px; color: #475569; }
.sign-name { font-size: 10.5px; font-weight: 700; color: #0f172a; }
.sign-caption { font-size: 9px; color: #64748b; margin-top: 2px; }

.privacy-note { font-size: 8.5px; color: #94a3b8; line-height: 1.6; margin-top: 14px; border-top: 1px solid #f1f5f9; padding-top: 8px; }
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

    <div class="doc-title">Adéndum al Contrato de Promesa de Compraventa</div>
    <div class="doc-sub">Folio CV-00028</div>

    <p>Adéndum al Contrato de Promesa de Compraventa celebrado el 7 de agosto de 2026, respecto del inmueble ubicado en Avenida Cuauhtémoc número 947, Departamento 703, Colonia Narvarte Poniente, Alcaldía Benito Juárez, Ciudad de México, C.P. 03020, celebrado entre Óscar Nogués González, por su propio derecho y en representación de María Encarnación Nieto Pliego, a quien en lo sucesivo se le denominará &ldquo;EL PROMITENTE VENDEDOR&rdquo;, y Roberto Ruiz Ramírez y Araceli Bautista Fabian, a quienes en lo sucesivo se les denominará &ldquo;EL PROMITENTE COMPRADOR&rdquo;; conjuntamente, &ldquo;LAS PARTES&rdquo;, quienes convienen en sujetarse a la siguiente:</p>

    <div class="sec-title">CLÁUSULA ÚNICA</div>

    <p><strong>ÚNICA. – PRÓRROGA DE LA FECHA LÍMITE PARA ESCRITURACIÓN:</strong> A solicitud de &ldquo;EL PROMITENTE VENDEDOR&rdquo; y con la conformidad expresa de &ldquo;EL PROMITENTE COMPRADOR&rdquo;, &ldquo;LAS PARTES&rdquo; acuerdan prorrogar la fecha límite para el otorgamiento y firma de la escritura pública definitiva de compraventa hasta el día 18 de septiembre de 2026, por lo que toda referencia contenida en el Contrato de Promesa de Compraventa a la fecha límite del 7 de septiembre de 2026 deberá entenderse sustituida por la del 18 de septiembre de 2026.</p>

    <p>Salvo por la modificación expresamente pactada en el presente Adéndum, todas y cada una de las declaraciones, cláusulas, obligaciones, términos y condiciones del Contrato de Promesa de Compraventa original permanecen vigentes, sin modificación alguna, conservando plenamente su fuerza legal y obligatoria.</p>

    <p>Leído que fue el presente Adéndum y enteradas &ldquo;LAS PARTES&rdquo; de su contenido y alcance legal, lo firman de conformidad en la Ciudad de México, el día 31 de agosto de 2026.</p>

    <div class="sign-row">
      <div class="sign-col">
        <div class="sign-line">
          <div class="sign-name">Óscar Nogués González</div>
          <div class="sign-caption">Por su propio derecho y en representación de<br>María Encarnación Nieto Pliego</div>
        </div>
        <div style="margin-top:6px;font-size:9.5px;color:#475569;">EL PROMITENTE VENDEDOR</div>
      </div>
      <div class="sign-col">
        <div class="sign-line">
          <div class="sign-name">Roberto Ruiz Ramírez</div>
        </div>
        <div class="sign-line">
          <div class="sign-name">Araceli Bautista Fabian</div>
        </div>
        <div style="margin-top:6px;font-size:9.5px;color:#475569;">EL PROMITENTE COMPRADOR</div>
      </div>
    </div>

    <div class="privacy-note">Documento confidencial. Generado por el sistema de Home del Valle. Folio CV-00028.</div>

  </div></div>
  <div class="page-foot"><strong>Home del Valle</strong><span>Pocos inmuebles. Más control. Mejores resultados.</span><span>Adéndum · Folio CV-00028</span></div>
</div>

</body>
</html>
