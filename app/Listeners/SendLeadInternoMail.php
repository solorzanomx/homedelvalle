<?php

namespace App\Listeners;

use App\Events\FormSubmitted;
use App\Helpers\FormDataMapper;
use App\Mail\V4\Mailables\LeadInternoMail;
use Illuminate\Support\Facades\Mail;

class SendLeadInternoMail
{
    public function handle(FormSubmitted $event): void
    {
        $data = FormDataMapper::toLeadInternoData($event->submission);

        // LEADS_EMAIL en .env → fallback al from address configurado
        $teamInbox = config('mail.leads_email')
            ?? env('LEADS_EMAIL')
            ?? config('mail.from.address');

        Mail::to($teamInbox)->send(new LeadInternoMail($data));
    }
}
