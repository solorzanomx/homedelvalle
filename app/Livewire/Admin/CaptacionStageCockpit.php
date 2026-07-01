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

    // Objetivo y mensaje motivador de cada etapa, redactados a partir de
    // docs/08-MANUAL-BROKER-CAPTACION.md.
    public const STAGE_OBJECTIVES = [
        'lead' => 'Contactar al propietario en menos de 1 hora y confirmar que quiere vender/rentar.',
        'contacto' => 'Enviar la Presentación de Home del Valle y agendar la visita mientras el interés está fresco.',
        'visita' => 'La etapa más importante de todo el proceso: aquí se decide si firmas la exclusiva o no.',
        'revision_docs' => 'Reunir los documentos del propietario sin frenar la negociación de precio.',
        'avaluo' => 'Llegar a un precio de lista acordado con el propietario, respaldado por datos.',
        'exclusiva' => 'Firmar el contrato de exclusiva y dejar claro qué va a pasar después.',
    ];

    public const STAGE_MOTIVATION = [
        'lead' => 'La velocidad de respuesta es tu ventaja #1 — quien llama en la primera hora casi siempre gana la exclusiva.',
        'contacto' => 'No dejes "te aviso" sin fecha concreta: un lead sin próximo paso definido es un lead que se enfría.',
        'visita' => 'No hay mejor momento para cerrar que cuando el interés está al máximo. Prepárate en serio.',
        'revision_docs' => 'Esto puede correr en paralelo mientras cierras el precio — no dejes que se convierta en cuello de botella.',
        'avaluo' => 'Un precio bien sustentado se defiende solo: usa comparables reales, no opiniones.',
        'exclusiva' => 'Ya llegaste hasta aquí — no dejes que se enfríe justo en la firma. Da seguimiento activo, no lo dejes "esperando a que firme solo".',
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
