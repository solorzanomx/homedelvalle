<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portal_notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Per-visit notifications
            $table->boolean('notify_visit_scheduled')->default(false);
            $table->boolean('notify_visit_confirmed')->default(false);
            $table->boolean('notify_visit_rescheduled')->default(false);
            // Summary: 'none', 'weekly', 'monthly'
            $table->string('summary_frequency')->default('weekly');
            // Process updates (stage changes, docs approved)
            $table->boolean('notify_process_updates')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_notification_preferences');
    }
};
