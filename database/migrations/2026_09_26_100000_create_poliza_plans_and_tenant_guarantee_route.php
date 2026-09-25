<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (! Schema::hasTable('poliza_plans')) {
            Schema::create('poliza_plans', function (Blueprint $table) {
                $table->id();
                $table->string('provider_name', 100)->default('Previsión Legal');
                $table->string('name', 100);
                $table->string('tagline', 120)->nullable();
                $table->decimal('price', 10, 2)->nullable();
                $table->string('currency', 3)->default('MXN');
                $table->json('inclusions')->nullable()->comment('lista de textos: qué incluye');
                $table->text('description')->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(false)->comment('solo los activos CON precio se muestran en el Portal');
                $table->boolean('is_recommended')->default(false);
                $table->timestamps();
            });

            // Arranque con los 3 esquemas de Previsión Legal (previsionlegal.mx, póliza jurídica de arrendamiento
            // habitacional): Básica, Superior e Integral. La página no publica precios (varían por estado): Superior
            // es la "media" más común ($6,000, dato de Alejandro); Básica e Integral quedan ocultas hasta que se les
            // ponga precio en el CRM. El alcance crece de plan a plan (cobranza extrajudicial, juicio de recuperación,
            // honorarios y gastos de juicio) — el detalle por plan lo completa el CRM, no se inventa aquí.
            $now = now();
            $shared = 'Incluye investigación del inquilino, contrato personalizado, asesoría jurídica y firma digital; el alcance de cobranza y procesos legales crece con el plan.';
            DB::table('poliza_plans')->insert([
                ['name' => 'Básica', 'tagline' => null, 'price' => null, 'description' => $shared, 'sort_order' => 1, 'is_active' => false, 'is_recommended' => false, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Superior', 'tagline' => 'La más común', 'price' => 6000, 'description' => $shared, 'sort_order' => 2, 'is_active' => true, 'is_recommended' => true, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Integral', 'tagline' => 'La más completa', 'price' => null, 'description' => $shared, 'sort_order' => 3, 'is_active' => false, 'is_recommended' => false, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        Schema::table('rental_processes', function (Blueprint $table) {
            if (! Schema::hasColumn('rental_processes', 'tenant_has_aval')) {
                $table->boolean('tenant_has_aval')->nullable()->comment('declaración del inquilino: ¿tiene aval con inmueble en CDMX? false = póliza');
            }
            if (! Schema::hasColumn('rental_processes', 'guarantee_declared_at')) {
                $table->timestamp('guarantee_declared_at')->nullable();
            }
            if (! Schema::hasColumn('rental_processes', 'poliza_plan_id')) {
                $table->unsignedBigInteger('poliza_plan_id')->nullable()->index();
            }
            if (! Schema::hasColumn('rental_processes', 'poliza_plan_selected_at')) {
                $table->timestamp('poliza_plan_selected_at')->nullable();
            }
        });
    }

    public function down(): void {
        Schema::table('rental_processes', function (Blueprint $table) {
            $cols = array_filter(['tenant_has_aval', 'guarantee_declared_at', 'poliza_plan_id', 'poliza_plan_selected_at'], fn($c) => Schema::hasColumn('rental_processes', $c));
            if ($cols) {
                $table->dropColumn(array_values($cols));
            }
        });
        Schema::dropIfExists('poliza_plans');
    }
};
