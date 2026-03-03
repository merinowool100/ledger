<?php

namespace Database\Factories;

use App\Models\Ledger;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LedgerFactory extends Factory
{
    protected $model = Ledger::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            // account will be created separately when needed
            'account_id' => null,
            'date' => $this->faker->date(),
            'item' => $this->faker->word(),
            'amount' => $this->faker->numberBetween(-1000, 1000),
            'balance' => 0,
            'transaction_id' => (string) \Illuminate\Support\Str::uuid(),
            'version' => 1,
        ];
    }
}
