<?php

namespace Tests\Http\Responses;

use App\Http\Responses\Errors\ErrorRegistry;
use App\Http\Responses\Failure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class FailureTest extends TestCase
{
    /**
     * @dataProvider codeProvider
     */
    public function test_it_returns_proper_code(int $httpCode, int $expected): void
    {
        $response = (new Failure($httpCode))->jsonSerialize();
        $this->assertEquals($expected, $response['error']['code']);
    }

    /**
     * @dataProvider codeProvider
     */
    public function test_it_renders_properly(int $httpCode, int $expected): void
    {
        $request = $this->createMock(Request::class);
        $response = (new Failure($httpCode))->toResponse($request);
        $data = $response->getData();

        $this->assertEquals($httpCode, $response->getStatusCode());
        $this->assertEquals(ErrorRegistry::getMessage($expected), $data->error->message);
    }

    public function codeProvider(): array
    {
        return [
            [Response::HTTP_UNAUTHORIZED, ErrorRegistry::UNAUTHENTICATED],
            [Response::HTTP_FORBIDDEN, ErrorRegistry::UNAUTHENTICATED],
            [Response::HTTP_UNPROCESSABLE_ENTITY, ErrorRegistry::INVALID_QUERY],
            [Response::HTTP_NOT_FOUND, ErrorRegistry::MODEL_NOT_FOUND],
            [Response::HTTP_INTERNAL_SERVER_ERROR, ErrorRegistry::GENERAL_ERROR],
        ];
    }
}
