<?php

namespace App\Console\Commands;

use App\Models\Property;
use Illuminate\Console\Command;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GeneratePropertyQrCodes extends Command
{
    protected $signature = 'properties:generate-qr-codes {--property-id=}';

    protected $description = 'Genera códigos QR persistentes para propiedades. Uso: php artisan properties:generate-qr-codes o php artisan properties:generate-qr-codes --property-id=5';

    public function handle()
    {
        // Si se especifica una propiedad
        if ($this->option('property-id')) {
            $property = Property::findOrFail($this->option('property-id'));
            $this->generateQrForProperty($property);
            return Command::SUCCESS;
        }

        // Generar para todas las propiedades sin QR
        $properties = Property::whereNull('qr_path')->get();

        if ($properties->isEmpty()) {
            $this->info('✅ Todas las propiedades ya tienen QR generado.');
            return Command::SUCCESS;
        }

        $this->info("Generando QR para {$properties->count()} propiedades...");

        foreach ($properties as $property) {
            $this->generateQrForProperty($property);
        }

        $this->info("✅ QR generados exitosamente.");
        return Command::SUCCESS;
    }

    private function generateQrForProperty(Property $property): void
    {
        try {
            $directory = storage_path("app/public/properties/{$property->id}/qr");

            // Crear directorio si no existe
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $filePath = "{$directory}/qr-code.png";
            $qrPath = "properties/{$property->id}/qr/qr-code.png";

            // Generar QR apuntando a la propiedad pública
            QrCode::size(300)
                ->format('png')
                ->generate(
                    route('properties.show', $property),
                    $filePath
                );

            // Actualizar BD
            $property->update(['qr_path' => $qrPath]);

            $this->line("✓ QR generado para: {$property->title}");
        } catch (\Exception $e) {
            $this->error("✗ Error al generar QR para {$property->title}: {$e->getMessage()}");
        }
    }
}
