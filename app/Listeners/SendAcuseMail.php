<?php

namespace App\Listeners;

use App\Events\FormSubmitted;
use App\Mail\V4\Data\AcuseData;
use App\Mail\V4\Mailables\AcuseMail;
use Illuminate\Support\Facades\Mail;

class SendAcuseMail
{

    public function handle(FormSubmitted $event): void
    {
        $submission = $event->submission;

        Mail::to($submission->email)->send(
            new AcuseMail(
                new AcuseData(folio: (string) $submission->id)
            )
        );
    }
}
