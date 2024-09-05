<?php

namespace Database\Factories;

use App\Models\Currency;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'currency_id' => Currency::factory(),
            'account_holder_name' => $this->faker->name(),
            'account_number' => $this->faker->unique()->numerify('##########'),
            'account_type' => $this->faker->randomElement(['savings', 'checking']),
            'balance' => $this->faker->randomFloat(2, 0, 1000),
            'total_deposits' => 0,
            'total_withdrawals' => 0,
        ];
    }
}
