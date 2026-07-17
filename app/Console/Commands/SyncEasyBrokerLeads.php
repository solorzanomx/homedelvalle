<?php

namespace App\Console\Commands;

use App\Models\FormSubmission;
use App\Models\Property;
use App\Services\EasyBrokerService;
use Illuminate\Console\Command;

/**
 * Trae las solicitudes de contacto de EasyBroker (leads que preguntan por
 * propiedades en EB y sus portales vinculados) y las registra como
 * FormSubmission (form_type 'easybroker') para que aparezcan en
 * Leads & Formularios como cualquier otro lead.
 *
 * Idempotente: cada solicitud lleva su id de EasyBroker en
 * payload.eb_request_id y se salta si ya existe. Corre por el scheduler
 * cada 30 min (routes/console.php). NO dispara AutomationEngine a
 * propósito: estos leads no aceptaron nuestro aviso de privacidad y ya
 * fueron contactados por el portal — el broker decide el seguimiento.
 */
class SyncEasyBrokerLeads extends Command
{
    protected $signature = 'easybroker:sync-leads
        {--pages=3 : Máximo de páginas (50 leads c/u) a revisar}
        {--dias=30 : Ignorar solicitudes más antiguas que N días (evita inundar el CRM con histórico)}';

    protected $description = 'Registra en el CRM las solicitudes de contacto de EasyBroker';

    private \App\Services\AILeadClassifierService $classifier;

    public function handle(EasyBrokerService $eb, \App\Services\AILeadClassifierService $classifier): int
    {
        $this->classifier = $classifier;

        if (! $eb->isConfigured()) {
            $this->warn('EasyBroker no está configurado — nada que sincronizar.');

            return self::SUCCESS;
        }

        $created = 0;
        $skipped = 0;
        $cutoff  = now()->subDays((int) $this->option('dias'));

        for ($page = 1; $page <= (int) $this->option('pages'); $page++) {
            $result = $eb->contactRequests($page);

            if (! $result['success']) {
                $this->error('EasyBroker respondió error: ' . ($result['message'] ?? 'desconocido'));

                return self::FAILURE;
            }

            if (empty($result['data'])) {
                break;
            }

            $newInPage = 0;
            foreach ($result['data'] as $cr) {
                // Vienen de más reciente a más antigua: al cruzar el corte de
                // antigüedad ya no hay nada más que importar.
                if (! empty($cr['happened_at']) && \Carbon\Carbon::parse($cr['happened_at'])->lt($cutoff)) {
                    $this->info("EasyBroker leads: {$created} nuevos, {$skipped} ya existentes (corte de {$this->option('dias')} días alcanzado).");

                    return self::SUCCESS;
                }

                if ($this->alreadyImported($cr['id'])) {
                    $skipped++;
                    continue;
                }

                try {
                    $this->import($cr);
                    $created++;
                    $newInPage++;
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('easybroker:sync-leads — lead no importado', [
                        'eb_request_id' => $cr['id'] ?? null,
                        'error'         => $e->getMessage(),
                    ]);
                    $this->warn("Lead {$cr['id']} no importado: {$e->getMessage()}");
                }
            }

            // Si una página completa ya estaba importada, las siguientes también.
            if ($newInPage === 0 && $skipped > 0) {
                break;
            }
        }

        $this->info("EasyBroker leads: {$created} nuevos, {$skipped} ya existentes.");

        return self::SUCCESS;
    }

    private function alreadyImported(int $ebRequestId): bool
    {
        return FormSubmission::where('form_type', 'easybroker')
            ->where('payload', 'like', '%"eb_request_id":' . $ebRequestId . ',%')
            ->exists();
    }

