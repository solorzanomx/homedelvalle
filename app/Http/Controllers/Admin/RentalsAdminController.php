<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Operation;
use App\Models\RentalProcess;
use Illuminate\Support\Facades\Schema;

class RentalsAdminController extends Controller
{
    // ── Fase 1 — Captación de renta ────────────────────────────────────────────

    public function captaciones()
    {
        $hasIntent = Schema::hasColumn('operations', 'intent');

        $captaciones = Operation::where('type', 'captacion')
            ->when($hasIntent, fn($q) => $q->where(function ($q) {
                $q->where('intent', 'renta')->orWhereNull('intent');
            }))
            ->with(['client', 'property', 'user'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('stage');

        $stages = Operation::CAPTACION_STAGES;

        $stats = [
            'total'    => $captaciones->flatten()->count(),
            'esta_sem' => Operation::where('type', 'captacion')
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            'sin_asig' => Operation::where('type', 'captacion')
                ->whereNull('user_id')
                ->count(),
        ];

        return view('admin.rentas.captaciones', compact('captaciones', 'stages', 'stats'));
    }

    // ── Fase 2 — Colocación (kanban Livewire RentasKanbanFase2) ──────────────

    public function activas()
    {
        // El kanban Livewire maneja toda la lógica de datos y filtros.
        // Esta acción solo renderiza el layout con el componente.
        return view('admin.rentas.activas');
    }

    // ── Fase 3 — Gestión post-cierre ──────────────────────────────────────────

    public function gestion()
    {
        $activas = RentalProcess::whereNotIn('status', ['completed', 'cancelled'])
            ->with(['property', 'ownerClient', 'tenantClient'])
            ->orderBy('lease_end_date')
            ->get();

        $renovacion = $activas->filter(function ($r) {
            if (! $r->lease_end_date) return false;
            $days = now()->diffInDays($r->lease_end_date, false);
            return $days > 0 && $days <= 60;
        });

        // move_out_scheduled_at es columna de Fase 1 schema — guardar si no existe
        if (Schema::hasColumn('rental_processes', 'move_out_scheduled_at')) {
            $moveout = RentalProcess::whereNotNull('move_out_scheduled_at')
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->with(['property', 'tenantClient'])
                ->orderBy('move_out_scheduled_at')
                ->get();
        } else {
            $moveout = collect();
        }

        $cerradas = RentalProcess::where('status', 'completed')
            ->with(['property', 'ownerClient', 'tenantClient'])
            ->orderByDesc('completed_at')
            ->limit(50)
            ->get();

        $stats = [
            'activas'    => $activas->count(),
            'renovacion' => $renovacion->count(),
            'moveout'    => $moveout->count(),
        ];

        return view('admin.rentas.gestion', compact(
            'activas', 'renovacion', 'moveout', 'cerradas', 'stats'
        ));
    }

    // ── Detalle de un RentalProcess ───────────────────────────────────────────

    public function show(RentalProcess $rental)
    {
        // Cargar solo relaciones que existen definitivamente
        $relations = ['property', 'ownerClient', 'tenantClient', 'stageLogs.user', 'documents'];

        // polizaJuridica: cargar solo si la relación está definida en el modelo
        if (method_exists($rental, 'polizaJuridica')) {
            $relations[] = 'polizaJuridica';
        }

        $rental->load($relations);

        return view('admin.rentas.show', compact('rental'));
    }
}
