<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Client;
use App\Models\Broker;
use App\Models\Deal;
use App\Models\Transaction;
use App\Models\Commission;
use App\Models\Task;

class AnalyticsController extends Controller
{
    public function index()
    {
        // KPIs
        $kpis = [
            'properties_active' => Property::where('status', 'active')->count(),
            'deals_pipeline'    => Deal::whereNotIn('stage', ['won', 'lost'])->count(),
            'revenue_month'     => Transaction::where('type', 'income')
                                    ->whereMonth('date', now()->month)
                                    ->whereYear('date', now()->year)
                                    ->sum('amount'),
            'conversion_rate'   => $this->conversionRate(),
        ];

        // Deals by stage
        $stages      = ['lead', 'contact', 'visit', 'negotiation', 'offer', 'closing', 'won', 'lost'];
        $stageLabels = ['Lead', 'Contacto', 'Visita', 'Negociacion', 'Oferta', 'Cierre', 'Ganado', 'Perdido'];
        $dealsByStage = [];
        foreach ($stages as $i => $stage) {
            $dealsByStage[] = [
                'stage' => $stageLabels[$i],
                'count' => Deal::where('stage', $stage)->count(),
            ];
        }

        // Monthly revenue vs expenses (last 6 months)
        $monthlyFinance = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $m    = $date->month;
            $y    = $date->year;
            $monthlyFinance[] = [
                'label'   => $date->format('M'),
                'income'  => (float) Transaction::where('type', 'income')->whereMonth('date', $m)->whereYear('date', $y)->sum('amount'),
                'expense' => (float) Transaction::where('type', 'expense')->whereMonth('date', $m)->whereYear('date', $y)->sum('amount'),
            ];
        }

        // Top 5 brokers by commissions
        $topBrokers = Broker::select('brokers.*')
            ->selectRaw('COALESCE((SELECT SUM(amount) FROM commissions WHERE commissions.broker_id = brokers.id AND commissions.status = "paid"), 0) as total_commissions')
            ->orderByDesc('total_commissions')
            ->limit(5)
            ->get();

        // Top 5 properties by price
        $topProperties = Property::orderByDesc('price')->limit(5)->get();

        // New clients per month (last 6)
        $clientsMonthly = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $clientsMonthly[] = [
                'label' => $date->format('M'),
                'count' => Client::whereMonth('created_at', $date->month)
                                 ->whereYear('created_at', $date->year)
                                 ->count(),
            ];
        }

        // Properties by type distribution
        $propertyTypes = Property::selectRaw('property_type, COUNT(*) as count')
            ->whereNotNull('property_type')
            ->groupBy('property_type')
            ->orderByDesc('count')
            ->get();

        // Tasks overview
        $taskStats = [
            'pending'        => Task::where('status', 'pending')->count(),
            'in_progress'    => Task::where('status', 'in_progress')->count(),
            'overdue'        => Task::where('status', '!=', 'completed')
                                    ->whereNotNull('due_date')
                                    ->whereDate('due_date', '<', now())
                                    ->count(),
            'completed_week' => Task::where('status', 'completed')
                                    ->where('completed_at', '>=', now()->subWeek())
                                    ->count(),
        ];

        return view('admin.analytics.index', compact(
            'kpis',
            'dealsByStage',
            'monthlyFinance',
            'topBrokers',
            'topProperties',
            'clientsMonthly',
            'propertyTypes',
            'taskStats'
        ));
    }

    private function conversionRate(): float
    {
        $total = Deal::count();
        if ($total === 0) {
            return 0;
        }
        $won = Deal::where('stage', 'won')->count();

        return round(($won / $total) * 100, 1);
    }
}
