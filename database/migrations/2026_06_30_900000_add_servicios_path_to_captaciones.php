<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('captaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('captaciones', 'last_servicios_pdf_path')) {
                $table->string('last_servicios_pdf_path')->nullable()->after('last_presentation_pdf_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('captaciones', function (Blueprint $table) {
            if (Schema::hasColumn('captaciones', 'last_servicios_pdf_path')) {
                $table->dropColumn('last_servicios_pdf_path');
            }
        });
    }
};
