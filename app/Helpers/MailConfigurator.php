<?php

namespace App\Helpers;

use App\Models\EmailSetting;
use Illuminate\Support\Facades\Config;

class MailConfigurator
{
    public static function applyGlobalSettings(): bool
    {
        $settings = EmailSetting::first();

        if (! $settings || ! $settings->smtp_server || ! $settings->from_email) {
            return false;
        }

        $encryption = $settings->enable_ssl
            ? ($settings->port == 465 ? 'ssl' : 'tls')
            : null;

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.host',       $settings->smtp_server);
        Config::set('mail.mailers.smtp.port',       $settings->port ?? 587);
        Config::set('mail.mailers.smtp.encryption', $encryption);
        Config::set('mail.mailers.smtp.username',   $settings->username ?: $settings->from_email);
        Config::set('mail.mailers.smtp.password',   $settings->password);
        Config::set('mail.from.address',            $settings->from_email);
        Config::set('mail.from.name',               $settings->from_name ?? 'Home del Valle');

        return true;
    }
}
