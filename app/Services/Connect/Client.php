<?php

namespace App\Services\Connect;

use Illuminate\Support\Facades\Http;
use JsonException;

class Client
{
    private const SIGNATURE_HEADER = 'x-accounts-signature-256';

    private readonly string $baseUrl;

    private readonly string $secretKey;

    public function __construct(string $baseUrl, string $secretKey)
    {
        if (str_ends_with($baseUrl, '/')) {
            $baseUrl = substr($baseUrl, 0, -1);
        }

        $this->baseUrl = $baseUrl;
        $this->secretKey = $secretKey;
    }

    /**
     * @throws JsonException
     * @codeCoverageIgnore
     */
    public function deleteDatasourcesForAccount(string $id): void
    {
        Http::withHeaders([self::SIGNATURE_HEADER => $this->getSignature($id)])
            ->delete($this->baseUrl . '/accounts/' . $id);
    }

    /**
     * @throws JsonException
     */
    private function getSignature(string $id): string
    {
        return hash_hmac('sha256', json_encode(['account_id' => $id], JSON_THROW_ON_ERROR), $this->secretKey);
    }
}
