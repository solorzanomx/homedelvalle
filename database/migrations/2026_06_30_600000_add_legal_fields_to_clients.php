<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {

            // --- Nombre desglosado ---
            if (! Schema::hasColumn('clients', 'first_name')) {
                $table->string('first_name', 100)->nullable()->after('name');
            }
            if (! Schema::hasColumn('clients', 'last_name_paterno')) {
                $table->string('last_name_paterno', 100)->nullable()->after('first_name');
            }
            if (! Schema::hasColumn('clients', 'last_name_materno')) {
                $table->string('last_name_materno', 100)->nullable()->after('last_name_paterno');
            }

            // --- Datos personales ---
            if (! Schema::hasColumn('clients', 'birth_date')) {
                $table->date('birth_date')->nullable()->after('last_name_materno');
            }
            if (! Schema::hasColumn('clients', 'birth_state')) {
                $table->string('birth_state', 50)->nullable()->after('birth_date');
            }
            if (! Schema::hasColumn('clients', 'gender')) {
                $table->enum('gender', ['H', 'M'])->nullable()->after('birth_state');
            }
            if (! Schema::hasColumn('clients', 'nationality')) {
                $table->string('nationality', 50)->nullable()->default('mexicana')->after('gender');
            }
            if (! Schema::hasColumn('clients', 'marital_status')) {
                $table->enum('marital_status', ['soltero', 'casado', 'divorciado', 'viudo', 'union_libre'])
                      ->nullable()->after('nationality');
            }
            if (! Schema::hasColumn('clients', 'occupation')) {
                $table->string('occupation', 120)->nullable()->after('marital_status');
            }

            // --- Identificación oficial ---
            if (! Schema::hasColumn('clients', 'id_type')) {
                $table->enum('id_type', ['INE', 'pasaporte', 'cedula_profesional', 'otro'])
                      ->nullable()->after('occupation');
            }
            if (! Schema::hasColumn('clients', 'id_number')) {
                $table->string('id_number', 60)->nullable()->after('id_type');
            }
            if (! Schema::hasColumn('clients', 'id_expiry')) {
                $table->date('id_expiry')->nullable()->after('id_number');
            }

            // --- Domicilio estructurado ---
            if (! Schema::hasColumn('clients', 'address_street')) {
                $table->string('address_street', 200)->nullable()->after('id_expiry');
            }
            if (! Schema::hasColumn('clients', 'address_colony')) {
                $table->string('address_colony', 100)->nullable()->after('address_street');
            }
            if (! Schema::hasColumn('clients', 'address_municipality')) {
                $table->string('address_municipality', 100)->nullable()->after('address_colony');
            }
            if (! Schema::hasColumn('clients', 'address_state')) {
                $table->string('address_state', 60)->nullable()->after('address_municipality');
            }
            if (! Schema::hasColumn('clients', 'address_zip')) {
                $table->string('address_zip', 5)->nullable()->after('address_state');
            }

            // --- Contratos de renta ---
            if (! Schema::hasColumn('clients', 'marital_regime')) {
                $table->enum('marital_regime', ['separacion_bienes', 'sociedad_conyugal'])
                      ->nullable()->after('address_zip');
            }
            if (! Schema::hasColumn('clients', 'spouse_name')) {
                $table->string('spouse_name', 200)->nullable()->after('marital_regime');
            }
            if (! Schema::hasColumn('clients', 'spouse_curp')) {
                $table->string('spouse_curp', 18)->nullable()->after('spouse_name');
            }
            if (! Schema::hasColumn('clients', 'bank_clabe')) {
                $table->string('bank_clabe', 18)->nullable()->after('spouse_curp');
            }
            if (! Schema::hasColumn('clients', 'bank_name')) {
                $table->string('bank_name', 80)->nullable()->after('bank_clabe');
            }

            // --- Verificación ---
            if (! Schema::hasColumn('clients', 'curp_verified_at')) {
                $table->timestamp('curp_verified_at')->nullable()->after('rfc');
            }
            if (! Schema::hasColumn('clients', 'rfc_verified_at')) {
                $table->timestamp('rfc_verified_at')->nullable()->after('curp_verified_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $columns = [
                'first_name',
                'last_name_paterno',
                'last_name_materno',
                'birth_date',
                'birth_state',
                'gender',
                'nationality',
                'marital_status',
                'occupation',
                'id_type',
                'id_number',
                'id_expiry',
                'address_street',
                'address_colony',
                'address_municipality',
                'address_state',
                'address_zip',
                'marital_regime',
                'spouse_name',
                'spouse_curp',
                'bank_clabe',
                'bank_name',
                'curp_verified_at',
                'rfc_verified_at',
            ];

            $existing = array_filter($columns, fn ($col) => Schema::hasColumn('clients', $col));

            if ($existing) {
                $table->dropColumn(array_values($existing));
            }
        });
    }
};
