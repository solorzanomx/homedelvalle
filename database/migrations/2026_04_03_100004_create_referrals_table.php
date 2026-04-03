<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('referrals')) {
            Schema::create('referrals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('referrer_id')->constrained()->cascadeOnDelete();
                $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('operation_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
                $table->decimal('commission_percentage', 5, 2)->default(0);
                $table->decimal('commission_amount', 12, 2)->default(0);
                $table->enum('status', ['pending', 'approved', 'paid'])->default('pending');
                $table->timestamp('paid_at')->nullable();
                $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
