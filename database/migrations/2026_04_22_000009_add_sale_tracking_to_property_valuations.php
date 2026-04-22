<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_valuations', function (Blueprint $table) {
            $table->decimal('actual_sale_price', 14, 2)->nullable()->after('suggested_list_price');
            $table->decimal('accuracy_pct', 6, 2)->nullable()->after('actual_sale_price')
                  ->comment('(actual - suggested) / suggested * 100. Negative = sold below prediction.');
            $table->timestamp('sale_recorded_at')->nullable()->after('accuracy_pct');
        });
    }

    public function down(): void
    {
        Schema::table('property_valuations', function (Blueprint $table) {
            $table->dropColumn(['actual_sale_price', 'accuracy_pct', 'sale_recorded_at']);
        });
    }
};
