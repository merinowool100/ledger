<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ledgers', function (Blueprint $table) {
            // 'pending' or 'confirmed' - default 'pending'
            $table->enum('status', ['pending', 'confirmed'])->default('pending')->after('balance');
            // index for filtering
            $table->index(['user_id', 'status', 'date'], 'ledgers_user_status_date_idx');
        });
    }

    public function down(): void
    {
        Schema::table('ledgers', function (Blueprint $table) {
            $table->dropIndex('ledgers_user_status_date_idx');
            $table->dropColumn('status');
        });
    }
};
