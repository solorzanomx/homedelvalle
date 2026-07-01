<?php

namespace App\Services;

use App\Models\Captacion;
use App\Models\Client;
use App\Models\GoogleSignatureRequest;
use App\Models\Operation;
use Illuminate\Support\Facades\Auth;

class CaptacionService
{
    public function __construct(protected OperationChecklistService $checklistService) {}

    /**
     * Get existing active captacion or create one for the client.
     * Garantiza que quede vinculada a una Operation (type=captacion) para que
     * sea visible en el kanban /admin/captaciones/pipeline — incluso las
     * captaciones legacy que se encuentren sin operation_id se reparan aquí.
     */
    public function getOrCreateForClient(Client $client): Captacion
    {
        $captacion = Captacion::firstOrCreate(
            ['client_id' => $client->id, 'status' => 'activo'],
            ['portal_etapa' => 1]
        );

        if (!$captacion->operation_id) {
            $operation = Operation::create([
                'type'       => 'captacion',
                'stage'      => 'lead',
                'phase'      => 'captacion',
                'status'     => 'active',
                'property_id'=> $captacion->property_id,
                'client_id'  => $client->id,
                'user_id'    => $client->assigned_user_id ?? $this->fallbackAgentId(),
            ]);
            $captacion->update(['operation_id' => $operation->id]);
            $this->checklistService->initializeChecklistForStage($operation, 'lead');
        }

        return $captacion;
    }

    /**
     * operations.user_id es NOT NULL. Cuando el cliente aún no tiene agente
     * asignado (típico en captaciones que arrancan desde el Portal del
     * Cliente), se le asigna al primer usuario de staff (no-cliente) como
     * placeholder — el agente real lo reasigna después desde la operación.
     */
    private function fallbackAgentId(): int
    {
        return \App\Models\User::where('role', '!=', 'client')->orderBy('id')->value('id');
    }

    /**
     * Mapeo de etapa (portal_etapa, 1-4) completada -> stage objetivo en la
     * Operation vinculada. Mantiene el kanban de captaciones (Operation.stage)
     * en sincronía con el avance real del negocio (documentos, valuación,
     * precio, exclusiva), que se gestiona en la ficha de captación.
     */
    private const ETAPA_TO_STAGE = [
        1 => 'avaluo',       // documentos aprobados -> listo para avaluar
        2 => 'mejoras',      // valuación vinculada -> a la espera de mejoras/negociación
        3 => 'exclusiva',    // precio acordado -> listo para firmar exclusiva
        4 => 'fotos_video',  // exclusiva firmada -> a producir fotos/video
    ];

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

        $completedEtapas = array_values(array_filter([1, 2, 3, 4], fn($etapa) => match ($etapa) {
            1 => $captacion->isEtapa1Complete(),
            2 => $captacion->isEtapa2Complete(),
            3 => $captacion->isEtapa3Complete(),
            4 => $captacion->isEtapa4Complete(),
        }));

        $this->advanceLinkedOperation($captacion, $completedEtapas);
    }

    /**
     * Empuja hacia adelante (nunca hacia atrás) la Operation vinculada del
     * kanban, según la etapa de negocio más avanzada que se acaba de completar.
     */
    private function advanceLinkedOperation(Captacion $captacion, array $completedEtapas): void
    {
        if (empty($completedEtapas) || !$captacion->operation_id) {
            return;
        }

        $operation = Operation::find($captacion->operation_id);
        if (!$operation || $operation->type !== 'captacion') {
            return;
        }

        $targetStage = self::ETAPA_TO_STAGE[max($completedEtapas)] ?? null;
        if (!$targetStage) {
            return;
        }

        $stages = Operation::CAPTACION_STAGES;
        $currentIdx = array_search($operation->stage, $stages);
        $targetIdx  = array_search($targetStage, $stages);

        if ($currentIdx === false || $targetIdx === false || $targetIdx <= $currentIdx) {
            return; // no retroceder ni tocar si ya está igual o más adelante
        }

        $user = Auth::user() ?? $operation->user;
        if (!$user) {
            return; // sin usuario resoluble (ej. contexto sin sesión) — no forzamos el avance
        }

        $this->checklistService->changeStage(
            $operation,
            $targetStage,
            $user,
            'Avance automático desde la ficha de captación'
        );
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
