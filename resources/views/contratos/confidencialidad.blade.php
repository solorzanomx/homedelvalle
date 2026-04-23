<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Contrato de Confidencialidad</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; line-height: 1.7; color: #222; margin: 50px 60px; }
    h1 { font-size: 15px; text-align: center; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; }
    h2 { font-size: 12px; text-transform: uppercase; margin-top: 20px; margin-bottom: 4px; border-bottom: 1px solid #ccc; padding-bottom: 2px; }
    .subtitle { text-align: center; font-size: 10px; color: #666; margin-bottom: 28px; }
    p { margin: 6px 0; text-align: justify; }
    .firma-bloque { margin-top: 50px; display: table; width: 100%; }
    .firma-col { display: table-cell; width: 50%; text-align: center; padding-top: 10px; }
    .firma-linea { border-top: 1px solid #555; width: 70%; margin: 0 auto 6px; }
    .footer { margin-top: 40px; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #eee; padding-top: 8px; }
    strong { font-weight: bold; }
</style>
</head>
<body>

<h1>Contrato de Confidencialidad</h1>
<p class="subtitle">Proceso de Valuación de Propiedad</p>

<p>En la Ciudad de México, a <strong>{{ $fecha }}</strong>, se celebra el presente Contrato de Confidencialidad
(en adelante "el Contrato") entre:</p>

<h2>Las Partes</h2>

<p><strong>{{ $empresa }}</strong> (en adelante "la Empresa"), con domicilio en la Ciudad de México, dedicada a
servicios inmobiliarios y de valuación de propiedades; y</p>

<p><strong>{{ $client->name }}</strong> (en adelante "el Cliente"), con correo electrónico
<strong>{{ $client->email }}</strong>@if($client->phone), teléfono <strong>{{ $client->phone }}</strong>@endif,
quien ha solicitado los servicios de valuación de su propiedad.</p>

<h2>Antecedentes</h2>

<p>El Cliente ha solicitado a la Empresa la realización de un proceso de valuación de su propiedad.
Con motivo de dicho proceso, ambas partes intercambiarán información de carácter confidencial,
incluyendo datos personales, información financiera, documentación del inmueble y demás datos
relacionados con la propiedad objeto de valuación.</p>

<h2>Cláusula Primera — Definición de Información Confidencial</h2>

<p>Para efectos del presente Contrato, se considera "Información Confidencial" toda aquella
información que cualquiera de las partes revele a la otra en el contexto del proceso de valuación,
incluyendo de manera enunciativa mas no limitativa: datos personales e identificación del Cliente,
información sobre la propiedad (dimensiones, estado, documentación legal, adeudos), información
financiera y patrimonial, así como cualquier documento, análisis o reporte generado durante el
proceso.</p>

<h2>Cláusula Segunda — Obligaciones de Confidencialidad</h2>

<p>Ambas partes se obligan a:</p>
<p>(a) Mantener la Información Confidencial en estricta reserva y no divulgarla a terceros sin
consentimiento previo y por escrito de la parte que la proporcionó.</p>
<p>(b) Utilizar la Información Confidencial únicamente para los fines del proceso de valuación
descrito en este Contrato.</p>
<p>(c) Implementar las medidas de seguridad razonables para proteger la Información Confidencial
contra acceso, uso o divulgación no autorizados.</p>
<p>(d) Notificar de inmediato a la otra parte en caso de cualquier divulgación no autorizada
de la Información Confidencial.</p>

<h2>Cláusula Tercera — Excepciones</h2>

<p>Las obligaciones de confidencialidad no aplicarán a información que: (i) sea o llegue a ser
de dominio público sin que ello sea consecuencia del incumplimiento de este Contrato; (ii) sea
conocida por la parte receptora con anterioridad a su divulgación; (iii) sea requerida por
disposición legal o mandato judicial, en cuyo caso la parte requerida notificará a la otra
en la mayor brevedad posible.</p>

<h2>Cláusula Cuarta — Protección de Datos Personales</h2>

<p>La Empresa se compromete a tratar los datos personales del Cliente conforme a la Ley Federal
de Protección de Datos Personales en Posesión de los Particulares (LFPDPPP) y su Reglamento,
así como a las políticas de privacidad vigentes de la Empresa. Los datos serán utilizados
exclusivamente para el proceso de valuación y actividades relacionadas.</p>

<h2>Cláusula Quinta — Vigencia</h2>

<p>El presente Contrato tendrá una vigencia de dos (2) años a partir de su firma, o hasta que
concluya el proceso de valuación y la relación comercial derivada del mismo, lo que ocurra
en último término.</p>

<h2>Cláusula Sexta — Consecuencias del Incumplimiento</h2>

<p>El incumplimiento de las obligaciones de confidencialidad establecidas en este Contrato dará
lugar a las acciones legales correspondientes conforme a la legislación mexicana aplicable,
incluyendo la reparación de daños y perjuicios causados.</p>

<h2>Cláusula Séptima — Disposiciones Generales</h2>

<p>Para la interpretación y cumplimiento del presente Contrato, las partes se someten a las
leyes aplicables de los Estados Unidos Mexicanos y a la jurisdicción de los tribunales
competentes de la Ciudad de México, renunciando a cualquier otro fuero que pudiera
corresponderles.</p>

<p>Leído el presente instrumento y enteradas las partes de su contenido y alcance legal,
lo firman de conformidad.</p>

<div class="firma-bloque">
    <div class="firma-col">
        <br><br>
        <div class="firma-linea"></div>
        <strong>{{ $empresa }}</strong><br>
        Representante Autorizado
    </div>
    <div class="firma-col">
        <br><br>
        <div class="firma-linea"></div>
        <strong>{{ $client->name }}</strong><br>
        El Cliente
    </div>
</div>

<div class="footer">
    {{ $empresa }} &mdash; Documento generado el {{ $fecha }} &mdash; Confidencial
</div>

</body>
</html>
