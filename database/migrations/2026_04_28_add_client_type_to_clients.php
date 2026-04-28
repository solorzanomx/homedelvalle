<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('clients', 'client_type')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->enum('client_type', ['owner', 'buyer', 'investor'])->nullable()->after('email')->comment('Tipo de cliente: propietario, comprador, inversionista');
                $table->string('lead_source')->nullable()->after('utm_campaign')->comment('Origen del lead: /comprar, /vende-tu-propiedad, /contacto, etc');
            });
        }
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['client_type', 'lead_source']);
        });
    }
};
