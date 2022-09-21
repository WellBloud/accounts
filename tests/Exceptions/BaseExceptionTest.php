<?php

namespace Tests\Exceptions;

use App\Exceptions\BaseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class BaseExceptionTest extends TestCase
{
    public function test_render_returns_false_on_docker(): void
    {
        $stub = $this->getMockForAbstractClass(BaseException::class);
        $request = $this->createMock(Request::class);

        if (app()->environment() === 'docker') {
            $this->assertFalse($stub->render($request));

            return;
        }

        $response = $stub->render($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function test_json_serialize_holds_proper_structure(): void
    {
        $stub = $this->getMockForAbstractClass(BaseException::class);

        $expected = [
            'message' => '',
            'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
        ];
        $this->assertEquals($expected, $stub->jsonSerialize());
    }
}
