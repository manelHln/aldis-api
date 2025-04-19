<?php

namespace App\Services;

use App\Models\User;
use App\Http\Resources\UserResource;
use App\Enums\TokenAbility;
use App\Contracts\AuthInterface;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthService implements AuthInterface
{
    public function register(array $data)
    {
        $userExists = User::where('phone', $data['phone'])->first();

        if ($userExists) {
            throw new HttpResponseException(
                response()->json([
                    'message' => 'A user with this phone number already exists.',
                    "errors" => []
                ], 422)
            );
        }

        $new_user = User::create([
            'fullname' => $data['fullname'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'],
        ]);

        // $user_role = Role::findOrFail($data['role_id']);
        $user_role = Role::where('name', $data['role_name'])->first();
        if (!$user_role) {
            throw new HttpResponseException(
                response()->json([
                    'message' => 'Role not found.',
                    "errors" => []
                ], 422)
            );
        }
        if ($user_role->name == 'admin') {
            Log::critical("Attempt to assign admin role to user: " . $new_user->phone);
            throw new HttpResponseException(
                response()->json([
                    'message' => 'Cannot assign admin role to user.',
                    "errors" => []
                ], 422)
            );
        }

        $new_user->assignRole($user_role);

        $access_token_expires_at = Carbon::now()->addMinutes(config('sanctum.expiration'));
        $refresh_token_expires_at = Carbon::now()->addMinutes(config('sanctum.rt_expiration'));

        return [
            'user' => new UserResource($new_user),
            'access_token' => $new_user->createToken($new_user->phone, [TokenAbility::ACCESS_API], $access_token_expires_at)->plainTextToken,
            'refresh_token' => $new_user->createToken($new_user->phone, [TokenAbility::ISSUE_ACCESS_TOKEN->value], $refresh_token_expires_at)->plainTextToken,
        ];
    }

    public function authenticate(array $data)
    {
        $user = User::where('phone', $data['phone'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            Log::warning("Failed login attempt for phone: " . $data['phone']);
            throw new HttpResponseException(
                response()->json([
                    "message" => "Invalid credentials",
                    "errors" => []
                ], 403)
            );
        }

        $user->tokens()->delete(); // Revoke all previous tokens

        $access_token_expires_at = Carbon::now()->addMinutes(config('sanctum.expiration'));
        $refresh_token_expires_at = Carbon::now()->addMinutes(config('sanctum.rt_expiration'));

        $access_token = $user->createToken($user->phone, [TokenAbility::ACCESS_API], $access_token_expires_at)->plainTextToken;
        $refresh_token = $user->createToken($user->phone, [TokenAbility::ISSUE_ACCESS_TOKEN->value], $refresh_token_expires_at)->plainTextToken;

        return [
            'access_token' => $access_token,
            'access_token_expires_at' => $access_token_expires_at,
            'refresh_token' => $refresh_token,
            'refresh_token_expires_at' => $refresh_token_expires_at,
            'token_type' => 'Bearer',
            'user' => new UserResource($user)
        ];
    }

    public function refreshToken(Request $request)
    {
        $current_refresh_token = $request->bearerToken();
        $refresh_token = PersonalAccessToken::findToken($current_refresh_token);

        if (!$refresh_token || !$refresh_token->can(TokenAbility::ISSUE_ACCESS_TOKEN->value) || $refresh_token->expires_at->isPast()) {
            throw new UnauthorizedHttpException('Invalid or expired refresh token');
        }

        $user = $refresh_token->tokenable;
        $refresh_token->delete();

        $access_token_expires_at = Carbon::now()->addMinutes(config('sanctum.expiration'));
        $refresh_token_expires_at = Carbon::now()->addMinutes(config('sanctum.rt_expiration'));

        $new_access_token = $user->createToken('access_token', [TokenAbility::ACCESS_API], $access_token_expires_at)->plainTextToken;
        $new_refresh_token = $user->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], $refresh_token_expires_at)->plainTextToken;

        return [
            'access_token' => $new_access_token,
            'access_token_expires_at' => $access_token_expires_at,
            'refresh_token' => $new_refresh_token,
            'refresh_token_expires_at' => $refresh_token_expires_at,
            'token_type' => 'Bearer',
        ];
    }
}
