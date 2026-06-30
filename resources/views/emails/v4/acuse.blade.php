@php
    $primerNombre = $data->nombre ? explode(' ', trim($data->nombre))[0] : '';
    $saludo       = $primerNombre ? ', ' . $primerNombre : '';
    $formType     = $data->form_type;
    $folio        = 'HDV-' . strtoupper(substr(md5($data->folio), 0, 4)) . '-' . $data->folio;

    $config = match($formType) {
        'vendedor' => [
            'preheader'  => 'Recibimos tu solicitud de valuación — te contactamos en menos de 24 horas',
            'badge'      => 'Solicitud recibida',
            'titulo'     => '¡Recibimos tu solicitud' . $saludo . '!',
            'bajada'     => 'Un asesor especializado revisará tu propiedad y te contactará por WhatsApp en <strong style="color:#0E304B;">menos de 24 horas hábiles</strong> con tu valuación gratuita.',
            'pasos'      => [
                ['icon' => 'icon-home.png',  'titulo' => 'Analizamos tu propiedad',   'desc' => 'Revisamos ubicación, metraje y comparables del mercado actual en Benito Juárez.'],
                ['icon' => 'icon-chart.png', 'titulo' => 'Te enviamos la valuación',   'desc' => 'Precio competitivo basado en datos reales, no estimados genéricos.'],
                ['icon' => 'icon-shield.png','titulo' => 'Diseñamos tu estrategia',    'desc' => 'Fotografía profesional, marketing digital y red de compradores calificados.'],
            ],
            'cta_primary'   => ['url' => 'https://homedelvalle.mx/precios',     'label' => 'Ver precios por m²'],
            'cta_secondary' => ['url' => 'https://homedelvalle.mx/propiedades', 'label' => 'Ver propiedades'],
            'nota'          => 'Sin compromiso y sin costos. Solo cobramos comisión al cerrar exitosamente.',
        ],
        'comprador' => [
            'preheader'  => 'Recibimos tu búsqueda — curación en menos de 72 horas',
            'badge'      => 'Búsqueda recibida',
            'titulo'     => '¡Recibimos tu búsqueda' . $saludo . '!',
            'bajada'     => 'Vamos a curar las mejores opciones que coincidan con tu brief y te las enviamos en <strong style="color:#0E304B;">menos de 72 horas hábiles</strong>. Sin spam, sin catálogos masivos.',
            'pasos'      => [
                ['icon' => 'icon-pin.png',   'titulo' => 'Filtramos el mercado',    'desc' => 'Revisamos inventario propio, red de contactos privada y mercado abierto.'],
                ['icon' => 'icon-check.png', 'titulo' => 'Selección curada',        'desc' => 'Solo te enviamos 3–5 opciones que realmente coinciden con tu brief.'],
                ['icon' => 'icon-shield.png','titulo' => 'Acompañamiento al cierre','desc' => 'Negociación, due diligence legal y firma de escrituras incluidos.'],
            ],
            'cta_primary'   => ['url' => 'https://homedelvalle.mx/propiedades', 'label' => 'Ver propiedades disponibles'],
            'cta_secondary' => ['url' => 'https://homedelvalle.mx/precios',     'label' => 'Ver precios por m²'],
            'nota'          => 'El servicio es gratuito para el comprador. La comisión la cubre el vendedor al cierre.',
        ],
        'arrendatario' => [
            'preheader'  => 'Tu búsqueda de renta llegó — selección curada en 72 horas',
            'badge'      => 'Búsqueda de renta recibida',
            'titulo'     => '¡Recibimos tu búsqueda' . $saludo . '!',
            'bajada'     => 'Vamos a curar las mejores opciones que coincidan con tu brief y te las enviamos en <strong style="color:#0E304B;">menos de 72 horas hábiles</strong>. Sin agentes que insisten, sin portales masivos.',
            'pasos'      => [
                ['icon' => 'icon-pin.png',       'titulo' => 'Filtramos el inventario', 'desc' => 'Revisamos inmuebles propios y red privada que coincidan con tu zona, presupuesto y preferencias.'],
                ['icon' => 'icon-check.png',     'titulo' => 'Selección curada',        'desc' => 'Solo te enviamos 3–5 opciones que realmente cumplen tu brief. Sin catálogos masivos.'],
                ['icon' => 'icon-homestep.png',  'titulo' => 'Te acompañamos a firmar', 'desc' => 'Revisamos contrato y póliza jurídica contigo antes de que firmes. Sin cláusulas escondidas.'],
            ],
            'cta_primary'   => ['url' => 'https://homedelvalle.mx/propiedades', 'label' => 'Ver inmuebles disponibles'],
            'cta_secondary' => ['url' => 'https://homedelvalle.mx/precios',     'label' => 'Ver precios por m²'],
            'nota'          => 'El servicio es gratuito para ti como inquilino. Nuestra comisión la cubre el propietario al cierre.',
        ],
        'propietario_renta' => [
            'preheader'  => 'Tu solicitud para rentar tu inmueble llegó — te contactamos en 24 horas',
            'badge'      => 'Solicitud recibida',
            'titulo'     => '¡Recibimos tu solicitud' . $saludo . '!',
            'bajada'     => 'Un asesor especializado en rentas te contactará en <strong style="color:#0E304B;">menos de 24 horas hábiles</strong> con un rango de renta orientativo y un plan personalizado.',
            'pasos'      => [
                ['icon' => 'icon-pin.png',   'titulo' => 'Analizamos tu inmueble',      'desc' => 'Revisamos ubicación, características y comparables de renta en tu colonia.'],
                ['icon' => 'icon-chart.png', 'titulo' => 'Te enviamos el rango de renta','desc' => 'Precio competitivo basado en datos reales del mercado. Sin inflar para captar la firma.'],
                ['icon' => 'icon-shield.png','titulo' => 'Diseñamos tu plan',           'desc' => 'Calificación de inquilinos, póliza jurídica y administración integral. Tú eliges.'],
            ],
            'cta_primary'   => ['url' => 'https://homedelvalle.mx/precios', 'label' => 'Ver precios por m²'],
            'cta_secondary' => null,
            'nota'          => 'Sin costo por adelantado. Comisión solo al firmar contrato. Sin exclusividad obligatoria.',
        ],
        'b2b' => [
            'preheader'  => 'Recibimos tu brief calificador — llamada en menos de 48 horas',
            'badge'      => 'Brief recibido',
            'titulo'     => 'Recibimos tu brief' . $saludo . '.',
            'bajada'     => 'Un miembro de nuestra dirección general te contactará en <strong style="color:#0E304B;">menos de 48 horas hábiles</strong> para agendar la llamada de calificación.',
            'pasos'      => [
                ['icon' => 'icon-check.png', 'titulo' => 'Calificamos tu brief',    'desc' => 'Revisamos objetivos, presupuesto y horizonte de inversión.'],
                ['icon' => 'icon-chat.png',  'titulo' => 'Llamada de alineación',   'desc' => 'Definimos criterios técnicos y financieros con dirección general.'],
                ['icon' => 'icon-pin.png',   'titulo' => 'Captación dirigida',      'desc' => 'Activamos la red para identificar activos que cumplan al 100%.'],
            ],
            'cta_primary'   => ['url' => 'mailto:leads@homedelvalle.mx', 'label' => 'Contactar dirección general'],
            'cta_secondary' => null,
            'nota'          => 'Información tratada bajo confidencialidad. Nunca compartimos tu brief sin autorización.',
        ],
        default => [
            'preheader'  => 'Recibimos tu mensaje — respondemos en menos de 24 horas',
            'badge'      => 'Mensaje recibido',
            'titulo'     => '¡Recibimos tu mensaje' . $saludo . '!',
            'bajada'     => 'Un asesor de <strong style="color:#0E304B;">Home del Valle</strong> te responderá en <strong style="color:#0E304B;">menos de 24 horas hábiles</strong>. Sin compromiso y sin spam.',
            'pasos'      => [
                ['icon' => 'icon-mail.png',    'titulo' => 'Revisamos tu mensaje',  'desc' => 'Un asesor lee tu solicitud y prepara la mejor respuesta.'],
                ['icon' => 'icon-chat.png',    'titulo' => 'Te contactamos',        'desc' => 'Por teléfono, email o WhatsApp según tu preferencia.'],
                ['icon' => 'icon-homestep.png','titulo' => 'Asesoría personalizada','desc' => 'Sin compromiso. Solo soluciones reales para tu caso.'],
            ],
            'cta_primary'   => ['url' => 'https://homedelvalle.mx/propiedades', 'label' => 'Ver propiedades'],
            'cta_secondary' => ['url' => 'https://homedelvalle.mx/precios',     'label' => 'Ver precios por m²'],
            'nota'          => 'Pocos inmuebles. Más control. Mejores resultados.',
        ],
    };

    $iconBase = rtrim(asset('img/email'), '/') . '/';
