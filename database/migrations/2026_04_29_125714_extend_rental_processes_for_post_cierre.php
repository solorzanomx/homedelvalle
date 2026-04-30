<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 1-B: Extiende rental_processes para gestión post-cierre.
 * Agrega campos de cobranza mensual y move-out.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_processes', function (Blueprint $table) {
            // Cobranza mensual
            if (! Schema::hasColumn('rental_processes', 'payment_day')) {
                $table->unsignedTinyInteger('payment_day')
                    ->nullable()
                    ->after('lease_duration_months')
                    ->comment('Día del mes en que vence el pago (1-28)');
            }
            if (! Schema::hasColumn('rental_processes', 'payment_confirmed_at')) {
                $table->timestamp('payment_confirmed_at')
                    ->nullable()
                    ->after('payment_day')
                    ->comment('Última confirmación de pago recibida');
            }
            // Move-out
            if (! Schema::hasColumn('rental_processes', 'move_out_scheduled_at')) {
                $table->date('move_out_scheduled_at')
                    ->nullable()
                    ->after('payment_confirmed_at')
                    ->comment('Fecha programada de salida del inquilino');
            }
            if (! Schema::hasColumn('rental_processes', 'actual_move_out_at')) {
                $table->date('actual_move_out_at')
                    ->nullable()
                    ->after('move_out_scheduled_at')
                    ->comment('Fecha real de salida del inquilino');
            }
            // Portal
            if (! Schema::hasColumn('rental_processes', 'portal_visible')) {
                $table->boolean('portal_visible')
                    ->default(true)
                    ->after('actual_move_out_at')
                    ->comment('¿Visible en el portal del cliente?');
            }
            // Renovación
            if (! Schema::hasColumn('rental_processes', 'renewal_offered_at')) {
                $table->timestamp('renewal_offered_at')
                    ->nullable()
                    ->after('portal_visible');
            }
            if (! Schema::hasColumn('rental_processes', 'renewal_signed_at')) {
                $table->timestamp('renewal_signed_at')
                    ->nullable()
                    ->after('renewal_offered_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rental_processes', function (Blueprint $table) {
            $cols = [
                'payment_day', 'payment_confirmed_at', 'move_out_scheduled_at',
                'actual_move_out_at', 'portal_visible', 'renewal_offered_at', 'renewal_signed_at',
            ];
            $existing = array_filter($cols, fn($c) => Schema::hasColumn('rental_processes', $c));
            if ($existing) $table->dropColumn(array_values($existing));
        });
    }
};
