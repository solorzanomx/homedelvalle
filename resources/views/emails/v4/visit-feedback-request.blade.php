<!DOCTYPE html>
<html lang="es" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="x-apple-disable-message-reformatting">
<title>Tu opinión cuenta — Home del Valle</title>
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
    .reaction-btn{width:100%!important;display:block!important;margin-bottom:10px!important}
    .cover-img{height:180px!important}
}
</style>
</head>
<body style="margin:0;padding:0;background:#F1F4F8;">

@php
    $nombre      = $client->name ?? 'Hola';
    $firstName   = explode(' ', trim($nombre))[0];
    $addr        = $propertyAddress ?: 'el inmueble que visitaste';
    $scheduled   = $interaction->scheduled_at;
    $token       = $interaction->visit_token;
    $feedbackUrl = url("/visit/{$token}/feedback-form");
    $likedUrl    = $feedbackUrl . '?r=liked';
    $neutralUrl  = $feedbackUrl . '?r=neutral';
    $dislikedUrl = $feedbackUrl . '?r=disliked';
    $asesor      = $interaction->user;
    $asesorName  = $asesor?->name ?? 'Tu asesor';
    $asesorFirstName = explode(' ', trim($asesorName))[0];
    $property    = $interaction->property;
    $coverPhoto  = null;
    if ($property) {
        $property->loadMissing('photos');
        $coverPhoto = $property->cover_photo_url;
    }
@endphp

<div style="display:none;max-height:0;overflow:hidden;mso-hide:all;font-size:1px;line-height:1px;color:#F1F4F8;">{{ $firstName }}, fue un gusto acompañarte. ¿Qué te pareció el inmueble?</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F1F4F8;">
<tr><td align="center" style="padding:40px 16px;">

