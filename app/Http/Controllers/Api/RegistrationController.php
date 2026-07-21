<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrationRequest;
use App\Services\RegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class RegistrationController extends Controller
{
    public function __construct(
        private readonly RegistrationService $registrationService,
    ) {}

    public function register(RegistrationRequest $request): JsonResponse
    {
        $user = $this->registrationService->register($request->validated());
        Auth::login($user);
        $request->session()->regenerate();

        $user->loadMissing(['profile.school']);

        return response()->json([
            'message' => 'Registration successful.',
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
        ], 201);
    }
}
