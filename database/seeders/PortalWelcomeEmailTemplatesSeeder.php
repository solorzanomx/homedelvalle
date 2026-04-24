<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class PortalWelcomeEmailTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        // ─── VENTA ──────────────────────────────────────────────────────────────
        $ventaContent = <<<'CONTENT'
<h2 style="margin:0 0 14px;font-size:21px;font-weight:800;color:#1e293b;">Hola, {{Nombre}} 👋</h2>
<p style="margin:0 0 16px;font-size:15px;line-height:1.75;color:#475569;">
  Estamos muy contentos de acompañarte en este proceso. Tu inmueble merece la mejor estrategia de venta posible, y eso es exactamente lo que te ofrecemos.
</p>
<p style="margin:0 0 24px;font-size:15px;line-height:1.75;color:#475569;">
  Desde tu portal podrás dar seguimiento a cada etapa: documentación, valuación, acuerdo de precio y firma del contrato de exclusiva. Nuestro equipo estará contigo en cada paso.
</p>

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
  <tr>
    <td style="padding:11px 0;border-bottom:1px solid #f1f5f9;">
      <span style="display:inline-block;width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;font-size:13px;font-weight:700;text-align:center;line-height:30px;margin-right:12px;vertical-align:middle;">1</span>
      <span style="font-size:14px;color:#334155;vertical-align:middle;font-weight:500;">Sube tus documentos de identidad e inmueble</span>
    </td>
  </tr>
  <tr>
    <td style="padding:11px 0;border-bottom:1px solid #f1f5f9;">
      <span style="display:inline-block;width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;font-size:13px;font-weight:700;text-align:center;line-height:30px;margin-right:12px;vertical-align:middle;">2</span>
      <span style="font-size:14px;color:#334155;vertical-align:middle;font-weight:500;">Recibe la opinión de valor de tu propiedad</span>
    </td>
  </tr>
  <tr>
    <td style="padding:11px 0;border-bottom:1px solid #f1f5f9;">
      <span style="display:inline-block;width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;font-size:13px;font-weight:700;text-align:center;line-height:30px;margin-right:12px;vertical-align:middle;">3</span>
      <span style="font-size:14px;color:#334155;vertical-align:middle;font-weight:500;">Acuerda el precio de venta con tu asesor</span>
    </td>
  </tr>
  <tr>
    <td style="padding:11px 0;">
      <span style="display:inline-block;width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;font-size:13px;font-weight:700;text-align:center;line-height:30px;margin-right:12px;vertical-align:middle;">4</span>
      <span style="font-size:14px;color:#334155;vertical-align:middle;font-weight:500;">Firma el contrato de exclusiva y ¡empezamos!</span>
    </td>
  </tr>
</table>

<p style="margin:0;font-size:13px;color:#94a3b8;font-style:italic;border-left:3px solid #667eea;padding-left:14px;">
  "El mejor momento para vender es cuando cuentas con el equipo correcto a tu lado." — Equipo {{Sitio}}
</p>
CONTENT;

        // ─── RENTA ──────────────────────────────────────────────────────────────
        $rentaContent = <<<'CONTENT'
<h2 style="margin:0 0 14px;font-size:21px;font-weight:800;color:#1e293b;">Hola, {{Nombre}} 👋</h2>
<p style="margin:0 0 16px;font-size:15px;line-height:1.75;color:#475569;">
  Tu acceso al portal está listo. Desde aquí podrás consultar el estado de tu proceso de renta, revisar y descargar tus contratos, y estar siempre al tanto de cada novedad.
</p>
<p style="margin:0 0 24px;font-size:15px;line-height:1.75;color:#475569;">
  Nos comprometemos a que cada proceso sea transparente, ágil y sin sorpresas. Tu tranquilidad es nuestra prioridad.
</p>

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
  <tr>
    <td width="48%" style="padding:16px;background:#f0fdf4;border-radius:12px;text-align:center;vertical-align:top;">
      <div style="font-size:30px;margin-bottom:8px;">📋</div>
      <div style="font-size:13px;font-weight:700;color:#166534;">Contratos en línea</div>
      <div style="font-size:12px;color:#86efac;margin-top:3px;">Disponibles 24/7</div>
    </td>
    <td width="4%"></td>
    <td width="48%" style="padding:16px;background:#eff6ff;border-radius:12px;text-align:center;vertical-align:top;">
      <div style="font-size:30px;margin-bottom:8px;">🔔</div>
      <div style="font-size:13px;font-weight:700;color:#1e40af;">Sin pendientes</div>
      <div style="font-size:12px;color:#93c5fd;margin-top:3px;">Actualizaciones al instante</div>
    </td>
  </tr>
</table>

<p style="margin:0;font-size:13px;color:#94a3b8;font-style:italic;border-left:3px solid #10b981;padding-left:14px;">
  "Rentar con confianza empieza con el equipo adecuado a tu lado." — Equipo {{Sitio}}
</p>
CONTENT;

        // ─── COMPRA ─────────────────────────────────────────────────────────────
        $compraContent = <<<'CONTENT'
