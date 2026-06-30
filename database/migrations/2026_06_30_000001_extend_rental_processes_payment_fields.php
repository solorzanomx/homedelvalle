<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_processes', function (Blueprint $table) {
            if (! Schema::hasColumn('rental_processes', 'payment_frequency')) {
                $table->enum('payment_frequency', ['mensual', 'trimestral', 'semestral', 'anual'])->default('mensual')->after('lease_duration_months');
            }
            // payment_day already exists (added in extend_rental_processes_for_post_cierre)
            if (! Schema::hasColumn('rental_processes', 'annual_increase_type')) {
                $table->enum('annual_increase_type', ['none', 'inpc', 'fixed'])->default('inpc')->after('payment_day');
            }
            if (! Schema::hasColumn('rental_processes', 'annual_increase_percentage')) {
                $table->decimal('annual_increase_percentage', 5, 2)->nullable()->after('annual_increase_type');
            }
            if (! Schema::hasColumn('rental_processes', 'broker_commission_amount')) {
                $table->decimal('broker_commission_amount', 12, 2)->nullable()->after('commission_percentage');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rental_processes', function (Blueprint $table) {
            $cols = ['payment_frequency', 'annual_increase_type', 'annual_increase_percentage', 'broker_commission_amount'];
            $existing = array_filter($cols, fn($c) => Schema::hasColumn('rental_processes', $c));
            if ($existing) $table->dropColumn(array_values($existing));
        });
    }
};
