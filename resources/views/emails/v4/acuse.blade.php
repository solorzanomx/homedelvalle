@php
    // Helper: replace {{key}} in text
    function acuseInterp(string $text, array $vars): string {
        foreach ($vars as $k => $v) {
            $text = str_replace('{{' . $k . '}}', $v, $text);
        }
        $text = str_replace('{{nombre}}', $vars['saludo'] ?? '', $text);
        return $text;
    }

    $badge     = acuseInterp($config->badge, $vars);
    $titulo    = acuseInterp($config->titulo, $vars);
    $bajada    = acuseInterp($config->bajada, $vars);
    $nota      = $config->nota ? acuseInterp($config->nota, $vars) : null;
    $cta1Label = acuseInterp($config->cta1_label, $vars);
    $cta2Label = $config->cta2_label ? acuseInterp($config->cta2_label, $vars) : null;
    $preheader = $badge . ' — respondemos pronto.';
@endphp
<!DOCTYPE html>
<html lang="es" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta http-equiv="X-UA-Compatible" content="IE=edge"><meta name="x-apple-disable-message-reformatting">
<title>{{ $titulo }}</title>
<!--[if mso]><noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript><![endif]-->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
body,table,td,a{-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%}table,td{mso-table-lspace:0;mso-table-rspace:0}img{-ms-interpolation-mode:bicubic;border:0;outline:none;text-decoration:none;display:block}body{margin:0;padding:0;width:100%!important;background:#F1F4F8}a{text-decoration:none}
@media screen and (max-width:620px){.container{width:100%!important}.px{padding-left:22px!important;padding-right:22px!important}.stack{display:block!important;width:100%!important;margin-bottom:10px!important}.btnh{height:12px!important;width:auto!important}}
</style>
</head>
<body style="margin:0;padding:0;background:#F1F4F8;">
<div style="display:none;max-height:0;overflow:hidden;mso-hide:all;font-size:1px;line-height:1px;color:#F1F4F8;">{{ $preheader }}</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F1F4F8;"><tr><td align="center" style="padding:40px 16px;">
<table role="presentation" class="container" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px;max-width:600px;background:#FFFFFF;border:1px solid #E6EAF1;border-radius:20px;overflow:hidden;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">

{{-- HEADER --}}
<tr><td class="px" align="center" style="padding:24px 34px;border-bottom:1px solid #EEF1F6;">
<img src="{{ $logoUrl ?? $iconBase . 'logo-azul.png' }}" width="116" height="40" alt="Home del Valle" style="width:116px;height:40px;margin:0 auto;">
<div style="font-size:11px;color:#7A8594;font-weight:700;letter-spacing:.4px;margin-top:9px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">Pocos inmuebles &middot; Más control &middot; Mejores resultados</div>
</td></tr>

{{-- HERO --}}
<tr><td class="px" align="center" style="padding:36px 34px 0;">
<div style="font-size:12px;font-weight:800;letter-spacing:2px;color:#2E80C6;text-transform:uppercase;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">{{ $badge }}</div>
<h1 style="font-size:27px;font-weight:800;color:#0E304B;margin:11px 0 0;letter-spacing:-.5px;line-height:1.15;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">{{ $titulo }}</h1>
<p style="font-size:15.5px;line-height:1.6;color:#5A6573;margin:13px auto 0;max-width:44ch;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">{!! $bajada !!}</p>
</td></tr>

{{-- FOLIO --}}
<tr><td class="px" style="padding:28px 34px 0;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F6F8FB;border:1px solid #EEF1F6;border-radius:16px;"><tr><td style="padding:18px 20px;">
<table cellpadding="0" cellspacing="0" border="0"><tr>
<td width="44" valign="middle"><table width="44" cellpadding="0" cellspacing="0" border="0" style="background:#DFF3E6;border-radius:22px;"><tr><td align="center" valign="middle" height="44" style="height:44px;"><img src="{{ $iconBase }}icon-check.png" width="22" height="22" alt="✓"></td></tr></table></td>
<td width="16">&nbsp;</td>
<td valign="middle">
<div style="font-size:11px;font-weight:800;letter-spacing:1px;color:#9AA6B5;text-transform:uppercase;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">Folio de seguimiento</div>
<div style="font-family:'Courier New',monospace;font-size:16px;font-weight:700;color:#0E304B;margin-top:4px;letter-spacing:.5px;">{{ $folio }}</div>
</td>
</tr></table>
</td></tr></table>
</td></tr>

{{-- QUÉ SIGUE --}}
<tr><td class="px" style="padding:28px 34px 0;">
<div style="font-size:13px;font-weight:800;color:#0E304B;text-transform:uppercase;letter-spacing:1px;margin-bottom:14px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">Qué sigue</div>

@foreach([
    ['icon'=>$config->paso1_icon,'titulo'=>$config->paso1_titulo,'desc'=>$config->paso1_desc],
    ['icon'=>$config->paso2_icon,'titulo'=>$config->paso2_titulo,'desc'=>$config->paso2_desc],
    ['icon'=>$config->paso3_icon,'titulo'=>$config->paso3_titulo,'desc'=>$config->paso3_desc],
] as $paso)
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F6F8FB;border:1px solid #EEF1F6;border-radius:14px;margin-bottom:12px;"><tr><td style="padding:16px 18px;">
<table width="100%"><tr>
<td width="40" valign="top" style="width:40px;"><table role="presentation" width="40" cellpadding="0" cellspacing="0" border="0" style="background:#E7F0FE;border-radius:11px;margin:0 auto;"><tr><td align="center" valign="middle" height="40" style="height:40px;"><img src="{{ $iconBase }}{{ $paso['icon'] }}" width="20" height="20" alt="" style="width:20px;height:20px;"></td></tr></table></td>
<td width="14">&nbsp;</td>
<td valign="top">
<div style="font-size:15px;font-weight:700;color:#0E304B;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">{{ $paso['titulo'] }}</div>
<div style="font-size:13.5px;color:#7A8594;margin-top:2px;line-height:1.5;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">{{ $paso['desc'] }}</div>
</td>
</tr></table>
</td></tr></table>
@endforeach

@if($nota)
<p style="font-size:12px;color:#9AA6B5;margin:6px 0 0;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">{{ $nota }}</p>
@endif
</td></tr>

{{-- BOTONES --}}
<tr><td class="px" style="padding:28px 34px 34px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
<td class="stack" valign="top">
<!--[if mso]><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $cta1Url }}" style="height:52px;v-text-anchor:middle;width:240px;" arcsize="24%" stroke="f" fillcolor="#0E304B"><w:anchorlock/><center style="color:#FFFFFF;font-family:Arial,sans-serif;font-size:15px;font-weight:bold;">{{ $cta1Label }}</center></v:roundrect><![endif]-->
<!--[if !mso]><!-->
<a href="{{ $cta1Url }}" style="display:block;background:#0E304B;border-radius:12px;color:#FFFFFF;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;font-size:15px;font-weight:700;line-height:50px;text-align:center;text-decoration:none;">{{ $cta1Label }}</a>
<!--<![endif]-->
</td>
@if($cta2Url && $cta2Label)
<td class="btnh" width="12" style="width:12px;">&nbsp;</td>
<td class="stack" valign="top">
<!--[if mso]><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $cta2Url }}" style="height:52px;v-text-anchor:middle;width:240px;" arcsize="24%" strokecolor="#D5DCE7" strokeweight="1.5px" fillcolor="#FFFFFF"><w:anchorlock/><center style="color:#0E304B;font-family:Arial,sans-serif;font-size:15px;font-weight:bold;">{{ $cta2Label }}</center></v:roundrect><![endif]-->
<!--[if !mso]><!-->
<a href="{{ $cta2Url }}" style="display:block;background:#FFFFFF;border:1.5px solid #D5DCE7;border-radius:12px;color:#0E304B;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;font-size:15px;font-weight:700;line-height:50px;text-align:center;text-decoration:none;">{{ $cta2Label }}</a>
<!--<![endif]-->
</td>
@endif
</tr></table>
</td></tr>

