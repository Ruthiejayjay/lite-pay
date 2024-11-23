<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $senderAccount = Account::factory()->create();
        $receiverAccount = Account::factory()->create();

        return [
            'sender_account_id' => $senderAccount->id,
            'receiver_account_id' => $receiverAccount->id,
            'receiver_account_number' => $receiverAccount->account_number, // Ensure this matches your schema
            'receiver_account_holder_name' => $receiverAccount->account_holder_name,
            'currency_id' => $senderAccount->currency_id, // Assuming both accounts share the same currency
            'amount' => $this->faker->numberBetween(100, 1000),
            'status' => 'completed',
        ];
    }
}
