<?php

namespace App\Console\Commands;

use App\Actions\Property\FetchStreetViewPhotoAction;
use App\Models\Property;
use Illuminate\Console\Command;

class FetchPropertyStreetViews extends Command
{
    protected $signature   = 'property:fetch-street-views {--limit=50 : Máx. propiedades a procesar}';
    protected $description = 'Descarga imagen de Street View para propiedades sin foto que tienen dirección';

    public function handle(FetchStreetViewPhotoAction $action): int
    {
        if (! config('services.google_maps.key')) {
            $this->error('GOOGLE_MAPS_KEY no configurado en .env');
            return self::FAILURE;
        }

        $limit = (int) $this->option('limit');

        // Solo propiedades con dirección o colonia y sin ninguna foto
        $properties = Property::whereDoesntHave('photos')
            ->where(fn($q) => $q->whereNotNull('address')->orWhereNotNull('colony'))
            ->limit($limit)
            ->get();

        if ($properties->isEmpty()) {
            $this->info('No hay propiedades pendientes de foto.');
            return self::SUCCESS;
        }

        $this->info("Procesando {$properties->count()} propiedades...");
        $bar = $this->output->createProgressBar($properties->count());
        $bar->start();

        $saved  = 0;
        $failed = 0;

        foreach ($properties as $property) {
            $ok = $action->execute($property);
            $ok ? $saved++ : $failed++;
            $bar->advance();
            usleep(200_000); // 200ms entre llamadas para no saturar la API
        }

        $bar->finish();
        $this->newLine();
        $this->info("Completado: {$saved} fotos guardadas, {$failed} sin imagery disponible.");

        return self::SUCCESS;
    }
}
