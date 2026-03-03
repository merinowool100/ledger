<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ledgers', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->after('user_id')->constrained('accounts')->cascadeOnDelete();
        });

        Schema::table('starting_balances', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->after('user_id')->constrained('accounts')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ledgers', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropColumn('account_id');
        });
        Schema::table('starting_balances', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropColumn('account_id');
        });
    }
};
