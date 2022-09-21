<?php

namespace App\Accounts;

use App\Models\Account;
use App\Models\AccountUser;
use App\Models\Information;
use App\Models\Subscription;
use App\Services\Auth0;
use App\Services\Connect\Client;
use Ramsey\Uuid\Uuid;

class Handler implements AccountHandler
{
    public function __construct(private readonly Auth0 $auth0, private readonly Client $connectClient)
    {
    }

    public function create(string $name, string $userId, string $roleId): Account
    {
        $account = new Account();
        $account->id = Uuid::uuid4()->toString();
        $account->name = $name;
        $account->save();

        $account->information()->create(
            [
                'value' => [
                    Information::COMPANY_NAME => $name,
                    Information::ONBOARDING_STEP => null,
                ],
            ]
        );
        $this->addUser($account->getKey(), $userId, $roleId);
        $account->load(['information', 'subscriptions']);

        return $account;
    }

    public function addUser(string $id, string $userId, string $roleId): void
    {
        $user = new AccountUser();
        $user->user_id = $userId;
        $user->role_id = $roleId;

        $account = Account::query()->findOrFail($id);
        $account->users()->save($user);
    }

    public function update(string $id, string $name): Account
    {
        /** @var Account $account */
        $account = Account::query()->findOrFail($id);
        $account->name = $name;
        $account->save();

        return $account;
    }

    public function delete(string $id): void
    {
        $account = Account::query()->with(['information', 'subscriptions', 'users'])->findOrFail($id);
        $account->users->each(function (AccountUser $user) {
            $this->auth0->deleteUser($user->user_id);
            $user->delete();
        });
        $account->subscriptions->each(fn (Subscription $subscription) => $subscription->delete());
        $account->information()->delete();
        $account->delete();
        $this->connectClient->deleteDatasourcesForAccount($id);
    }

    public function updateOnboarding(string $id, ?string $step): void
    {
        /** @var Account $account */
        $account = Account::query()->findOrFail($id);

        if ($info = $account->information()->first()) {
            $info->value = array_merge($info->value, [Information::ONBOARDING_STEP => $step]);
            $info->save();

            return;
        }

        $account->information()->create(['value' => [Information::ONBOARDING_STEP => $step]]);
    }
}