    /**
     * Señales de que el "lead" es en realidad otro broker preguntando por
     * colaboración/comisión compartida. No es basura: alimenta la red de
     * Brokers Externos (Alejandro los quiere en listado aparte para enviarles
     * inventario cuando haya que vender rápido).
     */
    private function looksLikeBroker(array $cr): bool
    {
        $texto = mb_strtolower(($cr['message'] ?? '') . ' ' . ($cr['name'] ?? ''));

        $senales = [
            'compart', 'comision', 'comisión', 'colabora', 'colega',
            'soy asesor', 'soy asesora', 'soy broker', 'soy agente',
            'tengo cliente', 'tengo un cliente', 'mi cliente',
            'inmobiliaria', 'bienes raices', 'bienes raíces',
            'remax', 're/max', 'century 21', 'century21', 'coldwell', 'keller williams',
        ];

        foreach ($senales as $senal) {
            if (str_contains($texto, $senal)) {
                return true;
            }
        }

        return false;
    }

    private function import(array $cr): void
    {
        // Si la propiedad de EB corresponde a una del sitio, se vincula.
        $localProperty = ! empty($cr['property_id'])
            ? Property::where('easybroker_id', $cr['property_id'])->first()
            : null;

        // Clasificación con IA (Gemini): rol, temperatura y resumen. Si la IA
        // no responde, cae a la heurística de palabras clave — la IA es
        // mejora, nunca dependencia. Nada se descarta: 'spam' solo etiqueta.
        $ai = $this->classifier->classifyPortalLead([
            'nombre'    => $cr['name'] ?? '',
            'email'     => $cr['email'] ?? '',
            'mensaje'   => $cr['message'] ?? '',
            'portal'    => $cr['source'] ?? 'EasyBroker',
            'propiedad' => $localProperty
                ? sprintf('%s (%s, %s $%s %s)', $localProperty->title, $localProperty->operation_type === 'rental' ? 'renta' : 'venta', $localProperty->colony ?: $localProperty->city, number_format((float) $localProperty->price), $localProperty->currency)
                : ($cr['property_id'] ?? 'desconocida'),
        ]);

        $esBroker = $ai['ok'] ? ($ai['rol'] === 'broker_colaboracion') : $this->looksLikeBroker($cr);

        $temperatura = $esBroker || ($ai['ok'] && $ai['rol'] === 'spam')
            ? 'cold'
            : ($ai['ok'] ? $ai['temperatura'] : 'warm');

        $clientType = match (true) {
            $esBroker                              => null,
            $ai['ok'] && $ai['rol'] === 'inquilino' => 'renter',
            $ai['ok'] && $ai['rol'] === 'spam'      => null,
            default                                 => 'buyer',
        };

        // withoutEvents: crear un FormSubmission dispara FormSubmitted →
        // SendAcuseMail (correo de acuse al lead). Estos leads ya fueron
        // atendidos por el portal y muchos correos son de meses atrás —
        // mandarles un acuse ahora sería spam (y en una importación inicial
        // serían cientos de correos de golpe).
        FormSubmission::withoutEvents(fn () => FormSubmission::create([
            'form_type'        => 'easybroker',
            'source_page'      => 'easybroker:' . ($cr['property_id'] ?? 'sin-propiedad'),
            'full_name'        => $cr['name'] ?: 'Sin nombre (EasyBroker)',
            'email'            => ($cr['email'] ?? null) ?: 'eb-' . $cr['id'] . '@sin-correo.easybroker',
            'phone'            => ($cr['phone'] ?? null) ?: 'sin teléfono',
            'lead_tag'         => $esBroker ? 'LEAD_BROKER' : 'LEAD_EASYBROKER',
            'client_type'      => $clientType,
            'lead_temperature' => $temperatura,
            'status'           => 'new',
            'utm_source'       => 'easybroker',
            'utm_medium'       => $cr['source'] ?? null,
            'payload'          => [
                'eb_request_id'      => $cr['id'],
                'posible_broker'     => $esBroker,
                'ai_rol'             => $ai['ok'] ? $ai['rol'] : null,
                'ai_resumen'         => $ai['ok'] ? $ai['resumen'] : null,
                'eb_contact_id'      => $cr['contact_id'] ?? null,
                'eb_property_id'     => $cr['property_id'] ?? null,
                'mensaje'            => $cr['message'] ?? null,
                'portal_origen'      => $cr['source'] ?? null,
                'fecha_en_easybroker' => $cr['happened_at'] ?? null,
                'propiedad_local_id' => $localProperty?->id,
                'propiedad_local'    => $localProperty?->title,
            ],
        ]));
    }
}
