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
        Schema::create('carousel_image_prompts', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();        // 'cover','key_stat',…,'_global'
            $table->string('label');                // nombre para mostrar en UI
            $table->text('prompt');                 // cuerpo editable del prompt
            $table->boolean('is_global')->default(false); // true = se añade a TODOS los prompts
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carousel_image_prompts');
    }
};
