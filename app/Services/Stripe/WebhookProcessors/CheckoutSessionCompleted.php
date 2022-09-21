<?php

namespace App\Services\Stripe\WebhookProcessors;

use App\Exceptions\InvalidStripeInvoiceStatusException;
use App\Services\Stripe\SubscriptionHandler;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Event;

class CheckoutSessionCompleted implements WebhookProcessor
{
    public function __construct(private readonly SubscriptionHandler $handler)
    {
    }

    public function supports(string $type): bool
    {
        return $type === Event::CHECKOUT_SESSION_COMPLETED;
    }

    public function process(Event $event): void
    {
        Log::info('Processing Stripe event `' . $event->type . '` with id ' . $event->id);

        /** @var Session $session */
        $session = $event->data->object;
        if (!$session->client_reference_id) {
            throw new InvalidStripeInvoiceStatusException(message: 'Missing account_id from Stripe event: ' . $event->id);
        }

        $this->handler->create($session->client_reference_id, $session);
    }
}
