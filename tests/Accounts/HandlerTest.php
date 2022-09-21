<?php

namespace Tests\Accounts;

use App\Accounts\Handler;
use App\Models\Account;
use App\Models\AccountUser;
use App\Models\Information;
use App\Services\Auth0;
use App\Services\Connect\Client;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use PHPUnit\Framework\MockObject\MockObject;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;
use Throwable;

class HandlerTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseTransactions;

    private Handler $handler;

    private Client|MockObject $connectClient;

    private Auth0|MockObject $auth0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->auth0 = $this->createMock(Auth0::class);
        $this->connectClient = $this->createMock(Client::class);
        $this->handler = new Handler($this->auth0, $this->connectClient);
    }

    /**
     * @dataProvider accountNames
     */
    public function test_can_create_the_account(string $name, string $userId, string $roleId): void
    {
        $this->handler->create($name, $userId, $roleId);

        /** @var Account $account */
        $account = Account::query()->with(['users', 'information'])->where('name', '=', $name)->first();

        self::assertThat($account->name, self::equalTo($name));
        self::assertThat($account->users()->count(), self::equalTo(1));
        /** @var AccountUser $userAccount */
        $userAccount = $account->users()->first();
        self::assertThat($userAccount->user_id, self::equalTo($userId));

        self::assertEquals([Information::COMPANY_NAME => $name, Information::ONBOARDING_STEP => null], $account->information->value);
    }

    public function test_cannot_update_non_existing(): void
    {
        $accountId = Uuid::uuid4()->toString();

        try {
            $this->handler->update($accountId, 'my name');
        } catch (Throwable $exception) {
            self::assertThat($exception, self::isInstanceOf(ModelNotFoundException::class));
        }
    }

    public function test_can_update_account(): void
    {
        /** @var Account $account */
        $account = Account::factory()->createOne();

        $returnedAccount = $this->handler->update($account->id, 'my name');
        self::assertThat($returnedAccount->name, self::equalTo('my name'));

        /** @var Account $retrievedAccount */
        $retrievedAccount = Account::query()->findOrFail($account->id);

        self::assertThat($retrievedAccount->name, self::equalTo('my name'));
    }

    public function test_will_update_only_correct_one(): void
    {
        /** @var Account $accountToUpdate */
        /** @var Account $accountToVerify */
        [$accountToUpdate, $accountToVerify] = Account::factory()->count(2)->create()->random(2);

        $this->handler->update($accountToUpdate->id, 'name');

        /** @var Account $retrievedAccount */
        $retrievedAccount = Account::query()->findOrFail($accountToVerify->id);
        self::assertThat($retrievedAccount->name, self::equalTo($accountToVerify->name));
    }

    /**
     * @dataProvider accountNames
     */
    public function test_can_update_account_name_but_company_should_not_change(string $name, string $userId, string $roleId): void
    {
        $account = $this->handler->create($name, $userId, $roleId);

        $this->handler->update($account->id, 'New account name');

        /** @var Account $accountFetched */
        $accountFetched = Account::query()->with(['information'])->find($account->id);

        self::assertEquals('New account name', $accountFetched->name);
        self::assertEquals([Information::COMPANY_NAME => $name, Information::ONBOARDING_STEP => null], $accountFetched->information->value);
    }

    /**
     * @dataProvider accountNames
     */
    public function test_can_update_onboarding_step_but_company_should_remain(string $name, string $userId, string $roleId): void
    {
        $account = $this->handler->create($name, $userId, $roleId);

        $this->handler->updateOnboarding($account->id, 'started');

        /** @var Account $accountFetched */
        $accountFetched = Account::query()->with(['information'])->find($account->id);

        self::assertArrayHasKey(Information::ONBOARDING_STEP, $accountFetched->information->value);
        self::assertArrayHasKey(Information::COMPANY_NAME, $accountFetched->information->value);
        self::assertEquals('started', $accountFetched->information->value[Information::ONBOARDING_STEP]);
        self::assertEquals($name, $accountFetched->information->value[Information::COMPANY_NAME]);
    }

    public function test_delete_non_existing_throws_exception(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $accountId = Uuid::uuid4()->toString();
        $this->handler->delete($accountId);
    }

    public function test_delete_already_deleted_throws_exception(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $account = Account::factory()->createOne();
        $this->handler->delete($account->getKey()); // ok
        $this->handler->delete($account->getKey()); // should throw 404
    }

    public function test_can_delete_correct_one(): void
    {
        /** @var Account $accountToDelete */
        /** @var Account $accountToVerify */
        [$accountToDelete, $accountToVerify] = Account::factory()->count(2)->create()->random(2);

        $this->connectClient->expects($this->once())
            ->method('deleteDatasourcesForAccount')
            ->with($accountToDelete->id);
        $this->handler->delete($accountToDelete->id);

        /** @var Collection $retrievedAccounts */
        $retrievedAccounts = Account::all();
        self::assertThat($retrievedAccounts->count(), self::equalTo(1));
        /** @var Account $retrievedAccount */
        $retrievedAccount = $retrievedAccounts[0];
        self::assertThat($retrievedAccount->id, self::equalTo($accountToVerify->id));
    }

    public function test_delete_calls_all_necessary_steps(): void
    {
        $account = Account::factory()->createOne();
        $information = Information::factory()->makeOne();

        $account->information()->create($information->toArray());
        $user = AccountUser::factory()->create(['account_id' => $account->getKey()]);
        $account->load('information');

        $this->auth0->expects($this->once())
            ->method('deleteUser')
            ->with($user->getKey());

        $this->connectClient->expects($this->once())
            ->method('deleteDatasourcesForAccount')
            ->with($account->getKey());

        $this->handler->delete($account->getKey());

        // assertSoftDeleted() is not available in Lumen :/
        $this->seeInDatabase('accounts', ['id' => $account->getKey()])
            ->notSeeInDatabase('accounts', ['id' => $account->getKey(), 'deleted_at' => null]);
        $this->seeInDatabase('account_users', ['user_id' => $user->getKey()])
            ->notSeeInDatabase('account_users', ['user_id' => $user->getKey(), 'deleted_at' => null]);
        $this->seeInDatabase('information', ['informationable_id' => $account->information->informationable_id, 'informationable_type' => $account->information->informationable_type])
            ->notSeeInDatabase('information', ['informationable_id' => $account->information->informationable_id, 'informationable_type' => $account->information->informationable_type, 'deleted_at' => null]);
    }

    public function accountNames(): array
    {
        return [
            ['test_name', Uuid::uuid4()->toString(), 'admin'],
            ['david', Uuid::uuid4()->toString(), 'user'],
            ['Acme corp', Uuid::uuid4()->toString(), 'rol_dRF5aaaa1Vi5aMqw'],
        ];
    }
}
