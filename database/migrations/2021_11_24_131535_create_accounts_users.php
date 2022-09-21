<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountsUsers extends Migration
{
    public function up(): void
    {
        Schema::create('account_users', function (Blueprint $table) {
            $table->id();
            $table->uuid('account_id');
            $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->string('user_id')->comment('Auth0 user id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_users');
    }
}
