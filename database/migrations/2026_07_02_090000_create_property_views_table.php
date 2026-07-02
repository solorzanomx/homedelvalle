<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('visitor_key', 64);   // hash del identificador del visitante anónimo (sesión)
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->string('referrer', 255)->nullable();
            $table->timestamp('viewed_at')->useCurrent();

            $table->index(['property_id', 'viewed_at']);
            $table->index(['property_id', 'visitor_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_views');
    }
};
