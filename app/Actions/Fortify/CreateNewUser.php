<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Country;
use App\Models\Province;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'role' => ['nullable', 'string', 'in:teacher,student'],
            'country_name' => ['nullable', 'string', 'max:255'],
            'province_name' => ['nullable', 'string', 'max:255'],
            'school_name' => ['nullable', 'string', 'max:255'],
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'role' => $input['role'] ?? 'student',
        ]);

        $user->assignRole($user->role);

        $profileData = $this->buildProfileData($input);
        if ($profileData !== []) {
            $user->profile()->create($profileData);
        }

        return $user;
    }

    protected function buildProfileData(array $input): array
    {
        $profileData = [];

        $countryName = trim((string) ($input['country_name'] ?? ''));
        if ($countryName !== '') {
            $country = Country::firstOrCreate(
                ['name' => $countryName],
                ['code' => Str::upper(Str::substr($countryName, 0, 2))],
            );
            $profileData['country_id'] = $country->id;
        }

        $provinceName = trim((string) ($input['province_name'] ?? ''));
        if ($provinceName !== '') {
            $province = Province::firstOrCreate(
                ['name' => $provinceName],
                ['country_id' => $profileData['country_id'] ?? null],
            );
            $profileData['province_id'] = $province->id;
            if (! isset($profileData['country_id']) && $province->country_id !== null) {
                $profileData['country_id'] = $province->country_id;
            }
        }

        $schoolName = trim((string) ($input['school_name'] ?? ''));
        if ($schoolName !== '') {
            $school = School::firstOrCreate(
                ['name' => $schoolName],
                ['province_id' => $profileData['province_id'] ?? null],
            );
            $profileData['school_id'] = $school->id;
            if (! isset($profileData['province_id']) && $school->province_id !== null) {
                $profileData['province_id'] = $school->province_id;
            }
        }

        return $profileData;
    }
}
