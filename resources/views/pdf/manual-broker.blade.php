@php include(resource_path('views/pdf/_brand_data.php')); @endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Manual del Broker — Proceso de Captación</title>
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
    font-size: 13px;
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
.inner      { flex: 1; padding: 40px 52px 16px; display: flex; flex-direction: column; overflow: hidden; }
.page-foot  {
    flex-shrink: 0; border-top: 1px solid #e2e8f0; padding: 8px 52px;
    display: flex; justify-content: space-between; align-items: center;
    font-size: 9.5px; color: #94a3b8;
}
.page-foot strong { color: var(--hdv-navy); font-weight: 600; }

.cover-body { flex: 1; background: var(--hdv-navy); padding: 60px 52px; display: flex; flex-direction: column; }
.cover-foot { flex-shrink: 0; background: #17154a; padding: 12px 52px; display: flex; justify-content: space-between; align-items: center; font-size: 10px; color: rgba(199,210,254,.55); border-top: 1px solid rgba(255,255,255,.08); }
.cover-foot strong { color: rgba(199,210,254,.85); }
.cover-logo { height: 44px; max-width: 200px; object-fit: contain; object-position: left; margin-bottom: 40px; }
.cover-logo-text { font-size: 12px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: rgba(199,210,254,.5); margin-bottom: 40px; }
.cover-tag { display: inline-block; background: rgba(37,99,235,.18); border: 1px solid rgba(37,99,235,.4); color: #93c5fd; font-size: 9px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; padding: 3px 11px; border-radius: 20px; margin-bottom: 16px; }
.cover-title { font-size: 34px; font-weight: 800; color: #fff; line-height: 1.15; letter-spacing: -.4px; margin-bottom: 10px; }
.cover-sub   { font-size: 15px; font-weight: 300; color: rgba(199,210,254,.8); max-width: 420px; }
.cover-spacer { flex: 1; min-height: 10px; }
.cover-index { border-top: 1px solid rgba(255,255,255,.1); padding-top: 20px; }
.cover-index .label { font-size: 8px; letter-spacing: 2px; text-transform: uppercase; color: rgba(199,210,254,.35); margin-bottom: 10px; }
.cover-index ol { list-style: none; counter-reset: idx; }
.cover-index li { counter-increment: idx; font-size: 12.5px; color: rgba(199,210,254,.85); padding: 5px 0; }
.cover-index li::before { content: counter(idx) "."; color: #93c5fd; font-weight: 700; margin-right: 8px; }

.section-tag { font-size: 8.5px; font-weight: 700; letter-spacing: 2.5px; text-transform: uppercase; color: #94a3b8; margin-bottom: 5px; }
.section-h2  { font-size: 22px; font-weight: 800; color: var(--hdv-navy); line-height: 1.2; letter-spacing: -.3px; margin-bottom: 7px; }
.accent-bar  { width: 28px; height: 3px; background: var(--hdv-accent); border-radius: 2px; margin-bottom: 20px; }
p { color: #475569; font-size: 12.5px; line-height: 1.75; margin-bottom: 11px; }
strong { color: #1e293b; }
h3.sub { font-size: 13px; font-weight: 700; color: var(--hdv-navy); margin: 14px 0 6px; }

.steps { list-style: none; margin: 12px 0; }
.steps li { padding: 9px 0 9px 38px; position: relative; border-bottom: 1px solid #f1f5f9; color: #475569; font-size: 12.5px; }
.steps li:last-child { border-bottom: none; }
.step-n { position: absolute; left: 0; top: 7px; width: 24px; height: 24px; border-radius: 50%; background: var(--hdv-navy); color: #fff; font-size: 10.5px; font-weight: 700; display: flex; align-items: center; justify-content: center; }

.blist { list-style: none; margin: 10px 0 16px; }
.blist li { padding: 8px 0 8px 22px; position: relative; border-bottom: 1px solid #f1f5f9; color: #475569; font-size: 12.5px; line-height: 1.5; }
.blist li:last-child { border-bottom: none; }
.blist li::before { content: ''; position: absolute; left: 0; top: 15px; width: 6px; height: 6px; border-radius: 50%; background: var(--hdv-accent); }

.mkt-box { background: #f8fafc; border-left: 3px solid var(--hdv-accent); border-radius: 0 8px 8px 0; padding: 14px 18px; margin: 12px 0; }
.mkt-box h4 { font-size: 11.5px; font-weight: 700; color: var(--hdv-navy); margin-bottom: 4px; }
.mkt-box p { font-size: 12px; color: #334155; line-height: 1.7; margin: 0; }

.insight-box { background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 10px 14px; margin-top: 12px; }
.insight-box p { font-size: 11.5px; color: #78350f; margin: 0; line-height: 1.5; }
.insight-box strong { color: #92400e; }

.warn-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 12px 16px; margin: 12px 0; }
.warn-box p { font-size: 11.5px; color: #991b1b; margin: 0 0 6px; line-height: 1.5; }
.warn-box p:last-child { margin-bottom: 0; }

.cmp-table { width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 0; }
.cmp-table th { padding: 7px 10px; text-align: left; font-size: 8.5px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
.cmp-table th:first-child { color: #94a3b8; background: #f8fafc; border-radius: 6px 0 0 0; width: 38%; }
.cmp-table th:last-child  { color: #fff; background: var(--hdv-navy); border-radius: 0 6px 0 0; }
.cmp-table td { padding: 9px 10px; border-bottom: 1px solid #f1f5f9; color: #475569; vertical-align: top; line-height: 1.5; }
.cmp-table tr:last-child td { border-bottom: none; }

.err-list { list-style: none; margin: 10px 0; counter-reset: err; }
.err-list li { counter-increment: err; padding: 8px 0 8px 30px; position: relative; border-bottom: 1px solid #f1f5f9; color: #475569; font-size: 12px; line-height: 1.5; }
.err-list li:last-child { border-bottom: none; }
.err-list li::before { content: counter(err); position: absolute; left: 0; top: 7px; width: 20px; height: 20px; border-radius: 50%; background: #fef2f2; color: #dc2626; font-size: 10px; font-weight: 700; display: flex; align-items: center; justify-content: center; }
</style>
</head>
<body>

{{-- ═══════════ PORTADA ═══════════ --}}
<div class="page">
  <div class="cover-body">
    @if(!empty($brandLogoSrcLight))
      <img src="{{ $brandLogoSrcLight }}" class="cover-logo" alt="Home del Valle">
    @elseif(!empty($brandLogoSrc))
      <img src="{{ $brandLogoSrc }}" class="cover-logo" alt="Home del Valle">
    @else
      <div class="cover-logo-text">Home del Valle · Bienes Raíces</div>
    @endif

    <div class="cover-tag">Uso Interno · Confidencial</div>
    <h1 class="cover-title">Manual del Broker<br>Proceso de Captación</h1>
    <p class="cover-sub">Qué hacer, en qué momento y con qué herramienta del sistema — desde que llega un lead de propietario hasta que firma el Acuerdo de Representación.</p>

    <div class="cover-spacer"></div>

    <div class="cover-index">
      <div class="label">Contenido</div>
      <ol>
        <li>Principios generales</li>
        <li>Etapa: Lead</li>
        <li>Etapa: Contacto</li>
        <li>Etapa: Visita</li>
        <li>Revisión de documentos / Avalúo</li>
        <li>Etapa: Acuerdo de Representación</li>
        <li>Manejo de objeciones comunes</li>
        <li>Qué NO prometer y errores comunes</li>
      </ol>
    </div>
  </div>
  <div class="cover-foot">
    <strong>Home del Valle</strong>
    <span>Pocos inmuebles. Más control. Mejores resultados.</span>
  </div>
</div>

{{-- ═══════════ PRINCIPIOS GENERALES ═══════════ --}}
<div class="page">
  <div class="page-header-inner">
    @if(!empty($brandLogoSrcLight))<img src="{{ $brandLogoSrcLight }}" alt="Home del Valle">
    @elseif(!empty($brandLogoSrc))<img src="{{ $brandLogoSrc }}" alt="Home del Valle">
    @else<span class="phi-text">Home del Valle</span>@endif
    <span class="phi-tag">Manual del Broker · Uso Interno</span>
  </div>
  <div class="page-body"><div class="inner">
    <div class="section-tag">Antes de cualquier otra cosa</div>
    <h2 class="section-h2">Principios generales</h2>
    <div class="accent-bar"></div>
    <p>Tu meta en cada caso es una sola: <strong>llegar al Acuerdo de Representación firmado</strong>, lo más rápido y con la mejor experiencia posible para el propietario.</p>
    <ul class="steps">
      <li><span class="step-n">1</span><strong>La velocidad de respuesta es tu ventaja #1.</strong> Un propietario que recibe una llamada en los primeros 30-60 minutos percibe profesionalismo automáticamente. Uno que espera un día ya empezó a llamar a otras inmobiliarias.</li>
      <li><span class="step-n">2</span><strong>Nunca llegues a una llamada o visita sin prepararte.</strong> El sistema te da los datos (precio de referencia de la zona, historial del cliente) — úsalos antes de marcar.</li>
      <li><span class="step-n">3</span><strong>Nunca termines una interacción sin dejar la siguiente acción agendada.</strong> Un lead sin próximo paso definido es un lead que se enfría.</li>
      <li><span class="step-n">4</span><strong>Cada etapa que avanzas en el kanban debe reflejar la realidad</strong>, no lo que esperas que pase. Si no ha pasado, no lo muevas.</li>
    </ul>
    <div class="mkt-box">
      <h4>La Cabina de Etapa</h4>
      <p>Cuando entras a la ficha de una captación no vas a encontrar solo una lista de checkboxes sueltos — cada etapa tiene su propia cabina con los botones de acción directa (llamar, WhatsApp, agendar), el objetivo de esa etapa, y lo que ya lograste a un lado de lo que sigue. Resuelve todo desde ahí.</p>
    </div>
  </div></div>
  <div class="page-foot"><strong>Home del Valle</strong><span>Pocos inmuebles. Más control. Mejores resultados.</span><span>Manual del Broker</span></div>
</div>

{{-- ═══════════ LEAD ═══════════ --}}
<div class="page">
  <div class="page-header-inner">
    @if(!empty($brandLogoSrcLight))<img src="{{ $brandLogoSrcLight }}" alt="Home del Valle">
    @elseif(!empty($brandLogoSrc))<img src="{{ $brandLogoSrc }}" alt="Home del Valle">
    @else<span class="phi-text">Home del Valle</span>@endif
    <span class="phi-tag">Manual del Broker · Uso Interno</span>
  </div>
  <div class="page-body"><div class="inner">
    <div class="section-tag">Etapa 1 de 6</div>
    <h2 class="section-h2">Lead</h2>
    <div class="accent-bar"></div>
    <p><strong>Qué hacer:</strong> entra a <code>/admin/captaciones/pipeline</code>. Los leads nuevos están en la columna LEAD. Antes de marcar, revisa la tarjeta: tiene la dirección, tipo de inmueble y (si ya está disponible) una referencia de precio de la zona.</p>
    <h3 class="sub">Acción</h3>
    <p>Botón "Llamar" en la tarjeta. Objetivo de la llamada: confirmar interés real, entender la urgencia del propietario (¿por qué vende/renta?, ¿tiene prisa?), y agendar la visita.</p>
    <div class="insight-box"><p><strong>Tiempo objetivo:</strong> contactar en menos de 1 hora desde que el lead entra.</p></div>
    <h3 class="sub">Al terminar la llamada</h3>
    <p>Botón "Crear captación" — esto abre el wizard de 3 pasos (Cliente → Inmueble → Intención). Complétalo ahí mismo, con el cliente todavía en la línea si es posible — los datos son más precisos. <strong>La dirección exacta del inmueble se necesita desde este primer momento</strong>: es la que se usa para generar la portada de la Presentación.</p>
  </div></div>
  <div class="page-foot"><strong>Home del Valle</strong><span>Pocos inmuebles. Más control. Mejores resultados.</span><span>Manual del Broker</span></div>
</div>

{{-- ═══════════ CONTACTO ═══════════ --}}
<div class="page">
  <div class="page-header-inner">
    @if(!empty($brandLogoSrcLight))<img src="{{ $brandLogoSrcLight }}" alt="Home del Valle">
    @elseif(!empty($brandLogoSrc))<img src="{{ $brandLogoSrc }}" alt="Home del Valle">
    @else<span class="phi-text">Home del Valle</span>@endif
    <span class="phi-tag">Manual del Broker · Uso Interno</span>
  </div>
  <div class="page-body"><div class="inner">
    <div class="section-tag">Etapa 2 de 6</div>
    <h2 class="section-h2">Contacto</h2>
    <div class="accent-bar"></div>
    <p><strong>Qué hacer:</strong> envía la Presentación de Home del Valle (botón "Presentación" en la tarjeta, o desde la ficha de la captación) por WhatsApp o email — el sistema genera un link que puedes rastrear.</p>
    <h3 class="sub">Acción</h3>
    <p>Agenda la visita ahí mismo, mientras tienes el interés fresco. No dejes "te aviso" sin fecha concreta.</p>
    <div class="mkt-box">
      <h4>🔑 Portal del Cliente — se activa aquí</h4>
      <p>Junto con la Presentación, invita al propietario a su Portal del Cliente — no esperes al Acuerdo de Representación firmado. Ver que ya existe un espacio propio con su proceso reduce su duda más común ("¿esto va en serio?") antes de que lleguemos a la visita — es la ventana de mayor ansiedad de todo el proceso.</p>
    </div>
    <div class="warn-box"><p><strong>Qué NO hacer:</strong> no mandes la presentación sin haber hablado primero por teléfono — pierde el toque personal y baja la tasa de respuesta.</p></div>
  </div></div>
  <div class="page-foot"><strong>Home del Valle</strong><span>Pocos inmuebles. Más control. Mejores resultados.</span><span>Manual del Broker</span></div>
</div>

{{-- ═══════════ VISITA ═══════════ --}}
<div class="page">
  <div class="page-header-inner">
    @if(!empty($brandLogoSrcLight))<img src="{{ $brandLogoSrcLight }}" alt="Home del Valle">
    @elseif(!empty($brandLogoSrc))<img src="{{ $brandLogoSrc }}" alt="Home del Valle">
    @else<span class="phi-text">Home del Valle</span>@endif
    <span class="phi-tag">Manual del Broker · Uso Interno</span>
  </div>
  <div class="page-body"><div class="inner">
    <div class="section-tag">Etapa 3 de 6 · La más importante de todo el proceso</div>
    <h2 class="section-h2">Visita</h2>
    <div class="accent-bar"></div>
    <p>Esta es la etapa que decide si firma el Acuerdo de Representación o no. Prepárate en serio.</p>
    <h3 class="sub">Antes de ir</h3>
    <ul class="blist">
      <li>Revisa el precio de referencia de la colonia en el Observatorio (<code>/precios</code>).</li>
      <li>Revisa el historial de interacciones del cliente (llamadas, notas previas).</li>
      <li>Confirma la cita el día anterior.</li>
      <li>Lleva el checklist de documentos impreso — deja algo tangible en la mesa, y evita el "se me olvidó qué pedían" de la próxima llamada.</li>
    </ul>
    <h3 class="sub">Qué llevar</h3>
    <p>Laptop o tablet con acceso al CRM, cámara para fotos preliminares, tu criterio para dar una primera impresión de precio.</p>
    <h3 class="sub">Durante la visita</h3>
    <ul class="steps">
      <li><span class="step-n">1</span>Recorre el inmueble y documenta su estado (fotos preliminares, notas).</li>
      <li><span class="step-n">2</span><strong>Da una Opinión de Valor en el momento</strong>, usando Valor Rápido o Valuación Constructor desde tu dispositivo — no te vayas prometiendo "te aviso en unos días".</li>
      <li><span class="step-n">3</span>Presenta la <strong>Propuesta de Servicios</strong> ahí mismo: explica el plan de comercialización, apóyate en comparables reales de la zona.</li>
      <li><span class="step-n">4</span><strong>Si el propietario está listo, ofrece firmar el Acuerdo de Representación en el momento.</strong> No hay mejor momento que cuando el interés está al máximo.</li>
    </ul>
    <div class="warn-box"><p><strong>Qué NO hacer:</strong> no salgas de la visita sin haber intentado cerrar o, como mínimo, sin una fecha concreta de decisión.</p></div>
  </div></div>
  <div class="page-foot"><strong>Home del Valle</strong><span>Pocos inmuebles. Más control. Mejores resultados.</span><span>Manual del Broker</span></div>
</div>

{{-- ═══════════ REVISIÓN DE DOCUMENTOS / AVALÚO ═══════════ --}}
<div class="page">
  <div class="page-header-inner">
    @if(!empty($brandLogoSrcLight))<img src="{{ $brandLogoSrcLight }}" alt="Home del Valle">
    @elseif(!empty($brandLogoSrc))<img src="{{ $brandLogoSrc }}" alt="Home del Valle">
    @else<span class="phi-text">Home del Valle</span>@endif
    <span class="phi-tag">Manual del Broker · Uso Interno</span>
  </div>
  <div class="page-body"><div class="inner">
    <div class="section-tag">Etapa 4 de 6</div>
    <h2 class="section-h2">Revisión de documentos / Avalúo</h2>
    <div class="accent-bar"></div>
    <p><strong>Qué hacer:</strong> el sistema organiza el expediente del propietario en 4 grupos — pídelos a su ritmo, no todos de golpe. No dejes que esto frene la negociación: puede correr en paralelo mientras cierras el precio con el propietario.</p>
    <ul class="blist">
      <li><strong>Personales</strong> — identificación oficial, CURP, comprobante de domicilio (no mayor a 3 meses), constancia de situación fiscal, y estado de cuenta o carátula bancaria con CLABE.</li>
      <li><strong>Según estado civil</strong> — el sistema pide el documento correcto automáticamente: Acta de Matrimonio (casado/unión libre), Acta de Divorcio + Convenio (divorciado), Testamento o Declaratoria de Herederos si heredó el inmueble.</li>
      <li><strong>Del inmueble</strong> — escritura, cancelación de hipoteca si aplica, carta finiquito si aplica, predial y agua (boleta + pago), no adeudo de mantenimiento, reglamento de condominio. El propietario puede subir todo desde su Portal.</li>
      <li><strong>Notariales</strong> — libertad de gravámenes, no adeudo de contribuciones, avalúo notarial/fiscal, cálculo de impuestos. Estos <strong>no se le piden al propietario</strong> — los tramita la notaría, tú solo das seguimiento.</li>
    </ul>
    <div class="insight-box"><p>Vincula la valuación formal a la captación desde la ficha en cuanto la tengas.</p></div>
  </div></div>
  <div class="page-foot"><strong>Home del Valle</strong><span>Pocos inmuebles. Más control. Mejores resultados.</span><span>Manual del Broker</span></div>
</div>

{{-- ═══════════ ACUERDO DE REPRESENTACIÓN ═══════════ --}}
<div class="page">
  <div class="page-header-inner">
    @if(!empty($brandLogoSrcLight))<img src="{{ $brandLogoSrcLight }}" alt="Home del Valle">
    @elseif(!empty($brandLogoSrc))<img src="{{ $brandLogoSrc }}" alt="Home del Valle">
    @else<span class="phi-text">Home del Valle</span>@endif
    <span class="phi-tag">Manual del Broker · Uso Interno</span>
  </div>
  <div class="page-body"><div class="inner">
    <div class="section-tag">Etapa 5 de 6</div>
    <h2 class="section-h2">Acuerdo de Representación</h2>
    <div class="accent-bar"></div>
    <p><strong>Qué hacer:</strong> una vez acordado el precio, genera el Acuerdo de Representación desde el botón correspondiente en la ficha de la captación. Envíalo al propietario y da seguimiento activo hasta la firma — no lo dejes "esperando a que firme solo".</p>
    <div class="mkt-box">
      <h4>Al entregar el Acuerdo</h4>
      <p>Explica con claridad qué va a pasar después (fecha estimada de fotos, de publicación, primer reporte) — esto reduce la duda más común: "¿y si firmo y no hacen nada?"</p>
    </div>
  </div></div>
  <div class="page-foot"><strong>Home del Valle</strong><span>Pocos inmuebles. Más control. Mejores resultados.</span><span>Manual del Broker</span></div>
</div>

{{-- ═══════════ OBJECIONES ═══════════ --}}
<div class="page">
  <div class="page-header-inner">
    @if(!empty($brandLogoSrcLight))<img src="{{ $brandLogoSrcLight }}" alt="Home del Valle">
    @elseif(!empty($brandLogoSrc))<img src="{{ $brandLogoSrc }}" alt="Home del Valle">
    @else<span class="phi-text">Home del Valle</span>@endif
    <span class="phi-tag">Manual del Broker · Uso Interno</span>
  </div>
  <div class="page-body"><div class="inner">
    <div class="section-tag">Referencia rápida</div>
    <h2 class="section-h2">Manejo de objeciones comunes</h2>
    <div class="accent-bar"></div>
    <table class="cmp-table">
      <thead><tr><th>Objeción del propietario</th><th>Cómo responder</th></tr></thead>
      <tbody>
        <tr><td>"Su precio me parece bajo"</td><td>Muestra los comparables reales del Observatorio de Precios de su colonia — datos, no opinión.</td></tr>
        <tr><td>"Quiero pensarlo"</td><td>Pregunta qué le genera duda específicamente y resuélvelo ahí (precio, plazo, confianza). No presiones, pero tampoco te vayas sin agendar cuándo te va a dar respuesta.</td></tr>
        <tr><td>"Otra inmobiliaria me ofrece un precio más alto"</td><td>Explica que un precio inflado sin respaldo de mercado solo alarga el tiempo en venta — un inmueble sobrevaluado se queda sin visitas. Usa datos, no discurso genérico.</td></tr>
        <tr><td>"No quiero exclusividad, prefiero varias inmobiliarias"</td><td>Explica el modelo boutique: pocas propiedades = más atención, mejor comercialización, sin competir contigo mismo bajando precio. No es que "no pueda" trabajar con otros — es que representarlo de verdad requiere que trabajemos juntos, no en competencia con otras 5 inmobiliarias bajando el precio entre ellas.</td></tr>
      </tbody>
    </table>
  </div></div>
  <div class="page-foot"><strong>Home del Valle</strong><span>Pocos inmuebles. Más control. Mejores resultados.</span><span>Manual del Broker</span></div>
</div>

{{-- ═══════════ QUÉ NO PROMETER + ERRORES ═══════════ --}}
<div class="page">
  <div class="page-header-inner">
    @if(!empty($brandLogoSrcLight))<img src="{{ $brandLogoSrcLight }}" alt="Home del Valle">
    @elseif(!empty($brandLogoSrc))<img src="{{ $brandLogoSrc }}" alt="Home del Valle">
    @else<span class="phi-text">Home del Valle</span>@endif
    <span class="phi-tag">Manual del Broker · Uso Interno</span>
  </div>
  <div class="page-body"><div class="inner">
    <div class="section-tag">Cierre</div>
    <h2 class="section-h2">Qué NO prometer</h2>
    <div class="accent-bar"></div>
    <ul class="blist">
      <li>No prometas un plazo específico de venta o renta — da rangos basados en datos del Observatorio, nunca una fecha fija.</li>
      <li>No prometas un precio final antes de tener la Opinión de Valor completa.</li>
      <li>No prometas resultados de marketing específicos (número de interesados, ofertas) — promete el proceso y el reporte periódico, no el resultado.</li>
    </ul>

    <h3 class="sub" style="margin-top:22px;">Errores comunes a evitar</h3>
    <ol class="err-list">
      <li>Mandar la Presentación sin haber llamado antes.</li>
      <li>Dejar pasar más de un día sin dar seguimiento a un lead.</li>
      <li>Ir a la visita sin revisar el precio de referencia de la zona.</li>
      <li>Dar la Opinión de Valor "después" en vez de en el momento de la visita.</li>
      <li>Salir de la visita sin intentar cerrar o sin fecha concreta de decisión.</li>
      <li>Mover una tarjeta de etapa en el kanban antes de que la acción real haya sucedido.</li>
      <li>Prometer plazos o resultados que no dependen de ti.</li>
      <li>Pedirle al propietario documentación notarial (libertad de gravámenes, no adeudo de contribuciones, avalúo fiscal) — eso lo tramita la notaría, no él.</li>
    </ol>
  </div></div>
  <div class="page-foot"><strong>Home del Valle</strong><span>Pocos inmuebles. Más control. Mejores resultados.</span><span>Manual del Broker</span></div>
</div>

</body>
</html>
