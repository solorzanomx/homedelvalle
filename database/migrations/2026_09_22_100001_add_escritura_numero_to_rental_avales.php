<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('rental_avales', function (Blueprint $table) {
            if (! Schema::hasColumn('rental_avales', 'escritura_numero')) {
                $table->string('escritura_numero', 80)->nullable();
            }
        });
    }

    public function down(): void {
        Schema::table('rental_avales', function (Blueprint $table) {
            if (Schema::hasColumn('rental_avales', 'escritura_numero')) {
                $table->dropColumn('escritura_numero');
            }
        });
    }
};
