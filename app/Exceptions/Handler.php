<?php

namespace App\Exceptions;

use App\Http\Responses\Failure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ValidationException::class,
    ];

    public function render($request, Throwable $e)
    {
        if ($e instanceof ModelNotFoundException) {
            return (new Failure(Response::HTTP_NOT_FOUND))->toResponse($request);
        }

        return parent::render($request, $e);
    }
}
