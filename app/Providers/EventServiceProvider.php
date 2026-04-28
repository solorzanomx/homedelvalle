<?php

namespace App\Providers;

use App\Events\FormSubmitted;
use App\Listeners\SendAcuseMail;
use App\Listeners\SendLeadInternoMail;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        FormSubmitted::class => [
            SendAcuseMail::class,
            SendLeadInternoMail::class,
        ],
    ];

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
