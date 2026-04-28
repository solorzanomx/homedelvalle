<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            // Campos de calificación de lead
            $table->enum('client_type', ['owner', 'buyer', 'investor'])->nullable()->after('lead_tag');
            $table->enum('lead_temperature', ['hot', 'warm', 'cold'])->default('warm')->after('client_type');

            // Campos de presupuesto (para compradores)
            $table->decimal('budget_min', 12, 2)->nullable()->after('lead_temperature');
            $table->decimal('budget_max', 12, 2)->nullable()->after('budget_min');

            // Campos adicionales
            $table->string('property_type')->nullable()->after('budget_max')->comment('Tipo de propiedad de interés');
            $table->json('interest_types')->nullable()->after('property_type')->comment('Tipos de inmueble de interés');
        });
    }

    public function down(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->dropColumn([
                'client_type',
                'lead_temperature',
                'budget_min',
                'budget_max',
                'property_type',
                'interest_types',
            ]);
        });
    }
};
