<!DOCTYPE html>
<html lang="es" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="x-apple-disable-message-reformatting">
<title>Respuesta del propietario sobre candidato</title>
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
    $decision  = $investigation->owner_decision;
    $ownerName = $owner->name ?? 'El propietario';
    $addr      = $rental->property?->address ?? 'el inmueble';
    $crmUrl    = route('rentals.show', $rental->id);

    $configs = [
        'approved'  => ['bg' => '#D1FAE5', 'color' => '#065F46', 'badge_bg' => '#D1FAE5', 'icon' => '✓', 'label' => 'Candidato aprobado', 'msg' => 'aprobó al candidato. Puedes avanzar a la etapa de contrato.'],
        'declined'  => ['bg' => '#FEE2E2', 'color' => '#991B1B', 'badge_bg' => '#FEE2E2', 'icon' => '✕', 'label' => 'Candidato declinado', 'msg' => 'declinó al candidato. Continuaremos la búsqueda.'],
        'more_info' => ['bg' => '#FEF3C7', 'color' => '#92400E', 'badge_bg' => '#FEF3C7', 'icon' => '?', 'label' => 'Solicita más información', 'msg' => 'solicita más información antes de decidir.'],
    ];
    $cfg = $configs[$decision] ?? $configs['more_info'];
@endphp

<div style="display:none;max-height:0;overflow:hidden;mso-hide:all;font-size:1px;line-height:1px;color:#F1F4F8;">{{ $ownerName }} {{ $cfg['msg'] }}</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F1F4F8;">
<tr><td align="center" style="padding:40px 16px;">

<table role="presentation" class="container" width="600" cellpadding="0" cellspacing="0" border="0"
       style="width:600px;max-width:600px;background:#FFFFFF;border:1px solid #E6EAF1;border-radius:20px;overflow:hidden;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">

    {{-- Header --}}
    <tr>
        <td class="px" align="center" style="padding:24px 34px;border-bottom:1px solid #EEF1F6;">
            <img src="{{ asset('img/email/logo-azul.png') }}" width="116" height="40" alt="Home del Valle"
                 style="width:116px;height:40px;margin:0 auto;">
        </td>
    </tr>

    {{-- Hero --}}
    <tr>
        <td class="px" align="center" style="padding:34px 34px 0;">
            <table cellpadding="0" cellspacing="0" border="0" style="margin:0 auto;">
                <tr>
                    <td width="64" align="center">
                        <table width="64" cellpadding="0" cellspacing="0" border="0"
                               style="background:{{ $cfg['bg'] }};border-radius:32px;">
                            <tr>
                                <td align="center" valign="middle" height="64"
                                    style="height:64px;color:{{ $cfg['color'] }};font-size:26px;font-weight:800;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                                    {{ $cfg['icon'] }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <table cellpadding="0" cellspacing="0" border="0" style="margin:18px auto 0;">
                <tr>
                    <td style="background:{{ $cfg['badge_bg'] }};border-radius:999px;padding:6px 13px;font-size:11.5px;font-weight:800;letter-spacing:1.5px;color:{{ $cfg['color'] }};text-transform:uppercase;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                        {{ $cfg['label'] }}
                    </td>
                </tr>
            </table>

            <h1 style="font-size:24px;font-weight:800;color:#0E304B;margin:13px 0 0;letter-spacing:-.5px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                {{ $ownerName }} {{ $cfg['msg'] }}
            </h1>
            <p style="font-size:13.5px;color:#7A8594;margin:7px 0 0;font-weight:500;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                Inmueble: {{ $addr }}
            </p>
        </td>
    </tr>

    {{-- Notes --}}
    @if($investigation->owner_decision_notes)
    <tr>
        <td class="px" style="padding:24px 34px 0;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                   style="border-left:3px solid {{ $cfg['color'] }};background:{{ $cfg['bg'] }};border-radius:0 10px 10px 0;padding:0;overflow:hidden;">
                <tr>
                    <td style="padding:14px 18px;">
                        <div style="font-size:11px;font-weight:800;letter-spacing:.8px;text-transform:uppercase;color:{{ $cfg['color'] }};margin-bottom:6px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">Nota del propietario</div>
                        <p style="margin:0;font-size:13.5px;color:#3D4F63;line-height:1.6;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                            "{{ $investigation->owner_decision_notes }}"
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    @endif

    {{-- CTA --}}
    <tr>
        <td class="px" style="padding:28px 34px 34px;" align="center">
            <a href="{{ $crmUrl }}"
               style="display:inline-block;background:#0E304B;border-radius:12px;color:#FFFFFF;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;font-size:15px;font-weight:700;line-height:50px;text-align:center;text-decoration:none;padding:0 32px;">
                Ver proceso en CRM &rarr;
            </a>
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
