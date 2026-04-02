<?php

namespace App\Services;

use App\Models\Automation;
use App\Models\AutomationEnrollment;
use App\Models\AutomationStep;
use App\Models\AutomationStepLog;
use App\Models\Client;
use App\Models\LeadEvent;
use App\Models\Message;
use App\Models\Operation;
use App\Models\Task;
use App\Services\EmailService;
use Illuminate\Support\Facades\Log;

class AutomationEngine
{
    public function __construct(
        private EmailService $emailService,
        private LeadScoringService $scoringService,
        private WhatsAppService $whatsAppService,
    ) {}

    // ──────────────────────────────────────────────────
    // ENROLLMENT
    // ──────────────────────────────────────────────────

    /**
     * Enroll a client into an automation.
     */
    public function enroll(Automation $automation, Client $client): ?AutomationEnrollment
    {
        // Check if already enrolled
        $exists = $automation->enrollments()
            ->where('client_id', $client->id)
            ->where('status', 'active')
            ->exists();

        if ($exists && !$automation->allow_reentry) {
            return null;
        }

        $firstStep = $automation->getFirstStep();
        if (!$firstStep) return null;

        $enrollment = AutomationEnrollment::create([
            'automation_id' => $automation->id,
            'client_id' => $client->id,
            'current_step' => $firstStep->position,
            'status' => 'active',
            'next_run_at' => now(),
        ]);

        $automation->increment('enrollment_count');

        Log::info("AutomationEngine: Enrolled client #{$client->id} in automation #{$automation->id}");

        return $enrollment;
    }

    /**
     * Enroll clients entering a segment.
     */
    public function processSegmentEnter(int $segmentId, int $clientId): void
    {
        $automations = Automation::active()
            ->where('trigger_type', 'segment_enter')
            ->get()
            ->filter(fn($a) => ($a->trigger_config['segment_id'] ?? null) == $segmentId);

        $client = Client::find($clientId);
        if (!$client) return;

        foreach ($automations as $automation) {
            $this->enroll($automation, $client);
        }
    }

    // ──────────────────────────────────────────────────
    // EXECUTION (called by scheduler)
    // ──────────────────────────────────────────────────

    /**
     * Process all ready enrollments.
     */
    public function processReadyEnrollments(): array
    {
        $stats = ['processed' => 0, 'executed' => 0, 'failed' => 0, 'completed' => 0];

        AutomationEnrollment::ready()
            ->with(['automation.steps', 'client'])
            ->chunk(100, function ($enrollments) use (&$stats) {
                foreach ($enrollments as $enrollment) {
                    $stats['processed']++;
                    $result = $this->executeStep($enrollment);
                    $stats[$result ? 'executed' : 'failed']++;

                    if ($enrollment->fresh()->status === 'completed') {
                        $stats['completed']++;
                    }
                }
            });

        return $stats;
    }

    /**
     * Execute the current step for an enrollment.
     */
    public function executeStep(AutomationEnrollment $enrollment): bool
    {
        $step = $enrollment->getCurrentStep();
        if (!$step) {
            $enrollment->markCompleted();
            return true;
        }

        try {
            $result = match ($step->type) {
                'delay'         => $this->executeDelay($enrollment, $step),
                'send_email'    => $this->executeSendEmail($enrollment, $step),
                'send_whatsapp' => $this->executeSendWhatsApp($enrollment, $step),
                'condition'     => $this->executeCondition($enrollment, $step),
                'create_task'   => $this->executeCreateTask($enrollment, $step),
                'move_pipeline' => $this->executeMovePipeline($enrollment, $step),
                'update_field'  => $this->executeUpdateField($enrollment, $step),
                'add_score'     => $this->executeAddScore($enrollment, $step),
                default         => ['success' => false, 'error' => "Unknown step type: {$step->type}"],
            };

            AutomationStepLog::create([
                'enrollment_id' => $enrollment->id,
                'step_id' => $step->id,
                'status' => $result['success'] ? 'executed' : 'failed',
                'result' => $result,
                'error' => $result['error'] ?? null,
                'executed_at' => now(),
            ]);

            if ($result['success'] && ($result['advance'] ?? true)) {
                $enrollment->advance();
            }

            return $result['success'];
        } catch (\Throwable $e) {
            Log::error("AutomationEngine: Step #{$step->id} failed", ['error' => $e->getMessage()]);

            AutomationStepLog::create([
                'enrollment_id' => $enrollment->id,
                'step_id' => $step->id,
                'status' => 'failed',
                'error' => $e->getMessage(),
                'executed_at' => now(),
            ]);

            return false;
        }
    }

