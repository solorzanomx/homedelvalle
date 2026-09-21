<!DOCTYPE html>
<html lang="es" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="x-apple-disable-message-reformatting">
<title>Calificación a revisar — Home del Valle</title>
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
    $client    = $interaction->client;
    $lead      = $interaction->formSubmission;
    $name      = $interaction->contactName() ?? 'Un visitante';
    $contactUrl = $client ? route('clients.show', $client) : ($lead ? route('admin.form-submissions.show', $lead) : url('/admin'));
    $reactionLabel = match($interaction->visitor_reaction) {
        'disliked' => '❌ No cumplió sus expectativas',
        'neutral'  => '🤔 Tiene dudas',
        'liked'    => '👍 Le gustó',
        default    => null,
    };
    $priceLabel = match($interaction->price_perception) {
        'high'       => '💸 Precio percibido como alto',
        'negotiable' => '💬 Precio negociable',
        'fair'       => '✅ Precio justo',
        default      => null,
    };
@endphp

<div style="display:none;max-height:0;overflow:hidden;mso-hide:all;font-size:1px;line-height:1px;color:#F1F4F8;">{{ $name }} calificó la visita — revisa el detalle para hablar con el dueño.</div>

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
        <td class="px" align="center" style="padding:34px 34px 0;">
            <table cellpadding="0" cellspacing="0" border="0" style="margin:0 auto;">
                <tr><td style="background:#FEF2F2;border-radius:999px;padding:6px 13px;font-size:11.5px;font-weight:800;letter-spacing:1.5px;color:#991B1B;text-transform:uppercase;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                    ⚠️ Calificación a revisar
                </td></tr>
            </table>
            <h1 style="font-size:25px;font-weight:800;color:#0E304B;margin:14px 0 0;letter-spacing:-.5px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">{{ $name }}</h1>
            <p style="font-size:14.5px;color:#7A8594;margin:7px 0 0;font-weight:500;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                Calificó su visita con una señal que vale la pena atender — sensibiliza al dueño cuanto antes.
            </p>
        </td>
    </tr>

    <tr>
        <td class="px" style="padding:26px 34px 0;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #E6EAF1;border-radius:16px;overflow:hidden;">

                @if($interaction->property)
                <tr>
                    <td style="padding:15px 20px;border-bottom:1px solid #F1F4F8;">
                        <table width="100%"><tr>
                            <td width="130" style="width:130px;font-size:11px;font-weight:800;letter-spacing:.8px;text-transform:uppercase;color:#9AA6B5;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">Inmueble</td>
                            <td style="font-size:14.5px;font-weight:700;color:#0E304B;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                                {{ $interaction->property->address }}{{ $interaction->property->colony ? ', ' . $interaction->property->colony : '' }}
                            </td>
                        </tr></table>
                    </td>
                </tr>
                @endif

                @if($reactionLabel)
                <tr>
                    <td style="padding:15px 20px;border-bottom:1px solid #F1F4F8;">
                        <table width="100%"><tr>
                            <td width="130" style="width:130px;font-size:11px;font-weight:800;letter-spacing:.8px;text-transform:uppercase;color:#9AA6B5;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">Reacción</td>
                            <td style="font-size:14.5px;font-weight:700;color:#0E304B;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">{{ $reactionLabel }}</td>
                        </tr></table>
                    </td>
                </tr>
                @endif

                @if($priceLabel)
                <tr>
                    <td style="padding:15px 20px;{{ $interaction->visitor_comment ? 'border-bottom:1px solid #F1F4F8;' : '' }}">
                        <table width="100%"><tr>
                            <td width="130" style="width:130px;font-size:11px;font-weight:800;letter-spacing:.8px;text-transform:uppercase;color:#9AA6B5;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">Precio</td>
                            <td style="font-size:14.5px;font-weight:700;color:#0E304B;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">{{ $priceLabel }}</td>
                        </tr></table>
                    </td>
                </tr>
                @endif

                @if($interaction->visitor_comment)
                <tr>
                    <td style="padding:15px 20px;">
                        <table width="100%"><tr>
                            <td width="130" style="width:130px;font-size:11px;font-weight:800;letter-spacing:.8px;text-transform:uppercase;color:#9AA6B5;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">Comentario</td>
                            <td style="font-size:14.5px;font-weight:600;color:#0E304B;font-style:italic;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">"{{ $interaction->visitor_comment }}"</td>
                        </tr></table>
                    </td>
                </tr>
                @endif

            </table>
        </td>
    </tr>

    <tr>
        <td class="px" style="padding:28px 34px 34px;">
            <a href="{{ $contactUrl }}"
               style="display:block;background:#0E304B;border-radius:12px;color:#FFFFFF;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;font-size:15px;font-weight:700;line-height:50px;text-align:center;text-decoration:none;">
                {{ $client ? 'Ver perfil del cliente' : 'Ver ficha del lead' }}
            </a>
        </td>
    </tr>

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
