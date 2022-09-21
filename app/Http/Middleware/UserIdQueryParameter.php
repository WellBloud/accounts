<?php

namespace App\Http\Middleware;

use App\Http\Responses\Errors\ErrorRegistry;
use Closure;
use Laravel\Lumen\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserIdQueryParameter
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (!$request->has('user_id')) {
            return response(ErrorRegistry::getMessage(ErrorRegistry::UNAUTHENTICATED), Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
