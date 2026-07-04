<?php

namespace App\Console\Commands;

use App\Models\Captacion;
use App\Models\Client;
use App\Models\Operation;
use App\Models\Property;
use App\Models\PropertyValuation;
use App\Models\PurchaseOffer;
use App\Models\User;
use App\Services\CaptacionIntakeService;
use Illuminate\Console\Command;

/**
 * Crea (o repara) el set fijo de datos de ejemplo usado por las vistas
 * previas de /admin/documentos — un Cliente/Propiedad claramente marcados
 * como "MUESTRA", con una Captación, una Valuación y una Oferta de Compra
 * de ejemplo. Idempotente: correrlo de nuevo no duplica nada.
 */
class SeedDemoDocuments extends Command
{
    protected $signature = 'documentos:seed-demo';
    protected $description = 'Crea los datos de ejemplo para las vistas previas del panel de Documentos de Marca';

    const DEMO_EMAIL = 'muestra@homedelvalle.mx';
    const DEMO_NAME  = '⚠️ MUESTRA — No usar';

    public function handle(): int
    {
        $agent = User::first();
        if (!$agent) {
            $this->error('No hay ningún usuario en el sistema para asignar como agente.');
            return self::FAILURE;
        }

        $client = Client::where('email', self::DEMO_EMAIL)->first();

        if (!$client) {
            $captacion = app(CaptacionIntakeService::class)->createFromCall([
                'name'            => self::DEMO_NAME,
                'phone'           => '5500000000',
                'email'           => self::DEMO_EMAIL,
                'property_type'   => 'House',
                'colony'          => 'Colonia Muestra',
                'city'            => 'CDMX',
                'intent'          => 'venta_residencial',
                'commission_pct'  => 5,
                'marketing_plan'  => 'Plan de ejemplo para vista previa.',
                'source'          => 'phone_call',
            ], $agent);

            $client   = $captacion->client;
            $property = $captacion->property;
            $property->update([
                'address' => 'Calle de Ejemplo 123',
                'title'   => 'Casa de muestra — no usar',
            ]);

            $this->info("Captación de ejemplo creada (id {$captacion->id}).");
        } else {
            $property  = $client->ownedProperties()->first();
            $captacion = $property ? Captacion::where('property_id', $property->id)->first() : null;
        }

        if (!$property) {
            $this->error('No se pudo resolver la propiedad de ejemplo.');
            return self::FAILURE;
        }

        $valuation = PropertyValuation::where('property_id', $property->id)->first();
        if (!$valuation) {
            $valuation = PropertyValuation::create([
                'property_id'          => $property->id,
                'total_value_low'      => 2800000,
                'total_value_mid'      => 3000000,
                'total_value_high'     => 3200000,
                'suggested_list_price' => 3100000,
                'status'               => 'final',
                'input_m2_total'       => 90,
                'input_age_years'      => 10,
                'diagnosis'            => 'on_market',
                'confidence'           => 'high',
                'base_price_m2'        => 33000,
                'adjusted_price_m2'    => 34000,
            ]);
            $this->info("Valuación de ejemplo creada (id {$valuation->id}).");
        }

        $buyerOperation = Operation::where('type', 'venta')
            ->where('property_id', $property->id)
            ->where('client_id', $client->id)
            ->first();

        if (!$buyerOperation) {
            $buyerOperation = Operation::create([
                'type'         => 'venta',
                'target_type'  => 'venta',
                'phase'        => 'operacion',
                'stage'        => 'candidatos',
                'status'       => 'active',
                'property_id'  => $property->id,
                'client_id'    => $client->id,
                'user_id'      => $agent->id,
                'amount'       => 3100000,
                'deposit_amount' => 50000,
                'currency'     => 'MXN',
            ]);
            $this->info("Operation de ejemplo (comprador) creada (id {$buyerOperation->id}).");
        }

        $offer = PurchaseOffer::where('operation_id', $buyerOperation->id)->first();
        if (!$offer) {
            $offer = PurchaseOffer::create([
                'operation_id'         => $buyerOperation->id,
                'precio_ofertado'      => 2950000,
                'monto_apartado'       => 50000,
                'pago_firma_contrato'  => 500000,
                'pago_firma_escritura' => 2400000,
                'forma_pago'           => 'Transferencia bancaria y crédito hipotecario preautorizado',
                'vigencia_dias'        => 5,
                'folio_real'           => '123456/1',
                'comentarios'          => 'Oferta de ejemplo para vista previa — no representa una operación real.',
                'offered_at'           => now(),
            ]);
            $this->info("Oferta de ejemplo creada (id {$offer->id}).");
        }

        $this->info('Datos de ejemplo listos.');
        return self::SUCCESS;
    }
}
