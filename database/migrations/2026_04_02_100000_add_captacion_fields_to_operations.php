<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('operations', 'target_type')) {
        Schema::table('operations', function (Blueprint $table) {
            $table->string('target_type', 10)->nullable()->after('type');
            $table->foreignId('source_operation_id')->nullable()->after('user_id')->constrained('operations')->nullOnDelete();
        });
        }
    }

    public function down(): void
    {
        Schema::table('operations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('source_operation_id');
            $table->dropColumn('target_type');
        });
    }
};
