<?php

namespace Tests\Exceptions;

use App\Exceptions\BaseClientException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tests\TestCase;

class BaseClientExceptionTest extends TestCase
{
    public function test_render_returns_false_on_docker(): void
    {
        $stub = $this->getMockForAbstractClass(BaseClientException::class);
        $request = $this->createMock(Request::class);

        if (app()->environment() === 'docker') {
            $this->assertFalse($stub->render($request));

            return;
        }

        $response = $stub->render($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }
}
