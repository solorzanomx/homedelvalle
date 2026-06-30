<?php

namespace App\Console\Commands;

use App\Mail\V4\Data\RecordatorioCitaData;
use App\Mail\V4\Mailables\RecordatorioCitaMail;
use App\Models\Interaction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendVisitReminders extends Command
{
    protected $signature = 'visits:send-reminders';
    protected $description = 'Send reminder emails for visits scheduled today';

    public function handle(): int
    {
        $todayStart = Carbon::today();
        $todayEnd   = Carbon::today()->endOfDay();

        $visits = Interaction::with(['client', 'property', 'user'])
            ->where('type', 'visit')
            ->whereBetween('scheduled_at', [$todayStart, $todayEnd])
            ->whereNull('reminder_sent_at')
            ->whereNotNull('visit_token')
            ->get();

        $sent = 0;

        foreach ($visits as $interaction) {
            $client = $interaction->client;
            if (!$client?->email) {
                continue;
            }

            $scheduled = $interaction->scheduled_at;
            $asesor    = $interaction->user?->name ?? 'Tu asesor';

            try {
                Mail::to($client->email)->send(
                    new RecordatorioCitaMail(
                        new RecordatorioCitaData(
                            email: $client->email,
                            nombre: $client->name,
                            dia_semana: $scheduled->locale('es')->dayName,
                            dia: (string) $scheduled->day,
                            mes: $scheduled->locale('es')->monthName,
                            anio: (string) $scheduled->year,
                            hora: $scheduled->format('g:i A'),
                            duracion: '30',
                            direccion: $interaction->property?->address ?? 'A coordinar',
                            colonia: $interaction->property?->colonia ?? '',
                            asesor: $asesor,
                            visit_token: $interaction->visit_token,
                            maps_url: '',
                            is_today: 'hoy',
                        )
                    )
                );

                $interaction->update(['reminder_sent_at' => now()]);
                $sent++;
                $this->info("Reminder sent to {$client->email}");
            } catch (\Exception $e) {
                $this->error("Failed for {$client->email}: {$e->getMessage()}");
            }
        }

        $this->info("Done. {$sent} reminders sent.");
        return Command::SUCCESS;
    }
}
