<?php

namespace App\Http\Controllers\Api\General;

use App\Enums\Status;
use App\Models\Transaction;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    /**
     * @OA\Get(
     *     path="/api/v1/transactions",
     *     tags={"Transactions"},
     *     summary="Get all user transactions",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Transactions retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Transactions Retrieved Successfully"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
     *                     @OA\Property(property="sender_account_id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
     *                     @OA\Property(property="receiver_account_id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
     *                     @OA\Property(property="receiver_account_holder_name", type="string", example="John Doe"),
     *                     @OA\Property(property="account_number", type="number", format="float", example=1234567890),
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
        $userAccountIds = Auth::user()->accounts->pluck('id');
        $transactions = Transaction::whereIn('sender_account_id', $userAccountIds)
            ->orWhereIn('receiver_account_id', $userAccountIds)
            ->get();

        if ($transactions->isEmpty()) {
            return response()->json([
                'status' => Status::SUCCESS,
                'message' => 'You have no transactions',
                'status_code' => Response::HTTP_OK,
            ]);
        }

        return response()->json([
            'status' => Status::SUCCESS,
            'message' => 'Transactions Retrieved Successfully',
            'status_code' => Response::HTTP_OK,
            'data' => $transactions
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransactionRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
}
