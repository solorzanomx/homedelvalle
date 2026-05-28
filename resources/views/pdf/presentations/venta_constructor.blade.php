@php
$propertyArea = $captacion->property->area ?? 0;
$cusEstimado  = 3.5; // CUS típico H5 en Benito Juárez
$m2Construibles = $propertyArea > 0 ? number_format(round($propertyArea * $cusEstimado), 0) : null;

$proposicion = '
<p>Tu inmueble tiene un <strong>potencial de desarrollo</strong> que el mercado de usuarios finales no puede pagar. Necesitas llegar a los compradores que lo entienden: desarrolladoras y constructoras que calculan por <em>metro cuadrado construible</em>, no por metro cuadrado de terreno.</p>

<p>Tenemos una <strong>red activa de más de 30 desarrolladores</strong> buscando predios en Benito Juárez hoy. Conocemos su lenguaje, sus criterios técnicos y sabemos cómo estructurar la presentación para generar <strong>múltiples ofertas competitivas</strong> que eleven el precio final.</p>

<ul class="blist">
    <li><strong>Análisis de potencial de desarrollo</strong> — zonificación, CUS, COS, densidades permitidas y restricciones de la zona.</li>
    <li><strong>Brief técnico para desarrolladores</strong> — documento con toda la información que necesitan para decidir rápido: área, FOS, NIS, nivel de aguas, restricciones.</li>
    <li><strong>Presentación directa a 30+ desarrolladores activos en BJ</strong> — contacto confidencial, sin portales públicos que alertan a vecinos o deprimen el precio.</li>
    <li><strong>Negociación de precio por m² construible</strong> — maximizamos el valor real del predio, no el precio por m² de terreno.</li>
    <li><strong>Múltiples ofertas simultáneas</strong> — la competencia entre desarrolladores es el mejor mecanismo de precio que existe.</li>
    <li><strong>Due diligence técnico y legal acompañado</strong> — cobertura completa desde la primera oferta hasta escrituración.</li>
</ul>
' . ($m2Construibles ? '
<div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:13px 18px;margin:12px 0;">
    <div style="font-size:9px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#92400e;margin-bottom:10px;">Análisis preliminar de potencial constructivo</div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;">
        <div style="text-align:center;padding:8px;background:#fff;border-radius:7px;border:1px solid #fde68a;">
            <div style="font-size:9px;color:#92400e;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px;">Área del predio</div>
            <div style="font-size:18px;font-weight:800;color:#78350f;line-height:1;">' . number_format($propertyArea, 0) . ' m²</div>
        </div>
        <div style="text-align:center;padding:8px;background:#fff;border-radius:7px;border:1px solid #fde68a;">
            <div style="font-size:9px;color:#92400e;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px;">CUS estimado H5</div>
            <div style="font-size:18px;font-weight:800;color:#78350f;line-height:1;">' . $cusEstimado . '×</div>
        </div>
        <div style="text-align:center;padding:8px;background:#fff;border-radius:7px;border:1px solid #fde68a;">
            <div style="font-size:9px;color:#92400e;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px;">M² construibles est.</div>
            <div style="font-size:18px;font-weight:800;color:#78350f;line-height:1;">' . $m2Construibles . ' m²</div>
        </div>
    </div>
    <p style="font-size:9.5px;color:#92400e;margin:8px 0 0;font-style:italic;">* Estimación preliminar sujeta a verificación de uso de suelo oficial. La valuación técnica determinará el potencial exacto.</p>
</div>
' : '
<div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:13px 18px;margin:12px 0;">
    <div style="font-size:9px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#92400e;margin-bottom:6px;">Potencial constructivo</div>
    <p style="font-size:11.5px;color:#78350f;margin:0;line-height:1.6;">Los predios con zonificación H5/H6 en Benito Juárez permiten construir 3.5–5.5× el área del terreno. La valuación técnica determinará el potencial exacto de tu inmueble y el precio óptimo por m² construible.</p>
</div>
');
@endphp
@include('pdf.presentations._layout')
