<?php

namespace App\Listeners;

use App\Events\StripeWebhookReceived;
use App\Exceptions\UnknownStripeEventException;
use App\Services\Stripe\WebhookProcessors\WebhookProcessor;
use Illuminate\Support\Collection;

class ProcessStripeEvent
{
    public function __construct(private readonly Collection $processors)
    {
    }

    public function handle(StripeWebhookReceived $event): void
    {
        $processor = $this->findProcessor($event->stripeEvent->type);
        $processor->process($event->stripeEvent);
    }

    private function findProcessor(string $eventType): WebhookProcessor
    {
        $processor = $this->processors->first(fn (WebhookProcessor $processor) => $processor->supports($eventType));

        if (!$processor) {
            throw new UnknownStripeEventException('Event `' . $eventType . '` is not supported');
        }

        return $processor;
    }
}
