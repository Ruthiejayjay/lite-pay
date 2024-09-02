<?php

namespace App\Http\Controllers\Api\General;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

class AccountsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/accounts",
     *     tags={"Accounts"},
     *     summary="Get all user accounts",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Accounts retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Accounts Retrieved Successfully"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
     *                     @OA\Property(property="user_id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
     *                     @OA\Property(property="account_type", type="string", example="savings"),
     *                     @OA\Property(property="currency_id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
     *                     @OA\Property(property="balance", type="number", format="float", example=1000.50),
     *                     @OA\Property(property="total_deposits", type="number", format="float", example=5000.00),
     *                     @OA\Property(property="total_withdrawals", type="number", format="float", example=3000.00),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-09-01T12:34:56Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-09-01T12:34:56Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failure"),
     *             @OA\Property(property="message", type="string", example="Unauthenticated"),
     *             @OA\Property(property="status_code", type="integer", example=401)
     *         )
     *     )
     * )
     */
    public function index()
    {
        $accounts = Account::where('user_id', Auth::id())->get();

        if ($accounts->isEmpty()) {
            return response()->json([
                'status' => Status::SUCCESS,
                'message' => 'You have no accounts',
                'status_code' => Response::HTTP_OK,
            ]);
        }

        return response()->json([
            'status' => Status::SUCCESS,
            'message' => 'Accounts Retrieved Successfully',
            'status_code' => Response::HTTP_OK,
            'data' => $accounts
        ]);
    }
}
