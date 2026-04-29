<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->string('operation_type')->nullable()->after('location'); // Compra, Venta, Renta, Desarrollo
            $table->string('ticket')->nullable()->after('operation_type');   // "$3.5 MDP"
            $table->string('time_in_market')->nullable()->after('ticket');   // "32 días"
        });
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropColumn(['operation_type', 'ticket', 'time_in_market']);
        });
    }
};
