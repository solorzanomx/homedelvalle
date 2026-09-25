<?php

namespace App\Console\Commands;

use App\Services\DocumentRejectionNotifier;
use Illuminate\Console\Command;

class NotifyDocumentRejections extends Command
{
    protected $signature = 'documents:notify-rejections {--minutes=10 : Espera desde el último rechazo antes de avisar}';

    protected $description = 'Envía al cliente UN correo con todos sus documentos rechazados (con motivo y enlace al Portal) una vez cumplida la espera';

    public function handle(DocumentRejectionNotifier $notifier): int
    {
        $n = $notifier->notifyDue((int) $this->option('minutes'));
        $this->info("{$n} cliente(s) notificados.");

        return self::SUCCESS;
    }
}
