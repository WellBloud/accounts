<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddReplicaIdentityFullToAccountUsersTable extends Migration
{
    public function up(): void
    {
        DB::statement(
            sprintf('ALTER TABLE %s.account_users REPLICA IDENTITY FULL', config('database.connections.pgsql.schema'))
        );
    }

    public function down(): void
    {
        DB::statement(
            sprintf('ALTER TABLE %s.account_users REPLICA IDENTITY DEFAULT', config('database.connections.pgsql.schema'))
        );
    }
}
