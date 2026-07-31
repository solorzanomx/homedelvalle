<?php

namespace App\Providers;

use App\Events\FormSubmitted;
use App\Listeners\CreateOperationFromLead;
use App\Listeners\HandleResendWebhook;
use App\Listeners\NotifyAdminsNewLead;
use App\Listeners\RecordSentMessage;
use App\Listeners\SendAcuseMail;
use App\Listeners\SendLeadInternoMail;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Mail\Events\MessageSent;
use Resend\Laravel\Events\EmailBounced;
use Resend\Laravel\Events\EmailClicked;
use Resend\Laravel\Events\EmailComplained;
use Resend\Laravel\Events\EmailDelivered;
use Resend\Laravel\Events\EmailFailed;
use Resend\Laravel\Events\EmailOpened;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        FormSubmitted::class => [
            SendAcuseMail::class,
            SendLeadInternoMail::class,
            NotifyAdminsNewLead::class,
            CreateOperationFromLead::class,
        ],
        MessageSent::class => [
            RecordSentMessage::class,
        ],
        EmailOpened::class => [
            [HandleResendWebhook::class, 'handleOpened'],
        ],
        EmailClicked::class => [
            [HandleResendWebhook::class, 'handleClicked'],
        ],
        EmailDelivered::class => [
            [HandleResendWebhook::class, 'handleDelivered'],
        ],
        EmailBounced::class => [
            [HandleResendWebhook::class, 'handleBounced'],
        ],
        EmailComplained::class => [
            [HandleResendWebhook::class, 'handleComplained'],
        ],
        EmailFailed::class => [
            [HandleResendWebhook::class, 'handleFailed'],
        ],
    ];

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
