<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Operation;
use App\Models\RentalProcess;

/**
 * Track B — Funnel de Rentas
 * Vistas dedicadas /admin/rentas/* separadas del genérico /admin/operations
 *
 * PR Rentas-1: vistas placeholder con datos reales.
 * PR Rentas-2: kanban interactivo Fase 1 (captación).
 * PR Rentas-3: kanban interactivo Fase 2 (colocación).
 * PR Rentas-4: gestión post-cierre completa.
 */
class RentalsAdminController extends Controller
{
    // ── Fase 1 — Captación de renta ────────────────────────────────────────────

    public function captaciones()
    {
        // intent es columna opcional (Fase 1 schema). Si no existe, mostrar todas las captaciones.
        $hasIntent = \Illuminate\Support\Facades\Schema::hasColumn('operations', 'intent');

        $captaciones = Operation::where('type', 'captacion')
            ->when($hasIntent, fn($q) => $q->where(function ($q) {
                $q->where('intent', 'renta')->orWhereNull('intent');
            }))
            ->with(['client', 'property', 'user'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('stage');

        $stages = \App\Models\Operation::CAPTACION_STAGES;

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

    // ── Fase 2 — Colocación (rentas activas en mercado) ───────────────────────

    public function activas()
    {
        $operaciones = Operation::where('type', 'renta')
            ->where('status', 'active')
            ->with(['client', 'property', 'assignedUser'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('stage');

        $stages = \App\Models\Operation::RENTA_STAGES;

        $stats = [
            'total'       => $operaciones->flatten()->count(),
            'en_busqueda' => Operation::where('type', 'renta')->where('stage', 'busqueda')->count(),
            'con_oferta'  => Operation::where('type', 'renta')->where('stage', 'investigacion')->count(),
            'por_firmar'  => Operation::where('type', 'renta')->where('stage', 'contrato')->count(),
        ];

        return view('admin.rentas.activas', compact('operaciones', 'stages', 'stats'));
    }

    // ── Fase 3 — Gestión post-cierre ──────────────────────────────────────────

    public function gestion()
    {
        $activas = RentalProcess::where('status', 'active')
            ->with(['property', 'ownerClient', 'tenantClient', 'assignedUser'])
            ->orderBy('lease_end_date')
            ->get();

        $renovacion = $activas->filter(function ($r) {
            return $r->lease_end_date
                && now()->diffInDays($r->lease_end_date, false) <= 60
                && now()->diffInDays($r->lease_end_date, false) > 0;
        });

        $moveout = RentalProcess::whereNotNull('move_out_scheduled_at')
            ->where('status', 'active')
            ->with(['property', 'tenantClient'])
            ->orderBy('move_out_scheduled_at')
            ->get();

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
        $rental->load([
            'property',
            'ownerClient',
            'tenantClient',
            'assignedUser',
            'stageLogs.user',
            'documents',
            'polizaJuridica',
        ]);

        return view('admin.rentas.show', compact('rental'));
    }
}
