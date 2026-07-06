<?php

namespace App\Services;

use App\Models\Property;
use App\Models\PropertyPortalReport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PropertyPortalReportImportService
{
    public function import(Property $property, string $portal, UploadedFile $file, ?User $uploader): array
    {
        return match ($portal) {
            'inmuebles24' => $this->importInmuebles24($property, $file, $uploader),
            default => throw new \InvalidArgumentException("Portal no soportado: {$portal}"),
        };
    }

    /**
     * Formato del export "Rendimiento" de Inmuebles24 (sin API, descarga
     * manual): hoja unica, columna A = "DD-MM-YYYY / DD-MM-YYYY" (semana),
     * B-G = metricas. Cada descarga trae el historial completo hasta la
     * fecha, no solo la semana nueva — updateOrCreate por semana para que
     * volver a subir el archivo actualice en vez de duplicar.
     */
    private function importInmuebles24(Property $property, UploadedFile $file, ?User $uploader): array
    {
        $externalListingId = null;
        if (preg_match('/_(\d+)\.xlsx$/i', $file->getClientOriginalName(), $m)) {
            $externalListingId = $m[1];
        }

        $spreadsheet = IOFactory::load($file->getRealPath());
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        $created = 0;
        $updated = 0;

        foreach (array_slice($rows, 1) as $row) {
            $periodo = trim((string) ($row[0] ?? ''));
            if ($periodo === '' || !str_contains($periodo, '/')) {
                continue;
            }

            [$startRaw, $endRaw] = array_map('trim', explode('/', $periodo, 2));
            try {
                $weekStart = Carbon::createFromFormat('d-m-Y', $startRaw)->startOfDay();
                $weekEnd = Carbon::createFromFormat('d-m-Y', $endRaw)->startOfDay();
            } catch (\Throwable) {
                continue;
            }

            // No usar updateOrCreate([...cast 'date'...]) directo: Eloquent
            // guarda el cast 'date' con formato datetime completo
            // ('Y-m-d H:i:s'), pero el array de busqueda de updateOrCreate
            // no aplica el cast — un string plano 'Y-m-d' nunca hace match
            // y termina intentando crear un duplicado (choca con el unique).
            // whereDate() sí compara solo la parte de fecha sin importar el
            // formato exacto almacenado.
            $existing = PropertyPortalReport::where('property_id', $property->id)
                ->where('portal', 'inmuebles24')
                ->whereDate('week_start', $weekStart->toDateString())
                ->first();

            $attributes = [
                'week_end' => $weekEnd->toDateString(),
                'external_listing_id' => $externalListingId,
                'exposicion' => (int) ($row[1] ?? 0),
                'visualizaciones' => (int) ($row[2] ?? 0),
                'consultas_recibidas' => (int) ($row[3] ?? 0),
                'completaron_formulario' => (int) ($row[4] ?? 0),
                'contactaron_whatsapp' => (int) ($row[5] ?? 0),
                'vieron_datos' => (int) ($row[6] ?? 0),
                'uploaded_by' => $uploader?->id,
            ];

            if ($existing) {
                $existing->update($attributes);
                $updated++;
            } else {
                PropertyPortalReport::create($attributes + [
                    'property_id' => $property->id,
                    'portal' => 'inmuebles24',
                    'week_start' => $weekStart->toDateString(),
                ]);
                $created++;
            }
        }

        return ['created' => $created, 'updated' => $updated];
    }
}
