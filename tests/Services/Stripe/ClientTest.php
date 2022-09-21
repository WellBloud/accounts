<?php

namespace Tests\Services\Stripe;

use App\Exceptions\StripeEventSigningException;
use App\Services\Stripe\Client;
use PHPUnit\Framework\MockObject\MockObject;
use Stripe\Event;
use Stripe\StripeClient;
use Tests\TestCase;

class ClientTest extends TestCase
{
    private Client $client;

    private StripeClient|MockObject $stripeClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stripeClient = $this->createMock(StripeClient::class);
        $this->client = new Client($this->stripeClient, 'whook_secret');
    }

    public function test_exception_is_thrown(): void
    {
        $_SERVER['HTTP_STRIPE_SIGNATURE'] = 'invalid_signature';
        $this->expectException(StripeEventSigningException::class);
        $this->client->getEvent('{}');
    }

    public function test_event_is_returned(): void
    {
        $this->client = new Client($this->stripeClient, '');
        $event = $this->client->getEvent('{}');
        $this->assertInstanceOf(Event::class, $event);
    }
}
