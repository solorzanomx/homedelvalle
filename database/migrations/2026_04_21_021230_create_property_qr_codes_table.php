<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('property_qr_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->string('qr_code_path')->nullable()->comment('Path to QR image (PNG/SVG)');
            $table->string('qr_url')->comment('The URL encoded in the QR code');
            $table->timestamp('generated_at')->nullable()->comment('When the QR was generated');
            $table->timestamps();

            // Index for quick lookups
            $table->unique('property_id');
            $table->index('generated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_qr_codes');
    }
};
