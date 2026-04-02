<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operations', function (Blueprint $table) {
            $table->id();
            $table->string('type', 10);
            $table->string('phase', 20)->default('captacion');
            $table->string('stage', 30)->default('lead');
            $table->string('status', 20)->default('active');

            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('secondary_client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('broker_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->decimal('amount', 14, 2)->nullable();
            $table->decimal('monthly_rent', 12, 2)->nullable();
            $table->string('currency', 10)->default('MXN');
            $table->decimal('deposit_amount', 12, 2)->nullable();
            $table->decimal('commission_amount', 12, 2)->nullable();
            $table->decimal('commission_percentage', 5, 2)->nullable();
            $table->string('guarantee_type', 30)->nullable();

            $table->date('expected_close_date')->nullable();
            $table->date('lease_start_date')->nullable();
            $table->date('lease_end_date')->nullable();
            $table->integer('lease_duration_months')->nullable();

            $table->text('notes')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'stage']);
            $table->index(['status']);
            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operations');
    }
};
