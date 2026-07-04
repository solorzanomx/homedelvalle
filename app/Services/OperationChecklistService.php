<?php

namespace App\Services;

use App\Models\Operation;
use App\Models\OperationChecklistItem;
use App\Models\OperationStageLog;
use App\Models\PolizaJuridica;
use App\Models\Referral;
use App\Models\RentalProcess;
use App\Models\RentalStageLog;
use App\Models\StageChecklistTemplate;
use App\Models\User;

class OperationChecklistService
{
    /**
     * Initialize checklist items for a stage from templates.
     */
    public function initializeChecklistForStage(Operation $operation, string $stage): void
    {
        // Don't create duplicates if items already exist for this stage
        if ($operation->checklistItems()->where('stage', $stage)->exists()) {
            return;
        }

        $templates = StageChecklistTemplate::forStage($stage, $operation->type)->get();

        foreach ($templates as $template) {
            OperationChecklistItem::create([
                'operation_id' => $operation->id,
                'stage_checklist_template_id' => $template->id,
                'stage' => $stage,
            ]);
        }
    }

    /**
     * Toggle a checklist item and check for auto-advance.
     */
    public function toggleChecklistItem(OperationChecklistItem $item, User $user): bool
    {
        if ($item->is_completed) {
            $item->update([
                'is_completed' => false,
                'completed_by' => null,
                'completed_at' => null,
            ]);
            return false;
        }

        $item->update([
            'is_completed' => true,
            'completed_by' => $user->id,
            'completed_at' => now(),
        ]);

        return $this->checkAndAutoAdvance($item->operation, $user);
    }

    /**
     * Check if all required items are done and auto-advance if so.
     */
    public function checkAndAutoAdvance(Operation $operation, User $user): bool
    {
        $operation->refresh();

        $pendingRequired = $operation->checklistItems()
            ->where('stage', $operation->stage)
            ->where('is_completed', false)
            ->whereHas('template', fn($q) => $q->where('is_required', true))
            ->count();

        if ($pendingRequired > 0) {
            return false;
        }

        $nextStage = $operation->getNextStage();

        if ($nextStage) {
            $this->changeStage($operation, $nextStage, $user, 'Avance automatico: checklist completado');
            return true;
        }

        // Terminal stage — for captacion, spawn a new venta/renta operation
        if ($operation->type === 'captacion' && $operation->target_type) {
            $this->completeCaptacionAndSpawn($operation, $user);
            return true;
        }

        return false;
    }

    /**
     * Complete a captacion operation and auto-create the resulting venta/renta operation.
     */
    public function completeCaptacionAndSpawn(Operation $captacion, User $user): Operation
    {
        $captacion->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        OperationStageLog::create([
            'operation_id' => $captacion->id,
            'user_id' => $user->id,
            'from_stage' => $captacion->stage,
            'to_stage' => $captacion->stage,
            'from_phase' => 'captacion',
            'to_phase' => 'captacion',
            'notes' => 'Captacion completada. Generando operacion de ' . $captacion->target_type . '.',
        ]);

        $startStage = 'mejoras';
        $startPhase = Operation::PHASE_MAP[$startStage];

        $newOperation = Operation::create([
            'type' => $captacion->target_type,
            'phase' => $startPhase,
            'stage' => $startStage,
            'status' => 'active',
            'property_id' => $captacion->property_id,
            'client_id' => $captacion->client_id,
            'secondary_client_id' => $captacion->secondary_client_id,
            'broker_id' => $captacion->broker_id,
            'user_id' => $captacion->user_id,
            'source_operation_id' => $captacion->id,
            'amount' => $captacion->amount,
            'monthly_rent' => $captacion->monthly_rent,
            'currency' => $captacion->currency,
            'deposit_amount' => $captacion->deposit_amount,
            'commission_amount' => $captacion->commission_amount,
            'commission_percentage' => $captacion->commission_percentage,
            'guarantee_type' => $captacion->guarantee_type,
            'expected_close_date' => $captacion->expected_close_date,
            'notes' => 'Generado automaticamente desde captacion #' . $captacion->id,
        ]);

        $this->initializeChecklistForStage($newOperation, $startStage);

        OperationStageLog::create([
            'operation_id' => $newOperation->id,
            'user_id' => $user->id,
            'to_stage' => $startStage,
            'to_phase' => $startPhase,
            'notes' => 'Operacion generada desde captacion #' . $captacion->id,
        ]);

        return $newOperation;
    }