    // ──────────────────────────────────────────────────
    // STEP EXECUTORS
    // ──────────────────────────────────────────────────

    private function executeDelay(AutomationEnrollment $enrollment, AutomationStep $step): array
    {
        $minutes = $step->getDelayMinutes();
        $enrollment->update(['next_run_at' => now()->addMinutes($minutes)]);

        return ['success' => true, 'advance' => false, 'delay_minutes' => $minutes];
    }

    private function executeSendEmail(AutomationEnrollment $enrollment, AutomationStep $step): array
    {
        $client = $enrollment->client;
        if (!$client->email) {
            return ['success' => false, 'error' => 'Client has no email'];
        }

        $config = $step->config;
        $subject = $this->replacePlaceholders($config['subject'] ?? 'Sin asunto', $client);
        $body = $this->replacePlaceholders($config['body'] ?? '', $client);

        // Create message record
        $message = Message::create([
            'client_id' => $client->id,
            'enrollment_id' => $enrollment->id,
            'channel' => 'email',
            'subject' => $subject,
            'body' => $body,
            'status' => 'queued',
        ]);

        // Send via EmailService
        $sent = $this->emailService->send($client->email, $subject, $body, $client->name);

        if ($sent) {
            $message->markSent();
            $this->scoringService->processEvent($client->id, 'message_sent', ['source' => 'automation']);
        } else {
            $message->markFailed();
        }

        return ['success' => $sent, 'message_id' => $message->id];
    }

    private function executeSendWhatsApp(AutomationEnrollment $enrollment, AutomationStep $step): array
    {
        $client = $enrollment->client;
        $phone = $client->whatsapp ?? $client->phone;
        if (!$phone) {
            return ['success' => false, 'error' => 'Client has no phone/whatsapp'];
        }

        $config = $step->config;
        $text = $this->replacePlaceholders($config['message'] ?? '', $client);

        $message = Message::create([
            'client_id' => $client->id,
            'enrollment_id' => $enrollment->id,
            'channel' => 'whatsapp',
            'body' => $text,
            'status' => 'queued',
        ]);

        $result = $this->whatsAppService->send($phone, $text);

        if ($result['success']) {
            $message->markSent();
            $message->update(['external_id' => $result['message_id'] ?? null]);
            $this->scoringService->processEvent($client->id, 'message_sent', ['source' => 'automation']);
        } else {
            $message->markFailed();
        }

        return ['success' => $result['success'], 'message_id' => $message->id];
    }

    private function executeCondition(AutomationEnrollment $enrollment, AutomationStep $step): array
    {
        $client = $enrollment->client;
        $config = $step->config;
        $field = $config['field'] ?? null;
        $operator = $config['operator'] ?? 'equals';
        $value = $config['value'] ?? null;

        $met = $this->evaluateCondition($client, $field, $operator, $value);

        if ($met) {
            // Continue to next step
            return ['success' => true, 'condition_met' => true];
        }

        // Condition not met — skip to step defined in config, or skip the next step
        $skipTo = $config['skip_to_position'] ?? null;
        if ($skipTo) {
            $enrollment->update(['current_step' => $skipTo, 'next_run_at' => now()]);
            return ['success' => true, 'advance' => false, 'condition_met' => false, 'skipped_to' => $skipTo];
        }

        // Default: skip next step (advance twice)
        $enrollment->advance();
        return ['success' => true, 'condition_met' => false];
    }

    private function executeCreateTask(AutomationEnrollment $enrollment, AutomationStep $step): array
    {
        $client = $enrollment->client;
        $config = $step->config;

        $task = Task::create([
            'client_id' => $client->id,
            'user_id' => $client->assigned_user_id ?? $enrollment->automation->created_by,
            'title' => $this->replacePlaceholders($config['title'] ?? 'Tarea automatica', $client),
            'description' => $this->replacePlaceholders($config['description'] ?? '', $client),
            'priority' => $config['priority'] ?? 'media',
            'status' => 'pending',
            'due_date' => now()->addDays((int) ($config['due_days'] ?? 3)),
        ]);

        return ['success' => true, 'task_id' => $task->id];
    }

