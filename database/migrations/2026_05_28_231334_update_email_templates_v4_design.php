<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table("email_templates")
            ->where("name", "presentation_initial")
            ->update(["body" => '<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Home del Valle — Presentación</title>
<style type="text/css">
*{margin:0;padding:0;border:0}
html,body{margin:0;padding:0;font-family:"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;color:#4B5563;background-color:#F6F7F9}
table{border-collapse:collapse;mso-table-lspace:0;mso-table-rspace:0}
img{outline:none;text-decoration:none;border:none}
a{color:#2E84C4;text-decoration:none}
@media only screen and (max-width:520px){.container{width:100%!important}.inner-container{padding:16px!important}}
</style>
</head>
<body>
<div style="display:none;font-size:0;line-height:0;max-height:0;mso-hide:all;">Tu presentación de Home del Valle está lista — adjunta en PDF y disponible en línea.</div>

<table role="presentation" width="100%" style="background-color:#F6F7F9;mso-padding-alt:0">
<tr><td align="center" style="padding:28px 0">

  <table role="presentation" class="container" width="600" style="background-color:#ffffff;border:1px solid #EAEEF3;border-radius:16px;box-shadow:0 4px 12px rgba(0,0,0,.10)">
  <tr><td style="padding:0">

    <!-- HEADER -->
    <table role="presentation" width="100%">
    <tr><td style="border-bottom:1px solid #EAEEF3;padding:18px 24px">
      <table role="presentation" width="100%">
      <tr>
        <td align="left" style="vertical-align:middle">
          <div style="display:inline-flex;align-items:center;gap:10px">
            <div style="width:32px;height:32px;border-radius:50%;background-color:#16314D;color:#fff;font-size:14px;font-weight:600;line-height:32px;text-align:center">HV</div>
            <div>
              <p style="margin:0;font-size:14px;font-weight:600;color:#16314D">Home del Valle</p>
              <p style="margin:0;font-size:12px;color:#8B95A8">homedelvalle.mx</p>
            </div>
          </div>
        </td>
        <td align="right" style="vertical-align:middle">
          <div style="display:inline-block;background-color:#EBF4FA;color:#2E84C4;padding:6px 12px;border-radius:999px;font-size:12px;font-weight:600">Presentación Inicial</div>
        </td>
      </tr>
      </table>
    </td></tr>
    </table>

    <!-- BODY -->
    <table role="presentation" width="100%">
    <tr><td class="inner-container" style="padding:28px 28px 8px">

      <!-- Título -->
      <h1 style="margin:0 0 16px;font-size:22px;font-weight:700;color:#16314D;line-height:1.3">Hola, {{NombrePropietario}} 👋</h1>

      <!-- Intro -->
      <p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:#4B5563">
        Gracias por tomarte el tiempo hoy. Como te comenté, te comparto la presentación de <strong style="color:#16314D">Home del Valle</strong> para tu inmueble en <strong>{{NombreInmueble}}</strong>.
      </p>
      <p style="margin:0 0 24px;font-size:15px;line-height:1.6;color:#4B5563">
        La encontrarás adjunta en PDF y también puedes verla en línea:
      </p>

      <!-- CTA principal -->
      <table role="presentation" style="margin:0 0 28px;border-collapse:collapse">
      <tr><td align="center" style="border-radius:12px;background-color:#16314D">
        <a href="{{PresentationUrl}}" style="display:inline-block;padding:13px 28px;color:#fff;text-decoration:none;font-size:15px;font-weight:600;border-radius:12px">
          📋 Ver presentación en línea
        </a>
      </td></tr>
      </table>

      <!-- Qué sigue -->
      <p style="margin:0 0 10px;font-size:12px;font-weight:700;color:#16314D;text-transform:uppercase;letter-spacing:0.05em">Qué sigue</p>

      <!-- Paso 1 -->
      <table role="presentation" width="100%" style="margin-bottom:10px">
      <tr><td style="background-color:#F9FAFB;border:1px solid #EAEEF3;border-radius:12px;padding:14px">
        <table role="presentation" width="100%"><tr>
          <td style="width:32px;vertical-align:top;padding-right:12px;font-size:20px;line-height:1.4">📞</td>
          <td style="vertical-align:top">
            <p style="margin:0 0 3px;font-size:14px;font-weight:600;color:#16314D">Revisamos tu presentación juntos</p>
            <p style="margin:0;font-size:13px;color:#8B95A8;line-height:1.5">Si tienes preguntas sobre los servicios, el proceso o los costos, estoy disponible para resolverlas.</p>
          </td>
        </tr></table>
      </td></tr>
      </table>

      <!-- Paso 2 -->
      <table role="presentation" width="100%" style="margin-bottom:10px">
      <tr><td style="background-color:#F9FAFB;border:1px solid #EAEEF3;border-radius:12px;padding:14px">
        <table role="presentation" width="100%"><tr>
          <td style="width:32px;vertical-align:top;padding-right:12px;font-size:20px;line-height:1.4">🏠</td>
          <td style="vertical-align:top">
            <p style="margin:0 0 3px;font-size:14px;font-weight:600;color:#16314D">Visita técnica a tu inmueble</p>
            <p style="margin:0;font-size:13px;color:#8B95A8;line-height:1.5">Agendamos la visita para conocer tu propiedad y preparar tu opinión de valor personalizada.</p>
          </td>
        </tr></table>
      </td></tr>
      </table>

      <!-- Paso 3 -->
      <table role="presentation" width="100%" style="margin-bottom:24px">
      <tr><td style="background-color:#F9FAFB;border:1px solid #EAEEF3;border-radius:12px;padding:14px">
        <table role="presentation" width="100%"><tr>
          <td style="width:32px;vertical-align:top;padding-right:12px;font-size:20px;line-height:1.4">📊</td>
          <td style="vertical-align:top">
            <p style="margin:0 0 3px;font-size:14px;font-weight:600;color:#16314D">Estrategia de comercialización</p>
            <p style="margin:0;font-size:13px;color:#8B95A8;line-height:1.5">Fotografía profesional, marketing digital y acceso a nuestra red activa de compradores en Benito Juárez.</p>
          </td>
        </tr></table>
      </td></tr>
      </table>

      <!-- Nota -->
      <p style="margin:0 0 24px;font-size:12px;color:#8B95A8;line-height:1.5">
        Pocos inmuebles. Más control. Mejores resultados. Sin anticipos — comisión solo al cierre.
      </p>

      <!-- Firma del agente -->
      <table role="presentation" width="100%" style="margin-bottom:0">
      <tr><td style="border-top:1px solid #EAEEF3;padding-top:20px">
        <p style="margin:0 0 3px;font-size:15px;font-weight:600;color:#16314D">{{NombreAgente}}</p>
        <p style="margin:0 0 2px;font-size:13px;color:#8B95A8">Asesor · Home del Valle Bienes Raíces</p>
        <p style="margin:0;font-size:13px;color:#8B95A8">Heriberto Frías 903-A · Col. del Valle · CDMX</p>
      </td></tr>
      </table>

    </td></tr>
    </table>

    <!-- FOOTER -->
    <table role="presentation" width="100%">
    <tr><td style="border-top:1px solid #EAEEF3;padding:18px;text-align:center">
      <div style="display:inline-block;width:28px;height:28px;border-radius:50%;background-color:#2E84C4;color:#fff;font-size:12px;font-weight:600;line-height:28px;text-align:center">HV</div>
      <p style="margin:8px 0 4px;font-size:14px;font-weight:600;color:#16314D">El equipo Home del Valle</p>
      <p style="margin:0;font-size:12px;color:#8B95A8;line-height:1.5">Pocos inmuebles · Más control · Mejores resultados</p>
      <p style="margin:10px 0 0;font-size:12px;color:#B5BFCC">
        <a href="https://homedelvalle.mx" style="color:#2E84C4">homedelvalle.mx</a> ·
        <a href="mailto:contacto@homedelvalle.mx" style="color:#2E84C4">contacto@homedelvalle.mx</a>
      </p>
    </td></tr>
    </table>

  </td></tr>
  </table>

</td></tr>
</table>

<!-- Tracking pixel -->
<img src="{{TrackingPixel}}" width="1" height="1" alt="" style="display:block;width:1px;height:1px;border:0">
</body>
</html>']);

        DB::table("email_templates")
            ->where("name", "captacion_declined_friendly")
            ->update(["body" => '<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Home del Valle — Mensaje importante</title>
<style type="text/css">
*{margin:0;padding:0;border:0}
html,body{margin:0;padding:0;font-family:"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;color:#4B5563;background-color:#F6F7F9}
table{border-collapse:collapse;mso-table-lspace:0;mso-table-rspace:0}
img{outline:none;text-decoration:none;border:none}
a{color:#2E84C4;text-decoration:none}
@media only screen and (max-width:520px){.container{width:100%!important}.inner-container{padding:16px!important}}
</style>
</head>
<body>
<div style="display:none;font-size:0;line-height:0;max-height:0;mso-hide:all;">Gracias por tu confianza en Home del Valle — un mensaje importante de tu asesor.</div>

<table role="presentation" width="100%" style="background-color:#F6F7F9;mso-padding-alt:0">
<tr><td align="center" style="padding:28px 0">

  <table role="presentation" class="container" width="600" style="background-color:#ffffff;border:1px solid #EAEEF3;border-radius:16px;box-shadow:0 4px 12px rgba(0,0,0,.10)">
  <tr><td style="padding:0">

    <!-- HEADER -->
    <table role="presentation" width="100%">
    <tr><td style="border-bottom:1px solid #EAEEF3;padding:18px 24px">
      <table role="presentation" width="100%">
      <tr>
        <td align="left" style="vertical-align:middle">
          <div style="display:inline-flex;align-items:center;gap:10px">
            <div style="width:32px;height:32px;border-radius:50%;background-color:#16314D;color:#fff;font-size:14px;font-weight:600;line-height:32px;text-align:center">HV</div>
            <div>
              <p style="margin:0;font-size:14px;font-weight:600;color:#16314D">Home del Valle</p>
              <p style="margin:0;font-size:12px;color:#8B95A8">homedelvalle.mx</p>
            </div>
          </div>
        </td>
        <td align="right" style="vertical-align:middle">
          <div style="display:inline-block;background-color:#F6F7F9;color:#8B95A8;padding:6px 12px;border-radius:999px;font-size:12px;font-weight:600">Mensaje de tu asesor</div>
        </td>
      </tr>
      </table>
    </td></tr>
    </table>

    <!-- BODY -->
    <table role="presentation" width="100%">
    <tr><td class="inner-container" style="padding:28px 28px 8px">

      <!-- Título -->
      <h1 style="margin:0 0 16px;font-size:22px;font-weight:700;color:#16314D;line-height:1.3">Gracias por tu confianza, {{NombrePropietario}}</h1>

      <!-- Mensaje principal -->
      <p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#4B5563">
        Nos da mucho gusto que hayas tomado el tiempo de hablar con nosotros y considerado a <strong style="color:#16314D">Home del Valle</strong> para la comercialización de tu inmueble en <strong>{{NombreInmueble}}</strong>.
      </p>
      <p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#4B5563">
        Después de analizarlo con nuestro equipo, hemos concluido que en este momento no somos el mejor socio para este caso. Nuestra forma de trabajar — con pocos inmuebles y atención muy personalizada — nos lleva a ser muy selectivos para garantizar que podemos comprometer el tiempo y los recursos que cada inmueble merece.
      </p>
      <p style="margin:0 0 24px;font-size:15px;line-height:1.7;color:#4B5563">
        Esto no tiene nada que ver con tu inmueble. Es simplemente una cuestión de enfoque y de hacer bien lo que hacemos.
      </p>

      <!-- Card de cierre cálido -->
      <table role="presentation" width="100%" style="margin-bottom:24px">
      <tr><td style="background-color:#EBF4FA;border:1px solid #EAEEF3;border-radius:12px;padding:18px">
        <p style="margin:0 0 10px;font-size:14px;font-weight:600;color:#16314D">🤝 Quedamos disponibles para el futuro</p>
        <p style="margin:0;font-size:14px;line-height:1.6;color:#4B5563">Si en algún momento las condiciones cambian, o si tienes a alguien conocido que necesite asesoría inmobiliaria en Benito Juárez, estaremos aquí con gusto.</p>
      </td></tr>
      </table>

      <!-- CTA secundario: ver precios de mercado -->
      <p style="margin:0 0 16px;font-size:14px;line-height:1.6;color:#4B5563">Mientras tanto, puedes consultar los precios de mercado actuales de tu zona en nuestro Observatorio de Precios:</p>

      <table role="presentation" style="margin:0 0 28px;border-collapse:collapse">
      <tr><td align="center" style="border-radius:12px;background-color:#ffffff;border:1px solid #16314D">
        <a href="https://homedelvalle.mx/mercado" style="display:inline-block;padding:12px 24px;color:#16314D;text-decoration:none;font-size:14px;font-weight:600;border-radius:12px">
          Ver precios de mercado en BJ →
        </a>
      </td></tr>
      </table>

      <!-- Nota final -->
      <p style="margin:0 0 24px;font-size:14px;color:#8B95A8;line-height:1.5">
        Muchas gracias por tu confianza. Te deseamos mucho éxito en el proceso.
      </p>

      <!-- Firma del agente -->
      <table role="presentation" width="100%" style="margin-bottom:0">
      <tr><td style="border-top:1px solid #EAEEF3;padding-top:20px">
        <p style="margin:0 0 3px;font-size:15px;font-weight:600;color:#16314D">{{NombreAgente}}</p>
        <p style="margin:0 0 2px;font-size:13px;color:#8B95A8">Asesor · Home del Valle Bienes Raíces</p>
        <p style="margin:0;font-size:13px;color:#8B95A8">Heriberto Frías 903-A · Col. del Valle · CDMX</p>
      </td></tr>
      </table>

    </td></tr>
    </table>

    <!-- FOOTER -->
    <table role="presentation" width="100%">
    <tr><td style="border-top:1px solid #EAEEF3;padding:18px;text-align:center">
      <div style="display:inline-block;width:28px;height:28px;border-radius:50%;background-color:#2E84C4;color:#fff;font-size:12px;font-weight:600;line-height:28px;text-align:center">HV</div>
      <p style="margin:8px 0 4px;font-size:14px;font-weight:600;color:#16314D">El equipo Home del Valle</p>
      <p style="margin:0;font-size:12px;color:#8B95A8;line-height:1.5">Pocos inmuebles · Más control · Mejores resultados</p>
      <p style="margin:10px 0 0;font-size:12px;color:#B5BFCC">
        <a href="https://homedelvalle.mx" style="color:#2E84C4">homedelvalle.mx</a> ·
        <a href="mailto:contacto@homedelvalle.mx" style="color:#2E84C4">contacto@homedelvalle.mx</a>
      </p>
    </td></tr>
    </table>

  </td></tr>
  </table>

</td></tr>
</table>
</body>
</html>']);
    }

    public function down(): void {} // irreversible design update
};
