<?php

namespace App\Exceptions;

use App\Http\Responses\Failure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Lumen\Http\ResponseFactory;

abstract class BaseClientException extends BaseException
{
    /**
     * @var int
     */
    protected $code = Response::HTTP_BAD_REQUEST;

    public function render(Request $request): Response|bool|JsonResponse|ResponseFactory
    {
        // @codeCoverageIgnoreStart
        if (app()->environment('docker')) {
            return false;
        }
        // @codeCoverageIgnoreEnd

        $data = (new Failure($this->code))->jsonSerialize();

        return response()->json($data, $this->getCode());
    }
}
