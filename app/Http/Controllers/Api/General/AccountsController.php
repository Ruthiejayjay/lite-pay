<?php

namespace App\Http\Controllers\Api\General;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Mail\Accounts\NewAccountMail as AccountsNewAccountMail;
use App\Mail\Accounts\UpdateAccountMail;
use App\Mail\NewAccountMail;
use App\Models\Account;
use App\Models\Currency;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
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
     *                     @OA\Property(property="account_holder_name", type="string", example="John Doe"),
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
     *                 @OA\Property(property="account_holder_name", type="string", example="John Doe"),
     *                 @OA\Property(property="account_number", type="number", format="float", example=1234567890),
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
        $user = Auth::user();

        $currency = Currency::where('currency_code', $validated['currency_code'])->first();
        $accountNumber = mt_rand(1000000000, 9999999999);
        $totalWithdrawals = $validated['total_withdrawals'] ?? 0;

        if (!$currency) {
            return response()->json([
                'status' => Status::FAILURE,
                'message' => 'Currency not found',
                'status_code' => Response::HTTP_NOT_FOUND,
            ], Response::HTTP_NOT_FOUND);
        }

        $existingAccount = Account::where('user_id', $user->id)
            ->where('currency_id', $currency->id)
            ->first();

        if ($existingAccount) {
            return response()->json([
                'status' => Status::FAILURE,
                'message' => 'You already have an account with this currency',
                'status_code' => Response::HTTP_CONFLICT,
            ], Response::HTTP_CONFLICT);
        }

        $account = Account::create([
            'user_id' => $user->id,
            'account_holder_name' => $user->name,
            'account_number' => $accountNumber,
            'account_type' => $validated['account_type'],
            'balance' => $validated['balance'],
            'total_deposits' => $validated['total_deposits'],
            'total_withdrawals' => $totalWithdrawals,
            'currency_id' => $currency->id
        ]);

        Notification::create([
            'user_id' => $user->id,
            'account_id' => $account->id,
            'type' => 'account_created',
            'title' => 'New Account',
            'message' => "Your {$currency->currency_code} account has been created successfully with account number {$account->account_number}.",
        ]);

        Mail::to($user->email)->queue(new AccountsNewAccountMail($user, $accountNumber, $currency->currency_code));

        return response()->json([
            'status' => Status::SUCCESS,
            'message' => 'Accounts Created Successfully',
            'status_code' => Response::HTTP_CREATED,
            'data' => $account
        ], Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/accounts/{id}",
     *     summary="Retrieve a specific account",
     *     tags={"Accounts"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the account to retrieve",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Account Retrieved Successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Account Retrieved Successfully"),
     *             @OA\Property(property="status_code", type="integer", example=200),
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
     *         description="Account not Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failure"),
     *             @OA\Property(property="message", type="string", example="Account not Found"),
     *             @OA\Property(property="status_code", type="integer", example=404)
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $account = Account::where('user_id', Auth::id())->where('id', $id)->first();
        if (!$account) {
            return response()->json([
                'status' => Status::FAILURE,
                'message' => 'Account not Found',
                'status_code' => Response::HTTP_NOT_FOUND,
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'status' => Status::SUCCESS,
            'message' => 'Account Retrieved Successfully',
            'status_code' => Response::HTTP_OK,
            'data' => $account
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/accounts/{id}",
     *     summary="Update Account Balance",
     *     description="Update the balance of an account by adding the provided amount to it. This also updates the total deposits.",
     *     operationId="updateAccountBalance",
     *     security={{"sanctum":{}}},
     *     tags={"Accounts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the account to update",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"balance"},
     *             @OA\Property(
     *                 property="balance",
     *                 type="number",
     *                 format="float",
     *                 description="The amount to add to the account balance"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Account Balance Updated Successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Account Balance Updated Successfully"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="user_id", type="string", format="uuid"),
     *                 @OA\Property(property="account_type", type="string"),
     *                 @OA\Property(property="balance", type="number", format="float"),
     *                 @OA\Property(property="total_deposits", type="number", format="float"),
     *                 @OA\Property(property="total_withdrawals", type="number", format="float"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Account not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failure"),
     *             @OA\Property(property="message", type="string", example="Account not Found"),
     *             @OA\Property(property="status_code", type="integer", example=404)
     *         )
     *     )
     * )
     */
    public function update(UpdateAccountRequest $request, $id)
    {
        $validated = $request->validated();
        $user = Auth::user();
        $account = Account::where('user_id', $user->id)->where('id', $id)->firstOrFail();

        $amountToAdd = $validated['balance'];

        $account->update([
            'balance' => $account->balance + $amountToAdd,
            'total_deposits' => $account->total_deposits + $amountToAdd
        ]);
        Mail::to($user->email)->queue(new UpdateAccountMail($user, $account->account_number, $amountToAdd));
        return response()->json([
            'status' => Status::SUCCESS,
            'message' => 'Account Balance Updated Successfully',
            'status_code' => Response::HTTP_OK,
            'data' => $account
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/accounts/{id}",
     *     summary="Delete a specific account",
     *     tags={"Accounts"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the account to delete",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Account Deleted Successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status_code", type="integer", example=204)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Account not Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failure"),
     *             @OA\Property(property="message", type="string", example="Account not Found"),
     *             @OA\Property(property="status_code", type="integer", example=404)
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        $account = Account::where('user_id', Auth::id())->where('id', $id)->first();
        if (!$account) {
            return response()->json([
                'status' => Status::FAILURE,
                'message' => 'Account not Found',
                'status_code' => Response::HTTP_NOT_FOUND,
            ], Response::HTTP_NOT_FOUND);
        }

        $account->delete();

        return response()->json([
            'status_code' => Response::HTTP_NO_CONTENT
        ], Response::HTTP_NO_CONTENT);
    }
}
