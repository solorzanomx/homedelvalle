<!DOCTYPE html>
<html lang="es" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="x-apple-disable-message-reformatting">
<title>Candidato para tu inmueble</title>
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
}
</style>
</head>
<body style="margin:0;padding:0;background:#F1F4F8;">

@php
    $inv      = $rental->investigation;
    $property = $rental->property;
    $addr     = $property?->address ?? 'tu inmueble';
    $portalUrl = url('/mi-renta/' . $rental->id . '/candidato');
@endphp

<div style="display:none;max-height:0;overflow:hidden;mso-hide:all;font-size:1px;line-height:1px;color:#F1F4F8;">Hemos completado la investigación del candidato. Revisa su perfil y da tu respuesta.</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F1F4F8;">
<tr><td align="center" style="padding:40px 16px;">

<table role="presentation" class="container" width="600" cellpadding="0" cellspacing="0" border="0"
       style="width:600px;max-width:600px;background:#FFFFFF;border:1px solid #E6EAF1;border-radius:20px;overflow:hidden;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">

    {{-- Header --}}
    <tr>
        <td class="px" align="center" style="padding:24px 34px;border-bottom:1px solid #EEF1F6;">
            <img src="{{ asset('img/email/logo-azul.png') }}" width="116" height="40" alt="Home del Valle"
                 style="width:116px;height:40px;margin:0 auto;">
            <div style="font-size:11px;color:#7A8594;font-weight:700;letter-spacing:.4px;margin-top:9px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                Pocos inmuebles &middot; Más control &middot; Mejores resultados
            </div>
        </td>
    </tr>

    {{-- Hero --}}
    <tr>
        <td class="px" align="center" style="padding:34px 34px 0;">
            <table cellpadding="0" cellspacing="0" border="0" style="margin:0 auto;">
                <tr>
                    <td width="64" align="center">
                        <table width="64" cellpadding="0" cellspacing="0" border="0"
                               style="background:#EDE9FE;border-radius:32px;">
                            <tr>
                                <td align="center" valign="middle" height="64"
                                    style="height:64px;color:#5B21B6;font-size:28px;font-weight:800;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                                    &#128100;
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <table cellpadding="0" cellspacing="0" border="0" style="margin:18px auto 0;">
                <tr>
                    <td style="background:#EDE9FE;border-radius:999px;padding:6px 13px;font-size:11.5px;font-weight:800;letter-spacing:1.5px;color:#5B21B6;text-transform:uppercase;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                        Candidato listo para revisión
                    </td>
                </tr>
            </table>

            <h1 style="font-size:26px;font-weight:800;color:#0E304B;margin:13px 0 0;letter-spacing:-.5px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                Tenemos un candidato para tu inmueble
            </h1>
            <p style="font-size:14.5px;color:#7A8594;margin:7px 0 0;font-weight:500;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                {{ $addr }}
            </p>
        </td>
    </tr>

    {{-- Info box --}}
    <tr>
        <td class="px" style="padding:26px 34px 0;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                   style="background:#F8FAFF;border:1px solid #E6EAF1;border-radius:14px;padding:0;overflow:hidden;">
                <tr>
                    <td style="padding:18px 20px;">
                        <p style="margin:0;font-size:14px;line-height:1.7;color:#3D4F63;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                            Hemos realizado la investigación completa del candidato interesado en rentar tu inmueble en <strong>{{ $addr }}</strong>.
                            El perfil ya está disponible en tu portal con todos los detalles: perfil laboral, financiero, historial crediticio
                            @if($inv?->asesor_recommendation === 'approve') y una <strong style="color:#065F46;">recomendación de aprobación</strong> por parte de tu asesor.
                            @elseif($inv?->asesor_recommendation === 'conditional') y una <strong style="color:#92400E;">recomendación condicionada</strong> por parte de tu asesor.
                            @else . @endif
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    {{-- CTA --}}
    <tr>
        <td class="px" style="padding:28px 34px 34px;" align="center">
            <!--[if mso]><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $portalUrl }}" style="height:52px;v-text-anchor:middle;width:320px;" arcsize="24%" stroke="f" fillcolor="#0E304B"><w:anchorlock/><center style="color:#FFFFFF;font-family:Arial,sans-serif;font-size:15px;font-weight:bold;">Ver perfil del candidato</center></v:roundrect><![endif]-->
            <!--[if !mso]><!-->
            <a href="{{ $portalUrl }}"
               style="display:inline-block;background:#0E304B;border-radius:12px;color:#FFFFFF;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;font-size:15px;font-weight:700;line-height:50px;text-align:center;text-decoration:none;padding:0 32px;">
                Ver perfil del candidato &rarr;
            </a>
            <!--<![endif]-->
            <p style="margin:14px 0 0;font-size:12px;color:#9AA6B5;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                Puedes aprobar, declinar o pedir más información desde el portal.
            </p>
        </td>
    </tr>

    {{-- Footer --}}
    <tr>
        <td align="center" style="background:#0E304B;padding:22px 34px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
            <div style="font-size:12px;color:#9FB0C6;">
                Notificación automática &middot; <span style="color:#fff;font-weight:700;">Home del Valle CRM</span>
            </div>
        </td>
    </tr>

</table>
</td></tr>
</table>
</body>
</html>
