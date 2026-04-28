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

        Mail::to(config('mail.team_inbox', 'leads@homedelvalle.mx'))->send(
            new LeadInternoMail($data)
        );
    }
}
