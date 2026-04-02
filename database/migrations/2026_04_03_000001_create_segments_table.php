<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('segments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug', 80)->unique();
            $table->text('description')->nullable();
            $table->json('rules');          // array of rule groups (AND/OR)
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false); // built-in segments
            $table->unsignedInteger('cached_count')->default(0);
            $table->timestamp('last_evaluated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('client_segment', function (Blueprint $table) {
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('segment_id')->constrained()->cascadeOnDelete();
            $table->timestamp('entered_at')->useCurrent();
            $table->timestamp('exited_at')->nullable();
            $table->primary(['client_id', 'segment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_segment');
        Schema::dropIfExists('segments');
    }
};
