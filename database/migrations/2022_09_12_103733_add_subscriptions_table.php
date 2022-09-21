<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubscriptionsTable extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', static function (Blueprint $table) {
            $table->id();
            $table->uuid('account_id');
            $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->string('customer_id')->comment('Stripe customer_id');
            $table->string('subscription_id')->comment('Stripe price_id');
            $table->string('status');
            $table->dateTime('valid_to')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
}
