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
 * Kanban interactivo — Fase 2: Colocación Activa de Renta
 * PR Rentas-3 · Track B
 *
 * Drag & drop via SortableJS + Livewire.
 */
class RentasKanbanFase2 extends Component
{
    // ── Filtros ───────────────────────────────────────────────────────────────
    public string $filtroAgente   = '';
    public string $filtroColonia  = '';
    public string $filtroRentaMin = '';
    public string $filtroRentaMax = '';

    // ── Selección para bulk actions ───────────────────────────────────────────
    public array $selected    = [];
    public string $bulkStage  = '';
    public string $bulkAgente = '';

    // ── SLA en días por etapa (objetivo de permanencia máxima) ────────────────
    private const SLA_DAYS = [
        'lead'          => 2,
        'contacto'      => 3,
        'visita'        => 5,
        'exclusiva'     => 10,
        'publicacion'   => 14,
        'busqueda'      => 14,
        'investigacion' => 7,
        'contrato'      => 5,
        'entrega'       => 3,
        'cierre'        => 2,
    ];

    // ── Mover card entre columnas ─────────────────────────────────────────────

    #[On('card-moved-fase2')]
    public function moveCard(int $operationId, string $newStage, string $oldStage): void
    {
        $op = Operation::find($operationId);
        if (! $op || $op->type !== 'renta') return;

        $stages   = Operation::RENTA_STAGES;
        $newIndex = array_search($newStage, $stages);
        $oldIndex = array_search($oldStage,  $stages);

        // Validar checklist solo en avance
        if ($newIndex > $oldIndex) {
            $checklistQuery = $op->checklistItems()
                ->where('stage', $oldStage)
                ->where('is_completed', false);

            if (\Illuminate\Support\Facades\Schema::hasColumn('operation_checklist_items', 'is_required')) {
                $checklistQuery->where('is_required', true);
            }

            if ($checklistQuery->count() > 0) {
                $pending = $checklistQuery->count();
                $this->dispatch('kanban-error', [
                    'message' => "Hay {$pending} tarea(s) requerida(s) pendientes en '{$oldStage}' antes de avanzar.",
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
                'notes'        => 'Movido vía kanban colocación',
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
            ->where('type', 'renta')
            ->update(['user_id' => $this->bulkAgente]);

        $count = count($this->selected);
        $this->reset('selected', 'bulkAgente');
        $this->dispatch('kanban-success', ['message' => "{$count} operación(es) reasignadas."]);
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
                'notes'        => 'Movido en masa vía kanban colocación',
            ]);

            $op->update(['stage' => $this->bulkStage]);
        }

        $count = count($this->selected);
        $this->reset('selected', 'bulkStage');
        $this->dispatch('kanban-success', ['message' => "{$count} operación(es) movidas."]);
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $query = Operation::where('type', 'renta')
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->with(['client', 'property', 'user'])
            ->orderBy('updated_at', 'desc');

        if ($this->filtroAgente) {
            $query->where('user_id', $this->filtroAgente);
        }

        if ($this->filtroColonia) {
            $query->whereHas('property', fn($q) => $q->where('colony', 'like', "%{$this->filtroColonia}%"));
        }

        if ($this->filtroRentaMin) {
            $query->where('monthly_rent', '>=', (int) $this->filtroRentaMin);
        }

        if ($this->filtroRentaMax) {
            $query->where('monthly_rent', '<=', (int) $this->filtroRentaMax);
        }

        $operations = $query->get();

        // Solo mostrar stages relevantes a colocación (excluir post-cierre)
        $activeStages = ['lead','contacto','visita','exclusiva','publicacion','busqueda','investigacion','contrato','entrega','cierre'];

        $byStage = [];
        foreach ($activeStages as $stage) {
            $items = $operations->where('stage', $stage)->values();
            $byStage[$stage] = $items->map(function ($op) use ($stage) {
                $daysInStage = $op->updated_at ? now()->diffInDays($op->updated_at) : 0;
                $slaDays     = self::SLA_DAYS[$stage] ?? 7;
                $slaPercent  = $slaDays > 0 ? round(($daysInStage / $slaDays) * 100) : 0;

                return [
                    'id'           => $op->id,
                    'client_name'  => $op->client?->name ?? 'Sin cliente',
                    'address'      => $op->property?->address ?? '—',
                    'colony'       => $op->property?->colony ?? $op->client?->zone_of_interest ?? '—',
                    'area'         => $op->property?->area ?? null,
                    'rooms'        => $op->property?->bedrooms ?? null,
                    'rent'         => $op->monthly_rent,
                    'agent'        => $op->user?->name ?? null,
                    'days'         => $daysInStage,
                    'sla_pct'      => $slaPercent,
                    'sla_color'    => $slaPercent < 50 ? 'green' : ($slaPercent < 100 ? 'yellow' : 'red'),
                    'created_at'   => $op->created_at,
                ];
            })->all();
        }

        $agentes = User::whereIn('role', ['admin', 'broker', 'editor'])
            ->orderBy('name')
            ->get(['id', 'name']);

        $stats = [
            'total'         => $operations->count(),
            'esta_sem'      => $operations->filter(fn($o) => $o->created_at->isCurrentWeek())->count(),
            'sin_asig'      => $operations->whereNull('user_id')->count(),
            'vencidos'      => $operations->filter(function ($o) {
                $sla = self::SLA_DAYS[$o->stage] ?? 7;
                return now()->diffInDays($o->updated_at) > $sla;
            })->count(),
            'en_busqueda'   => $operations->where('stage', 'busqueda')->count(),
            'investigacion' => $operations->where('stage', 'investigacion')->count(),
            'por_firmar'    => $operations->where('stage', 'contrato')->count(),
        ];

        return view('livewire.admin.rentas-kanban-fase2', compact('byStage', 'agentes', 'stats', 'activeStages'));
    }
}
