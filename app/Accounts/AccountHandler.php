<?php

namespace App\Accounts;

use App\Models\Account;

interface AccountHandler
{
    public function create(string $name, string $userId, string $roleId): Account;

    public function addUser(string $id, string $userId, string $roleId): void;

    public function update(string $id, string $name): Account;

    public function delete(string $id): void;

    public function updateOnboarding(string $id, ?string $step): void;
}
