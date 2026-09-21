<!DOCTYPE html>
<html lang="es" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="x-apple-disable-message-reformatting">
<title>Requisitos para tu renta — Home del Valle</title>
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

@php $firstName = explode(' ', trim($nombre))[0] ?: 'Hola'; @endphp
<div style="display:none;max-height:0;overflow:hidden;mso-hide:all;font-size:1px;line-height:1px;color:#F1F4F8;">Estos son los documentos que necesitamos para avanzar tu renta.</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F1F4F8;">
<tr><td align="center" style="padding:40px 16px;">

<table role="presentation" class="container" width="600" cellpadding="0" cellspacing="0" border="0"
       style="width:600px;max-width:600px;background:#FFFFFF;border:1px solid #E6EAF1;border-radius:20px;overflow:hidden;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">

    <tr>
        <td class="px" align="center" style="padding:24px 34px;border-bottom:1px solid #EEF1F6;">
            <img src="{{ asset('img/email/logo-azul.png') }}" width="116" height="40" alt="Home del Valle" style="width:116px;height:40px;margin:0 auto;">
        </td>
    </tr>

    <tr>
        <td class="px" style="padding:34px 34px 0;">
            <h1 style="font-size:24px;font-weight:800;color:#0E304B;margin:0;letter-spacing:-.5px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">Hola {{ $firstName }}</h1>
            <p style="font-size:14.5px;color:#5a6573;margin:12px 0 0;line-height:1.6;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                Para avanzar tu proceso de renta necesitamos que nos compartas los siguientes documentos. Puedes subirlos directamente en tu portal, donde también verás tu avance.
            </p>
        </td>
    </tr>

    <tr>
        <td class="px" style="padding:22px 34px 0;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #E6EAF1;border-radius:16px;overflow:hidden;">
                @foreach($checklist as $label)
                <tr>
                    <td style="padding:13px 20px;{{ !$loop->last ? 'border-bottom:1px solid #F1F4F8;' : '' }}font-size:14px;color:#0E304B;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                        <span style="color:#94a3b8;margin-right:8px;">○</span>{{ $label }}
                    </td>
                </tr>
                @endforeach
            </table>
            <p style="font-size:12.5px;color:#94a3b8;margin:12px 0 0;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                Además, tu asesor te indicará si tu garantía será con aval o con pagarés — esa sección se activa en tu portal en cuanto se defina.
            </p>
        </td>
    </tr>

    <tr>
        <td class="px" style="padding:28px 34px 34px;">
            <a href="{{ $portalUrl }}"
               style="display:block;background:#0E304B;border-radius:12px;color:#FFFFFF;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;font-size:15px;font-weight:700;line-height:50px;text-align:center;text-decoration:none;">
                {{ $isNewAccount ? 'Activar mi portal y subir documentos' : 'Entrar a mi portal' }}
            </a>
            @if($isNewAccount)
            <p style="font-size:12.5px;color:#94a3b8;margin:10px 0 0;text-align:center;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                Al entrar por primera vez te pediremos aceptar el Aviso de Privacidad y el Acuerdo de Confidencialidad.
            </p>
            @endif
        </td>
    </tr>

    <tr>
        <td align="center" style="background:#0E304B;padding:22px 34px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
            <div style="font-size:12px;color:#9FB0C6;">
                <span style="color:#fff;font-weight:700;">Home del Valle</span> · Bienes Raíces
            </div>
        </td>
    </tr>

</table>
</td></tr>
</table>
</body>
</html>
