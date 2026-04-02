<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // broker que envia
            $table->string('subject');
            $table->longText('body_html');
            $table->json('property_ids')->nullable(); // IDs de propiedades enviadas
            $table->uuid('tracking_id')->unique(); // para open tracking pixel
            $table->timestamp('opened_at')->nullable();
            $table->integer('open_count')->default(0);
            $table->string('status')->default('sent'); // sent, failed
            $table->timestamps();

            $table->index('tracking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_emails');
    }
};
