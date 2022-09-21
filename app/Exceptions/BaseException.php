<?php

namespace App\Exceptions;

use App\Http\Responses\Failure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use JsonSerializable;
use Laravel\Lumen\Http\ResponseFactory;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseException extends RuntimeException implements JsonSerializable
{
    /**
     * We agreed that $code will represent HTTP status code
     * @var int
     */
    protected $code = Response::HTTP_INTERNAL_SERVER_ERROR;

    public function render(Request $request): HttpResponse|bool|JsonResponse|ResponseFactory
    {
        // @codeCoverageIgnoreStart
        if (app()->environment('docker')) {
            return false;
        }
        // @codeCoverageIgnoreEnd

        $data = (new Failure($this->code))->jsonSerialize();

        return response()->json($data, $this->getCode());
    }

    public function jsonSerialize(): array
    {
        return [
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
        ];
    }
}
