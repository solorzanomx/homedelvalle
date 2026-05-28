<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guard: si la tabla quedó a medias en un intento anterior, la eliminamos
        Schema::dropIfExists('market_update_runs');

        Schema::create('market_update_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_colonia_id')
                  ->constrained('market_colonias')
                  ->cascadeOnDelete();
            $table->enum('operation_type', ['sale', 'rent']);
            $table->enum('status', ['pending', 'running', 'done', 'failed'])->default('pending');
            $table->json('property_types')->nullable();        // e.g. ["apartment","house"]
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error_msg')->nullable();
            $table->timestamps();

            $table->index(['market_colonia_id', 'operation_type', 'created_at'], 'mur_colonia_type_date_idx');
            $table->index(['status', 'created_at'], 'mur_status_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_update_runs');
    }

};
