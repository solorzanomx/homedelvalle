<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->foreignId('rental_process_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Not reverted to NOT NULL — would break sale contracts that only have operation_id.
    }
};
