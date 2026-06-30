<!DOCTYPE html>
<html lang="es" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="x-apple-disable-message-reformatting">
<title>¿Qué te pareció el inmueble?</title>
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
@endphp

<div style="display:none;max-height:0;overflow:hidden;mso-hide:all;font-size:1px;line-height:1px;color:#F1F4F8;">{{ $firstName }}, tu opinión es muy importante para nosotros. ¿Qué te pareció?</div>

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
            <div style="font-size:40px;margin-bottom:12px;">🏠</div>
            <h1 style="font-size:26px;font-weight:800;color:#0E304B;margin:0 0 8px;letter-spacing:-.5px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                {{ $firstName }}, ¿qué te pareció?
            </h1>
            <p style="font-size:14.5px;color:#7A8594;margin:0;font-weight:500;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                {{ $addr }}
                @if($scheduled)
                · {{ $scheduled->locale('es')->isoFormat('D [de] MMMM') }}
                @endif
            </p>
        </td>
    </tr>

    {{-- Reaction buttons --}}
    <tr>
        <td class="px" style="padding:28px 34px 0;">
            <p style="font-size:14px;color:#5A6573;text-align:center;margin:0 0 20px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                Selecciona la opción que mejor describe tu experiencia:
            </p>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    {{-- Liked --}}
                    <td class="reaction-btn" valign="top" style="width:32%;">
                        <!--[if mso]><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $likedUrl }}" style="height:80px;v-text-anchor:middle;width:180px;" arcsize="15%" stroke="f" fillcolor="#D1FAE5"><w:anchorlock/><center style="color:#065F46;font-family:Arial,sans-serif;font-size:14px;font-weight:bold;">👍 Me gustó</center></v:roundrect><![endif]-->
                        <!--[if !mso]><!-->
                        <a href="{{ $likedUrl }}"
                           style="display:block;background:#D1FAE5;border-radius:12px;color:#065F46;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;font-size:13px;font-weight:700;text-align:center;text-decoration:none;padding:16px 8px;">
                            <div style="font-size:28px;margin-bottom:6px;">👍</div>
                            Me gustó
                        </a>
                        <!--<![endif]-->
                    </td>
                    <td width="8" style="width:8px;">&nbsp;</td>
                    {{-- Neutral --}}
                    <td class="reaction-btn" valign="top" style="width:32%;">
                        <!--[if mso]><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $neutralUrl }}" style="height:80px;v-text-anchor:middle;width:180px;" arcsize="15%" stroke="f" fillcolor="#FEF3C7"><w:anchorlock/><center style="color:#92400E;font-family:Arial,sans-serif;font-size:14px;font-weight:bold;">🤔 Tengo dudas</center></v:roundrect><![endif]-->
                        <!--[if !mso]><!-->
                        <a href="{{ $neutralUrl }}"
                           style="display:block;background:#FEF3C7;border-radius:12px;color:#92400E;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;font-size:13px;font-weight:700;text-align:center;text-decoration:none;padding:16px 8px;">
                            <div style="font-size:28px;margin-bottom:6px;">🤔</div>
                            Tengo dudas
                        </a>
                        <!--<![endif]-->
                    </td>
                    <td width="8" style="width:8px;">&nbsp;</td>
                    {{-- Disliked --}}
                    <td class="reaction-btn" valign="top" style="width:32%;">
                        <!--[if mso]><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $dislikedUrl }}" style="height:80px;v-text-anchor:middle;width:180px;" arcsize="15%" stroke="f" fillcolor="#FEE2E2"><w:anchorlock/><center style="color:#991B1B;font-family:Arial,sans-serif;font-size:14px;font-weight:bold;">❌ No era lo que buscaba</center></v:roundrect><![endif]-->
                        <!--[if !mso]><!-->
                        <a href="{{ $dislikedUrl }}"
                           style="display:block;background:#FEE2E2;border-radius:12px;color:#991B1B;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;font-size:13px;font-weight:700;text-align:center;text-decoration:none;padding:16px 8px;">
                            <div style="font-size:28px;margin-bottom:6px;">❌</div>
                            No era lo que buscaba
                        </a>
                        <!--<![endif]-->
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    {{-- Or leave comment CTA --}}
    <tr>
        <td class="px" style="padding:20px 34px 34px;" align="center">
            <p style="font-size:12.5px;color:#9AA6B5;margin:0 0 12px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                ¿Tienes algún comentario adicional?
            </p>
            <a href="{{ $feedbackUrl }}"
               style="font-size:13px;color:#0E304B;font-weight:600;text-decoration:underline;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                Escribe tu opinión completa aquí →
            </a>
        </td>
    </tr>

    {{-- Footer --}}
    <tr>
        <td align="center" style="background:#0E304B;padding:22px 34px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
            <div style="font-size:12px;color:#9FB0C6;">
                &copy; Home del Valle {{ date('Y') }} &middot; <span style="color:#fff;font-weight:700;">Portal del Cliente</span>
            </div>
        </td>
    </tr>

</table>
</td></tr>
</table>
</body>
</html>
