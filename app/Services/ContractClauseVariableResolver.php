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
     *
     * Hallazgo 2026-11-02 (Renta #11, co-arrendatarios): ensureCaratulaClause() y
     * generateVersion() leen {{vendedor_nombre}}/{{comprador_nombre}} sin importar si es
     * venta o renta (son los nombres genéricos de "las partes" en la carátula). Para
     * venta esos tokens sí existen (resolveForOperation); para renta NUNCA existían — la
     * carátula de un Contrato de Arrendamiento generado con cláusulas estructuradas salía
     * con el nombre del arrendatario en blanco. Se agregan aquí como alias de los tokens
     * de renta reales (que SÍ incluyen al co-arrendatario, ver ContractService::buildVariables).
     */
    public function resolveForRental(RentalProcess $rental): array
    {
        $tokens = app(ContractService::class)->buildVariables($rental);

        $tokens['{{vendedor_nombre}}'] = $tokens['{{propietario_nombre}}'] ?? '';
        $tokens['{{comprador_nombre}}'] = $tokens['{{inquilino_nombre}}'] ?? '';
        $tokens['{{precio_texto}}'] = isset($tokens['{{renta_mensual}}']) && $tokens['{{renta_mensual}}'] !== ''
            ? '$' . $tokens['{{renta_mensual}}'] . ' ' . ($tokens['{{moneda}}'] ?? 'MXN') . ' mensuales'
            : '';

        return $tokens;
    }
}
