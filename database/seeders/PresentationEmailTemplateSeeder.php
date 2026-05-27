<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class PresentationEmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // Template: Envío de presentación inicial al propietario
        EmailTemplate::updateOrCreate(
            ['name' => 'presentation_initial'],
            [
                'subject' => 'Tu presentación de Home del Valle — {{NombreInmueble}}',
                'body'    => <<<'HTML'
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;">
<div style="max-width:600px;margin:30px auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">

  <!-- Header navy HDV -->
  <div style="background:#1e1b4b;padding:32px 40px;text-align:center;">
    <div style="font-size:11px;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.5);margin-bottom:8px;">Bienes Raíces</div>
    <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:700;">Home del Valle</h1>
    <p style="margin:8px 0 0;color:rgba(255,255,255,.7);font-size:13px;">Pocos inmuebles. Más control. Mejores resultados.</p>
  </div>

  <!-- Body -->
  <div style="padding:36px 40px;">
    <h2 style="margin:0 0 12px;color:#1e1b4b;font-size:20px;font-weight:600;">
      Hola {{NombrePropietario}},
    </h2>
    <p style="color:#475569;font-size:15px;line-height:1.7;margin:0 0 20px;">
      Gracias por la llamada de hoy. Como te comenté, te envío la presentación
      inicial de <strong style="color:#1e1b4b;">Home del Valle</strong> para tu
      inmueble en <strong>{{NombreInmueble}}</strong>.
    </p>

    <!-- CTA principal -->
    <div style="text-align:center;margin:28px 0;">
      <a href="{{PresentationUrl}}"
         style="display:inline-block;background:#1e1b4b;color:#ffffff;text-decoration:none;
                padding:14px 36px;border-radius:8px;font-size:15px;font-weight:600;letter-spacing:.3px;">
        Ver presentación en línea
      </a>
    </div>

    <p style="color:#64748b;font-size:14px;line-height:1.6;margin:0 0 16px;">
      También te la adjunto en PDF para que la tengas a la mano.
    </p>

    <!-- Separador -->
    <div style="border-top:1px solid #e2e8f0;margin:24px 0;"></div>

    <p style="color:#475569;font-size:14px;line-height:1.6;margin:0 0 8px;">
      Si tienes dudas o quieres que agendemos la visita técnica,
      respóndeme este correo o escríbeme directamente por WhatsApp.
    </p>
    <p style="color:#475569;font-size:14px;line-height:1.6;margin:0;">
      Quedo atento a tus comentarios.
    </p>
  </div>

  <!-- Agent signature -->
  <div style="background:#f8fafc;padding:24px 40px;border-top:1px solid #e2e8f0;">
    <p style="margin:0 0 4px;color:#1e1b4b;font-size:15px;font-weight:600;">{{NombreAgente}}</p>
    <p style="margin:0 0 2px;color:#64748b;font-size:13px;">Home del Valle Bienes Raíces</p>
    <p style="margin:0 0 2px;color:#64748b;font-size:13px;">Heriberto Frías 903-A · Col. del Valle · CDMX</p>
    <p style="margin:8px 0 0;color:#94a3b8;font-size:11px;font-style:italic;">
      Pocos inmuebles. Más control. Mejores resultados.
    </p>
  </div>

</div>

<!-- Tracking pixel (1x1, invisible) -->
<img src="{{TrackingPixel}}" width="1" height="1" alt="" style="display:block;width:1px;height:1px;border:0;"/>

</body>
</html>
HTML
            ]
        );

        // Template: Declinar caso amistosamente
        EmailTemplate::updateOrCreate(
            ['name' => 'captacion_declined_friendly'],
            [
                'subject' => 'Gracias por tu confianza, {{NombrePropietario}}',
                'body'    => <<<'HTML'
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;">
<div style="max-width:600px;margin:30px auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">

  <!-- Header -->
  <div style="background:#1e1b4b;padding:32px 40px;text-align:center;">
    <div style="font-size:11px;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.5);margin-bottom:8px;">Bienes Raíces</div>
    <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:700;">Home del Valle</h1>
    <p style="margin:8px 0 0;color:rgba(255,255,255,.7);font-size:13px;">Pocos inmuebles. Más control. Mejores resultados.</p>
  </div>

  <!-- Body -->
  <div style="padding:36px 40px;">
    <h2 style="margin:0 0 12px;color:#1e1b4b;font-size:20px;font-weight:600;">
      Estimado {{NombrePropietario}},
    </h2>
    <p style="color:#475569;font-size:15px;line-height:1.7;margin:0 0 20px;">
      Gracias por tomarte el tiempo de hablar con nosotros y por considerar a
      <strong style="color:#1e1b4b;">Home del Valle</strong> para la comercialización de
      tu inmueble en <strong>{{NombreInmueble}}</strong>.
    </p>
    <p style="color:#475569;font-size:15px;line-height:1.7;margin:0 0 20px;">
      Después de analizar el caso con nuestro equipo, hemos llegado a la conclusión de que
      en este momento no somos el socio correcto para acompañarte en esta operación.
      Nuestra forma de trabajar — con pocos inmuebles y atención muy personalizada —
      nos lleva a ser selectivos para asegurarnos de que podemos comprometer el tiempo
      y los recursos que cada caso merece.
    </p>
    <p style="color:#475569;font-size:15px;line-height:1.7;margin:0 0 20px;">
      Esta decisión no tiene nada que ver con la calidad de tu inmueble. Es simplemente
      una cuestión de enfoque y de hacer bien lo que hacemos.
    </p>
    <p style="color:#475569;font-size:15px;line-height:1.7;margin:0;">
      Te deseamos mucho éxito en el proceso. Si en el futuro las circunstancias cambian,
      estaremos con gusto disponibles para una nueva conversación.
    </p>

    <div style="border-top:1px solid #e2e8f0;margin:28px 0;"></div>

    <p style="color:#475569;font-size:14px;margin:0;">
      Muchas gracias por tu confianza.
    </p>
  </div>

  <!-- Agent signature -->
  <div style="background:#f8fafc;padding:24px 40px;border-top:1px solid #e2e8f0;">
    <p style="margin:0 0 4px;color:#1e1b4b;font-size:15px;font-weight:600;">{{NombreAgente}}</p>
    <p style="margin:0 0 2px;color:#64748b;font-size:13px;">Home del Valle Bienes Raíces</p>
    <p style="margin:0 0 2px;color:#64748b;font-size:13px;">Heriberto Frías 903-A · Col. del Valle · CDMX</p>
    <p style="margin:8px 0 0;color:#94a3b8;font-size:11px;font-style:italic;">
      Pocos inmuebles. Más control. Mejores resultados.
    </p>
  </div>

</div>
</body>
</html>
HTML
            ]
        );
    }
}
