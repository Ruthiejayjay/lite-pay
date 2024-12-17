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
     *     tags={"Password Management"},
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
     *             @OA\Property(property="success", type="boolean", example=success),
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

    /**
     * @OA\Post(
     *     path="/api/v1/auth/password/change",
     *     summary="Change Password",
     *     description="Change the user's password after verifying the provided verification code.",
     *     tags={"Password Management"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Request payload for changing the password.",
     *         @OA\JsonContent(
     *             required={"verification_code", "new_password", "new_password_confirmation"},
     *             @OA\Property(property="verification_code", type="string", example="ABC123", description="The verification code sent to the user's email."),
     *             @OA\Property(property="new_password", type="string", format="password", example="newSecurePassword123", description="The new password."),
     *             @OA\Property(property="new_password_confirmation", type="string", format="password", example="newSecurePassword123", description="Confirmation of the new password.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Password changed successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="string", example="success", description="Status of the request."),
     *             @OA\Property(property="message", type="string", example="Your password has been changed successfully.", description="Success message."),
     *             @OA\Property(property="status_code", type="integer", example=201, description="HTTP status code.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Invalid or expired verification code.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid or expired verification code.", description="Error message.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized. User not authenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.", description="Authentication error message.")
     *         )
     *     )
     * )
     */

    public function changePassword(Request $request)
    {
        $request->validate([
            'verification_code' => ['required'],
            'new_password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = Auth::user();

        $storedCode = cache()->get('password_change_code_' . $user->id);

        if (!$storedCode || $storedCode !== $request->verification_code) {
            return response()->json([
                'message' => 'Invalid or expired verification code.'
            ], 400);
        }

        $user->forceFill([
            'password' => Hash::make($request->new_password),
        ])->save();

        cache()->forget('password_change_code_' . $user->id);

        return response()->json([
            'success' => Status::SUCCESS,
            'message' => 'Your password has been changed successfully.',
            'status_code' => Response::HTTP_CREATED
        ]);
    }
}
