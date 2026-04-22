<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_valuations', function (Blueprint $table) {
            // Only relevant for apartments
            $table->enum('input_unit_position', ['exterior', 'interior'])->nullable()->after('input_has_storage');
            $table->enum('input_orientation', ['norte','noreste','este','sureste','sur','suroeste','oeste','noroeste'])->nullable()->after('input_unit_position');
            $table->enum('input_seismic_status', ['none','damaged_repaired','damaged_reinforced','unknown'])->nullable()->after('input_orientation');
        });
    }

    public function down(): void
    {
        Schema::table('property_valuations', function (Blueprint $table) {
            $table->dropColumn(['input_unit_position', 'input_orientation', 'input_seismic_status']);
        });
    }
};
