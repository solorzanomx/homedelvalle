<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('easybroker_settings', function (Blueprint $table) {
            $table->id();
            $table->text('api_key')->nullable();
            $table->string('base_url')->default('https://api.easybroker.com/v1');
            $table->boolean('auto_publish')->default(false);
            $table->string('default_property_type')->default('House');
            $table->string('default_operation_type')->default('sale');
            $table->string('default_currency')->default('MXN');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('easybroker_settings');
    }
};
