<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Country;
use App\Models\Province;
use App\Models\School;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a paginated, searchable list of users.
     */
    public function index(Request $request): Response
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

        return Inertia::render('admin/users/Index', [
            'users' => $users,
            'filters' => ['search' => $search],
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): Response
    {
        return Inertia::render('admin/users/Create', [
            'roles' => Role::orderBy('name')->pluck('name'),
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $this->syncProfile($user, $validated);

        // Admin-created accounts are considered verified immediately.
        $user->forceFill(['email_verified_at' => now()])->save();

        $user->syncRoles($validated['roles'] ?? []);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User created.')]);

        return to_route('admin.users.index');
    }

    /**
     * Show the form for editing the given user.
     */
    public function edit(User $user): Response
    {
        $user->load(['roles:id,name', 'profile.country', 'profile.province', 'profile.school']);

        return Inertia::render('admin/users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
                'country_name' => $user->profile?->country?->name ?? '',
                'province_name' => $user->profile?->province?->name ?? '',
                'school_name' => $user->profile?->school?->name ?? '',
            ],
            'roles' => Role::orderBy('name')->pluck('name'),
        ]);
    }

    /**
     * Update the given user.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
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

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User updated.')]);

        return to_route('admin.users.index');
    }

    /**
     * Remove the given user.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __('You cannot delete your own account.')]);

            return to_route('admin.users.index');
        }

        $user->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User deleted.')]);

        return to_route('admin.users.index');
    }

    /**
     * Format a user for the admin users table.
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
            'school_name' => $user->profile?->school?->name ?? '-',
            'country_name' => $user->profile?->country?->name ?? '-',
            'province_name' => $user->profile?->province?->name ?? '-',
        ];
    }

    /**
     * Create or update the profile data used by the admin users table.
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
                ['name' => $schoolName],
                ['province_id' => $profileData['province_id'] ?? $user->profile?->province_id],
            );
            $profileData['school_id'] = $school->id;
            if (! isset($profileData['province_id']) && $school->province_id !== null) {
                $profileData['province_id'] = $school->province_id;
            }
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
