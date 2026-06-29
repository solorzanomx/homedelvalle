<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('colonia_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();                  // narvarte, del-valle, napoles
            $table->string('name');                            // Narvarte Poniente
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('heading')->nullable();
            $table->string('subheading')->nullable();
            $table->text('about')->nullable();                 // Descripción del barrio (HTML)
            $table->json('faqs')->nullable();                  // [{'q':'...','a':'...'}]
            $table->string('colony_search_terms')->nullable(); // "narvarte,narvarte poniente" — para filtrar propiedades
            $table->boolean('is_published')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('colonia_pages');
    }
};
