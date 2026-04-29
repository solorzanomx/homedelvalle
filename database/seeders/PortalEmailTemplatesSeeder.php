<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Siembra plantillas de email del portal en la tabla email_templates.
 * Idempotente: sólo inserta si el slug no existe.
 */
class PortalEmailTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'slug'     => 'portal_welcome',
                'name'     => 'Portal — Bienvenida y activación de cuenta',
                'subject'  => 'Tu portal de Home del Valle está listo, {{Nombre}}',
                'body'     => $this->portalWelcomeBody(),
                'variables'=> json_encode(['Nombre', 'ActivationLink', 'Email']),
            ],
            [
                'slug'     => 'portal_new_message',
                'name'     => 'Portal — Nuevo mensaje de tu asesor',
                'subject'  => 'Tienes un mensaje nuevo en tu portal · Home del Valle',
                'body'     => $this->portalNewMessageBody(),
                'variables'=> json_encode(['Nombre', 'MensajeResumen', 'PortalLink']),
            ],
            [
                'slug'     => 'portal_document_available',
                'name'     => 'Portal — Nuevo documento disponible',
                'subject'  => 'Hay un documento nuevo esperándote en tu portal',
                'body'     => $this->portalDocumentBody(),
                'variables'=> json_encode(['Nombre', 'DocumentoNombre', 'PortalLink']),
            ],
            [
                'slug'     => 'portal_stage_change',
                'name'     => 'Portal — Cambio de etapa en tu operación',
                'subject'  => 'Tu operación avanzó a una nueva etapa · Home del Valle',
                'body'     => $this->portalStageChangeBody(),
                'variables'=> json_encode(['Nombre', 'Etapa', 'Descripcion', 'PortalLink']),
            ],
        ];

        foreach ($templates as $tpl) {
            $exists = DB::table('email_templates')->where('slug', $tpl['slug'])->exists();
            if (! $exists) {
                DB::table('email_templates')->insert(array_merge($tpl, [
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
                $this->command->info("  ✓ Plantilla '{$tpl['slug']}' creada.");
            } else {
                $this->command->line("  — Plantilla '{$tpl['slug']}' ya existe, omitida.");
            }
        }
    }

    private function portalWelcomeBody(): string
    {
        return <<<HTML
<p>Hola {{Nombre}},</p>

<p>Tu portal personal de <strong>Home del Valle</strong> ya está listo. Desde aquí podrás:</p>

<ul>
  <li>Ver el estado real de tu operación en cada etapa.</li>
  <li>Descargar y subir documentos sin pedírselos a nadie.</li>
  <li>Comunicarte directamente con tu asesor.</li>
  <li>Consultar recibos, contratos y reportes cuando quieras.</li>
</ul>

<p>Para activar tu cuenta y crear tu contraseña, haz clic aquí:</p>

<p><a href="{{ActivationLink}}" style="display:inline-block;background:#2563A0;color:#fff;text-decoration:none;padding:12px 28px;border-radius:10px;font-weight:600;font-size:14px;">Activar mi cuenta →</a></p>

<p style="font-size:12px;color:#64748b;">Este enlace es válido por 7 días. Si no solicitaste esta cuenta, ignora este correo.<br>Tu correo de acceso es: {{Email}}</p>

<p>Pocos inmuebles. Más control. Mejores resultados.<br><strong>El equipo Home del Valle</strong></p>
HTML;
    }

    private function portalNewMessageBody(): string
    {
        return <<<HTML
<p>Hola {{Nombre}},</p>
<p>Tu asesor de Home del Valle te envió un mensaje:</p>
<blockquote style="border-left:3px solid #2563A0;padding:8px 16px;margin:16px 0;color:#475569;">{{MensajeResumen}}</blockquote>
<p><a href="{{PortalLink}}" style="display:inline-block;background:#2563A0;color:#fff;text-decoration:none;padding:12px 28px;border-radius:10px;font-weight:600;font-size:14px;">Ver mensaje completo →</a></p>
<p><strong>El equipo Home del Valle</strong></p>
HTML;
    }

    private function portalDocumentBody(): string
    {
        return <<<HTML
<p>Hola {{Nombre}},</p>
<p>Hay un nuevo documento disponible en tu portal: <strong>{{DocumentoNombre}}</strong></p>
<p><a href="{{PortalLink}}" style="display:inline-block;background:#2563A0;color:#fff;text-decoration:none;padding:12px 28px;border-radius:10px;font-weight:600;font-size:14px;">Ver mis documentos →</a></p>
<p><strong>El equipo Home del Valle</strong></p>
HTML;
    }

    private function portalStageChangeBody(): string
    {
        return <<<HTML
<p>Hola {{Nombre}},</p>
<p>Tu operación avanzó a una nueva etapa: <strong>{{Etapa}}</strong></p>
<p>{{Descripcion}}</p>
<p><a href="{{PortalLink}}" style="display:inline-block;background:#2563A0;color:#fff;text-decoration:none;padding:12px 28px;border-radius:10px;font-weight:600;font-size:14px;">Ver mi operación →</a></p>
<p><strong>El equipo Home del Valle</strong></p>
HTML;
    }
}
