<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('clients', function (Blueprint $table) {
            if (! Schema::hasColumn('clients', 'occupants_count')) {
                $table->unsignedTinyInteger('occupants_count')->nullable()->comment('personas que habitarán el inmueble');
            }
            if (! Schema::hasColumn('clients', 'employer_name')) {
                $table->string('employer_name', 150)->nullable();
            }
            if (! Schema::hasColumn('clients', 'employer_address')) {
                $table->string('employer_address', 200)->nullable();
            }
            if (! Schema::hasColumn('clients', 'employer_phone')) {
                $table->string('employer_phone', 30)->nullable();
            }
            if (! Schema::hasColumn('clients', 'job_seniority')) {
                $table->string('job_seniority', 60)->nullable()->comment('antigüedad en la empresa, texto libre (ej. "14 años")');
            }
            if (! Schema::hasColumn('clients', 'other_income_amount')) {
                $table->decimal('other_income_amount', 12, 2)->nullable();
            }
            if (! Schema::hasColumn('clients', 'other_income_description')) {
                $table->string('other_income_description', 200)->nullable();
            }
            if (! Schema::hasColumn('clients', 'previous_landlord_name')) {
                $table->string('previous_landlord_name', 150)->nullable();
            }
            if (! Schema::hasColumn('clients', 'previous_landlord_phone')) {
                $table->string('previous_landlord_phone', 30)->nullable();
            }
            if (! Schema::hasColumn('clients', 'previous_landlord_mobile')) {
                $table->string('previous_landlord_mobile', 30)->nullable();
            }
            if (! Schema::hasColumn('clients', 'previous_landlord_email')) {
                $table->string('previous_landlord_email', 150)->nullable();
            }
            if (! Schema::hasColumn('clients', 'previous_landlord_years')) {
                $table->string('previous_landlord_years', 60)->nullable()->comment('tiempo de arrendamiento, texto libre (ej. "3 años")');
            }
        });
    }

    public function down(): void {
        Schema::table('clients', function (Blueprint $table) {
            $columns = [
                'occupants_count', 'employer_name', 'employer_address', 'employer_phone',
                'job_seniority', 'other_income_amount', 'other_income_description',
                'previous_landlord_name', 'previous_landlord_phone', 'previous_landlord_mobile',
                'previous_landlord_email', 'previous_landlord_years',
            ];
            $existing = array_filter($columns, fn($col) => Schema::hasColumn('clients', $col));
            if ($existing) {
                $table->dropColumn(array_values($existing));
            }
        });
    }
};
