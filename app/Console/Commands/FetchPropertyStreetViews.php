<?php

namespace App\Console\Commands;

use App\Actions\Property\FetchStreetViewPhotoAction;
use App\Models\Captacion;
use App\Models\Property;
use Illuminate\Console\Command;

class FetchPropertyStreetViews extends Command
{
    protected $signature   = 'property:fetch-street-views {--limit=100 : Máx. propiedades a procesar}';
    protected $description = 'Descarga imagen de Street View para propiedades sin foto (dirección propia o via captación)';

    public function handle(FetchStreetViewPhotoAction $action): int
    {
        if (! config('services.google_maps.key')) {
            $this->error('GOOGLE_MAPS_KEY no configurado en .env');
            return self::FAILURE;
        }

        $limit = (int) $this->option('limit');

        // 1. Propiedades sin foto con dirección/colonia propia
        $directIds = Property::whereDoesntHave('photos')
            ->where(fn($q) => $q->where(fn($q2) => $q2->whereNotNull('address')->where('address', '!=', ''))
                                ->orWhere(fn($q2) => $q2->whereNotNull('colony')->where('colony', '!=', '')))
            ->pluck('id');

        // 2. Propiedades sin foto vinculadas a captaciones con property_address
        $captacionIds = Captacion::whereNotNull('property_id')
            ->whereNotNull('property_address')
            ->where('property_address', '!=', '')
            ->pluck('property_id');

        $allIds = $directIds->merge($captacionIds)->unique()->take($limit);

        if ($allIds->isEmpty()) {
            $this->info('No hay propiedades pendientes de foto.');
            return self::SUCCESS;
        }

        $properties = Property::with('photos')->whereIn('id', $allIds)->get()
            ->filter(fn($p) => ! $p->photos->count()); // doble-check sin foto

        $this->info("Procesando {$properties->count()} propiedades...");
        $bar = $this->output->createProgressBar($properties->count());
        $bar->start();

        $saved = 0; $failed = 0;

        foreach ($properties as $property) {
            // Si la propiedad no tiene address/colony propia, intentar completar desde captación
            if (empty($property->address) && empty($property->colony)) {
                $captacion = Captacion::where('property_id', $property->id)
                    ->whereNotNull('property_address')
                    ->first();

                if ($captacion?->property_address) {
                    // Parsear address desde captacion (formato: "Colonia, Calle Num, Ciudad")
                    $property->colony = $property->colony ?: explode(',', $captacion->property_address)[0] ?? null;
                }
            }

            $ok = $action->execute($property);
            $ok ? $saved++ : $failed++;
            $bar->advance();
            usleep(250_000); // 250ms entre llamadas
        }

        $bar->finish();
        $this->newLine();
        $this->info("Completado: {$saved} fotos guardadas, {$failed} sin imagery o sin dirección suficiente.");

        return self::SUCCESS;
    }
}
