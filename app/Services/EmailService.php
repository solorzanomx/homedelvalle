<?php

namespace App\Services;

use App\Models\EmailSetting;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use PHPMailer\PHPMailer\PHPMailer;

class EmailService
{
    /**
     * Resolve SMTP config for a given sender.
     *
     * Per-user: broker sends from their own @homedelvalle.mx email.
     * Uses user's credentials with the global SMTP server settings as base.
     * Falls back to global config if no user config or not active.
     */
    private function resolveSmtpConfig(?User $sender = null): ?object
    {
        $global = EmailSetting::first();

        // Try user-specific config
        if ($sender) {
            $userConfig = $sender->mailSetting;
            if ($userConfig && $userConfig->is_active && $userConfig->isConfigured()) {
                // Use user's email/password, but inherit server settings from global if not set
                return (object) [
                    'host' => $userConfig->smtp_server ?: ($global->smtp_server ?? ''),
                    'port' => $userConfig->port ?: ($global->port ?? 587),
                    'username' => $userConfig->username ?: $userConfig->from_email,
                    'password' => $userConfig->password,
                    'enable_ssl' => $userConfig->encryption !== 'none'
                        ? true
                        : ($global->enable_ssl ?? true),
                    'ssl_mode' => $userConfig->encryption !== 'tls' ? $userConfig->encryption : null,
                    'from_email' => $userConfig->from_email,
                    'from_name' => $userConfig->from_name ?: $sender->full_name,
                ];
            }
        }

        // Fallback to global
        if (!$global || !$global->from_email || !$global->smtp_server) {
            return null;
        }

        return (object) [
            'host' => $global->smtp_server,
            'port' => $global->port,
            'username' => $global->from_email,
            'password' => $global->password,
            'enable_ssl' => $global->enable_ssl,
            'ssl_mode' => null,
            'from_email' => $global->from_email,
            'from_name' => $global->from_name ?? 'CRM Homedelvalle',
        ];
    }

    /**
     * Configure PHPMailer instance with SMTP settings.
     */
    private function configureMail(PHPMailer $mail, object $config): void
    {
        $mail->isSMTP();
        $mail->Host = $config->host;
        $mail->SMTPAuth = true;
        $mail->Username = $config->username;
        $mail->Password = $config->password;
        $mail->Port = $config->port;
        $mail->CharSet = 'UTF-8';

        if ($config->enable_ssl) {
            if ($config->ssl_mode === 'ssl' || $config->port == 465) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
        } else {
            $mail->SMTPSecure = '';
            $mail->SMTPAutoTLS = false;
        }
    }

    /**
     * Send an email. If $sender is provided and has active SMTP config,
     * sends from their personal @homedelvalle.mx email.
     * Otherwise uses global system email.
     */
    public function send(string $to, string $subject, string $htmlBody, ?string $toName = null, ?string $textBody = null, ?User $sender = null): bool
    {
        $config = $this->resolveSmtpConfig($sender);

        if (!$config) {
            Log::warning('EmailService: No hay configuracion SMTP. Correo no enviado a ' . $to);
            return false;
        }

        $mail = new PHPMailer(true);

        try {
            $this->configureMail($mail, $config);
            $mail->setFrom($config->from_email, $config->from_name);
            $mail->addAddress($to, $toName ?? '');

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = $textBody ?? strip_tags($htmlBody);

            $mail->send();

            Log::info('EmailService: Correo enviado a ' . $to . ' desde ' . $config->from_email . ' - Asunto: ' . $subject);
            return true;
        } catch (\Exception $e) {
            Log::error('EmailService: Error al enviar correo a ' . $to . ' - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send an email using a named template with variable replacement.
     */
    public function sendTemplate(string $templateName, string $to, array $variables, ?string $toName = null, ?User $sender = null): bool
    {
        $template = EmailTemplate::where('name', $templateName)->first();

        if (!$template) {
            Log::warning('EmailService: Template "' . $templateName . '" no encontrado.');
            return false;
        }

        $rendered = $template->render($variables);

        return $this->send($to, $rendered['subject'], $rendered['body'], $toName, $rendered['body_text'] ?? null, $sender);
    }

    /**
     * Send welcome email (always uses global/system email).
     */
    public function sendWelcomeEmail(string $name, string $email, string $password, string $role): bool
    {
        $siteName = \App\Models\SiteSetting::first()?->site_name ?? 'Homedelvalle';

        return $this->sendTemplate('BienvenidaUsuario', $email, [
            'Nombre' => $name,
            'Email' => $email,
            'Password' => $password,
            'Rol' => $role,
            'Fecha' => now()->format('d/m/Y H:i'),
            'Sitio' => $siteName,
        ], $name);
    }

    /**
     * Send portal welcome email to a client.
     */
    public function sendPortalWelcome(string $name, string $email, string $password): bool
    {
        $siteName = \App\Models\SiteSetting::first()?->site_name ?? 'Homedelvalle';
        $portalUrl = url('/portal');

        return $this->sendTemplate('BienvenidaPortal', $email, [
            'Nombre' => $name,
            'Email' => $email,
            'Password' => $password,
            'PortalURL' => $portalUrl,
            'Fecha' => now()->format('d/m/Y H:i'),
            'Sitio' => $siteName,
        ], $name);
    }

    /**
     * Test SMTP connection for a user's config or global.
     */
    public function testConnection(?User $user = null): array
    {
        $config = $this->resolveSmtpConfig($user);

        if (!$config) {
            return ['success' => false, 'message' => 'No hay configuracion SMTP guardada.'];
        }

        $mail = new PHPMailer(true);

        try {
            $this->configureMail($mail, $config);
            $mail->smtpConnect();
            $mail->smtpClose();

            return ['success' => true, 'message' => 'Conexion SMTP exitosa con ' . $config->from_email];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}
