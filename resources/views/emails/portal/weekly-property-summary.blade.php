<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Resumen semanal de tu inmueble</title>
</head>
<body style="margin:0;padding:0;background:#F1F4F8;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#F1F4F8;padding:32px 16px;">
<tr><td align="center">
<table width="100%" cellpadding="0" cellspacing="0" style="max-width:580px;">

  {{-- Logo --}}
  <tr><td align="center" style="padding-bottom:24px;">
    <img src="{{ url('img/email/logo-azul.png') }}" alt="Home del Valle" style="height:32px;display:block;">
  </td></tr>

  {{-- Card --}}
  <tr><td style="background:#fff;border-radius:16px;border:1px solid #E6EAF1;overflow:hidden;box-shadow:0 2px 16px rgba(0,0,0,.06);">

    {{-- Header band --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#0E304B;padding:28px 36px;">
      <tr>
        <td>
          <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#93C5FD;margin:0 0 8px;">Resumen semanal</p>
          <h1 style="font-size:22px;font-weight:800;color:#fff;margin:0 0 6px;line-height:1.3;">
            {{ $visits->count() }} {{ $visits->count() === 1 ? 'visita' : 'visitas' }} esta semana
          </h1>
          <p style="font-size:13px;color:#94A3B8;margin:0;">
            {{ ucfirst($weekStart->locale('es')->isoFormat('D [de] MMMM')) }}
            — {{ ucfirst($weekStart->copy()->endOfWeek()->locale('es')->isoFormat('D [de] MMMM, YYYY')) }}
          </p>
        </td>
      </tr>
    </table>

    {{-- Body --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="padding:32px 36px;">

      <tr><td>
        <p style="font-size:15px;color:#475569;line-height:1.6;margin:0 0 28px;">
          Hola {{ $owner->name }}, aquí tienes el resumen de actividad de tu inmueble
          <strong style="color:#0E304B;">{{ $property->address }}{{ $property->colony ? ', ' . $property->colony : '' }}</strong>
          durante la semana pasada.
        </p>
      </td></tr>

      {{-- Stats row --}}
      <tr><td style="padding-bottom:28px;">
        <table width="100%" cellpadding="0" cellspacing="0">
          <tr>
            <td width="33%" align="center" style="background:#F6F8FB;border-radius:10px;padding:18px 12px;text-align:center;">
              <div style="font-size:28px;font-weight:800;color:#0E304B;">{{ $visits->count() }}</div>
              <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#9AA6B5;margin-top:4px;">Visitas</div>
            </td>
            <td width="4%"></td>
            <td width="29%" align="center" style="background:#ECFDF5;border-radius:10px;padding:18px 12px;text-align:center;">
              <div style="font-size:28px;font-weight:800;color:#166534;">{{ $confirmedVisits }}</div>
              <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#16a34a;margin-top:4px;">Confirmadas</div>
            </td>
            <td width="4%"></td>
            <td width="30%" align="center" style="background:#EFF6FF;border-radius:10px;padding:18px 12px;text-align:center;">
              <div style="font-size:28px;font-weight:800;color:#1D4ED8;">{{ $positiveFeedback }}</div>
              <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#2563EB;margin-top:4px;">👍 Positivas</div>
            </td>
          </tr>
        </table>
      </td></tr>

      {{-- CTA --}}
      <tr><td align="center" style="padding-bottom:8px;">
        <a href="{{ $portalUrl }}"
           style="display:inline-block;background:#0E304B;color:#fff;font-size:15px;font-weight:700;padding:14px 36px;border-radius:10px;text-decoration:none;letter-spacing:.2px;">
          Ver detalle en mi portal &rarr;
        </a>
      </td></tr>

    </table>

  </td></tr>

  {{-- Footer --}}
  <tr><td align="center" style="padding-top:20px;font-size:12px;color:#9AA6B5;line-height:1.8;">
    <strong style="color:#475569;">Home del Valle</strong><br>
    contacto@homedelvalle.mx &middot; +52 55 1345 0978<br>
    Heriberto Frías 903-C, Col. del Valle, CDMX<br>
    <a href="https://miportal.homedelvalle.mx/cuenta" style="color:#9AA6B5;">Gestionar notificaciones</a>
    &middot; © Home del Valle 2026
  </td></tr>

</table>
</td></tr>
</table>
</body>
</html>