<table role="presentation" class="container" width="600" cellpadding="0" cellspacing="0" border="0"
       style="width:600px;max-width:600px;background:#FFFFFF;border:1px solid #E6EAF1;border-radius:20px;overflow:hidden;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">

    {{-- Header con logo --}}
    <tr>
        <td class="px" align="center" style="padding:22px 34px;border-bottom:1px solid #EEF1F6;">
            <img src="{{ asset('img/email/logo-azul.png') }}" width="116" height="40" alt="Home del Valle"
                 style="width:116px;height:40px;margin:0 auto;">
        </td>
    </tr>

    {{-- Foto de portada del inmueble --}}
    @if($coverPhoto)
    <tr>
        <td style="padding:0;line-height:0;">
            <img class="cover-img" src="{{ $coverPhoto }}" alt="{{ $addr }}" width="600"
                 style="width:100%;max-width:600px;height:240px;object-fit:cover;display:block;">
        </td>
    </tr>
    @endif

    {{-- Saludo personal del asesor --}}
    <tr>
        <td class="px" style="padding:32px 34px 0;">
            <p style="font-size:15px;color:#5A6573;line-height:1.7;margin:0;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                Hola <strong style="color:#0E304B;">{{ $firstName }}</strong>,
            </p>
            <p style="font-size:15px;color:#5A6573;line-height:1.7;margin:12px 0 0;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                Fue un gusto acompañarte en la visita a <strong style="color:#0E304B;">{{ $addr }}</strong>
                @if($scheduled) el <strong style="color:#0E304B;">{{ $scheduled->locale('es')->isoFormat('D [de] MMMM') }}</strong>@endif.
                Me gustaría saber qué te pareció, para poder ayudarte mejor en tu búsqueda.
            </p>
            <p style="font-size:15px;color:#5A6573;line-height:1.7;margin:12px 0 0;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                Son solo <strong style="color:#0E304B;">3 preguntas rápidas</strong>, y tu opinión también le ayuda al dueño del inmueble a tomar mejores decisiones.
            </p>
        </td>
    </tr>

    {{-- Divider --}}
    <tr>
        <td class="px" style="padding:24px 34px 0;">
            <div style="height:1px;background:#EEF1F6;"></div>
        </td>
    </tr>

    {{-- Pregunta 1: Reacción --}}
    <tr>
        <td class="px" style="padding:24px 34px 0;">
            <p style="font-size:12px;font-weight:700;color:#9AA6B5;text-transform:uppercase;letter-spacing:.5px;margin:0 0 14px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                1 de 3 &nbsp;·&nbsp; El inmueble
            </p>
            <p style="font-size:16px;font-weight:700;color:#0E304B;margin:0 0 16px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                ¿Qué te pareció el inmueble?
            </p>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td class="reaction-btn" valign="top" style="width:32%;">
                        <!--[if mso]><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $likedUrl }}" style="height:80px;v-text-anchor:middle;width:176px;" arcsize="15%" stroke="f" fillcolor="#D1FAE5"><w:anchorlock/><center style="color:#065F46;font-family:Arial,sans-serif;font-size:13px;font-weight:bold;">👍 Me gustó</center></v:roundrect><![endif]-->
                        <!--[if !mso]><!-->
                        <a href="{{ $likedUrl }}"
                           style="display:block;background:#D1FAE5;border-radius:12px;color:#065F46;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;font-size:13px;font-weight:700;text-align:center;text-decoration:none;padding:15px 6px;">
                            <div style="font-size:26px;margin-bottom:5px;">👍</div>
                            Me gustó
                        </a>
                        <!--<![endif]-->
                    </td>
                    <td width="8">&nbsp;</td>
                    <td class="reaction-btn" valign="top" style="width:32%;">
                        <!--[if mso]><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $neutralUrl }}" style="height:80px;v-text-anchor:middle;width:176px;" arcsize="15%" stroke="f" fillcolor="#FEF3C7"><w:anchorlock/><center style="color:#92400E;font-family:Arial,sans-serif;font-size:13px;font-weight:bold;">🤔 Tengo dudas</center></v:roundrect><![endif]-->
                        <!--[if !mso]><!-->
                        <a href="{{ $neutralUrl }}"
                           style="display:block;background:#FEF3C7;border-radius:12px;color:#92400E;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;font-size:13px;font-weight:700;text-align:center;text-decoration:none;padding:15px 6px;">
                            <div style="font-size:26px;margin-bottom:5px;">🤔</div>
                            Tengo dudas
                        </a>
                        <!--<![endif]-->
                    </td>
                    <td width="8">&nbsp;</td>
                    <td class="reaction-btn" valign="top" style="width:32%;">
                        <!--[if mso]><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $dislikedUrl }}" style="height:80px;v-text-anchor:middle;width:176px;" arcsize="15%" stroke="f" fillcolor="#FEE2E2"><w:anchorlock/><center style="color:#991B1B;font-family:Arial,sans-serif;font-size:13px;font-weight:bold;">No era lo que buscaba</center></v:roundrect><![endif]-->
                        <!--[if !mso]><!-->
                        <a href="{{ $dislikedUrl }}"
                           style="display:block;background:#FEE2E2;border-radius:12px;color:#991B1B;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;font-size:13px;font-weight:700;text-align:center;text-decoration:none;padding:15px 6px;">
                            <div style="font-size:26px;margin-bottom:5px;">❌</div>
                            No era lo que buscaba
                        </a>
                        <!--<![endif]-->
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    {{-- Pregunta 2 + 3: CTA al formulario completo --}}
    <tr>
        <td class="px" style="padding:24px 34px 0;">
            <div style="background:#F6F8FB;border:1px solid #E6EAF1;border-radius:14px;padding:20px 22px;">
                <p style="font-size:13px;color:#5A6573;margin:0 0 4px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                    <strong style="color:#0E304B;">2 preguntas más esperan tu respuesta:</strong>
                </p>
                <p style="font-size:12.5px;color:#9AA6B5;margin:0 0 16px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                    ¿Cómo viste el precio? &nbsp;·&nbsp; Califica la atención recibida
                </p>
                <a href="{{ $feedbackUrl }}"
                   style="display:block;background:#0E304B;color:#fff;border-radius:10px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;font-size:14px;font-weight:700;text-align:center;text-decoration:none;padding:13px 20px;">
                    Completar cuestionario →
                </a>
            </div>
        </td>
    </tr>

    {{-- Firma del asesor --}}
    <tr>
        <td class="px" style="padding:28px 34px 32px;">
            <div style="height:1px;background:#EEF1F6;margin-bottom:24px;"></div>
            <p style="font-size:14px;color:#5A6573;margin:0 0 6px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                Con gusto te sigo asesorando,
            </p>
            <p style="font-size:15px;font-weight:700;color:#0E304B;margin:0 0 2px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                {{ $asesorName }}
            </p>
            <p style="font-size:12.5px;color:#9AA6B5;margin:0;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                Asesor inmobiliario &middot; Home del Valle
            </p>
        </td>
    </tr>

    {{-- Footer --}}
    <tr>
        <td align="center" style="background:#0E304B;padding:20px 34px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
            <img src="{{ asset('img/email/logo-blanco.png') }}" width="90" height="31" alt="Home del Valle"
                 style="width:90px;height:31px;margin:0 auto 10px;display:block;">
            <div style="font-size:11px;color:#9FB0C6;">
                Pocos inmuebles &middot; Más control &middot; Mejores resultados
            </div>
            <div style="font-size:11px;color:#5A6E82;margin-top:8px;">
                &copy; Home del Valle {{ date('Y') }}
            </div>
        </td>
    </tr>

</table>
</td></tr>
</table>
</body>
</html>
