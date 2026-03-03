<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('starting_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // the date/time as of which this balance is calculated
            $table->dateTime('as_of');
            $table->integer('balance');
            // incremental revision to detect stale caches
            $table->unsignedBigInteger('revision')->default(1);
            $table->timestamps();

            $table->unique(['user_id', 'as_of']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('starting_balances');
    }
};
