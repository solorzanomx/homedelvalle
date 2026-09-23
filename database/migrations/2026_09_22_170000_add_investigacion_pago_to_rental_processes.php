<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('rental_processes', function (Blueprint $table) {
            if (! Schema::hasColumn('rental_processes', 'investigacion_amount')) {
                $table->decimal('investigacion_amount', 10, 2)->nullable();
            }
            if (! Schema::hasColumn('rental_processes', 'investigacion_paid_at')) {
                $table->date('investigacion_paid_at')->nullable();
            }
            if (! Schema::hasColumn('rental_processes', 'investigacion_payment_method')) {
                $table->string('investigacion_payment_method', 20)->nullable();
            }
            if (! Schema::hasColumn('rental_processes', 'investigacion_notes')) {
                $table->text('investigacion_notes')->nullable();
            }
        });
    }

    public function down(): void {
        Schema::table('rental_processes', function (Blueprint $table) {
            $columns = ['investigacion_amount', 'investigacion_paid_at', 'investigacion_payment_method', 'investigacion_notes'];
            $existing = array_filter($columns, fn($col) => Schema::hasColumn('rental_processes', $col));
            if ($existing) {
                $table->dropColumn(array_values($existing));
            }
        });
    }
};
