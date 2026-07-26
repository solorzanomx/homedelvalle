<?php

namespace App\Console\Commands;

use App\Models\Broker;
use App\Models\Client;
use App\Models\BirthdayEmailLog;
use App\Models\CustomEmailTemplate;
use App\Models\ProviderContact;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendBirthdayGreetings extends Command
{
    protected $signature = 'app:send-birthday-greetings';
    protected $description = 'Envía el correo de felicitación de cumpleaños a clientes, brokers externos y contactos de proveedores';

    public function handle(): int
    {
        $template = CustomEmailTemplate::where('slug', 'feliz-cumpleanos')
            ->where('status', 'published')
            ->first();

        if (!$template) {
            $this->error('No se encontró la plantilla "feliz-cumpleanos" publicada en /admin/email/custom-templates.');
            return self::FAILURE;
        }

        $today = now();
        $year  = $today->year;
        $sent  = 0;

        $groups = [
            'client'           => Client::whereNotNull('birth_date')->whereNotNull('email')
                ->whereMonth('birth_date', $today->month)->whereDay('birth_date', $today->day)->get(),
            'broker'           => Broker::whereNotNull('birth_date')->whereNotNull('email')
                ->whereMonth('birth_date', $today->month)->whereDay('birth_date', $today->day)->get(),
            'provider_contact' => ProviderContact::whereNotNull('birth_date')->whereNotNull('email')
                ->whereMonth('birth_date', $today->month)->whereDay('birth_date', $today->day)->get(),
        ];

        foreach ($groups as $type => $recipients) {
            foreach ($recipients as $recipient) {
                if (BirthdayEmailLog::alreadySentThisYear($type, $recipient->id, $year)) {
                    continue;
                }

                try {
                    $template->send($recipient->email, ['nombre' => $recipient->name]);

                    BirthdayEmailLog::create([
                        'recipient_type' => $type,
                        'recipient_id'   => $recipient->id,
                        'year'           => $year,
                        'sent_at'        => now(),
                    ]);

                    $sent++;
                } catch (\Throwable $e) {
                    Log::warning('SendBirthdayGreetings: fallo al enviar', [
                        'type' => $type,
                        'id'   => $recipient->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $this->info("Correos de cumpleaños enviados: {$sent}");
        return self::SUCCESS;
    }
}
