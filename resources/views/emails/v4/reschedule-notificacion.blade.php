<!DOCTYPE html>
<html lang="es" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="x-apple-disable-message-reformatting">
<title>Solicitud de reagendamiento — {{ $interaction->client?->name ?? 'Cliente' }}</title>
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

@php
    $client   = $interaction->client;
    $name     = $client?->name ?? 'El cliente';
    $words    = preg_split('/\s+/', trim($name));
    $iniciales = strtoupper(substr($words[0] ?? 'C', 0, 1) . substr($words[1] ?? '', 0, 1));
    $clientUrl = $client ? route('clients.show', $client) : url('/admin');
    $scheduled = $interaction->scheduled_at;
@endphp

{{-- Preheader --}}
<div style="display:none;max-height:0;overflow:hidden;mso-hide:all;font-size:1px;line-height:1px;color:#F1F4F8;">{{ $name }} quiere reagendar su visita{{ $scheduled ? ' del ' . $scheduled->format('d/m/Y') : '' }}.</div>

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

            {{-- Avatar --}}
            <table cellpadding="0" cellspacing="0" border="0" style="margin:0 auto;">
                <tr>
                    <td width="64" align="center">
                        <table width="64" cellpadding="0" cellspacing="0" border="0"
                               style="background:#FEF3C7;border-radius:32px;">
                            <tr>
                                <td align="center" valign="middle" height="64"
                                    style="height:64px;color:#92400E;font-size:22px;font-weight:800;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                                    {{ $iniciales }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            {{-- Badge --}}
            <table cellpadding="0" cellspacing="0" border="0" style="margin:18px auto 0;">
                <tr>
                    <td style="background:#FEF3C7;border-radius:999px;padding:6px 13px;font-size:11.5px;font-weight:800;letter-spacing:1.5px;color:#92400E;text-transform:uppercase;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                        ↩ Reagendamiento solicitado
                    </td>
                </tr>
            </table>

            <h1 style="font-size:27px;font-weight:800;color:#0E304B;margin:13px 0 0;letter-spacing:-.5px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                {{ $name }}
            </h1>
            <p style="font-size:14.5px;color:#7A8594;margin:7px 0 0;font-weight:500;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                No podrá asistir a su visita y solicita reagendarla
            </p>
        </td>
    </tr>

    {{-- Data table --}}
    <tr>
        <td class="px" style="padding:26px 34px 0;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                   style="border:1px solid #E6EAF1;border-radius:16px;overflow:hidden;">

                {{-- Visita original --}}
                @if($scheduled)
                <tr>
                    <td style="padding:15px 20px;border-bottom:1px solid #F1F4F8;">
                        <table width="100%"><tr>
                            <td width="130" valign="middle" style="width:130px;font-size:11px;font-weight:800;letter-spacing:.8px;text-transform:uppercase;color:#9AA6B5;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">Visita agendada</td>
                            <td valign="middle" style="font-size:14.5px;font-weight:700;color:#0E304B;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                                {{ $scheduled->locale('es')->isoFormat('dddd D [de] MMMM · H:mm') }} h
                            </td>
                        </tr></table>
                    </td>
                </tr>
                @endif

                {{-- Inmueble --}}
                @if($interaction->property)
                <tr>
                    <td style="padding:15px 20px;border-bottom:1px solid #F1F4F8;">
                        <table width="100%"><tr>
                            <td width="130" valign="middle" style="width:130px;font-size:11px;font-weight:800;letter-spacing:.8px;text-transform:uppercase;color:#9AA6B5;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">Inmueble</td>
                            <td valign="middle" style="font-size:14.5px;font-weight:700;color:#0E304B;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                                {{ $interaction->property->address }}{{ $interaction->property->colony ? ', ' . $interaction->property->colony : '' }}
                            </td>
                        </tr></table>
                    </td>
                </tr>
                @endif

                {{-- Solicitado el --}}
                <tr>
                    <td style="padding:15px 20px;">
                        <table width="100%"><tr>
                            <td width="130" valign="middle" style="width:130px;font-size:11px;font-weight:800;letter-spacing:.8px;text-transform:uppercase;color:#9AA6B5;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">Solicitado el</td>
                            <td valign="middle" style="font-size:14.5px;font-weight:700;color:#0E304B;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                                {{ now()->locale('es')->isoFormat('D [de] MMMM · H:mm') }} h
                            </td>
                        </tr></table>
                    </td>
                </tr>

            </table>
        </td>
    </tr>

    {{-- Mensaje del cliente --}}
    @if($interaction->reschedule_message)
    <tr>
        <td class="px" style="padding:24px 34px 0;">
            <div style="font-size:11px;font-weight:800;letter-spacing:.8px;text-transform:uppercase;color:#9AA6B5;margin-bottom:10px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                Mensaje del cliente
            </div>
            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                   style="background:#FFFBEB;border-left:3px solid #F59E0B;border-radius:4px 14px 14px 4px;">
                <tr>
                    <td style="padding:17px 20px;font-size:15px;line-height:1.6;color:#2c3a52;font-style:italic;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                        "{{ $interaction->reschedule_message }}"
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    @endif

    {{-- Botones --}}
    <tr>
        <td class="px" style="padding:28px 34px 34px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td class="stack" valign="top">
                        <!--[if mso]><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $clientUrl }}" style="height:52px;v-text-anchor:middle;width:240px;" arcsize="24%" stroke="f" fillcolor="#0E304B"><w:anchorlock/><center style="color:#FFFFFF;font-family:Arial,sans-serif;font-size:15px;font-weight:bold;">Ver perfil del cliente</center></v:roundrect><![endif]-->
                        <!--[if !mso]><!-->
                        <a href="{{ $clientUrl }}"
                           style="display:block;background:#0E304B;border-radius:12px;color:#FFFFFF;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;font-size:15px;font-weight:700;line-height:50px;text-align:center;text-decoration:none;">
                            Ver perfil del cliente
                        </a>
                        <!--<![endif]-->
                    </td>

                    @if($client?->phone)
                    <td class="btnh" width="12" style="width:12px;">&nbsp;</td>
                    <td class="stack" valign="top">
                        <!--[if mso]><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $client->phone) }}" style="height:52px;v-text-anchor:middle;width:240px;" arcsize="24%" strokecolor="#25D366" strokeweight="1.5px" fillcolor="#FFFFFF"><w:anchorlock/><center style="color:#25D366;font-family:Arial,sans-serif;font-size:15px;font-weight:bold;">WhatsApp</center></v:roundrect><![endif]-->
                        <!--[if !mso]><!-->
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $client->phone) }}"
                           style="display:block;background:#FFFFFF;border:1.5px solid #25D366;border-radius:12px;color:#25D366;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;font-size:15px;font-weight:700;line-height:50px;text-align:center;text-decoration:none;">
                            WhatsApp
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
