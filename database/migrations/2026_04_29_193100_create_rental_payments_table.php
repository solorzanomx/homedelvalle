<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_process_id')->constrained()->cascadeOnDelete();
            $table->date('period');                          // Primer día del mes (2026-05-01)
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'paid', 'late', 'waived'])->default('pending');
            $table->date('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['rental_process_id', 'period']);
            $table->index(['rental_process_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_payments');
    }
};
