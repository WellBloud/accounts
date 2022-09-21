<?php

namespace App\Services\Stripe\WebhookProcessors;

use Illuminate\Support\Facades\Log;
use Stripe\Event;

class NullProcessor implements WebhookProcessor
{

    public function supports(string $type): bool
    {
        return true;
    }

    public function process(Event $event): void
    {
        Log::warning('Stripe event `' . $event->type . '` is not implemented, ignoring processing');
    }
}
