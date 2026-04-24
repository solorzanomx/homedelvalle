<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Broker;
use App\Models\Client;
use App\Models\User;
use App\Models\Property;
use App\Models\Task;
use App\Models\Captacion;
use App\Models\Operation;
use App\Models\Interaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = now()->startOfDay();
        $weekStart = now()->startOfWeek();
        $monthStart = now()->startOfMonth();

        // ── URGENT: Clients without contact in 24h+ ──
        $staleClients = Client::where('created_at', '<', now()->subHours(24))
            ->whereDoesntHave('interactions')
            ->latest()
            ->take(10)
            ->get();

        // ── TODAY'S TASKS (follow-ups) ──
        $todayTasks = Task::with(['client', 'operation', 'property', 'user'])
            ->where('status', '!=', 'completed')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', today())
            ->orderBy('due_date')
            ->take(15)
            ->get();

        // ── OVERDUE TASKS ──
        $overdueCount = Task::where('status', '!=', 'completed')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', today())
            ->count();

        // ── QUICK STATS ──
        $newClientsWeek = Client::where('created_at', '>=', $weekStart)->count();

        $activeOperations = Operation::active()->count();

        $captacionesActive = Operation::active()->captaciones()->count();

        $operationsClosedMonth = Operation::where('status', 'completed')
            ->where('completed_at', '>=', $monthStart)
            ->count();

        // ── PIPELINE SUMMARY (active operations by stage) ──
        $pipelineSummary = Operation::active()
            ->selectRaw('type, stage, count(*) as total')
            ->groupBy('type', 'stage')
            ->get()
            ->groupBy('type');

        // ── RECENT INTERACTIONS ──
        $recentInteractions = Interaction::with(['client', 'user'])
            ->latest()
            ->take(8)
            ->get();

        // ── UPCOMING TASKS (next 7 days) ──
        $upcomingTasks = Task::with(['client', 'operation', 'property'])
            ->where('status', '!=', 'completed')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '>', today())
            ->whereDate('due_date', '<=', now()->addDays(7))
            ->orderBy('due_date')
            ->take(10)
            ->get();

        // ── CAPTACIONES EN PROGRESO ──
        $captacionesPipeline = Captacion::with(['client'])
            ->where('status', 'activo')
            ->latest()
            ->take(8)
            ->get();

        // ── LEGACY COUNTS (kept for compatibility) ──
        $propertiesCount = Property::count();
        $clientsCount = Client::count();
        $brokersCount = Broker::count();
        $usersCount = User::count();

        return view('admin.dashboard', compact(
            'user',
            'staleClients',
            'todayTasks',
            'overdueCount',
            'upcomingTasks',
            'newClientsWeek',
            'activeOperations',
            'captacionesActive',
            'operationsClosedMonth',
            'pipelineSummary',
            'recentInteractions',
            'captacionesPipeline',
            'propertiesCount',
            'clientsCount',
            'brokersCount',
            'usersCount',
        ));
    }
}
