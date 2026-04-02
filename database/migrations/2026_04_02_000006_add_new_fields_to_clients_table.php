<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->json('interest_types')->nullable()->after('property_type');
            $table->string('lead_temperature', 10)->nullable()->after('interest_types');
            $table->string('priority', 10)->nullable()->after('lead_temperature');
            $table->string('whatsapp', 20)->nullable()->after('phone');
            $table->text('initial_notes')->nullable()->after('priority');
            $table->foreignId('assigned_user_id')->nullable()->after('broker_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_user_id');
            $table->dropColumn(['interest_types', 'lead_temperature', 'priority', 'whatsapp', 'initial_notes']);
        });
    }
};
