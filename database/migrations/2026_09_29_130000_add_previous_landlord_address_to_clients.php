<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Dirección del inmueble que rentaba antes (dato del arrendador anterior). */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('clients', 'previous_landlord_address')) {
            Schema::table('clients', fn(Blueprint $t) => $t->string('previous_landlord_address', 200)->nullable());
        }
    }

    public function down(): void
    {
        Schema::table('clients', fn(Blueprint $t) => $t->dropColumn('previous_landlord_address'));
    }
};
