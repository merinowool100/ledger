<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ledgers', function (Blueprint $table) {
            // composite index for common filtering by user, account and date
            $table->index(['user_id', 'account_id', 'date'], 'ledgers_user_account_date_idx');
            // index to speed up account-based queries
            $table->index(['account_id', 'date'], 'ledgers_account_date_idx');
            // index for group updates
            $table->index('group_id', 'ledgers_group_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('ledgers', function (Blueprint $table) {
            $table->dropIndex('ledgers_user_account_date_idx');
            $table->dropIndex('ledgers_account_date_idx');
            $table->dropIndex('ledgers_group_id_idx');
        });
    }
};
