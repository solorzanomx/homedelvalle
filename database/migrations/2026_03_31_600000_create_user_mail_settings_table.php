<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_mail_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('smtp_server', 255)->nullable();
            $table->integer('port')->default(587);
            $table->string('username', 255)->nullable();
            $table->text('password')->nullable();
            $table->string('encryption', 10)->default('tls');
            $table->string('from_email', 255)->nullable();
            $table->string('from_name', 255)->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_mail_settings');
    }
};
