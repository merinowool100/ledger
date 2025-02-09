<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('ledgers', function (Blueprint $table) {
            $table->integer('balance')->default(0);// 初期値を0に設定
        });
    }
    
    public function down()
    {
        Schema::table('ledgers', function (Blueprint $table) {
            $table->dropColumn('balance');
        });
    }
    
};
