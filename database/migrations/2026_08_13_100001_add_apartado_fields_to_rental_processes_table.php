<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_processes', function (Blueprint $table) {
            $table->decimal('apartado_amount', 12, 2)->nullable()->after('deposit_amount');
            $table->date('apartado_paid_at')->nullable()->after('apartado_amount');
            $table->string('apartado_payment_method')->nullable()->after('apartado_paid_at'); // efectivo | transferencia | cheque
            $table->text('apartado_notes')->nullable()->after('apartado_payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('rental_processes', function (Blueprint $table) {
            $table->dropColumn(['apartado_amount', 'apartado_paid_at', 'apartado_payment_method', 'apartado_notes']);
        });
    }
};
