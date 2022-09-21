<?php

namespace App\Accounts;

use App\Models\Account;
use App\Models\AccountUser;
use Illuminate\Database\Eloquent\Builder;

class Provider implements AccountProvider
{
    public function getAccounts(string $userId): array
    {
        return Account::query()
            ->whereHas('users', fn (Builder $query) => $query->where('user_id', $userId))
            ->get()
            ->toArray();
    }

    public function getRole(string $id, string $userId): string
    {
        /** @var AccountUser $accountUser */
        $accountUser = AccountUser::query()
            ->where(AccountUser::ACCOUNT_ID, '=', $id)
            ->where(AccountUser::USER_ID, '=', $userId)
            ->firstOrFail();

        return $accountUser->role_id;
    }
}
