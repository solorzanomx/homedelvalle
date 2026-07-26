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
        Schema::create('birthday_email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('recipient_type'); // 'client', 'broker', 'provider_contact'
            $table->unsignedBigInteger('recipient_id');
            $table->unsignedSmallInteger('year');
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->unique(['recipient_type', 'recipient_id', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('birthday_email_logs');
    }
};
