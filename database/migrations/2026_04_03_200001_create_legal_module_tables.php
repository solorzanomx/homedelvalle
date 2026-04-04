<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('legal_documents')) {
            Schema::create('legal_documents', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug', 120)->unique();
                $table->enum('type', ['aviso_privacidad', 'terminos_condiciones', 'contrato', 'otro']);
                $table->boolean('is_public')->default(true);
                $table->enum('status', ['draft', 'published'])->default('draft');
                $table->unsignedBigInteger('current_version_id')->nullable();
                $table->text('meta_description')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('legal_document_versions')) {
            Schema::create('legal_document_versions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('legal_document_id')->constrained('legal_documents')->cascadeOnDelete();
                $table->integer('version_number');
                $table->longText('content');
                $table->text('change_notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->boolean('is_active')->default(false);
                $table->timestamp('published_at')->nullable();
                $table->timestamps();

                $table->index(['legal_document_id', 'is_active']);
            });
        }

        // Now add the foreign key for current_version_id
        if (Schema::hasTable('legal_documents') && Schema::hasTable('legal_document_versions')) {
            Schema::table('legal_documents', function (Blueprint $table) {
                $table->foreign('current_version_id')
                    ->references('id')
                    ->on('legal_document_versions')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasTable('legal_acceptances')) {
            Schema::create('legal_acceptances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('legal_document_id')->constrained('legal_documents')->cascadeOnDelete();
                $table->foreignId('legal_document_version_id')->constrained('legal_document_versions')->cascadeOnDelete();
                $table->string('email', 255);
                $table->string('ip_address', 45);
                $table->text('user_agent')->nullable();
                $table->timestamp('accepted_at');
                $table->string('context', 100)->nullable();
                $table->json('extra_data')->nullable();
                $table->timestamps();

                $table->index(['email', 'legal_document_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_acceptances');

        if (Schema::hasTable('legal_documents')) {
            Schema::table('legal_documents', function (Blueprint $table) {
                $table->dropForeign(['current_version_id']);
            });
        }

        Schema::dropIfExists('legal_document_versions');
        Schema::dropIfExists('legal_documents');
    }
};
