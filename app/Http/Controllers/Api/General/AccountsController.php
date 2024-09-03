<?php

namespace App\Http\Controllers\Api\General;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAccountRequest;
use App\Models\Account;
use App\Models\Currency;
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
    /**
     * @OA\Post(
     *     path="/api/v1/accounts",
     *     summary="Create a new account",
     *     tags={"Accounts"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"account_type", "balance", "total_deposits", "total_withdrawals", "currency_code"},
     *             @OA\Property(property="account_type", type="string", example="savings"),
     *             @OA\Property(property="balance", type="number", format="float", example=1000.00),
     *             @OA\Property(property="total_deposits", type="number", format="float", example=0.00),
     *             @OA\Property(property="total_withdrawals", type="number", format="float", example=0.00),
     *             @OA\Property(property="currency_code", type="string", example="USD")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Account Created Successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Accounts Created Successfully"),
     *             @OA\Property(property="status_code", type="integer", example=201),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="c9a1f8f5-1010-4d5a-88c4-f60c7b6537c2"),
     *                 @OA\Property(property="user_id", type="string", format="uuid", example="e43e0d0d-d7c4-4a82-8c55-36c947d11692"),
     *                 @OA\Property(property="account_type", type="string", example="savings"),
     *                 @OA\Property(property="balance", type="number", format="float", example=1000.00),
     *                 @OA\Property(property="total_deposits", type="number", format="float", example=0.00),
     *                 @OA\Property(property="total_withdrawals", type="number", format="float", example=0.00),
     *                 @OA\Property(property="currency_id", type="string", format="uuid", example="aa0e4cba-72fc-41ff-847b-4b1b4d3cd832"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-03T12:34:56Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-09-03T12:34:56Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Currency not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failure"),
     *             @OA\Property(property="message", type="string", example="Currency not found"),
     *             @OA\Property(property="status_code", type="integer", example=404)
     *         )
     *     )
     * )
     */
    public function store(StoreAccountRequest $request)
    {
        $validated = $request->validated();

        $currency = Currency::where('currency_code', $validated['currency_code'])->first();

        if (!$currency) {
            return response()->json([
                'status' => Status::FAILURE,
                'message' => 'Currency not found',
                'status_code' => Response::HTTP_NOT_FOUND,
            ], Response::HTTP_NOT_FOUND);
        }

        $account = Account::create([
            'user_id' => Auth::id(),
            'account_type' => $validated['account_type'],
            'balance' => $validated['balance'],
            'total_deposits' => $validated['total_deposits'],
            'total_withdrawals' => $validated['total_withdrawals'],
            'currency_id' => $currency->id
        ]);

        return response()->json([
            'status' => Status::SUCCESS,
            'message' => 'Accounts Created Successfully',
            'status_code' => Response::HTTP_CREATED,
            'data' => $account
        ], Response::HTTP_CREATED);
    }
}
