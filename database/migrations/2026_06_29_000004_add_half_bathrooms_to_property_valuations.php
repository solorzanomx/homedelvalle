<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_valuations', function (Blueprint $table) {
            $table->unsignedTinyInteger('input_half_bathrooms')
                  ->default(0)
                  ->after('input_bathrooms');
        });
    }

    public function down(): void
    {
        Schema::table('property_valuations', function (Blueprint $table) {
            $table->dropColumn('input_half_bathrooms');
        });
    }
};
