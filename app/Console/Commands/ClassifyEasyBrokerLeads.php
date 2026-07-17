<?php

namespace App\Console\Commands;

use App\Models\FormSubmission;
use App\Models\Property;
use App\Services\AILeadClassifierService;
use Illuminate\Console\Command;

/**
 * Clasifica con IA los leads de EasyBroker YA importados que aún no tienen
 * clasificación (payload.ai_rol vacío) — el backlog anterior a que el sync
 * clasificara al importar. Idempotente: los ya clasificados se saltan.
 *
 * Actualiza: payload.ai_rol / ai_resumen / posible_broker, lead_temperature,
 * lead_tag (LEAD_BROKER si la IA detecta broker) y client_type. Respeta los
 * leads ya trabajados: si el status no es 'new', solo agrega la clasificación
 * al payload sin tocar etiquetas ni temperatura.
 */
class ClassifyEasyBrokerLeads extends Command
{
    protected $signature = 'easybroker:classify-leads {--limit=100 : Máximo de leads a clasificar en esta corrida}';

    protected $description = 'Clasifica con IA los leads de EasyBroker importados sin clasificación';

    public function handle(AILeadClassifierService $classifier, \App\Services\EasyBrokerService $eb): int
    {
        $pendientes = FormSubmission::where('form_type', 'easybroker')
            ->where(function ($q) {
                $q->whereNull('payload->ai_rol')->orWhere('payload->ai_rol', '');
            })
            ->orderByDesc('created_at')
            ->limit((int) $this->option('limit'))
            ->get();

        if ($pendientes->isEmpty()) {
            $this->info('No hay leads de EasyBroker pendientes de clasificar.');

            return self::SUCCESS;
        }

        $clasificados = 0;
        $fallidos     = 0;

        foreach ($pendientes as $lead) {
            $payload = $lead->payload ?? [];

            $localProperty = ! empty($payload['propiedad_local_id'])
                ? Property::find($payload['propiedad_local_id'])
                : null;

            // Enriquecer con los detalles de EasyBroker (operación venta/renta,
            // precio, título) si la propiedad no está en el CRM y el lead aún
            // no los tiene — el backlog viejo se importó sin ellos.
            if (! $localProperty && empty($payload['eb_titulo']) && ! empty($payload['eb_property_id'])) {
                $raw = $eb->getProperty($payload['eb_property_id']);
                if ($raw) {
                    $resumenProp = \App\Services\EasyBrokerService::summarizeProperty($raw);
                    $payload['eb_titulo']    = $resumenProp['titulo'];
                    $payload['eb_operacion'] = $resumenProp['operacion'];
                    $payload['eb_precio']    = $resumenProp['precio'];
                    $payload['eb_ubicacion'] = $resumenProp['ubicacion'];
                    $payload['eb_url']       = $resumenProp['url'];
                }
            }

            $contextoPropiedad = match (true) {
                (bool) $localProperty            => sprintf('%s (%s, %s $%s %s)', $localProperty->title, $localProperty->operation_type === 'rental' ? 'renta' : 'venta', $localProperty->colony ?: $localProperty->city, number_format((float) $localProperty->price), $localProperty->currency),
                ! empty($payload['eb_titulo'])   => sprintf('%s (%s, %s, %s)', $payload['eb_titulo'], $payload['eb_operacion'] ?? 'operación desconocida', $payload['eb_ubicacion'] ?? '', $payload['eb_precio'] ?? ''),
                default                          => $payload['eb_property_id'] ?? 'desconocida',
            };

            $ai = $classifier->classifyPortalLead([
                'nombre'    => $lead->full_name,
                'email'     => $lead->email,
                'mensaje'   => $payload['mensaje'] ?? '',
                'portal'    => $payload['portal_origen'] ?? 'EasyBroker',
                'propiedad' => $contextoPropiedad,
            ]);

            if (! $ai['ok']) {
                $fallidos++;
                $this->warn("Lead {$lead->id} ({$lead->full_name}): la IA no respondió — se reintenta en la próxima corrida.");
                continue;
            }

            $esBroker = $ai['rol'] === 'broker_colaboracion';

            $payload['ai_rol']         = $ai['rol'];
            $payload['ai_resumen']     = $ai['resumen'];
            $payload['posible_broker'] = $esBroker || ! empty($payload['posible_broker']);

            $updates = ['payload' => $payload];

            // Solo re-etiquetar leads aún no trabajados — si Alejandro ya lo
            // movió de estado, la clasificación es informativa, no invasiva.
            if ($lead->status === 'new') {
                $updates['lead_temperature'] = $esBroker || $ai['rol'] === 'spam' ? 'cold' : $ai['temperatura'];
                if ($esBroker) {
                    $updates['lead_tag']    = 'LEAD_BROKER';
                    $updates['client_type'] = null;
                } elseif ($ai['rol'] === 'inquilino') {
                    $updates['client_type'] = 'renter';
                }
            }

            FormSubmission::withoutEvents(fn () => $lead->update($updates));
            $clasificados++;

            $this->line("  {$lead->full_name} → {$ai['rol']} / {$ai['temperatura']}" . ($ai['resumen'] ? " — {$ai['resumen']}" : ''));

            // Respiro entre llamadas para no rozar el rate limit de Gemini
            usleep(400_000);
        }

        $this->info("Clasificados: {$clasificados} | Sin respuesta de IA: {$fallidos}.");

        return self::SUCCESS;
    }
}