<h2 style="margin:0 0 14px;font-size:21px;font-weight:800;color:#1e293b;">Hola, {{Nombre}} 👋</h2>
<p style="margin:0 0 16px;font-size:15px;line-height:1.75;color:#475569;">
  ¡Felicidades por dar el primer paso! Comprar una propiedad es una de las decisiones más importantes de tu vida, y estamos aquí para acompañarte en cada etapa del proceso.
</p>
<p style="margin:0 0 24px;font-size:15px;line-height:1.75;color:#475569;">
  En tu portal encontrarás toda la información sobre las propiedades que hemos seleccionado para ti, los documentos del proceso y el estado actualizado de tu búsqueda.
</p>

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
  <tr>
    <td width="30%" style="padding:14px;background:#fdf4ff;border-radius:12px;text-align:center;vertical-align:top;">
      <div style="font-size:28px;margin-bottom:6px;">🏡</div>
      <div style="font-size:12px;font-weight:700;color:#7c3aed;">Propiedades</div>
      <div style="font-size:11px;color:#c4b5fd;margin-top:3px;">Seleccionadas para ti</div>
    </td>
    <td width="5%"></td>
    <td width="30%" style="padding:14px;background:#fff7ed;border-radius:12px;text-align:center;vertical-align:top;">
      <div style="font-size:28px;margin-bottom:6px;">📑</div>
      <div style="font-size:12px;font-weight:700;color:#c2410c;">Documentos</div>
      <div style="font-size:11px;color:#fdba74;margin-top:3px;">Siempre organizados</div>
    </td>
    <td width="5%"></td>
    <td width="30%" style="padding:14px;background:#f0fdf4;border-radius:12px;text-align:center;vertical-align:top;">
      <div style="font-size:28px;margin-bottom:6px;">✅</div>
      <div style="font-size:12px;font-weight:700;color:#166534;">Seguimiento</div>
      <div style="font-size:11px;color:#86efac;margin-top:3px;">En tiempo real</div>
    </td>
  </tr>
</table>

<p style="margin:0;font-size:13px;color:#94a3b8;font-style:italic;border-left:3px solid #8b5cf6;padding-left:14px;">
  "Tu hogar ideal existe. Nosotros te ayudamos a encontrarlo." — Equipo {{Sitio}}
</p>
CONTENT;

        // ─── GENÉRICO (fallback) ─────────────────────────────────────────────────
        $genericContent = <<<'CONTENT'
<h2 style="margin:0 0 14px;font-size:21px;font-weight:800;color:#1e293b;">Hola, {{Nombre}} 👋</h2>
<p style="margin:0 0 16px;font-size:15px;line-height:1.75;color:#475569;">
  Tu acceso al portal de clientes de <strong>{{Sitio}}</strong> ha sido activado. Aquí podrás consultar y dar seguimiento a toda la información relacionada con tu proceso inmobiliario.
</p>
<p style="margin:0 0 24px;font-size:15px;line-height:1.75;color:#475569;">
  Estamos disponibles para resolver cualquier duda que tengas. No dudes en contactarnos respondiendo este correo.
</p>
<p style="margin:0;font-size:13px;color:#94a3b8;font-style:italic;border-left:3px solid #667eea;padding-left:14px;">
  "Gracias por confiar en nosotros." — Equipo {{Sitio}}
