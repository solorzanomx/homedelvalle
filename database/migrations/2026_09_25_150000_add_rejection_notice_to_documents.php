<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('documents', function (Blueprint $table) {
            if (! Schema::hasColumn('documents', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable();
            }
            if (! Schema::hasColumn('documents', 'rejection_notified_at')) {
                $table->timestamp('rejection_notified_at')->nullable();
            }
            if (! Schema::hasColumn('documents', 'rejection_notified_via')) {
                $table->string('rejection_notified_via', 20)->nullable()->comment('email, whatsapp, skipped, no_email, failed');
            }
        });
    }

    public function down(): void {
        Schema::table('documents', function (Blueprint $table) {
            $existing = array_filter(['rejected_at', 'rejection_notified_at', 'rejection_notified_via'], fn($c) => Schema::hasColumn('documents', $c));
            if ($existing) {
                $table->dropColumn(array_values($existing));
            }
        });
    }
};
