<?php

namespace App\Services;

use App\Models\LeadEvent;
use App\Models\LeadScore;
use App\Models\LeadScoreRule;
use Illuminate\Support\Facades\Log;

class LeadScoringService
{
    /**
     * Process an event and update the client's score.
     */
    public function processEvent(int $clientId, string $event, array $context = []): ?int
    {
        $rule = LeadScoreRule::active()->where('event', $event)->first();

        if (!$rule) return null;

        // Check daily cap
        if ($rule->max_per_day > 0) {
            $todayCount = LeadEvent::where('client_id', $clientId)
                ->where('event', $event)
                ->where('occurred_at', '>=', now()->startOfDay())
                ->count();

            if ($todayCount >= $rule->max_per_day) {
                return null;
            }
        }

        $score = LeadScore::getOrCreate($clientId);
        $points = $rule->points;

        // Determine which score bucket
        $engagementEvents = ['message_opened', 'message_replied', 'form_submitted'];
        $activityEvents = ['call_completed', 'visit_scheduled', 'visit_completed', 'task_completed'];

        if (in_array($event, $engagementEvents)) {
            $score->addPoints(engagement: $points);
        } elseif (in_array($event, $activityEvents)) {
            $score->addPoints(activity: $points);
        } else {
            $score->addPoints(engagement: $points);
        }

        // Record the event with score delta
        LeadEvent::record($clientId, $event, array_merge($context, [
            'score_delta' => $points,
            'source' => $context['source'] ?? 'system',
        ]));

        return $score->total_score;
    }

    /**
     * Recalculate profile score for a client based on data completeness.
     */
    public function recalculateProfileScore(int $clientId): void
    {
        $client = \App\Models\Client::find($clientId);
        if (!$client) return;

        $points = 0;
        if ($client->email) $points += 5;
        if ($client->phone) $points += 5;
        if ($client->whatsapp) $points += 3;
        if ($client->city) $points += 3;
        if ($client->budget_min || $client->budget_max) $points += 5;
        if ($client->property_type) $points += 3;
        if ($client->interest_types && count($client->interest_types)) $points += 4;
        if ($client->initial_notes) $points += 2;

        $score = LeadScore::getOrCreate($clientId);
        $diff = $points - $score->profile_score;
        if ($diff !== 0) {
            $score->update(['profile_score' => $points]);
            $score->update([
                'total_score' => $score->engagement_score + $score->activity_score + $points,
            ]);
            $score->refresh();
            $score->recalculateGrade();
        }
    }

    /**
     * Get default scoring rules.
     */
    public static function getDefaultRules(): array
    {
        return [
            ['event' => 'message_sent',     'points' => 2,  'description' => 'Se le envio un mensaje',     'max_per_day' => 5],
            ['event' => 'message_opened',   'points' => 10, 'description' => 'Abrio un mensaje',           'max_per_day' => 3],
            ['event' => 'message_replied',  'points' => 30, 'description' => 'Respondio un mensaje',       'max_per_day' => 5],
            ['event' => 'call_completed',   'points' => 15, 'description' => 'Llamada completada',         'max_per_day' => 3],
            ['event' => 'visit_scheduled',  'points' => 25, 'description' => 'Visita agendada',            'max_per_day' => 2],
            ['event' => 'visit_completed',  'points' => 50, 'description' => 'Visita completada',          'max_per_day' => 2],
            ['event' => 'form_submitted',   'points' => 20, 'description' => 'Envio formulario',           'max_per_day' => 3],
            ['event' => 'deal_created',     'points' => 40, 'description' => 'Deal creado',                'max_per_day' => 0],
            ['event' => 'pipeline_entered', 'points' => 50, 'description' => 'Entro a pipeline',           'max_per_day' => 0],
            ['event' => 'stage_changed',    'points' => 15, 'description' => 'Cambio de etapa',            'max_per_day' => 3],
            ['event' => 'task_completed',   'points' => 10, 'description' => 'Tarea completada',           'max_per_day' => 5],
            ['event' => 'segment_entered',  'points' => 5,  'description' => 'Entro a segmento',           'max_per_day' => 3],
        ];
    }
}
