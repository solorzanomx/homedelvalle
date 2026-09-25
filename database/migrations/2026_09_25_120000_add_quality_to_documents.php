<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('documents', function (Blueprint $table) {
            if (! Schema::hasColumn('documents', 'quality_status')) {
                $table->string('quality_status', 20)->nullable()->comment('ok, warn');
            }
            if (! Schema::hasColumn('documents', 'quality_notes')) {
                $table->text('quality_notes')->nullable();
            }
        });
    }

    public function down(): void {
        Schema::table('documents', function (Blueprint $table) {
            $existing = array_filter(['quality_status', 'quality_notes'], fn($c) => Schema::hasColumn('documents', $c));
            if ($existing) {
                $table->dropColumn(array_values($existing));
            }
        });
    }
};
