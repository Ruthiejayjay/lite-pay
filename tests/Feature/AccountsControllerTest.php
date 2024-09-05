<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AccountsControllerTest extends TestCase
{

    use DatabaseTransactions;

    protected User $user;
    protected Currency $currency;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->currency = Currency::factory()->create(['currency_code' => 'USD']);
        Auth::login($this->user);
    }

    protected function createAccount(array $attributes = [])
    {
        return Account::factory()->create(array_merge([
            'user_id' => $this->user->id,
            'currency_id' => $this->currency->id,
        ], $attributes));
    }

    public function test_it_can_list_accounts()
    {
        $this->createAccount();

        $response = $this->getJson(route('accounts.index'));

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Accounts Retrieved Successfully',
            ]);
    }

    public function test_it_can_create_an_account()
    {
        $accountData = [
            'account_type' => 'savings',
            'balance' => 1000,
            'total_deposits' => 1000,
            'currency_code' => 'USD',
        ];

        $response = $this->postJson(route('accounts.store'), $accountData);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Accounts Created Successfully',
            ]);

        $this->assertDatabaseHas('accounts', [
            'user_id' => $this->user->id,
            'account_holder_name' => $this->user->name,
        ]);
    }

    public function test_it_can_update_an_account_balance()
    {
        $account = $this->createAccount(['balance' => 1000, 'total_deposits' => 1000]);

        $response = $this->putJson(route('accounts.update', $account->id), [
            'balance' => 500,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Account Balance Updated Successfully',
            ]);

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'balance' => 1500,
            'total_deposits' => 1500,
        ]);
    }

    public function test_it_can_delete_an_account()
    {
        $account = $this->createAccount();

        $response = $this->deleteJson(route('accounts.destroy', $account->id));

        $response->assertStatus(204);

        $this->assertDatabaseMissing('accounts', ['id' => $account->id]);
    }
}
