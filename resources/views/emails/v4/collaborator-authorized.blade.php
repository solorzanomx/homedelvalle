<!DOCTYPE html>
<html lang="es" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="x-apple-disable-message-reformatting">
<title>Confirmación de tu autorización</title>
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

<div style="display:none;max-height:0;overflow:hidden;mso-hide:all;font-size:1px;line-height:1px;color:#F1F4F8;">Confirmamos tu autorización para aparecer en homedelvalle.mx.</div>

@php
    $photoUrl = $collaborator->photo_path ? url(\Illuminate\Support\Facades\Storage::url($collaborator->photo_path)) : null;
    $logoSrc = $logoUrl ?? ($iconBase . 'logo-azul.png');
@endphp

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F1F4F8;">
<tr><td align="center" style="padding:40px 16px;">

<table role="presentation" class="container" width="600" cellpadding="0" cellspacing="0" border="0"
       style="width:600px;max-width:600px;background:#FFFFFF;border:1px solid #E6EAF1;border-radius:20px;overflow:hidden;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">

    {{-- Header --}}
    <tr>
        <td class="px" align="center" style="padding:24px 34px;border-bottom:1px solid #EEF1F6;">
            <img src="{{ $logoSrc }}" width="116" height="40" alt="Home del Valle" style="width:116px;height:40px;margin:0 auto;">
            <div style="font-size:11px;color:#7A8594;font-weight:700;letter-spacing:.4px;margin-top:9px;">
                Pocos inmuebles &middot; Más control &middot; Mejores resultados
            </div>
        </td>
    </tr>

    {{-- Hero --}}
    <tr>
        <td class="px" align="center" style="padding:36px 34px 0;">
            <table cellpadding="0" cellspacing="0" border="0" style="margin:0 auto;">
                <tr>
                    <td style="background:#ECFDF5;border-radius:999px;padding:6px 13px;font-size:11.5px;font-weight:800;letter-spacing:1.5px;color:#15803D;text-transform:uppercase;">
                        Autorización confirmada
                    </td>
                </tr>
            </table>
            <h1 style="font-size:24px;font-weight:800;color:#0E304B;margin:14px 0 0;letter-spacing:-.4px;line-height:1.2;">
                Gracias, {{ explode(' ', trim($collaborator->name))[0] }}
            </h1>
            <p style="font-size:15px;line-height:1.6;color:#5A6573;margin:12px auto 0;max-width:44ch;">
                Confirmamos que autorizaste a Home del Valle a publicar la siguiente información en homedelvalle.mx y en materiales relacionados con la firma.
            </p>
        </td>
    </tr>

    {{-- Snapshot card --}}
    <tr>
        <td class="px" style="padding:28px 34px 0;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #E6EAF1;border-radius:16px;background:#F6F8FB;">
                <tr>
                    <td style="padding:22px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                @if($photoUrl)
                                <td width="56" valign="top" style="width:56px;">
                                    <img src="{{ $photoUrl }}" width="56" height="56" alt="{{ $collaborator->name }}"
                                         style="width:56px;height:56px;border-radius:50%;object-fit:cover;">
                                </td>
                                <td width="14" style="width:14px;">&nbsp;</td>
                                @endif
                                <td valign="middle">
                                    <div style="font-size:16px;font-weight:800;color:#0E304B;">{{ $collaborator->name }}</div>
                                    <div style="font-size:13px;font-weight:700;color:#2270B0;margin-top:2px;">{{ $collaborator->role }}</div>
                                </td>
                            </tr>
                        </table>
                        @if($collaborator->bio)
                        <p style="font-size:13.5px;color:#5A6573;line-height:1.6;margin:16px 0 0;">{{ $collaborator->bio }}</p>
                        @endif
                        @if($collaborator->link_url)
                        <p style="font-size:13px;margin:12px 0 0;">
                            <a href="{{ $collaborator->link_url }}" style="color:#2270B0;font-weight:700;">{{ $collaborator->link_label ?: $collaborator->link_url }}</a>
                        </p>
                        @endif
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    {{-- Detail row: fecha --}}
    <tr>
        <td class="px" style="padding:20px 34px 0;">
            <div style="font-size:11px;letter-spacing:.6px;text-transform:uppercase;color:#9AA6B5;font-weight:800;">Autorizado el</div>
            <div style="font-size:14px;font-weight:700;color:#0E304B;margin-top:2px;">{{ $collaborator->consent_at?->translatedFormat('d \d\e F \d\e Y, H:i') }}</div>
        </td>
    </tr>

    {{-- Footer note --}}
    <tr>
        <td class="px" style="padding:24px 34px 34px;">
            <p style="font-size:12.5px;color:#7A8594;line-height:1.7;margin:0;">
                Si en algún momento quieres que dejemos de publicar esta información, escríbenos a
                <a href="mailto:contacto@homedelvalle.mx" style="color:#2270B0;font-weight:700;">contacto@homedelvalle.mx</a>
                y lo damos de baja sin necesidad de que nos des explicación.
            </p>
        </td>
    </tr>

    {{-- Footer --}}
    <tr>
        <td style="background:#0E304B;padding:22px 34px;">
            <div style="text-align:center;">
                <div style="font-size:11.5px;color:#C4D0E0;font-weight:600;">© Home del Valle 2026</div>
                <div style="font-size:11px;color:#7E8DA6;margin-top:6px;">Heriberto Frías 903-A, Col. del Valle, Benito Juárez, CDMX</div>
            </div>
        </td>
    </tr>

</table>
</td></tr>
</table>
</body>
</html>
