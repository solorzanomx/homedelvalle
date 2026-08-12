<?php

namespace App\Services;

use App\Models\Operation;
use App\Models\RentalProcess;
use App\Models\SiteSetting;

class ContractClauseVariableResolver
{
    /**
     * Tokens disponibles para cláusulas de contratos de venta, sacados de
     * la Operación, sus partes y la oferta de compra aceptada más reciente.
     */
    public function resolveForOperation(Operation $operation): array
    {
        $operation->loadMissing(['property', 'client', 'secondaryClient']);
        $settings = SiteSetting::first();

        $acceptedOffer = $operation->purchaseOffers()
            ->where('status', 'accepted')
            ->latest('offered_at')
            ->first();

        $amount = $acceptedOffer->amount ?? $operation->amount;

        return [
            '{{vendedor_nombre}}' => $operation->client->name ?? '',
            '{{comprador_nombre}}' => $operation->secondaryClient->name ?? '',
            '{{propiedad_direccion}}' => $operation->property->address ?? $operation->property->title ?? '',
            '{{precio_texto}}' => $amount ? '$' . number_format($amount, 2) . ' ' . ($operation->currency ?? 'MXN') : '[PRECIO A CONFIRMAR]',
            '{{fecha_actual}}' => now()->translatedFormat('d \d\e F \d\e Y'),
            '{{fecha_firma_texto}}' => now()->translatedFormat('\l\o\s d \d\í\a\s \d\e\l \m\e\s \d\e F \d\e\l \a\ñ\o Y'),
            '{{fecha_limite_escrituracion}}' => $operation->expected_close_date
                ? $operation->expected_close_date->translatedFormat('d \d\e F \d\e Y')
                : '[FECHA LÍMITE A CONFIRMAR]',
            '{{folio}}' => 'CV-' . str_pad((string) $operation->id, 5, '0', STR_PAD_LEFT),
            '{{empresa_nombre}}' => $settings->site_name ?? 'Home del Valle',
        ];
    }

    /**
     * Conserva el conjunto de tokens de renta ya existente en ContractService,
     * sin cambios — solo se centraliza la firma para uso uniforme.
     */
    public function resolveForRental(RentalProcess $rental): array
    {
        return app(ContractService::class)->buildVariables($rental);
    }
}
