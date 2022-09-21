<?php

namespace App\Services\Stripe\WebhookProcessors;

use App\Services\Stripe\SubscriptionHandler;
use Illuminate\Support\Facades\Log;
use Stripe\Event;
use Stripe\Invoice;

class InvoicePaymentFailed implements WebhookProcessor
{
    public function __construct(private readonly SubscriptionHandler $handler)
    {
    }

    public function supports(string $type): bool
    {
        return $type === Event::INVOICE_PAYMENT_FAILED;
    }

    public function process(Event $event): void
    {
        Log::info('Processing Stripe event `' . $event->type . '` with id ' . $event->id);

        /** @var Invoice $invoice */
        $invoice = $event->data->object;
        $this->handler->invoicePaymentFailed($invoice->customer);
    }
}
