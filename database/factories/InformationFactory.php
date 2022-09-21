<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Information;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class InformationFactory extends Factory
{
    protected $model = Information::class;

    public function definition(): array
    {
        /** @var Account $account */
        $account = Account::factory()->makeOne();

        return [
            'informationable_id' => $account->getKey(),
            'informationable_type' => Account::class,
            'value' => [Information::COMPANY_NAME => $account->name, Information::ONBOARDING_STEP => $this->faker->word],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'deleted_at' => null,
        ];
    }
}
