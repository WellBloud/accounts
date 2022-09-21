<?php

namespace App\Http\Controllers;

use App\Events\StripeWebhookReceived;
use App\Exceptions\StripeEventParsingException;
use App\Exceptions\StripeEventSigningException;
use App\Http\Responses\Failure;
use App\Http\Responses\Success;
use App\Services\Stripe\Client;
use Laravel\Lumen\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhooksController extends Controller
{
    public function __construct(private readonly Client $client)
    {
    }

    /**
     * @response status=204 scenario="when successful"
     * @response status=400 scenario="when error during parsing/signing the Stripe payload"
     */
    public function process(): Success|Failure
    {
        $payload = @file_get_contents('php://input');
        if ($payload === false) {
            return new Failure(Response::HTTP_INTERNAL_SERVER_ERROR, 'Error while reading data');
        }

        try {
            $stripeEvent = $this->client->getEvent($payload);
        } catch (StripeEventParsingException|StripeEventSigningException $e) {
            return new Failure(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }

        event(new StripeWebhookReceived($stripeEvent));

        return new Success([], httpCode: Response::HTTP_NO_CONTENT);
    }
}
