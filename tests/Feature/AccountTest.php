<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_user_can_create_up_to_five_accounts()
    {
        for ($i = 1; $i <= 5; $i++) {
            $response = $this->post('/accounts', ['name' => "Acct $i"]);
            $response->assertRedirect('/accounts');
            $this->assertDatabaseHas('accounts', ['name' => "Acct $i", 'user_id' => $this->user->id]);
        }

        $response = $this->post('/accounts', ['name' => 'Sixth']);
        $response->assertSessionHasErrors();
    }

    public function test_user_can_rename_and_delete_account()
    {
        $acct = Account::factory()->create(['user_id' => $this->user->id, 'name' => 'Old']);
        $response = $this->put("/accounts/{$acct->id}", ['name' => 'New']);
        $response->assertRedirect('/accounts');
        $this->assertDatabaseHas('accounts', ['id' => $acct->id, 'name' => 'New']);

        $response = $this->delete("/accounts/{$acct->id}");
        $response->assertRedirect('/accounts');
        $this->assertDatabaseMissing('accounts', ['id' => $acct->id]);
    }
}
