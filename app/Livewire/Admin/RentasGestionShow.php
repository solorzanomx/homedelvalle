<?php

namespace App\Livewire\Admin;

use App\Models\RentalStageLog;
use App\Models\RentalPayment;
use App\Models\RentalProcess;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

/**
 * Gestión Post-Cierre — Vista de detalle de RentalProcess
 * PR Rentas-4
 *
 * Acciones: cambiar etapa, agregar nota, marcar pago, renovar contrato, programar move-out.
 */
class RentasGestionShow extends Component
{
    public RentalProcess $rental;

    // ── Modales / paneles ─────────────────────────────────────────────────────
    public string $activePanel = ''; // 'stage' | 'nota' | 'pago' | 'renovacion' | 'moveout'

    // ── Cambio de etapa ───────────────────────────────────────────────────────
    public string $newStage = '';
    public string $stageNote = '';

    // ── Nota ─────────────────────────────────────────────────────────────────
    public string $notaText = '';

    // ── Marcar pago ──────────────────────────────────────────────────────────
    public ?int $paymentId = null;
    public string $paidAt  = '';
    public string $paymentNote = '';

    // ── Renovación ───────────────────────────────────────────────────────────
    public string $newEndDate  = '';
    public string $newRent     = '';
    public string $renewalNote = '';

    // ── Move-out ─────────────────────────────────────────────────────────────
    public string $moveOutDate = '';
    public string $moveOutNote = '';

    // ── Feedback ─────────────────────────────────────────────────────────────
    public string $successMsg = '';
    public string $errorMsg   = '';

    public function mount(RentalProcess $rental): void
    {
        $this->rental   = $rental;
        $this->newStage = $rental->stage;
        $this->newRent  = (string) ($rental->monthly_rent ?? '');
        $this->paidAt   = now()->format('Y-m-d');
    }

    // ── Cambiar etapa ─────────────────────────────────────────────────────────

    public function changeStage(): void
    {
        $this->validate(['newStage' => 'required|string']);

        if ($this->newStage === $this->rental->stage) {
            $this->activePanel = '';
            return;
        }

        RentalStageLog::create([
            'rental_process_id' => $this->rental->id,
            'user_id'           => Auth::id(),
            'from_stage'        => $this->rental->stage,
            'to_stage'          => $this->newStage,
            'notes'             => $this->stageNote ?: 'Cambio manual desde gestión post-cierre',
        ]);

        $this->rental->update([
            'stage'    => $this->newStage,
            'status'   => $this->newStage === 'cerrado' ? 'completed' : $this->rental->status,
            'completed_at' => $this->newStage === 'cerrado' ? now() : $this->rental->completed_at,
        ]);

        $this->rental->refresh();
        $this->activePanel = '';
        $this->stageNote   = '';
        $this->successMsg  = 'Etapa actualizada a "' . ($this->rental->stage_label) . '".';
    }

    // ── Agregar nota ──────────────────────────────────────────────────────────

    public function addNota(): void
    {
        $this->validate(['notaText' => 'required|string|min:3|max:500']);

        RentalStageLog::create([
            'rental_process_id' => $this->rental->id,
            'user_id'           => Auth::id(),
            'from_stage'        => $this->rental->stage,
            'to_stage'          => $this->rental->stage,
            'notes'             => '📝 ' . $this->notaText,
        ]);

        $this->rental->load('stageLogs.user');
        $this->activePanel = '';
        $this->notaText    = '';
        $this->successMsg  = 'Nota agregada.';
    }

    // ── Generar pagos del contrato ────────────────────────────────────────────

    public function generatePayments(): void
    {
        if (! $this->rental->lease_start_date || ! $this->rental->lease_end_date) {
            $this->errorMsg = 'El contrato no tiene fechas de inicio/fin.';
            return;
        }

        if (! Schema::hasTable('rental_payments')) {
            $this->errorMsg = 'La tabla de pagos aún no existe. Corre php artisan migrate.';
            return;
        }

        $start  = $this->rental->lease_start_date->copy()->startOfMonth();
        $end    = $this->rental->lease_end_date->copy()->startOfMonth();
        $amount = $this->rental->monthly_rent ?? 0;
        $count  = 0;

        for ($d = $start->copy(); $d->lte($end); $d->addMonth()) {
            RentalPayment::firstOrCreate(
                ['rental_process_id' => $this->rental->id, 'period' => $d->format('Y-m-d')],
                ['amount' => $amount, 'status' => 'pending']
            );
            $count++;
        }

        $this->rental->load('payments');
        $this->successMsg = "{$count} períodos de pago generados.";
    }

