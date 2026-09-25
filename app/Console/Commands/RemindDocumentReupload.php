<?php

namespace App\Console\Commands;

use App\Services\DocumentRejectionNotifier;
use Illuminate\Console\Command;

class RemindDocumentReupload extends Command
{
    protected $signature = 'documents:remind-reupload';

    protected $description = 'Recordatorio amable (cada 3 días, máx. 2) a clientes con documentos rechazados que no han vuelto a subir; tras el 2.º avisa al asesor';

    public function handle(DocumentRejectionNotifier $notifier): int
    {
        $r = $notifier->remindDue();
        $this->info("{$r['reminded']} recordatorio(s) enviados; {$r['escalated']} escalado(s) al asesor.");

        return self::SUCCESS;
    }
}
