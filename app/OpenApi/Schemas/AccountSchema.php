<?php

namespace App\OpenApi\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Account",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="user_id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="account_type", type="string", example="savings"),
 *     @OA\Property(property="currency_id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="balance", type="number", format="float", example=1000.50),
 *     @OA\Property(property="total_deposits", type="number", format="float", example=5000.00),
 *     @OA\Property(property="total_withdrawals", type="number", format="float", example=3000.00),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-09-01T12:34:56Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-09-01T12:34:56Z")
 * )
 */


