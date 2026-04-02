<?php

namespace App\Services;

use App\Models\AutomationRule;
use App\Models\AutomationLog;
use App\Models\Task;
use Illuminate\Support\Facades\Log;

class AutomationService
{
    public function evaluate(string $trigger, array $context = []): void
    {
        $rules = AutomationRule::where('is_active', true)->where('trigger', $trigger)->get();

        foreach ($rules as $rule) {
            try {
                if ($this->checkConditions($rule, $context)) {
                    $result = $this->executeAction($rule, $context);

                    AutomationLog::create([
                        'automation_rule_id' => $rule->id,
                        'trigger_data' => $context,
                        'action_result' => $result,
                        'status' => 'success',
                    ]);

                    $rule->update([
                        'last_triggered_at' => now(),
                        'trigger_count' => $rule->trigger_count + 1,
                    ]);
                } else {
                    AutomationLog::create([
                        'automation_rule_id' => $rule->id,
                        'trigger_data' => $context,
                        'action_result' => ['reason' => 'Conditions not met'],
                        'status' => 'skipped',
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Automation rule {$rule->id} failed: " . $e->getMessage());

                AutomationLog::create([
                    'automation_rule_id' => $rule->id,
                    'trigger_data' => $context,
                    'action_result' => [],
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }
        }
    }

    protected function checkConditions(AutomationRule $rule, array $context): bool
    {
        $conditions = $rule->conditions ?? [];

        if (empty($conditions)) {
            return true;
        }

        // Check stage condition for deal_stage_change
        if (isset($conditions['stage']) && isset($context['stage'])) {
            if ($conditions['stage'] !== $context['stage']) {
                return false;
            }
        }

        // Check property_type condition
        if (isset($conditions['property_type']) && isset($context['property_type'])) {
            if ($conditions['property_type'] !== $context['property_type']) {
                return false;
            }
        }

        return true;
    }

    protected function executeAction(AutomationRule $rule, array $context): array
    {
        $config = $rule->action_config ?? [];

        return match ($rule->action) {
            'create_task' => $this->createTask($config, $context),
            'notify_user' => $this->notifyUser($config, $context),
            'change_status' => $this->changeStatus($config, $context),
            'send_email' => $this->sendEmail($config, $context),
            default => ['action' => $rule->action, 'result' => 'Unknown action type'],
        };
    }

    protected function createTask(array $config, array $context): array
    {
        $task = Task::create([
            'title' => $config['task_title'] ?? 'Tarea automatica',
            'description' => $config['task_description'] ?? 'Creada por automatizacion',
            'priority' => $config['priority'] ?? 'medium',
            'status' => 'pending',
            'user_id' => $config['assign_to'] ?? $context['user_id'] ?? 1,
            'client_id' => $context['client_id'] ?? null,
            'property_id' => $context['property_id'] ?? null,
            'deal_id' => $context['deal_id'] ?? null,
            'due_date' => isset($config['due_days']) ? now()->addDays((int)$config['due_days'])->toDateString() : null,
        ]);

        return ['task_id' => $task->id, 'title' => $task->title];
    }

    protected function notifyUser(array $config, array $context): array
    {
        // For now, log the notification. Could be expanded to send email/push.
        Log::info('Automation notification', [
            'user_id' => $config['user_id'] ?? null,
            'message' => $config['message'] ?? 'Notificacion automatica',
            'context' => $context,
        ]);

        return ['notified' => true, 'message' => $config['message'] ?? 'Notificado'];
    }

    protected function changeStatus(array $config, array $context): array
    {
        $model = $context['model'] ?? null;
        $newStatus = $config['new_status'] ?? null;

        if ($model && $newStatus && method_exists($model, 'update')) {
            $model->update(['status' => $newStatus]);
            return ['status_changed' => $newStatus];
        }

        return ['status_changed' => false, 'reason' => 'No model or status provided'];
    }

    protected function sendEmail(array $config, array $context): array
    {
        // Placeholder — would use PHPMailer service
        Log::info('Automation email trigger', [
            'template_id' => $config['template_id'] ?? null,
            'to' => $config['to'] ?? 'client',
            'context' => $context,
        ]);

        return ['email_queued' => true, 'template' => $config['template_id'] ?? 'none'];
    }
}
