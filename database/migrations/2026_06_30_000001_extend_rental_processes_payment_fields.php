<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_processes', function (Blueprint $table) {
            $table->enum('payment_frequency', ['mensual', 'trimestral', 'semestral', 'anual'])->default('mensual')->after('lease_duration_months');
            $table->tinyInteger('payment_day')->unsigned()->nullable()->after('payment_frequency')->comment('Día del mes en que se paga (1-28)');
            $table->enum('annual_increase_type', ['none', 'inpc', 'fixed'])->default('inpc')->after('payment_day');
            $table->decimal('annual_increase_percentage', 5, 2)->nullable()->after('annual_increase_type');
            $table->decimal('broker_commission_amount', 12, 2)->nullable()->after('commission_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('rental_processes', function (Blueprint $table) {
            $table->dropColumn(['payment_frequency', 'payment_day', 'annual_increase_type', 'annual_increase_percentage', 'broker_commission_amount']);
        });
    }
};
