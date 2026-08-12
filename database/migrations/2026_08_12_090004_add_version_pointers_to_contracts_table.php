<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->foreignId('current_version_id')->nullable()->after('contract_template_id')
                ->constrained('contract_versions')->nullOnDelete();
            $table->foreignId('final_version_id')->nullable()->after('current_version_id')
                ->constrained('contract_versions')->nullOnDelete();
            $table->string('folio')->nullable()->after('title');
        });

        Schema::table('contract_templates', function (Blueprint $table) {
            $table->boolean('uses_clauses')->default(false)->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_version_id');
            $table->dropConstrainedForeignId('final_version_id');
            $table->dropColumn('folio');
        });

        Schema::table('contract_templates', function (Blueprint $table) {
            $table->dropColumn('uses_clauses');
        });
    }
};
