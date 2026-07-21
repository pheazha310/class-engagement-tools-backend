<?php

namespace App\Services;

use App\Models\Country;
use App\Models\Province;
use App\Models\School;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class RegistrationService
{
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'] ?? 'student',
            ]);

            $profileData = $this->buildProfileData($data);
            if ($profileData !== []) {
                UserProfile::create(array_merge([
                    'user_id' => $user->id,
                ], $profileData));
            }

            $roleName = $data['role'] ?? 'student';
            Role::findOrCreate($roleName, config('auth.defaults.guard', 'web'));
            $user->assignRole($roleName);

            return $user;
        });
    }

    private function buildProfileData(array $data): array
    {
        $profileData = [];

        $countryName = trim((string) ($data['country_name'] ?? ''));
        if ($countryName !== '') {
            $country = Country::firstOrCreate(
                ['name' => $countryName],
                ['code' => Str::upper(Str::substr($countryName, 0, 2))],
            );
            $profileData['country_id'] = $country->id;
        } elseif (isset($data['country_id'])) {
            $profileData['country_id'] = $data['country_id'];
        }

        $provinceName = trim((string) ($data['province_name'] ?? ''));
        if ($provinceName !== '') {
            $province = Province::firstOrCreate(
                ['name' => $provinceName],
                ['country_id' => $profileData['country_id'] ?? null],
            );
            $profileData['province_id'] = $province->id;
            if (! isset($profileData['country_id']) && $province->country_id !== null) {
                $profileData['country_id'] = $province->country_id;
            }
        } elseif (isset($data['province_id'])) {
            $profileData['province_id'] = $data['province_id'];
        }

        $schoolName = trim((string) ($data['school_name'] ?? ''));
        if ($schoolName !== '') {
            $school = School::firstOrCreate(
                ['school_name' => $schoolName],
                [
                    'country_id' => $profileData['country_id'] ?? null,
                    'province_id' => $profileData['province_id'] ?? null,
                ],
            );
            $profileData['school_id'] = $school->id;
            if (! isset($profileData['country_id']) && $school->country_id !== null) {
                $profileData['country_id'] = $school->country_id;
            }
            if (! isset($profileData['province_id']) && $school->province_id !== null) {
                $profileData['province_id'] = $school->province_id;
            }
        } elseif (isset($data['school_id'])) {
            $profileData['school_id'] = $data['school_id'];
        }

        return $profileData;
    }
}
