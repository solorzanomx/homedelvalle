<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('captacion_id')->nullable()->after('client_id')->constrained('captaciones')->nullOnDelete();
            $table->boolean('is_captacion_required')->default(false)->after('captacion_id');
            $table->enum('captacion_status', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente')->after('is_captacion_required');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['captacion_id']);
            $table->dropColumn(['captacion_id', 'is_captacion_required', 'captacion_status']);
        });
    }
};