    // ── Marcar pago como pagado ───────────────────────────────────────────────

    public function markPaid(int $paymentId): void
    {
        if (! Schema::hasTable('rental_payments')) return;

        $payment = RentalPayment::where('rental_process_id', $this->rental->id)->find($paymentId);
        if (! $payment) return;

        $this->validate([
            'paidAt'      => 'required|date',
            'paymentNote' => 'nullable|string|max:200',
        ]);

        $payment->update([
            'status'      => 'paid',
            'paid_at'     => $this->paidAt,
            'notes'       => $this->paymentNote ?: null,
            'recorded_by' => Auth::id(),
        ]);

        $this->rental->load('payments');
        $this->activePanel  = '';
        $this->paymentId    = null;
        $this->paymentNote  = '';
        $this->successMsg   = 'Pago registrado como pagado.';
    }

    public function markLate(int $paymentId): void
    {
        if (! Schema::hasTable('rental_payments')) return;
        $payment = RentalPayment::where('rental_process_id', $this->rental->id)->find($paymentId);
        if ($payment) {
            $payment->update(['status' => 'late', 'recorded_by' => Auth::id()]);
            $this->rental->load('payments');
            $this->successMsg = 'Pago marcado como atrasado.';
        }
    }

    // ── Renovar contrato ──────────────────────────────────────────────────────

    public function renovar(): void
    {
        $this->validate([
            'newEndDate'   => 'required|date|after:today',
            'newRent'      => 'required|numeric|min:1',
            'renewalNote'  => 'nullable|string|max:300',
        ]);

        $oldEnd  = $this->rental->lease_end_date?->format('d/m/Y') ?? '—';
        $oldRent = $this->rental->monthly_rent ?? 0;

        $this->rental->update([
            'lease_end_date' => $this->newEndDate,
            'monthly_rent'   => (float) $this->newRent,
            'stage'          => 'activo',
        ]);

        RentalStageLog::create([
            'rental_process_id' => $this->rental->id,
            'user_id'           => Auth::id(),
            'from_stage'        => 'renovacion',
            'to_stage'          => 'activo',
            'notes'             => '🔄 Renovación: vencimiento ' . $oldEnd . ' → ' . \Carbon\Carbon::parse($this->newEndDate)->format('d/m/Y')
                                 . ' · Renta $' . number_format($oldRent) . ' → $' . number_format((float)$this->newRent)
                                 . ($this->renewalNote ? ' · ' . $this->renewalNote : ''),
        ]);

        $this->rental->refresh()->load('stageLogs.user');
        $this->activePanel  = '';
        $this->renewalNote  = '';
        $this->successMsg   = 'Contrato renovado correctamente.';
    }

    // ── Programar move-out ────────────────────────────────────────────────────

    public function scheduleMoveOut(): void
    {
        $this->validate([
            'moveOutDate' => 'required|date',
            'moveOutNote' => 'nullable|string|max:300',
        ]);

        $updateData = ['stage' => 'renovacion'];

        if (Schema::hasColumn('rental_processes', 'move_out_scheduled_at')) {
            $updateData['move_out_scheduled_at'] = $this->moveOutDate;
        }

        $this->rental->update($updateData);

        RentalStageLog::create([
            'rental_process_id' => $this->rental->id,
            'user_id'           => Auth::id(),
            'from_stage'        => $this->rental->stage,
            'to_stage'          => 'renovacion',
            'notes'             => '📦 Move-out programado: ' . \Carbon\Carbon::parse($this->moveOutDate)->format('d/m/Y')
                                 . ($this->moveOutNote ? ' · ' . $this->moveOutNote : ''),
        ]);

        $this->rental->refresh()->load('stageLogs.user');
        $this->activePanel  = '';
        $this->moveOutNote  = '';
        $this->successMsg   = 'Move-out programado para ' . \Carbon\Carbon::parse($this->moveOutDate)->format('d/m/Y') . '.';
    }

    public function clearMsg(): void
    {
        $this->successMsg = '';
        $this->errorMsg   = '';
    }

    public function render()
    {
        $hasPayments = Schema::hasTable('rental_payments');
        $payments    = $hasPayments
            ? RentalPayment::where('rental_process_id', $this->rental->id)
                ->orderBy('period')
                ->get()
            : collect();

        return view('livewire.admin.rentas-gestion-show', [
            'stages'      => \App\Models\RentalProcess::STAGES,
            'hasPayments' => $hasPayments,
            'payments'    => $payments,
        ]);
    }
}
