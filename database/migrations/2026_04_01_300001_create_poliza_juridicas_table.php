<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('poliza_juridicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_process_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('insurance_company')->nullable();
            $table->string('policy_number')->nullable();
            $table->string('status')->default('pending'); // pending, documents_submitted, in_review, approved, rejected, expired
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('review_started_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->string('currency', 3)->default('MXN');
            $table->date('coverage_start')->nullable();
            $table->date('coverage_end')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poliza_juridicas');
    }
};
