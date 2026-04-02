<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_settings', function (Blueprint $table) {
            $table->id();
            $table->string('smtp_server')->default('smtp.gmail.com');
            $table->integer('port')->default(587);
            $table->string('from_email')->nullable();
            $table->string('from_name')->nullable();
            $table->text('password')->nullable();
            $table->boolean('enable_ssl')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_settings');
    }
};
