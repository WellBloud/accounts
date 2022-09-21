<?php

namespace App\Providers;

use App\Events\StripeWebhookReceived;
use App\Listeners\ProcessStripeEvent;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        StripeWebhookReceived::class => [
            ProcessStripeEvent::class,
        ],
    ];
}
