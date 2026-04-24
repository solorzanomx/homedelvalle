<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('focus_keyword', 150)->nullable()->after('meta_description');
            $table->json('secondary_keywords')->nullable()->after('focus_keyword');
            $table->unsignedTinyInteger('seo_score')->nullable()->after('secondary_keywords');
            $table->unsignedTinyInteger('reading_time')->nullable()->after('seo_score');
            $table->string('schema_type', 50)->default('Article')->after('reading_time');
            $table->json('image_prompts')->nullable()->after('schema_type');
            $table->json('internal_links')->nullable()->after('image_prompts');
            $table->boolean('ai_generated')->default(false)->after('internal_links');
            $table->string('ai_generation_status', 30)->nullable()->after('ai_generated');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['focus_keyword','secondary_keywords','seo_score','reading_time','schema_type','image_prompts','internal_links','ai_generated','ai_generation_status']);
        });
    }
};
