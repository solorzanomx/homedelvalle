<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_signature_requests', function (Blueprint $table) {
            $table->id();
            $table->string('file_id')->unique()->comment('ID del archivo en Google Drive');
            $table->string('signature_request_id')->nullable()->comment('ID retornado por eSignature API (ej: requests/abc123)');
            $table->string('tipo')->comment('ej: confidencialidad, exclusiva, compraventa');
            $table->foreignId('contacto_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->enum('status', ['pending', 'completed', 'declined'])->default('pending');
            $table->json('signers')->comment('Array de firmantes: name, email, role');
            $table->string('document_name');
            $table->string('local_pdf_path')->nullable()->comment('Ruta relativa del PDF descargado en storage/app/');
            $table->json('google_response')->nullable()->comment('Respuesta completa de las APIs de Google para debugging');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_signature_requests');
    }
};
