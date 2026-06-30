<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_valuations', function (Blueprint $table) {
            $table->string('input_address', 250)->nullable()->after('input_colonia_raw');
        });
    }

    public function down(): void
    {
        Schema::table('property_valuations', function (Blueprint $table) {
            $table->dropColumn('input_address');
        });
    }
};
