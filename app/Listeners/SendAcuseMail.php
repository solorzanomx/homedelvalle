<?php

namespace App\Listeners;

use App\Events\FormSubmitted;
use App\Mail\V4\Data\AcuseData;
use App\Mail\V4\Mailables\AcuseMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendAcuseMail implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

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
