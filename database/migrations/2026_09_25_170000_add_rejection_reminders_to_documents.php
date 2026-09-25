<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('documents', function (Blueprint $table) {
            if (! Schema::hasColumn('documents', 'rejection_reminders_sent')) {
                $table->unsignedTinyInteger('rejection_reminders_sent')->default(0);
            }
            if (! Schema::hasColumn('documents', 'rejection_reminded_at')) {
                $table->timestamp('rejection_reminded_at')->nullable();
            }
        });
    }

    public function down(): void {
        Schema::table('documents', function (Blueprint $table) {
            $existing = array_filter(['rejection_reminders_sent', 'rejection_reminded_at'], fn($c) => Schema::hasColumn('documents', $c));
            if ($existing) {
                $table->dropColumn(array_values($existing));
            }
        });
    }
};
