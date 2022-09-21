<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\AccountUser;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountUserFactory extends Factory
{
    protected $model = AccountUser::class;

    public function definition(): array
    {
        return [
            'user_id' => $this->faker->uuid,
            'account_id' => Account::factory()->makeOne()->getKey(),
            'role_id' => $this->faker->randomElement(['admin', 'user']),
            'deleted_at' => null,
        ];
    }
}
