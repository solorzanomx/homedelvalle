<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->longText('generated_html')->nullable();
            $table->string('pdf_path')->nullable();
            $table->json('clauses_snapshot')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('generation_note')->nullable();
            $table->string('signature_status')->default('unsigned'); // unsigned, pending_signature, signed
            $table->json('signature_data')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->foreignId('signed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['contract_id', 'version_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_versions');
    }
};
