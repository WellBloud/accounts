<?php

namespace Tests\Exceptions;

use App\Exceptions\Handler;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class HandlerTest extends TestCase
{
    private Handler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = new Handler();
    }

    public function test_render_uses_failure_response(): void
    {
        $request = $this->createMock(Request::class);
        $this->assertInstanceOf(JsonResponse::class, $this->handler->render($request, new ModelNotFoundException()));
    }

    public function test_render_uses_default_response(): void
    {
        $request = $this->createMock(Request::class);
        $this->assertInstanceOf(Response::class, $this->handler->render($request, new AuthorizationException()));
    }
}
