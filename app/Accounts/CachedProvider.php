<?php

namespace App\Accounts;

use Illuminate\Support\Facades\Cache;

class CachedProvider implements AccountProvider
{
    private AccountProvider $databaseProvider;

    private int $cacheTTL;

    public function __construct(AccountProvider $databaseProvider, int $cacheTTL)
    {
        $this->databaseProvider = $databaseProvider;
        $this->cacheTTL = $cacheTTL;
    }

    public function getAccounts(string $userId): array
    {
        return Cache::tags([
            CacheTagGenerator::createUserTag($userId), CacheTagGenerator::ALL_ACCOUNTS_TAG,
        ])->remember(CacheKeyGenerator::generate('', $userId), $this->cacheTTL, function () use ($userId) {
            return $this->databaseProvider->getAccounts($userId);
        });
    }

    public function getRole(string $id, string $userId): string
    {
        return Cache::tags([
            CacheTagGenerator::createUserTag($userId), CacheTagGenerator::createAccountTag($id), CacheTagGenerator::ROLE_TAG,
        ])->remember(CacheKeyGenerator::generateRoleKey($id, $userId), $this->cacheTTL, function () use ($id, $userId) {
            return $this->databaseProvider->getRole($id, $userId);
        });
    }
}
