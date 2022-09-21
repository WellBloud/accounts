<?php

namespace App\Events;

use Stripe\Event as StripeEvent;

class StripeWebhookReceived extends Event
{
    public function __construct(public StripeEvent $stripeEvent)
    {
    }
}
