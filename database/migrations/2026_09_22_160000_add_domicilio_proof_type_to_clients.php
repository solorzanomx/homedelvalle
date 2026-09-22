<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('clients', function (Blueprint $table) {
            if (! Schema::hasColumn('clients', 'domicilio_proof_type')) {
                $table->string('domicilio_proof_type', 20)->nullable()->comment('agua, luz, gas');
            }
        });
    }

    public function down(): void {
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'domicilio_proof_type')) {
                $table->dropColumn('domicilio_proof_type');
            }
        });
    }
};
