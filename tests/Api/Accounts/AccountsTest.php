<?php

namespace Tests\Api\Accounts;

use App\Models\Account;
use App\Models\AccountUser;
use App\Services\Auth0;
use App\Services\Connect\Client;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Mockery;
use Mockery\MockInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AccountsTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseTransactions;

    public function test_create_account_cannot_be_accessed(): void
    {
        $response = $this->get('accounts/');
        $response->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_create_account_is_ok(): void
    {
        $response = $this->post('accounts/?user_id=' . Uuid::uuid4()->toString(), [
            'account_name' => 'test name',
            'role_id' => 'admin',
        ]);
        $response->assertResponseStatus(Response::HTTP_CREATED);
    }

    public function test_get_accounts_cannot_be_accessed(): void
    {
        $response = $this->get('accounts/');
        $response->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_get_accounts_is_ok(): void
    {
        $userId = Uuid::uuid4();
        $account = Account::factory()->createOne();
        AccountUser::factory(['user_id' => $userId, 'account_id' => $account->getKey()])->createOne();

        $response = $this->get('accounts/?user_id=' . $userId);
        $response->assertResponseOk();
    }

    public function test_update_is_ok(): void
    {
        /** @var Account $account */
        $account = Account::factory()->createOne();

        $response = $this->put('accounts/' . $account->id, [
            'account_name' => 'my name',
        ]);

        $response->assertResponseOk();
    }

    public function test_update_onboarding_step_is_ok(): void
    {
        $userId = Uuid::uuid4();
        /** @var Account $account */
        $account = Account::factory()->createOne();
        AccountUser::factory(['user_id' => $userId, 'account_id' => $account->getKey()])->createOne();

        $response = $this->put('accounts/' . $account->id . '/step?user_id=' . $userId, [
            'onboarding_step' => 'started',
        ]);

        $response->assertResponseStatus(204);
    }

    public function test_delete_is_ok(): void
    {
        $this->withoutMiddleware();

        $userId = 'veryrandomuserid';
        /** @var Account $account */
        $account = Account::factory()->createOne();
        AccountUser::factory(['user_id' => $userId, 'account_id' => $account->getKey()])->createOne();

        $this->app->instance(
            Auth0::class,
            Mockery::mock(Auth0::class, static function (MockInterface $mock) use ($userId) {
                $mock->shouldReceive('deleteUser')->with($userId)->once()->andReturn();
            })
        );

        $this->app->instance(
            Client::class,
            Mockery::mock(Client::class, static function (MockInterface $mock) use ($account) {
                $mock->shouldReceive('deleteDatasourcesForAccount')->with($account->id)->once()->andReturn([]);
            })
        );

        $response = $this->delete('accounts/' . $account->id);

        $response->assertResponseStatus(Response::HTTP_NO_CONTENT);
        self::assertSame('', $response->response->getContent());
    }

    public function test_get_role_cannot_be_accessed(): void
    {
        $response = $this->get('accounts/non-existing-id/role');
        $response->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_get_role_is_ok(): void
    {
        $userId = Uuid::uuid4();
        /** @var Account $account */
        $account = Account::factory()->createOne();
        AccountUser::factory(['user_id' => $userId, 'account_id' => $account->getKey()])->createOne();

        $response = $this->get('accounts/' . $account->id . '/role?user_id=' . $userId);
        $response->assertResponseOk();
    }
}
