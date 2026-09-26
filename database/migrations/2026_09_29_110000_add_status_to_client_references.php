<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** El asesor puede rechazar una referencia que no sirve (mamá, alguien que vive en la misma casa…) y el cliente debe dar otra. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_references', function (Blueprint $table) {
            if (! Schema::hasColumn('client_references', 'status')) {
                $table->string('status', 20)->default('pending');   // pending | rejected
                $table->string('rejection_reason', 200)->nullable();
                $table->timestamp('rejected_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('client_references', function (Blueprint $table) {
            $table->dropColumn(['status', 'rejection_reason', 'rejected_at']);
        });
    }
};
