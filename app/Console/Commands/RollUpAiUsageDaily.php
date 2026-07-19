<?php

namespace App\Console\Commands;

use App\Models\AiUsageLog;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * Cierre diario del gasto de IA: suma ai_usage_logs del día y lo vuelca
 * como un solo gasto en Finanzas (categoría 'ai_tools'), convertido a MXN.
 * Idempotente por fecha vía `reference` — reejecutar el mismo día actualiza
 * en vez de duplicar.
 */
class RollUpAiUsageDaily extends Command
{
    protected $signature = 'ai:rollup-usage {date? : Fecha a cerrar (Y-m-d), default ayer}';
    protected $description = 'Suma el gasto de IA del día y lo registra como transacción en Finanzas';

    public function handle(): int
    {
        $date = $this->argument('date') ? now()->parse($this->argument('date')) : now()->subDay();
        $day  = $date->toDateString();

        $logs = AiUsageLog::whereDate('created_at', $day)->get();

        if ($logs->isEmpty()) {
            $this->info("Sin uso de IA registrado el {$day}.");
            return self::SUCCESS;
        }

        $totalUsd = (float) $logs->sum('cost_usd');
        $rate     = (float) config('ai_pricing.usd_mxn_rate', 18.5);
        $totalMxn = round($totalUsd * $rate, 2);

        $breakdown = $logs->groupBy('service')
            ->map(fn ($g) => round((float) $g->sum('cost_usd'), 4))
            ->sortDesc();

        $description = "Gasto de IA {$day} — " . $breakdown->map(fn ($usd, $service) => "{$service}: \${$usd}")->implode(', ');

        $reference = "ai-usage:{$day}";

        $existing = Transaction::where('reference', $reference)->first();

        $attributes = [
            'type'        => 'expense',
            'category'    => 'ai_tools',
            'description' => $description,
            'amount'      => $totalMxn,
            'currency'    => 'MXN',
            'date'        => $day,
            'reference'   => $reference,
            'notes'       => "USD: \${$totalUsd} · tipo de cambio {$rate} · " . $logs->count() . ' llamadas',
            'user_id'     => User::where('role', 'admin')->value('id') ?? 1,
        ];

        if ($existing) {
            $existing->update($attributes);
            $this->info("Actualizado: {$day} — \${$totalMxn} MXN (\${$totalUsd} USD, {$logs->count()} llamadas)");
        } else {
            Transaction::create($attributes);
            $this->info("Registrado: {$day} — \${$totalMxn} MXN (\${$totalUsd} USD, {$logs->count()} llamadas)");
        }

        return self::SUCCESS;
    }
}
