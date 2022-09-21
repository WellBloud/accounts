<?php

use App\Http\Controllers\AccountsController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\StripeWebhooksController;
use Laravel\Lumen\Routing\Router;
use Symfony\Component\HttpFoundation\Response;

/** @var Router $router */
$router->get('/', function () {
    return 'Nothing to see here ¯\_(ツ)_/¯';
});

$router->get('/health', function () {
    echo Response::HTTP_OK;
});

$router->group(['prefix' => 'accounts'], function (Router $router) {
    $router->group(['middleware' => 'user_id_required'], function (Router $router) {
        $router->post('/', ['uses' => AccountsController::class . '@store']);
        $router->get('/', ['uses' => AccountsController::class . '@index']);
        $router->get('/{id}/role', ['uses' => AccountsController::class . '@role']);
        $router->put('/{id}/step', ['uses' => AccountsController::class . '@step']);
    });

    $router->put('/{id}', ['uses' => AccountsController::class . '@update']);
    $router->delete('/{id}', ['uses' => AccountsController::class . '@delete']);

    $router->group(['prefix' => '{accountId}', 'middleware' => 'account_id_required'], function (Router $router) {
        $router->group(['prefix' => 'payment'], function (Router $router) {
            $router->post('/checkout', ['uses' => PaymentsController::class . '@checkout']);
        });
    });
});


$router->group(['prefix' => 'payments'], function (Router $router) {
    $router->post('/webhook', ['uses' => StripeWebhooksController::class . '@process']);
});
