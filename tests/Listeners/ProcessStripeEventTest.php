<?php

namespace Tests\Listeners;

use App\Events\StripeWebhookReceived;
use App\Exceptions\UnknownStripeEventException;
use App\Listeners\ProcessStripeEvent;
use App\Services\Stripe\WebhookProcessors\CheckoutSessionCompleted;
use Mockery;
use Stripe\Event as StripeEvent;
use Tests\TestCase;

class ProcessStripeEventTest extends TestCase
{
    private ProcessStripeEvent $processStripeEvent;


    protected function setUp(): void
    {
        parent::setUp();
        $this->processStripeEvent = app(ProcessStripeEvent::class);
    }

    public function test_it_will_throw_exception_for_unknown_event()
    {
        $stripeEvent = $this->createMock(StripeEvent::class);
        $stripeEvent->method('__get')->with('type')->willReturn('unknown');
        $this->expectException(UnknownStripeEventException::class);
        $processStripeEvent = new ProcessStripeEvent(collect([]));
        $processStripeEvent->handle(new StripeWebhookReceived($stripeEvent));
    }

    /**
     * @dataProvider processorProvider
     */
    public function test_it_finds_correct_processor(string $type, $processorClass): void
    {
        $stripeEvent = $this->createPartialMock(StripeEvent::class, ['__get']);
        $stripeEvent->method('__get')->willReturn($type);

        $event = new StripeWebhookReceived($stripeEvent);

        $processor = Mockery::mock($processorClass);
        $processor->shouldReceive('supports')->with($type)->andReturn(true);
        $processor->shouldReceive('process')->with($stripeEvent);
        $this->processStripeEvent->handle($event);
    }

    public function processorProvider(): array
    {
        return [
            [StripeEvent::CHECKOUT_SESSION_COMPLETED, CheckoutSessionCompleted::class],
//            [StripeEvent::INVOICE_PAID, InvoicePaid::class],
//            [StripeEvent::INVOICE_PAYMENT_FAILED, InvoicePaymentFailed::class],
        ];
    }
}
