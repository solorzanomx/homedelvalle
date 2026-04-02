<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('marketing_channel_id')->nullable()->after('broker_id')->constrained()->nullOnDelete();
            $table->foreignId('marketing_campaign_id')->nullable()->after('marketing_channel_id')->constrained()->nullOnDelete();
            $table->decimal('acquisition_cost', 10, 2)->nullable()->after('marketing_campaign_id');
            $table->string('utm_source')->nullable()->after('acquisition_cost');
            $table->string('utm_medium')->nullable()->after('utm_source');
            $table->string('utm_campaign')->nullable()->after('utm_medium');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['marketing_channel_id']);
            $table->dropForeign(['marketing_campaign_id']);
            $table->dropColumn(['marketing_channel_id', 'marketing_campaign_id', 'acquisition_cost', 'utm_source', 'utm_medium', 'utm_campaign']);
        });
    }
};
