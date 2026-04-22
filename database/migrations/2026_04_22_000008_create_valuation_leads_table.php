<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valuation_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('colonia_id')
                  ->nullable()
                  ->constrained('market_colonias')
                  ->nullOnDelete();
            $table->string('colonia_raw', 150)->nullable();
            $table->enum('property_type', ['apartment', 'house', 'land', 'office'])->default('apartment');
            $table->decimal('m2_approx', 8, 2)->nullable();

            $table->string('owner_name', 120);
            $table->string('owner_phone', 20);
            $table->string('owner_email', 150)->nullable();
            $table->text('message')->nullable();

            $table->string('source_page', 300)->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();

            $table->enum('status', ['new', 'contacted', 'qualified', 'converted', 'discarded'])
                  ->default('new');
            $table->foreignId('converted_property_id')
                  ->nullable()
                  ->constrained('properties')
                  ->nullOnDelete();
            $table->foreignId('assigned_to')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valuation_leads');
    }
};