{{-- FOOTER --}}
<tr><td align="center" style="background:#0E304B;padding:28px 34px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 auto;"><tr>
<td><a href="https://homedelvalle.mx" style="display:inline-block;width:34px;height:34px;line-height:34px;border-radius:10px;background:rgba(255,255,255,.07);color:#9FB0C6;font-size:12px;font-weight:700;text-align:center;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">f</a></td>
<td style="padding-left:8px;"><a href="https://instagram.com/homedelvalle" style="display:inline-block;width:34px;height:34px;line-height:34px;border-radius:10px;background:rgba(255,255,255,.07);color:#9FB0C6;font-size:12px;font-weight:700;text-align:center;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">ig</a></td>
</tr></table>
<div style="font-size:13px;margin-top:18px;"><a href="https://homedelvalle.mx" style="color:#7FB0DF;font-weight:700;">homedelvalle.mx</a> <span style="color:#5A6A85;">&middot;</span> <a href="mailto:contacto@homedelvalle.mx" style="color:#7FB0DF;font-weight:700;">contacto@homedelvalle.mx</a></div>
<div style="font-size:11.5px;color:#7E8DA6;margin-top:9px;">Notificación automática &middot; Home del Valle</div>
</td></tr>

</table>
</td></tr></table>
</body></html>
