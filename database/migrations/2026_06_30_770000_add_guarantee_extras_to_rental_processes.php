<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('rental_processes', function (Blueprint $table) {
            if (! Schema::hasColumn('rental_processes', 'poliza_aseguradora')) {
                $table->string('poliza_aseguradora', 100)->nullable()->after('id');
            }
            if (! Schema::hasColumn('rental_processes', 'poliza_number')) {
                $table->string('poliza_number', 60)->nullable()->after('poliza_aseguradora');
            }
            if (! Schema::hasColumn('rental_processes', 'poliza_expiry')) {
                $table->date('poliza_expiry')->nullable()->after('poliza_number');
            }
        });
    }

    public function down(): void {
        Schema::table('rental_processes', function (Blueprint $table) {
            $columns = ['poliza_aseguradora', 'poliza_number', 'poliza_expiry'];
            $existing = array_filter($columns, fn($col) => Schema::hasColumn('rental_processes', $col));
            if ($existing) {
                $table->dropColumn(array_values($existing));
            }
        });
    }
};
