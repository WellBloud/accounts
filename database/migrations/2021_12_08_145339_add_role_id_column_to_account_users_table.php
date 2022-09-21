<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoleIdColumnToAccountUsersTable extends Migration
{
    public function up(): void
    {
        Schema::table('account_users', function (Blueprint $table) {
            $table->dropColumn(['id']);
            $table->string('role_id')->after('user_id')->comment('Role Id from Auth0');
            $table->unique(['account_id', 'user_id', 'role_id'], 'acc_us_unq');
        });
    }

    public function down(): void
    {
        Schema::table('account_users', function (Blueprint $table) {
            $table->dropUnique('acc_us_unq');
            $table->dropColumn(['role_id']);
            $table->id();
        });
    }
}
