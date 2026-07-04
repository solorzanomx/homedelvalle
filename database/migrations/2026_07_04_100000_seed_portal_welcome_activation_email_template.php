<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * ClientPortalService::sendWelcomeInvitation() manda el flujo de "link de
 * activación" (el cliente elige su propia contraseña en /activar/{token},
 * distinto del flujo "aquí está tu contraseña" de EmailService::
 * sendPortalWelcome() — ambos son reales y coexisten a propósito). El
 * template 'portal_welcome' que ese método busca nunca se sembró — bug
 * real encontrado 2026-07-04, el correo de bienvenida nunca se enviaba
 * (además de un método inexistente llamado, ya corregido aparte).
 */
return new class extends Migration
{
    public function up(): void
    {
        $body = <<<'HTML'
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Activa tu portal — {{Sitio}}</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;color:#1e293b;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f1f5f9;">
<tr><td align="center" style="padding:36px 16px 48px;">

  <table width="600" cellpadding="0" cellspacing="0" border="0"
    style="max-width:600px;width:100%;background:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,.10);">

    <tr>
      <td style="background:linear-gradient(135deg,#0E304B,#1e4a73);padding:44px 40px 36px;text-align:center;">
        <img src="{{LogoURL}}" alt="{{Sitio}}" height="50"
          style="max-height:50px;max-width:210px;display:block;margin:0 auto 20px;object-fit:contain;"
          onerror="this.style.display='none'">
        <div style="font-size:52px;line-height:1;margin-bottom:12px;">👋</div>
        <h1 style="margin:0 0 10px;color:#ffffff;font-size:28px;font-weight:800;letter-spacing:-.4px;line-height:1.2;">
          ¡Bienvenido, {{Nombre}}!
        </h1>
        <p style="margin:0;color:rgba(255,255,255,.88);font-size:15px;line-height:1.5;">Activa tu portal para dar seguimiento a tu proceso</p>
      </td>
    </tr>

    <tr>
      <td style="padding:38px 40px 28px;">
        <p style="margin:0 0 16px;font-size:15px;line-height:1.75;color:#475569;">
          Tu portal de cliente en <strong>{{Sitio}}</strong> está casi listo. Solo falta que actives tu cuenta y elijas tu propia contraseña.
        </p>
        <p style="margin:0;font-size:15px;line-height:1.75;color:#475569;">
          Desde ahí podrás consultar el avance de tu proceso, tus documentos y toda la información relacionada, en cualquier momento.
        </p>
      </td>
    </tr>

    <tr>
      <td style="padding:0 40px 44px;text-align:center;">
        <a href="{{ActivationLink}}"
          style="display:inline-block;background:linear-gradient(135deg,#0E304B,#1e4a73);color:#ffffff;font-size:16px;font-weight:700;text-decoration:none;padding:16px 52px;border-radius:50px;letter-spacing:.2px;box-shadow:0 6px 20px rgba(0,0,0,.15);">
          Activar mi portal &rarr;
        </a>
        <p style="margin:16px 0 0;font-size:12px;color:#94a3b8;">
          Este enlace es válido por 7 días. Si no esperabas este correo, ignóralo con confianza.
        </p>
      </td>
    </tr>

    <tr>
      <td style="background:#f8fafc;border-top:1px solid #e2e8f0;padding:24px 40px;text-align:center;">
        <p style="margin:0 0 4px;font-size:13px;font-weight:700;color:#475569;">{{Sitio}}</p>
        <p style="margin:0;font-size:12px;color:#94a3b8;line-height:1.6;">
          Correo enviado el {{Fecha}} &middot; ¿Tienes dudas? Responde este correo y te ayudamos de inmediato.
        </p>
      </td>
    </tr>

  </table>
</td></tr>
</table>

</body>
</html>
HTML;

        DB::table('email_templates')->updateOrInsert(
            ['name' => 'portal_welcome'],
            [
                'subject'    => '👋 Activa tu portal — {{Sitio}}',
                'body'       => $body,
                'body_text'  => "Hola {{Nombre}}, activa tu portal aquí: {{ActivationLink}}",
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('email_templates')->where('name', 'portal_welcome')->delete();
    }
};
