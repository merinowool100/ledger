<?php

namespace Tests\Feature;

use App\Models\Ledger;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        // create user and authenticate
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_fetch_returns_ledgers_and_token()
    {
        // create an account for the user and attach ledger
        $account = \App\Models\Account::factory()->create(['user_id' => $this->user->id]);
        Ledger::factory()->create([
            'user_id' => $this->user->id,
            'account_id' => $account->id,
            'date' => '2026-03-01',
            'item' => 'Coffee',
            'amount' => 300,
        ]);

        $response = $this->getJson('/sync');

        $response->assertStatus(200)
            ->assertJsonStructure(['ledgers', 'token']);

        $json = $response->json();
        $this->assertCount(1, $json['ledgers']);

        // filter by the account should still return the same
        $response2 = $this->getJson('/sync?account=' . $account->id);
        $response2->assertStatus(200);
        $this->assertCount(1, $response2->json('ledgers'));

        // and filtering by a different account yields none
        $other = \App\Models\Account::factory()->create(['user_id' => $this->user->id]);
        $response3 = $this->getJson('/sync?account=' . $other->id);
        $response3->assertStatus(200);
        $this->assertCount(0, $response3->json('ledgers'));
    }

    public function test_push_applies_new_record_and_updates_token()
    {
        $payload = [
            'ledgers' => [
                [
                    'transaction_id' => 'tx-1',
                    'version' => 0,
                    'date' => '2026-03-02',
                    'item' => 'Snack',
                    'amount' => 150,
                    'account_id' => $account->id,
                ],
            ],
        ];

        $response = $this->postJson('/sync', $payload);
        $response->assertStatus(200)
                 ->assertJsonStructure(['updated', 'token']);

        $this->assertDatabaseHas('ledgers', [
            'transaction_id' => 'tx-1',
            'amount' => 150,
            'version' => 1,
        ]);
        // horizon placeholder should exist at least five years later than inserted date
        $horizonDate = Carbon\parse('2026-03-02')->addYears(5)->toDateString();
        $this->assertDatabaseHas('ledgers', [
            'amount' => 0,
            'date' => $horizonDate,
            'account_id' => $account->id,
        ]);
    }

    public function test_push_conflict_returns_409()
    {
        $ledger = Ledger::factory()->create([
            'user_id' => $this->user->id,
            'account_id' => $account->id,
            'transaction_id' => 'tx-conflict',
            'version' => 2,
        ]);

        $payload = [
            'ledgers' => [
                [
                    'transaction_id' => 'tx-conflict',
                    'version' => 1, // old version
                    'date' => '2026-03-03',
                    'item' => 'Lunch',
                    'amount' => 1000,
                ],
            ],
        ];

        $response = $this->postJson('/sync', $payload);
        $response->assertStatus(409);
        $response->assertJsonFragment(['transaction_id' => 'tx-conflict']);
    }
}
