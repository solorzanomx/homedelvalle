<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('clients', function (Blueprint $table) {
            // El INE/IFE normalmente solo trae mes/año de vigencia, no día —
            // id_expiry (date) obligaba a inventar un día que no existe en
            // el documento real.
            if (! Schema::hasColumn('clients', 'id_expiry_month')) {
                $table->unsignedTinyInteger('id_expiry_month')->nullable();
            }
            if (! Schema::hasColumn('clients', 'id_expiry_year')) {
                $table->unsignedSmallInteger('id_expiry_year')->nullable();
            }
            // Cómo decide comprobar ingresos (nómina, estados de cuenta,
            // declaración de impuestos...) — antes se le pedían los 4 tipos
            // de documento a la vez sin preguntar cuál aplica.
            if (! Schema::hasColumn('clients', 'income_proof_type')) {
                $table->string('income_proof_type', 30)->nullable()->comment('nomina, estado_cuenta, declaracion_impuestos, cfdi_honorarios, otro');
            }
            // Mascotas — array de {type: perro|gato, size: chico|mediano|grande}
            if (! Schema::hasColumn('clients', 'pets')) {
                $table->json('pets')->nullable();
            }
        });
    }

    public function down(): void {
        Schema::table('clients', function (Blueprint $table) {
            $columns = ['id_expiry_month', 'id_expiry_year', 'income_proof_type', 'pets'];
            $existing = array_filter($columns, fn($col) => Schema::hasColumn('clients', $col));
            if ($existing) {
                $table->dropColumn(array_values($existing));
            }
        });
    }
};