</p>
CONTENT;

        $templates = [
            [
                'name'     => 'BienvenidaPortalVenta',
                'subject'  => '🏠 ¡Tu portal está listo! Comenzamos el proceso de venta',
                'gradient' => 'linear-gradient(135deg,#667eea 0%,#764ba2 100%)',
                'accent'   => '#667eea',
                'icon'     => '🏠',
                'tagline'  => 'Estamos listos para vender tu inmueble al mejor precio.',
                'content'  => $ventaContent,
            ],
            [
                'name'     => 'BienvenidaPortalRenta',
                'subject'  => '🔑 ¡Acceso activado! Tu portal de renta está listo — {{Sitio}}',
                'gradient' => 'linear-gradient(135deg,#10b981 0%,#059669 100%)',
                'accent'   => '#10b981',
                'icon'     => '🔑',
                'tagline'  => 'Gestiona tu proceso de renta en un solo lugar.',
                'content'  => $rentaContent,
            ],
            [
                'name'     => 'BienvenidaPortalCompra',
                'subject'  => '🎉 ¡Bienvenido! Tu búsqueda del hogar ideal comienza aquí',
                'gradient' => 'linear-gradient(135deg,#8b5cf6 0%,#7c3aed 100%)',
                'accent'   => '#8b5cf6',
                'icon'     => '🎉',
                'tagline'  => 'Encontrar tu hogar ideal es nuestra misión.',
                'content'  => $compraContent,
            ],
            [
                'name'     => 'BienvenidaPortal',
                'subject'  => '👋 Bienvenido a tu portal de cliente — {{Sitio}}',
                'gradient' => 'linear-gradient(135deg,#667eea 0%,#764ba2 100%)',
                'accent'   => '#667eea',
                'icon'     => '👋',
                'tagline'  => 'Tu portal está listo. Entra cuando quieras.',
                'content'  => $genericContent,
            ],
        ];

        foreach ($templates as $t) {
            EmailTemplate::updateOrCreate(
                ['name' => $t['name']],
                [
                    'subject'   => $t['subject'],
                    'body'      => $this->buildHtml($t),
                    'body_text' => strip_tags($t['content']),
                ]
            );
        }

        $this->command->info('✔ Portal welcome templates created/updated: ' . count($templates));
    }

    private function buildHtml(array $t): string
    {
        $gradient = $t['gradient'];
        $accent   = $t['accent'];
        $icon     = $t['icon'];
        $tagline  = $t['tagline'];
        $content  = $t['content'];

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Bienvenido — {{Sitio}}</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;color:#1e293b;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f1f5f9;">
<tr><td align="center" style="padding:36px 16px 48px;">

  <table width="600" cellpadding="0" cellspacing="0" border="0"
    style="max-width:600px;width:100%;background:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,.10);">

    <!-- ═══ HEADER ═══ -->
    <tr>
      <td style="background:{$gradient};padding:44px 40px 36px;text-align:center;">

        <!-- Logo (onerror oculta si no carga) -->
        <img src="{{LogoURL}}" alt="{{Sitio}}" height="50"
          style="max-height:50px;max-width:210px;display:block;margin:0 auto 20px;object-fit:contain;"
          onerror="this.style.display='none'">

        <!-- Hero icon -->
        <div style="font-size:52px;line-height:1;margin-bottom:12px;">{$icon}</div>

        <h1 style="margin:0 0 10px;color:#ffffff;font-size:28px;font-weight:800;letter-spacing:-.4px;line-height:1.2;">
          ¡Bienvenido a tu portal!
        </h1>
        <p style="margin:0;color:rgba(255,255,255,.88);font-size:15px;line-height:1.5;">{$tagline}</p>
      </td>
    </tr>

    <!-- ═══ BODY ═══ -->
    <tr>
      <td style="padding:38px 40px 28px;">
        {$content}
      </td>
    </tr>

    <!-- ═══ CREDENCIALES ═══ -->
    <tr>
      <td style="padding:0 40px 32px;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0"
          style="background:#f8fafc;border:2px solid #e2e8f0;border-radius:14px;">
          <tr>
            <td style="padding:24px 26px;">
              <p style="margin:0 0 16px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:1.2px;color:#94a3b8;">
                🔐 &nbsp;Tus credenciales de acceso
              </p>
              <table cellpadding="0" cellspacing="0" border="0" width="100%">
                <tr>
                  <td style="padding:8px 0 8px;font-size:13px;color:#64748b;width:110px;border-bottom:1px solid #f1f5f9;">Usuario:</td>
                  <td style="padding:8px 0 8px;font-size:14px;font-weight:600;color:#1e293b;border-bottom:1px solid #f1f5f9;">{{Email}}</td>
                </tr>
                <tr>
                  <td style="padding:8px 0 0;font-size:13px;color:#64748b;">Contraseña:</td>
                  <td style="padding:8px 0 0;">
                    <code style="background:#ffffff;border:2px solid {$accent};border-radius:8px;padding:6px 16px;font-size:17px;font-weight:800;color:{$accent};letter-spacing:1.5px;font-family:Courier,monospace;">{{Password}}</code>
                  </td>
                </tr>
              </table>
              <p style="margin:16px 0 0;font-size:12px;color:#94a3b8;border-top:1px solid #e2e8f0;padding-top:14px;">
                💡 Por seguridad, cambia tu contraseña después del primer ingreso en <strong>Mi Cuenta → Cambiar contraseña</strong>.
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <!-- ═══ CTA ═══ -->
    <tr>
      <td style="padding:0 40px 44px;text-align:center;">
        <a href="{{PortalURL}}"
          style="display:inline-block;background:{$gradient};color:#ffffff;font-size:16px;font-weight:700;text-decoration:none;padding:16px 52px;border-radius:50px;letter-spacing:.2px;box-shadow:0 6px 20px rgba(0,0,0,.15);">
          Ingresar a mi portal &rarr;
        </a>
        <p style="margin:16px 0 0;font-size:12px;color:#94a3b8;">
          O visita directamente: <a href="{{PortalURL}}" style="color:{$accent};text-decoration:none;font-weight:600;">{{PortalURL}}</a>
        </p>
      </td>
    </tr>

    <!-- ═══ FOOTER ═══ -->
    <tr>
      <td style="background:#f8fafc;border-top:1px solid #e2e8f0;padding:24px 40px;text-align:center;">
        <p style="margin:0 0 4px;font-size:13px;font-weight:700;color:#475569;">{{Sitio}}</p>
        <p style="margin:0;font-size:12px;color:#94a3b8;line-height:1.6;">
          Correo enviado el {{Fecha}} &middot; Si no esperabas este mensaje, ignóralo con confianza.<br>
          ¿Tienes dudas? Responde este correo y te ayudamos de inmediato.
        </p>
      </td>
    </tr>

  </table>
</td></tr>
</table>
</body>
</html>
HTML;
    }
}
