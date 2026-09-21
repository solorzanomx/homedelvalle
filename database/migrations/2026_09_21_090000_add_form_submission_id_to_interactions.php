<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Una visita puede existir para un lead (form_submission) que todavia no
     * se convierte a Client — agendar/confirmar/calificar ya no debe forzar
     * la conversion. client_id se vuelve opcional; form_submission_id cubre
     * el caso de un lead sin cliente todavia.
     */
    public function up(): void
    {
        Schema::table('interactions', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->change();
            $table->foreignId('form_submission_id')->nullable()->after('client_id')
                ->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('interactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('form_submission_id');
            $table->foreignId('client_id')->nullable(false)->change();
        });
    }
};
