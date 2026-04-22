<?php

namespace App\Observers;

use App\Actions\Valuation\RecordClosedSaleAction;
use App\Models\Operation;
use Illuminate\Support\Facades\Log;

class OperationObserver
{
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
}
