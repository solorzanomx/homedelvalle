<?php

namespace App\Console\Commands;

use App\Services\EmailReplyChecker;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('email:check-replies')]
#[Description('Revisa la bandeja de entrada (IMAP) por respuestas de clientes y detiene sus automatizaciones activas.')]
class CheckEmailReplies extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(EmailReplyChecker $checker)
    {
        $stats = $checker->checkReplies();

        if ($stats['error'] ?? null) {
            $this->error('Error revisando correos: ' . $stats['error']);
            return self::FAILURE;
        }

        $this->info("Revisados: {$stats['checked']} | Emparejados a un cliente: {$stats['matched']} | Sin emparejar: {$stats['unmatched']}");
        return self::SUCCESS;
    }
}
