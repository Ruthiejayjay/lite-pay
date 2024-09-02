<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            ['id' => Str::uuid(), 'currency_code' => 'EUR', 'created_at'=> now(), 'updated_at' => now()],
            ['id' => Str::uuid(), 'currency_code' => 'GBP', 'created_at'=> now(), 'updated_at' => now()],
            ['id' => Str::uuid(), 'currency_code' => 'NGN', 'created_at'=> now(), 'updated_at' => now()],
            ['id' => Str::uuid(), 'currency_code' => 'USD', 'created_at'=> now(), 'updated_at' => now()],
        ];

        DB::table('currencies')->insert($currencies);
    }
}
