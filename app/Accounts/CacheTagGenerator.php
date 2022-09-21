<?php

namespace App\Accounts;

class CacheTagGenerator
{
    public const ROLE_TAG = 'role';
    public const ALL_ACCOUNTS_TAG = 'all_accounts';

    private const CACHE_ACCOUNT_PREFIX = 'account_';
    private const CACHE_USER_PREFIX = 'user_';

    public static function createAccountTag(string $accountId): string
    {
        return self::CACHE_ACCOUNT_PREFIX . $accountId;
    }

    public static function createUserTag(string $userId): string
    {
        return self::CACHE_USER_PREFIX . $userId;
    }
}
