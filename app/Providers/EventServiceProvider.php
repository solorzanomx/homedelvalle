<?php

namespace App\Providers;

use App\Events\FormSubmitted;
use App\Listeners\CreateOperationFromLead;
use App\Listeners\NotifyAdminsNewLead;
use App\Listeners\SendAcuseMail;
use App\Listeners\SendLeadInternoMail;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        FormSubmitted::class => [
            SendAcuseMail::class,
            SendLeadInternoMail::class,
            NotifyAdminsNewLead::class,
            CreateOperationFromLead::class,
        ],
    ];

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
