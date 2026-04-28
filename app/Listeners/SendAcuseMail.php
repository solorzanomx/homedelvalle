<?php

namespace App\Listeners;

use App\Events\FormSubmitted;
use App\Helpers\MailConfigurator;
use App\Mail\V4\Data\AcuseData;
use App\Mail\V4\Mailables\AcuseMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAcuseMail
{
    public function handle(FormSubmitted $event): void
    {
        $cacheKey = 'acuse_sent_' . $event->submission->id;

        // Idempotency guard — skip if already sent for this submission
        if (Cache::has($cacheKey)) {
            Log::info('SendAcuseMail: skipped duplicate for submission ' . $event->submission->id);
            return;
        }

        Cache::put($cacheKey, true, now()->addMinutes(10));

        Log::info('SendAcuseMail::handle fired', [
            'submission_id' => $event->submission->id,
            'email'         => $event->submission->email,
        ]);

        MailConfigurator::applyGlobalSettings();

        $submission = $event->submission;

        Mail::to($submission->email)->send(
            new AcuseMail(new AcuseData(
                folio:     (string) $submission->id,
                email:     $submission->email,
                form_type: $submission->form_type,
                nombre:    $submission->full_name,
            ))
        );
    }
}
