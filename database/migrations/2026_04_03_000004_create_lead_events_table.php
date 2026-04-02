<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('event', 60);       // message_sent, message_opened, message_replied, call_completed, visit_scheduled, visit_completed, form_submitted, score_changed, segment_entered, segment_exited, pipeline_entered, stage_changed
            $table->string('source', 40)->nullable(); // automation, manual, system, webhook
            $table->nullableMorphs('eventable');       // polymorphic: Message, Interaction, Operation, etc.
            $table->json('properties')->nullable();    // event-specific data
            $table->integer('score_delta')->default(0);
            $table->timestamp('occurred_at')->useCurrent();
            $table->timestamps();

            $table->index(['client_id', 'event']);
            $table->index('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_events');
    }
};
