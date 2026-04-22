<?php

namespace App\Console\Commands;

use App\Events\DocumentoFirmadoGoogle;
use App\Models\GoogleSignatureRequest;
use App\Services\GoogleESignatureService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckGoogleSignatures extends Command
{
    protected $signature   = 'google:check-signatures
                               {--dry-run : Muestra qué actualizaría sin guardar cambios}';
    protected $description = 'Consulta el estado de todas las solicitudes de firma pendientes en Google eSignature';

    public function handle(GoogleESignatureService $eSignature): int
    {
        $pending = GoogleSignatureRequest::pending()->get();

        if ($pending->isEmpty()) {
            $this->line('No hay solicitudes de firma pendientes.');
            return self::SUCCESS;
        }

        $this->info("Revisando {$pending->count()} solicitud(es) pendiente(s)...");
        $bar = $this->output->createProgressBar($pending->count());
        $bar->start();

        $updated = 0;
        $errors  = 0;

        foreach ($pending as $request) {
            try {
                $status = $eSignature->getSignatureStatus($request->signature_request_id);

                if ($status['status'] !== 'pending') {
                    if (!$this->option('dry-run')) {
                        $request->update([
                            'status'          => $status['status'],
                            'google_response' => array_merge(
                                $request->google_response ?? [],
                                ['last_poll' => $status['raw']]
                            ),
                            'completed_at' => $status['status'] === 'completed'
                                ? now()
                                : null,
                        ]);

                        if ($status['status'] === 'completed') {
                            DocumentoFirmadoGoogle::dispatch($request);
                        }
                    }

                    $this->newLine();
                    $this->line(
                        "  [{$request->id}] {$request->document_name}: " .
                        "{$status['google_state']}" .
                        ($this->option('dry-run') ? ' (dry-run)' : ' ✓')
                    );
                    $updated++;
                }
            } catch (\Throwable $e) {
                $errors++;
                Log::warning('google:check-signatures — error al consultar', [
                    'request_id' => $request->id,
                    'error'      => $e->getMessage(),
                ]);
                $this->newLine();
                $this->warn("  [{$request->id}] Error: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Completado. Actualizados: {$updated} | Errores: {$errors}");

        return self::SUCCESS;
    }
}
