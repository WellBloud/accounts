<?php

namespace App\Services;

use Auth0\SDK\Auth0 as Auth0SDK;
use Auth0\SDK\Exception\ArgumentException;
use Auth0\SDK\Exception\NetworkException;
use Illuminate\Support\Facades\Log;

/**
 * @codeCoverageIgnore
 */
class Auth0
{
    private Auth0SDK $sdk;

    public function __construct(string $domain, string $clientId, string $clientSecret)
    {
        $this->sdk = new Auth0SDK(
            [
                'domain' => $domain,
                'clientId' => $clientId,
                'clientSecret' => $clientSecret,
            ]
        );
    }

    public function deleteUser(string $id): void
    {
        try {
            $this->sdk->management()->users()->delete($id);
        } catch (ArgumentException|NetworkException $e) {
            Log::error($e->getMessage());
        }
    }
}
