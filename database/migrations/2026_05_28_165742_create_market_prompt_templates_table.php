<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_prompt_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();          // 'sale.search', 'rent.search', etc.
            $table->string('label');                   // "Búsqueda de venta"
            $table->text('description')->nullable();   // Descripción para el admin
            $table->longText('prompt_text');           // Prompt activo (editable)
            $table->longText('default_text');          // Default original para restaurar
            $table->timestamps();

            // No is_active — siempre se usa el prompt del key correspondiente
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_prompt_templates');
    }
};
