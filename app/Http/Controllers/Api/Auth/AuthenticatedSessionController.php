<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */

    /**
     * Login a user.
     *
     * Login a user with the provided information and returns a success response upon successful registration.
     *
     * @group Authentication
     * @return \Illuminate\Http\JsonResponse
     *
     * @bodyParam email string required User's email address. Example: user@example.com
     * @bodyParam password string required User's password. Example: mypassword
     *
     * @response {
     *     "status_code": 200,
     *     "message": "Login successful",
     *     "data": {
     *         "access_token": "1|asdfekm...",
     *         "token_type": "Bearer"
     *     }
     * }
     */

    /**
     * @OA\Post(
     *     path="/api/v1/login",
     *     operationId="loginUser",
     *     tags={"Authentication"},
     *     summary="Login a user",
     *     description="User Login Endpoint",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"email","password"},
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     example="user@example.com"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     example="mypassword"
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="Login Successfully",
     *       @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Unprocessable Entity",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *         @OA\JsonContent()
     *     ),
     * )
     */

    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => 'Login successful',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]
        ]);
    }

    /**
     * Destroy an authenticated session.
     */

    /**
     * @OA\Post(
     *     path="/api/v1/logout",
     *     operationId="logout",
     *     tags={"Authentication"},
     *     summary="Log out the authenticated user",
     *     description="Revokes the authenticated user's tokens and invalidates the session.",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Logout successful")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status_code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();
        $user->tokens()->delete();
        // $request->session()->invalidate();
        // $request->session()->regenerateToken();

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => 'Logout successful'
        ]);
    }
}
