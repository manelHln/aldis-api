<?php

namespace App\Http\Controllers;

use App\Contracts\AuthInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="Authentication related endpoints"
 * )
 */
class AuthController extends Controller
{
    public function __construct(protected AuthInterface $authService){}

    /**
     * @OA\Post(
     *     path="/auth/register",
     *     tags={"Auth"},
     *     summary="Register a new user",
     *     description="Create a new user account with the provided details.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"fullname", "password", "password_confirmation", "phone", "role_name"},
     *             @OA\Property(property="fullname", type="string", example="John Doe"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123"),
     *             @OA\Property(property="phone", type="string", example="1234567890"),
     *             @OA\Property(property="role_name", type="string", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         ref="#/components/responses/ServerError"
     *     )
     * )
     */
    public function register(Request $request){
        $request->validate([
            'fullname' => 'required|string|min:5|max:255',
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
            'phone' => 'required|string|min:10|max:15|unique:users',
            'role_name' => 'required|string|in:delivery_man,user',
        ]);

        $data = $this->authService->register($request->all());

        return response()->json([
            'message' => 'User created successfully',
            'data' => $data
        ], 201)->withHeaders([
            'Location' => route('user.show', ['id' => $data['user']->id])
        ]);
    }

    /**
     * @OA\Post(
     *     path="/auth/login",
     *     tags={"Auth"},
     *     summary="Login a user",
     *     description="Authenticate a user and return an access token.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone", "password"},
     *             @OA\Property(property="phone", type="string", example="1234567890"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User logged in successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User logged in successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function login(Request $request){
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required'
        ]);

        $data = $this->authService->authenticate($request->all());

        return response()->json([
            "message" => "User logged in successfully.",
            "data" => $data
        ]);
    }

    /**
     * @OA\Post(
     *     path="/auth/logout",
     *     tags={"Auth"},
     *     summary="Logout a user",
     *     description="Revoke the user's access token.",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logged out successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logged out"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     )
     * )
     */
    public function logout(Request $request){
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out',
            "data" => null
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/auth/reset-password",
     *     tags={"Auth"},
     *     summary="Reset password",
     *     description="Send a password reset link to the user's email.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset link sent",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="Password reset link sent.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function resetPassword(Request $request){
        $request->validate([
            'email' => 'required|email'
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );


        if ($status != Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json(['status' => __($status)]);
    }

    /**
     * @OA\Post(
     *     path="/auth/refresh-token",
     *     tags={"Auth"},
     *     summary="Refresh access token",
     *     description="Generate a new access token for the authenticated user.",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token refreshed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Token refreshed successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", example="new-access-token")
     *             )
     *         )
     *     )
     * )
     */
    public function refreshToken(Request $request){
        $user = $request->user();
        $data = $this->authService->refreshToken($request);

        return response()->json([
            'message' => 'Token refreshed successfully',
            'data' => [
                'token' => $data
            ]
        ]);
    }

}
