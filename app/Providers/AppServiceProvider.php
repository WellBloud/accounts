<?php

namespace App\Providers;

use App\Accounts\AccountHandler;
use App\Accounts\AccountProvider;
use App\Accounts\CachedHandler;
use App\Accounts\CachedProvider;
use App\Accounts\Handler;
use App\Accounts\Provider;
use App\Listeners\ProcessStripeEvent;
use App\Services\Auth0;
use App\Services\Connect\Client as ConnectClient;
use App\Services\Stripe\Client as StripeClient;
use App\Services\Stripe\WebhookProcessors\CheckoutSessionCompleted;
use App\Services\Stripe\WebhookProcessors\InvoicePaid;
use App\Services\Stripe\WebhookProcessors\InvoicePaymentFailed;
use App\Services\Stripe\WebhookProcessors\NullProcessor;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;
use Knuckles\Scribe\Scribe;
use Stripe\StripeClient as Stripe;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Auth0::class, fn (Container $app) => new Auth0(
            config('auth0.domain'),
            config('auth0.clientId'),
            config('auth0.clientSecret')
        ));

        $this->app->singleton(AccountProvider::class, fn (Container $app) => $app->make(Provider::class));
        $this->app->extend(AccountProvider::class, fn ($service, Container $app) => new CachedProvider($service, config('cache.default_ttl')));

        $this->app->singleton(AccountHandler::class, fn (Container $app) => $app->make(Handler::class));
        $this->app->extend(AccountHandler::class, fn ($service, Container $app) => new CachedHandler($service));

        $this->app->singleton(ConnectClient::class, fn (Container $app) => new ConnectClient(config('connect.baseUrl'), config('connect.secretKey')));

        $this->app->singleton(Stripe::class, fn (Container $app) => new Stripe(config('stripe.secretKey')));
        $this->app->singleton(StripeClient::class, fn (Container $app) => new StripeClient($app->make(Stripe::class), config('stripe.webhookSecretKey')));

        $this->app->singleton(ProcessStripeEvent::class, fn (Container $app) => new ProcessStripeEvent(
            collect(
                [
                    $app->make(CheckoutSessionCompleted::class),
                    $app->make(InvoicePaid::class),
                    $app->make(InvoicePaymentFailed::class),
                    $app->make(NullProcessor::class), // make sure this is last
                ]
            )
        ));
    }

    public function boot(): void
    {
        if (class_exists(Scribe::class)) {
            Scribe::afterGenerating(function (array $paths) {
                rename($paths['openapi'], $this->app->basePath('./openapi.yaml'));
            });
        }
    }
}
