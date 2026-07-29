<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Importa las bases públicas de SEDUVI/catastro de Benito Juárez a su propia
 * capa de datos, independiente de Property — sirve para armar listas de
 * predios candidatos (ej. H4 + más de 300m²) y, más adelante, como dato de
 * referencia al dar de alta propiedades o hacer valuaciones.
 */
class ImportSeduviData extends Command
{
    protected $signature = 'app:import-seduvi-data
        {--zonificacion= : Ruta al CSV de zonificación (benito_juarez.csv)}
        {--catastro= : Ruta al CSV de catastro (catastro2021_BENITO_JUAREZ.csv)}
        {--truncate : Vaciar la tabla correspondiente antes de importar}';

    protected $description = 'Importa los CSV públicos de SEDUVI (zonificación) y catastro de Benito Juárez';

    private const BATCH_SIZE = 1000;

    public function handle(): int
    {
        if ($path = $this->option('zonificacion')) {
            $this->importZonificacion($path);
        }

        if ($path = $this->option('catastro')) {
            $this->importCatastro($path);
        }

        if (!$this->option('zonificacion') && !$this->option('catastro')) {
            $this->error('Especifica al menos --zonificacion=ruta o --catastro=ruta.');
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function clean(?string $value): ?string
    {
        $value = trim((string) $value);
        return in_array(strtoupper($value), ['', 'NA', 'NULL', 'N/A'], true) ? null : $value;
    }

    private function cleanDecimal(?string $value): ?float
    {
        $value = $this->clean($value);
        return ($value !== null && is_numeric($value)) ? (float) $value : null;
    }

    private function cleanInt(?string $value): ?int
    {
        $value = $this->clean($value);
        return ($value !== null && is_numeric($value)) ? (int) $value : null;
    }

    private function importZonificacion(string $path): void
    {
        if (!is_readable($path)) {
            $this->error("No se puede leer: {$path}");
            return;
        }

        if ($this->option('truncate')) {
            DB::table('zonificacion_predios')->truncate();
        }

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);
        $header = array_map('trim', $header);

        $batch = [];
        $total = 0;
        $now = now();

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) !== count($header)) {
                continue; // fila corrupta/desalineada, se salta
            }
            $r = array_combine($header, $row);

            $liga = $this->clean($r['liga_ciuda'] ?? null);
            $cuentaCatastral = null;
            if ($liga && preg_match('/cuentaCatastral=([^&]+)/', $liga, $m)) {
                $cuentaCatastral = $m[1];
            }

            $batch[] = [
                'alcaldia'         => $this->clean($r['alcaldia'] ?? null) ?? 'BENITO JUAREZ',
                'calle'            => $this->clean($r['calle'] ?? null),
                'no_externo'       => $this->clean($r['no_externo'] ?? null),
                'colonia'          => $this->clean($r['colonia'] ?? null),
                'codigo_postal'    => $this->clean($r['codigo_pos'] ?? null),
                'superficie'       => $this->cleanDecimal($r['superficie'] ?? null),
                'uso_descri'       => $this->clean($r['uso_descri'] ?? null),
                'densidad_d'       => $this->clean($r['densidad_d'] ?? null),
                'niveles'          => $this->clean($r['niveles'] ?? null),
                'altura'           => $this->clean($r['altura'] ?? null),
                'area_libre'       => $this->clean($r['area_libre'] ?? null),
                'minimo_viv'       => $this->clean($r['minimo_viv'] ?? null),
                'liga_ciudadana'   => $liga,
                'cuenta_catastral' => $cuentaCatastral,
                'longitud'         => $this->cleanDecimal($r['longitud'] ?? null),
                'latitud'          => $this->cleanDecimal($r['latitud'] ?? null),
                'created_at'       => $now,
                'updated_at'       => $now,
            ];

            if (count($batch) >= self::BATCH_SIZE) {
                DB::table('zonificacion_predios')->insert($batch);
                $total += count($batch);
                $batch = [];
                $this->output->write("\r  zonificación: {$total} filas importadas...");
            }
        }
        if ($batch) {
            DB::table('zonificacion_predios')->insert($batch);
            $total += count($batch);
        }
        fclose($handle);

        $this->newLine();
        $this->info("Zonificación: {$total} filas importadas.");
    }

    private function importCatastro(string $path): void
    {
        if (!is_readable($path)) {
            $this->error("No se puede leer: {$path}");
            return;
        }

        if ($this->option('truncate')) {
            DB::table('catastro_predios')->truncate();
        }

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);
        $header = array_map('trim', $header);

        $batch = [];
        $total = 0;
        $now = now();

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) !== count($header)) {
                continue;
            }
            $r = array_combine($header, $row);

            $calleNumero = $this->clean($r['calle_numero'] ?? null);
            $calle = $calleNumero;
            $numero = null;
            if ($calleNumero && preg_match('/^(.*?)\s+(\d+[A-Za-z]?)$/', $calleNumero, $m)) {
                $calle = trim($m[1]);
                $numero = $m[2];
            }

            $batch[] = [
                'fid'                   => $this->cleanInt($r['fid'] ?? null),
                'fid_2'                 => $this->cleanInt($r['fid_2'] ?? null),
                'calle_numero'          => $calleNumero,
                'calle'                 => $calle,
                'numero'                => $numero,
                'codigo_postal'         => $this->clean($r['codigo_postal'] ?? null),
                'colonia'               => $this->clean($r['colonia'] ?? null),
                'alcaldia'              => $this->clean($r['alcaldia'] ?? null),
                'sup_terreno'           => $this->cleanDecimal($r['sup_terreno'] ?? null),
                'sup_construccion'      => $this->cleanDecimal($r['sup_construccion'] ?? null),
                'anio_construccion'     => $this->cleanInt($r['anio_construccion'] ?? null),
                'instal_esp'            => $this->clean($r['instal_esp'] ?? null),
                'valor_unitario_suelo'  => $this->cleanDecimal($r['valor_unitario_suelo'] ?? null),
                'valor_suelo'           => $this->cleanDecimal($r['valor_suelo'] ?? null),
                'cve_vus'               => $this->clean($r['cve_vus'] ?? null),
                'subsidio'              => $this->clean($r['subsidio'] ?? null),
                'created_at'            => $now,
                'updated_at'            => $now,
            ];

            if (count($batch) >= self::BATCH_SIZE) {
                DB::table('catastro_predios')->insert($batch);
                $total += count($batch);
                $batch = [];
                $this->output->write("\r  catastro: {$total} filas importadas...");
            }
        }
        if ($batch) {
            DB::table('catastro_predios')->insert($batch);
            $total += count($batch);
        }
        fclose($handle);

        $this->newLine();
        $this->info("Catastro: {$total} filas importadas.");
    }
}
