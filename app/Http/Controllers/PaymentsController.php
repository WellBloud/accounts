<?php

namespace App\Http\Controllers;

use App\Http\Responses\Failure;
use App\Http\Responses\Success;
use App\Services\Stripe\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Lumen\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class PaymentsController extends Controller
{
    public function __construct(private readonly Client $client)
    {
    }

    /**
     * @bodyParam price_id string required
     * @response status=201 scenario="when successful" Returns the Stripe url for redirection
     * @responseFile status=400 scenario="when missing account ID in the URL" responses/error.json
     * @responseFile status=404 scenario="when account ID not found in the DB" responses/error.json
     * @responseFile status=422 scenario="when validation fails" responses/error.json
     */
    public function checkout(Request $request): Success|Failure
    {
        $validator = Validator::make(
            $request->all(),
            [
                'price_id' => ['required', 'string', Rule::in($this->client->getPriceIds())],
            ],
            [
                'required' => ':attribute is required',
                'string' => ':attribute must be a string',
                'in' => ':attribute was not found',
            ]
        );

        if ($validator->fails()) {
            return new Failure(Response::HTTP_UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }

        $validated = $validator->validated();
        $session = $this->client->createCheckoutSession($request->accountId, $validated['price_id']);
        Log::debug($session->url);

        return new Success(['redirect_uri' => $session->url], httpCode: Response::HTTP_CREATED);
    }
}
