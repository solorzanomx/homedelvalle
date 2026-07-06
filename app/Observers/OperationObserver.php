<?php

namespace App\Observers;

use App\Actions\Valuation\RecordClosedSaleAction;
use App\Models\Commission;
use App\Models\Operation;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class OperationObserver
{
    /**
     * Los "atajos" para crear una Operation (fuera del flujo oficial de cada
     * pipeline) se olvidan de poner algún campo con demasiada frecuencia —
     * mismo bug encontrado y arreglado 8+ veces en la misma sesión
     * (target_type faltante, phase inconsistente con el stage). Este método
     * centraliza la corrección para que nunca vuelva a faltar sin importar
     * el punto de creación, sin tocar cada llamada dispersa.
     *
     * property_id NO se valida aquí a propósito — es legítimamente nulo en
     * varios flujos reales (captación activada desde el Portal antes de que
     * el broker asigne el inmueble, todo el pipeline de Compradores).
     */
    public function creating(Operation $operation): void
    {
        if ($operation->type && !array_key_exists($operation->type, Operation::TYPES)) {
            throw new \InvalidArgumentException("Operation::type invalido: '{$operation->type}'.");
        }

        if ($operation->type && $operation->stage) {
            $validStages = Operation::stagesForType($operation->type);
            if (!in_array($operation->stage, $validStages, true)) {
                throw new \InvalidArgumentException(
                    "Operation::stage '{$operation->stage}' no es valido para type '{$operation->type}'. Etapas validas: "
                    . implode(', ', $validStages)
                );
            }
        }

        if ($operation->stage && isset(Operation::PHASE_MAP[$operation->stage])) {
            $correctPhase = Operation::PHASE_MAP[$operation->stage];
            if ($operation->phase !== $correctPhase) {
                Log::info('OperationObserver: phase autocorregido al crear Operation', [
                    'type' => $operation->type, 'stage' => $operation->stage,
                    'phase_recibido' => $operation->phase, 'phase_correcto' => $correctPhase,
                ]);
                $operation->phase = $correctPhase;
            }
        }

        if ($operation->type === 'captacion' && !$operation->target_type) {
            // El vocabulario de Operation::intent no es consistente en todo el
            // código (a veces 'renta', a veces 'renta_residencial') — por eso
            // se busca la palabra 'renta' en vez de comparar contra una lista
            // fija. Bug real encontrado 2026-07-04: este default siempre caía
            // en 'venta' sin mirar el intent, y CreateOperationFromLead::
            // createCaptacionOperation() (leads de "propietario_renta" desde
            // el sitio publico) nunca ponia target_type — toda captacion de
            // renta terminaba generando una Operation de venta al completarse.
            $targetType = (is_string($operation->intent) && str_contains(mb_strtolower($operation->intent), 'renta'))
                ? 'renta'
                : 'venta';
            Log::info("OperationObserver: target_type autocompletado a '{$targetType}' al crear Operation de captacion", [
                'client_id' => $operation->client_id,
                'intent'    => $operation->intent,
            ]);
            $operation->target_type = $targetType;
        }
    }

    /**
     * When an operation transitions to 'completed', auto-record the sale
     * against the property's latest delivered/final valuation.
     */
    public function updated(Operation $operation): void
    {
        // Only act when status just became 'completed'
        if (!$operation->wasChanged('status')) {
            return;
        }
        if ($operation->status !== 'completed') {
            return;
        }

        $this->recordCommission($operation);

        // Only sale operations with a price and a property
        if (!$operation->amount || !$operation->property_id) {
            return;
        }

        $property = $operation->property;
        if (!$property) {
            return;
        }

        // Find the most recent final or delivered valuation for this property
        $valuation = $property->valuations()
            ->whereIn('status', ['final', 'delivered'])
            ->whereNotNull('suggested_list_price')
            ->whereNull('actual_sale_price')   // not already recorded
            ->latest()
            ->first();

        if (!$valuation) {
            Log::info('OperationObserver: operación completada pero sin valuación elegible', [
                'operation_id' => $operation->id,
                'property_id'  => $operation->property_id,
            ]);
            return;
        }

        app(RecordClosedSaleAction::class)->execute(
            $valuation,
            (float) $operation->amount,
            $operation->completed_at ?? now(),
        );
    }

    /**
     * El dashboard de Finanzas (/admin/finance) leía Transaction/Commission,
     * un subsistema paralelo que nada alimentaba desde Operation — no había
     * ninguna cifra real de comisiones ganadas (auditoría 2026-07-06).
     * Aplica a venta Y renta (a diferencia del bloque de arriba, que solo
     * corre para venta con property/valuación).
     */
    private function recordCommission(Operation $operation): void
    {
        if (!$operation->commission_amount) {
            return;
        }
        if (Commission::where('operation_id', $operation->id)->exists()) {
            return;
        }

        $commission = Commission::create([
            'operation_id' => $operation->id,
            'broker_id'    => $operation->broker_id,
            'amount'       => $operation->commission_amount,
            'percentage'   => $operation->commission_percentage,
            'status'       => 'pending',
        ]);

        Transaction::create([
            'type'         => 'income',
            'category'     => 'commission',
            'description'  => "Comision — Operation #{$operation->id}",
            'amount'       => $operation->commission_amount,
            'date'         => ($operation->completed_at ?? now())->toDateString(),
            'operation_id' => $operation->id,
            'property_id'  => $operation->property_id,
            'broker_id'    => $operation->broker_id,
            'user_id'      => $operation->user_id,
        ]);

        Log::info('OperationObserver: Commission/Transaction generadas al cerrar la operacion', [
            'operation_id'  => $operation->id,
            'commission_id' => $commission->id,
        ]);
    }
}
