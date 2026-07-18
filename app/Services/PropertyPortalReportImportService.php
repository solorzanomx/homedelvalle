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
     * Export "Rendimiento" de Inmuebles24 (sin API, descarga manual), hoja
     * unica, columna A = Período, B-G = metricas. Inmuebles24 ha usado dos
     * formatos de columna A: uno por semana ("DD-MM-YYYY / DD-MM-YYYY",
     * como en la sesion original de este importador) y otro por dia
     * ("DD/MM/YYYY", vigente desde 2026-07-18 tras un rediseño de su SPA
     * frágil — ver memoria de esta sesión). Detectamos el formato fila por
     * fila y, si es diario, agregamos sumando por semana antes de guardar —
     * la tabla siempre almacena por semana. Las semanas de Inmuebles24 NO
     * son semanas ISO lunes-domingo: anclan al día en que arrancó el
     * tracking del anuncio (en un caso real, martes-lunes). Para no crear
     * una grilla de semanas paralela que nunca haga match con el
     * historial ya guardado, el ancla se deriva del week_start más
     * reciente que ya exista para esta propiedad/portal.
     */
    private function importInmuebles24(Property $property, UploadedFile $file, ?User $uploader): array
    {
        $externalListingId = null;
        if (preg_match('/_(\d+)\.xlsx$/i', $file->getClientOriginalName(), $m)) {
            $externalListingId = $m[1];
        }

        $lastWeekStart = PropertyPortalReport::where('property_id', $property->id)
            ->where('portal', 'inmuebles24')
            ->max('week_start');
        $anchorDow = $lastWeekStart ? Carbon::parse($lastWeekStart)->dayOfWeek : Carbon::MONDAY;

        $spreadsheet = IOFactory::load($file->getRealPath());
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        $metricKeys = ['exposicion', 'visualizaciones', 'consultas_recibidas', 'completaron_formulario', 'contactaron_whatsapp', 'vieron_datos'];

        // Acumulador por semana: se llena tanto con filas ya semanales como
        // con filas diarias agregadas, para que ambos formatos convivan si
        // aparecieran en el mismo archivo.
        $weeks = [];

        foreach (array_slice($rows, 1) as $row) {
            $periodo = trim((string) ($row[0] ?? ''));
            if ($periodo === '') {
                continue;
            }

            $days = 1;
            if (str_contains($periodo, ' / ')) {
                // Formato semanal: "DD-MM-YYYY / DD-MM-YYYY"
                [$startRaw, $endRaw] = array_map('trim', explode('/', $periodo, 2));
                try {
                    $weekStart = Carbon::createFromFormat('d-m-Y', $startRaw)->startOfDay();
                    $weekEnd = Carbon::createFromFormat('d-m-Y', $endRaw)->startOfDay();
                } catch (\Throwable) {
                    continue;
                }
                $days = 7;
            } elseif (str_contains($periodo, '/')) {
                // Formato diario: "DD/MM/YYYY" — se agrega a su semana ISO
                try {
                    $day = Carbon::createFromFormat('d/m/Y', $periodo)->startOfDay();
                } catch (\Throwable) {
                    continue;
                }
                $weekStart = $day->copy()->startOfWeek($anchorDow);
                $weekEnd = $weekStart->copy()->addDays(6);
            } else {
                continue;
            }

            $key = $weekStart->toDateString();
            if (!isset($weeks[$key])) {
                $weeks[$key] = ['week_end' => $weekEnd->toDateString(), 'days' => 0, ...array_fill_keys($metricKeys, 0)];
            }
            $weeks[$key]['days'] += $days;
            $weeks[$key]['week_end'] = max($weeks[$key]['week_end'], $weekEnd->toDateString());
            foreach ($metricKeys as $i => $metricKey) {
                $weeks[$key][$metricKey] += (int) ($row[$i + 1] ?? 0);
            }
        }

        $created = 0;
        $updated = 0;

        foreach ($weeks as $weekStartStr => $data) {
            // No usar updateOrCreate([...cast 'date'...]) directo: Eloquent
            // guarda el cast 'date' con formato datetime completo
            // ('Y-m-d H:i:s'), pero el array de busqueda de updateOrCreate
            // no aplica el cast — un string plano 'Y-m-d' nunca hace match
            // y termina intentando crear un duplicado (choca con el unique).
            // whereDate() sí compara solo la parte de fecha sin importar el
            // formato exacto almacenado.
            $existing = PropertyPortalReport::where('property_id', $property->id)
                ->where('portal', 'inmuebles24')
                ->whereDate('week_start', $weekStartStr)
                ->first();

            // Una semana armada con menos de 7 dias sueltos es parcial
            // (semana en curso, o el archivo diario no alcanzo a cubrirla
            // completa). Si ya existe una semana completa guardada, no la
            // pisamos con un total parcial menor — se actualizara sola
            // cuando una descarga futura traiga los dias que faltan.
            if ($existing && $data['days'] < 7) {
                continue;
            }

            $attributes = [
                'week_end' => $data['week_end'],
                'external_listing_id' => $externalListingId,
                'uploaded_by' => $uploader?->id,
                ...array_intersect_key($data, array_flip($metricKeys)),
            ];

            if ($existing) {
                $existing->update($attributes);
                $updated++;
            } else {
                PropertyPortalReport::create($attributes + [
                    'property_id' => $property->id,
                    'portal' => 'inmuebles24',
                    'week_start' => $weekStartStr,
                ]);
                $created++;
            }
        }

        return ['created' => $created, 'updated' => $updated];
    }
}
