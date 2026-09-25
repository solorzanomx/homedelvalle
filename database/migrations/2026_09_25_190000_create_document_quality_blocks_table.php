<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('document_quality_blocks')) {
            return;
        }
        Schema::create('document_quality_blocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->nullable()->index();
            $table->string('category', 60)->nullable();
            $table->string('reason', 300);
            $table->boolean('bypassed')->default(false)->comment('true = tras varios rechazos se dejó pasar con marca');
            $table->timestamp('created_at')->useCurrent();
            $table->index('created_at');
        });
    }

    public function down(): void {
        Schema::dropIfExists('document_quality_blocks');
    }
};
