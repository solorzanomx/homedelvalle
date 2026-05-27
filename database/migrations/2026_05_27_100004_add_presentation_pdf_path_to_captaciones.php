<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('captaciones', function (Blueprint $table) {
            $table->text('last_presentation_pdf_path')->nullable()->after('declined_reason');
        });
    }

    public function down(): void
    {
        Schema::table('captaciones', function (Blueprint $table) {
            $table->dropColumn('last_presentation_pdf_path');
        });
    }
};
