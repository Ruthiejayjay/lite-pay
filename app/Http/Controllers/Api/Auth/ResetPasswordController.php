<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordLinkEmail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/v1/auth/reset-password-link",
     *     summary="Send a password reset link to the user's email",
     *     description="Generates a password reset token and sends an email with the reset link.",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com", description="The email address of the user requesting a password reset.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reset link sent successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Reset link sent successfully. Please check your email.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", additionalProperties=@OA\Property(type="array", @OA\Items(type="string"))),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="We couldn’t find a user with that email."),
     *         )
     *     )
     * )
     */
    public function resetPasswordLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email']
        ]);

        $email = $request->email;

        $user = DB::table('users')->where('email', $email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ["we couldn't find a user with this email."],
            ]);
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => $token, 'created_at' => now()]
        );

        Mail::to($email)->queue(new ResetPasswordLinkEmail($email, $token));

        return response()->json([
            'success' => true,
            'message' => 'Reset link sent successfully. Please check your email.',
        ], Response::HTTP_OK);
    }
}
