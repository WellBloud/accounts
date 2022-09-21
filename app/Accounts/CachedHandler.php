<?php

namespace App\Accounts;

use App\Models\Account;
use Illuminate\Support\Facades\Cache;

class CachedHandler implements AccountHandler
{
    private AccountHandler $databaseHandler;

    public function __construct(AccountHandler $databaseHandler)
    {
        $this->databaseHandler = $databaseHandler;
    }

    public function create(string $name, string $userId, string $roleId): Account
    {
        Cache::tags([CacheTagGenerator::createUserTag($userId)])->flush();

        return $this->databaseHandler->create($name, $userId, $roleId);
    }

    public function addUser(string $id, string $userId, string $roleId): void
    {
        Cache::tags([CacheTagGenerator::createUserTag($userId)])->flush();

        $this->databaseHandler->addUser($id, $userId, $roleId);
    }

    public function update(string $id, string $name): Account
    {
        Cache::tags([CacheTagGenerator::ALL_ACCOUNTS_TAG])->flush();

        return $this->databaseHandler->update($id, $name);
    }

    public function delete(string $id): void
    {
        Cache::tags([
            CacheTagGenerator::createAccountTag($id),
            CacheTagGenerator::ALL_ACCOUNTS_TAG,
            CacheTagGenerator::ROLE_TAG,
        ])->flush();

        $this->databaseHandler->delete($id);
    }

    public function updateOnboarding(string $id, ?string $step): void
    {
        Cache::tags([CacheTagGenerator::ALL_ACCOUNTS_TAG])->flush();

        $this->databaseHandler->updateOnboarding($id, $step);
    }
}
