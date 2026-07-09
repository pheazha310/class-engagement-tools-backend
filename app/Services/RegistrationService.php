<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegistrationService
{
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
            ]);

            UserProfile::create([
                'user_id' => $user->id,
                'country_id' => $data['country_id'] ?? null,
                'province_id' => $data['province_id'] ?? null,
                'school_id' => $data['school_id'] ?? null,
            ]);

            return $user;
        });
    }
}
