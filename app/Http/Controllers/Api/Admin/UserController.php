<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Country;
use App\Models\Province;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = $request->string('search')->trim()->value();

        $users = User::query()
            ->with(['roles:id,name', 'profile.country', 'profile.province', 'profile.school'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (User $user): array => $this->formatUser($user));

        return response()->json($users);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $this->syncProfile($user, $validated);

        $user->forceFill(['email_verified_at' => now()])->save();
        $user->syncRoles($validated['roles'] ?? []);
        $user->load(['roles:id,name', 'profile.country', 'profile.province', 'profile.school']);

        return response()->json([
            'message' => 'User created.',
            'user' => $this->formatUser($user),
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        $user->load(['roles:id,name', 'profile.country', 'profile.province', 'profile.school']);

        return response()->json($this->formatUser($user));
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $validated = $request->validated();

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        $this->syncProfile($user, $validated);
        $user->syncRoles($validated['roles'] ?? []);
        $user->load(['roles:id,name', 'profile.country', 'profile.province', 'profile.school']);

        return response()->json([
            'message' => 'User updated.',
            'user' => $this->formatUser($user),
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($request->user()->is($user)) {
            return response()->json([
                'message' => 'You cannot delete your own account.',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted.',
        ]);
    }

    public function roles(): JsonResponse
    {
        return response()->json(
            Role::orderBy('name')->pluck('name')
        );
    }

    /**
     * Format a user for admin responses.
     *
     * @return array<string, mixed>
     */
    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'roles' => $user->roles->pluck('name'),
            'school_name' => $user->profile?->school?->school_name ?? '-',
            'country_name' => $user->profile?->country?->name ?? '-',
            'province_name' => $user->profile?->province?->name ?? '-',
            'created_at' => $user->created_at,
        ];
    }

    /**
     * Create or update the profile data used by admin responses.
     *
     * @param  array<string, mixed>  $validated
     */
    private function syncProfile(User $user, array $validated): void
    {
        $profileData = [];

        $countryName = trim((string) ($validated['country_name'] ?? ''));
        if ($countryName !== '') {
            $country = Country::firstOrCreate(
                ['name' => $countryName],
                ['code' => Str::upper(Str::substr($countryName, 0, 2))],
            );
            $profileData['country_id'] = $country->id;
        }

        $provinceName = trim((string) ($validated['province_name'] ?? ''));
        if ($provinceName !== '') {
            $province = Province::firstOrCreate(
                ['name' => $provinceName],
                ['country_id' => $profileData['country_id'] ?? $user->profile?->country_id],
            );
            $profileData['province_id'] = $province->id;
            if (! isset($profileData['country_id']) && $province->country_id !== null) {
                $profileData['country_id'] = $province->country_id;
            }
        }

        $schoolName = trim((string) ($validated['school_name'] ?? ''));
        if ($schoolName !== '') {
            $school = School::firstOrCreate(
                ['school_name' => $schoolName],
                [
                    'country_id' => $profileData['country_id'] ?? $user->profile?->country_id,
                    'province_id' => $profileData['province_id'] ?? $user->profile?->province_id,
                ],
            );
            $profileData['school_id'] = $school->id;
        }

        if ($profileData === []) {
            return;
        }

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $profileData,
        );
    }
}
