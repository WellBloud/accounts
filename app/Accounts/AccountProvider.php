<?php

namespace App\Accounts;

interface AccountProvider
{
    public function getAccounts(string $userId): array;

    public function getRole(string $id, string $userId): string;
}
