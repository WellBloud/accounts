<?php

namespace Tests\Accounts;

use App\Accounts\AccountHandler;
use App\Accounts\CachedHandler;
use App\Accounts\CacheKeyGenerator;
use App\Accounts\CacheTagGenerator;
use Illuminate\Support\Facades\Cache;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use PHPUnit\Framework\MockObject\MockObject;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class CachedHandlerTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseTransactions;

    private string $accountId;

    private string $userId;

    private MockObject|AccountHandler $mock;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->userId = Uuid::getFactory()->uuid4()->toString();
        $this->accountId = Uuid::getFactory()->uuid4()->toString();
        $this->mock = $this->createMock(AccountHandler::class);
    }

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }

    public function test_cache_is_flushed_on_add_user(): void
    {
        $accountTags = [CacheTagGenerator::createUserTag($this->userId), CacheTagGenerator::ALL_ACCOUNTS_TAG];
        $accountKey = CacheKeyGenerator::generate('', $this->userId);
        $roleTags = [CacheTagGenerator::createUserTag($this->userId), CacheTagGenerator::createAccountTag($this->accountId), CacheTagGenerator::ROLE_TAG];
        $roleKey = CacheKeyGenerator::generateRoleKey($this->accountId, $this->userId);

        Cache::tags($accountTags)->remember($accountKey, 20, fn () => 'cached_accounts');
        Cache::tags($roleTags)->remember($roleKey, 20, fn () => 'cached_roles');

        $this->mock->method('addUser')->with($this->accountId, $this->userId, 'admin');
        $cachedHandler = new CachedHandler($this->mock);

        $this->assertEquals('cached_accounts', Cache::tags($accountTags)->get($accountKey));
        $this->assertEquals('cached_roles', Cache::tags($roleTags)->get($roleKey));
        $cachedHandler->addUser($this->accountId, $this->userId, 'admin');

        $this->assertNull(Cache::tags($accountTags)->get($accountKey));
        $this->assertNull(Cache::tags($roleTags)->get($roleKey));
    }

    /**
     * @dataProvider tagsProvider
     */
    public function test_no_other_cache_is_flushed_on_add_user(array $otherTags): void
    {
        $roleKey = CacheKeyGenerator::generateRoleKey($this->accountId, $this->userId);
        Cache::tags($otherTags)->remember($roleKey, 20, fn () => 'cached');

        $this->mock->method('addUser')->with($this->accountId, $this->userId, 'admin');
        $cachedHandler = new CachedHandler($this->mock);

        $this->assertEquals('cached', Cache::tags($otherTags)->get($roleKey));
        $cachedHandler->addUser($this->accountId, $this->userId, 'admin');
        $this->assertEquals('cached', Cache::tags($otherTags)->get($roleKey));
    }

    public function test_cache_is_flushed_on_create(): void
    {
        $accountTags = [CacheTagGenerator::createUserTag($this->userId), CacheTagGenerator::ALL_ACCOUNTS_TAG];
        $accountKey = CacheKeyGenerator::generate('', $this->userId);
        Cache::tags($accountTags)->remember($accountKey, 20, fn () => 'cached');

        $this->mock->method('create')->with('Some Account name', $this->userId, $this->accountId);
        $cachedHandler = new CachedHandler($this->mock);

        $this->assertEquals('cached', Cache::tags($accountTags)->get($accountKey));
        $cachedHandler->create('Some Account name', $this->userId, $this->accountId);
        $this->assertNull(Cache::tags($accountTags)->get($accountKey));
    }

    /**
     * @dataProvider tagsProvider
     */
    public function test_no_other_cache_is_flushed_on_create(array $otherTags): void
    {
        $accountKey = CacheKeyGenerator::generate('', $this->userId);
        Cache::tags($otherTags)->remember($accountKey, 20, fn () => 'cached');

        $this->mock->method('create')->with('Some account name', $this->userId, $this->accountId);
        $cachedHandler = new CachedHandler($this->mock);

        $this->assertEquals('cached', Cache::tags($otherTags)->get($accountKey));
        $cachedHandler->create('Some account name', $this->userId, $this->accountId);
        $this->assertEquals('cached', Cache::tags($otherTags)->get($accountKey));
    }

    public function test_cache_is_flushed_on_update(): void
    {
        $accountTags = [CacheTagGenerator::createUserTag($this->userId), CacheTagGenerator::ALL_ACCOUNTS_TAG];
        $accountKey = CacheKeyGenerator::generate('', $this->userId);
        Cache::tags($accountTags)->remember($accountKey, 20, fn () => 'cached');

        $this->mock->method('update')->with($this->accountId, 'Some Account name');
        $cachedHandler = new CachedHandler($this->mock);

        $this->assertEquals('cached', Cache::tags($accountTags)->get($accountKey));
        $cachedHandler->update($this->accountId, 'Some Account name');

        $this->assertNull(Cache::tags($accountTags)->get($accountKey));
    }

    public function test_cache_is_flushed_on_onboarding_step_update(): void
    {
        $accountTags = [CacheTagGenerator::createUserTag($this->userId), CacheTagGenerator::ALL_ACCOUNTS_TAG];
        $accountKey = CacheKeyGenerator::generate('', $this->userId);
        Cache::tags($accountTags)->remember($accountKey, 20, fn () => 'cached');

        $this->mock->method('updateOnboarding')->with($this->accountId, 'started');
        $cachedHandler = new CachedHandler($this->mock);

        $this->assertEquals('cached', Cache::tags($accountTags)->get($accountKey));
        $cachedHandler->update($this->accountId, 'started');

        $this->assertNull(Cache::tags($accountTags)->get($accountKey));
    }

    /**
     * @dataProvider tagsProvider
     */
    public function test_no_other_cache_is_flushed_on_update(array $otherTags): void
    {
        $accountKey = CacheKeyGenerator::generate('', $this->userId);
        Cache::tags($otherTags)->remember($accountKey, 20, fn () => 'cached');

        $this->mock->method('update')->with($this->accountId, 'Some Account name');
        $cachedHandler = new CachedHandler($this->mock);

        $this->assertEquals('cached', Cache::tags($otherTags)->get($accountKey));
        $cachedHandler->update($this->accountId, 'Some Account name');
        $this->assertEquals('cached', Cache::tags($otherTags)->get($accountKey));
    }

    public function test_cache_is_flushed_on_delete(): void
    {
        $accountTags = [CacheTagGenerator::createUserTag($this->userId), CacheTagGenerator::ALL_ACCOUNTS_TAG];
        $accountKey = CacheKeyGenerator::generate('', $this->userId);
        $roleTags = [CacheTagGenerator::createUserTag($this->userId), CacheTagGenerator::createAccountTag($this->accountId), CacheTagGenerator::ROLE_TAG];
        $roleKey = CacheKeyGenerator::generateRoleKey($this->accountId, $this->userId);

        Cache::tags($accountTags)->remember($accountKey, 20, fn () => 'cached_accounts');
        Cache::tags($roleTags)->remember($roleKey, 20, fn () => 'cached_roles');

        $this->mock->method('delete')->with($this->accountId);
        $cachedHandler = new CachedHandler($this->mock);

        $this->assertEquals('cached_accounts', Cache::tags($accountTags)->get($accountKey));
        $this->assertEquals('cached_roles', Cache::tags($roleTags)->get($roleKey));
        $cachedHandler->delete($this->accountId);
        $this->assertNull(Cache::tags($accountTags)->get($accountKey));
        $this->assertNull(Cache::tags($roleTags)->get($roleKey));
    }

    /**
     * @dataProvider tagsProvider
     */
    public function test_no_other_cache_is_flushed_on_delete(array $otherTags): void
    {
        $roleKey = CacheKeyGenerator::generateRoleKey($this->accountId, $this->userId);
        Cache::tags($otherTags)->remember($roleKey, 20, fn () => 'cached');

        $this->mock->method('delete')->with($this->accountId);
        $cachedHandler = new CachedHandler($this->mock);

        $this->assertEquals('cached', Cache::tags($otherTags)->get($roleKey));
        $cachedHandler->delete($this->accountId);
        $this->assertEquals('cached', Cache::tags($otherTags)->get($roleKey));
    }

    public function tagsProvider(): array
    {
        return [
            'empty array' => [
                [],
            ],
            'numeric array' => [
                [1, 2],
            ],
            'single value' => [
                ['lonely_tag'],
            ],
            'mixed array' => [
                ['not_so_lonely_tag', 2],
            ],
            'multiple values' => [
                ['some_tag', 'another_tag', 'abcd_tag'],
            ],
        ];
    }
}
