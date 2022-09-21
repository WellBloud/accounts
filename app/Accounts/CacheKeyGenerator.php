<?php

namespace App\Accounts;

class CacheKeyGenerator
{
    private const CACHE_PREFIX = 'accounts_';
    private const CACHE_ROLE_PREFIX = 'role_';

    public static function generate(string $accountId, string $userId): string
    {
        return self::CACHE_PREFIX . $accountId . '.' . $userId;
    }

    public static function generateRoleKey(string $accountId, string $userId): string
    {
        return self::CACHE_ROLE_PREFIX . $accountId . '.' . $userId;
    }
}
