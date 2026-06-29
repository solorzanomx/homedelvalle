<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('zona_mercado_slug', 80)->nullable()->after('focus_keyword');
        });
    }
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('zona_mercado_slug');
        });
    }
};
