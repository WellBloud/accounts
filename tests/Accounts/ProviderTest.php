<?php

namespace Tests\Accounts;

use App\Accounts\Provider;
use App\Models\Account;
use App\Models\AccountUser;
use App\Models\Information;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class ProviderTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseTransactions;

    private Provider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = new Provider();
    }

    public function test_user_has_no_accounts(): void
    {
        $userId = Uuid::getFactory()->uuid4()->toString();

        //nothing returned by default
        $accounts = $this->provider->getAccounts($userId);
        self::assertThat($accounts, self::countOf(0));

        Account::factory()
            ->count(0)
            ->has(AccountUser::factory()->count(1)->state(['user_id' => $userId]), 'users')
            ->create();

        $accounts = $this->provider->getAccounts($userId);

        self::assertThat($accounts, self::countOf(0));
    }

    public function test_user_properly_assigned_accounts(): void
    {
        $accountNotOwned = Account::factory()->createOne();

        $account = Account::factory()->createOne();
        $accountUser = AccountUser::factory()->create(['account_id' => $account->getKey()]);

        $accounts = $this->provider->getAccounts($accountUser->user_id);

        self::assertContainsOnlyAccount($accounts, $accountUser->account);
    }

    public function test_two_user_have_same_accounts(): void
    {
        $firstUserId = Uuid::getFactory()->uuid4()->toString();
        $secondUserId = Uuid::getFactory()->uuid4()->toString();

        /** @var Collection<Account> $sharedAccounts */
        $sharedAccounts = Account::factory()
            ->count(5)
            ->create()
            ->random(3);

        $sharedAccounts->each(function (Account $account) use ($firstUserId, $secondUserId) {
            AccountUser::factory()->create(['user_id' => $firstUserId, 'account_id' => $account->getKey()]);
            AccountUser::factory()->create(['user_id' => $secondUserId, 'account_id' => $account->getKey()]);
            $account->information()->create(Information::factory()->makeOne()->toArray());
            $account->load(['information', 'subscriptions']);
        });

        $retrievedAccountsFirstUser = $this->provider->getAccounts($firstUserId);
        $retrievedAccountsSecondUser = $this->provider->getAccounts($secondUserId);

        self::assertThat($retrievedAccountsFirstUser, self::equalToCanonicalizing($sharedAccounts->toArray()));
        self::assertThat($retrievedAccountsSecondUser, self::equalToCanonicalizing($sharedAccounts->toArray()));
    }

    private static function assertContainsOnlyAccount(array $retrievedAccounts, Account $sharedAccount): void
    {
        self::assertThat($retrievedAccounts, self::countOf(1));
        self::assertThat($retrievedAccounts[0]['id'], self::equalTo($sharedAccount->getKey()));
    }

    public function test_no_role_for_non_existing_account(): void
    {
        /** @var Account $account */
        $account = Account::factory()->createOne();
        /** @var AccountUser $accountUser */
        $accountUser = AccountUser::factory(['account_id' => $account->id])->createOne();

        $nonExistingAccountId = Uuid::getFactory()->uuid4()->toString();

        try {
            $this->provider->getRole($nonExistingAccountId, $accountUser->user_id);
        } catch (\Throwable $exception) {
            self::assertThat($exception, self::isInstanceOf(ModelNotFoundException::class));
        }
    }

    public function test_no_role_for_non_existing_user(): void
    {
        /** @var Account $account */
        $account = Account::factory()->createOne();
        /** @var AccountUser $accountUser */
        $accountUser = AccountUser::factory(['account_id' => $account->id])->createOne();

        try {
            $this->provider->getRole($accountUser->account_id, 'non-existing');
        } catch (\Throwable $exception) {
            self::assertThat($exception, self::isInstanceOf(ModelNotFoundException::class));
        }
    }

    public function test_correct_role_returned(): void
    {
        $accounts = Account::factory()->count(3)->create()->random(2);
        $userId = Uuid::getFactory()->uuid4()->toString();
        $accountUsers = collect();
        $accounts->each(function (Account $account) use ($userId, $accountUsers) {
            $accountUsers->add(
                AccountUser::factory()->createOne(['user_id' => $userId, 'account_id' => $account->getKey()])
            );
        });

        /** @var AccountUser $accountUser */
        $accountUser = $accountUsers[0];
        $role = $this->provider->getRole($accountUser->account_id, $accountUser->user_id);

        self::assertThat($role, self::equalTo($accountUser->role_id));
    }
}
