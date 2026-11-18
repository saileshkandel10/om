<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Resources\Api\V1\UserProfileResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user and return a Sanctum token.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
            'mobile' => $request->validated('mobile'),
            'status' => 'active',
        ]);

        $role = Role::where('name', $request->validated('role'))->first();

        if ($role) {
            $user->roles()->attach($role);
        }

        $token = $user->createToken($request->validated('device_name'))->plainTextToken;

        $user->load(['roles', 'rewardWallet']);

        return response()->json([
            'message' => 'Registration successful.',
            'user' => new UserProfileResource($user),
            'token' => $token,
        ], 201);
    }

    /**
     * Authenticate and return a Sanctum token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->validated('email'))->first();

        if (! $user || ! $user->password || ! Hash::check($request->validated('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('The provided credentials are incorrect.')],
            ]);
        }

        $token = $user->createToken($request->validated('device_name'))->plainTextToken;

        $user->load(['roles', 'rewardWallet', 'shareholderAccount', 'pharmacy', 'doctor']);

        return response()->json([
            'user' => new UserProfileResource($user),
            'token' => $token,
        ]);
    }

    /**
     * Revoke the current access token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * Return the authenticated user with full profile.
     */
    public function user(Request $request): UserProfileResource
    {
        $user = $request->user();
        $user->load(['roles', 'rewardWallet', 'shareholderAccount.master', 'pharmacy', 'doctor']);

        return new UserProfileResource($user);
    }
}
