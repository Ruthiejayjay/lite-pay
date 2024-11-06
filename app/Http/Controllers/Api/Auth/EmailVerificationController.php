<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Mail\EmailVerificationMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;



class EmailVerificationController extends Controller
{

    /**
     * Generate the email verification URL for the given user.
     *
     * @param  \App\Models\User  $user
     * @return string
     */

    /**
     * @OA\Post(
     *     path="/api/v1/auth/generate-verification-url",
     *     summary="Generate email verification URL",
     *     description="Generates a temporary signed email verification URL for a given user.",
     *     operationId="generateVerificationUrl",
     *     tags={"Authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "email"},
     *             @OA\Property(property="user_id", type="string", example="406fdaa9-6927-47ec-aed6-d6d371422916", description="User ID (UUID)"),
     *             @OA\Property(property="email", type="string", example="user@example.com", description="User email address")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully generated verification URL.",
     *         @OA\JsonContent(
     *             @OA\Property(property="url", type="string", example="https://your-domain.com/api/email/verify/1/6b8b4567")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid user data provided.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid user data provided.")
     *         )
     *     )
     * )
     */

    public function generateVerificationUrl(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
            'email' => 'required|email',
        ]);

        $user = User::find($validatedData['user_id']);
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        if ($user->email !== $validatedData['email']) {
            return response()->json(['message' => 'Email does not match the user.'], 400);
        }

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );
        Log::info($verificationUrl);
        Mail::to($user->email)->queue(new EmailVerificationMail($user, $verificationUrl));

        return response()->json(['url' => $verificationUrl], 200);
    }

    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function verify(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        if (!$user->hasVerifiedEmail()) {
            if ($request->hasValidSignature()) {
                $user->markEmailAsVerified();
                event(new Verified($user));
            } else {
                return response()->json([
                    'message' => 'Invalid or expired verification link.',
                ], 422);
            }
        }

        return response()->json([
            'message' => 'Email verified successfully.',
        ], 200);
    }
}
