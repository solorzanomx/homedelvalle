<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mifiel_documentos', function (Blueprint $table) {
            $table->id();
            $table->string('document_id')->unique()->comment('ID que devuelve Mifiel');
            $table->string('tipo')->comment('ej: confidencialidad, exclusiva, compraventa');
            $table->foreignId('contacto_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->enum('status', ['pending', 'signed', 'rejected'])->default('pending');
            $table->string('pdf_path')->nullable()->comment('Ruta del PDF firmado en storage/app/');
            $table->json('mifiel_response')->nullable()->comment('Respuesta completa de Mifiel para debugging');
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mifiel_documentos');
    }
};
