<?php

namespace Tests\Api\Payments;

use App\Models\Account;
use App\Services\Stripe\Client;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Ramsey\Uuid\Uuid;
use Stripe\Checkout\Session;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class PaymentsTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseTransactions;

    private Client|MockObject $client;

    private Session|MockObject $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->session = $this->createMock(Session::class);
        $this->session->method('__get')->with('url')->willReturn('http://testing.localhost');
    }

    public function test_checkout_session_is_created(): void
    {
        $this->app->instance(
            Client::class,
            Mockery::mock(Client::class, function (MockInterface $mock) {
                $mock->shouldReceive('getPriceIds')->once()->andReturn(['price_random_id']);
                $mock->shouldReceive('createCheckoutSession')->once()->andReturn($this->session);
            })
        );

        $account = Account::factory()->createOne();

        $request = $this->post('accounts/' . $account->getKey() . '/payment/checkout', [
            'price_id' => 'price_random_id',
        ]);

        $request->assertResponseStatus(Response::HTTP_CREATED);
        $response = $request->response->json();
        self::assertEquals(['redirect_uri' => 'http://testing.localhost'], $response['data']);
    }

    public function test_price_validation(): void
    {
        $this->app->instance(
            Client::class,
            Mockery::mock(Client::class, static function (MockInterface $mock) {
                $mock->shouldReceive('getPriceIds')->once()->andReturn(['price_random_id']);
            })
        );

        $account = Account::factory()->createOne();

        $response = $this->post('accounts/' . $account->getKey() . '/payment/checkout', [
            'price_id' => 'not_mocked_price_id',
        ]);

        $response->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_account_validation(): void
    {
        $response = $this->post(Uuid::uuid4() . '/payment/checkout', [
            'price_id' => 'price_random_id',
        ]);

        $response->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }
}
