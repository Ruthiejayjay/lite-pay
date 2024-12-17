<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ChangePasswordController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/auth/password/send-verification-code",
     *     summary="Send a password change verification code",
     *     description="Generates and sends a verification code to the authenticated user's email for password change verification.",
     *     tags={"Authentication"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 example="user@example.com",
     *                 description="The email address of the authenticated user requesting to change their password."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verification code sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="A verification code has been sent to your email address.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorized access.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="email", type="array", @OA\Items(type="string", example="The email field is required."))
     *             )
     *         )
     *     )
     * )
     */
    public function sendVerificationCode(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $user = Auth::user();

        if ($user->email !== $request->email) {
            return response()->json(['error' => 'Unauthorized access.'], 403);
        }

        $verificationCode = Str::random(6);

        cache()->put('password_change_code_' . $user->id, $verificationCode, now()->addMinutes(20));

        Mail::send('emails.password_change_code', ['code' => $verificationCode, 'user' => $user], function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Your Password Change Verification Code');
        });
        return response()->json([
            'success' => true,
            'message' => 'A verification code has been sent to your email address.'
        ]);
    }

}
