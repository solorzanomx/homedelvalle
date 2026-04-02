<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('trigger_type', 40);   // segment_enter, segment_exit, stage_change, new_client, manual, score_threshold, inactivity
            $table->json('trigger_config')->nullable(); // e.g. {"segment_id":3} or {"stage":"visita","operation_type":"captacion"}
            $table->boolean('is_active')->default(false);
            $table->boolean('allow_reentry')->default(false);
            $table->unsignedInteger('enrollment_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('automation_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position');  // order within automation
            $table->string('type', 40);               // delay, send_email, send_whatsapp, condition, create_task, move_pipeline, update_field, add_score
            $table->json('config');                    // type-specific config
            $table->timestamps();

            $table->index(['automation_id', 'position']);
        });

        Schema::create('automation_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('current_step')->default(0);
            $table->string('status', 20)->default('active'); // active, completed, paused, cancelled
            $table->timestamp('next_run_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'next_run_at']);
            $table->unique(['automation_id', 'client_id', 'status']); // prevent duplicate active enrollments
        });

        Schema::create('automation_step_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('automation_enrollments')->cascadeOnDelete();
            $table->foreignId('step_id')->constrained('automation_steps')->cascadeOnDelete();
            $table->string('status', 20); // executed, skipped, failed, waiting
            $table->json('result')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_step_logs');
        Schema::dropIfExists('automation_enrollments');
        Schema::dropIfExists('automation_steps');
        Schema::dropIfExists('automations');
    }
};
