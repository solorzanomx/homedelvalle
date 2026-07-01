<?php

namespace App\Livewire\Admin;

use App\Models\Captacion;
use App\Models\Interaction;
use App\Models\Operation;
use App\Models\OperationChecklistItem;
use App\Services\OperationChecklistService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * "Cabina de etapa": por cada ítem del checklist de la etapa ACTUAL de una
 * captación, embebe la acción real (llamar, registrar datos, etc.) en vez
 * de un checkbox suelto — al completarla ahí mismo, el ítem se marca solo.
 * Ver docs/07-FLUJO-CAPTACION-Y-MEJORAS.md. Empieza cubriendo LEAD; el
 * resto de acciones caen al checkbox manual de siempre (action_type null).
 */
class CaptacionStageCockpit extends Component
{
    public Operation $operation;
    public Captacion $captacion;

    public array $formData = [];
    public ?int $editingItemId = null;

    public const MOTIVO_OPTIONS = ['Mudanza', 'Necesita liquidez', 'Herencia o sucesión', 'Ya no la necesita', 'Otro'];
    public const URGENCIA_OPTIONS = ['Inmediata', '1-3 meses', '3-6 meses', 'Sin prisa'];
    public const TIPO_OPTIONS = [
        'House' => 'Casa',
        'Apartment' => 'Departamento',
        'Land' => 'Terreno',
        'Office' => 'Oficina',
        'Commercial' => 'Local comercial',
    ];

    // Objetivo de la etapa, tal como está redactado en
    // docs/08-MANUAL-BROKER-CAPTACION.md — se va llenando etapa por etapa
    // conforme se diseña su propia cabina.
    public const STAGE_OBJECTIVES = [
        'lead' => 'Contactar al propietario en menos de 1 hora y confirmar que quiere vender/rentar.',
    ];

    public function mount(Operation $operation, Captacion $captacion): void
    {
        $this->operation = $operation;
        $this->captacion = $captacion;
        $this->seedFormData();
    }

    public function render()
    {
        $stageKeys = Operation::CAPTACION_STAGES;
        $currentIdx = array_search($this->operation->stage, $stageKeys);
        $itemsByStage = $this->operation->checklistItems()
            ->with(['template', 'completedByUser'])
            ->get()
            ->groupBy('stage');

        return view('livewire.admin.captacion-stage-cockpit', [
            'stageKeys' => $stageKeys,
            'currentIdx' => $currentIdx,
            'itemsByStage' => $itemsByStage,
        ]);
    }

    private function currentItems()
    {
        return $this->operation->checklistItems()
            ->where('stage', $this->operation->stage)
            ->with(['template', 'completedByUser'])
            ->orderBy('id')
            ->get();
    }

    private function seedFormData(): void
    {
        $property = $this->captacion->property;

        foreach ($this->currentItems() as $item) {
            $key = (string) $item->id;
            if (isset($this->formData[$key])) {
                continue;
            }

            $this->formData[$key] = match ($item->template->action_type ?? null) {
                'llamar' => ['nota' => ''],
                'confirmar_interes' => [
                    'motivo' => $this->captacion->motivo ?? '',
                    'urgencia' => $this->captacion->urgencia ?? '',
                    'nota' => '',
                ],
                'datos_inmueble' => [
                    'direccion' => $property?->address ?? '',
                    'tipo' => $property?->property_type ?? '',
                    'm2' => $property?->area ?? '',
                ],
                default => [],
            };
        }
    }

    public function startEdit(int $itemId): void
    {
        $this->editingItemId = $itemId;
    }

    public function cancelEdit(): void
    {
        $this->editingItemId = null;
    }

    public function completeLlamada(int $itemId): void
    {
        $nota = trim($this->formData[$itemId]['nota'] ?? '');

        Interaction::create([
            'client_id' => $this->captacion->client_id,
            'property_id' => $this->captacion->property_id,
            'user_id' => Auth::id(),
            'type' => 'call',
            'description' => $nota !== '' ? $nota : 'Llamada de contacto inicial (captación)',
            'completed_at' => now(),
        ]);

        $this->completeItem($itemId);
    }

    public function saveInteres(int $itemId): void
    {
        $data = $this->formData[$itemId] ?? [];

        if (empty($data['motivo']) || empty($data['urgencia'])) {
            $this->addError("formData.{$itemId}.motivo", 'Selecciona motivo y urgencia para continuar.');
            return;
        }

        $this->captacion->update([
            'motivo' => $data['motivo'],
            'urgencia' => $data['urgencia'],
        ]);

        $this->completeItem($itemId);
    }

    public function saveDatosInmueble(int $itemId): void
    {
        $data = $this->formData[$itemId] ?? [];

        if (empty($data['direccion']) || empty($data['tipo'])) {
            $this->addError("formData.{$itemId}.direccion", 'Completa dirección y tipo de inmueble.');
            return;
        }

        $property = $this->captacion->property;
        if ($property) {
            $property->update([
                'address' => $data['direccion'],
                'property_type' => $data['tipo'],
                'area' => $data['m2'] !== '' ? $data['m2'] : $property->area,
            ]);
        }

        $this->completeItem($itemId);
    }

    public function toggleManual(int $itemId): void
    {
        $item = OperationChecklistItem::findOrFail($itemId);
        app(OperationChecklistService::class)->toggleChecklistItem($item, Auth::user());
        $this->refreshAfterChange();
    }

    private function completeItem(int $itemId): void
    {
        $this->resetErrorBag();
        $item = OperationChecklistItem::findOrFail($itemId);

        if (!$item->is_completed) {
            app(OperationChecklistService::class)->toggleChecklistItem($item, Auth::user());
        }

        $this->editingItemId = null;
        $this->refreshAfterChange();
    }

    private function refreshAfterChange(): void
    {
        $this->operation->refresh();
        $this->captacion->refresh();
        $this->seedFormData();
    }
}
