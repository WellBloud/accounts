<?php

namespace App\Services\Stripe\WebhookProcessors;

use Stripe\Event;

interface WebhookProcessor
{
    public function supports(string $type): bool;

    public function process(Event $event): void;
}
