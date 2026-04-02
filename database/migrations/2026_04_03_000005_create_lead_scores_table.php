<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->unique()->constrained()->cascadeOnDelete();
            $table->integer('total_score')->default(0);
            $table->integer('engagement_score')->default(0);  // opens, clicks, replies
            $table->integer('activity_score')->default(0);    // calls, visits, meetings
            $table->integer('profile_score')->default(0);     // completeness, budget, etc.
            $table->string('grade', 2)->default('D');         // A, B, C, D
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();

            $table->index('total_score');
            $table->index('grade');
        });

        Schema::create('lead_score_rules', function (Blueprint $table) {
            $table->id();
            $table->string('event', 60);           // matches lead_events.event
            $table->integer('points');
            $table->string('description');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('max_per_day')->default(0); // 0 = unlimited
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_score_rules');
        Schema::dropIfExists('lead_scores');
    }
};
