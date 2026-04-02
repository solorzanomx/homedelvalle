<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('help_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug', 80)->unique();
            $table->string('icon', 10)->nullable(); // emoji
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('help_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('help_category_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug', 120)->unique();
            $table->longText('content');           // markdown
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->unsignedInteger('view_count')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('help_tips', function (Blueprint $table) {
            $table->id();
            $table->string('context', 80);         // route or feature key: "campaigns.create", "segments.index", "pipeline"
            $table->string('title');
            $table->text('content');
            $table->string('type', 20)->default('tip');  // tip, warning, pro_tip
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('context');
        });

        Schema::create('help_onboarding_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->json('completed_steps')->nullable(); // ["welcome","first_client","first_campaign"]
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('help_onboarding_progress');
        Schema::dropIfExists('help_tips');
        Schema::dropIfExists('help_articles');
        Schema::dropIfExists('help_categories');
    }
};
