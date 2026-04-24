<?php

namespace App\Services;

use App\Models\Captacion;
use App\Models\Client;
use App\Models\GoogleSignatureRequest;

class CaptacionService
{
    /**
     * Get existing active captacion or create one for the client.
     */
    public function getOrCreateForClient(Client $client): Captacion
    {
        return Captacion::firstOrCreate(
            ['client_id' => $client->id, 'status' => 'activo'],
            ['portal_etapa' => 1]
        );
    }

    /**
     * Recalculate and persist the portal_etapa based on current completion state.
     */
    public function recalculateStage(Captacion $captacion): void
    {
        $captacion->load('documents', 'signatureRequest');

        if ($captacion->isEtapa4Complete() && !$captacion->etapa4_completed_at) {
            $captacion->etapa4_completed_at = now();
            $captacion->status = 'completado';
        }
        if ($captacion->isEtapa3Complete() && !$captacion->etapa3_completed_at) {
            $captacion->etapa3_completed_at = now();
        }
        if ($captacion->isEtapa2Complete() && !$captacion->etapa2_completed_at) {
            $captacion->etapa2_completed_at = now();
        }
        if ($captacion->isEtapa1Complete() && !$captacion->etapa1_completed_at) {
            $captacion->etapa1_completed_at = now();
        }

        $captacion->portal_etapa = $captacion->getCurrentEtapa();
        $captacion->save();
    }

    /**
     * Link a property valuation (advances to etapa 3).
     */
    public function linkValuation(Captacion $captacion, int $valuationId): void
    {
        $captacion->update(['etapa3_valuation_id' => $valuationId]);
        $this->recalculateStage($captacion);
    }

    /**
     * Record agreed price (advances to etapa 4).
     */
    public function confirmPrice(Captacion $captacion, float $price): void
    {
        $captacion->update(['precio_acordado' => $price]);
        $this->recalculateStage($captacion);
    }

    /**
     * Link the exclusiva signature request.
     */
    public function linkExclusiva(Captacion $captacion, int $signatureRequestId): void
    {
        $captacion->update(['etapa4_signature_id' => $signatureRequestId]);
        $this->recalculateStage($captacion);
    }

    /**
     * Approve a document in captacion context and recalculate stage.
     */
    public function approveDocument(\App\Models\Document $document): void
    {
        $document->update(['captacion_status' => 'aprobado']);
        if ($document->captacion_id) {
            $this->recalculateStage(Captacion::find($document->captacion_id));
        }
    }

    /**
     * Reject a document in captacion context.
     */
    public function rejectDocument(\App\Models\Document $document, ?string $reason = null): void
    {
        $document->update([
            'captacion_status' => 'rechazado',
            'rejection_reason' => $reason,
        ]);
    }
}
