<!DOCTYPE html>
<html lang="es" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="x-apple-disable-message-reformatting">
<title>Tu visita está agendada</title>
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
<div style="display:none;max-height:0;overflow:hidden;mso-hide:all;font-size:1px;line-height:1px;color:#F1F4F8;">Tu visita está agendada para {{ $data->dia_semana }}, {{ $data->dia }} de {{ $data->mes }} a las {{ $data->hora }}.</div>

@php
    $logoSrc = $logoUrl ?? ($iconBase . 'logo-azul.png');
    $rescheduleUrl = 'https://homedelvalle.mx/visit/' . $data->visit_token . '/reschedule';
    $calendarUrl = $data->maps_url ?: '#';
    // Asesor initials: first letter of first two words
    $asesorWords = preg_split('/\s+/', trim($data->asesor));
    $initials = strtoupper(substr($asesorWords[0] ?? 'A', 0, 1) . substr($asesorWords[1] ?? 'X', 0, 1));
@endphp

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F1F4F8;">
<tr><td align="center" style="padding:40px 16px;">

<table role="presentation" class="container" width="600" cellpadding="0" cellspacing="0" border="0"
       style="width:600px;max-width:600px;background:#FFFFFF;border:1px solid #E6EAF1;border-radius:20px;overflow:hidden;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">

    {{-- Header: Logo --}}
    <tr>
        <td class="px" align="center" style="padding:24px 34px;border-bottom:1px solid #EEF1F6;">
            <img src="{{ $logoSrc }}" width="116" height="40" alt="Home del Valle"
                 style="width:116px;height:40px;margin:0 auto;">
            <div style="font-size:11px;color:#7A8594;font-weight:700;letter-spacing:.4px;margin-top:9px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                Pocos inmuebles &middot; Más control &middot; Mejores resultados
            </div>
        </td>
    </tr>

    {{-- Hero --}}
    <tr>
        <td class="px" align="center" style="padding:36px 34px 0;">
            {{-- Badge --}}
            <table cellpadding="0" cellspacing="0" border="0" style="margin:0 auto;">
                <tr>
                    <td style="background:#EAF3FB;border-radius:999px;padding:6px 13px;font-size:11.5px;font-weight:800;letter-spacing:1.5px;color:#2270B0;text-transform:uppercase;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                        Visita Confirmada
                    </td>
                </tr>
            </table>
            <h1 style="font-size:27px;font-weight:800;color:#0E304B;margin:11px 0 0;letter-spacing:-.5px;line-height:1.15;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                Tu visita está agendada
            </h1>
            <p style="font-size:15.5px;line-height:1.6;color:#5A6573;margin:13px auto 0;max-width:44ch;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                Te esperamos. Aquí están los detalles para que llegues sin contratiempos.
            </p>
        </td>
    </tr>

    {{-- Date card --}}
    <tr>
        <td class="px" style="padding:28px 34px 0;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                   style="border:1px solid #E6EAF1;border-radius:16px;overflow:hidden;">
                <tr>
                    <td style="background:#0E304B;padding:18px 22px;">
                        <table width="100%">
                            <tr>
                                <td valign="middle">
                                    <div style="color:#8FA9D2;font-size:12px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">{{ $data->dia_semana }} {{ $data->dia }} de {{ $data->mes }}</div>
                                    <div style="color:#fff;font-size:22px;font-weight:800;margin-top:3px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">{{ $data->hora }} <span style="color:#C4D2E9;font-weight:600;font-size:15px;">&middot; {{ $data->duracion }} min</span></div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Details rows --}}
                <tr>
                    <td style="padding:20px 22px;background:#F6F8FB;">

                        {{-- Inmueble --}}
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td width="34" valign="top" style="width:34px;">
                                    <table role="presentation" width="34" cellpadding="0" cellspacing="0" border="0"
                                           style="background:#E7F0FE;border-radius:11px;margin:0 auto;">
                                        <tr>
                                            <td align="center" valign="middle" height="34" style="height:34px;">
                                                <img src="{{ $iconBase }}icon-home.png" width="18" height="18" style="width:18px;height:18px;">
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td width="12" style="width:12px;">&nbsp;</td>
                                <td valign="top">
                                    <div style="font-size:11px;letter-spacing:.6px;text-transform:uppercase;color:#9AA6B5;font-weight:800;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">Inmueble</div>
                                    <div style="font-size:15px;font-weight:700;color:#0E304B;line-height:1.35;margin-top:2px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">{{ $data->direccion }}</div>
                                </td>
                            </tr>
                        </table>

                        <div style="height:16px;"></div>

                        {{-- Zona --}}
                        @if($data->colonia)
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td width="34" valign="top" style="width:34px;">
                                    <table role="presentation" width="34" cellpadding="0" cellspacing="0" border="0"
                                           style="background:#E7F0FE;border-radius:11px;margin:0 auto;">
                                        <tr>
                                            <td align="center" valign="middle" height="34" style="height:34px;">
                                                <img src="{{ $iconBase }}icon-pin.png" width="18" height="18" style="width:18px;height:18px;">
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td width="12" style="width:12px;">&nbsp;</td>
                                <td valign="top">
                                    <div style="font-size:11px;letter-spacing:.6px;text-transform:uppercase;color:#9AA6B5;font-weight:800;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">Zona</div>
                                    <div style="font-size:15px;font-weight:700;color:#0E304B;line-height:1.35;margin-top:2px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">{{ $data->colonia }}</div>
                                </td>
                            </tr>
                        </table>

                        <div style="height:16px;"></div>
                        @endif

                        {{-- Asesor --}}
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td width="34" valign="middle" style="width:34px;">
                                    <table width="34" cellpadding="0" cellspacing="0" border="0"
                                           style="background:#0E304B;border-radius:17px;">
                                        <tr>
                                            <td align="center" valign="middle" height="34" style="height:34px;color:#fff;font-size:12px;font-weight:800;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">{{ $initials }}</td>
                                        </tr>
                                    </table>
                                </td>
                                <td width="12">&nbsp;</td>
                                <td valign="middle">
                                    <div style="font-size:11px;letter-spacing:.6px;text-transform:uppercase;color:#9AA6B5;font-weight:800;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">Te acompaña</div>
                                    <div style="font-size:15px;font-weight:700;color:#0E304B;margin-top:2px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">{{ $data->asesor }}</div>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>
            </table>
        </td>
    </tr>

    {{-- Buttons --}}
    <tr>
        <td class="px" style="padding:28px 34px 34px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    {{-- Reagendar (primary, navy) --}}
                    <td class="stack" valign="top">
                        <!--[if mso]><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $rescheduleUrl }}" style="height:52px;v-text-anchor:middle;width:240px;" arcsize="24%" stroke="f" fillcolor="#0E304B"><w:anchorlock/><center style="color:#FFFFFF;font-family:Arial,sans-serif;font-size:15px;font-weight:bold;">Reagendar</center></v:roundrect><![endif]-->
                        <!--[if !mso]><!-->
                        <a href="{{ $rescheduleUrl }}"
                           style="display:block;background:#0E304B;border-radius:12px;color:#FFFFFF;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;font-size:15px;font-weight:700;line-height:50px;text-align:center;text-decoration:none;">
                            Reagendar
                        </a>
                        <!--<![endif]-->
                    </td>

                    <td class="btnh" width="12" style="width:12px;">&nbsp;</td>

                    {{-- Agregar al calendario (secondary, white border) --}}
                    <td class="stack" valign="top">
                        <!--[if mso]><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $calendarUrl }}" style="height:52px;v-text-anchor:middle;width:240px;" arcsize="24%" strokecolor="#D5DCE7" strokeweight="1.5px" fillcolor="#FFFFFF"><w:anchorlock/><center style="color:#0E304B;font-family:Arial,sans-serif;font-size:15px;font-weight:bold;">Agregar al calendario</center></v:roundrect><![endif]-->
                        <!--[if !mso]><!-->
                        <a href="{{ $calendarUrl }}"
                           style="display:block;background:#FFFFFF;border:1.5px solid #D5DCE7;border-radius:12px;color:#0E304B;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;font-size:15px;font-weight:700;line-height:50px;text-align:center;text-decoration:none;">
                            Agregar al calendario
                        </a>
                        <!--<![endif]-->
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    {{-- Footer --}}
    <tr>
        <td style="background:#0E304B;padding:30px 34px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td valign="top" style="font-size:11.5px;color:#9FB0C6;line-height:1.85;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                        <span style="font-weight:700;color:#fff;font-size:12px;">+52 55 1345 0978</span><br>
                        <a href="mailto:contacto@homedelvalle.mx" style="color:#7FB0DF;font-weight:700;text-decoration:none;">contacto@homedelvalle.mx</a><br>
                        Heriberto Frías 903-A, Col. del Valle, CDMX
                    </td>
                    <td valign="top" align="right">
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 auto;">
                            <tr>
                                <td><a href="https://facebook.com/homedelvalle" style="display:inline-block;width:34px;height:34px;line-height:34px;border-radius:10px;background:rgba(255,255,255,.07);color:#9FB0C6;font-size:12px;font-weight:700;text-align:center;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">f</a></td>
                                <td style="padding-left:8px;"><a href="https://instagram.com/homedelvalle" style="display:inline-block;width:34px;height:34px;line-height:34px;border-radius:10px;background:rgba(255,255,255,.07);color:#9FB0C6;font-size:12px;font-weight:700;text-align:center;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">ig</a></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <div style="height:1px;background:rgba(255,255,255,.12);margin:22px 0 16px;"></div>
            <div style="text-align:center;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
                <div style="font-size:11.5px;color:#C4D0E0;font-weight:600;">© Home del Valle 2026</div>
                <div style="font-size:11px;color:#7E8DA6;margin-top:7px;">Políticas de privacidad &middot; Términos y condiciones</div>
            </div>
        </td>
    </tr>

</table>
</td></tr>
</table>
</body>
</html>
