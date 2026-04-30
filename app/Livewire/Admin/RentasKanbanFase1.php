<?php

namespace App\Livewire\Admin;

use App\Models\Operation;
use App\Models\OperationStageLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\On;

/**
 * Kanban interactivo — Fase 1: Captación de Renta
 * PR Rentas-2 · Track B
 *
 * Drag & drop via SortableJS + Livewire.
 * Livewire aprobado en CRM admin por Alex (2026-04-29).
 */
class RentasKanbanFase1 extends Component
{
    // ── Filtros ───────────────────────────────────────────────────────────────
    public string $filtroAgente  = '';
    public string $filtroColonia = '';
    public string $filtroRentaMin = '';
    public string $filtroRentaMax = '';

    // ── Selección para bulk actions ───────────────────────────────────────────
    public array $selected = [];
    public string $bulkStage  = '';
    public string $bulkAgente = '';
    public bool   $showBulk   = false;

    // ── SLA en días por etapa (objetivo de permanencia máxima) ────────────────
    private const SLA_DAYS = [
        'lead'         => 2,
        'contacto'     => 3,
        'visita'       => 5,
        'revision_docs'=> 7,
        'avaluo'       => 7,
        'mejoras'      => 14,
        'exclusiva'    => 5,
        'fotos_video'  => 5,
        'carpeta_lista'=> 3,
    ];

    // ── Mover card entre columnas (llamado desde JS via dispatch) ─────────────

    #[On('card-moved')]
    public function moveCard(int $operationId, string $newStage, string $oldStage): void
    {
        $op = Operation::find($operationId);
        if (! $op || $op->type !== 'captacion') return;

        // Verificar checklist del stage actual antes de avanzar
        $stageIndex = array_search($newStage, Operation::CAPTACION_STAGES);
        $oldIndex   = array_search($oldStage,  Operation::CAPTACION_STAGES);

        if ($stageIndex > $oldIndex) {
            // Solo validar avance, no retroceso
            $pendingChecklist = $op->checklistItems()
                ->where('stage', $oldStage)
                ->where('is_completed', false)
                ->where('is_required', true)
                ->count();

            if ($pendingChecklist > 0) {
                $this->dispatch('kanban-error', [
                    'message' => "Hay {$pendingChecklist} tarea(s) requerida(s) pendientes en '{$oldStage}' antes de avanzar.",
                    'operationId' => $operationId,
                ]);
                return;
            }
        }

        DB::transaction(function () use ($op, $newStage, $oldStage) {
            OperationStageLog::create([
                'operation_id' => $op->id,
                'user_id'      => Auth::id(),
                'from_stage'   => $oldStage,
                'to_stage'     => $newStage,
                'from_phase'   => $op->phase ?? '',
                'to_phase'     => $newStage,
                'notes'        => 'Movido vía kanban',
            ]);

            $op->update([
                'stage'      => $newStage,
                'updated_at' => now(),
            ]);
        });

        $this->dispatch('kanban-success', ['message' => 'Etapa actualizada.']);
    }

    // ── Bulk: reasignar agente ────────────────────────────────────────────────

    public function bulkAssignAgent(): void
    {
        if (empty($this->selected) || ! $this->bulkAgente) return;

        Operation::whereIn('id', $this->selected)
            ->where('type', 'captacion')
            ->update(['user_id' => $this->bulkAgente]);

        $this->reset('selected', 'bulkAgente', 'showBulk');
        $this->dispatch('kanban-success', ['message' => count($this->selected) . ' captación(es) reasignadas.']);
    }

    // ── Bulk: mover a stage ───────────────────────────────────────────────────

