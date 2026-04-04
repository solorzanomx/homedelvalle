<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        EmailTemplate::updateOrCreate(
            ['name' => 'BienvenidaUsuario'],
            [
                'subject' => 'Bienvenido a {{Sitio}}, {{Nombre}}',
                'body' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;">
<div style="max-width:600px;margin:30px auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
    <!-- Header -->
    <div style="background:linear-gradient(135deg,#3B82C4,#1E3A5F);padding:32px 40px;text-align:center;">
        <h1 style="margin:0;color:#ffffff;font-size:24px;font-weight:700;">{{Sitio}}</h1>
        <p style="margin:8px 0 0;color:rgba(255,255,255,0.85);font-size:14px;">Plataforma de Gestion Inmobiliaria</p>
    </div>

    <!-- Body -->
    <div style="padding:32px 40px;">
        <h2 style="margin:0 0 8px;color:#1e293b;font-size:20px;">Hola, {{Nombre}}</h2>
        <p style="color:#64748b;font-size:15px;line-height:1.6;">
            Tu cuenta ha sido creada exitosamente. A continuacion encontraras tus credenciales de acceso:
        </p>

        <!-- Credentials Box -->
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:20px;margin:20px 0;">
            <table style="width:100%;border-collapse:collapse;">
                <tr>
                    <td style="padding:6px 0;color:#64748b;font-size:13px;width:100px;">Email:</td>
                    <td style="padding:6px 0;color:#1e293b;font-size:14px;font-weight:600;">{{Email}}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:#64748b;font-size:13px;">Contrasena:</td>
                    <td style="padding:6px 0;color:#1e293b;font-size:14px;font-weight:600;">{{Password}}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:#64748b;font-size:13px;">Rol:</td>
                    <td style="padding:6px 0;color:#1e293b;font-size:14px;font-weight:600;text-transform:capitalize;">{{Rol}}</td>
                </tr>
            </table>
        </div>

        <p style="color:#ef4444;font-size:13px;margin-bottom:24px;">
            <strong>Importante:</strong> Te recomendamos cambiar tu contrasena despues de tu primer inicio de sesion.
        </p>

        <!-- CTA Button -->
        <div style="text-align:center;margin:24px 0;">
            <a href="#" style="display:inline-block;background:linear-gradient(135deg,#3B82C4,#1E3A5F);color:#ffffff;text-decoration:none;padding:12px 32px;border-radius:8px;font-size:14px;font-weight:600;">
                Iniciar Sesion
            </a>
        </div>
    </div>

    <!-- Footer -->
    <div style="background:#f8fafc;padding:20px 40px;border-top:1px solid #e2e8f0;text-align:center;">
        <p style="margin:0;color:#94a3b8;font-size:12px;">
            Este correo fue enviado automaticamente el {{Fecha}}.
            <br>{{Sitio}} - Todos los derechos reservados.
        </p>
    </div>
</div>
</body>
</html>
HTML
            ]
        );

        // Template: Recuperar Contrasena
        EmailTemplate::updateOrCreate(
            ['name' => 'RecuperarPassword'],
            [
                'subject' => 'Recupera tu contrasena - {{Sitio}}',
                'body' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;">
<div style="max-width:600px;margin:30px auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
    <!-- Header -->
    <div style="background:linear-gradient(135deg,#3B82C4,#1E3A5F);padding:32px 40px;text-align:center;">
        <h1 style="margin:0;color:#ffffff;font-size:24px;font-weight:700;">{{Sitio}}</h1>
        <p style="margin:8px 0 0;color:rgba(255,255,255,0.85);font-size:14px;">Recuperacion de Contrasena</p>
    </div>

    <!-- Body -->
    <div style="padding:32px 40px;">
        <h2 style="margin:0 0 8px;color:#1e293b;font-size:20px;">Hola, {{Nombre}}</h2>
        <p style="color:#64748b;font-size:15px;line-height:1.6;">
            Recibimos una solicitud para restablecer la contrasena de tu cuenta. Haz clic en el siguiente boton para crear una nueva contrasena:
        </p>

        <!-- CTA Button -->
        <div style="text-align:center;margin:28px 0;">
            <a href="{{EnlaceReset}}" style="display:inline-block;background:linear-gradient(135deg,#3B82C4,#1E3A5F);color:#ffffff;text-decoration:none;padding:14px 36px;border-radius:8px;font-size:15px;font-weight:600;">
                Restablecer Contrasena
            </a>
        </div>

        <!-- Expiration notice -->
        <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:8px;padding:14px 18px;margin:20px 0;">
            <p style="margin:0;color:#92400e;font-size:13px;">
                <strong>Este enlace expirara en {{Expiracion}} minutos.</strong> Si no lo usas a tiempo, solicita uno nuevo.
            </p>
        </div>

        <p style="color:#64748b;font-size:14px;line-height:1.6;">
            Si no solicitaste este cambio, puedes ignorar este correo de manera segura. Tu contrasena no sera modificada.
        </p>

        <!-- Fallback link -->
        <p style="color:#94a3b8;font-size:12px;margin-top:20px;word-break:break-all;">
            Si el boton no funciona, copia y pega este enlace en tu navegador:<br>
            <a href="{{EnlaceReset}}" style="color:#3B82C4;">{{EnlaceReset}}</a>
        </p>
    </div>

    <!-- Footer -->
    <div style="background:#f8fafc;padding:20px 40px;border-top:1px solid #e2e8f0;text-align:center;">
        <p style="margin:0;color:#94a3b8;font-size:12px;">
            Este correo fue enviado automaticamente el {{Fecha}}.
            <br>{{Sitio}} - Todos los derechos reservados.
        </p>
    </div>
</div>
</body>
</html>
HTML
            ]
        );

        // Template: Contrasena Actualizada
        EmailTemplate::updateOrCreate(
            ['name' => 'PasswordCambiado'],
            [
                'subject' => 'Tu contrasena fue actualizada - {{Sitio}}',
                'body' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;">
<div style="max-width:600px;margin:30px auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
    <!-- Header -->
    <div style="background:linear-gradient(135deg,#3B82C4,#1E3A5F);padding:32px 40px;text-align:center;">
        <h1 style="margin:0;color:#ffffff;font-size:24px;font-weight:700;">{{Sitio}}</h1>
        <p style="margin:8px 0 0;color:rgba(255,255,255,0.85);font-size:14px;">Notificacion de Seguridad</p>
    </div>

    <!-- Body -->
    <div style="padding:32px 40px;">
        <h2 style="margin:0 0 8px;color:#1e293b;font-size:20px;">Hola, {{Nombre}}</h2>
        <p style="color:#64748b;font-size:15px;line-height:1.6;">
            Tu contrasena ha sido actualizada exitosamente el <strong>{{Fecha}}</strong>.
        </p>

        <!-- Confirmation Box -->
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:8px;padding:14px 18px;margin:20px 0;">
            <p style="margin:0;color:#065f46;font-size:13px;">
                &#10003; Cambio de contrasena confirmado.
            </p>
        </div>

        <!-- Warning -->
        <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:14px 18px;margin:20px 0;">
            <p style="margin:0;color:#991b1b;font-size:13px;">
                <strong>Si tu no realizaste este cambio,</strong> contacta al administrador del sistema inmediatamente para proteger tu cuenta.
            </p>
        </div>
    </div>

    <!-- Footer -->
    <div style="background:#f8fafc;padding:20px 40px;border-top:1px solid #e2e8f0;text-align:center;">
        <p style="margin:0;color:#94a3b8;font-size:12px;">
            Este correo fue enviado automaticamente el {{Fecha}}.
            <br>{{Sitio}} - Todos los derechos reservados.
        </p>
    </div>
</div>
</body>
</html>
HTML
            ]
        );

        // Template: Bienvenida Portal de Cliente
        EmailTemplate::updateOrCreate(
            ['name' => 'BienvenidaPortal'],
            [
                'subject' => 'Bienvenido a tu Portal de Cliente - {{Sitio}}',
                'body' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;">
<div style="max-width:600px;margin:30px auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
    <!-- Header -->
    <div style="background:linear-gradient(135deg,#3B82C4,#1E3A5F);padding:32px 40px;text-align:center;">
        <h1 style="margin:0;color:#ffffff;font-size:24px;font-weight:700;">{{Sitio}}</h1>
        <p style="margin:8px 0 0;color:rgba(255,255,255,0.85);font-size:14px;">Portal de Cliente</p>
    </div>

    <!-- Body -->
    <div style="padding:32px 40px;">
        <h2 style="margin:0 0 8px;color:#1e293b;font-size:20px;">Hola, {{Nombre}}</h2>
        <p style="color:#64748b;font-size:15px;line-height:1.6;">
            Se ha creado tu acceso al <strong>Portal de Cliente</strong>. Desde aqui podras consultar el estado de tus procesos de renta, revisar documentos, ver contratos y dar seguimiento a tu propiedad.
        </p>

        <!-- Credentials Box -->
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:20px;margin:20px 0;">
            <table style="width:100%;border-collapse:collapse;">
                <tr>
                    <td style="padding:6px 0;color:#64748b;font-size:13px;width:120px;">Email:</td>
                    <td style="padding:6px 0;color:#1e293b;font-size:14px;font-weight:600;">{{Email}}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:#64748b;font-size:13px;">Contrasena:</td>
                    <td style="padding:6px 0;color:#1e293b;font-size:14px;font-weight:600;font-family:monospace;letter-spacing:1px;">{{Password}}</td>
                </tr>
            </table>
        </div>

        <p style="color:#ef4444;font-size:13px;margin-bottom:24px;">
            <strong>Importante:</strong> Te recomendamos cambiar tu contrasena desde tu portal una vez que inicies sesion.
        </p>

        <!-- CTA Button -->
        <div style="text-align:center;margin:24px 0;">
            <a href="{{PortalURL}}" style="display:inline-block;background:linear-gradient(135deg,#3B82C4,#1E3A5F);color:#ffffff;text-decoration:none;padding:12px 32px;border-radius:8px;font-size:14px;font-weight:600;">
                Acceder a Mi Portal
            </a>
        </div>

        <!-- What you can do -->
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:16px 20px;margin-top:20px;">
            <p style="margin:0 0 8px;color:#166534;font-size:14px;font-weight:600;">Desde tu portal puedes:</p>
            <ul style="margin:0;padding-left:20px;color:#15803d;font-size:13px;line-height:1.8;">
                <li>Ver el estado de tus procesos de renta</li>
                <li>Consultar y descargar documentos</li>
                <li>Revisar contratos</li>
                <li>Subir documentos solicitados</li>
                <li>Cambiar tu contrasena</li>
            </ul>
        </div>
    </div>

    <!-- Footer -->
    <div style="background:#f8fafc;padding:20px 40px;border-top:1px solid #e2e8f0;text-align:center;">
        <p style="margin:0;color:#94a3b8;font-size:12px;">
            Este correo fue enviado automaticamente el {{Fecha}}.
            <br>{{Sitio}} - Todos los derechos reservados.
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
