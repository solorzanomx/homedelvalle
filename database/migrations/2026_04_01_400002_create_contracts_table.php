<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_process_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contract_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('rental'); // rental, commission, renewal
            $table->string('title');
            $table->longText('generated_html')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('source')->default('generated'); // generated, uploaded
            $table->string('signature_status')->default('unsigned'); // unsigned, pending_signature, signed
            $table->json('signature_data')->nullable(); // {ip, user_agent, timestamp, signer_name, signer_email}
            $table->timestamp('signed_at')->nullable();
            $table->foreignId('signed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
