<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        /** @var Account $account */
        $account = Account::factory()->makeOne();

        return [
            'account_id' => $account->getKey(),
            'customer_id' => $this->faker->word,
            'subscription_id' => $this->faker->word,
            'valid_to' => Carbon::now()->addDays(7),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'deleted_at' => null,
        ];
    }
}
