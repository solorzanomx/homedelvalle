<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_valuations', function (Blueprint $table) {
            // Tipo de estacionamiento
            $table->enum('input_parking_type', ['regular', 'tandem', 'lift'])
                  ->default('regular')
                  ->after('input_parking');

            // Condición del edificio (separada de la condición del departamento)
            $table->enum('input_building_condition', ['excellent', 'good', 'fair', 'poor'])
                  ->nullable()
                  ->after('input_condition');

            // Ajuste de precio manual y autorización
            $table->decimal('price_override', 12, 2)->nullable()->after('suggested_list_price');
            $table->text('price_override_notes')->nullable()->after('price_override');
            $table->foreignId('price_override_by')->nullable()->constrained('users')->nullOnDelete()->after('price_override_notes');
            $table->timestamp('price_override_at')->nullable()->after('price_override_by');
            $table->boolean('price_override_authorized')->default(false)->after('price_override_at');
        });
    }

    public function down(): void
    {
        Schema::table('property_valuations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('price_override_by');
            $table->dropColumn([
                'input_parking_type',
                'input_building_condition',
                'price_override',
                'price_override_notes',
                'price_override_at',
                'price_override_authorized',
            ]);
        });
    }
};
