<?php

namespace App\Http\Controllers;

use App\Accounts\AccountHandler as Handler;
use App\Accounts\AccountProvider as Provider;
use App\Http\Responses\Success;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class AccountsController extends Controller
{
    private Provider $provider;

    private Handler $handler;

    public function __construct(Provider $provider, Handler $handler)
    {
        $this->provider = $provider;
        $this->handler = $handler;
    }

    /**
     * @responseFile status=200 scenario="when authenticated" responses/accounts.get.json
     * @response status=401 scenario="when not authenticated" Unauthorized.
     */
    public function index(Request $request): Success
    {
        $validated = $this->validate($request, [
            'user_id' => 'required|string',
        ]);

        return new Success(['accounts' => $this->provider->getAccounts($validated['user_id'])]);
    }

    /**
     * @bodyParam account_name string required
     * @bodyParam user_id string required
     * @bodyParam role_id string required
     * @responseFile status=200 scenario="when authenticated" responses/accounts.post_put.json
     */
    public function store(Request $request): Success
    {
        $validated = $this->validate($request, [
            'account_name' => 'required|string',
            'user_id' => 'required|string',
            'role_id' => 'required|string',
        ]);

        return new Success(['account' => $this->handler->create($validated['account_name'], $validated['user_id'], $validated['role_id'])], httpCode: Response::HTTP_CREATED);
    }

    /**
     * @urlParam id string
     * @bodyParam account_name string required
     * @responseFile status=200 scenario="when authenticated" responses/accounts.post_put.json
     * @response status=401 scenario="when not authenticated" Unauthorized.
     * @response status=404 scenario="account not found"
     */
    public function update(Request $request, string $id): Success
    {
        $validated = $this->validate($request, [
            'account_name' => 'required|string',
        ]);

        return new Success(['account' => $this->handler->update($id, $validated['account_name'])]);
    }

    /**
     * @urlParam id string
     * @response status=204 scenario="when authenticated" Empty response.
     * @response status=401 scenario="when not authenticated" Unauthorized.
     * @response status=404 scenario="account not found"
     */
    public function delete(string $id): Success
    {
        $this->handler->delete($id);

        return new Success([], httpCode: Response::HTTP_NO_CONTENT);
    }

    /**
     * @urlParam id string
     * @bodyParam user_id string required
     * @response status=200 scenario="when authenticated"
     * @response status=401 scenario="when not authenticated" Unauthorized.
     * @response status=404 scenario="account not found"
     */
    public function role(Request $request, string $id): Success
    {
        $validated = $this->validate($request, [
            'user_id' => 'required|string',
        ]);

        return new Success([$this->provider->getRole($id, $validated['user_id'])]);
    }

    /**
     * @urlParam id string
     * @bodyParam onboarding_step string required
     * @response status=204 scenario="when authenticated" Empty response.
     * @response status=401 scenario="when not authenticated" Unauthorized.
     * @response status=404 scenario="account not found"
     */
    public function step(Request $request, string $id): Success
    {
        $validated = $this->validate($request, [
            'onboarding_step' => 'nullable|string',
        ]);
        $this->handler->updateOnboarding($id, $validated['onboarding_step']);

        return new Success([], httpCode: Response::HTTP_NO_CONTENT);
    }
}
