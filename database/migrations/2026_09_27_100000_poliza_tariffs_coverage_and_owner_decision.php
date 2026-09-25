<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Hoja de Servicios de Previsión Legal "AM QRO NL 2026" (2026-09-27): tarifas por RANGO DE RENTA, gastos de emisión
 * y la matriz oficial de cobertura. Reemplaza la cobertura tomada de la página web (que no coincidía con la hoja) y el
 * precio fijo por plan. Además, la decisión del plan y del reparto del costo pasa al PROPIETARIO.
 */
return new class extends Migration {
    public function up(): void {
        if (! Schema::hasTable('poliza_tariff_sheets')) {
            Schema::create('poliza_tariff_sheets', function (Blueprint $table) {
                $table->id();
                $table->string('name', 150);
                $table->string('zone_label', 200)->nullable();
                $table->unsignedSmallInteger('valid_year')->nullable();
                $table->decimal('emission_fee', 10, 2)->default(0)->comment('gastos de emisión por trámite; se acreditan al precio si la operación se concreta');
                $table->boolean('is_active')->default(true);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('poliza_rates')) {
            Schema::create('poliza_rates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('poliza_tariff_sheet_id')->constrained('poliza_tariff_sheets')->cascadeOnDelete();
                $table->foreignId('poliza_plan_id')->constrained('poliza_plans')->cascadeOnDelete();
                $table->decimal('rent_over', 12, 2)->comment('renta mensual MAYOR que esto…');
                $table->decimal('rent_up_to', 12, 2)->nullable()->comment('…y menor o igual a esto (null = sin tope)');
                $table->decimal('fixed_price', 12, 2)->nullable();
                $table->decimal('percent', 6, 2)->nullable()->comment('% de la renta mensual (rangos sin tope)');
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
                $table->index(['poliza_tariff_sheet_id', 'poliza_plan_id', 'rent_over']);
            });
        }
        if (! Schema::hasTable('poliza_coverages')) {
            Schema::create('poliza_coverages', function (Blueprint $table) {
                $table->id();
                $table->string('section', 20)->comment('incluye | incumplimiento');
                $table->string('label', 300);
                $table->string('note', 200)->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('poliza_coverage_plan')) {
            Schema::create('poliza_coverage_plan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('poliza_coverage_id')->constrained('poliza_coverages')->cascadeOnDelete();
                $table->foreignId('poliza_plan_id')->constrained('poliza_plans')->cascadeOnDelete();
                $table->boolean('included')->default(false);
                $table->unique(['poliza_coverage_id', 'poliza_plan_id']);
            });
        }

        Schema::table('rental_processes', function (Blueprint $table) {
            $add = [
                'poliza_tenant_share' => fn() => $table->unsignedTinyInteger('poliza_tenant_share')->nullable()->comment('% de la póliza que paga el inquilino (100 o 50)'),
                'poliza_decided_by' => fn() => $table->string('poliza_decided_by', 20)->nullable()->comment('owner | advisor'),
                'poliza_quote_amount' => fn() => $table->decimal('poliza_quote_amount', 12, 2)->nullable()->comment('precio del plan al decidir (foto)'),
                'poliza_emission_fee' => fn() => $table->decimal('poliza_emission_fee', 10, 2)->nullable(),
                'poliza_tariff_sheet_id' => fn() => $table->unsignedBigInteger('poliza_tariff_sheet_id')->nullable(),
                'poliza_payment_mode' => fn() => $table->string('poliza_payment_mode', 10)->default('direct')->comment('direct = cada parte paga a Previsión Legal; hdv = Home del Valle cobra y liquida'),
                'poliza_tenant_paid_at' => fn() => $table->timestamp('poliza_tenant_paid_at')->nullable(),
                'poliza_owner_paid_at' => fn() => $table->timestamp('poliza_owner_paid_at')->nullable(),
            ];
            foreach ($add as $col => $fn) {
                if (! Schema::hasColumn('rental_processes', $col)) {
                    $fn();
                }
            }
        });

        // ── Datos de la hoja ─────────────────────────────────────────────────────────
        if (DB::table('poliza_tariff_sheets')->exists()) {
            return;
        }
        $now = now();
        $sheetId = DB::table('poliza_tariff_sheets')->insertGetId([
            'name' => 'Hoja de Servicios AM QRO NL 2026', 'zone_label' => 'Área Metropolitana, Querétaro y Nuevo León', 'valid_year' => 2026,
            'emission_fee' => 1700, 'is_active' => true,
            'notes' => 'Para todo trámite es necesario cubrir los gastos de emisión ($1,700); se cobran en caso de no concretarse la operación. Rentas desde $30,001: porcentaje de la renta mensual.',
            'created_at' => $now, 'updated_at' => $now,
        ]);

        $planIds = [];
        foreach (['Básica' => 1, 'Superior' => 2, 'Integral' => 3] as $name => $order) {
            $id = DB::table('poliza_plans')->where('name', $name)->value('id');
            if (! $id) {
                $id = DB::table('poliza_plans')->insertGetId(['provider_name' => 'Previsión Legal', 'name' => $name, 'sort_order' => $order, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
            }
            $planIds[$name] = $id;
        }
        // Los planes se ofrecen por TARIFA según la renta (ya no por un precio fijo por plan).
        DB::table('poliza_plans')->whereIn('id', array_values($planIds))->update([
            'is_active' => true, 'price' => null, 'show_price_public' => true, 'updated_at' => $now,
        ]);
        DB::table('poliza_plans')->where('id', $planIds['Básica'])->update(['tagline' => null, 'description' => 'Antecedentes legales, contrato y firma digital, cobranza extrajudicial y proceso judicial por falta de pago o abandono, con honorarios y gastos incluidos.']);
        DB::table('poliza_plans')->where('id', $planIds['Superior'])->update(['tagline' => 'La más común', 'is_recommended' => true, 'description' => 'Suma antecedentes crediticios y los procesos judiciales por vencimiento del contrato y por Ley de Extinción de Dominio.']);
        DB::table('poliza_plans')->where('id', $planIds['Integral'])->update(['tagline' => 'La más completa', 'description' => 'Suma la cobranza judicial de rentas o servicios (pagarés).']);

        // Tarifas por rango de renta mensual (pesos): [más de, hasta, Básica, Superior, Integral]
        $ranges = [
            [0, 7000, 3950, 5750, 7000],
            [7000, 10000, 4600, 6600, 8500],
            [10000, 15000, 5100, 7300, 9750],
            [15000, 20000, 5600, 8000, 11350],
            [20000, 25000, 5850, 8250, 13200],
            [25000, 30000, 6200, 8400, 15000],
        ];
        foreach ($ranges as $i => [$over, $upTo, $b, $s, $in]) {
            foreach (['Básica' => $b, 'Superior' => $s, 'Integral' => $in] as $name => $price) {
                DB::table('poliza_rates')->insert(['poliza_tariff_sheet_id' => $sheetId, 'poliza_plan_id' => $planIds[$name], 'rent_over' => $over, 'rent_up_to' => $upTo, 'fixed_price' => $price, 'sort_order' => $i, 'created_at' => $now, 'updated_at' => $now]);
            }
        }
        foreach (['Básica' => 21, 'Superior' => 29.5, 'Integral' => 50] as $name => $pct) {
            DB::table('poliza_rates')->insert(['poliza_tariff_sheet_id' => $sheetId, 'poliza_plan_id' => $planIds[$name], 'rent_over' => 30000, 'rent_up_to' => null, 'percent' => $pct, 'sort_order' => 6, 'created_at' => $now, 'updated_at' => $now]);
        }

        // Matriz oficial de cobertura: [sección, concepto, nota, Básica, Superior, Integral]
        $asterisk = 'Sujeto a disponibilidad o previa cita.';
        $matrix = [
            ['incluye', 'Consulta de antecedentes legales del inquilino', null, 1, 1, 1],
            ['incluye', 'Consulta de antecedentes crediticios del inquilino', null, 0, 1, 1],
            ['incluye', 'Asesoría legal personalizada y elaboración del contrato de arrendamiento', null, 1, 1, 1],
            ['incluye', 'Firma digital con cotejo biométrico de los firmantes', null, 1, 1, 1],
            ['incluye', 'Asistencia personalizada de un abogado a la firma del contrato', $asterisk, 1, 1, 1],
            ['incumplimiento', 'Intervención extrajudicial apegada a la legislación aplicable conforme a la cobertura contratada', null, 1, 1, 1],
            ['incumplimiento', 'Cobranza extrajudicial de rentas y servicios en el inmueble no pagados', null, 1, 1, 1],
            ['incumplimiento', 'Asistencia personalizada de un abogado a la firma del convenio de devolución', $asterisk, 1, 1, 1],
            ['incumplimiento', 'Proceso judicial para recuperar el inmueble rentado por falta de pago de rentas', null, 1, 1, 1],
            ['incumplimiento', 'Proceso judicial para recuperar el inmueble rentado por abandono', null, 1, 1, 1],
            ['incumplimiento', 'Proceso judicial para recuperar el inmueble rentado por vencimiento del contrato', null, 0, 1, 1],
            ['incumplimiento', 'Proceso judicial para recuperar el inmueble rentado ante la aplicación de la Ley Nacional de Extinción de Dominio', null, 0, 1, 1],
            ['incumplimiento', 'Cobranza judicial de rentas o servicios en el inmueble no pagados (pagarés)', null, 0, 0, 1],
            ['incumplimiento', 'Honorarios de los abogados', null, 1, 1, 1],
            ['incumplimiento', 'Gastos del juicio', null, 1, 1, 1],
            ['incumplimiento', 'Gastos del desalojo o lanzamiento del inmueble en renta', null, 1, 1, 1],
        ];
        foreach ($matrix as $i => [$section, $label, $note, $b, $s, $in]) {
            $cid = DB::table('poliza_coverages')->insertGetId(['section' => $section, 'label' => $label, 'note' => $note, 'sort_order' => $i, 'created_at' => $now, 'updated_at' => $now]);
            foreach (['Básica' => $b, 'Superior' => $s, 'Integral' => $in] as $name => $inc) {
                DB::table('poliza_coverage_plan')->insert(['poliza_coverage_id' => $cid, 'poliza_plan_id' => $planIds[$name], 'included' => (bool) $inc]);
            }
        }
    }

    public function down(): void {
        Schema::table('rental_processes', function (Blueprint $table) {
            $cols = array_filter(['poliza_tenant_share', 'poliza_decided_by', 'poliza_quote_amount', 'poliza_emission_fee', 'poliza_tariff_sheet_id', 'poliza_payment_mode', 'poliza_tenant_paid_at', 'poliza_owner_paid_at'], fn($c) => Schema::hasColumn('rental_processes', $c));
            if ($cols) {
                $table->dropColumn(array_values($cols));
            }
        });
        Schema::dropIfExists('poliza_coverage_plan');
        Schema::dropIfExists('poliza_coverages');
        Schema::dropIfExists('poliza_rates');
        Schema::dropIfExists('poliza_tariff_sheets');
    }
};