@endphp
<!DOCTYPE html>
<html lang="es" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="x-apple-disable-message-reformatting">
<title>{{ $config['titulo'] }}</title>
<!--[if mso]><noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript><![endif]-->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
body,table,td,a{-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%}
table,td{mso-table-lspace:0;mso-table-rspace:0}
img{-ms-interpolation-mode:bicubic;border:0;outline:none;text-decoration:none;display:block}
body{margin:0;padding:0;width:100%!important;background:#F1F4F8}
a{text-decoration:none}
@media screen and (max-width:620px){
    .container{width:100%!important}
    .px{padding-left:22px!important;padding-right:22px!important}
    .stack{display:block!important;width:100%!important;margin-bottom:10px!important}
    .btnh{height:12px!important;width:auto!important}
}
</style>
</head>
<body style="margin:0;padding:0;background:#F1F4F8;">

{{-- Preheader --}}
<div style="display:none;max-height:0;overflow:hidden;mso-hide:all;font-size:1px;line-height:1px;color:#F1F4F8;">{{ $config['preheader'] }}</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F1F4F8;">
<tr><td align="center" style="padding:40px 16px;">

<table role="presentation" class="container" width="600" cellpadding="0" cellspacing="0" border="0"
       style="width:600px;max-width:600px;background:#FFFFFF;border:1px solid #E6EAF1;border-radius:20px;overflow:hidden;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">

    {{-- Header: Logo --}}
    <tr>
        <td class="px" align="center" style="padding:24px 34px;border-bottom:1px solid #EEF1F6;">
            <img src="{{ $logoUrl ?? $iconBase . 'logo-azul.png' }}" width="116" height="40" alt="Home del Valle"
                 style="width:116px;height:40px;margin:0 auto;">
            <div style="font-size:11px;color:#7A8594;font-weight:700;letter-spacing:.4px;margin-top:9px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                Pocos inmuebles &middot; Más control &middot; Mejores resultados
            </div>
        </td>
    </tr>

    {{-- Hero --}}
    <tr>
        <td class="px" align="center" style="padding:36px 34px 0;">
            <div style="font-size:12px;font-weight:800;letter-spacing:2px;color:#2E80C6;text-transform:uppercase;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                {{ $config['badge'] }}
            </div>
            <h1 style="font-size:27px;font-weight:800;color:#0E304B;margin:11px 0 0;letter-spacing:-.5px;line-height:1.15;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                {{ $config['titulo'] }}
            </h1>
            <p style="font-size:15.5px;line-height:1.6;color:#5A6573;margin:13px auto 0;max-width:44ch;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                {!! $config['bajada'] !!}
            </p>
        </td>
    </tr>

    {{-- Folio --}}
    <tr>
        <td class="px" style="padding:28px 34px 0;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                   style="background:#F6F8FB;border:1px solid #EEF1F6;border-radius:16px;">
                <tr>
                    <td style="padding:18px 20px;">
                        <table cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td width="44" valign="middle">
                                    <table width="44" cellpadding="0" cellspacing="0" border="0"
                                           style="background:#DFF3E6;border-radius:22px;">
                                        <tr>
                                            <td align="center" valign="middle" height="44" style="height:44px;">
                                                <img src="{{ $iconBase }}icon-check.png" width="22" height="22" alt="✓">
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td width="16">&nbsp;</td>
                                <td valign="middle">
                                    <div style="font-size:11px;font-weight:800;letter-spacing:1px;color:#9AA6B5;text-transform:uppercase;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">Folio de seguimiento</div>
                                    <div style="font-family:'Courier New',monospace;font-size:16px;font-weight:700;color:#0E304B;margin-top:4px;letter-spacing:.5px;">{{ $folio }}</div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    {{-- Qué sigue --}}
    <tr>
        <td class="px" style="padding:28px 34px 0;">
            <div style="font-size:13px;font-weight:800;color:#0E304B;text-transform:uppercase;letter-spacing:1px;margin-bottom:14px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                Qué sigue
            </div>

            @foreach($config['pasos'] as $paso)
            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                   style="background:#F6F8FB;border:1px solid #EEF1F6;border-radius:14px;margin-bottom:12px;">
                <tr>
                    <td style="padding:16px 18px;">
                        <table width="100%">
                            <tr>
                                <td width="40" valign="top" style="width:40px;">
                                    <table role="presentation" width="40" cellpadding="0" cellspacing="0" border="0"
                                           style="background:#E7F0FE;border-radius:11px;margin:0 auto;">
                                        <tr>
                                            <td align="center" valign="middle" height="40" style="height:40px;">
                                                <img src="{{ $iconBase }}{{ $paso['icon'] }}" width="20" height="20"
                                                     alt="" style="width:20px;height:20px;">
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td width="14">&nbsp;</td>
                                <td valign="top">
                                    <div style="font-size:15px;font-weight:700;color:#0E304B;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">{{ $paso['titulo'] }}</div>
                                    <div style="font-size:13.5px;color:#7A8594;margin-top:2px;line-height:1.5;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">{{ $paso['desc'] }}</div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            @endforeach

            {{-- Nota --}}
            <p style="font-size:12px;color:#9AA6B5;margin:6px 0 0;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                {{ $config['nota'] }}
            </p>
        </td>
    </tr>

    {{-- Botones --}}
    <tr>
        <td class="px" style="padding:28px 34px 34px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td class="stack" valign="top">
                        <!--[if mso]><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $config['cta_primary']['url'] }}" style="height:52px;v-text-anchor:middle;width:240px;" arcsize="24%" stroke="f" fillcolor="#0E304B"><w:anchorlock/><center style="color:#FFFFFF;font-family:Arial,sans-serif;font-size:15px;font-weight:bold;">{{ $config['cta_primary']['label'] }}</center></v:roundrect><![endif]-->
                        <!--[if !mso]><!-->
                        <a href="{{ $config['cta_primary']['url'] }}"
                           style="display:block;background:#0E304B;border-radius:12px;color:#FFFFFF;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;font-size:15px;font-weight:700;line-height:50px;text-align:center;text-decoration:none;">
                            {{ $config['cta_primary']['label'] }}
                        </a>
                        <!--<![endif]-->
                    </td>

                    @if($config['cta_secondary'])
                    <td class="btnh" width="12" style="width:12px;">&nbsp;</td>
                    <td class="stack" valign="top">
                        <!--[if mso]><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $config['cta_secondary']['url'] }}" style="height:52px;v-text-anchor:middle;width:240px;" arcsize="24%" strokecolor="#D5DCE7" strokeweight="1.5px" fillcolor="#FFFFFF"><w:anchorlock/><center style="color:#0E304B;font-family:Arial,sans-serif;font-size:15px;font-weight:bold;">{{ $config['cta_secondary']['label'] }}</center></v:roundrect><![endif]-->
                        <!--[if !mso]><!-->
                        <a href="{{ $config['cta_secondary']['url'] }}"
                           style="display:block;background:#FFFFFF;border:1.5px solid #D5DCE7;border-radius:12px;color:#0E304B;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;font-size:15px;font-weight:700;line-height:50px;text-align:center;text-decoration:none;">
                            {{ $config['cta_secondary']['label'] }}
                        </a>
                        <!--<![endif]-->
                    </td>
                    @endif
                </tr>
            </table>
        </td>
    </tr>

    {{-- Footer --}}
    <tr>
        <td align="center" style="background:#0E304B;padding:28px 34px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 auto;">
                <tr>
                    <td><a href="https://homedelvalle.mx" style="display:inline-block;width:34px;height:34px;line-height:34px;border-radius:10px;background:rgba(255,255,255,.07);color:#9FB0C6;font-size:12px;font-weight:700;text-align:center;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">f</a></td>
                    <td style="padding-left:8px;"><a href="https://instagram.com/homedelvalle" style="display:inline-block;width:34px;height:34px;line-height:34px;border-radius:10px;background:rgba(255,255,255,.07);color:#9FB0C6;font-size:12px;font-weight:700;text-align:center;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">ig</a></td>
                </tr>
            </table>
            <div style="font-size:13px;margin-top:18px;">
                <a href="https://homedelvalle.mx" style="color:#7FB0DF;font-weight:700;">homedelvalle.mx</a>
                <span style="color:#5A6A85;">&middot;</span>
                <a href="mailto:contacto@homedelvalle.mx" style="color:#7FB0DF;font-weight:700;">contacto@homedelvalle.mx</a>
            </div>
            <div style="font-size:11.5px;color:#7E8DA6;margin-top:9px;">
                Notificación automática &middot; Home del Valle CRM
            </div>
        </td>
    </tr>

</table>
</td></tr>
</table>
</body>
</html>
