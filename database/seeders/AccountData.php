<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountUser;
use Illuminate\Database\Seeder;

class AccountData extends Seeder
{
    public function run(): void
    {
        Account::factory()
           ->count(10)
           ->has(AccountUser::factory()->count(3), 'users')
           ->create();
    }
}
