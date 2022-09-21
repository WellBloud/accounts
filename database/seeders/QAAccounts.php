<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountUser;
use App\Models\Information;
use Illuminate\Database\Seeder;

class QAAccounts extends Seeder
{
    private const AUTH0_ADMIN_ROLE = 'rol_7HmSMEWsJRenztT0';

    public function run(): void
    {
        foreach ($this->getUserSetup() as $userId => $setup) {
            $this->createAccount($userId, $setup['account_name'], $setup['onboarding_steps']);
        }
    }

    private function createAccount(string $userId, string $name, array $step): void
    {
        $account = Account::factory()->createOne(
            [
                'name' => $name,
            ]
        );
        AccountUser::factory()->createOne(
            [
                'account_id' => $account->getKey(),
                'user_id' => $userId,
                'role_id' => self::AUTH0_ADMIN_ROLE,
            ]
        );
        Information::factory()->createOne(
            [
                'informationable_id' => $account->getKey(),
                'informationable_type' => Account::class,
                'value' => [
                    Information::COMPANY_NAME => $name,
                    Information::ONBOARDING_STEP => json_encode($step, JSON_THROW_ON_ERROR),
                ],
            ]
        );
    }

    private function getUserSetup(): array
    {
        $defaultStep = [
            'dontWantToConnect' => [
                'status' => 'skipped',
                'hideInSidebar' => true,
            ],
            'survey' => [
                'status' => 'active',
                'substeps' => [],
            ],
            'learnAboutOurFramework' => [
                'status' => 'inactive',
            ],
        ];

        $facebookStep = [
            'facebook_ads' => [
                'status' => 'completed',
                'hasSidebarContent' => true,
                'displayName' => 'Facebook Ads',
            ],
            'google_ads' => [
                'status' => 'skipped',
                'hasSidebarContent' => true,
                'displayName' => 'Google Ads',
            ],
        ];

        $googleStep = [
            'facebook_ads' => [
                'status' => 'skipped',
                'hasSidebarContent' => true,
                'displayName' => 'Facebook Ads',
            ],
            'google_ads' => [
                'status' => 'completed',
                'hasSidebarContent' => true,
                'displayName' => 'Google Ads',
            ],
        ];

        $facebookAndGoogleStep = [
            'facebook_ads' => [
                'status' => 'completed',
                'hasSidebarContent' => true,
                'displayName' => 'Facebook Ads',
            ],
            'google_ads' => [
                'status' => 'completed',
                'hasSidebarContent' => true,
                'displayName' => 'Google Ads',
            ],
        ];

        // these users are hardcoded in Terraform for QA environment, so we have full Auth0 login functionality
        // https://bitbucket.org/LumenAd_Platform/joinr-terraform/src/main/auth0/qa/users.tf
        return [
            'auth0|no_datasources' => [
                'account_name' => 'No datasource',
                'onboarding_steps' => [
                    'version' => 1,
                    'steps' => $defaultStep,
                ],
            ],
            'auth0|fb_datasource' => [
                'account_name' => 'FB datasource',
                'onboarding_steps' => [
                    'version' => 1,
                    'steps' => array_merge($facebookStep, $defaultStep),
                ],
            ],
            'auth0|ga_datasource' => [
                'account_name' => 'GA datasource',
                'onboarding_steps' => [
                    'version' => 1,
                    'steps' => array_merge($googleStep, $defaultStep),
                ],
            ],
            'auth0|fb_and_ga_datasource' => [
                'account_name' => 'FB + GA datasource',
                'onboarding_steps' => [
                    'version' => 1,
                    'steps' => array_merge($facebookAndGoogleStep, $defaultStep),
                ],
            ],
        ];
    }
}
