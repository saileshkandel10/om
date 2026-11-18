<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Profile\UpdateProfileRequest;
use App\Http\Resources\Api\V1\UserProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Show the authenticated user's full profile.
     */
    public function show(Request $request): UserProfileResource
    {
        $user = $request->user();
        $user->load(['roles', 'rewardWallet', 'shareholderAccount.master', 'pharmacy', 'doctor']);

        return new UserProfileResource($user);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(UpdateProfileRequest $request): UserProfileResource
    {
        $user = $request->user();
        $user->update($request->validated());
        $user->load(['roles', 'rewardWallet', 'shareholderAccount.master', 'pharmacy', 'doctor']);

        return new UserProfileResource($user);
    }

    /**
     * Upload a new avatar image.
     */
    public function updateAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        $user = $request->user();
        $path = $request->file('avatar')->store('avatars/'.$user->id, 'public');

        $user->update(['avatar' => url('storage/'.$path)]);

        return response()->json([
            'message' => 'Avatar updated successfully.',
            'avatar' => $user->avatar,
        ]);
    }
}
