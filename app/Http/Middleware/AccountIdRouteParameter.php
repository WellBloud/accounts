<?php

namespace App\Http\Middleware;

use App\Http\Responses\Errors\ErrorRegistry;
use App\Http\Responses\Failure;
use App\Models\Account;
use Closure;
use Laravel\Lumen\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccountIdRouteParameter
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (!$request->accountId) {
            return new Failure(Response::HTTP_BAD_REQUEST, ErrorRegistry::getMessage(ErrorRegistry::INVALID_QUERY));
        }

        if (!Account::query()->find($request->accountId)) {
            return new Failure(Response::HTTP_NOT_FOUND, ErrorRegistry::getMessage(ErrorRegistry::MODEL_NOT_FOUND));
        }

        return $next($request);
    }
}
