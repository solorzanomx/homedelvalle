<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_valuations', function (Blueprint $table) {
            $table->text('narrative_error')->nullable()->after('narrative_status');
        });
    }

    public function down(): void
    {
        Schema::table('property_valuations', function (Blueprint $table) {
            $table->dropColumn('narrative_error');
        });
    }
};
