<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Deal;
use App\Models\MarketingCampaign;
use App\Models\MarketingChannel;
use App\Models\Transaction;
use Illuminate\Http\Request;

class MarketingController extends Controller
{
    public function dashboard()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // KPIs
        $leadsThisMonth = Client::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)->count();

        $avgCostPerLead = Client::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->whereNotNull('acquisition_cost')
            ->avg('acquisition_cost') ?? 0;

        $totalClients = Client::count();
        $clientsWithWonDeals = Client::whereHas('deals', fn($q) => $q->where('stage', 'won'))->count();
        $conversionRate = $totalClients > 0 ? round(($clientsWithWonDeals / $totalClients) * 100, 1) : 0;

        $marketingSpendMonth = Transaction::where('type', 'expense')
            ->where('category', 'marketing')
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->sum('amount');

        // Channel analytics
        $channels = MarketingChannel::active()->ordered()
            ->withCount('clients')
            ->get();

        $channelStats = [];
        $maxCpl = 0;
        $maxRoi = 0;

        foreach ($channels as $channel) {
            $channelClients = Client::where('marketing_channel_id', $channel->id);
            $totalLeads = $channel->clients_count;
            $totalCost = (clone $channelClients)->sum('acquisition_cost');
            $cpl = $totalLeads > 0 ? $totalCost / $totalLeads : 0;

            // Conversion funnel
            $clientIds = (clone $channelClients)->pluck('id');
            $contacted = Deal::whereIn('client_id', $clientIds)
                ->whereNotIn('stage', ['lead'])->distinct('client_id')->count('client_id');
            $visited = Deal::whereIn('client_id', $clientIds)
                ->whereNotIn('stage', ['lead', 'contact'])->distinct('client_id')->count('client_id');
            $won = Deal::whereIn('client_id', $clientIds)
                ->where('stage', 'won')->distinct('client_id')->count('client_id');

            // ROI
            $revenue = Deal::whereIn('client_id', $clientIds)->where('stage', 'won')->sum('amount');
            $roi = $totalCost > 0 ? round((($revenue - $totalCost) / $totalCost) * 100, 1) : 0;

            if ($cpl > $maxCpl) $maxCpl = $cpl;
            if (abs($roi) > $maxRoi) $maxRoi = abs($roi);

            $channelStats[] = [
                'channel' => $channel,
                'leads' => $totalLeads,
                'cost' => $totalCost,
                'cpl' => $cpl,
                'contacted' => $contacted,
                'visited' => $visited,
                'won' => $won,
                'conversion_rate' => $totalLeads > 0 ? round(($won / $totalLeads) * 100, 1) : 0,
                'revenue' => $revenue,
                'roi' => $roi,
            ];
        }

        // Monthly lead trend (last 6 months)
        $monthlyLeads = [];
        $maxMonthLeads = 0;
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $m = $date->month;
            $y = $date->year;
            $count = Client::whereMonth('created_at', $m)->whereYear('created_at', $y)->count();
            if ($count > $maxMonthLeads) $maxMonthLeads = $count;
            $monthlyLeads[] = [
                'label' => $date->format('M'),
                'count' => $count,
            ];
        }

        // Recommendations
        $recommendations = [];
        foreach ($channelStats as $stat) {
            if ($stat['leads'] === 0) continue;
            if ($stat['roi'] > 200) {
                $recommendations[] = ['channel' => $stat['channel']->name, 'type' => 'increase', 'message' => 'ROI de ' . $stat['roi'] . '%. Aumentar presupuesto.'];
            } elseif ($stat['roi'] < 0 && $stat['cost'] > 0) {
                $recommendations[] = ['channel' => $stat['channel']->name, 'type' => 'pause', 'message' => 'ROI negativo (' . $stat['roi'] . '%). Revisar o pausar.'];
            } elseif ($stat['leads'] >= 5 && $stat['conversion_rate'] < 10) {
                $recommendations[] = ['channel' => $stat['channel']->name, 'type' => 'optimize', 'message' => $stat['leads'] . ' leads pero solo ' . $stat['conversion_rate'] . '% conversion. Mejorar calificacion.'];
            }
        }

        // Campaign performance
        $campaigns = MarketingCampaign::with('channel')
            ->withCount('clients')
            ->get()
            ->map(function ($campaign) {
                $clientIds = $campaign->clients()->pluck('id');
                $won = Deal::whereIn('client_id', $clientIds)->where('stage', 'won')->count();
                $revenue = Deal::whereIn('client_id', $clientIds)->where('stage', 'won')->sum('amount');
                $cpl = $campaign->clients_count > 0 ? $campaign->spent / $campaign->clients_count : 0;
                $roi = $campaign->spent > 0 ? round((($revenue - $campaign->spent) / $campaign->spent) * 100, 1) : 0;

                return [
                    'campaign' => $campaign,
                    'leads' => $campaign->clients_count,
                    'cpl' => $cpl,
                    'won' => $won,
                    'revenue' => $revenue,
                    'roi' => $roi,
                ];
            });

        return view('admin.marketing.dashboard', compact(
            'leadsThisMonth', 'avgCostPerLead', 'conversionRate', 'marketingSpendMonth',
            'channelStats', 'maxCpl', 'maxRoi',
            'monthlyLeads', 'maxMonthLeads',
            'recommendations', 'campaigns'
        ));
    }

    public function channels()
    {
        $channels = MarketingChannel::ordered()->withCount('clients')->get();
        $types = ['paid' => 'Pagado', 'organic' => 'Organico', 'referral' => 'Referido', 'direct' => 'Directo'];
        return view('admin.marketing.channels', compact('channels', 'types'));
    }

    public function storeChannel(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:paid,organic,referral,direct',
            'color' => 'required|string|max:7',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = MarketingChannel::max('sort_order') + 1;

        MarketingChannel::create($validated);
        return redirect()->route('admin.marketing.channels')->with('success', 'Canal creado exitosamente.');
    }

    public function updateChannel(Request $request, MarketingChannel $channel)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:paid,organic,referral,direct',
            'color' => 'required|string|max:7',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $channel->update($validated);
        return redirect()->route('admin.marketing.channels')->with('success', 'Canal actualizado.');
    }

    public function destroyChannel(MarketingChannel $channel)
    {
        $channel->delete();
        return redirect()->route('admin.marketing.channels')->with('success', 'Canal eliminado.');
    }

    public function campaigns(Request $request)
    {
        $query = MarketingCampaign::with('channel')->withCount('clients');

        if ($channelId = $request->input('channel_id')) {
            $query->where('marketing_channel_id', $channelId);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $campaigns = $query->latest()->paginate(20)->withQueryString();
        $channels = MarketingChannel::active()->ordered()->get();
        return view('admin.marketing.campaigns', compact('campaigns', 'channels'));
    }

    public function createCampaign()
    {
        $channels = MarketingChannel::active()->ordered()->get();
        $campaign = null;
        return view('admin.marketing.campaign-form', compact('channels', 'campaign'));
    }

    public function storeCampaign(Request $request)
    {
        $validated = $request->validate([
            'marketing_channel_id' => 'required|exists:marketing_channels,id',
            'name' => 'required|string|max:255',
            'budget' => 'required|numeric|min:0',
            'spent' => 'nullable|numeric|min:0',
            'currency' => 'required|in:MXN,USD',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:active,paused,completed',
            'notes' => 'nullable|string',
        ]);

        $validated['spent'] = $validated['spent'] ?? 0;

        MarketingCampaign::create($validated);
        return redirect()->route('admin.marketing.campaigns')->with('success', 'Campana creada exitosamente.');
    }

    public function editCampaign(MarketingCampaign $campaign)
    {
        $channels = MarketingChannel::active()->ordered()->get();
        return view('admin.marketing.campaign-form', compact('channels', 'campaign'));
    }

    public function updateCampaign(Request $request, MarketingCampaign $campaign)
    {
        $validated = $request->validate([
            'marketing_channel_id' => 'required|exists:marketing_channels,id',
            'name' => 'required|string|max:255',
            'budget' => 'required|numeric|min:0',
            'spent' => 'nullable|numeric|min:0',
            'currency' => 'required|in:MXN,USD',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:active,paused,completed',
            'notes' => 'nullable|string',
        ]);

        $validated['spent'] = $validated['spent'] ?? 0;

        $campaign->update($validated);
        return redirect()->route('admin.marketing.campaigns')->with('success', 'Campana actualizada.');
    }

    public function destroyCampaign(MarketingCampaign $campaign)
    {
        $campaign->delete();
        return redirect()->route('admin.marketing.campaigns')->with('success', 'Campana eliminada.');
    }
}
