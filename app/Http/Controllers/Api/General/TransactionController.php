<?php

namespace App\Http\Controllers\Api\General;

use App\Enums\Status;
use App\Exceptions\InsufficientBalanceException;
use App\Models\Transaction;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Http\Controllers\Controller;
use App\Mail\Transactions\InsufficientBalanceEmail;
use App\Mail\Transactions\TransactionSuccessfulReceiverEmail;
use App\Mail\Transactions\TransactionSuccessfulSenderEmail;
use App\Models\Account;
use App\Models\Currency;
use App\Models\Notification;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

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
     * Store a newly created resource in storage.
     */

    /**
     * @OA\Post(
     *     path="/api/v1/transactions",
     *     summary="Create a new transaction",
     *     description="Process a transaction between the sender and receiver accounts.",
     *     tags={"Transactions"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"receiver_account_number", "currency_code", "amount"},
     *             @OA\Property(property="receiver_account_number", type="integer", example="1234567890", description="Receiver's account number"),
     *             @OA\Property(property="currency_code", type="string", example="USD", description="Currency code for the transaction"),
     *             @OA\Property(property="amount", type="number", format="float", example="100.50", description="Amount to transfer"),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Transaction successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Transaction successful"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="uuid"),
     *                 @OA\Property(property="sender_account_id", type="string", example="uuid"),
     *                 @OA\Property(property="receiver_account_id", type="string", example="uuid"),
     *                 @OA\Property(property="currency_id", type="string", example="uuid"),
     *                 @OA\Property(property="amount", type="number", example="100.50"),
     *                 @OA\Property(property="status", type="string", example="completed")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failure"),
     *             @OA\Property(property="message", type="string", example="Error message")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failure"),
     *             @OA\Property(property="message", type="string", example="Error message")
     *         )
     *     )
     * )
     */

    public function store(StoreTransactionRequest $request)
    {
        DB::beginTransaction();
        try {
            $selectedCurrency = Currency::where('currency_code', $request->currency_code)->first();
            $senderAccount = $this->getSenderAccount($selectedCurrency);
            $receiverAccount = $this->getReceiverAccount($request->receiver_account_number);
            $amount = $request->amount;

            $this->checkSenderAccount($senderAccount);
            $this->checkReceiverAccount($receiverAccount);
            $this->checkSufficientBalance($senderAccount, $amount);

            $this->processTransaction($senderAccount, $receiverAccount, $amount);

            $transaction = $this->createTransaction($senderAccount, $receiverAccount, $selectedCurrency->id, $amount);

            DB::commit();

            Notification::create([
                'user_id' => $senderAccount->user_id,
                'transaction_id' => $transaction->id,
                'type' => 'transaction',
                'title' => 'Outgoing Transaction',
                'message' => "You sent {$transaction->amount} to {$receiverAccount->account_holder_name}.",
            ]);

            Notification::create([
                'user_id' => $receiverAccount->user_id,
                'transaction_id' => $transaction->id,
                'type' => 'transaction',
                'title' => 'Incoming Transaction',
                'message' => "You received {$transaction->amount} from {$senderAccount->account_holder_name}.",
            ]);

            Mail::to($senderAccount->user->email)->queue(new TransactionSuccessfulSenderEmail($senderAccount, $amount));
            Mail::to($receiverAccount->user->email)->queue(new TransactionSuccessfulReceiverEmail($receiverAccount, $amount));
            return $this->successResponse('Transaction successful', $transaction, Response::HTTP_CREATED);
        } catch (InsufficientBalanceException $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), null, $e->getCode());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Transaction failed', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    /**
     * @OA\Get(
     *     path="/api/v1/transactions/{id}",
     *     tags={"Transactions"},
     *     summary="Get a transaction",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the transaction to retrieve",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transaction retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Transaction Retrieved Successfully"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(
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
    public function show(Transaction $transaction)
    {
        $userAccountIds = Auth::user()->accounts->pluck('id');

        $transaction = Transaction::where(function ($query) use ($userAccountIds, $transaction) {
            $query->where('id', $transaction->id)
                ->where(function ($q) use ($userAccountIds) {
                    $q->whereIn('sender_account_id', $userAccountIds)
                        ->orWhereIn('receiver_account_id', $userAccountIds);
                });
        })->first();
        if (!$transaction) {
            return response()->json([
                'status' => Status::FAILURE,
                'message' => 'Transaction not Found',
                'status_code' => Response::HTTP_NOT_FOUND,
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'status' => Status::SUCCESS,
            'message' => 'Transaction Retrieved Successfully',
            'status_code' => Response::HTTP_OK,
            'data' => $transaction
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * @OA\Delete(
     *     path="/api/v1/transactions/{id}",
     *     tags={"Transactions"},
     *     summary="Delete a transaction",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the transaction to delete",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Transaction Deleted Successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status_code", type="integer", example=204)
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
    public function destroy(Transaction $transaction)
    {
        $userAccountIds = Auth::user()->accounts->pluck('id');

        $transaction = Transaction::where(function ($query) use ($userAccountIds, $transaction) {
            $query->where('id', $transaction->id)
                ->where(function ($q) use ($userAccountIds) {
                    $q->whereIn('sender_account_id', $userAccountIds)
                        ->orWhereIn('receiver_account_id', $userAccountIds);
                });
        })->first();
        if (!$transaction) {
            return response()->json([
                'status' => Status::FAILURE,
                'message' => 'Transaction not Found',
                'status_code' => Response::HTTP_NOT_FOUND,
            ], Response::HTTP_NOT_FOUND);
        }

        $transaction->delete();

        return response()->json([
            'status_code' => Response::HTTP_NO_CONTENT
        ], Response::HTTP_NO_CONTENT);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/transactions/currencies/{id}",
     *     tags={"Transactions"},
     *     summary="Get all transactions belonging to a currency",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the currency to retrieve transactions",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
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
    public function getTransactionsByCurrency($currencyId)
    {
        $userAccountIds = Auth::user()->accounts->pluck('id');
        $transactions = Transaction::where('currency_id', $currencyId)
            ->where(function ($query) use ($userAccountIds) {
                $query->whereIn('sender_account_id', $userAccountIds)
                    ->orWhereIn('receiver_account_id', $userAccountIds);
            })
            ->get();
        if ($transactions->isEmpty()) {
            return response()->json([
                'status' => Status::SUCCESS,
                'message' => 'No transactions found for this currency',
                'status_code' => Response::HTTP_OK,
                'data' => []
            ], Response::HTTP_OK);
        }
        return response()->json([
            'status' => Status::SUCCESS,
            'message' => 'Transactions Retrieved Successfully for Currency',
            'status_code' => Response::HTTP_OK,
            'data' => $transactions
        ], Response::HTTP_OK);
    }

    /**
     * Get the authenticated user's account for the selected currency.
     */
    protected function getSenderAccount($selectedCurrency)
    {
        return Auth::user()->accounts->where('currency_id', $selectedCurrency->id)->first();
    }

    /**
     * Get the receiver's account by account number.
     */
    protected function getReceiverAccount($receiverAccountNumber)
    {
        return Account::where('account_number', $receiverAccountNumber)->first();
    }

    /**
     * Check if sender account exists.
     */
    protected function checkSenderAccount($senderAccount)
    {
        if (!$senderAccount) {
            return $this->errorResponse('No associated account found with the selected currency.', null, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Check if receiver account exists.
     */
    protected function checkReceiverAccount($receiverAccount)
    {
        if (!$receiverAccount) {
            return $this->errorResponse('Receiver account not found.', null, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Check if sender has sufficient balance.
     */
    protected function checkSufficientBalance($senderAccount, $amount)
    {
        if ($senderAccount->balance < $amount) {
            Mail::to($senderAccount->user->email)->send(new InsufficientBalanceEmail($senderAccount, $amount));
            throw new InsufficientBalanceException();
        }
    }

    /**
     * Deduct from sender and add to receiver.
     */
    protected function processTransaction($senderAccount, $receiverAccount, $amount)
    {
        // Deduct from sender
        $senderAccount->balance -= $amount;
        $senderAccount->total_withdrawals += $amount;
        $senderAccount->save();

        // Add to receiver
        $receiverAccount->balance += $amount;
        $receiverAccount->total_deposits += $amount;
        $receiverAccount->save();
    }

    /**
     * Create the transaction.
     */
    protected function createTransaction($senderAccount, $receiverAccount, $currencyId, $amount)
    {
        return Transaction::create([
            'sender_account_id' => $senderAccount->id,
            'receiver_account_id' => $receiverAccount->id,
            'receiver_account_number' => $receiverAccount->account_number,
            'receiver_account_holder_name' => $receiverAccount->account_holder_name,
            'currency_id' => $currencyId,
            'amount' => $amount,
            'status' => 'completed',
        ]);
    }

    /**
     * Return a success response.
     */
    protected function successResponse($message, $data = null, $statusCode = Response::HTTP_OK)
    {
        return response()->json([
            'status' => Status::SUCCESS,
            'message' => $message,
            'status_code' => $statusCode,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Return an error response.
     */
    protected function errorResponse($message, $error = null, $statusCode = Response::HTTP_BAD_REQUEST)
    {
        return response()->json([
            'status' => Status::FAILURE,
            'message' => $message,
            'status_code' => $statusCode,
            'error' => $error
        ], $statusCode);
    }
}
