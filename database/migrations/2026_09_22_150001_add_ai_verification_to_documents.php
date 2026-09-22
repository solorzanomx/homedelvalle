<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('documents', function (Blueprint $table) {
            if (! Schema::hasColumn('documents', 'ai_extracted_data')) {
                $table->json('ai_extracted_data')->nullable();
            }
            if (! Schema::hasColumn('documents', 'ai_verification_status')) {
                $table->string('ai_verification_status', 20)->nullable()->comment('match, mismatch, expired, unreadable, error');
            }
            if (! Schema::hasColumn('documents', 'ai_verification_notes')) {
                $table->text('ai_verification_notes')->nullable();
            }
        });
    }

    public function down(): void {
        Schema::table('documents', function (Blueprint $table) {
            $columns = ['ai_extracted_data', 'ai_verification_status', 'ai_verification_notes'];
            $existing = array_filter($columns, fn($col) => Schema::hasColumn('documents', $col));
            if ($existing) {
                $table->dropColumn(array_values($existing));
            }
        });
    }
};
