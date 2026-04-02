<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stage_checklist_templates', function (Blueprint $table) {
            $table->id();
            $table->string('operation_type', 10);
            $table->string('stage', 30);
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['operation_type', 'stage', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stage_checklist_templates');
    }
};
