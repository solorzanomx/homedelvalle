<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Segment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SegmentService
{
    /**
     * Evaluate all active segments and update memberships.
     */
    public function evaluateAll(): array
    {
        $stats = ['evaluated' => 0, 'entered' => 0, 'exited' => 0];

        Segment::active()->each(function (Segment $segment) use (&$stats) {
            $result = $this->evaluate($segment);
            $stats['evaluated']++;
            $stats['entered'] += $result['entered'];
            $stats['exited'] += $result['exited'];
        });

        return $stats;
    }

    /**
     * Evaluate a single segment: add/remove client memberships.
     */
    public function evaluate(Segment $segment): array
    {
        $matchingIds = $this->buildQuery($segment->rules)->pluck('clients.id');
        $currentIds = $segment->clients()->wherePivotNull('exited_at')->pluck('clients.id');

        $toEnter = $matchingIds->diff($currentIds);
        $toExit = $currentIds->diff($matchingIds);

        $entered = 0;
        $exited = 0;

        foreach ($toEnter as $clientId) {
            $existing = DB::table('client_segment')
                ->where('client_id', $clientId)
                ->where('segment_id', $segment->id)
                ->whereNull('exited_at')
                ->exists();

            if (!$existing) {
                $segment->clients()->attach($clientId, ['entered_at' => now()]);
                $entered++;

                // Fire event for automation triggers
                \App\Models\LeadEvent::record($clientId, 'segment_entered', [
                    'source' => 'system',
                    'properties' => ['segment_id' => $segment->id, 'segment_name' => $segment->name],
                ]);
            }
        }

        foreach ($toExit as $clientId) {
            DB::table('client_segment')
                ->where('client_id', $clientId)
                ->where('segment_id', $segment->id)
                ->whereNull('exited_at')
                ->update(['exited_at' => now()]);
            $exited++;

            \App\Models\LeadEvent::record($clientId, 'segment_exited', [
                'source' => 'system',
                'properties' => ['segment_id' => $segment->id, 'segment_name' => $segment->name],
            ]);
        }

        $segment->update([
            'cached_count' => $matchingIds->count(),
            'last_evaluated_at' => now(),
        ]);

        return ['entered' => $entered, 'exited' => $exited];
    }

    /**
     * Build an Eloquent query from segment rules.
     * Rules format: [ { "field": "...", "operator": "...", "value": "..." }, ... ]
     * All rules are AND-joined.
     */
    public function buildQuery(array $rules): \Illuminate\Database\Eloquent\Builder
    {
        $query = Client::query();

        foreach ($rules as $rule) {
            $field = $rule['field'] ?? null;
            $op = $rule['operator'] ?? 'equals';
            $value = $rule['value'] ?? null;

            if (!$field) continue;

            // Virtual fields requiring joins/subqueries
            match ($field) {
                'total_score' => $query->whereHas('leadScore', fn($q) => $this->applyOperator($q, 'total_score', $op, $value)),
                'grade' => $query->whereHas('leadScore', fn($q) => $this->applyOperator($q, 'grade', $op, $value)),
                'days_inactive' => $this->applyDaysInactive($query, $op, (int) $value),
                'has_deal' => $value ? $query->whereHas('deals') : $query->whereDoesntHave('deals'),
                'has_operation' => $value ? $query->whereHas('operations') : $query->whereDoesntHave('operations'),
                'emails_opened' => $query->whereHas('messages', fn($q) => $q->whereNotNull('opened_at'), $op === 'greater_than' ? '>=' : '<=', (int) $value),
                'last_interaction' => $this->applyLastInteraction($query, $op, $value),
                default => $this->applyOperator($query, $field, $op, $value),
            };
        }

        return $query;
    }

    private function applyOperator($query, string $field, string $op, mixed $value): void
    {
        match ($op) {
            'equals'              => $query->where($field, $value),
            'not_equals'          => $query->where($field, '!=', $value),
            'contains'            => $query->where($field, 'like', "%{$value}%"),
            'not_contains'        => $query->where($field, 'not like', "%{$value}%"),
            'greater_than'        => $query->where($field, '>', $value),
            'less_than'           => $query->where($field, '<', $value),
            'is_empty'            => $query->whereNull($field),
            'is_not_empty'        => $query->whereNotNull($field),
            'in'                  => $query->whereIn($field, (array) $value),
            'not_in'              => $query->whereNotIn($field, (array) $value),
            'days_ago_more_than'  => $query->where($field, '<', now()->subDays((int) $value)),
            'days_ago_less_than'  => $query->where($field, '>', now()->subDays((int) $value)),
            default               => null,
        };
    }

    private function applyDaysInactive($query, string $op, int $days): void
    {
        $threshold = now()->subDays($days);
        $sub = \App\Models\Interaction::selectRaw('MAX(created_at)')
            ->whereColumn('client_id', 'clients.id');

        if ($op === 'greater_than') {
            $query->where(function ($q) use ($sub, $threshold) {
                $q->whereNull(DB::raw("({$sub->toSql()})"))
                  ->orWhere(DB::raw("({$sub->toSql()})"), '<', $threshold);
            })->addBinding($sub->getBindings(), 'where')->addBinding($sub->getBindings(), 'where');
        } else {
            $query->where(DB::raw("({$sub->toSql()})"), '>=', $threshold)
                  ->addBinding($sub->getBindings(), 'where');
        }
    }

    private function applyLastInteraction($query, string $op, mixed $value): void
    {
        $this->applyDaysInactive($query, $op === 'days_ago_more_than' ? 'greater_than' : 'less_than', (int) $value);
    }

    /**
     * Preview which clients match a set of rules (without saving).
     */
    public function preview(array $rules): \Illuminate\Database\Eloquent\Collection
    {
        return $this->buildQuery($rules)->limit(50)->get(['id', 'name', 'email', 'lead_temperature', 'city']);
    }
}
