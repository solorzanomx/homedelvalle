<?php

namespace App\Console\Commands;

use App\Services\EmailInboxProcessor;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('email:check-replies')]
#[Description('Revisa la bandeja de entrada (IMAP): detiene automatizaciones si un cliente respondio, e importa leads nuevos de Inmuebles24.')]
class CheckEmailReplies extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(EmailInboxProcessor $processor)
    {
        $stats = $processor->run();

        if ($stats['error'] ?? null) {
            $this->error('Error revisando correos: ' . $stats['error']);
            return self::FAILURE;
        }

        $this->info(
            "Revisados: {$stats['checked']} | Respuestas de clientes: {$stats['client_replies']} | "
            . "Leads Inmuebles24: {$stats['inmuebles24_leads']} | Sin relacion: {$stats['skipped']}"
        );
        return self::SUCCESS;
    }
}
