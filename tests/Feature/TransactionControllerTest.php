<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Currency;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{

    use RefreshDatabase;
    protected User $user;
    protected Account $senderAccount;
    protected Account $receiverAccount;
    protected Currency $currency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->currency = Currency::factory()->create(['currency_code' => 'USD']);

        $this->senderAccount = Account::factory()->create([
            'user_id' => $this->user->id,
            'currency_id' => $this->currency->id,
            'balance' => 1000,
            'total_deposits' => 1000,
            'total_withdrawals' => 0,
        ]);

        $this->receiverAccount = Account::factory()->create([
            'currency_id' => $this->currency->id,
            'balance' => 0, // Ensure balance starts at 0
        ]);

        $this->actingAs($this->user);
    }

    public function test_it_can_list_transactions()
    {
        Transaction::factory()->create([
            'sender_account_id' => $this->senderAccount->id,
            'receiver_account_id' => $this->receiverAccount->id,
            'amount' => 500,
        ]);

        $response = $this->getJson(route('transactions.index'));

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Transactions Retrieved Successfully'
            ]);
    }

    public function test_it_can_store_a_transaction(): void
    {
        $this->assertEquals(1000, $this->senderAccount->balance, 'Initial sender account balance mismatch');
        $this->assertEquals(0, $this->receiverAccount->balance, 'Initial receiver account balance mismatch');

        $payload = [
            'receiver_account_number' => $this->receiverAccount->account_number,
            'amount' => 500,
            'currency_code' => $this->currency->currency_code,
        ];

        $response = $this->postJson(route('transactions.store'), $payload);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Transaction successful',
                'data' => [
                    'sender_account_id' => $this->senderAccount->id,
                    'receiver_account_id' => $this->receiverAccount->id,
                    'amount' => 500,
                    'currency_id' => $this->currency->id,
                    'status' => 'completed',
                ],
            ]);

        $updatedSenderAccount = $this->senderAccount->fresh();
        $updatedReceiverAccount = $this->receiverAccount->fresh();

        $this->assertEquals(500, $updatedSenderAccount->balance, 'Sender account balance mismatch after transaction');
        $this->assertEquals(500, $updatedReceiverAccount->balance, 'Receiver account balance mismatch after transaction');
    }

    public function test_it_fails_to_store_transaction_due_to_insufficient_balance()
    {
        $transactionData = [
            'receiver_account_number' => $this->receiverAccount->account_number,
            'currency_code' => $this->currency->currency_code,
            'amount' => 2000, // Greater than sender's balance
        ];

        $response = $this->postJson(route('transactions.store'), $transactionData);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'failure',
                'message' => 'Insufficient balance',
            ]);

        // Assert sender's balance remains unchanged
        $this->assertEquals(1000, $this->senderAccount->fresh()->balance, 'Sender account balance mismatch after failed transaction');
    }

    public function test_it_fails_to_store_transaction_when_no_account_with_currency_code()
    {
        // Create a sender account with a different currency
        $senderAccount = Account::factory()->create(); // Currency differs from the transaction request
        $receiverAccount = Account::factory()->create();
        $currency = Currency::factory()->create();

        $transactionData = [
            'receiver_account_number' => $receiverAccount->account_number,
            'currency_code' => $currency->currency_code, // Currency that doesn't match sender's account
            'amount' => 500,
        ];

        // Simulate an authenticated user
        $response = $this->actingAs($senderAccount->user)->postJson(route('transactions.store'), $transactionData);

        // Assert the validation failure response
        $response->assertStatus(422) // Updated to match Laravel's validation error status code
            ->assertJsonValidationErrors(['currency_code']) // Assert the exact field with an error
            ->assertJsonFragment([
                'currency_code' => ['No associated account found with this currency.'],
            ]);

        // Assert the database has no new transaction for this attempt
        $this->assertDatabaseMissing('transactions', [
            'sender_account_id' => $senderAccount->id,
            'receiver_account_id' => $receiverAccount->id,
            'amount' => 500,
        ]);
    }

    public function test_it_can_show_a_transaction()
    {
        $transaction = Transaction::factory()->create([
            'sender_account_id' => $this->senderAccount->id,
            'receiver_account_id' => $this->receiverAccount->id,
            'amount' => 500,
            'currency_id' => $this->currency->id,
            'status' => 'completed',
        ]);

        $response = $this->getJson(route('transactions.show', $transaction->id));

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Transaction Retrieved Successfully',
                'data' => [
                    'id' => $transaction->id,
                    'sender_account_id' => $this->senderAccount->id,
                    'receiver_account_id' => $this->receiverAccount->id,
                    'amount' => 500,
                    'currency_id' => $this->currency->id,
                    'status' => 'completed',
                ],
            ]);
    }

    public function test_it_can_delete_a_transaction()
    {
        $transaction = Transaction::factory()->create([
            'sender_account_id' => $this->senderAccount->id,
            'receiver_account_id' => $this->receiverAccount->id,
            'amount' => 500,
            'currency_id' => $this->currency->id,
            'status' => 'completed',
        ]);

        $response = $this->deleteJson(route('transactions.destroy', $transaction->id));

        $response->assertStatus(204);

        // Assert the transaction is deleted from the database
        $this->assertDatabaseMissing('transactions', [
            'id' => $transaction->id,
        ]);
    }
}
