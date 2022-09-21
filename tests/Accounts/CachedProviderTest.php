<?php

namespace Tests\Accounts;

use App\Accounts\CachedProvider;
use App\Accounts\CacheKeyGenerator;
use App\Accounts\CacheTagGenerator;
use App\Accounts\Provider;
use App\Models\Account;
use App\Models\AccountUser;
use Illuminate\Support\Facades\Cache;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class CachedProviderTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseTransactions;

    private Provider $provider;

    private CachedProvider $cachedProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = new Provider();
        $this->cachedProvider = new CachedProvider($this->provider, 10);
        Cache::flush();
    }

    public function test_cache_is_not_called(): void
    {
        $userId = Uuid::getFactory()->uuid4()->toString();
        $account = Account::factory()->createOne();
        AccountUser::factory()->createOne(['user_id' => $userId, 'account_id' => $account->getKey()]);
        $data = $this->provider->getAccounts($userId);

        $cacheKey = CacheKeyGenerator::generate('', $userId);
        $cachedData = Cache::get($cacheKey);
        self::assertEquals(null, $cachedData);
        self::assertNotEquals($data, $cachedData);
        self::assertEquals(null, Cache::tags([CacheTagGenerator::createUserTag($userId)])->get($cacheKey));
    }

    public function test_cache_is_called(): void
    {
        $userId = Uuid::getFactory()->uuid4()->toString();
        /** @var Account $account */
        $account = Account::factory()->createOne();
        AccountUser::factory()->createOne(['user_id' => $userId, 'account_id' => $account->getKey()]);

        $cacheKey = CacheKeyGenerator::generate('', $userId);

        $accounts = $this->cachedProvider->getAccounts($userId);
        self::assertEquals(null, Cache::get($cacheKey));
        self::assertEquals($accounts, Cache::tags([CacheTagGenerator::createUserTag($userId), CacheTagGenerator::ALL_ACCOUNTS_TAG])->get($cacheKey));
    }

    public function test_role_cache_is_not_called()
    {
        $userId = Uuid::getFactory()->uuid4()->toString();
        /** @var Account $account */
        $account = Account::factory()->createOne();
        AccountUser::factory()->createOne(['user_id' => $userId, 'account_id' => $account->getKey()]);

        $data = $this->provider->getRole($account->id, $userId);

        $cacheKey = CacheKeyGenerator::generateRoleKey($account->id, $userId);
        $cachedData = Cache::get($cacheKey);
        self::assertEquals(null, $cachedData);
        self::assertNotEquals($data, $cachedData);
        self::assertEquals(null, Cache::tags([CacheTagGenerator::createUserTag($userId), CacheTagGenerator::ROLE_TAG])->get($cacheKey));
    }

    public function test_cache_roles(): void
    {
        $userId = Uuid::getFactory()->uuid4()->toString();
        /** @var Account $account */
        $account = Account::factory()->createOne();
        AccountUser::factory()->createOne(['user_id' => $userId, 'account_id' => $account->getKey()]);

        $accounts = $this->cachedProvider->getRole($account->id, $userId);

        $cacheKey = CacheKeyGenerator::generateRoleKey($account->id, $userId);
        self::assertEquals(null, Cache::get($cacheKey));
        self::assertEquals(
            $accounts,
            Cache::tags([
                CacheTagGenerator::createUserTag($userId), CacheTagGenerator::createAccountTag($account->id), CacheTagGenerator::ROLE_TAG,
            ])->get($cacheKey)
        );
    }
}
