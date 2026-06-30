<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_investigations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_process_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // Perfil laboral
            $table->string('occupation')->nullable();
            $table->string('employer')->nullable();
            $table->unsignedSmallInteger('employment_years')->nullable();
            $table->enum('income_type', ['employed', 'self_employed', 'business_owner', 'pension', 'other'])->nullable();

            // Perfil financiero
            $table->decimal('monthly_income', 12, 2)->nullable();
            $table->boolean('income_verified')->default(false);
            $table->enum('credit_status', ['excellent', 'good', 'regular', 'poor'])->nullable();
            $table->boolean('bureau_checked')->default(false);
            $table->text('credit_notes')->nullable();

            // Referencias
            $table->unsignedTinyInteger('references_count')->default(0);
            $table->boolean('references_ok')->default(false);
            $table->text('references_notes')->nullable();

            // Opinión del asesor
            $table->enum('asesor_recommendation', ['approve', 'conditional', 'decline'])->nullable();
            $table->text('asesor_notes')->nullable();  // carta al propietario

            // Control de visibilidad
            $table->boolean('visible_to_owner')->default(false);
            $table->timestamp('presented_at')->nullable(); // cuando se activó visible_to_owner

            // Decisión del propietario
            $table->enum('owner_decision', ['pending', 'approved', 'declined', 'more_info'])->default('pending');
            $table->timestamp('owner_decision_at')->nullable();
            $table->text('owner_decision_notes')->nullable();

            $table->timestamps();
        });

        Schema::table('rental_processes', function (Blueprint $table) {
            $table->timestamp('proposed_tenant_at')->nullable()->after('notes');
            $table->timestamp('tenant_approved_at')->nullable()->after('proposed_tenant_at');
        });
    }

    public function down(): void
    {
        Schema::table('rental_processes', function (Blueprint $table) {
            $table->dropColumn(['proposed_tenant_at', 'tenant_approved_at']);
        });
        Schema::dropIfExists('tenant_investigations');
    }
};
