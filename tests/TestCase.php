<?php

namespace Tests;

use App\Services\Auth0;
use Illuminate\Support\Facades\Http;
use Laravel\Lumen\Application;
use Laravel\Lumen\Testing\TestCase as BaseTestCase;
use Mockery;

abstract class TestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        return require __DIR__ . '/../bootstrap/app.php';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance(Auth0::class, Mockery::mock(Auth0::class));

        Http::fake(
            [
                config('connect.baseUrl') . '/data_sources/*' => Http::response('Mocked call: Datasource was deleted', status: 200),
                '*' => Http::response('Mocked call: faked response', 200),
            ]
        );
    }
}
