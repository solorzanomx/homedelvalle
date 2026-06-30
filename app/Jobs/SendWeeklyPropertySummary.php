<?php

namespace App\Jobs;

use App\Models\Client;
use App\Models\Interaction;
use App\Models\PortalNotificationPreference;
use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWeeklyPropertySummary implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $lastWeekStart = Carbon::now()->subWeek()->startOfWeek();
        $lastWeekEnd   = Carbon::now()->subWeek()->endOfWeek();

        // Get all portal users who want weekly summary
        $prefs = PortalNotificationPreference::where('summary_frequency', 'weekly')
            ->with('user')
            ->get();

        foreach ($prefs as $pref) {
            $user = $pref->user;
            if (!$user) {
                continue;
            }

            // Resolve the client linked to this portal user
            $client = Client::where('user_id', $user->id)->first();
            if (!$client) {
                continue;
            }

            // Get their properties
            $properties = Property::where('client_id', $client->id)->get();
            if ($properties->isEmpty()) {
                continue;
            }

            // Get last week's visits
            $visits = Interaction::whereIn('property_id', $properties->pluck('id'))
                ->where('type', 'visit')
                ->whereBetween('scheduled_at', [$lastWeekStart, $lastWeekEnd])
                ->get();

            if ($visits->isEmpty()) {
                continue; // Don't send if no activity
            }

            try {
                Mail::to($user->email)->send(
                    new \App\Mail\Portal\WeeklyPropertySummaryMail(
                        $user,
                        $properties->first(),
                        $visits,
                        $lastWeekStart
                    )
                );
            } catch (\Exception $e) {
                Log::error("Weekly summary failed for user {$user->id}: {$e->getMessage()}");
            }
        }
    }
}
