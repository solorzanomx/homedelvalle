<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Visita reagendada</title>
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

    <p style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#F59E0B;margin:0 0 12px;">Tu Inmueble</p>
    <h1 style="font-size:22px;font-weight:800;color:#0E304B;margin:0 0 12px;line-height:1.3;">Solicitud de reagendamiento</h1>
    <p style="font-size:15px;color:#5A6573;line-height:1.6;margin:0 0 24px;">
      Un interesado en tu inmueble ha solicitado cambiar la fecha de su visita.
    </p>

    @if($interaction->reschedule_message)
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#FEF3C7;border-left:4px solid #F59E0B;border-radius:0 10px 10px 0;padding:16px 18px;margin-bottom:28px;">
      <tr>
        <td style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#92400E;padding-bottom:6px;">Mensaje del visitante</td>
      </tr>
      <tr>
        <td style="font-size:14px;color:#78350F;line-height:1.5;">{{ $interaction->reschedule_message }}</td>
      </tr>
    </table>
    @endif

    @if($interaction->scheduled_at)
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#F6F8FB;border-radius:12px;padding:14px 18px;margin-bottom:28px;">
      <tr>
        <td style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#9AA6B5;padding-bottom:4px;">Visita original</td>
      </tr>
      <tr>
        <td style="font-size:14px;font-weight:600;color:#0E304B;">
          {{ ucfirst($interaction->scheduled_at->locale('es')->isoFormat('dddd D [de] MMMM, YYYY')) }}
          — {{ $interaction->scheduled_at->format('g:i A') }}
        </td>
      </tr>
    </table>
    @endif

    {{-- CTA --}}
    <table width="100%" cellpadding="0" cellspacing="0">
      <tr><td align="center">
        <a href="https://miportal.homedelvalle.mx/mi-inmueble"
           style="display:inline-block;background:#0E304B;color:#fff;font-size:15px;font-weight:700;padding:14px 32px;border-radius:10px;text-decoration:none;">
          Ver mi inmueble &rarr;
        </a>
      </td></tr>
    </table>

  </td></tr>

  {{-- Footer --}}
  <tr><td align="center" style="padding-top:20px;font-size:12px;color:#9AA6B5;line-height:1.8;">
    <strong style="color:#475569;">Home del Valle</strong><br>
    contacto@homedelvalle.mx &middot; +52 55 1345 0978<br>
    <a href="https://miportal.homedelvalle.mx/cuenta" style="color:#9AA6B5;">Gestionar notificaciones</a>
  </td></tr>

</table>
</td></tr>
</table>
</body>
</html>
