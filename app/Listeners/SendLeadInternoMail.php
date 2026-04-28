<?php

namespace App\Listeners;

use App\Events\FormSubmitted;
use App\Helpers\FormDataMapper;
use App\Helpers\MailConfigurator;
use App\Mail\V4\Mailables\LeadInternoMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class SendLeadInternoMail
{
    public function handle(FormSubmitted $event): void
    {
        $cacheKey = 'lead_interno_sent_' . $event->submission->id;

        // Idempotency guard — skip if already sent for this submission
        if (Cache::has($cacheKey)) {
            return;
        }

        Cache::put($cacheKey, true, now()->addMinutes(10));

        MailConfigurator::applyGlobalSettings();

        $data      = FormDataMapper::toLeadInternoData($event->submission);
        $teamInbox = env('LEADS_EMAIL') ?: config('mail.from.address');

        Mail::to($teamInbox)->send(new LeadInternoMail($data));
    }
}
