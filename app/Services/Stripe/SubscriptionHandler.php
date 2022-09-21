<?php

namespace App\Services\Stripe;

use App\Exceptions\InvalidStripeInvoiceStatusException;
use App\Models\Account;
use App\Models\Subscription;
use Stripe\Checkout\Session;
use Stripe\Invoice;
use Stripe\Subscription as StripeSubscription;

class SubscriptionHandler
{
    public function create(string $accountId, Session $session): void
    {
        Account::query()->findOrFail($accountId);
        Subscription::query()->create(
            [
                'account_id' => $accountId,
                'customer_id' => $session->customer,
                'subscription_id' => $session->subscription,
                'status' => StripeSubscription::STATUS_ACTIVE,
            ]
        );
    }

    public function invoicePaid(string $customerId, string $status): void
    {
        if ($status !== Invoice::STATUS_PAID) {
            throw new InvalidStripeInvoiceStatusException('Trying to forge active subscription for customer_id `' . $customerId . '`  with invoice status `' . $status . '`');
        }

        $subscription = Subscription::query()->where(['customer_id' => $customerId])->firstOrFail();
        $subscription->update(['status' => StripeSubscription::STATUS_ACTIVE]);
    }

    public function invoicePaymentFailed(string $customerId): void
    {
        $subscription = Subscription::query()->where(['customer_id' => $customerId])->firstOrFail();
        $subscription->update(['status' => StripeSubscription::STATUS_INCOMPLETE]);
    }
}
