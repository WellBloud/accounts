<?php

namespace App\Services\Stripe;

use App\Exceptions\StripeEventParsingException;
use App\Exceptions\StripeEventSigningException;
use Stripe\Checkout\Session;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Price;
use Stripe\StripeClient;
use Stripe\Webhook;
use UnexpectedValueException;

class Client
{
    private const SUBSCRIPTION_MODE = 'subscription';

    public function __construct(private readonly StripeClient $stripe, private readonly string $webhookSecret)
    {
    }

    /**
     * @codeCoverageIgnore
     */
    public function createCheckoutSession(string $accountId, string $priceId): Session
    {
        return $this->stripe->checkout->sessions->create(
            [
                // we can add `?session_id={CHECKOUT_SESSION_ID}` for customizing the success page
                'success_url' => config('app.juiceUrl') . '/?premium_payment=successful',
                'cancel_url' => config('app.juiceUrl') . '/?premium_payment=canceled',
                'mode' => self::SUBSCRIPTION_MODE,
                'client_reference_id' => $accountId,
                'line_items' => [
                    [
                        'price' => $priceId,
                        // For metered billing, do not pass quantity
                        'quantity' => 1,
                    ],
                ],
            ]
        );
    }

    /**
     * @codeCoverageIgnore
     */
    public function getPriceIds(): array
    {
        $prices = $this->stripe->prices->all(['active' => true, 'type' => 'recurring']);

        return array_reduce(
            $prices->data,
            static function (array $carry, Price $item) {
                $carry[] = $item->id;

                return $carry;
            },
            []
        );
    }

    /**
     * @throws StripeEventParsingException
     * @throws StripeEventSigningException
     */
    public function getEvent(string $payload): Event
    {
        try {
            $event = Event::constructFrom(json_decode($payload, true));
        } catch (UnexpectedValueException $e) {
            throw new StripeEventParsingException($e->getMessage());
        }

        if ($this->webhookSecret) {
            try {
                return Webhook::constructEvent(
                    $payload,
                    $_SERVER['HTTP_STRIPE_SIGNATURE'],
                    $this->webhookSecret
                );
            } catch (SignatureVerificationException $e) {
                throw new StripeEventSigningException($e->getMessage());
            }
        }

        return $event;
    }
}
