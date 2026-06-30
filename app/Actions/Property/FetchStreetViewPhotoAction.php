<?php

namespace App\Actions\Property;

use App\Models\Property;
use App\Models\PropertyPhoto;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FetchStreetViewPhotoAction
{
    /**
     * Descarga la imagen de Google Street View y la guarda como foto principal
     * de la propiedad. Solo actúa si la propiedad no tiene fotos aún.
     */
    public function execute(Property $property, bool $forceReplace = false): bool
    {
        $key = config('services.google_maps.key');
        if (! $key) return false;

        // Si ya tiene fotos y no se fuerza reemplazo, saltamos
        if (! $forceReplace && $property->photos()->exists()) return false;

        $parts = array_filter([
            $property->address,
            $property->colony,
            $property->city ?: 'Benito Juárez, CDMX',
            'México',
        ]);

        if (count($parts) < 2) return false;

        $location = implode(', ', $parts);

        $url = 'https://maps.googleapis.com/maps/api/streetview?' . http_build_query([
            'size'              => '1200x675',
            'location'         => $location,
            'fov'              => '90',
            'pitch'            => '5',
            'key'              => $key,
            'return_error_code'=> 'true',
        ]);

        try {
            $response = Http::timeout(20)->get($url);

            if (! $response->successful()) {
                Log::info("FetchStreetViewPhoto: sin imagery para propiedad #{$property->id}", ['location' => $location]);
                return false;
            }

            $contentType = $response->header('Content-Type', '');
            if (! str_contains($contentType, 'image')) {
                return false;
            }

            // Guardar en storage público
            $filename = 'sv-' . $property->id . '-' . Str::random(8) . '.jpg';
            $path     = 'properties/photos/' . $filename;

            Storage::disk('public')->put($path, $response->body());

            // Crear registro PropertyPhoto
            PropertyPhoto::create([
                'property_id' => $property->id,
                'path'        => $path,
                'description' => 'Vista de calle (Street View)',
                'is_primary'  => true,
                'sort_order'  => 0,
            ]);

            return true;

        } catch (\Throwable $e) {
            Log::warning("FetchStreetViewPhoto: error descargando para propiedad #{$property->id}", [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
