<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carousel_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carousel_post_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('version_number')->default(1);
            $table->string('label', 100)->nullable();              // e.g. "Borrador inicial", "Revisión 2"
            $table->json('snapshot');                              // full post + slides state at this point
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Now that carousel_versions exists, add the FK on carousel_posts
        Schema::table('carousel_posts', function (Blueprint $table) {
            $table->foreign('approved_version_id')->references('id')->on('carousel_versions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('carousel_posts', function (Blueprint $table) {
            $table->dropForeign(['approved_version_id']);
        });
        Schema::dropIfExists('carousel_versions');
    }
};
