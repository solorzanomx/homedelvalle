<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('tenant_client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('broker_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('stage')->default('captacion');
            $table->decimal('monthly_rent', 12, 2)->nullable();
            $table->string('currency', 10)->default('MXN');
            $table->decimal('deposit_amount', 12, 2)->nullable();
            $table->decimal('commission_amount', 12, 2)->nullable();
            $table->decimal('commission_percentage', 5, 2)->nullable();
            $table->string('guarantee_type', 30)->default('deposito'); // deposito, poliza_juridica, fianza
            $table->date('lease_start_date')->nullable();
            $table->date('lease_end_date')->nullable();
            $table->integer('lease_duration_months')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_processes');
    }
};
