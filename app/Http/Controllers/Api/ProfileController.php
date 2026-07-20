<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Get the authenticated user's profile.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load(['profile.school']);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'profile_image' => $user->profile_image,
                'profile_image_url' => $user->profile_image
                    ? asset('storage/'.$user->profile_image)
                    : null,
                'school' => $user->profile?->school?->school_name ?? null,
            ],
        ]);
    }

    /**
     * Update the authenticated user's profile (name).
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $user->name = $validated['name'];
        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'profile_image' => $user->profile_image,
                'profile_image_url' => $user->profile_image
                    ? asset('storage/'.$user->profile_image)
                    : null,
                'school' => $user->profile?->school?->school_name ?? null,
            ],
        ]);
    }

    /**
     * Upload a profile image.
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);

        $user = $request->user();

        // Delete old image if exists
        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        // Store new image
        $path = $request->file('image')->store('profile-images', 'public');

        $user->profile_image = $path;
        $user->save();

        return response()->json([
            'message' => 'Profile image updated successfully.',
            'profile_image' => $path,
            'profile_image_url' => asset('storage/'.$path),
        ]);
    }
}
