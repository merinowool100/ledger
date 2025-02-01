<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('ledgers', function (Blueprint $table) {
            // 'group_id' カラムをnullableに設定
            $table->string('group_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('ledgers', function (Blueprint $table) {
            // 'group_id' カラムを元に戻す（nullableを削除）
            $table->string('group_id')->nullable(false)->change();
        });
    }
};
