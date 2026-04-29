<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Fix: Heriberto Frias → Heriberto Frías (address field)
        DB::table('site_settings')
            ->whereRaw("address LIKE '%Heriberto Frias%'")
            ->update([
                'address' => DB::raw("REPLACE(address, 'Heriberto Frias', 'Heriberto Frías')"),
            ]);

        // Fix: business_model_steps JSON — accent erratas
        // "Ejecutamos la operacion" → "Ejecutamos la operación"
        // "Negociacion"            → "Negociación"
        DB::table('site_settings')
            ->whereNotNull('business_model_steps')
            ->whereRaw("business_model_steps LIKE '%Ejecutamos la operacion%' OR business_model_steps LIKE '%Negociacion%'")
            ->update([
                'business_model_steps' => DB::raw(
                    "REPLACE(REPLACE(business_model_steps, 'Ejecutamos la operacion', 'Ejecutamos la operación'), 'Negociacion', 'Negociación')"
                ),
            ]);
    }

    public function down(): void
    {
        // Accent fixes are not reversible
    }
};
