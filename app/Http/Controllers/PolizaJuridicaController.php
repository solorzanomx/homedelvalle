<?php

namespace App\Http\Controllers;

use App\Models\PolizaJuridica;
use App\Models\PolizaEvent;
use App\Models\Operation;
use App\Models\RentalProcess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PolizaJuridicaController extends Controller
{
    public function store(Request $request, string $rentalId)
    {
        $rental = RentalProcess::findOrFail($rentalId);

        if ($rental->poliza) {
            return back()->with('error', 'Este proceso ya tiene una poliza juridica.');
        }

        $validated = $request->validate([
            'insurance_company' => 'nullable|string|max:255',
            'policy_number' => 'nullable|string|max:100',
            'cost' => 'nullable|numeric|min:0',
            'currency' => 'nullable|in:MXN,USD',
            'notes' => 'nullable|string|max:2000',
        ]);

        $validated['rental_process_id'] = $rental->id;
        $validated['tenant_client_id'] = $rental->tenant_client_id;
        $validated['status'] = 'pending';

        $poliza = PolizaJuridica::create($validated);

        PolizaEvent::create([
            'poliza_juridica_id' => $poliza->id,
            'user_id' => Auth::id(),
            'event_type' => 'created',
            'description' => 'Poliza juridica creada',
        ]);

        return back()->with('success', 'Poliza juridica creada.');
    }

    public function storeForOperation(Request $request, string $operationId)
    {
        $operation = Operation::findOrFail($operationId);

        if ($operation->poliza) {
            return back()->with('error', 'Esta operacion ya tiene una poliza juridica.');
        }

        $validated = $request->validate([
            'insurance_company' => 'nullable|string|max:255',
            'policy_number' => 'nullable|string|max:100',
            'cost' => 'nullable|numeric|min:0',
            'currency' => 'nullable|in:MXN,USD',
            'notes' => 'nullable|string|max:2000',
        ]);

        $validated['operation_id'] = $operation->id;
        $validated['tenant_client_id'] = $operation->secondary_client_id ?? $operation->client_id;
        $validated['status'] = 'pending';

        $poliza = PolizaJuridica::create($validated);

        PolizaEvent::create([
            'poliza_juridica_id' => $poliza->id,
            'user_id' => Auth::id(),
            'event_type' => 'created',
            'description' => 'Poliza juridica creada desde operacion',
        ]);

        return back()->with('success', 'Poliza juridica creada.');
    }

    public function update(Request $request, string $polizaId)
    {
        $poliza = PolizaJuridica::findOrFail($polizaId);

        $validated = $request->validate([
            'insurance_company' => 'nullable|string|max:255',
            'policy_number' => 'nullable|string|max:100',
            'cost' => 'nullable|numeric|min:0',
            'currency' => 'nullable|in:MXN,USD',
            'coverage_start' => 'nullable|date',
            'coverage_end' => 'nullable|date|after_or_equal:coverage_start',
            'notes' => 'nullable|string|max:2000',
        ]);

        $poliza->update($validated);

        return back()->with('success', 'Poliza actualizada.');
    }

    public function updateStatus(Request $request, string $polizaId)
    {
        $poliza = PolizaJuridica::findOrFail($polizaId);

        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(PolizaJuridica::STATUSES)),
            'rejection_reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:500',
        ]);

        $oldStatus = $poliza->status;
        $newStatus = $validated['status'];

        if ($oldStatus === $newStatus) {
            return back();
        }

        $data = ['status' => $newStatus];

        if ($newStatus === 'documents_submitted' && !$poliza->submitted_at) {
            $data['submitted_at'] = now();
        }
        if ($newStatus === 'in_review' && !$poliza->review_started_at) {
            $data['review_started_at'] = now();
        }
        if (in_array($newStatus, ['approved', 'rejected'])) {
            $data['resolved_at'] = now();
        }
        if ($newStatus === 'rejected') {
            $data['rejection_reason'] = $validated['rejection_reason'] ?? null;
        }

        $poliza->update($data);

        // Log the status change event
        $fromLabel = PolizaJuridica::STATUSES[$oldStatus] ?? $oldStatus;
        $toLabel = PolizaJuridica::STATUSES[$newStatus] ?? $newStatus;

        PolizaEvent::create([
            'poliza_juridica_id' => $poliza->id,
            'user_id' => Auth::id(),
            'event_type' => 'status_change',
            'description' => "Cambio de {$fromLabel} a {$toLabel}" . ($validated['notes'] ? ": {$validated['notes']}" : ''),
            'data' => ['from' => $oldStatus, 'to' => $newStatus],
        ]);

        // Auto-advance rental stage to 'contrato' when poliza is approved
        if ($newStatus === 'approved') {
            $rental = $poliza->rentalProcess;
            if ($rental && $rental->stage === 'investigacion') {
                $rental->update(['stage' => 'contrato']);

                \App\Models\RentalStageLog::create([
                    'rental_process_id' => $rental->id,
                    'user_id' => Auth::id(),
                    'from_stage' => 'investigacion',
                    'to_stage' => 'contrato',
                    'notes' => 'Avance automatico: poliza juridica aprobada',
                ]);
            }
        }

        return back()->with('success', 'Estado de poliza actualizado a ' . $toLabel);
    }

    public function addEvent(Request $request, string $polizaId)
    {
        $poliza = PolizaJuridica::findOrFail($polizaId);

        $validated = $request->validate([
            'description' => 'required|string|max:1000',
        ]);

        PolizaEvent::create([
            'poliza_juridica_id' => $poliza->id,
            'user_id' => Auth::id(),
            'event_type' => 'note',
            'description' => $validated['description'],
        ]);

        return back()->with('success', 'Nota agregada.');
    }
}
