<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Mail\RegisterMail;
use App\Mail\WelcomeEmail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */

    /**
     * Register a new user.
     *
     * Registers a new user with the provided information and returns a success response upon successful registration.
     *
     * @group Authentication
     * @return \Illuminate\Http\JsonResponse
     *
     * @bodyParam name string required User's name. Example: John Doe
     * @bodyParam email string required User's email address. Example: user@example.com
     * @bodyParam password string required User's password. Example: mypassword
     * @bodyParam password_confirmation string required Confirm password. Example: mypassword
     *
     * @response {
     *     "status_code": 201,
     *     "message": "User signed up successfully",
     *     "data": {
     *         "user": {
     *             "id": 1,
     *             "name": "John Doe",
     *             "email": "user@example.com",
     *         },
     *         "access_token": "1|asdfekm...",
     *         "token_type": "Bearer"
     *     }
     * }
     */
    /**
     * @OA\Post(
     *     path="/api/v1/register",
     *     operationId="registerUser",
     *     tags={"Authentication"},
     *     summary="Register a new user",
     *     description="User Registration Endpoint",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name","email","password","password_confirmation"},
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     example="John Doe"
     *                 ),
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
     *                 @OA\Property(
     *                     property="password_confirmation",
     *                     type="string",
     *                     example="mypassword"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User Registered Successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status_code", type="integer", example=201),
     *             @OA\Property(property="message", type="string", example="User Registered Successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="user@example.com")
     *                 ),
     *                 @OA\Property(property="access_token", type="string", example="1|asdfekm..."),
     *                 @OA\Property(property="token_type", type="string", example="Bearer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent()
     *     )
     * )
     */

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->string('password')),
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );
        Mail::to($user->email)->send(new WelcomeEmail($user, $verificationUrl));

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status_code' => Response::HTTP_CREATED,
            'message' => 'User Registered Successfully',
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]
        ], Response::HTTP_CREATED);
    }
}
