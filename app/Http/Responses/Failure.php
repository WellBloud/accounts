<?php

namespace App\Http\Responses;

use App\Http\Responses\Errors\ErrorRegistry;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use JsonSerializable;
use Symfony\Component\HttpFoundation\Response;

final class Failure implements JsonSerializable, Responsable
{
    public function __construct(private readonly int $httpCode = Response::HTTP_INTERNAL_SERVER_ERROR, private readonly ?string $message = null)
    {
    }

    public function jsonSerialize(): array
    {
        $code = $this->getCode();

        return [
            'error' => [
                'message' => $this->message ?: ErrorRegistry::getMessage($code),
                'code' => $code,
            ],
        ];
    }

    private function getCode(): int
    {
        return match ($this->httpCode) {
            Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN => ErrorRegistry::UNAUTHENTICATED,
            Response::HTTP_UNPROCESSABLE_ENTITY => ErrorRegistry::INVALID_QUERY,
            Response::HTTP_NOT_FOUND => ErrorRegistry::MODEL_NOT_FOUND,
            default => ErrorRegistry::GENERAL_ERROR,
        };
    }

    public function toResponse($request): JsonResponse|Response
    {
        return response()->json($this->jsonSerialize(), $this->httpCode);
    }
}