    public function bulkMoveStage(): void
    {
        if (empty($this->selected) || ! $this->bulkStage) return;

        foreach ($this->selected as $id) {
            $op = Operation::find($id);
            if (! $op) continue;

            OperationStageLog::create([
                'operation_id' => $op->id,
                'user_id'      => Auth::id(),
                'from_stage'   => $op->stage,
                'to_stage'     => $this->bulkStage,
                'from_phase'   => $op->phase ?? '',
                'to_phase'     => $this->bulkStage,
                'notes'        => 'Movido en masa vía kanban',
            ]);

            $op->update(['stage' => $this->bulkStage]);
        }

        $this->reset('selected', 'bulkStage', 'showBulk');
        $this->dispatch('kanban-success', ['message' => count($this->selected) . ' captación(es) movidas.']);
    }

    // ── Bulk: marcar como cold ────────────────────────────────────────────────

    public function bulkMarkCold(): void
    {
        if (empty($this->selected)) return;

        Operation::whereIn('id', $this->selected)
            ->where('type', 'captacion')
            ->update(['status' => 'cold']);

        $this->reset('selected', 'showBulk');
        $this->dispatch('kanban-success', ['message' => count($this->selected) . ' captación(es) marcadas como frías.']);
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $query = Operation::where('type', 'captacion')
            ->where(function ($q) {
                $q->where('intent', 'renta')->orWhereNull('intent');
            })
            ->where('status', '!=', 'cancelled')
            ->with(['client', 'property', 'user'])
            ->orderBy('updated_at', 'desc');

        if ($this->filtroAgente) {
            $query->where('user_id', $this->filtroAgente);
        }

        if ($this->filtroColonia) {
            $query->whereHas('property', fn($q) => $q->where('colony', 'like', "%{$this->filtroColonia}%"))
                  ->orWhereHas('client', fn($q) => $q->where('zone_of_interest', 'like', "%{$this->filtroColonia}%"));
        }

        if ($this->filtroRentaMin) {
            $query->where('monthly_rent', '>=', (int) $this->filtroRentaMin);
        }

        if ($this->filtroRentaMax) {
            $query->where('monthly_rent', '<=', (int) $this->filtroRentaMax);
        }

        $operations = $query->get();

        // Agrupar por stage y calcular SLA
        $byStage = [];
        foreach (Operation::CAPTACION_STAGES as $stage) {
            $items = $operations->where('stage', $stage)->values();
            $byStage[$stage] = $items->map(function ($op) use ($stage) {
                $daysInStage = $op->updated_at ? now()->diffInDays($op->updated_at) : 0;
                $slaDays     = self::SLA_DAYS[$stage] ?? 7;
                $slaPercent  = $slaDays > 0 ? round(($daysInStage / $slaDays) * 100) : 0;

                return [
                    'id'          => $op->id,
                    'client_name' => $op->client?->name ?? 'Sin cliente',
                    'colony'      => $op->property?->colony ?? $op->client?->zone_of_interest ?? '—',
                    'area'        => $op->property?->area ?? null,
                    'rent'        => $op->monthly_rent,
                    'agent'       => $op->user?->name ?? null,
                    'days'        => $daysInStage,
                    'sla_pct'     => $slaPercent,
                    'sla_color'   => $slaPercent < 50 ? 'green' : ($slaPercent < 100 ? 'yellow' : 'red'),
                    'intent'      => $op->intent,
                ];
            })->all();
        }

        $agentes = User::whereIn('role', ['admin', 'broker', 'editor'])
            ->orderBy('name')
            ->get(['id', 'name']);

        $stats = [
            'total'    => $operations->count(),
            'esta_sem' => $operations->filter(fn($o) => $o->created_at->isCurrentWeek())->count(),
            'sin_asig' => $operations->whereNull('user_id')->count(),
            'vencidos' => $operations->filter(function ($o) {
                $sla = self::SLA_DAYS[$o->stage] ?? 7;
                return now()->diffInDays($o->updated_at) > $sla;
            })->count(),
        ];

        return view('livewire.admin.rentas-kanban-fase1', compact('byStage', 'agentes', 'stats'));
    }
}
