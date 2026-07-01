<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Interaction;
use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PortalPropertyController extends Controller
{
    public function show()
    {
        $user   = Auth::user();
        $client = app(\App\Services\ClientPortalService::class)->getClientForUser($user);

        if (!$client) {
            abort(404);
        }

        // Get the primary property (from captación or owned properties)
        $captacion = \App\Models\Captacion::where('client_id', $client->id)
            ->where('status', 'activo')
            ->with('property')
            ->latest()->first();

        $property = $captacion?->property
            ?? Property::where('client_id', $client->id)->latest()->first();

        if (!$property) {
            return view('portal.mi-inmueble', ['property' => null, 'client' => $client]);
        }

        // Visit stats
        $visits = Interaction::where('property_id', $property->id)
            ->where('type', 'visit')
            ->whereNotNull('scheduled_at')
            ->orderByDesc('scheduled_at')
            ->get();

        $totalVisits     = $visits->count();
        $confirmedVisits = $visits->whereNotNull('confirmed_at')->count();
        $confirmRate     = $totalVisits > 0 ? round(($confirmedVisits / $totalVisits) * 100) : 0;
        $daysOnMarket    = $captacion ? (int) ($captacion->created_at->diffInDays(now())) : null;

        // Weekly pulse (last 4 weeks)
        $weeklyData = collect(range(3, 0))->map(function ($weeksAgo) use ($visits) {
            $start = Carbon::now()->startOfWeek()->subWeeks($weeksAgo);
            $end   = $start->copy()->endOfWeek();
            return [
                'label' => $start->locale('es')->isoFormat('D MMM'),
                'count' => $visits->filter(fn($v) =>
                    $v->scheduled_at >= $start && $v->scheduled_at <= $end
                )->count(),
            ];
        });

        // Reaction summary
        $withFeedback = $visits->whereNotNull('visitor_reaction');
        $reactionSummary = [
            'liked'   => $withFeedback->where('visitor_reaction', 'liked')->count(),
            'neutral' => $withFeedback->where('visitor_reaction', 'neutral')->count(),
            'disliked'=> $withFeedback->where('visitor_reaction', 'disliked')->count(),
            'total'   => $withFeedback->count(),
        ];

        // Price perception summary (only visits that answered)
        $withPrice = $visits->whereNotNull('price_perception');
        $priceTotal = $withPrice->count();
        $priceSummary = [
            'fair'       => $withPrice->where('price_perception', 'fair')->count(),
            'negotiable' => $withPrice->where('price_perception', 'negotiable')->count(),
            'high'       => $withPrice->where('price_perception', 'high')->count(),
            'total'      => $priceTotal,
        ];

        // Anonymous comments (no names, just comment + date)
        $comments = $visits->whereNotNull('visitor_comment')
            ->map(fn($v) => [
                'comment'  => $v->visitor_comment,
                'reaction' => $v->visitor_reaction,
                'date'     => $v->feedback_submitted_at,
            ])
            ->values();

        return view('portal.mi-inmueble', compact(
            'client', 'property', 'captacion', 'visits',
            'totalVisits', 'confirmedVisits', 'confirmRate', 'daysOnMarket',
            'weeklyData', 'reactionSummary', 'priceSummary', 'comments'
        ));
    }
}
