<?php

namespace App\Services;

use App\Models\Operation;
use App\Models\OperationChecklistItem;
use App\Models\OperationStageLog;
use App\Models\Referral;
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

        $startStage = 'publicacion';
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
