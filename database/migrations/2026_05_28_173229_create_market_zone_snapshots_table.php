<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_zone_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_zone_id')
                  ->constrained('market_zones')
                  ->cascadeOnDelete();
            $table->enum('operation_type', ['sale', 'rent']);
            $table->string('property_type', 30);   // apartment|house|office
            $table->string('age_category', 10);    // new|mid|old
            $table->date('period');
            $table->decimal('price_m2_low',  10, 2)->nullable();
            $table->decimal('price_m2_avg',  10, 2)->nullable();
            $table->decimal('price_m2_high', 10, 2)->nullable();
            $table->unsignedSmallInteger('sample_size')->default(0);
            $table->unsignedSmallInteger('listings_found')->default(0);
            $table->string('confidence', 10)->nullable();   // high|medium|low
            $table->string('source', 30)->default('perplexity');
            $table->text('source_raw')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(
                ['market_zone_id', 'operation_type', 'property_type', 'age_category', 'period'],
                'mzs_zone_main_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_zone_snapshots');
    }
};