    /**
     * CRITICAL: Move client to the existing operations pipeline.
     */
    private function executeMovePipeline(AutomationEnrollment $enrollment, AutomationStep $step): array
    {
        $client = $enrollment->client;
        $config = $step->config;

        $type = $config['operation_type'] ?? 'captacion'; // captacion, venta, renta
        $stage = $config['stage'] ?? 'lead';
        $assignUserId = $config['assign_user_id'] ?? $client->assigned_user_id ?? $enrollment->automation->created_by;

        // Check if client already has an active operation of this type
        $existingOp = Operation::where('client_id', $client->id)
            ->where('type', $type)
            ->whereNull('completed_at')
            ->whereNull('cancelled_at')
            ->first();

        if ($existingOp) {
            return ['success' => true, 'operation_id' => $existingOp->id, 'note' => 'Already has active operation'];
        }

        $operation = Operation::create([
            'type' => $type,
            'stage' => $stage,
            'status' => 'active',
            'client_id' => $client->id,
            'user_id' => $assignUserId,
            'property_id' => $config['property_id'] ?? null,
            'notes' => 'Creada automaticamente por automatizacion: ' . $enrollment->automation->name,
        ]);

        // Create initial task
        Task::create([
            'operation_id' => $operation->id,
            'client_id' => $client->id,
            'user_id' => $assignUserId,
            'title' => "Primer contacto: {$client->name}",
            'description' => "Lead calificado automaticamente. Hacer seguimiento inmediato.",
            'priority' => 'alta',
            'status' => 'pending',
            'due_date' => now()->addDay(),
        ]);

        $this->scoringService->processEvent($client->id, 'pipeline_entered', [
            'source' => 'automation',
            'properties' => ['operation_id' => $operation->id, 'type' => $type],
        ]);

        LeadEvent::record($client->id, 'pipeline_entered', [
            'source' => 'automation',
            'eventable_type' => Operation::class,
            'eventable_id' => $operation->id,
            'properties' => ['type' => $type, 'stage' => $stage, 'automation' => $enrollment->automation->name],
        ]);

        return ['success' => true, 'operation_id' => $operation->id];
    }

    private function executeUpdateField(AutomationEnrollment $enrollment, AutomationStep $step): array
    {
        $client = $enrollment->client;
        $config = $step->config;
        $field = $config['field'] ?? null;
        $value = $config['value'] ?? null;

        $allowed = ['lead_temperature', 'priority', 'property_type'];
        if (!$field || !in_array($field, $allowed)) {
            return ['success' => false, 'error' => "Field not allowed: {$field}"];
        }

        $client->update([$field => $value]);
        return ['success' => true, 'field' => $field, 'value' => $value];
    }

    private function executeAddScore(AutomationEnrollment $enrollment, AutomationStep $step): array
    {
        $points = (int) ($step->config['points'] ?? 0);
        $score = \App\Models\LeadScore::getOrCreate($enrollment->client_id);
        $score->addPoints(engagement: $points);

        return ['success' => true, 'points_added' => $points, 'new_total' => $score->total_score];
    }

    // ──────────────────────────────────────────────────
    // HELPERS
    // ──────────────────────────────────────────────────

    private function evaluateCondition(Client $client, ?string $field, string $operator, mixed $value): bool
    {
        $actual = match ($field) {
            'lead_temperature' => $client->lead_temperature,
            'priority' => $client->priority,
            'grade' => $client->grade,
            'total_score' => $client->score,
            'has_email' => !empty($client->email),
            'has_phone' => !empty($client->phone ?? $client->whatsapp),
            'last_message_opened' => $client->messages()->whereNotNull('opened_at')->exists(),
            'last_message_replied' => $client->messages()->whereNotNull('replied_at')->exists(),
            default => $client->{$field} ?? null,
        };

        return match ($operator) {
            'equals'       => $actual == $value,
            'not_equals'   => $actual != $value,
            'greater_than' => $actual > $value,
            'less_than'    => $actual < $value,
            'is_true'      => (bool) $actual === true,
            'is_false'     => (bool) $actual === false,
            default        => false,
        };
    }

    private function replacePlaceholders(string $text, Client $client): string
    {
        return str_replace(
            ['{{nombre}}', '{{email}}', '{{telefono}}', '{{ciudad}}', '{{temperatura}}'],
            [$client->name, $client->email ?? '', $client->phone ?? '', $client->city ?? '', $client->lead_temperature ?? ''],
            $text
        );
    }
}
