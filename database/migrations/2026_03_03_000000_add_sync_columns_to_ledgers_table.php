<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ledgers', function (Blueprint $table) {
            // unique identifier used by clients to track a specific transaction
            $table->string('transaction_id')->after('group_id')->nullable();
            $table->unsignedBigInteger('version')->default(0)->after('transaction_id');
            // optional time portion for ordering when date alone is ambiguous
            $table->time('effective_time')->nullable()->after('date');

            $table->index('transaction_id');
            $table->index(['user_id', 'updated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ledgers', function (Blueprint $table) {
            $table->dropIndex(['transaction_id']);
            $table->dropIndex(['user_id', 'updated_at']);
            $table->dropColumn(['transaction_id', 'version', 'effective_time']);
        });
    }
};
