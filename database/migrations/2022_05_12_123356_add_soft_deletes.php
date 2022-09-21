<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftDeletes extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', static fn (Blueprint $table) => $table->softDeletes());
        Schema::table('account_users', static fn (Blueprint $table) => $table->softDeletes());
        Schema::table('information', static fn (Blueprint $table) => $table->softDeletes());
    }

    public function down(): void
    {
        Schema::table('accounts', static fn (Blueprint $table) => $table->dropSoftDeletes());
        Schema::table('account_users', static fn (Blueprint $table) => $table->dropSoftDeletes());
        Schema::table('information', static fn (Blueprint $table) => $table->dropSoftDeletes());
    }
}
