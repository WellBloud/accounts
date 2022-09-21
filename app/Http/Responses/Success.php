<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use JsonSerializable;
use Symfony\Component\HttpFoundation\Response;

final class Success implements JsonSerializable, Responsable
{
    private array $data;

    private array $meta;

    private int $httpCode;

    public function __construct(array $data, array $meta = [], int $httpCode = Response::HTTP_OK)
    {
        $this->data = $data;
        $this->meta = $meta;
        $this->httpCode = $httpCode;
    }

    public function jsonSerialize(): array
    {
        return [
            'data' => $this->data,
            'meta' => $this->meta,
        ];
    }

    public function toResponse($request): JsonResponse|Response
    {
        if ($this->httpCode === Response::HTTP_NO_CONTENT) {
            return response('', Response::HTTP_NO_CONTENT);
        }

        return response()->json($this->jsonSerialize(), $this->httpCode);
    }
}
