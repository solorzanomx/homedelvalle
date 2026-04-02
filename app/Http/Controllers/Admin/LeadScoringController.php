<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeadScore;
use App\Models\LeadScoreRule;
use App\Models\LeadEvent;
use Illuminate\Http\Request;

class LeadScoringController extends Controller
{
    public function index(Request $request)
    {
        $scores = LeadScore::with('client')
            ->orderByDesc('total_score')
            ->paginate(30);

        $rules = LeadScoreRule::orderBy('event')->get();

        $gradeDistribution = LeadScore::selectRaw('grade, COUNT(*) as count')
            ->groupBy('grade')
            ->orderByDesc('count')
            ->pluck('count', 'grade');

        return view('admin.scoring.index', compact('scores', 'rules', 'gradeDistribution'));
    }

    public function updateRules(Request $request)
    {
        $rules = $request->validate([
            'rules' => 'required|array',
            'rules.*.id' => 'required|exists:lead_score_rules,id',
            'rules.*.points' => 'required|integer',
            'rules.*.is_active' => 'boolean',
            'rules.*.max_per_day' => 'integer|min:0',
        ]);

        foreach ($rules['rules'] as $ruleData) {
            LeadScoreRule::where('id', $ruleData['id'])->update([
                'points' => $ruleData['points'],
                'is_active' => $ruleData['is_active'] ?? true,
                'max_per_day' => $ruleData['max_per_day'] ?? 0,
            ]);
        }

        return back()->with('success', 'Reglas de scoring actualizadas');
    }

    public function clientTimeline(int $clientId)
    {
        $events = LeadEvent::where('client_id', $clientId)
            ->orderByDesc('occurred_at')
            ->limit(50)
            ->get();

        $score = LeadScore::where('client_id', $clientId)->first();

        return response()->json(['events' => $events, 'score' => $score]);
    }
}
