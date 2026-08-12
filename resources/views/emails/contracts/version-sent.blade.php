<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Contrato — {{ $version->contract->title }}</title>
</head>
<body style="margin:0;padding:0;background:#F1F4F8;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#F1F4F8;padding:32px 16px;">
<tr><td align="center">
<table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;">

  {{-- Logo --}}
  <tr><td align="center" style="padding-bottom:24px;">
    <img src="{{ url('img/email/logo-azul.png') }}" alt="Home del Valle" style="height:32px;display:block;">
  </td></tr>

  {{-- Card --}}
  <tr><td style="background:#fff;border-radius:16px;border:1px solid #E6EAF1;padding:40px 36px;box-shadow:0 2px 16px rgba(0,0,0,.06);">

    <p style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#1D4ED8;margin:0 0 12px;">Contrato para revisión</p>
    <h1 style="font-size:22px;font-weight:800;color:#0E304B;margin:0 0 12px;line-height:1.3;">{{ $version->contract->title }}</h1>
    <p style="font-size:15px;color:#5A6573;line-height:1.6;margin:0 0 24px;">
      Te compartimos la versión {{ $version->version_number }} de este contrato para tu revisión. El documento va adjunto en PDF.
      Si tienes comentarios o solicitas algún cambio, por favor contáctanos respondiendo a este correo o por WhatsApp.
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" style="background:#F6F8FB;border-radius:12px;padding:18px 20px;margin-bottom:8px;">
      <tr>
        <td style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#9AA6B5;padding-bottom:4px;">Versión</td>
      </tr>
      <tr>
        <td style="font-size:15px;font-weight:700;color:#0E304B;">
          Versión {{ $version->version_number }} &middot; {{ $version->created_at->translatedFormat('d \d\e F \d\e Y') }}
        </td>
      </tr>
    </table>

  </td></tr>

  {{-- Footer --}}
  <tr><td align="center" style="padding-top:20px;font-size:12px;color:#9AA6B5;line-height:1.8;">
    <strong style="color:#475569;">Home del Valle</strong><br>
    contacto@homedelvalle.mx &middot; +52 55 1345 0978
  </td></tr>

</table>
</td></tr>
</table>
</body>
</html>
