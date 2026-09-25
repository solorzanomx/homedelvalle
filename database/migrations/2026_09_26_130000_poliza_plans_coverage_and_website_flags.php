<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('poliza_plans', function (Blueprint $table) {
            if (! Schema::hasColumn('poliza_plans', 'show_on_website')) {
                $table->boolean('show_on_website')->default(true)->comment('aparece en la página pública /rentar/polizas-juridicas');
            }
            if (! Schema::hasColumn('poliza_plans', 'show_price_public')) {
                $table->boolean('show_price_public')->default(false)->comment('muestra el precio en la página pública (las tarifas varían por estado; apagado hasta confirmarlas)');
            }
        });

        // Coberturas tomadas de previsionlegal.mx (póliza jurídica de arrendamiento de uso habitacional, 2026-09-26).
        // La página presenta los planes de forma PROGRESIVA ("cada plan incluye progresivamente"): Superior suma a
        // Básica e Integral suma a Superior. No publica montos, plazos ni precios (varían por estado).
        // Solo se rellena lo que esté VACÍO: nunca pisa lo que el asesor haya editado en el CRM.
        $plans = [
            ['names' => ['Básica'], 'description' => 'Investigación del inquilino, contrato a la medida y asesoría jurídica.', 'inclusions' => [
                'Investigación de antecedentes legales y laborales del inquilino',
                'Elaboración del contrato de arrendamiento a la medida',
                'Asesoría jurídica personalizada para aclarar tus dudas',
                'Firma digital',
                'Asesoría e intervención extrajudicial apegada a la legislación',
            ]],
            ['names' => ['Superior', 'Media'], 'description' => 'Suma la investigación de crédito del inquilino y la cobranza extrajudicial.', 'inclusions' => [
                'Todo lo del plan Básica',
                'Investigación de antecedentes crediticios del inquilino',
                'Cobranza extrajudicial de rentas y servicios adeudados',
                'Asistencia de un abogado a la firma del convenio',
            ]],
            ['names' => ['Integral', 'Completa'], 'description' => 'Suma procesos judiciales, honorarios de abogados y gastos de juicio y desalojo.', 'inclusions' => [
                'Todo lo del plan Superior',
                'Investigación de antecedentes laborales del fiador u obligado solidario',
                'Asistencia de un abogado en la firma del contrato',
                'Procesos judiciales: adeudo de rentas, abandono del inmueble, vencimiento del contrato y extinción de dominio',
                'Cobranza judicial de rentas o ejecución de pagarés',
                'Honorarios de los abogados',
                'Gastos del juicio',
                'Gastos del desalojo',
            ]],
        ];

        foreach ($plans as $p) {
            $row = DB::table('poliza_plans')->whereIn('name', $p['names'])->orderBy('id')->first();
            if (! $row) {
                continue;
            }
            $current = json_decode($row->inclusions ?? 'null', true);
            $update = [];
            if (empty($current)) {
                $update['inclusions'] = json_encode($p['inclusions'], JSON_UNESCAPED_UNICODE);
            }
            // La descripción genérica del arranque se reemplaza; una editada a mano se respeta.
            if (! $row->description || str_starts_with($row->description, 'Incluye investigación del inquilino, contrato personalizado')) {
                $update['description'] = $p['description'];
            }
            if ($update) {
                DB::table('poliza_plans')->where('id', $row->id)->update($update + ['updated_at' => now()]);
            }
        }
    }

    public function down(): void {
        Schema::table('poliza_plans', function (Blueprint $table) {
            $cols = array_filter(['show_on_website', 'show_price_public'], fn($c) => Schema::hasColumn('poliza_plans', $c));
            if ($cols) {
                $table->dropColumn(array_values($cols));
            }
        });
    }
};