    /**
     * Puente Colocación Activa → Post-Cierre: al cerrar una Operation de
     * renta se crea el RentalProcess correspondiente — sin esto todo lo que
     * depende de RentalProcess (pagos mensuales, renovación, investigación
     * de inquilino, póliza jurídica) quedaba inalcanzable (bug real
     * encontrado 2026-07-04, auditoría de Rentas). Mismo patrón que
     * completeCaptacionAndSpawn() arriba, incluyendo el vínculo
     * bidireccional (aquí vía rental_processes.operation_id).
     *
     * Arranca en stage='activo', no 'captacion' (el default de altas
     * manuales desde /rentals/create) — la Operation ya recorrió todo el
     * ciclo de colocación hasta cierre, el inquilino ya está encontrado y
     * el contrato ya se firmó; repetir 'captacion' retrocedería un proceso
     * que ya terminó.
     */
    public function spawnRentalProcess(Operation $operation, User $user): RentalProcess
    {
        if ($operation->rentalProcess) {
            return $operation->rentalProcess;
        }

        $rentalProcess = RentalProcess::create([
            'operation_id' => $operation->id,
            'property_id' => $operation->property_id,
            'owner_client_id' => $operation->client_id,
            'tenant_client_id' => $operation->secondary_client_id,
            'broker_id' => $operation->broker_id,
            'user_id' => $operation->user_id,
            'stage' => 'activo',
            'status' => 'active',
            'monthly_rent' => $operation->monthly_rent,
            'currency' => $operation->currency,
            'deposit_amount' => $operation->deposit_amount,
            'commission_amount' => $operation->commission_amount,
            'commission_percentage' => $operation->commission_percentage,
            // rental_processes.guarantee_type es NOT NULL (default 'deposito')
            // — pasar null explicito aqui violaria esa restriccion en vez de
            // usar el default de la columna (bug real encontrado al probar
            // el backfill 2026-07-04).
            'guarantee_type' => $operation->guarantee_type ?? 'deposito',
            'lease_start_date' => $operation->lease_start_date,
            'lease_end_date' => $operation->lease_end_date,
            'lease_duration_months' => $operation->lease_duration_months,
            'notes' => 'Generado automaticamente desde Operation #' . $operation->id . ' al cerrarse.',
        ]);

        RentalStageLog::create([
            'rental_process_id' => $rentalProcess->id,
            'user_id' => $user->id,
            'to_stage' => 'activo',
            'notes' => 'Expediente generado desde Operation #' . $operation->id,
        ]);

        // Si ya existia una poliza jurídica adjunta a la Operation (via
        // storeForOperation), se re-vincula al RentalProcess para que el
        // auto-avance de etapa al aprobarla (PolizaJuridicaController::
        // updateStatus) la encuentre — ese metodo solo mira
        // $poliza->rentalProcess, nunca $poliza->operation.
        $poliza = PolizaJuridica::where('operation_id', $operation->id)
            ->whereNull('rental_process_id')
            ->first();
        if ($poliza) {
            $poliza->update(['rental_process_id' => $rentalProcess->id]);
        }

        return $rentalProcess;
    }

    /**
     * Manually change stage, log it, and initialize new checklist.
     */
    public function changeStage(Operation $operation, string $newStage, User $user, ?string $notes = null): void
    {
        $fromStage = $operation->stage;
        $fromPhase = Operation::PHASE_MAP[$fromStage] ?? $operation->phase;
        $toPhase = Operation::PHASE_MAP[$newStage] ?? 'operacion';

        $operation->update([
            'stage' => $newStage,
            'phase' => $toPhase,
            'status' => $newStage === 'cierre' ? 'completed' : $operation->status,
            'completed_at' => $newStage === 'cierre' ? now() : $operation->completed_at,
        ]);

        OperationStageLog::create([
            'operation_id' => $operation->id,
            'user_id' => $user->id,
            'from_stage' => $fromStage,
            'to_stage' => $newStage,
            'from_phase' => $fromPhase,
            'to_phase' => $toPhase,
            'notes' => $notes,
        ]);

        $this->initializeChecklistForStage($operation, $newStage);

        // Fire stage_change trigger for automations + scoring
        if ($operation->client) {
            \App\Models\LeadEvent::record($operation->client_id, 'stage_changed', [
                'source' => 'pipeline',
                'properties' => ['from_stage' => $fromStage, 'to_stage' => $newStage, 'operation_type' => $operation->type],
            ]);
            app(\App\Services\LeadScoringService::class)->processEvent($operation->client_id, 'stage_changed', ['source' => 'pipeline']);
            app(\App\Services\AutomationEngine::class)->processStageChange($operation->client, $fromStage, $newStage, $operation->type);
        }

        // Notificar también al comprador (secondaryClient) en las etapas
        // post-oferta-aceptada — sufijo '_comprador' en operation_type para
        // que estas automatizaciones no se crucen con las del vendedor
        // (mismo to_stage, distinto operation_type en el trigger_config).
        if ($operation->secondaryClient) {
            app(\App\Services\AutomationEngine::class)->processStageChange($operation->secondaryClient, $fromStage, $newStage, $operation->type . '_comprador');
        }

        // Puente Colocacion Activa -> Post-Cierre: al cerrar una renta se
        // genera el RentalProcess correspondiente (ver spawnRentalProcess()).
        if ($newStage === 'cierre' && $operation->type === 'renta') {
            $this->spawnRentalProcess($operation, $user);
        }

        // Auto-calculate referral commissions when operation reaches cierre
        if ($newStage === 'cierre' && $operation->commission_amount) {
            $referrals = Referral::where('operation_id', $operation->id)
                ->whereIn('status', ['registrado', 'en_proceso'])
                ->get();

            foreach ($referrals as $referral) {
                $amount = round($operation->commission_amount * $referral->commission_percentage / 100, 2);
                $referral->update([
                    'status' => 'por_pagar',
                    'commission_amount' => $amount,
                ]);
            }
        }
    }

    /**
     * Get progress for the current stage.
     */
    public function getStageProgress(Operation $operation): array
    {
        $items = $operation->checklistItems()->where('stage', $operation->stage)->get();
        $total = $items->count();
        $completed = $items->where('is_completed', true)->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'percentage' => $total > 0 ? round(($completed / $total) * 100) : 0,
        ];
    }
}
