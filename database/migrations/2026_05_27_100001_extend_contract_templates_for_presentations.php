<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_templates', function (Blueprint $table) {
            // No tocamos la columna `type` existente (string, valores: rental/commission/renewal).
            // Agregamos intent_target para las plantillas de presentación (type='presentation').
            $table->string('intent_target', 40)->nullable()->after('type');
            $table->index(['type', 'intent_target'], 'ct_type_intent_idx');
        });
    }

    public function down(): void
    {
        Schema::table('contract_templates', function (Blueprint $table) {
            $table->dropIndex('ct_type_intent_idx');
            $table->dropColumn('intent_target');
        });
    }
};
